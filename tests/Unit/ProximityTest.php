<?php

namespace Katsana\Geocoder\Tests\Unit;

use Katsana\Geocoder\Proximity;
use PHPUnit\Framework\TestCase;

class ProximityTest extends TestCase
{
    /**
     * @test
     * @dataProvider proximityDataProvider
     */
    public function it_can_be_initiated($latitude, $longitude, $expected)
    {
        $stub = new Proximity($latitude, $longitude);

        $this->assertSame($expected['latitude'], $stub->getLatitude());
        $this->assertSame($expected['longitude'], $stub->getLongitude());
        $this->assertSame($expected['sector'], $stub->getSector());

        $this->assertSame("{$expected['latitude']},{$expected['longitude']}", $stub->getCoordinate());
        $this->assertSame("geocoder.proximity.{$expected['sector']}", $stub->getCacheKey());
    }

    /**
     * @return array
     */
    public function proximityDataProvider()
    {
        return [
            [3.1339905, 101.6070148, ['latitude' => 6970.0, 'longitude' => 225626.0, 'sector' => '50:6970,225626']],
            [3.161907, 101.617954, ['latitude' => 7032.0, 'longitude' => 225644.0, 'sector' => '50:7032,225644']],
            [2.9258515, 101.6788381, ['latitude' => 6507.0, 'longitude' => 225829.0, 'sector' => '50:6507,225829']],
        ];
    }
}
