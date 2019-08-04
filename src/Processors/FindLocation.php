<?php

namespace Katsana\Geocoder\Processors;

use Illuminate\Contracts\Cache\Repository;
use Katsana\Geocoder\Contracts\ReverseGeocodeListener;
use Katsana\Geocoder\Geocode;
use Katsana\Geocoder\Location;
use Katsana\Geocoder\Manager as GeocoderManager;

class FindLocation
{
    /**
     * The cache repository implementation.
     *
     * @var \Illuminate\Contracts\Cache\Repository
     */
    protected $cache;

    /**
     * Geocoder manager.
     *
     * @var \Katsana\Geocoder\Manager
     */
    protected $geocoder;

    /**
     * Geocode implementation.
     *
     * @var $
     */
    protected $geocode;

    /**
     * Construct a new processor.
     *
     * @param \Katsana\Geocoder\Manager              $geocoder
     * @param \Illuminate\Contracts\Cache\Repository $cache
     */
    public function __construct(GeocoderManager $geocoder, Repository $cache)
    {
        $this->geocoder = $geocoder;

        if (\method_exists($cache->getStore(), 'tags')) {
            $this->cache = $cache->tags('geocoder');
        } else {
            $this->cache = $cache;
        }
    }

    /**
     * Search a location.
     *
     * @param \Katsana\Geocoder\Contracts\ReverseGeocodeListener $listener
     * @param \Katsana\Geocoder\Geocode                          $geocode
     *
     * @return mixed
     */
    public function search(ReverseGeocodeListener $listener, Geocode $geocode)
    {
        $driver = $listener->getGeocoderProvider();
        $coor = $geocode->getCoordinate();

        $address = $geocode->resolve($this->geocoder->driver($driver), $this->cache);

        if ($address === 'N/A') {
            $address = null;
        }

        $location = Location::parse($coor, $address);

        return $listener->foundGeocodedLocation($location);
    }
}
