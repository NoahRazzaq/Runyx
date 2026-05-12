<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;

class StravaService
{
    private const BASE_URL = 'https://www.strava.com';
    private const API_URL = 'https://www.strava.com/api/v3';
    private const MAX_RETRIES = 3;

    private string $clientId;
    private string $clientSecret;
    private string $redirectUri;

    public function __construct()
    {
        $this->clientId = config('services.strava.client_id');
        $this->clientSecret = config('services.strava.client_secret');
        $this->redirectUri = config('services.strava.redirect_uri');
    }

    public function getAuthUrl(): string
    {
        $params = http_build_query([
            'client_id'       => $this->clientId,
            'redirect_uri'    => $this->redirectUri,
            'response_type'   => 'code',
            'approval_prompt' => 'auto',
            'scope'           => 'read,activity:read_all',
        ]);

        return self::BASE_URL . '/oauth/authorize?' . $params;
    }

    public function exchangeToken(string $code): array
    {
        $response = Http::post(self::BASE_URL . '/oauth/token', [
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code'          => $code,
            'grant_type'    => 'authorization_code',
        ]);

        return $response->json() ?? [];
    }

    public function refreshToken(User $user): void
    {
        $response = Http::post(self::BASE_URL . '/oauth/token', [
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'grant_type'    => 'refresh_token',
            'refresh_token' => $user->strava_refresh_token,
        ]);

        $data = $response->json();

        $user->update([
            'strava_access_token'    => $data['access_token'],
            'strava_refresh_token'   => $data['refresh_token'],
            'strava_token_expires_at' => now()->createFromTimestamp($data['expires_at']),
        ]);
    }

    public function getAthlete(User $user): array
    {
        return $this->get($user, '/athlete');
    }

    public function getActivities(User $user, int $page = 1, int $perPage = 50, ?int $after = null): array
    {
        $params = ['page' => $page, 'per_page' => $perPage];

        if ($after !== null) {
            $params['after'] = $after;
        }

        return $this->get($user, '/athlete/activities', $params);
    }

    public function getActivity(User $user, int $stravaId): array
    {
        return $this->get($user, "/activities/{$stravaId}", [
            'include_all_efforts' => 'true',
        ]);
    }

    public function getActivityStreams(
        User $user,
        int $stravaId,
        array $keys = ['heartrate', 'time', 'distance', 'altitude']
    ): array {
        return $this->get($user, "/activities/{$stravaId}/streams", [
            'keys'             => implode(',', $keys),
            'key_by_type'      => 'true',
        ]);
    }

    private function ensureValidToken(User $user): string
    {
        if ($user->isStravaTokenExpired()) {
            $this->refreshToken($user);
            $user->refresh();
        }

        return $user->strava_access_token;
    }

    private function get(User $user, string $path, array $params = []): array
    {
        $token = $this->ensureValidToken($user);

        for ($attempt = 0; $attempt < self::MAX_RETRIES; $attempt++) {
            $response = Http::withToken($token)
                ->get(self::API_URL . $path, $params);

            if ($response->status() !== 429) {
                return $response->json() ?? [];
            }

            // Respect Strava's rate limit window (15 min or daily)
            $retryAfter = (int) ($response->header('X-RateLimit-Limit') ?: 60);
            sleep(min($retryAfter, 60));
        }

        return [];
    }
}
