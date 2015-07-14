<?php

namespace SmartCrowd\Rbac\Contracts;

interface RbacContext
{
    /**
     * Should return array of permission and role names,
     * assigned to given user.
     *
     * @param Assignable $user
     * @return array Array of user assignments.
     */
    public function getAssignments(Assignable $user);
}