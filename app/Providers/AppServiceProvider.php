<?php

namespace App\Providers;

use App\Services\AIService;
use App\Services\AIServiceInterface;
use App\Services\PodosoftAIService;
use App\Policies\PatientImportPolicy;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Permission;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AIServiceInterface::class, function () {
            $url = config('services.python_ai.url');
            if (!empty($url)) {
                return new PodosoftAIService();
            }
            return new AIService();
        });
    }

    public function boot(): void
    {
        Gate::define('importar-pacientes', function (?User $user) {
            if ($user === null) {
                return false;
            }

            return app(PatientImportPolicy::class)->import($user);
        });

        foreach ((array) config('whatsapp-contacts.permissions') as $permission) {
            Gate::define($permission, function (?User $user) use ($permission) {
                if ($user === null) {
                    return false;
                }

                if (!Permission::where('name', $permission)->exists()) {
                    return true;
                }

                return $user->hasDirectPermission($permission)
                    || $user->hasPermissionTo($permission);
            });
        }

        if (env('APP_ENV') === 'production') {
            URL::forceScheme('https');
        }
    }
}