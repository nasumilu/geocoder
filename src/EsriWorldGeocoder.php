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
 * Geocoder service provided by Esri
 *
 * @link https://developers.arcgis.com/rest/geocode/api-reference/geocoding-find-address-candidates.htm Esri Geocoding
 */
class EsriWorldGeocoder extends AbstractGeocoderInterface
{

    /**
     * The service endpoint
     */
    private const BASE_URI = 'https://geocode.arcgis.com/arcgis/rest/services/World/GeocodeServer/findAddressCandidates';

    public function __construct()
    {
        parent::__construct(self::BASE_URI);
    }

    /**
     * {@inheritDoc}
     */
    protected function mapCandidates(array $candidates): array
    {
        return array_map(
            fn(array $value): AddressCandidate => new AddressCandidate($value['address'], [$value['location']['x'], $value['location']['y']], $value['score']),
            $candidates['candidates']
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function query(string $address): array
    {
        return [
            'f' => 'json',
            'SingleLine' => $address
        ];
    }
}