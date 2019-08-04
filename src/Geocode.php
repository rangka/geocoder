<?php

namespace Katsana\Geocoder;

use Exception;
use Geocoder\Exception\CollectionIsEmpty;
use Geocoder\Exception\NoResult;
use Geocoder\Geocoder;
use Geocoder\Query\ReverseQuery;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Contracts\Cache\Repository;

abstract class Geocode
{
    /**
     * Exact latitude.
     *
     * @var float
     */
    protected $latitude;

    /**
     * Exact longitude.
     *
     * @var float
     */
    protected $longitude;

    /**
     * Proximity.
     *
     * @var \App\Geocoder\Proximity
     */
    protected $proximity;

    /**
     * Construct new geocode.
     *
     * @param float $latitude
     * @param float $longitude
     */
    public function __construct($latitude, $longitude)
    {
        $this->latitude = $latitude;
        $this->longitude = $longitude;

        $this->proximity = new Proximity($latitude, $longitude);
    }

    /**
     * Resolve address.
     *
     * @param \Geocoder\Geocoder                     $geocoder
     * @param \Illuminate\Contracts\Cache\Repository $cache
     *
     * @return \App\Geocoder\Location|null
     */
    public function resolve(Geocoder $geocoder, Repository $cache): ?Location
    {
        if (! \is_null($proximityLocation = $cache->get($this->getProximityCacheKey()))) {
            return Location::parse($this->getCoordinate(), $proximityLocation);
        }

        if (! \is_null($location = $this->queryFromLocalProvider($geocoder, $cache))) {
            return $location;
        }

        return $this->queryFromProvider($geocoder, $cache);
    }

    /**
     * Fetch from geocoder.
     *
     * @param \Geocoder\Geocoder                     $geocoder
     * @param \Illuminate\Contracts\Cache\Repository $cache
     *
     * @return \App\Geocoder\Location|null
     */
    public function queryFromProvider(Geocoder $geocoder, Repository $cache): ?Location
    {
        $location = null;
        $coor = $this->getCoordinate();
        $cacheKey = $this->getProximityCacheKey();

        try {
            $location = Location::make(
                $coor, $geocoder->reverseQuery(ReverseQuery::fromCoordinates($this->getLatitude(), $this->getLongitude()))
            );

            \event(new Events\LocationFound($location));
        } catch (CollectionIsEmpty $e) {
            $cache->add($cacheKey, 'N/A', 43800);
        } catch (NoResult $e) {
            $cache->add($cacheKey, 'N/A', 43800);
        } catch (BadResponseException $e) {
            \info("Failed to retrieved geocoder for {$this->getProximityCoordinate()}, search for {$coor}");
        } catch (Throwable | Exception $e) {
            \report($e);
        }

        return $location;
    }

    /**
     * Forget resolved address.
     *
     * @param \Illuminate\Contracts\Cache\Repository $cache
     * @param \App\Geocoder\Location|null            $address
     *
     * @return void
     */
    public function forget(Repository $cache): void
    {
        $cache->forget($this->getProximityCacheKey());
    }

    /**
     * Cache resolved address.
     *
     * @param \Illuminate\Contracts\Cache\Repository $cache
     * @param \App\Geocoder\Location|null            $address
     * @param bool                                   $force
     *
     * @return void
     */
    public function cache(Repository $cache, ?Location $address, bool $force = false): void
    {
        $key = $this->getProximityCacheKey();

        if (\is_null($address) || ($cache->has($key) && ! $force)) {
            return;
        }

        $cache->put($key, $address->toArray(), 2592000);
    }

    /**
     * Get exact coordinate.
     *
     * @return string
     */
    public function getCoordinate(): string
    {
        return "{$this->getLatitude()},{$this->getLongitude()}";
    }

    /**
     * Get exact latitude.
     *
     * @return float
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Get exact longitude.
     *
     * @return float
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Get proximity coordinate.
     *
     * @return string
     */
    public function getProximityCoordinate(): string
    {
        return $this->proximity->getCoordinate();
    }

    /**
     * Get proximity coordinate.
     *
     * @return string
     */
    public function getProximitySector(): string
    {
        return $this->proximity->getSector();
    }

    /**
     * Get proximity size.
     *
     * @return string
     */
    public function getProximityCacheKey(): string
    {
        return $this->proximity->getCacheKey();
    }

    /**
     * Get proximity latitude.
     *
     * @return int
     */
    public function getProximityLatitude()
    {
        $this->proximity->getLatitude();
    }

    /**
     * Get proximity longitude.
     *
     * @return int
     */
    public function getProximityLongitude()
    {
        $this->proximity->getLongitude();
    }

    /**
     * Fetch from local provider.
     *
     * @param \Geocoder\Geocoder                     $geocoder
     * @param \Illuminate\Contracts\Cache\Repository $cache
     *
     * @return \Katsana\Geocoder\Location|null
     */
    abstract public function queryFromLocalProvider(Geocoder $geocoder, Repository $cache): ?Location;
}
