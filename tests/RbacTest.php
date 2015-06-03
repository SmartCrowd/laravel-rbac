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
        $news = (object) ['author_id' => 2];
        $news2 = (object) ['author_id' => 3];

        return [
            [$admin, $news, 'news.delete', true],
            [$admin, $news, 'news.update', true],
            [$admin, $news2, 'news.delete', true],
            [$admin, $news2, 'news.update', true],
            [$user, $news, 'news.delete', true],
            [$user, $news, 'news.update', true],
            [$user, $news2, 'news.delete', false],
            [$user, $news2, 'news.update', false],
        ];
    }

    /**
     * @dataProvider rules
     */
    public function testRbac($subject, $object, $action, $result)
    {
        $this->assertEquals($result, $subject->allowed($action, ['news' => $object]));
    }

}