<?php

namespace Nasumilu\Spatial\Geocoder\ReverseGeocoder;

class EsriWorld extends AbstractReverseGeocoder
{
    private const BASE_URI = 'https://geocode.arcgis.com/arcgis/rest/services/World/GeocodeServer/reverseGeocode';

    public function __construct()
    {
        parent::__construct(self::BASE_URI);
    }

    protected function mapCandidates(array $candidates): string|null
    {
        return $candidates['address']['Match_addr'] ?? null;
    }

    protected function query(float|int|string $x, float|int|string $y): array
    {
        return [
            'location' => "$x,$y",
            'f' => 'json'
        ];
    }
}