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

use function array_filter;

/**
 * Description of AbstractGeocoder
 */
abstract class AbstractGeocoder implements Geocode
{
    
    private OptionsResolver $optionsResolver;
    
    public function __construct()
    {
        $this->optionsResolver = new OptionsResolver();
        $this->configureOptions($this->optionsResolver);
    }
    
    protected function configureOptions(OptionsResolver $optionsResolver): void {
        // address options is required
        $optionsResolver->setRequired(self::ADDRESS)
                ->addAllowedTypes(self::ADDRESS, 'string');
        // optional city value
        $optionsResolver->setDefined(self::CITY)
                ->setAllowedTypes(self::CITY, 'string');
        // optional region
        $optionsResolver->setDefined(self::REIGION)
                ->setAllowedTypes(self::REIGION, 'string');
        // optional postal code
        $optionsResolver->setDefined(self::POSTAL_CODE)
                ->setAllowedTypes(self::POSTAL_CODE, ['string', 'int']);
        // optional country
        $optionsResolver->setDefined(self::COUNTRY)
                ->setAllowedTypes(self::COUNTRY, 'string');
    }
    
    /**
     * Must return an array of candidate locations based upon the provided 
     * options. Data returned <strong>MUST</strong> adhere to following format:
     * <ul>
     *  <li>    
     *      ['score'] - a numeric value where 0 is the lowest possible match and
     *      100 is the highest possible match. The results may have duplicate
     *      match values.
     *  </li>   
     *  <li>
     *      ['location'] - an ordered pair (x,y) numerically index. This is 
     *      (longitude, latitude), where a point's longitude is its <em>x</em> 
     *      value and the latitude is its <em>y</em> value. The location 
     *  `   <strong>MUST</strong> use the WGS84 (EPSG:4326) coordinate system.
     *  </li>
     * </ul>
     */
    protected abstract function findCandidates(array $options) : array;

        
    public function geocode(array $options, callable $filter = null): array
    {
        $candidates = $this->findCandidates($this->optionsResolver->resolve($options));
        
        if($filter) {
            return array_filter($candidates, $filter);
        }
        return $candidates;
    }
    
}
