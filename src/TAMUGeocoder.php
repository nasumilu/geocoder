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

use function in_array;
use function array_filter;

/**
 * Description of TAMUGeocoder
 */
class TAMUGeocoder extends HttpGetGeocoder
{
    
    public const API_KEY = 'api_key';
    public const CENSUS_YEAR = 'census_year';
    public const VERSION = 'version';
    public const ALLOW_TIES = 'allow_ties';
    public const TIE_BREAKING_STRATEGY = 'tie_breaking_strategy';
    
    public const CENSUS_1990 = '1990';
    public const CENSUS_2000 = '2000';
    public const CENSUS_2010 = '2010';
     
    public const FLIP_A_COIN = 'flipACoin';
    public const REVERT_TO_HIERARCHY = 'revertToHierarchy';
    
    public const CENSUS_YEARS = [
        self::CENSUS_1990,
        self::CENSUS_2000,
        self::CENSUS_2010
    ];
    public const API_VERSION = '4.01';
    
    
    public const BASE_URI = 'https://geoservices.tamu.edu';
    
    private string $apiKey;
    
    public function __construct(string $apiKey, int $maxRedirects = 20)
    {
        $this->apiKey = $apiKey;
        parent::__construct(self::BASE_URI, $maxRedirects);
    }
    
    protected function configureOptions(OptionsResolver $optionsResolver): void
    {
        parent::configureOptions($optionsResolver);
        $optionsResolver->setRequired([self::API_KEY, self::CENSUS_YEAR, self::VERSION, self::ALLOW_TIES])
                ->setDefault(self::PATH, 
                'Services/Geocode/WebService/GeocoderWebServiceHttpNonParsed_V04_01.aspx')
                ->setDefault(self::API_KEY, $this->apiKey)
                ->setAllowedTypes(self::API_KEY, 'string')
                ->setDefault(self::VERSION, self::API_VERSION)
                ->setDefault(self::CENSUS_YEAR, implode('|', self::CENSUS_YEARS))
                ->setAllowedTypes(self::CENSUS_YEAR, ['string', 'int'])
                ->setDefault(self::ALLOW_TIES, false)
                ->setAllowedTypes(self::ALLOW_TIES, 'bool')
                ->setDefault(self::TIE_BREAKING_STRATEGY, self::FLIP_A_COIN)
                ->setAllowedTypes(self::TIE_BREAKING_STRATEGY, 'string')
                ->setAllowedValues(self::TIE_BREAKING_STRATEGY, 
                        fn ($value) => in_array($value, [self::FLIP_A_COIN, self::REVERT_TO_HIERARCHY]));
    }


    protected function query(array $options): array
    {
        return array_filter([
            'apiKey' => $options[self::API_KEY],
            self::VERSION => self::API_VERSION,
            'streetAddress' => $options[self::ADDRESS],
            'city' => $options[self::CITY] ?? null,
            'state' => $options[self::REIGION] ?? null,
            'zip' => $options[self::POSTAL_CODE] ?? null,
            'allowTies' => $options[self::ALLOW_TIES] ?? false,
            'tieBreakingStrategy' => $options[self::TIE_BREAKING_STRATEGY] ?? false,
            'censusYear' => $options[self::CENSUS_YEAR],
            'format' => 'json'
        ]);
    }
    
    protected function findCandidates(array $options): array
    {
        $response = parent::findCandidates($options);
        $candidates = [];
        if((int) $response['FeatureMatchingResultCount'] > 0) {
            foreach($response['OutputGeocodes'] as $candidate) {
                $candidates[] = [
                    self::SCORE => $candidate['OutputGeocode']['MatchScore'],
                    self::LOCATION => [$candidate['OutputGeocode']['Longitude'],
                        $candidate['OutputGeocode']['Latitude']]
                ];
            }
        }
        return $candidates;
        
    }

}
