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
 * Or you can bind only prefix for controller:
 *
 *     Rbac::controller('Auth\\AuthController', 'authorisation');
 *
 * this mean that Rbac will try to find permission
 * with name 'authorisation.<action>' for each controller
 * action, for example 'authorisation.postlogin' or 'authorisation.getlogout'
 *
 *
 * If you not bind permission directly or through prefix Rbac will try to find
 * needed permission by itself. For example, for action UsersController@show, 'users.show' will be searched
 */