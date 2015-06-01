<?php

namespace SmartCrowd\Rbac;

use SmartCrowd\Rbac\Contracts\ItemsProviderInterface;

class ItemsProvider implements ItemsProviderInterface
{
    public function get()
    {
        return [

            'admin' => [
                'type' => Item::TYPE_ROLE,
                'children' => [
                    'news.manage',
                ],
            ],

            'user' => [
                'type' => Item::TYPE_ROLE,
                'children' => [
                    'news.manage.own',
                ],
            ],

            'news.manage' => [
                'type' => Item::TYPE_PERMISSION,
                'children' => [
                    'news.delete',
                    'news.update',
                ],
            ],

            'news.manage.own' => [
                'type' => Item::TYPE_PERMISSION,
                'rule' => function ($params) {
                    return $this->user->id == $params['news']->author_id;
                },
                'children' => [
                    'news.manage',
                ],
            ],

            'news.delete' => [
                'type' => Item::TYPE_PERMISSION
            ],

            'news.update' => [
                'type' => Item::TYPE_PERMISSION
            ]
        ];
    }
}