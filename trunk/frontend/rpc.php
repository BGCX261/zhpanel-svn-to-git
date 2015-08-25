<?php
require_once 'lib/init.rpc.php';


require_once 'lib/phprpc/phprpc_server.php';


class RPC {
	/* session management */
	function show_login() {
		return 'please login first';
	}
	function do_login() {
		return 'do_loginzz';
	}
	function do_logout() {}

	/* user management */
	function user_add() {}
	function user_del() {}
	function user_show() {}
	function user_edit() {}
	function user_list() {
		$users = ZUser::getUsers();
		foreach($users as $k=>$v) {
			$users[$k]->packageName = ZPackage::getPackage($v->package)->name;
		}
		return json_encode($users);
	}

	/* package management */
	/*function pkg_add() {}
	function pkg_del() {}
	function pkg_show() {}
	function pkg_edit() {}
	function pkg_list() {}*/

	/* ftp management */
	function ftp_add() {}
	function ftp_del() {}
	function ftp_show() {}
	function ftp_edit() {}
	function ftp_list() {
		$owner = isadmin() ? '' : getmyname();
		$accounts = ZFtp::getFtpAccounts($owner);
		return json_encode($accounts);
	}

	/* db management */
	function db_add() {}
	function db_del() {}
	function db_show() {}
	function db_edit() {}
	function db_list() {}

	/* db user management */
	function db_user_add() {}
	function db_user_del() {}
	function db_user_show() {}
	function db_user_edit() {}
	function db_user_list() {}

	/* tpl management */
	/*function tpl_add() {}
	function tpl_del() {}
	function tpl_show() {}
	function tpl_edit() {}
	function tpl_list() {}*/

	/* task management */
	/*function task_add() {}
	function task_del() {}
	function task_show() {}
	function task_edit() {}
	function task_list() {}*/

	/* cron management */
	/*function cron_add() {}
	function cron_del() {}
	function cron_show() {}
	function cron_edit() {}
	function cron_list() {}*/

	/* svn management */
	/*function svn_add() {}
	function svn_del() {}
	function svn_show() {}
	function svn_edit() {}
	function svn_list() {}*/

	/* soft management */
	/*function soft_add() {}
	function soft_del() {}
	function soft_show() {}
	function soft_edit() {}
	function soft_list() {}*/
}
$rpc_sess_key = 'res';
$server = new PHPRPC_Server();
$rpc = new RPC();
/*
if(empty($_SESSION[$rpc_sess_key])) {
	$server->errno = 503;
	$server->errstr = 'Please login before calling';
	$server->sendError();
}
*/

$server->add(get_class_methods('RPC'), $rpc);
$server->start();
