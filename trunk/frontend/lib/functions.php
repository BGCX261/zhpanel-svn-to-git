<?php
// encrypt
function authcode($string, $operation='ENCODE', $key = '') {
	$key = md5($key ? $key : CRYPT_KEY );$key_length = strlen($key);$string = $operation == 'DECODE' ? pack('H'.strlen($string),$string) : substr(md5($string.$key), 0, 8).$string;
	$string_length = strlen($string);$rndkey = $box = array();$result = '';
	for($i = 0; $i <= 255; $i++) {$rndkey[$i] = ord($key[$i % $key_length]);$box[$i] = $i;}
	for($j = $i = 0; $i < 256; $i++) {$j = ($j + $box[$i] + $rndkey[$i]) % 256;$tmp = $box[$i];$box[$i] = $box[$j];$box[$j] = $tmp;}
	for($a = $j = $i = 0; $i < $string_length; $i++) {$a = ($a + 1) % 256;$j = ($j + $box[$a]) % 256;$tmp = $box[$a];$box[$a] = $box[$j];$box[$j] = $tmp;$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));}
	if($operation == 'DECODE') {
		if(substr($result, 0, 8) == substr(md5(substr($result, 8).$key), 0, 8)) return substr($result, 8);else return '';
	} else {
		return bin2hex($result);
	}
}

// template
function template($tpl) {
	global $sys;
	$tpl_dir = ZH . "/tpl";
	$cache_dir = ZH_TMP . "/tpl";
	if(!file_exists("$tpl_dir/{$sys->theme}/$tpl.tpl.php")) $theme='zh';
	else $theme = $sys->theme;
	$tplfile = "$tpl_dir/$theme/$tpl.tpl.php";
	$objfile = "$cache_dir/$theme.$tpl.cache.php";
	$langfile = "$tpl_dir/$theme/lang.ini.php";
	if (!file_exists($objfile) || filemtime($objfile)<max(filemtime($tplfile), filemtime($langfile))) {
		$t = new Template();
		$t->build($tplfile, $objfile);
		$t=null;
	}
	return $objfile;
}

// html helpers
function addScript($filename, $dir='assets/scripts') {
	global $tpl_headers;
	$line = "<script type=\"text/javascript\" src=\"{$dir}/{$filename}\"></script>";
	$tpl_headers[md5($line)] = $line;
}
function addStylesheet($filename, $dir='assets/styles') {
	global $tpl_headers;
	$line = "<link rel=\"stylesheet\" type=\"text/css\" href=\"{$dir}/{$filename}\" />";
	$tpl_headers[md5($line)] = $line;
}

// for php4
if (!function_exists('file_get_contents')) {
	function file_get_contents($filename) {
		$fp = fopen($filename, 'r');
		$str = @fread($fp, filesize($filename));
		fclose($fp);
		return $str;
	}
}
	
if (!function_exists('file_put_contents')) {
	function file_put_contents($filename, $data) {
		$fp = fopen($filename, 'w');
		$res = fwrite($fp, $str);
		fclose($fp);
		return $res;
	}
}

////////////////////////////////////////////////////////
// common functions
function getmyname() {
	global $sess_key;
	$sess = @$_SESSION[$sess_key];
	$me = isset($sess['switch']) ? $sess['switch'] : @$sess['myname'];
	return $me;
}

function getcrypted($password) {
	return crypt($password);
}

// tokens
function token() {
	$salt = uniqid('$1$zhtk');
	return crypt(CRYPT_KEY, $salt);
}

function checktoken($token='') {
	if(!$token) $token=isset($_REQUEST['token'])?$_REQUEST['token']:'';
	return crypt(CRYPT_KEY, $token)===$token;
}

// language
function t($str) {
	global $lang;
	return isset($lang[$str]) ? $lang[$str] : $str;
}

function getStatus($state) {
	global $token;
	$arr = explode(',', $state);
	$state = $arr[0];
	$name = $arr[1];
	$url = "?task=edit&amp;op=suspend&amp;suspend=$state&amp;$name&amp;token=\$token";
	$img = $state ? '<img src="assets/images/publish.png" />' : '<img src="assets/images/unpublish.png" />';
	return '<a href="'.$url.'">'.$img.'</a>';
}

