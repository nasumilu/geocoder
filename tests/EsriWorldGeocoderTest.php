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
use Nasumilu\Spatial\Geocoder\Geocode;
use Nasumilu\Spatial\Geocoder\EsriWorldGeocoder;

/**
 * Description of EsriWorldGeocoderTest
 */
class EsriWorldGeocoderTest extends TestCase
{
    
    private Geocode $geocoder;
    
    public function setUp(): void
    {
        $this->geocoder = new EsriWorldGeocoder();
    }
    
    /**
     * @test
     * @testWith [{ "address":"19720 NW 262 AVE", "city": "High Springs" }]
     * @param array $address
     */
    public function testGeocode(array $address) 
    {
        print_r($this->geocoder->geocode($address));
    }
    
}
