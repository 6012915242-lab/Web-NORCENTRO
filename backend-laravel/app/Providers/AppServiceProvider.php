<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Carga el ORM y los servicios propios de NORCENTRO.
        require_once dirname(base_path()) . '/autoload.php';
    }
}
