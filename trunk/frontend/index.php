<?php
require_once 'lib/init.php';

switch($task) {
	case 'settings':
		if (checkToken()) {
			if(updateSettings($_REQUEST)) setmsg(t('Settings saved.'), 'notice', 'self');
		}
		$sys = ZSystem::getSettings();
		$tpl_dir = ZH."/tpl";
		$d = dir($tpl_dir);
		$themes = array();
		while($f=$d->read()) {
			$param_file = "$tpl_dir/$f/param.ini";
			if(!is_file($param_file)) continue;
			$param = parse_ini_file($param_file);
			$themes[] = (object)$param;
		}
		break;
	case 'sync':
		syncSystemUsers();
		syncFtpUsers();
		syncVhosts();
		syncCBand();
		syncCrontab();
		setmsg(t('All data updated!'), 'notice');
		break;
    case 'ctl':
        if (!isadmin()){
            setmsg(t('Permission Denied'));
        }
        $a = strtolower($_REQUEST['a']);    // Application
        $o = $_REQUEST['o'];    // Operation
        if ($a=='apache' && in_array($o, array('start', 'stop', 'restart')) ) {
            $cmd = "/etc/init.d/apache2 $o";
        }
        elseif ($a=='proftpd' && in_array($o, array('start', 'stop', 'restart')) ){
            $cmd = "/etc/init.d/proftpd $o";
        }
        elseif ($a=='mysql' && in_array($o, array('start', 'stop', 'restart')) ){
            $cmd = "/etc/init.d/mysql $o";
        }
        else {
            $cmd = "Invalid command";
        }
        $res = shell_exec("$cmd 2>&1");
        setmsg(t('Response: ').$res, 'notice');
        break;
	case 'svn':
        if (!isadmin()){
            setmsg(t('Permission Denied'));
        }
        $cmd = "svn up ".ZH;
		$msg = `$cmd`;
		setmsg("<pre>$msg</pre>", 'notice');
		break;
	default:
		$user = $me;
		$group = $roles[$role];
		if(isadmin()) {
			if ($_REQUEST['refresh']==1 || !isset($_SESSION['services'])) {
				$_SESSION['services'] = getServicesStatus();
			}
			$services = $_SESSION['services'];
		}
		if (!isset($_SESSION['stats'])) {
			$_SESSION['stats'] = getStatistics();
		}
		$stats = $_SESSION['stats'];
		break;
}
include template('index');

//echo human2bytes('10GB')/1024;