<?php

namespace App\Http\Controllers;

use App\Jobs\SyncStravaActivities;
use App\Models\User;
use App\Services\StravaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StravaAuthController extends Controller
{
    public function __construct(private StravaService $stravaService) {}

    public function redirect()
    {
        return redirect($this->stravaService->getAuthUrl());
    }

    public function callback(Request $request)
    {
        if ($request->has('error')) {
            return redirect('/')->withErrors(['strava' => 'Autorisation Strava refusée.']);
        }

        $tokenData = $this->stravaService->exchangeToken($request->code);

        if (isset($tokenData['errors']) || ! isset($tokenData['athlete'])) {
            return redirect('/')->withErrors(['strava' => "Échec de l'authentification Strava."]);
        }

        $athlete = $tokenData['athlete'];

        $user = User::updateOrCreate(
            ['strava_id' => $athlete['id']],
            [
                'name'                    => trim($athlete['firstname'] . ' ' . $athlete['lastname']),
                'email'                   => $athlete['email'] ?? null,
                'avatar'                  => $athlete['profile'] ?? null,
                'strava_access_token'     => $tokenData['access_token'],
                'strava_refresh_token'    => $tokenData['refresh_token'],
                'strava_token_expires_at' => now()->createFromTimestamp($tokenData['expires_at']),
            ]
        );

        Auth::login($user, remember: true);

        $lastActivity = $user->activities()->latest('start_date')->first();
        $after = $lastActivity ? $lastActivity->start_date->timestamp : null;

        SyncStravaActivities::dispatch($user, $after);

        return redirect()->route('dashboard');
    }

    public function disconnect(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
