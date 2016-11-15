<?php
/*
 * Bind your permissions to controller actions here.
 *
 *
 * You can directly bind one permission to one or many actions:
 *
 *     Rbac::action('UsersController@destroy', 'removeUser');
 *     Rbac::action([
 *         'UsersController@update',
 *         'UsersController@edit',
 *     ], 'editUser');
 *
 *
 * If you not bind permission directly Rbac will try to find
 * needed permission by itself. For example, for action UsersController@show, 'users.show' will be searched
 */