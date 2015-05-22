<?php

namespace SmartCrowd\Rbac;

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