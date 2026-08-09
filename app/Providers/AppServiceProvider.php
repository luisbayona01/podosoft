<?php

namespace App\Providers;

use App\Services\AIService;
use App\Services\AIServiceInterface;
use App\Services\PodosoftAIService;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

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
        if (env('APP_ENV') === 'production') {
            URL::forceScheme('https');
        }
    }
}