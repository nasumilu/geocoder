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
    
    private OptionsResolver $optionsResolver;
    private HttpClientInterface $client;
    private string $method;
    
    public function __construct(string $baseUri, string $method = 'GET')
    {
        $this->client = HttpClient::createForBaseUri($baseUri);
        $this->method = $method;
        $this->optionsResolver = new OptionsResolver();
        $this->configureOptions($this->optionsResolver);
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
    
    protected function configureOptions(OptionsResolver $optionsResolver) {
        $optionsResolver->setDefault('query', 
                fn(OptionsResolver $resolver) => $this->configureQueryOptions($resolver))
                ->setAllowedTypes('query', 'array');
        $optionsResolver->setDefault('header', 
                fn(OptionsResolver $resolver) => $this->configureHeaderOptions($resolver));
    }
    
    protected function configureQueryOptions(OptionsResolver $optionsResolver) { }
    
    protected function configureHeaderOptions(OptionsResolver $optionsReolver) { }


    public function geocode(array $address): array
    {
        $options = $this->optionsResolver->resolve($address);
        $query = $this->optionsResolver->resolve($address);
        $response = $this->client->request($this->method, ['query' => $query]);
        return $response->toArray();
    }
    
}
