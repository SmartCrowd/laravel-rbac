<?php

namespace SmartCrowd\Rbac;


class Rule
{
    private $user;

    /**
     * @var Item $item
     */
    private $item;

    /**
     * @var \Closure $closure
     */
    private $closure;

    /**
     * @param \Closure $closure
     */
    public function __construct(\Closure $closure)
    {
        $this->closure = $closure->bindTo($this, $this);
    }

    /**
     * @param $user
     * @return $this
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @param Item $item
     * @return $this
     */
    public function setItem($item)
    {
        $this->item = $item;

        return $this;
    }

    /**
     * @param array $params
     * @return boolean
     */
    public function execute($params)
    {
        $closure = $this->closure;

        return (boolean)$closure($params);
    }

}