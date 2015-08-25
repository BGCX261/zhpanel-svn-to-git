<?php
require_once 'lib/init.php';

$norender = false;
initVars('name','desc','password','domain','shell','package');

switch($task) {
	case 'add':
		isadmin() || setmsg(t('Permission Dennied.'));
		header('Cache-Control: private');
		$token = token();
		$packages = ZPackage::getPackages();
		if (checktoken()){
			$username = $_REQUEST['name'];
			$password = $_REQUEST['password'];
			$domain = $_REQUEST['domain'];
			$package = intval($_REQUEST['package']);
			$shell = $_REQUEST['shell'];
			$break = false;
			// check username
			if( true!==($res=ZUser::chkUsername($username)) ) {
				$err['name'] = $res=='invalid' ?
					t('Username Invalid. No uppercase, not starting with number, less than 7 chars.') :
					t('Username Occupied.');
				$break = true;
			}
			// check password
			if( true!==($res=ZUser::chkPassword($password)) ) {
				$err['password'] = t('Password can not be empty.');
				$break = true;;
			}
			// check domain
			if( true!==($res=ZVhosts::chkDomain($domain)) ) {
				$err['domain'] = t('Domain Invalid.');
				if ($res=='occupied') $err['domain'] = t('Domain Occupied.');
				$break = true;
			}
			if ($break) break;
			// execute!
			$r1 = ZUser::adduser_request($username, $password, $package);
			$r2 = ZVhosts::addvhost($username, $domain);
			$r3 = ZDatabase::addmysqluser($username, $password);
			setmsg(t('Adduser request pending. It will take a few minutes to take into effect.'), 'notice');
		}
		break;
	case 'edit':
		$token = token();
		$name = isset($_REQUEST['name']) ? $_REQUEST['name'] : '';
		if (!isadmin() && $me!=$name) {
			setmsg(t('Permission Denied.'));
			//break;
		}
		$name = preg_replace("/[^a-z0-9]+/i","", $name);
		if (!$name) redirect('user.php');
		$package = ZPackage::getUserPackage($name);
		$packages = ZPackage::getPackages();
		$shells = ZShell::getShells();
		$user = ZUser::getUser($name);
		if (!$user) setmsg(t('No such user or this user is waiting for activation.'));
		$op = @$_REQUEST['op'];
		if (checktoken()) {
			if ('chpasswd'==$op) {
				$new = $_REQUEST['password'];
				$res = ZUser::chpasswd_request($name, $new);
				$message = t("Password modification request submited. Please wait for a few minutes.");
				setmsg($message, 'notice','self');
			}
			if ('setpkg'==$op) {
				isadmin() || setmsg(t('Permission Dennied.'));
				$new = intval($_REQUEST['package']);
				$res = ZPackage::setUserPackage($name, $new) && syncCBand();
				$package_name = ZPackage::getPackage($new)->name;
				$message = t("Package for $name has been changed to ")."[$package_name].";
				setmsg($message, 'notice','self');
			}
			if ('shell'==$op) {
				isadmin() || setmsg(t('Permission Dennied.'));
				$new = $_REQUEST['shell'];
				$res = ZShell::setUserShell($name, $new);
				$message = t("Shell for $name has been changed to ")."[$new].";
				setmsg($message, 'notice','self');
			}
			if ('suspend'==$op) {
				if(ZUser::suspendUser($name, !intval($_REQUEST['suspend']))) setmsg('', 'notice');
				else setmsg(t('Error'));
			}
			if ('remove'==$op) {
				header("Location: ?task=remove&name=$name&token={$_REQUEST['token']}");exit;
			}
		}
		break;
	case 'switch':
		$name = isset($_REQUEST['name']) ? $_REQUEST['name'] : '';
		$name = preg_replace("/[^a-z0-9]+/i","", $name);
		$user = ZUser::getUser($name);
		if (!$user) setmsg(t('No such user.'));
		$_SESSION[$sess_key]['switch'] = $name;
		setmsg(t('Switched to ').$name, 'notice');
		break;
	case 'leave':
		unset($_SESSION[$sess_key]['switch']);
		setmsg(t('Switched to Administrator mode.'), 'notice');
		break;
	case 'sync':
		$response = syncQuota();
		setmsg(t('Users data updated!'), 'notice');
		break;
	case 'remove':
		$token = token();
		$name = isset($_REQUEST['name']) ? $_REQUEST['name'] : '';
		if (!isadmin() && $me!=$name) {
			setmsg(t('Permission Denied.'));
			//break;
		}
		$name = preg_replace("/[^a-z0-9]+/i","", $name);
		if (!$name) redirect('user.php');
		$user = ZUser::getUser($name);
		if (checkToken()) {
			if(ZUser::removeUser($name)) {
				setmsg(t('Removed!'), 'notice');
			} else {
				setmsg(t('Error'));
			}
		}
		break;
	case 'list':
	default:
		$task = 'list';
		$start = 0;
		$limit = 20;
		if ($role==0) {
			// user
			redirect('?task=edit&name='.$me);
			//$users = array(ZUser::getUser($me));
		} elseif ($role==1) {
			// admin
			$users = ZUser::getUsers($start, $limit);
		} else {
			// reseller
			$users = ZUser::getUsers($start, $limit, ZUser::getUser($me)->id);
		}
		foreach($users as $k=>$v) {
			$users[$k]->packageName = ZPackage::getPackage($v->package)->name;
		}
		break;
}

if(!$norender) include template('user');

