<?php
namespace SmartCrowd\Rbac;

class ScopeManager
{
    protected $scopes = [];

    public function store($name, $data)
    {
        $this->scopes[$name] = $data;
    }

    public function add($name, $data)
    {
        if ($this->has($name))
            $this->scopes[$name] = array_merge($this->scopes[$name], $data);
        else
            $this->store($name, $data);
    }

    public function get($name)
    {
        if ($this->has($name))
            return $this->scopes[$name];
        throw new UnknownScopeException('Unknown rbac parameters scope: ' . $name);
    }

    public function clean($name = null)
    {
        if (is_null($name))
            $this->scopes = [];
        elseif ($this->has($name)) {
            unset($this->scopes[$name]);
        } else
            throw new UnknownScopeException('Unknown rbac parameters scope: ' . $name);
    }

    public function has($name)
    {
        return isset($this->scopes[$name]);
    }
}