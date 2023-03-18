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

use Nasumilu\Spatial\AddressCandidate;

class Here extends AbstractGeocoder
{

    private const BASE_URI = 'https://geocode.search.hereapi.com/v1/geocode';

    public function __construct(private readonly string $apiKey)
    {
        parent::__construct(self::BASE_URI);
    }

    protected function mapCandidates(array $candidates): array
    {
        return array_map(
            fn(array $value): AddressCandidate => new AddressCandidate(
                $value['address']['label'],
                [$value['position']['lat'], $value['position']['lng']],
                $value['scoring']['queryScore'] * 100
            ),
            $candidates['items']
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function query(string $address): array
    {
        return [
            'q' => $address,
            'apiKey' => $this->apiKey
        ];
    }
}