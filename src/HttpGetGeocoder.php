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

namespace Nasumilu\Spatial\Geocoder;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function array_filter;

/**
 * Description of HttpGeocoder
 */
abstract class HttpGetGeocoder extends AbstractGeocoder
{

    public const PATH = 'path';
    
    private HttpClientInterface $client;

    public function __construct(string $baseUri, int $maxRedirects = 20)
    {
        parent::__construct();
        $this->client = HttpClient::create([
                    'base_uri' => $baseUri,
                    'max_redirects' => $maxRedirects
        ]);
    }

    /**
     * Besides the required options found in {@see AbstractGeoder} this 
     * configures the RESTful endpoint for an HTTP get geocode services. 
     * The value defaults to an empty string so if the constructor's base uri
     * provides the full-qualified path to the endpoint the options is not 
     * needed.
     * 
     * @param OptionsResolver $optionsResolver
     */
    protected function configureOptions(OptionsResolver $optionsResolver): void
    {
        parent::configureOptions($optionsResolver);
        $optionsResolver->setDefault(self::PATH, '')
                ->setAllowedTypes(self::PATH, 'string');
    }

    /**
     * Builds a query string from the configured options which this service 
     * will recognize. 
     */
    protected abstract function query(array $options): array;

    /**
     * Builds the http request headers
     * @link https://symfony.com/doc/current/http_client.html#headers
     * @return array
     */
    protected function headers(): array
    {
        return [
            'User-Agent' => 'nasumilu/geocoder'
        ];
    }

    /**
     * Builds the http authentication needed for the request
     * @link https://symfony.com/doc/current/http_client.html#authentication
     * @return array
     */
    protected function authentication(): array
    {
        return [];
    }

    protected function findCandidates(array $options): array
    {
        $httpOptions = array_filter([
            'query' => $this->query($options),
            'headers' => $this->headers(),
            $this->authentication()
        ]);
        $response = $this->client->request('GET', $options['path'], $httpOptions);
        return $response->toArray();
    }

}
