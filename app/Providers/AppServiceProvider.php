<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('public-submissions', function (Request $request) {
            $routeCompetition = $request->route('competition');
            $competitionId = $routeCompetition instanceof \Illuminate\Database\Eloquent\Model
                ? (string) $routeCompetition->getKey()
                : (string) $routeCompetition;
            $sessionKey = hash('sha256', $request->session()->getId());
            $ipKey = hash('sha256', (string) $request->ip());
            $response = fn (Request $request, array $headers) => response()
                ->view('errors.429', [], 429, $headers);

            return [
                Limit::perMinute(config('submissions.rate_limits.session_per_minute'))
                    ->by("competition:{$competitionId}:session:{$sessionKey}")
                    ->response($response),
                Limit::perHour(config('submissions.rate_limits.session_per_hour'))
                    ->by("competition:{$competitionId}:session-hour:{$sessionKey}")
                    ->response($response),
                Limit::perMinute(config('submissions.rate_limits.ip_per_minute'))
                    ->by("competition:{$competitionId}:ip:{$ipKey}")
                    ->response($response),
                Limit::perHour(config('submissions.rate_limits.ip_per_hour'))
                    ->by("competition:{$competitionId}:ip-hour:{$ipKey}")
                    ->response($response),
            ];
        });
    }
}
