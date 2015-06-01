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

    /**
     * Determines whether is user allowed given permission
     * or not.
     *
     * @param string $name Permission name.
     * @param array $params Array of additional params for biz rule.
     * @return bool
     */
    public function allowed($name, $params);
}