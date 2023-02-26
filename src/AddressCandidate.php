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

use JetBrains\PhpStorm\ArrayShape;
use JsonSerializable;

/**
 * A basic DTO which represents an address candidate.
 */
class AddressCandidate implements JsonSerializable
{

    public function __construct(private readonly string $address,
                                private readonly array $location,
                                private readonly int|float|null $score)
    { }

    /**
     * The matched address value
     * @return string
     */
    public function getAddress(): string
    {
        return $this->address;
    }

    /**
     * The location of the address candidate
     * @return float[]
     */
    public function getLocation(): array
    {
        return $this->location;
    }

    /**
     * The ranking score
     * @return int|float|null
     */
    public function getScore(): int|float|null
    {
        return $this->score;
    }

    /**
     * @internal
     * @return array
     */
    #[ArrayShape(['type' => "string", 'coordinates' => "array", 'properties' => "array"])]
    public function jsonSerialize(): array
    {
        return [
            'type' => 'Point',
            'coordinates' => $this->location,
            'properties' => [
                'address' => $this->address,
                'score' => $this->score
            ]
        ];
    }
}