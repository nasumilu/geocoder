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
namespace Nasumilu\Spatial\ReverseGeocoder;

use Symfony\Component\Translation\LocaleSwitcher;

class Here extends AbstractReverseGeocoder
{

    private const BASE_URI = 'https://revgeocode.search.hereapi.com/v1/revgeocode';

    public function __construct(private readonly string $apiKey, private readonly LocaleSwitcher $localeSwitcher)
    {
        parent::__construct(self::BASE_URI);
    }

    protected function mapCandidates(array $candidates): string|null
    {
        return $candidates['items'][0]['address']['label'] ?? null;
    }

    protected function query(float|int|string $x, float|int|string $y): array
    {
        return [
            'at' => "$y,$x",
            'lang' => $this->localeSwitcher->getLocale(),
            'apiKey' => $this->apiKey
        ];
    }

}