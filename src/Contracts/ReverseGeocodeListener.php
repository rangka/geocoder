<?php

namespace Katsana\Geocoder\Contracts;

use Katsana\Geocoder\Location;

interface ReverseGeocodeListener
{
    /**
     * Found location and handle it.
     *
     * @return mixed
     */
    public function foundGeocodedLocation(Location $location);

    /**
     * Get geocoder provider.
     */
    public function getGeocoderProvider(): string;
}
