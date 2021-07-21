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

use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function array_filter;
use function rtrim;
use function floatval;

/**
 * Description of HereGeocoder
 */
class HereGeocoder extends HttpGetGeocoder
{

    public const BASE_URI = 'https://geocode.search.hereapi.com';
    private string $apiKey;

    public function __construct(string $apiKey, int $maxRedirects = 20)
    {
        $this->apiKey = $apiKey;
        parent::__construct(self::BASE_URI, $maxRedirects);
    }

    protected function configureOptions(OptionsResolver $optionsResolver): void
    {


        parent::configureOptions($optionsResolver);
        $optionsResolver->setDefault(self::PATH, 'v1/geocode')
                ->setDefault('api_key', $this->apiKey)
                ->setAllowedValues(self::FACTORY, fn($factory) => $factory->getSrid() === 4326);
    }

    protected function mapResponse(ResponseInterface $response): array
    {
        try {
            $data = $response->toArray();
            
            if(0 == count($data['items'])) {
                throw new NoCandidatesFoundException();
            }
            
            $candidates = [];
            
            foreach ($data['items'] as $candidate) {
                $candidates[] = [
                    self::ADDRESS => $candidate['title'],
                    self::SCORE => floatval($candidate['scoring']['queryScore']) * 100,
                    self::LOCATION => [
                        (float) $candidate['position']['lng'],
                        (float) $candidate['position']['lat']
                    ]
                ];
            }
        } catch (ClientException $ex) {
            throw new GeocoderException($ex);
        }
        return $candidates;
    }

    protected function query(array $options): array
    {
        $qq = '';
        $qq .= (isset($options[self::CITY])) ? "city={$options[self::CITY]};" : '';
        $qq .= (isset($options[self::NEIGHBORHOOD])) ? "district={$options[self::NEIGHBORHOOD]};" : '';
        $qq .= (isset($options[self::REGION])) ? "state={$options[self::REGION]};" : '';
        $qq .= (isset($options[self::COUNTRY])) ? "country={$options[self::COUNTRY]};" : '';
        $qq = rtrim($qq, ';');
        return array_filter(['q' => $options[self::ADDRESS], 'qq' => $qq, 'apikey' => $options['api_key']]);
    }

}