function mkButton($str) {
	global $token;
	$arr = explode(',', $str);
	$type = $arr[0];
	$where = $arr[1];
	$no_token = @$arr[2];
	$alert = '';
	switch($type) {
		case 'edit':
			$url = "?task=edit&amp;$where";
			$img = '<img src="assets/images/edit.png" />';
			break;
		case 'delete':
			$url = "?task=edit&amp;op=remove&amp;$where";
			if(!$no_token) $url .= '&amp;token=$token';	// keep the character '$'
			$img = '<img src="assets/images/delete.png" />';
			$alert = ' onclick="if(!confirm(\''.t('Are you sure to remove it?').'\'))return false;"';
			break;
		case 'switch':
			$url = "?task=switch&amp;$where";
			$img = '<img src="assets/images/switch.png" />';
			break;
		default:
			break;
	}
	return '<a href="'.$url.'"'.$alert.' title="'.t($type).'">'.$img.'</a>';
}

function mkPercentBar($percent) {
	return '<div class="graph"><strong class="bar" style="width: '.$percent.'%;">'.$percent.'%</strong></div>';
}

//
function redirect($url) {
	header("Location: $url");exit;
}

function r($bool) {
	return $bool ? '<b class="ok">OK</b><br />' : '<b class="failed">FAILED</b><br />';
}

function isadmin($user=0) {
	global $sess_key, $admins;
	if (!$user) $user = getmyname();
	return ZUser::getUser($user)->admin==1;
}

function isswitch() {
	global $sess_key;
	$sess = $_SESSION[$sess_key];
	return isset($sess['switch']) ? $sess['myname'] : false;
}

function startWith($haystack, $needle) {
	return $haystack===$needle || substr($haystack, 0, strlen($needle)+1)==$needle.'_';
}

function setmsg($message, $level='error', $redirect='') {
	if($message) $_SESSION['zmessage'] = "<div class='z$level'>$message</div>";
	$redirect || $redirect = $_SERVER['PHP_SELF'];
	$redirect == 'self' && $redirect = '?' .$_SERVER['QUERY_STRING'];
	if($redirect!='noredirect') redirect($redirect);
}

function initVars() {
    global $err;
    $params = func_get_args();
    foreach($params as $param) {
        if (!isset($err[$param])) $err[$param] = '';
        if (!isset($_REQUEST[$param])) $_REQUEST[$param] = '';
    }
}

// Gets configuration template
function getTplCallback($data, $key) {
	return $data->$key;
}
function getTpl($name, $data=false) {
	$conf_dir = ZH."/tpl/conf";
	$str = file_get_contents($conf_dir.'/'.$name.'.tpl');
	if($data) $str = preg_replace("#{([^}]+)}#e", "getTplCallback(\$data, $1)", $str);
	return $str;
}

