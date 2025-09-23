<?php
namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     * We will no longer rely on this constant directly in the controller.
     *
     * @var string
     */
    public const HOME = '/redirect-home';  // This could be the default fallback route

    /**
     * Redirect the user based on their role dynamically.
     *
     * @return string
     */
    public static function redirectBasedOnRole()
    {
        $role = auth()->user()->role ?? 'user';  // Get the role of the authenticated user

        // Use match to return the correct URL based on the role
        return match ($role) {
            'Managing-Director' => '/admin/dashboard',
            'Accountant' => '/admin/dashboard',
            'Showroom-Manager' => '/admin/dashboard',
            'Salesperson' => '/Facilitation/requests',
            'Support-Staff' => '/Facilitation/requests',
            'Yard-Supervisor' => '/Facilitation/requests',
            'HR' => '/admin/dashboard',
            'General-Manager' => '/admin/dashboard',
            default => self::HOME,  // Fallback to the default route, which is /dashboard
        };
    }

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
