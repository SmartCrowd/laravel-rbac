<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 11.07.15
 * Time: 15:34
 */

namespace SmartCrowd\Rbac;


use Illuminate\Support\Facades\Blade;
use Orchestra\Testbench\TestCase;

class BladeTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return ['SmartCrowd\Rbac\RbacServiceProvider'];
    }

    protected function getPackageAliases()
    {
        return [
            'Rbac' => 'SmartCrowd\Rbac\Facades\Rbac'
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('rbac.itemsPath', __DIR__ . '/items.php');
        $app['config']->set('rbac.actionsPath', __DIR__ . '/actions.php');
        $app['config']->set('rbac.shortDirectives', true);
    }

    public function testDirectives()
    {
        Blade::compile('test.blade.php');
        $this->assertEquals(
            file_get_contents(Blade::getCompiledPath('test.blade.php')),
            file_get_contents('test.compiled.php')
        );
    }

}