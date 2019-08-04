<?php

namespace Katsana\Geocoder;

use Geocoder\Model\AddressCollection;
use Orchestra\Support\Fluent;

class Location extends Fluent
{
    /**
     * All of the attributes set on the container.
     *
     * @var array
     */
    protected $attributes = [
        'coordinate' => null,
        'latitude' => null,
        'longitude' => null,
        'street_number' => null,
        'street_name' => null,
        'locality' => null,
        'sublocality' => null,
        'postcode' => null,
        'levels' => null,
        'country' => null,
        'address' => null,
    ];

    /**
     * Create a new fluent container instance.
     *
     * @param array|object $attributes
     */
    public function __construct($attributes = [])
    {
        parent::__construct($attributes);

        $this->address = static::resolveAddressFromLocation($this);
    }

    /**
     * Parse instanceof self or make from collection.
     *
     * @param string $searchCoordinate
     * @param array  $attributes
     *
     * @return $this
     */
    public static function parse(string $searchCoordinate, $attributes = [])
    {
        if ($attributes instanceof self) {
            return $attributes;
        } elseif (! \is_array($attributes)) {
            $attributes = [];
        }

        return \tap(new static($attributes), static function ($self) use ($searchCoordinate) {
            $self['coordinate'] = $searchCoordinate;
        });
    }

    /**
     * Make new instance.
     *
     * @param string                                 $searchCoordinate
     * @param \Geocoder\Model\AddressCollection|null $collection
     *
     * @return $this
     */
    public static function make(string $searchCoordinate, AddressCollection $collection = null)
    {
        if (\is_null($collection)) {
            return new static();
        }

        $addresses = \collect($collection->all());

        $geocoded = $addresses->filter(static function ($geocode) {
            return ! \is_null($geocode->getSubLocality()) || ! \is_null($geocode->getStreetNumber());
        })->first();

        if (\is_null($geocoded)) {
            $geocoded = $addresses->first();
        }

        if (\is_null($geocoded)) {
            return new static();
        }

        return new static([
            'coordinate' => $searchCoordinate,
            'latitude' => $geocoded->getCoordinates()->getLatitude(),
            'longitude' => $geocoded->getCoordinates()->getLongitude(),
            'street_number' => $geocoded->getStreetNumber(),
            'street_name' => $geocoded->getStreetName(),
            'locality' => $geocoded->getLocality(),
            'sublocality' => $geocoded->getSubLocality(),
            'postcode' => $geocoded->getPostalCode(),
            'levels' => $geocoded->getAdminLevels(),
            'country' => \optional($geocoded->getCountry())->getName(),
            'provider' => $geocoded->getProvidedBy(),
        ]);
    }

    /**
     * Get location coordinate.
     *
     * @return string
     */
    public function getCoordinate(): string
    {
        return $this->attributes['coordinate'] ?? "{$this->attributes['latitude']},{$this->attributes['longitude']}";
    }

    /**
     * Resolve address from location.
     *
     * @param self $location
     *
     * @return string
     */
    public static function resolveAddressFromLocation(self $location): string
    {
        $locality = $location->sublocality;

        if ($locality !== $location->locality && ! \is_null($location->locality)) {
            $locality = (! empty($locality) ? "{$locality}, " : '').$location->locality;
        }

        $address = [
            $location->street_number,
            $location->street_name,
            \trim($location->postcode.' '.$locality),
            $location->country,
        ];

        return $location->address ?? \implode(', ', \array_filter($address, static function ($value) {
            if (! empty($value)) {
                return $value;
            }
        }));
    }
}
