<?php

namespace Nasumilu\Spatial;

interface ReverseGeocoderInterface
{

    public function reverseGeocode(array $location): AddressCandidate|null;

}