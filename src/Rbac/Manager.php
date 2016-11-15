<?php

namespace SmartCrowd\Rbac;

use SmartCrowd\Rbac\Contracts\Assignable;
use SmartCrowd\Rbac\Contracts\RbacContext;
use SmartCrowd\Rbac\Contracts\RbacContextAccessor;
use SmartCrowd\Rbac\Contracts\RbacManager;

class Manager implements RbacManager
{
    /**
     * @var ItemsRepository
     */
    protected $items;

    public function __construct()
    {
        $this->items = new ItemsRepository;
    }

    /**
     * @param Assignable|null $user
     * @param string $itemName
     * @param array $params
     * @return boolean
     */
    public function checkAccess($user, $itemName, $params = [])
    {
        if (empty($user)) {
            return false;
        }

        $assignments = $user->getAssignments();
        $contextAssignments = $this->resolveContextAssignments($user, $params);
        $assignments = array_merge($assignments, $contextAssignments);

        return $this->checkAccessRecursive($user, $itemName, $params, $assignments);
    }

    /**
     * @param string $itemName
     * @return bool
     */
    public function has($itemName)
    {
        return isset($this->items[$itemName]);
    }

    /**
     * @param array|string $actions
     * @param array|string $permissions
     */
    public function action($actions, $permissions)
    {
        if (!is_array($actions)) {
            $actions = [$actions];
        }

        if (!is_array($permissions)) {
            $permissions = [$permissions];
        }

        $this->items->action($actions, $permissions);
    }

    /**
     * @param string $name
     * @param array $children
     * @param \Closure $rule
     * @param string $title
     * @throws \Exception
     */
    public function permission($name, $children = [], $rule = null, $title = '')
    {
        $this->items->addItem(Item::TYPE_PERMISSION, $name, $children, $rule, $title);
    }

    /**
     * @param string $name
     * @param array $children
     * @param string $title
     * @throws \Exception
     */
    public function role($name, $children, $title = '')
    {
        $this->items->addItem(Item::TYPE_ROLE, $name, $children, null, $title);
    }

    /**
     * @param string $itemName
     * @param string $controller
     * @param string $foreignKey
     */
    public function resource($itemName, $controller = null, $foreignKey = null)
    {
        $actions = [
            'index',
            'create',
            'store',
            'show',
            'edit',
            'update',
            'destroy',
        ];

        $tasks = [
            'public' => [
                'index',
                'show',
            ],
            'manage' => [
                'update',
                'edit',
                'destroy',
            ],
        ];

        foreach ($actions as $action) {
            $this->permission($itemName . '.' . $action);
        }

        foreach ($tasks as $taskName => $actions) {
            $this->permission($itemName . '.' . $taskName, array_map(function ($value) use ($itemName) {
                return $itemName . '.' . $value;
            }, $actions));
        }

        if (!empty($foreignKey)) {
            $this->permission($itemName . '.manage.own', [$itemName . '.manage'],
                function ($params) use ($foreignKey, $itemName) {
                    return $params[$itemName]->{$foreignKey} == $this->user->id;
                });
        }

        if (!empty($controller)) {
            foreach ($actions as $action) {
                $this->action($controller . '@' . $action, $itemName . '.' . $action);
            }
        }
    }

    /**
     * @return ItemsRepository
     */
    public function getRepository()
    {
        return $this->items;
    }

    /**
     * @var ItemsRepository $repository
     */
    public function setRepository(ItemsRepository $repository)
    {
        $this->items = $repository;
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
    protected function checkAccessRecursive(Assignable $user, $itemName, $params, $assignments)
    {
        if (!$this->items->has($itemName)) {
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

        foreach ($this->items->getChildren() as $parentName => $children) {
            if (isset($children[$itemName]) && $this->checkAccessRecursive($user, $parentName, $params, $assignments)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Executes the rule associated with the specified auth item.
     *
     * If the item does not specify a rule, this method will return true. Otherwise, it will
     * return the value of rule execution.
     *
     * @param Assignable $user the user.
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

    /**
     * Extracts assignments from business rules parameters,
     * if they are RBAC context, or context accessor.
     *
     * @param Assignable $user
     * @param array $params Business rules parameters.
     * @return array Array of new context assignments for current checked user.
     */
    protected function resolveContextAssignments($user, $params)
    {
        $assignments = [];
        foreach ($params as $parameter) {
            if ($parameter instanceof RbacContext) {
                $assignments = array_merge($assignments, $parameter->getAssignments($user));
            }
            if ($parameter instanceof RbacContextAccessor) {
                $assignments = array_merge(
                    $assignments,
                    $this->resolveContextAssignments($user, [$parameter->getContext()])
                );
            }
        }

        return $assignments;
    }

    /**
     * @return Item array
     */
    public function getActions()
    {
        return $this->items->getActions();
    }
}