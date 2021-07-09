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

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpClient\HttpClient;
/**
 * Description of AbstractGeocoder
 */
abstract class AbstractGeocoder implements Geocode
{
    
    private OptionsResolver $queryOptionsResolver;
    private HttpClientInterface $client;
    private string $method;
    
    public function __construct(string $baseUri, string $method = 'GET')
    {
        $this->client = HttpClient::createForBaseUri($baseUri);
        $this->method = $method;
        $this->queryOptionsResolver = new OptionsResolver();
        $this->configureQueryString($this->queryOptionsResolver);
    }
    
    public function getMethod(): string 
    {
        return $this->method;
    }
    
    public function setMethod(string $method = 'GET'): self
    {
        $this->method = $method;
        return $this;
    }
    
    protected abstract function configureQueryString(OptionsResolver $optionsResolver);
    
    public function geocode(array $address): array
    {
        $options = $this->queryOptionsResolver->resolve($address);
        $query = $this->queryOptionsResolver->resolve($address);
        
        $response = $this->client->request($this->method, $this->baseUri, ['query' => $query]);
        return $response->toArray();
    }
    
}
