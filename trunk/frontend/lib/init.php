<?php
//if(function_exists('eaccelerator')) :
//	eaccelerator();
//else:
define("ZH", realpath(dirname(__FILE__)."/../"));
define("DEBUG",true);


ob_start('ob_gzhandler');

if (DEBUG) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL ^ E_NOTICE);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}
$z_start_time = microtime(true);

// magic quotes
$gpc = get_magic_quotes_gpc();
foreach($_REQUEST as $k=>$v) {
	unset($$k);	// unset the register global variables
	if (is_array($v)) continue;	// do not touch the deep arrays
	if($gpc) $_REQUEST[$k] = stripslashes($v);
}
unset($_GET, $_POST);	// unset gp

require_once 'defines.php';
require_once 'functions.php';
require_once 'classes.php';

// index menu
$style = new stdClass();
//$menus = array('index','package','user','ftp','db','login','logout');
$arr = glob('*.php');
foreach($arr as $v) {
    $f = substr($v, 0, -4);
    $style->$f = '';
}

$task = @$_REQUEST['task'];
$task = preg_replace("/[^a-z0-9]+/i", "", $task);
$basename = basename($_SERVER['PHP_SELF'],'.php');
if($task=='login') $basename = 'login';
$style->$basename = ' class="here"';

//addStylesheet('simple.css');
$title = 'Zhpanel v' .$zh_version;

session_id();
session_start();

isset($_SESSION['zmessage']) || $_SESSION['zmessage']='';
isset($_SESSION[$sess_key]) || $_SESSION[$sess_key]=array();

$me = getmyname();
$isadmin = isadmin();
$role = ZUser::getRole($me);
$token = token();
$switch_from = isswitch();

if ($task!=='login' && $task!=='logout') {
	if (!$me) {
		redirect('misc.php?task=login');
	}
}

$sys = $pdo->fetchRow("SELECT * FROM system WHERE id=1");

//print_r($sys);

//$lang = parse_ini_file(ZH."/tpl/{$sys->theme}/lang.ini.php", false, INI_SCANNER_RAW);
include ZH . "/tpl/{$sys->theme}/lang.ini.php";


