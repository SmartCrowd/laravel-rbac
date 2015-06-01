<?php

namespace SmartCrowd\Rbac\Traits;

trait AllowedTrait
{
    public function allowed($itemName, $params = [])
    {
        return app('rbac')->checkAccess($this, $itemName, $params);
    }
}