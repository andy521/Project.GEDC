<?php

namespace App\Providers;

use App\Console\Commands\SocketCommand;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider {
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register() {
        $this->app->singleton('command.socket.start', function() {
            return new SocketCommand;
        });

        $this->commands(
            'command.socket.start'
        );
    }
}
