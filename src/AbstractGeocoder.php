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

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Represents a {@see Geocoder} service where the {@see AddressCandidate} are obtained using a service endpoint.
 */
abstract class AbstractGeocoder implements Geocoder
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
     * Responsible for mapping the response results to an array of {@see AddressCandidate} objects.
     *
     * @param array $candidates
     * @return array
     */
    protected abstract function mapCandidates(array $candidates): array;

    /**
     * Responsible for creating the necessary query string used to retrieve the address candidates from the service.
     * @param string $address
     * @return array
     */
    protected abstract function query(string $address): array;

    /**
     * {@inheritDoc}
     */
    public function geocode(string $address): array
    {
        try {
            $query = $this->query($address);
            $response = $this->client->request('GET', '', [
                'query' => $query
            ]);
            $candidates = $response->toArray();
            if (0 === count($candidates)) {
                throw new NoCandidatesFoundException();
            }
            return $this->mapCandidates($candidates);
        } catch(TransportExceptionInterface | ServerExceptionInterface | RedirectionExceptionInterface | DecodingExceptionInterface | ClientExceptionInterface $ex) {
            throw new GeocoderException($ex);
        }
    }
}