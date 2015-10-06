<?php

namespace SmartCrowd\Rbac\Traits;

use SmartCrowd\Rbac\Facades\Rbac;

trait AllowedTrait
{
    public function allowed($itemName, $params = [])
    {
        return Rbac::checkAccess($this, $itemName, $params);
    }
}