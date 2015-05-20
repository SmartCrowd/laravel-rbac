<?php

namespace SmartCrowd\Rbac;

class Item
{
    const TYPE_ROLE = 1;
    const TYPE_PERMISSION = 2;

    /**
     * @var integer the type of the item. This should be either [[TYPE_ROLE]] or [[TYPE_PERMISSION]].
     */
    public $type;

    /**
     * @var string the name of the item. This must be globally unique.
     */
    public $name;

    /**
     * @var string the item description
     */
    public $description;

    /**
     * @var \Closure business rule
     */
    public $rule;

    /**
     * @var mixed the additional data associated with this item
     */
    public $data;
}
