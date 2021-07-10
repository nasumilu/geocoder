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
use function array_merge;

/**
 * Description of HttpGeocoder
 */
abstract class HttpGetGeocoder extends AbstractGeocoder
{

    public const PATH = 'path';
    public const HEADERS = 'headers';
    public const AUTH = 'auth';
    public const QUERY = 'query';
    
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
                ->setAllowedTypes(self::PATH, 'string')
                ->setDefined(self::HEADERS)
                ->setAllowedTypes(self::HEADERS, 'array')
                ->setDefined(self::AUTH)
                ->setAllowedTypes(self::AUTH, 'array');
    }

    /**
     * Builds a query string from the configured options the HTTP service 
     * will recognize. Basically, maps the Geocode interface's generic options
     * to an array options which is used to construct a query string.
     * 
     * @param array $$options
     * @return array 
     */
    protected abstract function query(array $options): array;

    /**
     * Finds location candidates using an HttpClient.
     * 
     * @param array $options
     * @return array
     */
    protected function findCandidates(array $options): array
    {
        $httpOptions = array_filter(array_merge([
             self::QUERY => $this->query($options)],
             $options[self::HEADERS] ?? [],
             $options[self::AUTH] ?? []));
        $response = $this->client->request('GET', $options['path'], $httpOptions);
        return $response->toArray();
    }

}
