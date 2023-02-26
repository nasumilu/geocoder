<?php

namespace Nasumilu\Spatial\Geocoder;

use RuntimeException;
use Throwable;

/**
 * Used to indicate that something unexpected occurred when geocoding an address candidate
 */
class GeocoderException extends RuntimeException
{

    public function __construct(?string $message = null, ?Throwable $previous = null)
    {
        parent::__construct($message ?? 'Unable to geocode address!', E_ERROR, $previous);
    }

}