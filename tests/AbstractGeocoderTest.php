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
    NoCandidatesFoundException
};
use Nasumilu\Spatial\Geometry\{
    GeometryFactory,
    AbstractGeometryFactory,
    Point
};

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
     * @testWith [{ "address":"1375 E Buena Vista Dr", "city": "Orlando", "region": "FL" }]
     * @param array $address
     */
    public function testGeocodeSuccessful(array $address)
    {
        $options = array_merge($address, ['factory' => $this->factory]);
        $addresses = $this->geocoder->geocode($options);
        $this->assertIsArray($addresses);
        foreach ($addresses as $value) {
            $this->assertArrayHasKey('score', $value);
            $this->assertArrayHasKey('location', $value);
            $this->assertInstanceOf(Point::class, $value['location']);
        }
        
        print_r($addresses[0]);
    }

    /**
     * @test
     * @testWith [{ "address":"" }]
     * @param array $address
     */
    public function testGeocodeFail(array $address)
    {
        $options = array_merge($address, ['factory' => $this->factory]);
        $this->expectException(NoCandidatesFoundException::class);
        $addresses = $this->geocoder->geocode($options);
    }

}
