<?php

namespace SmartCrowd\Rbac\Middleware;

use Illuminate\Support\Facades\Auth;
use SmartCrowd\Rbac\Contracts\RbacManager;
use SmartCrowd\Rbac\Facades\Rbac;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class RbacMiddleware
{
    /**
     * @var Rbac
     */
    private $manager;

    public function __construct(RbacManager $rbacManager)
    {
        $this->manager = $rbacManager;
    }

    /**
     * Run the request filter.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @param array $permissions
     * @return mixed
     */
    public function handle($request, \Closure $next, $permissions = [])
    {
        $route = $request->route();

        if (empty($permissions)) {
            $permissions = $this->resolvePermission($route);
        }

        if (!is_array($permissions)) {
            $permissions = [$permissions];
        }
        foreach ($permissions as $permission){
            if (!Auth::check() || !$this->manager->checkAccess(Auth::user(), $permission, $route->parameters())) {
                throw new AccessDeniedHttpException;
            }
        }

        return $next($request);
    }

    private function resolvePermission($route)
    {
        $rbacActions     = $this->manager->getActions();
        $rbacControllers = $this->manager->getControllers();

        $action = $route->getAction();

        $actionNameSlash = str_replace($action['namespace'], '', $action['uses']);
        $actionName  = ltrim($actionNameSlash, '\\');
        $actionParts = explode('@', $actionName);

        if (isset($rbacActions[$actionName])) {
            $permissionName = $rbacActions[$actionName];
        } elseif (isset($rbacControllers[$actionParts[0]])) {
            $permissionName = $rbacControllers[$actionParts[0]] . '.' . $actionParts[1];
        } else {
            $permissionName = $this->dotStyle($actionName);
        }

        return $permissionName;
    }

    private function dotStyle($action)
    {
        return str_replace(['@', '\\'], '.', str_replace('controller', '', strtolower($action)));
    }

}