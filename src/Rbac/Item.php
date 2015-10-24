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
     * @var \Closure business rule
     */
    public $rule;

    /**
     * @var string Human readable item name
     */
    public $title;

    public function __construct($config)
    {
        foreach ($config as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }
}
