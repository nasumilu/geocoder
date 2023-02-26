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

use Nasumilu\Spatial\AddressCandidate;
use Nasumilu\Spatial\GeocoderException;
use Nasumilu\Spatial\ReverseGeocoderInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

abstract class AbstractReverseGeocoder implements ReverseGeocoderInterface
{

    /**
     * The http client used to retrieve the address candidates
     * @var HttpClientInterface
     */
    protected HttpClientInterface $client;

    /**
     * @param string $baseUri
     */
    public function __construct(string $baseUri)
    {
        $this->client = HttpClient::createForBaseUri($baseUri);
    }

    /**
     * Responsible for mapping the response results to a string (the address).
     *
     * @param array $candidates
     * @return string|null
     */
    protected abstract function mapCandidates(array $candidates): string|null;

    /**
     * Responsible for creating the necessary query string used to retrieve the address candidates from the service.
     * @param string|float|int $x
     * @param string|float|int $y
     * @return array
     */
    protected abstract function query(string|float|int $x, string|float|int $y): array;

    /**
     * {@inheritDoc}
     */
    public function reverseGeocode(array $location): AddressCandidate|null
    {
        // x-coordinate must exist
        if (null === $x = ($location[0] ?? $location['x'] ?? null)) {
            throw new GeocoderException('Expected the longitude, none found!');
        }

        // y-coordinate must exist
        if (null === $y = ($location[1] ?? $location['y'] ?? null)) {
            throw new GeocoderException('Expected the latitude, none found!');
        }

        // x & y-coordinates must be numeric
        if (!is_numeric($x) && !is_numeric($y)) {
            throw new GeocoderException('Expected the longitude and latitude to be numeric!');
        }

        try {
            $query = $this->query($x, $y);
            $response = $this->client->request('GET', '', [
                'query' => $query
            ]);
            if (null !== $address = $this->mapCandidates($response->toArray())) {
                $address = new AddressCandidate($address, [$x, $y], null);
            }
            return $address;
        } catch(TransportExceptionInterface | ServerExceptionInterface | RedirectionExceptionInterface | DecodingExceptionInterface | ClientExceptionInterface $ex) {
            throw new GeocoderException(previous: $ex);
        }

    }
}