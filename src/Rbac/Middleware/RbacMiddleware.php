<?php

namespace SmartCrowd\Rbac\Middleware;

use Illuminate\Support\Facades\Auth;
use SmartCrowd\Rbac\Facades\Rbac;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class RbacMiddleware
{
    /**
     * @var Rbac
     */
    private $manager;

    public function __construct(Rbac $rbacManager)
    {
        $this->manager = $rbacManager;
    }

    /**
     * Run the request filter.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @param string|null $permission
     * @return mixed
     */
    public function handle($request, \Closure $next, $permission = null)
    {
        $route = $request->route();

        if (empty($permission)) {
            $permission = $this->resolvePermission($route);
        }

        if (!Auth::check() || !Auth::user()->allowed($permission, $route->parameters())) {
            throw new AccessDeniedHttpException;
        }

        return $next($request);
    }

    private function resolvePermission($route)
    {
        $rbacActions     = $this->manager->getActions();
        $rbacControllers = $this->manager->getControllers();

        $action = $route->getAction();

        $actionName  = str_replace($action['namespace'], '', $action['uses']);
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