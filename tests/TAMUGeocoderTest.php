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
use Nasumilu\Spatial\Geocoder\Geocoder;
use Nasumilu\Spatial\Geocoder\TAMUGeocoder;
use Nasumilu\Spatial\Geometry\{
    GeometryFactory,
    AbstractGeometryFactory
};

/**
 * Description of EsriWorldGeocoderTest
 */
class TAMUGeocoderTest extends TestCase
{

    private Geocoder $geocoder;
    private GeometryFactory $factory;

    public function setUp(): void
    {
        $this->geocoder = new TAMUGeocoder('demo');
        $this->factory = $this->getMockForAbstractClass(AbstractGeometryFactory::class, [['srid' => 4326]]);
    }

    /**
     * @test
     * @testWith [{ "address": "19720 NW 262 AVE", "city": "High Springs", "region": "FL", "postal_code":"32643" }]
     *           [{ "address": "123 Main Street", "city": "Los Angeles", "region": "CA", "postal_code": 90007 }]
     * @param array $address
     */
    public function testGeocode(array $address)
    {
        $options = array_merge($address, ['factory' => $this->factory]);
        print_r($this->geocoder->geocode($options));
    }

}
