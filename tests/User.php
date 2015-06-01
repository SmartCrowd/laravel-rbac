<?php

namespace SmartCrowd\Rbac;

use SmartCrowd\Rbac\Contracts\Assignable;
use SmartCrowd\Rbac\Traits\AllowedTrait;

class User implements Assignable
{
    use AllowedTrait;

    public $roles;
    public $id;

    public function __construct($id, $roles)
    {
        $this->id = $id;
        $this->roles = $roles;
    }

    public function getAssignments()
    {
        return $this->roles;
    }
}