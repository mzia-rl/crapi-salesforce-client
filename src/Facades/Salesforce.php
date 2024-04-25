<?php

namespace Canzell\Facades;

use Illuminate\Support\Facades\Facade;
use Canzell\Http\Clients\SalesforceClient;

class Salesforce extends Facade
{

    static public function getFacadeAccessor()
    {
        return SalesforceClient::class;
    }

}
