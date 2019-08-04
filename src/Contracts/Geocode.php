<?php

namespace Katsana\Geocoder\Contracts;

use Geocoder\Geocoder;
use Illuminate\Contracts\Cache\Repository;
use Katsana\Geocoder\Location;

interface Geocode
{
    /**
     * Resolve address.
     *
     * @param \Geocoder\Geocoder                     $geocoder
     * @param \Illuminate\Contracts\Cache\Repository $cache
     *
     * @return \Katsana\Geocoder\Location|null
     */
    public function resolve(Geocoder $geocoder, Repository $cache): ?Location;
}
