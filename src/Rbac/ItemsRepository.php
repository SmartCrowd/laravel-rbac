<?php

namespace SmartCrowd\Rbac;

class ItemsRepository implements \ArrayAccess, \IteratorAggregate
{
    /**
     * @var array $items Items list
     */
    private $items = [];  // itemName => item

    /**
     * @var array $children Items tree
     */
    private $children = [];  // itemName, childName => child

    /**
     * @var array $action Permissions to Http actions assigns
     */
    protected $actions = []; // actionName => itemName[]

    /**
     * Add a item node to items list
     *
     * @param int $type
     * @param $name
     * @param array $children Names of child nodes
     * @param \Closure $rule
     * @param string $title
     * @throws \Exception
     */
    public function addItem($type, $name, $children = [], $rule = null, $title = '')
    {
        $class = $type == Item::TYPE_PERMISSION ? '\\SmartCrowd\\Rbac\\Permission' : '\\SmartCrowd\\Rbac\\Role';
        $this->items[$name] = new $class([
            'type'  => $type,
            'name'  => $name,
            'rule'  => $rule,
            'title' => $title,
        ]);

        foreach ($children as $childName) {
            if (isset($this->items[$childName])) {
                $this->addChild($this->items[$name], $this->items[$childName]);
            } elseif (strpos($childName, '*') !== false) {
                $found = $this->search($childName);
                foreach ($found as $foundItem) {
                    $this->addChild($this->items[$name], $this->items[$foundItem->name]);
                }
            } else {
                throw new \Exception("Can't add unknown permission '{$childName}' as child of '{$name}'");
            }
        }
    }

    /**
     * Searches items according given wildcard pattern
     *
     * @param string $pattern
     * @return array Founded items
     */
    public function search($pattern)
    {
        $pattern = '/^' . str_replace(['.', '*'], ['\\.', '.*'], $pattern) . '$/i';

        $found = [];
        foreach ($this->items as $item) {
            if (preg_match($pattern, $item->name)) {
                $found[] = $item;
            }
        }

        return $found;
    }

    /**
     * @param array $actions
     * @param array $permissions
     */
    public function action($actions, $permissions)
    {
        foreach ($actions as $action) {
            $currentPermissions = isset($this->actions[$action]) ? $this->actions[$action] : [];
            $this->actions[$action] = array_merge($currentPermissions, $permissions);
        }
    }

    /**
     * @return array
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @return array
     */
    public function getActions()
    {
        return $this->actions;
    }

    /**
     * @param $key
     * @return bool
     */
    public function has($key)
    {
        return $this->offsetExists($key);
    }

    /**
     * {inheritdoc}
     */
    public function offsetExists($offset)
    {
        return isset($this->items[$offset]);
    }

    /**
     * {inheritdoc}
     */
    public function offsetGet($offset)
    {
        return $this->items[$offset];
    }

    /**
     * {inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        $this->items[$offset] = $value;
    }

    /**
     * {inheritdoc}
     */
    public function offsetUnset($offset)
    {
        unset($this->items[$offset]);
    }

    /**
     * {inheritdoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->items);
    }


    /**
     * Make a new tree relation
     *
     * @param Item $parent
     * @param Item $child
     * @return bool
     * @throws \Exception
     */
    protected function addChild($parent, $child)
    {
        if (!isset($this->items[$parent->name], $this->items[$child->name])) {
            throw new \Exception("Either '{$parent->name}' or '{$child->name}' does not exist.");
        }

        if ($parent->name == $child->name) {
            throw new \Exception("Cannot add '{$parent->name} ' as a child of itself.");
        }

        if ($parent instanceof Permission && $child instanceof Role) {
            throw new \Exception("Cannot add a role as a child of a permission.");
        }

        if ($this->detectLoop($parent, $child)) {
            throw new \Exception("Cannot add '{$child->name}' as a child of '{$parent->name}'. A loop has been detected.");
        }

        if (isset($this->children[$parent->name][$child->name])) {
            throw new \Exception("The item '{$parent->name}' already has a child '{$child->name}'.");
        }

        $this->children[$parent->name][$child->name] = $this->items[$child->name];

        return true;
    }

    /**
     * Checks whether there is a loop in the authorization item hierarchy.
     *
     * @param Item $parent parent item
     * @param Item $child the child item that is to be added to the hierarchy
     * @return boolean whether a loop exists
     */
    protected function detectLoop($parent, $child)
    {
        if ($child->name === $parent->name) {
            return true;
        }

        if (!isset($this->children[$child->name], $this->items[$parent->name])) {
            return false;
        }

        foreach ($this->children[$child->name] as $grandchild) {
            /* @var $grandchild Item */
            if ($this->detectLoop($parent, $grandchild)) {
                return true;
            }
        }

        return false;
    }
}