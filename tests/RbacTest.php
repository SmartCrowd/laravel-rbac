<?php

namespace SmartCrowd\Rbac;

use Orchestra\Testbench\TestCase;

class RbacTest extends TestCase
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
    }

    public function rules()
    {
        $admin = new User(1, ['admin']);
        $user = new User(2, ['user']);
        $entity1 = (object) ['author_id' => 2];
        $entity2 = (object) ['author_id' => 3];

        $ret = [];
        foreach ([/*'news', */'article'] as $name) {
            $ret = array_merge($ret, [
                [$admin, $entity1, $name . '.destroy', true],
                [$admin, $entity1, $name . '.update', true],
                [$admin, $entity2, $name . '.destroy', true],
                [$admin, $entity2, $name . '.update', true],
                [$user, $entity1,  $name . '.destroy', true],
                [$user, $entity1,  $name . '.update', true],
                [$user, $entity2,  $name . '.destroy', false],
                [$user, $entity2,  $name . '.update', false],
            ]);
        }
        
        return $ret;
    }

    /**
     * @dataProvider rules
     */
    public function testRbac($subject, $object, $action, $result)
    {
        $params = [];
        foreach (['news', 'article'] as $name) {
            $params[$name] = $object;
        }
        $this->assertEquals($result, $subject->allowed($action, $params));
    }

}