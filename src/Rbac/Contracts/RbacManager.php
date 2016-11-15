<?php

namespace SmartCrowd\Rbac\Contracts;

use SmartCrowd\Rbac\Item;
use SmartCrowd\Rbac\ItemsRepository;

interface RbacManager
{
    /**
     * @param Assignable|null $user
     * @param string $itemName
     * @param array $params
     * @return boolean
     */
    public function checkAccess($user, $itemName, $params = []);

    /**
     * @param string $itemName
     * @return bool
     */
    public function has($itemName);

    /**
     * @param array|string $actions
     * @param array|string $permissions
     */
    public function action($actions, $permissions);

    /**
     * @param string $name
     * @param array $children
     * @param \Closure $rule
     */
    public function permission($name, $children = [], $rule = null);

    /**
     * @param string $name
     * @param array $children
     */
    public function role($name, $children);

    /**
     * @param string $itemName
     * @param string $controller
     * @param string $foreignKey
     */
    public function resource($itemName, $controller = null, $foreignKey = null);

    /**
     * @return ItemsRepository
     */
    public function getRepository();

    /**
     * @var ItemsRepository $repository
     */
    public function setRepository(ItemsRepository $repository);

    /**
     * @return Item array
     */
    public function getActions();
}