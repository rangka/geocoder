<?php

namespace Katsana\Geocoder;

use Orchestra\Support\Fluent;

class Place extends Fluent
{
    /**
     * All of the attributes set on the container.
     *
     * @var array
     */
    protected $attributes = [
        'areas' => [],
        'locations' => [],
        'address' => null,
    ];
}
