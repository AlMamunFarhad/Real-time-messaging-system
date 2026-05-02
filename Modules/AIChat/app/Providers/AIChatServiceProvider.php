<?php

namespace Modules\AIChat\app\Providers;

use Nwidart\Modules\Support\ModuleServiceProvider;
use Illuminate\Console\Scheduling\Schedule;

class AIChatServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'AIChat';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'aichat';

    /**
     * Command classes to register.
     *
     * @var string[]
     */
    // protected array $commands = [];

    /**
     * Provider classes to register.
     *
     * @var string[]
     */
    protected array $providers = [
        EventServiceProvider::class,
    ];

    public function register(): void
    {
        parent::register();
        $this->registerConfig();
        $this->app->singleton(\Modules\AIChat\app\Services\AI\AIGatewayService::class, function ($app) {
            return new \Modules\AIChat\app\Services\AI\AIGatewayService();
        });
    }


    public function boot(): void
    {
        \Log::info("AIChatServiceProvider booted.");
        
        // Listener for MessageSent is already registered in EventServiceProvider.
    }

    protected function registerConfig(): void
    {
        $this->publishes([
            __DIR__.'/../../config/aichat.php' => config_path('aichat.php'),
        ], 'config');

        $this->mergeConfigFrom(
            __DIR__.'/../../config/aichat.php', 'aichat'
        );
    }

    /**
     * Define module schedules.
     * 
     * @param $schedule
     */
    // protected function configureSchedules(Schedule $schedule): void
    // {
    //     $schedule->command('inspire')->hourly();
    // }
}
