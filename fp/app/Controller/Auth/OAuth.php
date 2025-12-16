<?php

declare(strict_types=1);

namespace App\Controller\Auth;

use App\Controller\BaseAuth;
use App\Model\Auth as AuthModel;
use App\Core\DB;
use App\Core\Env;
use App\Utils\Response;
use App\Enums\StatusCodes;

class OAuth extends BaseAuth
{
  private AuthModel $auth;
  private string $client_id;
  private string $client_secret;
  private string $oauth_uri;
  private string $redirect_uri;
  private string $token_uri;

  private array $scopes = [];

  public function __construct()
  {
    $this->auth = new AuthModel(DB::getInstance());
    $this->client_id = Env::get('GOOGLE_CLIENT_ID');
    $this->client_secret = Env::get('GOOGLE_CLIENT_SECRET');
    $this->redirect_uri = Env::get('GOOGLE_REDIRECT_URI');
    $this->oauth_uri = Env::get('GOOGLE_OAUTH_URL');
    $this->token_uri = Env::get('GOOGLE_OAUTH_TOKEN_URL');

    array_push($this->scopes, "https://www.googleapis.com/auth/userinfo.email");
    array_push($this->scopes, "https://www.googleapis.com/auth/userinfo.profile");
  }

  private function getAuthURL(): string
  {

    $state = bin2hex(random_bytes(24));
    $signature = hash_hmac('sha256', $state, Env::get('APP_KEY'));

    $params = [
      'client_id' => $this->client_id,
      'redirect_uri' => $this->redirect_uri,
      'response_type' => 'code',
      'access_type' => 'offline',
      'state' => $state . "." . $signature,
      'scope' => implode(" ", $this->scopes),
    ];

    return $this->oauth_uri . '?' . http_build_query($params);
  }

  private function getOAuthToken(string $code): ?array
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

  private function getGoogleUser(string $access_token, string $id_token): array
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

  private function parseJSON(): array
  {
    $body = file_get_contents('php://input');
    if (empty($body)) {
      return [];
    }

    $data = json_decode($body, true);
    return is_array($data) ? $data : [];
  }

  public function login()
  {
    $data = $this->parseJSON();

    if (isset($_GET['error'])) {
      header('Location: ' . $this->getAuthURL());
      exit;
    }

    [$state, $signature] = explode('.', $_GET['state']);

    if (hash_hmac('sha256', $state, Env::get('APP_KEY')) !== $signature) {
      Response::error(null, StatusCodes::BAD_REQUEST, "Invalid state");
      exit;
    }

    $code = $_GET['code'] ?? null;

    if (!$code) {
      Response::error(null, StatusCodes::BAD_REQUEST, "Authorization code missing");
      exit;
    }

    $tokenData = $this->getOAuthToken($code);
    if (!$tokenData) {
      Response::error(null, StatusCodes::INTERNAL_SERVER_ERROR, "Failed to fetch OAuth Token");
      exit;
    }

    $id_token = $tokenData['id_token'] ?? null;
    $access_token = $tokenData['access_token'] ?? null;


    if (!$id_token || !$access_token) {
      Response::error(null, StatusCodes::INTERNAL_SERVER_ERROR, "Token missing from Google response");
      exit;
    }

    // Get Google user info
    $googleUser = $this->getGoogleUser($access_token, $id_token);

    $user = $this->auth->findOrCreateFromGoogle($googleUser);

    $userId = (int)$user['id'];
    $rawToken = bin2hex(random_bytes(32));
    $tokenHash = hash('sha256', $rawToken);

    $this->auth->setToken($userId, $tokenHash);
    // return Response::success([
    //   // 'tokenData' => $tokenData,
    //   // 'googleUser' => $googleUser
    //   'user' => [
    //     // 'id' => $userId,
    //     'username' => $user['username'],
    //     'email' => $user['email'],
    //     'placeholder' => $user['placeholder'],
    //     'role' => $user['role']
    //   ],
    //   'api_token' => $rawToken
    // ]);

    setcookie('api_token', $rawToken, [
      'expires' => time() + 3600 * 24,
      'path' => '/',
      'httponly' => false,
      'samesite' => 'Lax',
    ]);

    setcookie('role', $user['role'], [
      'expires' => time() + 3600 * 24,
      'path' => '/',
      'httponly' => false,
      'samesite' => 'Lax',
    ]);

    $redirect = '/dashboard';

    if ($user['role'] == 'admin') {
      $redirect = '/admin';
    }

    header('Location: ' . $redirect);

    return;
  }

  public function register()
  {
    // return Response::success([
    //   'oauth_uri' => $this->getAuthURL()
    // ], StatusCodes::OK, "Google OAuth URI - Redirect to this");

    header('Location: ' . $this->getAuthURL());
    exit;
  }

  public function me(?array $user = null)
  {
    return Response::success([
      'username' => $user['username'],
      'placeholder' => $user['placeholder'],
      'email' => $user['email'],
      'role' => $user['role']
    ], StatusCodes::OK, "Authenticated");
  }

  public function logout(?array $user = null)
  {
    // $user = $this->getAuthenticatedUser();
    // if (!$user) {
    //   return Response::error("unauthorized", StatusCodes::UNAUTHORIZED);
    // }

    $this->auth->clearToken($user['id']);

    setcookie('api_token', '', time() - 3600, '/', '', false, true);
    setcookie('role', '', time() - 3600, '/', '', false, true);

    session_unset();
    session_destroy();

    return Response::success(null, StatusCodes::OK, "Logged out");
  }
}