/* sync */
function sync() {
	syncFtpUsers();
	syncVhosts();
	syncCBand();
	syncSystemUsers();
	syncCrontab();
	syncQuota();
}
function syncFtpUsers($call=true) {
	global $pdo, $sys;
	$ftp1 = 'proftpd';
	$ftp2 = 'pureftpd';
	$line1 = array();
	$line2 = array();
	/* system users */
	//if (PHP_OS!='WINNT') {
		$file = ZFile::getfile('/etc/passwd');
		$str = file_get_contents($file);
		$p = "/([^:\n]*):([^:]*):([\d]{3,5}):([^:]*):([^:]*):([^:]*):([^:]*)\n/";
		preg_match_all($p,$str,$passwd);
		
		$names = join('|', $passwd[1]);
		//echo $names;
		$file = ZFile::getfile('/etc/shadow');
		$str = file_get_contents($file);
		$p = "/\n($names):([^:]*):/";
		preg_match_all($p,$str,$shadow);
		$pw = array();
		foreach($shadow[1] as $k=>$v) {
			$pw[$v] = $shadow[2][$k];
		}
		foreach($passwd[1] as $k=>$user) {
			$data = new stdClass();
			$data->user = $user;
			$data->pass = $pw[$user];
			if(strlen($data->pass)<4) continue;
			$data->uid = $passwd[3][$k];
			$data->gid = $passwd[4][$k];
			$data->home = $passwd[6][$k];
			$users[$user] = $data;
			$line1[] = getTpl($ftp1, $data);
			$line2[] = getTpl($ftp1, $data);
			unset($data);
		}
	//}
	$sql = "SELECT * FROM ftp WHERE state=1";
	$all_data = $pdo->fetchAll($sql);
	foreach($all_data as $data) {
		$data->pass = crypt($data->pass);
		$data->home = str_replace('~', "/home/".$data->owner, $data->home);
		$data->uid = $users[$data->owner]->uid;
		$data->gid = $users[$data->owner]->gid;
		if (!$data->uid) $data->uid=9467;
		if (!$data->gid) $data->gid=9467;
		$data->gecos = '';
		$line1[] = getTpl($ftp1, $data);
		$line2[] = getTpl($ftp2, $data);
	}
	if($call) call($sys->ftp_software, 'restart');
	file_put_contents(ZH_DATA .'/conf/ftp/'.$ftp1, join("\n", $line1)."\n");
	file_put_contents(ZH_DATA.'/conf/ftp/'.$ftp2, join("\n", $line2)."\n");
	return true;
}
function syncVhosts() {
	global $pdo;
	$sql = "SELECT * FROM site";
	$all_data = $pdo->fetchAll($sql);
	$dir = ZH_DATA."/conf/httpd";
	if (!is_dir($dir)) return false;
	$d = dir($dir);
	while( ($f=$d->read())!==false ) {
		if(strpos($f, '.conf')!==false) @unlink($dir.'/'.$f);
	}
	
	foreach($all_data as $data) {
		$file = $data->owner.'@'.$data->name.'.conf';
		if(!$data->state && !$data->type) $file.='.suspended';
		if($data->type) $file='000-'.$file;
		$prefix = $data->type ? "NameVirtualHost *\n" : "";
		if(!file_put_contents($dir.'/'.$file, $prefix.getTpl('vhost', $data)."\n")) return false;
	}
	call('apache', 'restart');
	return true;
}
function syncCBand() {
	global $pdo;
	$sql = "SELECT * FROM user";
	$all_users = $pdo->fetchAll($sql);
	$dir = ZH_DATA."/conf/cband";
	if (!is_dir($dir)) return false;
	$d = dir($dir);
	while( ($f=$d->read())!==false ) {
		if(strpos($f, '.conf')!==false) @unlink($dir.'/'.$f);
	}
	foreach($all_users as $k=>$v) {
		$data = new stdClass();
		$data->user = $v->user;
		$limit = ZPackage::getPackage($v->package)->bandwidth;
		$limit = $limit==-1?999999:$limit;
		if(preg_match("/^[\d\.]+$/", $limit)) $data->limit = $limit.'G';
		else $data->limit = $limit;
		$file = $v->user.'.conf';
		if(!file_put_contents($dir.'/'.$file, getTpl('cband', $data)."\n")) return false;
	}
	call('apache', 'restart');
	return true;
}
function syncSystemUsers() {
	global $pdo;
	$file = ZFile::getfile('/etc/passwd');
	$str = file_get_contents($file);
	$p = "/([^:\n]*):([^:]*):([\d]{3,5}):([^:]*):([^:]*):([^:]*):([^:]*)\n/";
	preg_match_all($p,$str,$passwd);
	
	$names = join('|', $passwd[1]);
	//echo $names;
	$file = ZFile::getfile('/etc/shadow');
	$str = file_get_contents($file);
	$p = "/\n($names):([^:]*):/";
	preg_match_all($p,$str,$shadow);
	$pw = array();
	foreach($shadow[1] as $k=>$v) {
		$pw[$v] = $shadow[2][$k];
	}
	foreach($passwd[1] as $k=>$user) {
		$data = new stdClass();
		$data->hash = $pw[$user];
		if(substr($data->hash, 0, 3)!='$1$') continue;
		$data->uid = $passwd[3][$k];
		$data->gid = $passwd[4][$k];
		$data->home = $passwd[6][$k];

		$pdo->update('user', $data, "user='$user'");
	}
}
function syncCrontab() {
	global $pdo;
	$file = ZH_DATA."/conf/crontab";
	if (!is_file($file)) return false;
	file_put_contents($file, "")!==false or die(t('can not write'));
	$cron_lists = $pdo->fetchAll("SELECT * FROM cron WHERE state=1");

	$time_arr = array('hourly'=>'19 * * * *', 'daily'=>'39 1 * * *', 'weekly'=>'39 2 * * 2', 'monthly'=>'39 3 13 * *');

	foreach($cron_lists as $cron) {
		if (!isset($time_arr[$cron->frequency])) continue;	// SKIP!
		$cron->frequency = $time_arr[$cron->frequency];
		$str = getTpl('crontab', $cron);
		file_put_contents($file, $str, FILE_APPEND);
	}
	return true;
}
function syncQuota() {
	global $pdo;
	$arr = file(ZFile::getfile('/etc/passwd'));
	$uids = array();
	foreach($arr as $v) {
		$tmp = explode(':', $v);
		$uids[$tmp[0]] = $tmp[2];
		$pdo->update('user', array('uid'=>$tmp[2], 'gid'=>$tmp[3]), "user='{$tmp[0]}'");
	}
	$sql = "SELECT * FROM user";
	$users = $pdo->fetchAll($sql);
	foreach($users as $v) {
		$user = $v->user;
		$quota = ZPackage::getPackage($v->package)->space;
		if(preg_match('/^[\d\.]+$/', $quota)) $quota .= 'G';
		$kb = intval(human2bytes($quota)/1024);
		$uid = $uids[$user];
		if($uid<9000) continue;
        if($quota==-1) $kb = 0;
		//var_dump($kb, $quota,human2bytes($quota.'GB'));echo '<hr>';
		shell_exec("quotaquery set {$uid} $kb,$kb,0,0");
		shell_exec("usermod -s /zh/bin/shell_{$v->shell} $user");
	}
	//exit;
}

