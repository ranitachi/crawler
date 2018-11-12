<?php

namespace App\Providers\Scrapper;

use Illuminate\Support\Facades\Facade;

class ScrapperFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'scrapper';
    }
}
