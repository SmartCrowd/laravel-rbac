<?php

namespace SmartCrowd\Rbac;

use SmartCrowd\Rbac\Contracts\ItemsProviderInterface;

class Manager
{
    /**
     * @var Item[]
     */
    protected $items = []; // itemName => item

    /**
     * @var array
     */
    protected $children = []; // itemName, childName => child

    /**
     * @var array
     */
    protected $actions = []; // actionName => itemName

    /**
     * @var array
     */
    protected $controllers = []; // controllerName => prefix

    public function __construct(ItemsProviderInterface $itemsProvider)
    {
        $this->load($itemsProvider);
    }

    /**
     * @param Assignable $user
     * @param string $itemName
     * @param array $params
     * @return boolean
     */
    public function checkAccess($user, $itemName, $params = [])
    {
        $assignments = $user->getAssignments();
        return $this->checkAccessRecursive($user, $itemName, $params, $assignments);
    }

    public function has($itemName)
    {
        return isset($this->items[$itemName]);
    }

    public function action($actionName, $itemName)
    {
        $this->actions[$actionName] = $itemName;
    }

    public function controller($controllerName, $prefix)
    {
        $this->controllers[$controllerName] = $prefix;
    }

    public function permission()
    {

    }

    public function role()
    {

    }

    public function resource()
    {

    }

    /**
     * @return array
     */
    public function getActions()
    {
        return $this->actions;
    }

    /**
     * @return array
     */
    public function getControllers()
    {
        return $this->controllers;
    }

    /**
     * Performs access check for the specified user.
     * This method is internally called by [[checkAccess()]].
     *
     * @param Assignable $user the user.
     * @param string $itemName the name of the operation that need access check.
     * @param array $params name-value pairs that would be passed to rules associated.
     * with the permissions and roles assigned to the user.
     * @param array $assignments the list of permissions and roles, assigned to the specified user.
     * @return boolean whether the operations can be performed by the user.
     */
    protected function checkAccessRecursive($user, $itemName, $params, $assignments)
    {
        if (!isset($this->items[$itemName])) {
            return false;
        }

        /* @var $item Item */
        $item = $this->items[$itemName];

        if (!$this->executeRule($user, $item, $params)) {
            return false;
        }

        if (in_array($itemName, $assignments)) {
            return true;
        }

        foreach ($this->children as $parentName => $children) {
            if (isset($children[$itemName]) && $this->checkAccessRecursive($user, $parentName, $params, $assignments)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Load items from rules source and
     * @param ItemsProviderInterface $itemsProvider
     * @throws \Exception
     */
    protected function load($itemsProvider)
    {
        $this->children = [];
        $this->items = [];

        $items = $itemsProvider->get();

        foreach ($items as $name => $item) {
            $class = $item['type'] == Item::TYPE_PERMISSION ? '\\SmartCrowd\\Rbac\\Permission' : '\\SmartCrowd\\Rbac\\Role';
            $this->items[$name] = new $class([
                'name' => $name,
                'description' => isset($item['description']) ? $item['description'] : null,
                'rule' => isset($item['rule']) ? $item['rule'] : null,
                'data' => isset($item['data']) ? $item['data'] : null,
            ]);
        }

        foreach ($items as $name => $item) {
            if (isset($item['children'])) {
                foreach ($item['children'] as $childName) {
                    if (isset($this->items[$childName])) {
                        $this->addChild($this->items[$name], $this->items[$childName]);
                    }
                }
            }
        }
    }

    /**
     * Executes the rule associated with the specified auth item.
     *
     * If the item does not specify a rule, this method will return true. Otherwise, it will
     * return the value of rule execution.
     *
     * @param Model $user the user.
     * @param Item $item the auth item that needs to execute its rule
     * @param array $params parameters passed to [[ManagerInterface::checkAccess()]] and will be passed to the rule
     * @return boolean the return value of rule execution. If the auth item does not specify a rule, true will be returned.
     */
    protected function executeRule($user, $item, $params)
    {
        if ($item->rule instanceof \Closure) {
            return (new Rule($item->rule))
                ->setUser($user)
                ->setItem($item)
                ->execute($params);
        }
        return true;
    }

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