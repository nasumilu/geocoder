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

/**
 * Description of HereGeocoder
 */
class HereGeocoder extends HttpGetGeocoder
{

    private $apiKey;

    public function __construct(string $apiKey, int $maxRedirects = 20)
    {
        $this->apiKey = $apiKey;
        parent::__construct('https://geocode.search.hereapi.com', $maxRedirects);
    }

    protected function configureOptions(OptionsResolver $optionsResolver): void
    {


        parent::configureOptions($optionsResolver);
        $optionsResolver->setDefault(self::PATH, 'v1/geocode')
                ->setDefault('api_key', $this->apiKey)
                ->setAllowedValues(self::FACTORY, fn($factory) => $factory->srid() === 4326);
    }

    protected function mapResponse(ResponseInterface $response): array
    {
        try {
            $data = $response->toArray();
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
            throw new NoCandidatesFoundException($ex);
        }
        return $candidates;
    }

    protected function query(array $options): array
    {
        $q = $options[self::ADDRESS];
        $q .= (isset($options[self::CITY])) ? ' ' . $options[self::CITY] : '';

        $q .= (isset($options[self::REGION])) ? ' ' . $options[self::REGION] : '';
        $q .= (isset($options[self::COUNTRY])) ? ' ' . $options[self::COUNTRY] : '';
        return ['q' => $q, 'apikey' => $options['api_key']];
    }

}
