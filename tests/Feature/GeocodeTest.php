<?php

namespace Katsana\Geocoder\Tests\Feature\Geocoder;

use Geocoder\Geocoder;
use Illuminate\Contracts\Cache\Repository;
use Katsana\Geocoder\Location;
use Mockery as m;
use Orchestra\Testbench\TestCase;

class GeocodeTest extends TestCase
{
    /** @test */
    public function it_doesnt_cache_proximity_if_location_is_null()
    {
        $geocode = new Geocode(3.1339905, 101.6070148);
        $location = null;

        $cache = m::mock(Repository::class);

        $cache->shouldReceive('has')->never()
                ->shouldReceive('put')->never();

        $geocode->cache($cache, $location);
    }

    /** @test */
    public function it_doesnt_cache_proximity_if_already_stored()
    {
        $data = [
            'coordinate' => '3.1339905,101.6070148',
            'latitude' => 3.1339905,
            'longitude' => 101.6070148,
            'street_number' => null,
            'street_name' => 'Lebuh Bandar Utama',
            'locality' => 'Petaling Jaya',
            'sublocality' => 'Bandar Utama',
            'postcode' => '47800',
            'country' => 'Malaysia',
            'address' => 'Lebuh Bandar Utama, 47800 Bandar Utama, Petaling Jaya, Malaysia',
        ];

        $geocode = new Geocode(3.1339905, 101.6070148);
        $location = Location::parse('3.1339905,101.6070148', $data);

        $cache = m::mock(Repository::class);

        $cache->shouldReceive('has')->once()
                    ->with("geocoder.proximity.50:{$geocode->getProximityCoordinate()}")->andReturn(true)
                ->shouldReceive('put')->never();

        $geocode->cache($cache, $location);
    }

    /** @test */
    public function it_can_force_cache_proximity_if_already_stored()
    {
        $data = [
            'coordinate' => '3.1339905,101.6070148',
            'latitude' => 3.1339905,
            'longitude' => 101.6070148,
            'street_number' => null,
            'street_name' => 'Lebuh Bandar Utama',
            'locality' => 'Petaling Jaya',
            'sublocality' => 'Bandar Utama',
            'postcode' => '47800',
            'country' => 'Malaysia',
            'address' => 'Lebuh Bandar Utama, 47800 Bandar Utama, Petaling Jaya, Malaysia',
        ];

        $geocode = new Geocode(3.1339905, 101.6070148);
        $location = Location::parse('3.1339905,101.6070148', $data);
        $cacheKey = "geocoder.proximity.50:{$geocode->getProximityCoordinate()}";

        $cache = m::mock(Repository::class);

        $cache->shouldReceive('has')->once()
                    ->with($cacheKey)->andReturn(true)
                ->shouldReceive('put')->once()
                    ->with($cacheKey, $location->toArray(), 2592000);

        $geocode->cache($cache, $location, true);
    }
}

class Geocode extends \Katsana\Geocoder\Geocode
{
    /**
     * Fetch from local provider.
     */
    public function queryFromLocalProvider(Geocoder $geocoder, Repository $cache): ?Location
    {
        return null;
    }
}
