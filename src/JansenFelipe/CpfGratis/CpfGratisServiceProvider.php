<?php

namespace JansenFelipe\CpfGratis;

use Illuminate\Support\ServiceProvider;

class CpfGratisServiceProvider extends ServiceProvider {

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot() {
        $this->package('JansenFelipe/cpf-gratis');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register() {
        $this->app->singleton('cpf_gratis', function() {
            return new \JansenFelipe\CpfGratis\CpfGratis;
        });
    }

}
