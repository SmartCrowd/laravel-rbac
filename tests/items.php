<?php
Rbac::permission('news.delete');
Rbac::permission('news.update');
Rbac::permission('news.manage', [
    'news.delete',
    'news.update'
]);
Rbac::permission('news.manage.own', ['news.manage'], function ($params) {
    return $this->user->id == $params['news']->author_id;
});

Rbac::role('admin', ['news.manage']);
Rbac::role('user', ['news.manage.own']);