<?php
namespace SmartCrowd\Rbac;

use Illuminate\Support\Facades\Facade;

class RbacFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'rbac';
    }
}
