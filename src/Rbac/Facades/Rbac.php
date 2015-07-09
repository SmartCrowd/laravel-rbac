<?php

namespace SmartCrowd\Rbac\Facades;

use Illuminate\Support\Facades\Facade;

class Rbac extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return 'rbac';
    }
}