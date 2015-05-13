<?php
namespace SmartCrowd\Rbac;

class RbacConfig
{
    protected $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function get()
    {
        return $this->config;
    }
}