function call($func, $data='') {
	$args = func_get_args();
	global $pdo;
	$row = $pdo->fetchOne("SELECT COUNT(*) AS Row FROM task WHERE func='$func' AND data='$data' AND state=0", 'Row');
	if ($row) {
		$pdo->delete('task', "func='$func' AND data='$data' AND state=0");
	}
	$data = array('func'=>$func, 'data'=>$data, 'msg'=>'pending');
	$pdo->insert('task', $data);
	touch(ZH_TMP.'/run/is_dirty');
}

function getRandomPassword($len=7) {
	$str = crypt(rand());
	$str = preg_replace('/[^a-z0-9]/', '', $str);
	return substr($str, 0, $len);
}

function getServicesStatus() {
	// Apache2
	$services = array();
	$apache = new stdClass();
	$apache->name = 'Apache';
	$app = basename(PATH_HTTPD);
	preg_match('/Server version:\s*(.*)/', `$app -V`, $out);
	$apache->version = $out[1];
	$uptime = trim(`ps -eo "%U %c %t" | grep $app | grep -v grep | grep root | head -n1`);
	$apache->uptime = array_pop(explode(" ", $uptime));
	$apache->state = strlen($apache->uptime)>1;
	$services[] = $apache;

	// proftpd
	$proftpd = new stdClass();
	$proftpd->name = 'Proftpd';
	preg_match('/Version:\s*(.*)/', `proftpd -V`, $out);
	$proftpd->version = $out[1];
	$uptime = trim(`ps -eo "%U %c %t" | grep proftpd | grep -v grep | head -n1`);
	$proftpd->uptime = array_pop(explode(" ", $uptime));
	$proftpd->state = intval(strlen($proftpd->uptime)>1);
	$services[] = $proftpd;

	// MySQL
	$mysql = new stdClass();
	$mysql->name = 'MySQL';
	preg_match('/Ver\s*(\S*)/', `mysqld -V`, $out);
	$mysql->version = $out[1];
	$uptime = trim(`ps -eo "%U %c %t" | grep mysqld | grep -v grep | head -n1`);
	$mysql->uptime = array_pop(explode(" ", $uptime));
	$mysql->state = strlen($mysql->uptime)>1;
	$services[] = $mysql;

	return $services;
}

