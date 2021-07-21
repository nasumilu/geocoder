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
use Symfony\Component\OptionsResolver\OptionsResolver;

use function array_filter;
use function array_map;

/**
 * Description of EsriWorldGeocoder
 */
class EsriWorldGeocoder extends HttpGetGeocoder
{
    
    public const BASE_URI = 'https://geocode.arcgis.com';

    public function __construct(int $maxRedirects = 20)
    {
        parent::__construct(self::BASE_URI, $maxRedirects);
    }

    /**
     * {@inheritDoc}
     */
    protected function configureOptions(OptionsResolver $optionsResolver): void
    {
        parent::configureOptions($optionsResolver);
        $optionsResolver->setDefault(self::PATH, 
                'arcgis/rest/services/World/GeocodeServer/findAddressCandidates');
    }

    /**
     * {@inheritDoc}
     */
    protected function query(array $options): array
    {
        return array_filter([
            'f' => 'json',
            self::ADDRESS => $options[self::ADDRESS],
            self::NEIGHBORHOOD => $options[self::NEIGHBORHOOD] ?? null,
            self::CITY => $options[self::CITY] ?? null,
            self::REGION => $options[self::REGION] ?? null,
            'postal' => $options[self::POSTAL_CODE] ?? null,
            'countryCode' => $options[self::COUNTRY] ?? null,
            'outSR' => $options[self::FACTORY]->getSrid()
        ]);
    }
    
    protected function mapResponse(ResponseInterface $response): array
    {
        $data = $response->toArray();
        if(0 == count($data['candidates'])) {
            throw new NoCandidatesFoundException();
        }
        return array_map(function($candidate) {
            return [
                self::ADDRESS => $candidate[self::ADDRESS],
                self::SCORE => $candidate[self::SCORE],
                self::LOCATION => [(float) $candidate[self::LOCATION]['x'],
                    (float) $candidate[self::LOCATION]['y']]
            ];
        }, $data['candidates']);
    }

}
