<?php
require_once 'lib/init.php';

$norender = false;

isadmin() || setmsg(t('Permission Dennied.'), 'error', './');
initVars('name','desc','space','bandwidth','site','ftp','sql','state','op');

switch($task) {
	case 'add':
		header('Cache-Control: private');
		$token = token();
		if (checktoken()) {
			$package = new stdClass();
			$package->name = strip_tags($_REQUEST['name']);
			$package->desc = strip_tags($_REQUEST['desc']);
			$package->space = $_REQUEST['space'];
			$package->bandwidth = $_REQUEST['bandwidth'];
			$package->site = intval($_REQUEST['site']);
			$package->ftp = intval($_REQUEST['ftp']);
			$package->sql = intval($_REQUEST['sql']);
			$package->state = isset($_REQUEST['state']);

			if (ZPackage::insertPackage($id, $package)) {
				setmsg(t("New Package Added."),'notice', 'package.php');
			}
		}
		break;
	case 'edit':
		header('Cache-Control: private');
		$token = token();
		$id  = intval($_REQUEST['id']);
		$package = ZPackage::getPackage($id);
		if (!$package)
			setmsg(t("No such package defined."),'warning', 'package.php');
		$op = $_REQUEST['op'];
		if(checktoken() && 'remove'==$op) {
			if(ZPackage::removePackage($id)) setmsg(t('Package Removed.'),'notice');
		}
		if (checktoken() && 'suspend'==$_REQUEST['op']) {
			if(ZPackage::suspendPackage($id, !intval($_REQUEST['suspend']))) setmsg('', 'notice');
			else setmsg(t('Error'));
		}
		if (checktoken() && 'edit'==$op) {
			$package = array();
			$package['name'] = strip_tags($_REQUEST['name']);
			$package['desc'] = strip_tags($_REQUEST['desc']);
			$package['space'] = $_REQUEST['space'];
			$package['bandwidth'] = $_REQUEST['bandwidth'];
			$package['site'] = intval($_REQUEST['site']);
			$package['ftp'] = intval($_REQUEST['ftp']);
			$package['sql'] = intval($_REQUEST['sql']);
			$package['state'] = intval($_REQUEST['state']);
			$package['updated'] = date('Y-m-d H:i:s');

			if (ZPackage::updatePackage($id, $package)) {
				setmsg(t("Package Updated."),'notice', 'package.php');
			}
		}
		break;
	case 'list':
	default:
		$task = 'list';
		$packages = ZPackage::getPackages(false, 'all');
		if($_REQUEST['get']=='json') {
			$arr = array('records'=>$packages, 'total'=>count($packages));
			echo json_encode($arr);
			$norender = true;
		}
		break;
}

if(!$norender) include template('package');
