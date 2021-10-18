<?php

namespace Plank\NovaCalendarTool;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;
use Plank\NovaCalendarTool\Http\Middleware\Authorize;
use Plank\NovaCalendarTool\Models\Event;
use Plank\NovaCalendarTool\Observers\EventObserver;

class ToolServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'nova-calendar-tool');

        $this->publishes([
            __DIR__ . '/../database/migrations/create_events_table.php.stub' => database_path('migrations/'.date('Y_m_d_His', time()).'_create_events_table.php'),
        ], 'migrations');

        $this->publishes([
            __DIR__.'/config/nova-calendar-tool.php' => config_path('nova-calendar-tool.php'),
        ], 'config');

        $this->app->booted(function () {
            $this->routes();
        });

        Nova::serving(function (ServingNova $event) {
            if ( ! is_null(config('google-calendar.calendar_id')))
            {
                Event::observe(EventObserver::class);
            }

            Nova::provideToScript([
                'fullcalendar_locale' => config('nova-calendar-tool.fullcalendar_locale'),
            ]);
        });
    }

    /**
     * Register the tool's routes.
     *
     * @return void
     */
    protected function routes()
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        Route::middleware(['nova', Authorize::class])
                ->prefix('nova-vendor/nova-calendar-tool')
                ->namespace('Plank\NovaCalendarTool\Http\Controllers')
                ->group(__DIR__.'/../routes/api.php');

        $this->commands([
//            \Plank\NovaCalendarTool\Console\Commands\ImportEvents::class,
//            \Plank\NovaCalendarTool\Console\Commands\ExportEvents::class
        ]);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
