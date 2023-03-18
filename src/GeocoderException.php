<?php
/**
 *    Copyright 2023 Michael Lucas
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0

 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
namespace Nasumilu\Spatial;

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