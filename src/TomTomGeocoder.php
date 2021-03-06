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
use Exception;

use Symfony\Component\OptionsResolver\{
    OptionsResolver,
    Options
};
use function urlencode;
use function rtrim;
use function str_replace;

/**
 * Description of TomTomGeocoder
 */
class TomTomGeocoder extends HttpGetGeocoder
{

    public const BASE_URI = 'https://api.tomtom.com';

    private string $apiKey;

    public function __construct(string $apiKey, int $maxRedirects = 20)
    {
        $this->apiKey = $apiKey;
        parent::__construct(self::BASE_URI, $maxRedirects);
    }

    protected function mapResponse(ResponseInterface $response): array
    {
        try {
            $data = $response->toArray();
            if (0 == $data['summary']['totalResults']) {
                throw new NoCandidatesFoundException();
            }
            $candidates = [];

            foreach ($data['results'] as $candidate) {
                if ($candidate['type'] !== 'Point Address') {
                    continue;
                }
                $candidates[] = [
                    self::ADDRESS => $candidate[self::ADDRESS]['freeformAddress'],
                    self::SCORE => $candidate[self::SCORE],
                    self::LOCATION => [
                        (float) $candidate['position']['lon'],
                        (float) $candidate['position']['lat']
                    ]
                ];
            }
            return $candidates;
        } catch (Exception $ex) {
            throw new GeocoderException($ex);
        }
    }

    protected function configureOptions(OptionsResolver $optionsResolver): void
    {
        parent::configureOptions($optionsResolver);
        $optionsResolver->setDefault(self::PATH, 'search/2/geocode/{path}.json')
                ->setNormalizer(self::PATH, function(Options $options, string $value) {
                    $path = $options[self::ADDRESS] . ' ';
                    $path .= isset($options[self::NEIGHBORHOOD]) ? $options[self::NEIGHBORHOOD] . ' ' : '';
                    $path .= isset($options[self::CITY]) ? $options[self::CITY] . ' ' : '';
                    $path .= isset($options[self::REGION]) ? $options[self::REGION] . ' ' : '';
                    $path .= isset($options[self::COUNTRY]) ? $options[self::COUNTRY] . ' ' : '';

                    $path = urlencode(rtrim($path));
                    $count = 1;
                    return str_replace('{path}', $path, $value, $count);
                })
                ->setDefault('api_key', $this->apiKey)
                ->setAllowedValues(self::FACTORY, fn($factory) => $factory->getSrid() === 4326);
    }

    protected function query(array $options): array
    {
        return array_filter([
            'key' => $this->apiKey
        ]);
    }

}
