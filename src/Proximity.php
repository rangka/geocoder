<?php

namespace Katsana\Geocoder;

class Proximity
{
    const EARTH_RADIUS = 6371000;

    /**
     * Actual latitude.
     *
     * @var int
     */
    protected $latitude;

    /**
     * Actual longitude.
     *
     * @var int
     */
    protected $longitude;

    /**
     * Proximity latitude.
     *
     * @var int
     */
    protected $proximityLatitude;

    /**
     * Proximity longitude.
     *
     * @var int
     */
    protected $proximityLongitude;

    /**
     * Proximity in meter.
     *
     * @var int
     */
    protected $proximityInMeter = 50;

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
    }

    /**
     * Get proximity coordinate.
     */
    public function getSector(): string
    {
        return "{$this->proximityInMeter}:{$this->getCoordinate()}";
    }

    /**
     * Get proximity coordinate.
     */
    public function getCoordinate(): string
    {
        return "{$this->getLatitude()},{$this->getLongitude()}";
    }

    /**
     * Get exact latitude.
     *
     * @return int
     */
    public function getLatitude()
    {
        if (! isset($this->proximityLatitude)) {
            $radians = $this->getRadiansByDegree($this->latitude);

            $this->proximityLatitude = \round($radians / $this->getLatitudeKeyLength());
        }

        return $this->proximityLatitude;
    }

    /**
     * Get exact longitude.
     *
     * @return int
     */
    public function getLongitude()
    {
        if (! isset($this->proximityLongitude)) {
            $radians = $this->getRadiansByDegree($this->longitude);

            $this->proximityLongitude = \round($radians / $this->getLongitudeKeyLength($this->latitude));
        }

        return $this->proximityLongitude;
    }

    /**
     * Get proximity size.
     */
    public function getCacheKey(): string
    {
        return "geocoder.proximity.{$this->getSector()}";
    }

    /**
     * Get radians by proximity.
     *
     * @return float
     */
    final protected function getRadiansByProximity()
    {
        return $this->getRadiansByDistance($this->proximityInMeter);
    }

    /**
     * Get radians by degree.
     *
     * @param int|float $degree
     *
     * @return float
     */
    final protected function getRadiansByDegree($degree)
    {
        return (M_PI / 180) * $degree;
    }

    /**
     * Get radians by distance.
     *
     * @param int|float $distance
     *
     * @return float
     */
    final protected function getRadiansByDistance($distance)
    {
        return $distance / static::EARTH_RADIUS;
    }

    /**
     * Get latitude key length.
     *
     * @return float
     */
    final protected function getLatitudeKeyLength()
    {
        return $this->getRadiansByProximity();
    }

    /**
     * Get longitude key length.
     *
     * @param float $latitude
     *
     * @return float
     */
    final protected function getLongitudeKeyLength($latitude)
    {
        $operand = (2 * \sin($this->getRadiansByProximity() / 2)) / \cos($this->getRadiansByDegree($latitude));

        return ($operand > M_PI / 2) ? 1 : \asin($operand);
    }
}
