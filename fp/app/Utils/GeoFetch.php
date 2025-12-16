<?php

declare(strict_types=1);

namespace App\Utils;

use App\Core\Env;
use App\Enums\StatusCodes;

class GeoFetch
{
  public static function getLatLon(string $q): array
  {
    $encoded = urlencode(trim($q));

    $url = "https://geocode.maps.co/search?q={$encoded}&api_key=" . Env::get('GEO_API_KEY');

    $ch = curl_init();
    curl_setopt_array($ch, [
      CURLOPT_URL            => $url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_TIMEOUT        => 10,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTPHEADER     => [
        "Accept: application/json"
      ],
    ]);

    $response = curl_exec($ch);
    curl_close($ch);
    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);

    if($http != StatusCodes::OK->value || $err) {
      return [];
    }

    return \json_decode($response, true);
  }
}
