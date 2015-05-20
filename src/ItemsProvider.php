<?php
namespace SmartCrowd\Rbac;

class ItemsProvider implements ItemsProviderInterface
{
    public function get()
    {
        return [
            /* roles */
            'user' => [
                'type' => Item::TYPE_ROLE,
                'description' => 'Рядовой пользователь',
                'bizRule' => function ($params) {
                    return true;
                },
                'data' => [],
                'children' => [
                    'news.*'
                ],
            ],
            /* permissions */
            'news.manage' => [
                'type' => Item::TYPE_PERMISSION,
                'description' => 'Редактор новостей',
                'bizRule' => null,
                'children' => [
                    'news.*',
                ],
            ],
            'news.use' => [
                'type' => Item::TYPE_PERMISSION,
                'description' => 'Читатель новостей',
                'bizRule' => function ($params) {
                    return $params['model']->status == 1; // новость опубликована
                },
                'children' => [
                    'news.view.all',
                    'news.view.own',
                ],
            ],

            'news.view.all' => [
                'type' => Item::TYPE_PERMISSION,
                'description' => 'Просмотр всех новостей',
                'bizRule' => function ($params) {
                    return true;
                },
            ],
            'news.view.own' => [
                'type' => Item::TYPE_PERMISSION,
                'description' => 'Просмотр собственных новостей',
                'bizRule' => function ($params) {
                    return $this->user->id == $params['model']->user_id; // владелец новости
                },
            ],
        ];
    }
}