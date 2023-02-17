<?php
/** @noinspection PhpIllegalPsrClassPathInspection */

/*
 * Copyright © 2023. mPhpMaster(https://github.com/mPhpMaster) All rights reserved.
 */

namespace MPhpMaster\LaravelGuesserHelpers\Providers;

use Illuminate\Database\Schema\Builder;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

/**
 * Class HelperProvider
 *
 * @package MPhpMaster\LaravelGuesserHelpers\Providers
 */
class HelperProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @param Router $router
     *
     * @return void
     */
    public function boot(Router $router)
    {
        // Builder::defaultStringLength(191);
        // Schema::defaultStringLength(191);

        /**
         * Helpers
         */
        require_once __DIR__ . '/../Helpers/FCheckers.php';
        require_once __DIR__ . '/../Helpers/FCurrentGetters.php';
        require_once __DIR__ . '/../Helpers/FHelpers.php';
    }

    /**
     *
     */
    public function registerMacros()
    {

    }

    /**
     * @return array
     */
    public function provides()
    {
        return [];
    }

    public function register()
    {
        // $this->registerMacros();
    }
}
