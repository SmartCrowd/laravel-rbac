<?php

namespace SmartCrowd\Rbac\Middleware;

use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Auth;
use SmartCrowd\Rbac\Facades\Rbac;
use SmartCrowd\Rbac\Manager;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class RbacMiddleware
{
    /**
     * @var Rbac
     */
    private $manager;

    public function __construct(Manager $rbacManager)
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

        foreach ($permissions as $permission) {
            if (!Auth::check() || !$this->manager->checkAccess(Auth::user(), $permission, $route->parameters())) {
                throw new AccessDeniedHttpException;
            }
        }

        return $next($request);
    }

    private function resolvePermission(Route $route)
    {
        $rbacActions = $this->manager->getRepository()->getActions();

        $action = $route->getAction();

        $actionName = ltrim(str_replace($action['namespace'], '', $action['uses']), '\\');

        if (isset($rbacActions[$actionName])) {
            $permissionName = $rbacActions[$actionName];
        } elseif (!empty($action['as']) && config('rbac.useRouteName')) {
            $permissionName = $action['as'];
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