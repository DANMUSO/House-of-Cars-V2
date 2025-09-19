<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        
        // Skip non-essential routes
        if ($this->shouldSkip($request)) {
            return $response;
        }

        $this->logActivity($request);
        return $response;
    }

    private function shouldSkip($request): bool
    {
        return $request->method() === 'OPTIONS' || 
               $request->is('api/dashboard/*') ||
               $request->is('_debugbar/*');
    }

    private function logActivity($request): void
    {
        $action = $this->getAction($request);
        $user = Auth::user();
        
        $properties = [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'route' => $request->route()?->getName(),
            'data' => $this->sanitizeData($request->all())
        ];

        // Add user data if authenticated
        if ($user) {
            $properties['user'] = [
                'id' => $user->id,
                'email' => $user->email ?? ''
            ];
        }
        
        activity()
            ->causedBy($user)
            ->withProperties($properties)
            ->log($action);
    }

    private function getAction($request): string
    {
        $method = $request->method();
        $path = $request->path();

        $actions = [
            'GET' => [
                'admin/dashboard' => 'Accessed Admin Dashboard',
                'car-imports' => 'Viewed Car Imports',
                'hirepurchase' => 'Viewed Hire Purchase',
                'users' => 'Viewed Users',
                'leads' => 'Viewed Leads',
            ],
            'POST' => [
                'carimport/store' => 'Created Car Import',
                'hirepurchase/store' => 'Created Hire Purchase',
                'user/store' => 'Created User',
                'leads' => 'Created Lead',
            ],
            'PUT' => [
                'user/update' => 'Updated User',
                'tradein' => 'Updated Trade-in',
            ],
            'DELETE' => [
                'users' => 'Deleted User',
                'vehicle' => 'Deleted Vehicle',
            ]
        ];

        if (isset($actions[$method])) {
            foreach ($actions[$method] as $pattern => $action) {
                if (str_contains($path, $pattern)) {
                    return $action;
                }
            }
        }

        return ucfirst(strtolower($method)) . ': ' . $path;
    }

    private function sanitizeData(array $data): array
    {
        $sensitive = ['password', 'password_confirmation', '_token'];
        foreach ($sensitive as $field) {
            unset($data[$field]);
        }
        return array_slice($data, 0, 10); // Limit size
    }
}