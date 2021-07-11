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

use Symfony\Component\HttpClient\Exception\JsonException;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use function in_array;
use function array_filter;
use function array_merge;

/**
 * TAMUGeocoder utilizes Texas A&M Geoservices to geocode address candidates. 
 * The service provide geocoding for US addresses <strong>ONLY</strong> and does
 * not support the <kbd>neighborhood</kbd> option. Specific, options for this 
 * Geocoder are the following:
 * <ul>
 *  <li>
 *      census_year - possible values are 1990, 2000, 2010, or for all three use
 *          1990|2000|2010. The default operation is to use all three.
 *  </li>
 *  <li>
 *      allow_ties - a flag to indicate to return ties as valid results or handle
 *          using the <code>tie_breaking_strategy</code> option. (default: true)
 *  </li>
 *  <li>
 *      tie_breaking_strategy - if <code>allow_ties</code> options is false, this
 *          determines how to break the ties. Texas A&M provides two tie 
 *          breaking strategies, <kbd>flipACoin</kbd> & <kbd>revertToHierarchy</kbd>.
 *          The <kbd>flipACoin</kbd> is just that randomly select one of the 
 *          candidate locations. The <kbd>revertToHierarchy</kbd> fails on a tie
 *          and match to the next level of the geographic hierarchy. (default: 
 *          revertToHierarchy; when allow_ties is false)
 *  </li>
 * </ul>
 *
 * <strong>IMPORTANT: </strong> The factory option's spatial reference system id 
 * (SRID) <strong>MUST</strong> be 4326. The service does not support other 
 * coordinate systems.
 *  
 * 
 * @link https://geoservices.tamu.edu/Services/Geocode/WebService/GeocoderWebServiceDetailed.aspx Texas A&M Geoservices Geocoding APIs 
 */
class TAMUGeocoder extends HttpGetGeocoder
{
    // Option keys

    /** API key options key */
    public const API_KEY = 'api_key';

    /** Census census year option key */
    public const CENSUS_YEAR = 'census_year';

    /** Version option key */
    public const VERSION = 'version';

    /** Allow ties option key */
    public const ALLOW_TIES = 'allow_ties';

    /** Tie breaking strategy option key */
    public const TIE_BREAKING_STRATEGY = 'tie_breaking_strategy';

    // Option values

    /** Flip a coin tie breaking strategy option value */
    public const FLIP_A_COIN = 'flipACoin';

    /** Revert to hierarchy tie braking strategy option value */
    public const REVERT_TO_HIERARCHY = 'revertToHierarchy';

    /** Census year 1900 option value */
    public const CENSUS_1990 = '1990';

    /** Census year 2000 option value */
    public const CENSUS_2000 = '2000';

    /** Census year 2010 option value */
    public const CENSUS_2010 = '2010';

    /** Possible Census year options */
    public const CENSUS_YEARS = [
        self::CENSUS_1990,
        self::CENSUS_2000,
        self::CENSUS_2010
    ];

    /** TAMU Geocoding Service API version */
    public const API_VERSION = '4.01';

    /**
     * The Texas A&M Gecoding service base uri
     */
    public const BASE_URI = 'https://geoservices.tamu.edu';

    /**
     * @var string the Texas A&M Geocoding API key
     */
    private string $apiKey;

    /**
     * Constructs a TAMUGeocoder with an API key and maximum number of redirects.
     * 
     * The API key value is used as the default, if you need to utilize another 
     * API when geocoding address candidates use option TAMUGeocoder::API_KEY.
     * 
     * @param string $apiKey the default TAMU API key
     * @param int $maxRedirects the maximum number of redirects allowed
     */
    public function __construct(string $apiKey, int $maxRedirects = 20)
    {
        $this->apiKey = $apiKey;
        parent::__construct(self::BASE_URI, $maxRedirects);
    }

    /**
     * {@inheritDoc}
     */
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
                ->setDefault(self::ALLOW_TIES, true)
                ->setAllowedTypes(self::ALLOW_TIES, 'bool')
                ->setDefault(self::TIE_BREAKING_STRATEGY, self::REVERT_TO_HIERARCHY)
                ->setAllowedTypes(self::TIE_BREAKING_STRATEGY, 'string')
                ->setAllowedValues(self::TIE_BREAKING_STRATEGY,
                        fn($value) => in_array($value, [self::FLIP_A_COIN, self::REVERT_TO_HIERARCHY]))
                ->setAllowedValues(self::FACTORY, fn($factory) => $factory->srid() === 4326);
    }

    /**
     * {@inheritDoc}
     */
    protected function query(array $options): array
    {
        return array_merge(array_filter([
            self::VERSION => self::API_VERSION,
            'city' => $options[self::CITY] ?? null,
            'state' => $options[self::REGION] ?? null,
            'zip' => $options[self::POSTAL_CODE] ?? null,
            'allowTies' => $options[self::ALLOW_TIES] ? 'true' : 'false',
            'tieBreakingStrategy' => $options[self::ALLOW_TIES] ? null : $options[self::TIE_BREAKING_STRATEGY],
            'censusYear' => $options[self::CENSUS_YEAR]]), [
            'streetAddress' => $options[self::ADDRESS],
            'apiKey' => $options[self::API_KEY],
            'format' => 'json']);
    }

    protected function mapResponse(ResponseInterface $response): array
    {
        try {
            $candidates = $response->toArray();
            if ($candidates['FeatureMatchingResultCount'] == 0) {
                throw new NoCandidatesFoundException();
            }

            $value = [];
            foreach ($candidates['OutputGeocodes'] as $candidate) {
                $value[] = [
                    self::ADDRESS => $candidates['InputAddress']['StreetAddress'],
                    self::SCORE => $candidate['OutputGeocode']['MatchScore'],
                    self::LOCATION => [(float) $candidate['OutputGeocode']['Longitude'],
                        (float) $candidate['OutputGeocode']['Latitude']]
                ];
            }
            return $value;
        } catch (JsonException $jex) {
            throw new GeocoderException($jex);
        }
    }

}
