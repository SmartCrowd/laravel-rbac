<?php

namespace SmartCrowd\Rbac\Contracts;

interface RbacContextAccessor
{
    /**
     * Return RBAC Context entity, that are parent or
     * wrapper for current entity.
     *
     * @return RbacContext Context entity for current implementation.
     */
    public function getContext();
}