function getStatistics() {
	global $me, $pdo;
	$user = ZUser::getUser($me);
	$pkg = ZPackage::getPackage($user->package);

	$stats = array();

	// Disk Usage
	$s = new stdClass();
	$s->name = t('Disk');
	$quota = explode(' ', `quotaquery get $user->uid`);
	$s->usage = intval($quota[0]);
	$pkg->space = intval($quota[2]);
	$s->limits = $pkg->space==0 ? t('unlimited') : $pkg->space;
	$calc = $pkg->space==0 ? 9999999999999 : $pkg->space;
	$s->percent = $calc ? round($s->usage*100/$calc) : 100;
	$s->limits==t('unlimited') || $s->limits = bytes2human($s->limits*1024);
	$s->state = sprintf("%s / %s ", bytes2human($s->usage*1024), $s->limits);
	$stats[] = $s;

	// Bandwidth statistics
	$url = 'http://u.eaxi.com/zh-cband-status/?xml';
	PHP_OS=='WINNT' && $url = 'data/cband.xml';
	$str = @file_get_contents($url);
	
	if($str) {
		$p = "#<$me>.*</$me>#isU";
		preg_match($p, $str, $out);
		$part = $out[0];
		preg_match("#<usages>.*</usages>#isU", $part, $out);
		$part = $out[0];
		preg_match("#<total>.*</total>#isU", $part, $out);
		$usages = $out[0];
		$unit = preg_replace("/\d/", "", $usages);
		$used = preg_replace("/\D/", "", $usages);
		$num = $unit=='KiB' ? 1024 : 1000;
		$usages = intval($used) * $num;	// convert to bytes

		$s = new stdClass();
		$s->name = t('Bandwidth');
		$s->usage = $usages;
		$s->limits = $pkg->bandwidth==-1 ? t('unlimited') : $pkg->bandwidth;
		$calc = $pkg->bandwidth==-1 ? 9999999999999 : $pkg->bandwidth*1024*1024*1024;	// in bytes
		$s->percent = $calc ? round($s->usage*100/$calc) : 100;
		$s->limits==t('unlimited') || $s->limits = bytes2human($s->limits*1024*1024*1024);
		$s->state = sprintf("%s / %s", bytes2human($s->usage), $s->limits);
		$stats[] = $s;
	}

	// FTP Accounts
	$s = new stdClass();
	$s->name = t('FTP');
	$sql = "SELECT COUNT(*) AS total FROM ftp WHERE owner='$me'";
	$total = $pdo->fetchOne($sql, 'total');
	$s->usage = $total;
	$s->limits = $pkg->ftp==-1 ? t('unlimited') : $pkg->ftp;
	$calc = $pkg->ftp==-1 ? 99999999 : $pkg->ftp;
	$s->percent = $calc ? round($s->usage*100/$calc) : 100;
	$s->state = sprintf("%s / %s", $s->usage, $s->limits);
	$stats[] = $s;

	// Independent sites
	$s = new stdClass();
	$s->name = t('Sites');
	$sql = "SELECT COUNT(*) AS total FROM site WHERE owner='$me'";
	$total = $pdo->fetchOne($sql, 'total');
	$s->usage = $total;
	$s->limits = $pkg->site==-1 ? t('unlimited') : $pkg->site;
	$calc = $pkg->site==-1 ? 99999999 : $pkg->site;
	$s->percent = $calc ? round($s->usage*100/$calc) : 100;
	$s->state = sprintf("%s / %s", $s->usage, $s->limits);
	$stats[] = $s;

	// Databases
	if (function_exists('mysql_connect') && !$_SESSION['no_mysql']) {
		$s = new stdClass();
		$s->name = t('Databases');
		$sql = "SHOW DATABASES LIKE '$me\_%'";
		$db = @mysql_connect(DB_HOST, DB_USER, DB_PASS);// or die(mysql_error());
		if(!$db) {
			$_SESSION['no_mysql'] = true;
			return $stats;
		}
		$result = mysql_query($sql) or die(mysql_error());
		$total = mysql_num_rows($result);
		$s->usage = $total;
		$s->limits = $pkg->sql==-1 ? t('unlimited') : $pkg->sql;
		$calc = $pkg->sql==-1 ? 99999999 : $pkg->sql;
		$s->percent = $calc ? round($s->usage*100/$calc) : 100;
		$s->state = sprintf("%s / %s", $s->usage, $s->limits);
		$stats[] = $s;
	}

	return $stats;
}

