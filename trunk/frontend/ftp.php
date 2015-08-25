<?php
require_once 'lib/init.php';

$norender = false;
initVars('name','password','docroot','writable','upload_bandwidth','download_bandwidth','upload_ratio','download_ratio','max_conn','files_quota','size_quota','auth_local_ips','refused_local_ips','auth_client_ips','refused_client_ips','time_restrictions');

switch($task) {
	case 'add':
		header('Cache-Control: private');
		$token = token();
		$prefix = $me.'_';
		$accounts = ZFtp::getFtpAccounts();
		if(!checkPackage()) setmsg(t('The package limitation reached.'), 'warning');
        $dirs = ZFile::getUserDirs($me);
		$users = ZUser::getUsers();
		if (checktoken()) {
			$username = $_REQUEST['name'];
			$owner = $me;
			$passwd = $_REQUEST['password'];
			$docroot = $_REQUEST['docroot'];
            $ftpuser = $owner.'_'.$username;
			$writable = isset($_REQUEST['writable']);
			$break = false;
			// check username
			if( true!==($res=ZUser::chkUsername($username)) ) {
				if ($res=='invalid') {
					$err['name'] = t('Username Invalid. No uppercase, not starting with number, less than 7 chars.');
					$break = true;
				}
			}
			if (ZFtp::checkExistence($ftpuser)) {
				$err['name'] = t('Username Occupied.');
				$break = true;
			}
			// check password
			if( true!==($res=ZUser::chkPassword($passwd)) ) {
				$err['password'] = t('Password can not be empty.');
				$break = true;
			}
			// check ftp
			if( true!==($res=ZFtp::chkFtpRoot($docroot, $owner)) ) {
				$err['docroot'] = t('Directory must be inside $HOME.');
				$break = true;
			}
			if ($break) break;
			//var_dump($ftpuser, $accounts);break;
			if (ZFtp::addFtpAccount($owner, $ftpuser, $passwd, $docroot, $writable)) {
				setmsg( t("Added."), 'notice' );
			}
		}
		break;
	case 'edit':
		$name = isset($_REQUEST['name']) ? $_REQUEST['name'] : '';
		$name = preg_replace("/[^a-z0-9_]+/i","",$name);
		if (!$name) redirect('ftp.php');
		$password = $_REQUEST['password'];
		$owner = strtok($name, '_');
		$users = ZUser::getUsers();
		$account = ZFtp::getFtpAccount($name);
		if (!$account) setmsg(t('No such user.'));
		$myuid = ZUser::getuid($owner);
        $dirs = ZFile::getUserDirs($owner);

        $docroot = trim($account->home);
        $docroot = str_replace("/home/$owner",'~',$docroot);

		if (checktoken()) {
			$op = $_REQUEST['op'];
			if ($op=='remove') {
				if(ZFtp::removeFtpAccount($name)) {
					setmsg(t('Ftp Account Deleted.'),'notice','ftp.php');
				}
			} else if ('suspend'==$op) {
				if(ZFtp::suspendFtpUser($name, !intval($_REQUEST['suspend']))) 
					setmsg('', 'notice');
				else 
					setmsg(t('Error'));
			} else if ($op=='edit') {
				foreach($_REQUEST as $k=>$v) {
					$_REQUEST[$k] = str_replace(':', '', $v);
				}
				if (ZFtp::updateFtpAccount($owner, $name, $password, $_REQUEST['docroot'], $_REQUEST['writable'])) {
                    setmsg(t('Ftp Account Modified.'), 'notice', 'ftp.php');
                }
			}
		}
		break;
	case 'sync':
		if(syncFtpUsers()) {
			setmsg(t('Ftp configuration updated!'), 'notice');
		} else {
			setmsg(t('Can not write the configuration file.'), 'error');
		}
		break;
	case 'list':
	default:
		$task = 'list';
		$owner = isadmin() ? '' : $me;
		$accounts = ZFtp::getFtpAccounts($owner);
		break;
}

if(!$norender) include template('ftp');

