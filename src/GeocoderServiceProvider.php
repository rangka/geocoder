<?php

namespace Katsana\Geocoder;

use Geocoder\Geocoder;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Orchestra\Support\Providers\Concerns\AliasesProvider;

class GeocoderServiceProvider extends ServiceProvider implements DeferrableProvider
{
    use AliasesProvider;

    /**
     * Class aliases.
     *
     * @var array
     */
    protected $aliases = [
        'katsana.geocoder' => Manager::class,
    ];

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->registerGeocoder();
        $this->registerCoreContainerAliases();
    }

    /**
     * Register geocoder service provider.
     */
    protected function registerGeocoder(): void
    {
        $this->app->singleton('katsana.geocoder', static function ($app) {
            return new Manager($app);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'katsana.geocoder',
            Manager::class,
        ];
    }
}
