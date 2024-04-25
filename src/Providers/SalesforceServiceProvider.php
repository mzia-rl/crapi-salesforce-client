<?php

namespace Canzell\Providers;

use Illuminate\Support\ServiceProvider;
use Canzell\Http\Clients\SalesforceClient;

class SalesforceServiceProvider extends ServiceProvider
{

    public $singletons = [
        SalesforceClient::class => SalesforceClient::class 
    ];

    public function boot()
    {
        $this->publishes([
            __DIR__.'/../../config/salesforce-client.php' => config_path('salesforce-client.php')
        ]);
        $this->mergeConfigFrom(
            __DIR__.'/../../config/salesforce-client.php', 'salesforce-client'
        );
    }

}
