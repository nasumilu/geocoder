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

namespace Nasumilu\Spatial\Geocoder;

/**
 * The base interface for all gecoder(s).
 */
interface GeocoderInterface
{

    /**
     * Get an array of {@see AddressCandidate} for the `address` argument
     *
     * @param string $address The address to geocode
     * @return AddressCandidate[]  an array of possible address candidates
     * @throws GeocoderException when an unexpected issue occurs while geocoding the address
     */
    public function geocode(string $address): array;

}