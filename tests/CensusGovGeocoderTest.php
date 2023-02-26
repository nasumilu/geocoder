<?php
/**
 *    Copyright 2023 Michael Lucas
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0

 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
namespace Nasumilu\Spatial\Geocoder\Tests;

use Nasumilu\Spatial\Geocoder\CensusGovGeocoder;
use PHPUnit\Framework\TestCase;

class CensusGovGeocoderTest extends TestCase
{

    public function testGeocoder(): void {
        $expected = '4600 Silver Hill Rd, Washington, DC, 20233';
        $gecoder = new CensusGovGeocoder();
        $candidates = $gecoder->geocode($expected);
        $this->assertIsArray($candidates);
        $this->assertCount(1, $candidates);
        $this->assertEquals(strtoupper($expected), $candidates[0]->getAddress());
    }

}