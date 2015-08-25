<?php
require_once 'lib/init.php';

initVars('name','password','dbname', 'op');

if (function_exists('mysql_connect'))
	$db = mysql_connect(DB_HOST, DB_USER, DB_PASS) or die(mysql_error());
else {
	$mysql_error = 'mysql driver is not installed.';
	$task = 'error';
}
$norender = false;
$break = false;

switch($task) {
	case 'add':
		header('Cache-Control: private');
		$token = token();
		// Check db user settings
		$sql = "SELECT COUNT(*) FROM mysql.user WHERE User LIKE '$me'";
		$query = mysql_query($sql);
		$result = mysql_fetch_row($query);
		if(!$result[0]) {
			$random_passwd = getRandomPassword();
			
			// create user
			if (ZDatabase::addmysqluser($me, $random_passwd)) {
				setmsg(t("Added."), 'notice');
			} else {
				setmsg( t('Database Error. ').mysql_error(), 'error' );
			}
		}
		if (!isadmin()) setmsg(t(''), 'warning');
		if (checktoken()) {
			$username = $_REQUEST['name'];
			$password = $_REQUEST['password'];
			$break = false;
			if (in_array($username, ZDatabase::getDbUsers())) {
				$err['name'] = t('Username Occupied.');
				$break = true;
			}
			if( true!==($res=ZUser::chkUsername($username, '+')) ) {
				if ($res=='invalid') {
					$err['name'] = t('Username Invalid. No uppercase, not starting with number, less than 7 chars.');
				}
				$break = true;
			}
			// check password
			if( true!==($res=ZUser::chkPassword($password)) ) {
				$err['password'] = t('Password can not be empty.');
				$break = true;;
			}
			if($break) break;

			// create user
			if (ZDatabase::addmysqluser($username, $password)) {
				setmsg(t('Added.'), 'notice');
			} else {
				setmsg( t('Database Error. ').mysql_error(), 'error' );
			}
		}
		break;
	case 'edit':
		$token = token();
		$name = isset($_REQUEST['name']) ? $_REQUEST['name'] : '';
		$name = preg_replace("/[^a-z0-9_]+/i","",$name);
		$prefix = $me.'_';
		if (!$name) redirect('db.php');
		$user = in_array($name, ZDatabase::getDbUsers()) ? $name : false;
		$op = $_REQUEST['op'];

		$sql = "SHOW DATABASES LIKE '$user\_%'";
		$result = mysql_query($sql) or die(mysql_error());
		$databases = array();
		while($row = mysql_fetch_row($result)) {
			$databases[] = $row[0];
		}
		/*$sql = "SHOW DATABASES LIKE '$user'";
		$result = mysql_query($sql) or die(mysql_error());
		$row = mysql_fetch_row($result);
		if ($row) $databases[] = $row[0];*/
		sort($databases);

		$op = $_REQUEST['op'];
		if (checktoken() && 'createdb'==$op) {
			if(!checkPackage('sql')) setmsg(t('The package limitation reached.'), 'warning', 'self');
			$dbname = $prefix.$_REQUEST['dbname'];
			// check password
			if( $prefix == $dbname ) {
				$err['dbname'] = t('Database name can not be empty.');
				setmsg($err['dbname'], 'error', 'self');
			}
			
			if(!checkPackage('sql')) setmsg(t('The package limitation reached.'), 'warning');
			if ($break) break;
			$sql = "CREATE DATABASE `$dbname`";
			$res = mysql_query($sql);
			$dbname_escaped = str_replace('_','\_', $dbname);
			$sql = "GRANT ALL PRIVILEGES ON `$dbname_escaped` . * TO '$user'@'$dbhost' WITH GRANT OPTION ";
			$res2 = mysql_query($sql);
			if($res && $res2) {
				setmsg( t("Database created."), 'notice', 'self' );
			} else {
				setmsg( mysql_error(), 'error', 'self' );
			}
		}
		if (checktoken() && 'chpasswd'==$op) {
			$pass = $_REQUEST['password'];
			$host = $_REQUEST['host'];
			$host || $host='localhost';
			// check password
			if( true!==($res=ZUser::chkPassword($pass)) ) {
				$err['password'] = t('Password can not be empty.');
				setmsg($err['password'], 'error', 'self');
			}
			if ($break) break;
			$pass = mysql_real_escape_string($pass);
			$sql = "SET PASSWORD FOR '$name'@'$host' = PASSWORD('$pass')";
			$res = mysql_query($sql);
			if($res) {
				setmsg( t("Password updated"), 'notice', 'self' );
			} else {
				setmsg( mysql_error() );
			}
		}
		if (checktoken() && 'host'==$op) {
			$host = $_REQUEST['host'];
			$old_host = $_REQUEST['old_host'];
			$host || $host='localhost';
			$user = $name;
			$dbhost = $host; // overwrites the global $dbhost
			// create user
			if (ZDatabase::addmysqluser($user)) {
				$sql = "DROP USER '$user'@'$old_host'";
				$res = mysql_query($sql);
				if (!$res) {
					setmsg( mysql_error() );
					break;
				}
				setmsg(t('Updated.'), 'notice');
			} else {
				setmsg( t('Database Error. ').mysql_error(), 'error' );
			}
		}
		if (checktoken() && ('deluser'==$op || 'remove'==$op)) {
			$deldb = isset($_REQUEST['deldb']);
			$host = $_REQUEST['host'];
			$host || $host='localhost';
			$user = $name;
			// delete user
			$sql = "DROP USER '$user'@'$host'";
			$res = mysql_query($sql);
			if (!$res) {
				setmsg( mysql_error() );
				break;
			}
			if($deldb) {
				foreach($databases as $dbname) {
					$sql = "DROP DATABASE `$dbname`;";
					$res = mysql_query($sql);
					if (!$res) {
						setmsg( mysql_error() );
					}
				}
			}
			setmsg(t('User Deleted!'), 'notice');
		}
		$sql = "SELECT mysql.user.Host FROM mysql.user WHERE User='$user'";
		$query = mysql_query($sql) or die(mysql_error());
		$user_obj = mysql_fetch_object($query);

		$sql = "SELECT mysql.db.Db FROM mysql.db WHERE User='$user'";
		$query = mysql_query($sql) or die(mysql_error());
		$obj = mysql_fetch_object($query);
		if (!isset($obj->Db)) $obj->Db = t('none');
		$user_obj->Db = $obj->Db;

		//$databases = join(', <br />', $databases);
		$del_confirm = t('Are you sure to DROP this database?');
		break;
	case 'drop':
		$name = isset($_REQUEST['name']) ? $_REQUEST['name'] : '';
		$user = preg_replace("/[^a-z0-9_]+/i","",$name);
		$dbname = isset($_REQUEST['db']) ? $_REQUEST['db'] : '';
		$dbname = preg_replace("/[^a-z0-9_]+/i","",$dbname);
		$me = getmyname();
		if (!isadmin() && $dbname!==$me && strpos($dbname,$me.'_')!==0) {
			setmsg( t('Permission Denied.') );
			break;
		}
		$sql = "DROP DATABASE `$dbname`;";
		$res = mysql_query($sql);
		if (!$res) {
			$error = mysql_error();
			setmsg($error, 'error', 'self');
		}
		setmsg(t('Database deleted!'), 'notice', "db.php?task=edit&name=$user");
		//redirect("?task=edit&amp;name=$user");
		break;
	case 'error':
		break;
	case 'flush':
		$sql = "FLUSH PRIVILEGES";
		mysql_query($sql);
		setmsg( t('FLUSH PRIVILEGES'), 'notice');
		// no break needed
	case 'list':
	default:
		$task = 'list';
		$users = ZDatabase::getDbUsers();
		break;
}

if(!$norender) include template('db');
if(is_resource($db)) mysql_close($db);
