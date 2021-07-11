<?php

declare(strict_types=1);

/*
 * Copyright 2021 mlucas.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Nasumilu\Spatia\Geocoder\Tests;

use PHPUnit\Framework\TestCase;
use Nasumilu\Spatial\Geocoder\{
    Geocoder,
    NoCandidatesFoundException,
    GeocoderException
};
use Nasumilu\Spatial\Geometry\{
    GeometryFactory,
    AbstractGeometryFactory,
    Point
};

use function get_class;
use function count;
use function array_merge;

/**
 * Description of AbstractGeocoderTest
 */
abstract class AbstractGeocoderTest extends TestCase
{

    protected Geocoder $geocoder;
    protected GeometryFactory $factory;

    public function setUp(): void
    {
        $this->geocoder = $this->initGeocoder();
        $this->factory = $this->initGeometryFactory();
    }

    protected abstract function initGeocoder(): Geocoder;

    protected function initGeometryFactory(): GeometryFactory
    {
        return $this->getMockForAbstractClass(AbstractGeometryFactory::class, [['srid' => 4326]]);
    }

    /**
     * @test
     * @testWith [{ "address":"1375 E Buena Vista Dr", "city": "Orlando", "region": "FL", "country": "USA" }]
     *           [{ "address": "Av. Victoria 110", "neighborhood":"Centro", "city": "Nazas", "region": "Durango", "country": "Mexico" }]
     *           [{ "address": "3403 8a St SW", "city": "Calgary", "region": "Alberta", "country": "Canada", "postal_code": "T2T 3B3"}]
     * @param array $address
     */
    public function testGeocodeSuccessful(array $address)
    {
        $options = array_merge($address, ['factory' => $this->factory]);
        $addresses = $this->geocoder->geocode($options);
        $count = count($addresses);
        $this->assertTrue($count > 0);
        printf("Found %d candidates with %s!\n\n", $count, get_class($this->geocoder));
        $this->assertIsArray($addresses);
        foreach ($addresses as $value) {
            $this->assertArrayHasKey('address', $value);
            $this->assertArrayHasKey('score', $value);
            $this->assertArrayHasKey('location', $value);
            $this->assertInstanceOf(Point::class, $value['location']);
            printf("%s found %s scoring %d at location (%f,%f)\n", 
                    get_class($this->geocoder), $value['address'], 
                    $value['score'], $value['location']['x'], 
                    $value['location']['y']);
        }
    }

    /**
     * @test
     * @testWith [{ "address":"" }]
     * @param array $address
     */
    public function testGeocodeFail(array $address)
    {
        $options = array_merge($address, ['factory' => $this->factory]);
        $this->expectException(GeocoderException::class);
        $addresses = $this->geocoder->geocode($options);
    }

}
