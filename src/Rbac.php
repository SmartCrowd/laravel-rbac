<?php
namespace SmartCrowd\Rbac;

use Illuminate\Support\Str;

class Rbac
{
    const TYPE_ROLE = 1;
    const TYPE_TASK = 2;
    const TYPE_PERMISSION = 3;

    const DEFAULT_SCOPE = 0;

    protected $config;
    protected $user;
    protected $roles;
    protected $permissionsList;
    protected $affectedPerms = [];
    protected $currentScope;

    protected $enabled = true;

    protected $scopeManager = null;

    public function __construct(ScopeManager $manager)
    {
        $this->scopeManager = $manager;
        $this->currentScope = self::DEFAULT_SCOPE;
    }

    public function __call($name, $params)
    {
        if (strpos($name, 'with') === 0) {
            // with params sugar
            $cutName = Str::camel(substr($name, strlen('with')));
            return $this->with([$cutName => $params[0]]);
        }
        throw new MethodNotFoundException;
    }

    public function off()
    {
        $this->enabled = false;
        return $this;
    }

    public function on()
    {
        $this->enabled = true;
        return $this;
    }

    public function setConfig(RbacConfig $config)
    {
        $this->config = $config->get();
        return $this;
    }

    public function setUser($user, array $roles = [])
    {
        $this->user = $user;
        if ($roles)
            $this->setRoles($roles);
        return $this;
    }

    public function setRoles(array $roles)
    {
        foreach ($roles as $role) {
            if (!empty($this->config[$role]['type']) && $this->config[$role]['type'] == self::TYPE_ROLE) {
                $this->roles[] = $role;
            }
        }
        $this->initRolePermissions();
        return $this;
    }

    protected function initRolePermissions()
    {
        foreach ($this->roles as $role) {
            $this->buildPermissionsList($role);
        }
    }

    protected function buildPermissionsList($role)
    {
        if ($this->hasPermission($role)) {
            return false;
        }
        foreach ($this->resolveRole($role) as $plainRole) {
            $this->addPermission($plainRole);
            if (!empty($this->config[$role]['children'])) {
                foreach ($this->config[$role]['children'] as $child) {
                    foreach ($this->resolveRole($child) as $plainRole) {
                        $this->addPermission($plainRole);
                        $this->buildPermissionsList($plainRole);
                    }
                }
            }
        }
        return true;
    }

    protected function addPermission($name)
    {
        $this->permissionsList[$name] = isset($this->config[$name]) ? $this->config[$name] : [];
        return $this;
    }

    protected function removePermission($name)
    {
        unset($this->permissionsList[$name]);
        return $this;
    }

    protected function hasPermission($name)
    {
        return isset($this->permissionsList[$name]);
    }

    protected function resolveRole($role)
    {
        if (!$this->isWildCarded($role)) {
            return [$role];
        }
        if ($role == '*') {
            return array_keys($this->config);
        }
        $list = [];
        foreach ($this->config as $key => $item) {
            $pattern = str_replace('*', '.*?', $role);
            if (preg_match('|^' . $pattern . '$|', $key)) {
                $list[] = $key;
            }
        }
        return $list;
    }

    protected function isWildCarded($role)
    {
        return strpos($role, '*') !== false;
    }

    public function getPermissions()
    {
        return $this->permissionsList;
    }

    public function isAllow($rights)
    {
        $isAllow = false;
        $args = func_get_args();
        foreach ($args as $arg) {
            if (!is_array($arg)) {
                $arg = [$arg];
            }
            $isAllow = true;
            foreach ($arg as $one) {
                if (!$this->isAllowOne($one)) {
                    $isAllow = false;
                    break;
                }
            }
            if ($isAllow)
                break;
        }
        $this->cleanWith();
        return $isAllow;
    }

    public function cleanWith()
    {
        $this->currentScope = self::DEFAULT_SCOPE;
        $this->scopeManager->clean($this->currentScope);
        return $this;
    }

    protected function isAllowOne($right)
    {
        if (!$this->enabled) {
            return true;
        }
        if ($this->isWildCarded($right)) {
            $resolved = $this->resolveRole($right);
            return $this->isAllow($resolved);
        }
        $params = [];
        $isAllow = false;
        if ($this->scopeManager->has($this->currentScope)) {
            $params = $this->scopeManager->get($this->currentScope);
        }
        if (!empty($this->permissionsList[$right])) {
            $permission = $this->permissionsList[$right];
            $isAllow = true;
            if (!empty($permission['bizRule']) && is_callable($permission['bizRule'])) {
                $isAllow = call_user_func_array($permission['bizRule'], [$this->user, $params]);
            }
        }
        if (!$isAllow && !empty($permission['bizRuleOr']) && is_callable($permission['bizRuleOr'])) {
            $isAllow = call_user_func_array($permission['bizRuleOr'], [$this->user, $params]);
        }
        return $isAllow;
    }

    public function with($params)
    {
        if (is_array($params)) {
            $this->scopeManager->add($this->currentScope, $params);
        } elseif (is_string($params)) { // may be scope?
            $this->scope($params);
        }
        return $this;
    }

    public function getCurrentScope()
    {
        return $this->currentScope;
    }

    public function scope($name)
    {
        $this->currentScope = $name;
        return $this;
    }

    public function forget($name = null)
    {
        $this->scopeManager->clean($name);
        return $this;
    }

}