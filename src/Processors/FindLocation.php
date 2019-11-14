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
     */
    public function __construct(GeocoderManager $geocoder, Repository $cache)
    {
        $this->geocoder = $geocoder;

        $this->cache = \rescue(static function () use ($cache) {
            return $cache->tags('geocoder');
        }, $cache, false);
    }

    /**
     * Search a location.
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
