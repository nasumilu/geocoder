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
use Exception;

/**
 * Description of GoogleGeocoder
 */
class GoogleGeocoder extends HttpGetGeocoder
{

    public const BASE_URI = 'https://maps.googleapis.com';
    public const ROOFTOP = 100;
    public const RANGE_INTERPOLATED = 80;
    public const GEOMETRIC_CENTER = 60;
    public const APPROXIMATE = 50;

    private string $apiKey;

    public function __construct(string $apiKey, int $maxRedirects = 20)
    {
        $this->apiKey = $apiKey;
        parent::__construct(self::BASE_URI, $maxRedirects);
    }

    protected function configureOptions(OptionsResolver $optionsResolver): void
    {
        parent::configureOptions($optionsResolver);
        $optionsResolver->setDefault(self::PATH, 'maps/api/geocode/json')
                ->setDefault('api_key', $this->apiKey);
    }

    protected function mapResponse(ResponseInterface $response): array
    {
        try {
            $data = $response->toArray();
            if ('ZERO_RESULTS' == $data['status']) {
                throw new NoCandidatesFoundException();
            }
            if ('OK' !== $data['status']) {
                throw new GeocoderException();
            }

            $candidates = [];
            foreach ($data['results'] as $candidate) {
                $candidates[] = [
                    self::ADDRESS => $candidate['formatted_address'],
                    self::SCORE => constant(GoogleGeocoder::class . "::{$candidate['geometry']['location_type']}"),
                    self::LOCATION => [
                        (float) $candidate['geometry']['location']['lng'],
                        (float) $candidate['geometry']['location']['lat']
                    ]
                ];
            }
            return $candidates;
        } catch (Exception $ex) {
            throw new GeocoderException($ex);
        }
    }

    protected function query(array $options): array
    {
        $address = $options[self::ADDRESS];
        $address .= isset($options[self::NEIGHBORHOOD]) ? " {$options[self::NEIGHBORHOOD]}," : '';
        $address .= isset($options[self::CITY]) ? " {$options[self::CITY]}," : '';
        $address .= isset($options[self::REGION]) ? " {$options[self::REGION]}," : '';
        $components = '';
        $components .= isset($options[self::COUNTRY]) ? "country:{$options[self::COUNTRY]}|" : '';
        $components .= isset($options[self::POSTAL_CODE]) ? "postal_code:{$options[self::POSTAL_CODE]}" : '';
        $components = rtrim($components, "|");
        return array_filter(['address' => $address, 'components' => $components, 'key' => $options['api_key']]);
    }

}
