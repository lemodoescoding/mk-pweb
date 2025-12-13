<?php

declare(strict_types=1);

namespace App\Utils;

use App\Core\Env;

class OAuth
{
  private string $client_id;
  private string $client_secret;
  private string $oauth_uri;
  private string $redirect_uri;
  private string $token_uri;

  private array $scopes = [];

  public function __construct()
  {
    $this->client_id = Env::get('GOOGLE_CLIENT_ID');
    $this->client_secret = Env::get('GOOGLE_CLIENT_SECRET');
    $this->redirect_uri = Env::get('GOOGLE_REDIRECT_URI');
    $this->oauth_uri = Env::get('GOOGLE_OAUTH_URL');
    $this->token_uri = Env::get('GOOGLE_OAUTH_TOKEN_URL');

    array_push($this->scopes, "https://www.googleapis.com/auth/userinfo.email");
    array_push($this->scopes, "https://www.googleapis.com/auth/userinfo.profile");
  }

  public function getAuthURL(): string
  {
    $state = bin2hex(random_bytes(16));

    $params = [
      'client_id' => $this->client_id,
      'redirect_uri' => $this->redirect_uri,
      'response_type' => 'code',
      'access_type' => 'offline',
      'state' => $state,
      'scope' => implode(" ", $this->scopes),
    ];

    return $this->oauth_uri . '?' . http_build_query($params);
  }

  public function getOAuthToken(string $code): ?array
  {
    $post_data = [
      'client_id' => $this->client_id,
      'client_secret' => $this->client_secret,
      'code' => $code,
      'grant_type' => 'authorization_code',
      'redirect_uri' => $this->redirect_uri,
    ];

    $ch = curl_init($this->token_uri);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $result = curl_exec($ch);

    if (!$result) {
      curl_close($ch);
      return null;
    }

    curl_close($ch);
    return json_decode($result, true);
  }

  public function getGoogleUser(string $access_token, string $id_token): array
  {
    $url = "https://www.googleapis.com/oauth2/v1/userinfo?alt=json&access_token=" . $access_token;
    $url = filter_var($url, FILTER_SANITIZE_URL);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
      "Authorization: Bearer " . $id_token,
    ]);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $result = curl_exec($ch);
    curl_close($ch);

    if (!$result) {
      throw new \Exception('Failed to fetch google user');
    }

    return json_decode($result, true);
  }
}
