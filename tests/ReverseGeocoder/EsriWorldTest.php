<?php

namespace Nasumilu\Spatial\Geocoder\Tests\ReverseGeocoder;

use Nasumilu\Spatial\Geocoder\ReverseGeocoder\EsriWorld;
use PHPUnit\Framework\TestCase;

class EsriWorldTest extends TestCase
{

    public function testReverseGeocode(): void {
        $expected = '4600 Silver Hill Road, Suitland, Maryland, 20746';
        $location = [-76.93067442482834,38.84717175217679];
        $reverseGeocoder = new EsriWorld();
        $address = $reverseGeocoder->reverseGeocode($location);
        $this->assertNotNull($address);
        $this->assertEquals($expected, $address->getAddress());
    }

}