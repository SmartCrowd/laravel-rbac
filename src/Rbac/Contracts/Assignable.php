<?php

namespace SmartCrowd\Rbac\Contracts;

interface Assignable
{
    /**
     * Should return array of permissions and roles names,
     * assigned to user.
     *
     * @return array Array of user assignments.
     */
    public function getAssignments();
}