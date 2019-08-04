<?php

namespace Katsana\Geocoder\Contracts;

use Katsana\Geocoder\Location;

interface ReverseGeocodeListener
{
    /**
     * Found location and handle it.
     *
     * @param \Katsana\Geocoder\Location $location
     *
     * @return mixed
     */
    public function foundGeocodedLocation(Location $location);

    /**
     * Get geocoder provider.
     *
     * @return string
     */
    public function getGeocoderProvider(): string;
}