function bytes2human($bytes) {
	if ($bytes < 1024) return $bytes.'B';
	if ($bytes >= 1024 && $bytes < 1048576) return round($bytes / 1024, 2).'KB';
	if ($bytes >= 1048576 && $bytes < 1073741824) return round($bytes / 1048576, 2).'MB';
	if ($bytes >= 1073741824 && $bytes < 1099511627776) return round($bytes / 1073741824, 2).'GB';
	if ($bytes >= 1099511627776 && $bytes < 1125899906842624) return round($bytes / 1099511627776, 2).'TB';
}

function human2bytes($human) {
	$num  = preg_replace("/[^\d\.]+/", "", $human);
	$unit = preg_replace("/[^TGMK]+/", "", $human);
	if ($unit=='T') return 1099511627776*$num;
	if ($unit=='G') return 1073741824*$num;
	if ($unit=='M') return 1048576*$num;
	if ($unit=='K') return 1024*$num;
	return $num;
}

function checkPackage($type='') {
	global $me, $pdo;
	$usage = 0;
	if(!$type) $type=basename($_SERVER['PHP_SELF'], '.php');
	switch($type) {
		case 'site':
			$sql = "SELECT COUNT(*) AS usage FROM site WHERE owner='$me'";
			$usage = $pdo->fetchOne($sql, 'usage');
			break;
		case 'ftp':
			$sql = "SELECT COUNT(*) AS usage FROM ftp WHERE owner='$me'";
			$usage = $pdo->fetchOne($sql, 'usage');
			break;
		case 'space':
			break;
		case 'sql':
			$sql = "SHOW DATABASES LIKE '$me\_%'";
			$result = mysql_query($sql) or die(mysql_error());
			$usage = mysql_num_rows($result);
			break;
		case 'bandwidth':
			break;
		default:
			return false;
	}
	$limit = ZPackage::getPackage(ZUser::getUser($me)->package)->$type;
	//var_dump($type, $usage, $limit, $me);exit;
	return $limit==-1 || ($usage<$limit)/* || isadmin()*/;
}


function auth($user, $pass) {
	global $pdo;
	$sql = "SELECT count(*) AS rows FROM user WHERE user='$user' AND pass='$pass' AND state=1";
	$pass1 = $pdo->fetchOne($sql, 'rows');
	if ($pass1) return true;
	$sql = "SELECT hash FROM user WHERE user='$user' AND state=1";
	$hash = $pdo->fetchOne($sql, 'hash');
	return crypt($pass, $hash)==$hash;
}

function zlog($msg, $level='notice') {
	global $zh, $me;
	$filename = sprintf("%s/var/log/zh/%s.log", $zh, date('Y-m-d'));
	if(is_dir(dirname($filename))) mkdir(dirname($filename), 0700, true);
	$line = sprintf("[%s] [%s] [%s] %s\n", date("Y-m-d H:i:s"), $level, $me, $msg);
	error_log($line, 3, $filename);
}

function zexec( $cmd, $msg ) {
	if($msg) zlog($msg);
	zlog('Executing '.$cmd);
	return shell_exec($cmd);
}