<?php
Rbac::permission('news.destroy');
Rbac::permission('news.update');
Rbac::permission('news.manage', [
    'news.destroy',
    'news.update'
]);
Rbac::permission('news.manage.own', ['news.manage'], function ($params) {
    return $this->user->id == $params['news']->author_id;
});

Rbac::resource('article', 'ArticlesController', 'author_id');

Rbac::role('admin', [
    'news.manage',
    'article.manage' // from resource
]);
Rbac::role('user', [
    'news.manage.own',
    'article.manage.own',  // from resource
]);