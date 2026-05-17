<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class ViteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Blade::directive('vite', function (string $expression): string {
            return "<?php echo \\App\\Helpers\\Vite::tags($expression); ?>";
        });
    }
}

