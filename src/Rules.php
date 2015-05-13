<?php
namespace SmartCrowd\Rbac;

use SmartCrowd\Rbac\Rbac;

class Rules implements IRules
{
    public function get()
    {
        return [
            /* roles */
            'user' => [
                'type' => Rbac::TYPE_ROLE,
                'description' => 'Рядовой пользователь',
                'bizRule' => function ($user, $params) {
                    return true;
                },
                'data' => [],
                'children' => [
                    'news.*'
                ],
            ],
            /* tasks */
            'news.manage' => [
                'type' => Rbac::TYPE_TASK,
                'description' => 'Редактор новостей',
                'bizRule' => null,
                'children' => [
                    'news.*',
                ],
            ],
            'news.use' => [
                'type' => Rbac::TYPE_TASK,
                'description' => 'Читатель новостей',
                'bizRule' => function ($user, $params) {
                    return $params['model']->status == 1; // новость опубликована
                },
                'children' => [
                    'news.view.all',
                    'news.view.own',
                ],
            ],
            /* permissions */
            'news.view.all' => [
                'type' => Rbac::TYPE_PERMISSION,
                'description' => 'Просмотр всех новостей',
                'bizRule' => function ($user, $params) {
                    return true;
                },
            ],
            'news.view.own' => [
                'type' => Rbac::TYPE_PERMISSION,
                'description' => 'Просмотр собственных новостей',
                'bizRule' => function ($user, $params) {
                    return $user->id == $params['model']->user_id; // владелец новости
                },
            ],
        ];
    }
}