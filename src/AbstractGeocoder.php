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
use Nasumilu\Spatial\Geometry\GeometryFactory;

use function array_filter;

/**
 * Description of AbstractGeocoder
 */
abstract class AbstractGeocoder implements Geocoder
{

    private OptionsResolver $optionsResolver;

    public function __construct()
    {
        $this->optionsResolver = new OptionsResolver();
        $this->configureOptions($this->optionsResolver);
    }

    protected function configureOptions(OptionsResolver $optionsResolver): void
    {
        // factory option is required
        $optionsResolver->setRequired(self::FACTORY)
                ->setAllowedTypes(self::FACTORY, GeometryFactory::class);
        // address options is required
        $optionsResolver->setRequired(self::ADDRESS)
                ->addAllowedTypes(self::ADDRESS, 'string');
        // optional neighborhood value
        $optionsResolver->setDefined(self::NEIGHBORHOOD)
                ->setAllowedTypes(self::NEIGHBORHOOD, 'string');
        // optional city value
        $optionsResolver->setDefined(self::CITY)
                ->setAllowedTypes(self::CITY, 'string');
        // optional region
        $optionsResolver->setDefined(self::REGION)
                ->setAllowedTypes(self::REGION, 'string');
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
     *      ['location'] - an ordered pair (x,y) of coordinates.
     *  </li>
     * </ul>
     */
    protected abstract function findCandidates(array $options): array;

    public function geocode(array $options, callable $filter = null): array
    {
        $candidates = $this->findCandidates($this->optionsResolver->resolve($options));
        foreach ($candidates as &$candidate) {
            $candidate[self::LOCATION] = $options[self::FACTORY]
                    ->createPoint($candidate[self::LOCATION]);
        }

        if ($filter) {
            return array_filter($candidates, $filter);
        }
        
        return $candidates;
    }

}
