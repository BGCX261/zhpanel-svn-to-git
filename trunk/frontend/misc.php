<?php
if(isset($_REQUEST['f'])) {
	ob_start('ob_gzhandler');
	$f = $_REQUEST['f'];
	$ext = strrchr($f, '.');
	if ($ext=='.css') {
		$subdir = 'styles';
	} else if($ext=='.js') {
		$subdir = 'scripts';
	} else {
		setmsg('test');
	}
	$f = "assets/$subdir/$f";
	if (is_file($f)) {
		header("Content-type: text/css");
		header('Cache-Control: max-age=1000');
		header('Expires: Sat, 26 Jul 2017 05:00:00 GMT');
		echo file_get_contents($f);
		exit;
	}
} else {
	require_once 'lib/init.php';
}

$task || $task = 'tasks';
$norender = false;


$token = token();
switch($task) {
	case 'cron':
		header('Cache-Control: private');
		$times = array('hourly', 'daily', 'weekly', 'monthly');
		$op = @$_REQUEST['op'];
		
		if (!isadmin()) $users = array(getUser($me));
		else $users = ZUser::getUsers();
		
		$cron_lists = $pdo->fetchAll("SELECT * FROM cron");

		if(checkToken()) {
			if ('add'==$op) {
				$pdo->insert('cron', $_REQUEST);
				syncCrontab();
				setmsg(t('Save!'), 'notice', 'self');
			} elseif('edit'=='op') {
			}
		}

		
		$op = 'add';
		$token = token();
		break;
	case 'tpl':
		isadmin() || setmsg();
		$cwd = getcwd();
		chdir('tpl/conf');
		$files = glob('*.tpl');
		chdir($cwd);
		$f = @$_REQUEST['tpl'];
		if(!in_array($f, $files)) $f = $files[0];
		if (checkToken()) {
			$new_content = $_REQUEST['content'];
			if(file_put_contents('tpl/conf/'.$f, $new_content)) {
				setmsg(t('Saved!'), 'notice', 'self');
			}
		}
		$content = file_get_contents('tpl/conf/'.$f);
		break;
	case 'svn':
		$users = array();
		break;
	case 'random':	// random password
		$pass = getRandomPassword();
		$id = $_REQUEST['id'];
		break;
	case 'tasks':
		if(!isadmin()) break;
		if(checktoken()) {
			if($pdo->insert('task', $_REQUEST)) setmsg(t('Saved!'), 'notice', 'self');
		}
		$sql = "SELECT * FROM task WHERE state=0";
		$tasks = $pdo->fetchAll($sql);
		break;
	case 'login':
		if (checktoken()) {
			$user = addslashes($_REQUEST['user']);
			$pass = addslashes($_REQUEST['pass']);
			if (auth($user, $pass)) {
				$_SESSION[$sess_key]['myname'] = $user;
				//session_commit();
				setmsg(t('You\'ve successfully logged in.'), 'notice', 'index.php');
			} else {
				setmsg(t("Login Incorrect."));
			}
		}
		break;
	case 'logout':
		foreach($_SESSION as $k=>$v) {
			unset($_SESSION[$k]);
		}
		setmsg(t('You\'ve successfully logged out.'), 'notice', 'misc.php?task=login');
		break;
	case 'extra':
		break;
	case 'list':
	default:
		break;
}

if(!$norender) include template('misc');

