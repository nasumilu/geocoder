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

/**
 * Geocode interface defines a standard set of options which is used by all 
 * implementations. 
 */
interface Geocoder
{
    // input options keys

    /** Address option key */
    public const ADDRESS = 'address';

    /** Neighborhood option key */
    public const NEIGHBORHOOD = 'neighborhood';

    /** City option key */
    public const CITY = 'city';

    /** Region option key */
    public const REGION = 'region';

    /** Postal code option key */
    public const POSTAL_CODE = 'postal_code';

    /** Country option key */
    public const COUNTRY = 'country';

    /** GeometryFactory options key */
    public const FACTORY = 'factory';

    // output options keys

    /** Location candidate score key */
    public const SCORE = 'score';

    /** Location candidate key */
    public const LOCATION = 'location';

    /**
     * The basic options which each implementation <strong>MUST</strong> adhere 
     * to are:
     * 
     * <ul>
     *  <li>
     *      ['factory'] - the required GeometryFactory to construct build the 
     *          locations as Point objects.
     *  </li>
     *  <li>
     *      ['address'] - the required street address which may include the house number 
     *          and street name. (e.g. 123 N Main Street)
     *  </li>
     *  <li>
     *      ['neighborhood' - the smallest administrative are for which the address
     *          is located. Mostly used with address outside fo the US and may 
     *          not be supported by all gecoders.
     *  </li>
     *  <li>
     *      ['city'] - the city or municipality for which the address is 
     *          located. (e.g. Buffalo)
     *  </li>
     *  <li>
     *      ['region'] - the next largest administrative are for which the 
     *          address is located. (e.g. New York)
     *  </li>
     *  <li>
     *      ['postal_code'] - a standard postal code for the address. Typically,
     *          3 - 6 alphanumeric characters. (e.g 14202) This value <strong>MUST 
     *          NOT</strong> be an extended postal code.
     *  </li>
     *  <li>
     *      ['country'] - the country for which the address is located. 
     *          Implementing classes must use the <strong>ISO 3166-1 alpha-3</strong>
     *          value for this options. (e.g. USA)
     *  </li>
     * </ul>
     *  
     * @param array $options
     * @return array
     */
    public function geocode(array $options): array;
}
