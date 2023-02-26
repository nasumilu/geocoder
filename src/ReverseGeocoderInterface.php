<?php

namespace Nasumilu\Spatial\Geocoder;

interface ReverseGeocoderInterface
{

    public function reverseGeocode(array $location): AddressCandidate|null;

}