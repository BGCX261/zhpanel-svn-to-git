<?php
if (!isset($pdo)) {
	$pdo = new ZSqlite();
}

class Template{
	var $html		= '';
	var $code		= array();
	var $const_regexp 	= "([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)";
	var $var_regexp 	= "((\\\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)(\-\>[a-zA-Z0-9_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)*(\[[a-zA-Z0-9_\-\.\\\"\\\'\[\]\$\x7f-\xff]+\])*)";

	function build($tplfile, $objfile) {
		$this->tplfile = $tplfile;
		$this->loadtemplate($tplfile);
		$this->parse();
		$this->writeobjfile($objfile);
		$this->clearmemory();
	}

	function clearmemory() {
		$this->html = '';
		$this->code = array();
	}

	function parse() {
		$this->html = preg_replace("/([\n\r]+)\t+/s", "\\1", $this->html);
		$this->html = preg_replace("/\<\!\-\-\{(.+?)\}\-\-\>/s", "{\\1}", $this->html);

		$this->html = preg_replace("/\{lang\s+(.+?)\}/ies", "\$this->languagevar('\\1')", $this->html);
		$this->html = preg_replace("/\{t\s+(.+?)\}/ies","t('\\1')", $this->html);
		$this->html = preg_replace("/\{s\s+(.+?)\}/ies","getStatus('\\1')", $this->html);
		$this->html = preg_replace("/\{b\s+(.+?)\}/ies","mkButton('\\1')", $this->html);
		$this->html = preg_replace("/\{bar\s+(.+?)\}/ies","mkPercentBar('\\1')", $this->html);

		$this->html = preg_replace("/[\n\r\t]*\{((template\s|eval\s|echo\s|iif|loop\s|if\s|elseif\s|else(?=\})|\\$([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)|\/if|\/loop)[^}]*?)\}[\n\r\t]*/ies", "\\\$this->transform_lable('\\1')", $this->html);

		$this->html = preg_replace("/{$this->var_regexp}/ies", "\$this->addquote('<?php echo \\1 ?>')", $this->html);

		if($count = count($this->code)) {
			for($i = 0; $i < $count; $i++) {
				$this->html = str_replace("<[\tTE_CODE_N_{$i}\t]>", $this->code[$i], $this->html);
			}
		}

		$this->html = str_replace(' ?><?php ', '', $this->html);

		return true;
	}

	function transform_lable($str) {
		list($lable) = explode(' ', $str);
		switch(trim($lable)) {
			case 'if':
				$str = preg_replace("/if\s+(.*)/ies", "\$this->addquote('<?php if(\\1) { ?>')", $str);
				break;
			case 'else':
				$str = '<?php } else { ?>';
				break;
			case 'elseif':
				$str = preg_replace("/elseif\s+(.*)/ies", "\$this->addquote('<?php } elseif(\\1) { ?>')", $str);
				break;
			case '/if':
				$str = '<?php } ?>';
				break;
			case 'loop':
				$str = preg_replace("/loop\s+(\S+)\s+(\S+)/ies", "\$this->addquote('<?php if(is_array(\\1)){\n\tforeach(\\1 as \\2) {\n ?>')", $str);
				$str = preg_replace("/loop\s+(\S+)\s+(\S+)\s+(\S+)/ies", "\$this->addquote('<?php if(is_array(\\1)){\nforeach(\\1 as \\2 => \\3) { ?>'", $str);
				break;
			case '/loop':
				$str = '<?php }}?>';
				break;
			case 'template':
				$str = '<?php include template(\''.trim(substr($str, 9)).'\'); ?>';
				break;
			case 'eval':
				$str = '<?php '.trim(substr($str, 5)).'; ?>';
				break;
			case 'echo':
				$str = '<?php echo "'.trim(str_replace("echo","",$str)).'"; ?>';
				break;
			default:
				if(substr($str, 0, 1) == '$') {
					$str = '<?php echo '.$this->addquote($str).' ?>';
				} else {
					$new = preg_replace("/\{{$this->const_regexp}\}/s", "<?php echo \\1; ?>", $str);
					$str = $new == $str ? '{'.$str.'}' : $new;
				}
		}

		$this->code[] = $str;
		$count = count($this->code) - 1;
		return "<[\tTE_CODE_N_{$count}\t]>";
	}

	function addquote($var) {
		return str_replace("\\\"", "\"", preg_replace("/\[([a-zA-Z0-9_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\]/s", "['\\1']", $var));
	}

	function languagevar($var) {
		global $language,$self;
		$return = $self[$var] = isset($language[$var]) ? $language[$var] : '!$var!';
		return $return;
	}

	function writeobjfile($objfile) {
		if($this->writefile($objfile, $this->html) === false) {
			exit('Template obj file: '.$objfile. '  can\'t be writen');
		}
		return true;
	}

	function loadtemplate($filename) {
		$this->html = $this->loadfile($filename);
		if($this->html === false) {
			exit('Template file: '.$this->tplfile. ' not found or can\'t be read');
		}
		return true;
	}

	function loadfile($filename, $local = 1) {
		$return = false;
		if(function_exists('file_get_contents')) {
			$return = @file_get_contents($filename);
		} elseif($local && $fp = @fopen($filename, 'r')) {
			$return = @fread($fp, filesize($filename)); fclose($fp);
		} elseif(!$local) {
			$return = @implode('', file($filename));
		}
		return $return;
	}

	function writefile($filename, $data) {
		$return = false;
		if(function_exists('file_put_contents')) {
			$return = @file_put_contents($filename, $data);
		} elseif($fp = @fopen($this->objfile, 'wb')) {
			@flock($fp, LOCK_EX);
			$return = fwrite($fp, $data); fclose($fp);
		}
		return $return;
	}
}

class ZUser {
	function getUsers($start=0, $limit=0, $reseller=0) {
		global $pdo;
		$reseller = intval($reseller);
		$sql_where = $reseller ? " WHERE pid=$reseller" : '';
		$sql_limit = $limit ? " LIMIT $start, $limit" : '';
		$sql = "SELECT * FROM user $sql_where $sql_limit";
		return $pdo->fetchAll($sql);
	}

	function suspendUser($name, $state) {
		global $pdo;
		$row = array('state'=>$state);
		$rs1 = $pdo->update('user', $row, " user='$name'");
		$rs2 = $pdo->update('site', $row, "owner='$name'");
		$rs3 = true;//syncVhosts();
		sync();
		return $rs1 && $rs2 && $rs3;
	}

	function getUser($name) {
		global $pdo;
		$sql = "SELECT * FROM user WHERE user='$name'";
		return $pdo->fetchRow($sql);
	}

	function getUid($name) {
		global $pdo;
		$sql = "SELECT uid FROM user WHERE user='$name'";
		return $pdo->fetchOne($sql, 'uid');
	}
	
	function getRole($name) {
		global $pdo;
		$sql = "SELECT admin AS role FROM user WHERE user='$name'";
		return $pdo->fetchOne($sql, 'role');
	}

	function chkUsername($username, $lenlimit='{1,10}') {
		if (!preg_match("/^[a-z][0-9a-z]$lenlimit$/", $username)) {
			return 'invalid';
		} else {
			global $pdo;
			$sql = "SELECT COUNT(*) AS rows FROM user WHERE user='$username'";
			return !$pdo->fetchOne($sql, 'rows');
		}
	}

	function getlastuid() {
		global $pdo;
		$sql = "SELECT max(uid) AS lastuid FROM user WHERE user='$name'";
		return $pdo->fetchOne($sql, 'lastuid');
	}

	function chkPassword($password) {
		// TODO password strength checking
		return strlen($password)>0;
	}

	function chpasswd($user, $pass) {
		global $pdo;
		if (PHP_OS=='WINNT') {
			$uid = getuid($user);
			if ($uid < 1000 || $uid > 10000) return false;
			$p = "/\n$user:([^:]*:\d+):0:46714:7:::/";
			$shadow = getfile('/etc/shadow');
			//backupConf('shadow');
			$str = file_get_contents($shadow);
			if(!preg_match($p, $str, $out)) return false;
			$newpass = getcrypted($pass);
			$last_modified = ceil(time()/3600/24);
			$from = "\n$user:".$out[1];
			$to = "\n$user:$newpass:$last_modified";
			$str = str_replace($from, $to, $str);
			return file_put_contents($shadow, $str);
		} else {
			$pass = getcrypted($pass);
			$cmd = "usermod -p '$pass' $user 2>&1";
			$res = `$cmd`;
			if ($res) {
				setmsg($res, 'error');
			} else {
				return true;
			}
		}
		sync();
		$update = array('pass'=>$pass);
		return $pdo->update('user', $update, " user='$user'");
	}

	function chpasswd_request($user, $pass) {
		global $pdo;
		$row = array('pass'=>$pass);
		$data = serialize((object)array('action'=>'mod', 'user'=>$user, 'pass'=>$pass));
		call('user', $data);
		return $pdo->update('user', $row, "user='$user'");
	}

	function adduser_request($user, $pass, $package) {
		global $pdo;
		$data = serialize((object)array('action'=>'add','user'=>$user, 'pass'=>$pass, ''=>$domain));
		sync();
		call('user', $data);
		$row = array('user'=>$user, 'pass'=>$pass, 'package'=>$package, 'home'=>"/home/$user", 'created'=>date('Y-m-d H:i:s'));
		return $pdo->insert('user', $row);
	}

	function removeUser($name, $rmFtp=1, $rmSite=1, $rmContent=1, $rmDatabase=1) {
		global $pdo;
		$result = $pdo->delete('user', "user='$name'");
		$data = serialize((object)array('action'=>'del', 'user'=>$name));
		if($rmFtp) $result = $pdo->delete('ftp', " owner='$name'");// && syncFtpUsers() && $result;
		if($rmSite) $result = $pdo->delete('site', " owner='$name'");// && syncVhosts() && $result;
		if($rmContent) $result = $result;
		if ($rmDatabase && function_exists('mysql_connect')) {
			$db = mysql_connect(DB_HOST, DB_USER, DB_PASS) or die(mysql_error());
			mysql_query("DROP DATABASE IF EXISTS `$name`");
			mysql_query("DROP USER '$name'@'".DB_HOST."'");
			mysql_close($db);
		}
		sync();
		call('user', $data);
		call('apache', 'restart');
		return true;
	}

}

class ZPackage {
	function getPackage($id) {
		global $pdo;
		$sql = "SELECT * FROM package WHERE id='$id'";
		return $pdo->fetchRow($sql);
	}

	function suspendPackage($id, $state) {
		global $pdo;
		$row = array('state'=>$state);
		$res = $pdo->update('package', $row, " id=$id");
		sync();
		return $res;
	}

	function getPackages($name_only=false, $state=1) {
		global $pdo;
		$select = $name_only ? 'name' : '*';
		$state_filter = $state=='all' ? '' : "WHERE state='$state'";
		$sql = "SELECT * FROM package $state_filter";
		return $pdo->fetchAll($sql);
	}

	function insertPackage($id, $package) {
		global $pdo;
		return $pdo->insert('package', $package, " id='$id'");
	}

	function updatePackage($id, $package) {
		global $pdo;
		$res = $pdo->update('package', $package, " id='$id'");
		sync();
		return $res;
	}

	function removePackage($id) {
		global $pdo;
		$res = $pdo->delete('package', " id='$id'");
		sync();
		return $res;
	}

	function getUserPackage($user) {
		global $pdo;
		$sql = "SELECT package FROM user WHERE user='$user'";
		return ZPackage::getPackage($pdo->fetchOne($sql, 'package'))->name;
	}

	function setUserPackage($user, $package) {
		global $pdo;
		$update = array('package'=>$package);
		$res = $pdo->update('user', $update, " user='$user'");
		sync();
		return $res;
	}
}

class ZFtp {
	function getFtpAccounts($owner='') {
		global $pdo;
		if($owner) $where = "WHERE owner='$owner'";
		else $where='';
		$sql = "SELECT * FROM ftp $where";
		return $pdo->fetchAll($sql);
	}

	function suspendFtpUser($name, $state) {
		global $pdo;
		$row = array('state'=>$state);
		$res = $pdo->update('ftp', $row, " user='$name'");// && syncFtpUsers();
		sync();
		return $res;
	}

	function checkExistence($ftpuser) {
		global $pdo;
		$sql = "SELECT COUNT(*) AS rows FROM ftp WHERE user='$ftpuser'";
		return $pdo->fetchOne($sql, 'rows');
	}

	function getFtpAccount($name) {	// trust the name passed here
		global $pdo;
		$sql = "SELECT * FROM ftp WHERE user='$name'";
		return $pdo->fetchRow($sql);
	}

	function chkFtpRoot($path, $owner) {
		$path = "/home/$owner/$path";
		return (false===strpos($path, './'));
	}

	function addFtpAccount($owner, $ftpuser, $passwd, $dir='', $writable) {
		global $pdo;
		$user = ZUser::getUser($owner);
		//print_r($user);
		$row = array('owner'=>$owner, 'user'=>$ftpuser, 'uid'=>$user->uid, 'gid'=>$user->gid, 'pass'=>$passwd, 'home'=>$dir, 'writable'=>$writable, 'created'=>date('Y-m-d H:i:s'), 'gecos'=>date('Y-m-d H.i.s'));
		$res = $pdo->insert('ftp', $row);// && syncFtpUsers();
		sync();
		return $res;
	}

	function updateFtpAccount($owner, $ftpuser, $passwd='', $dir='', $writable=true) {
		global $pdo;
		$user = ZUser::getUser($owner);
		$row = array('owner'=>$owner, 'uid'=>$user->uid, 'gid'=>$user->gid, 'pass'=>$passwd, 'home'=>$dir, 'writable'=>$writable, 'updated'=>date('Y-m-d H:i:s'), 'gecos'=>date('Y-m-d H.i.s'));
		$row = array_merge($_REQUEST, $row);
		if(!$passwd) unset($row['pass']);
		$res = $pdo->update('ftp', $row, "user='$ftpuser'");// && syncFtpUsers();
		sync();
		return $res;
	}

	function removeFtpAccount($user) {
		global $pdo;
		$res = $pdo->delete('ftp', "user='$user'");
		sync();
		return $res;
	}
}

class ZVhosts {
	function getDomains() {
		global $pdo;
		$sql = "SELECT name FROM site";
		$domains = $pdo->fetchList($sql, 'name');
		$aliases = ZVhosts::getAliases('all');
		return array_merge($domains, $aliases);
	}

	function getVhosts($where='') {
		global $pdo;
		if($where) $where_sql=" WHERE $where ";
		$sql = "SELECT * FROM site {$where_sql} ORDER BY type DESC, name ASC";
		return $pdo->fetchAll($sql);
	}

	function saveVhosts($entries) {
		$ignores = array('_comment','action');
		$arr = array();
		$arr[] = 'NameVirtualHost *';
		$arr[] = "\n";
		// per virtual host
		foreach ($entries as $e) {
			$arr[] = "#{$e->ServerName}_start";
			$arr[] = '<VirtualHost *>';
			// per setting
			foreach ($e as $k=>$v) {
				if (in_array($k, $ignores)) continue;
				if ($k=='sub') {
					// per sub section, e.g., <Directory />
					foreach($v as $kk=>$vv) {
						$arr[] = "\t<{$vv->start_tag}>";
						// per setting in sub setting
						foreach($vv as $kkk=>$vvv) {
							if ($kkk=='start_tag' || $kkk=='end_tag'){
								continue;
							}
							// might have variables in same name
							foreach((array)$vvv as $value) {
								$arr[] = "\t\t{$kkk} \t {$value}";
							}
						}
						$arr[] = "\t<{$vv->end_tag}>";
					}   // one directory end
				} else {   // all directories end
					$arr[] = "\t{$k}\t{$v}";    // not a sub section, just a normal setting
				}
			} // one virtual host end
			$arr[] = '</VirtualHost>';
			$arr[] = "#{$e->ServerName}_end";
			$arr[] = "\n";
		} // all finished

		return file_put_contents(HTTPD_CONF, join("\n",$arr));
	}

	function getVhost($domain) {
		global $pdo;
		$sql = "SELECT * FROM site WHERE name='$domain'";
		return $pdo->fetchRow($sql);
	}

	function removeVhost($domain) {
		global $pdo;
		$res = $pdo->delete('site', "name='$domain'");// && syncVhosts();
		sync();
		return $res;
	}

	function suspendVhost($domain, $state=0) {
		global $pdo;
		$res = $pdo->update('site', array('state'=>$state), "name='$domain'");
		sync();
		return $res;
	}

	function listMyVhosts($name_only=false) {
		global $pdo, $me;
		$sel = $name_only?'name':'*';
		$sql = "SELECT $sel FROM site WHERE owner='$me'";
		if ($name_only) return $pdo->fetchList($sql, $sel);
		else return $pdo->fetchAll($sql);
	}

	function getAliases($domain) {
		global $pdo;
		if ('all'==$domain) {
			$sql = "SELECT aliases FROM site";
			$all_aliases = $pdo->fetchAll($sql);
			$str = '';
			foreach($all_aliases as $alias) {
				$str .= $alias->aliases.' ';
			}
			return array_unique(explode(' ', $str));
		} elseif($domain) {
			$sql = "SELECT aliases FROM site WHERE name='$domain'";
			return $pdo->fetchOne($sql, 'aliases');
		} else {
			return false;
		}
	}

	function chkDomain($domain) {
		if (in_array($domain, ZVhosts::getDomains())) return 'occupied';
		return (bool)preg_match("/^[a-z0-9\-\.\*]*\.[a-z0-9\-\.]+[a-z0-9]$/i",$domain);
	}

	function getCbandUsers() {
		$str = file_get_contents(CBAND_CONF);
		$p = "/<CBandUser\s+([^>]+)>/";
		preg_match_all($p, $str, $matches);
		return $matches[1];
	}

	function addvhost($user, $domain) {
		global $pdo;
		$row = array('name'=>$domain, 'aliases'=>'www.'.$domain, 'owner'=>$user, 'created'=>date('Y-m-d H:i:s'), 'root'=>"/home/$user/$domain");
		$res = $pdo->insert('site', $row);
		if($res) {
			$data = (object)$row;
			call('vhost', serialize($data));
		}
		sync();
		return $res;
	}

	function addsite($user, $domain, $docroot) {
		global $pdo;
		$row = array('name'=>$domain, 'aliases'=>'www.'.$domain, 'owner'=>$user, 'created'=>date('Y-m-d H:i:s'), 'root'=>$docroot);
		$res = $pdo->insert('site', $row);// && syncVhosts();
		sync();
		return $res;
	}

	function addcband($user) {
		if(!in_array($user, getCbandUsers())) {
			$str = file_get_contents(ZH . "/tpl/conf/cband.conf");
			$str = str_replace('%USER%', $user, $str);
			// backup
			//backupConf('cband');
			$current = file_get_contents(CONF_CBAND);
			return file_put_contents(CONF_CBAND, $current.$str);
		}
		return false;
	}

	function mkwebroot($user, $domain) {
		global $home;
		return mkdir("$home/$user/$domain", 0711, true);
	}

	function mklogroot($user, $domain) {
		global $home;
		if(is_dir("$home/$user/logs/$domain")) return true;
		return mkdir("$home/$user/logs/$domain", 0711, true);
	}

	function changeDocRoot($domain, $newRoot) {
		global $pdo;
		$set = array('updated'=>date('Y-m-d H:i:s'), 'root'=>$newRoot);
		$res = $pdo->update('site', $set, "name='$domain'");// && syncVhosts();
		sync();
		return $res;
	}

	function setDefaultSite($name) {
		global $pdo;
		$pdo->update('site', array('type'=>0), '1');
		$res = $pdo->update('site', array('type'=>1, 'updated'=>date('Y-m-d H:i:s')), "name='$name'");
		sync();
		return $res;
	}

	function restart_request() {
		sync();
		touch(ZH_TMP."/run/is_dirty");
		touch(ZH_TMP."/run/restart_needed");
		setmsg( t('Apache restart is pending.'), 'notice' );
	}

	function restart() {
		sync();
		$n = php_uname('n');
		
		$httpd = PATH_HTTPD;

		$syntax = `$httpd -t 2>&1`;
		$syntax = trim($syntax);
		if ($syntax!=='Syntax OK') return $syntax;
		$restart = `$httpd -k restart 2>&1`;
		if(''==trim($restart)) return t('Httpd restarted.');
		else return $restart;
	}
}

class ZDatabase {
	function getDbUsers() {
		global $db;
		if(!$db) return array();
		//$db = mysql_connect(DB_HOST, DB_USER, DB_PASS) or die(mysql_error());
		$sql = "SELECT User FROM mysql.user WHERE Select_priv='N'";
		$res = mysql_query($sql);
		$users = array();
		$me = getmyname();
		while($user = mysql_fetch_object($res)) {
			if (!isadmin() && !startWith($user->User, $me)) continue;
			$users[] = $user->User;
		}
		//mysql_close($db);
		return $users;
	}

	function addmysqluser($user, $pass='') {
		global $dbhost;
		if(!function_exists('mysql_connect')) return false;
		$db = mysql_connect(DB_HOST, DB_USER, DB_PASS) or die(mysql_error());
		if (!$pass) {
			// Keep the old password
			$sql = "SELECT Password FROM mysql.user WHERE Host='$dbhost' AND User='$user'";
			$query = mysql_query($sql);
			$obj = mysql_fetch_object($query);
			$old_pass = $obj->Password;
			$pass_str = " PASSWORD '$old_pass' ";
		} else {
			$pass_str = " '$pass' ";
		}
		$a = mysql_query("CREATE USER '$user'@'$dbhost' IDENTIFIED BY $pass_str ");
		if (!$a) return false;
		/*$b = mysql_query("GRANT USAGE ON * . * TO '$user'@'$dbhost' IDENTIFIED BY '$pass'");
		if (!$b) return false;*/
		//$c = mysql_query("GRANT ALL PRIVILEGES ON `$user\_%` . * TO '$user'@'$dbhost'");
		//if (!$c) return false;
		/*$c = mysql_query("GRANT ALL PRIVILEGES ON `$user` . * TO '$user'@'$dbhost'");
		if (!$c) return false;
		$d = mysql_query("CREATE DATABASE `$user` ;");
		if (!$d) return false;*/
		mysql_close($db);
		return true;
	}
}

class ZShell {
	function getShells() {
		return array('bash', 'disabled');
	}
	function getUserShell($user) {
		return getUser($user)->shell;
	}

	function setUserShell($user, $shell) {
		global $pdo;
		shell_exec("usermod -s /zh/bin/shell_{$shell} $user");
		$row = array('shell'=>$shell);
		return $pdo->update('user', $row, "user='$user'");
	}
}

class ZFile {
	function xCopy($source, $destination, $child=1){
		if(!is_dir($source)){
			echo("Error:the $source is not a direction!");
			return 0;
		}
		if(!is_dir($destination)){
			mkdir($destination,0777);
		}

		$handle=dir($source);
		while($entry=$handle->read()) {
			if(($entry!=".")&&($entry!="..")){
				if(is_dir($source."/".$entry)){
					if($child)
					xCopy($source."/".$entry,$destination."/".$entry,$child);
				} else {
					copy($source."/".$entry,$destination."/".$entry);
				}
			}
		}
		return 1;
	}

	function xchown($mypath, $uid, $gid) {
		$d = opendir ($mypath) ;
		while(($file = readdir($d)) !== false) {
			if ($file != "." && $file != "..") {
				$typepath = $mypath . "/" . $file ;
				if (filetype ($typepath) == 'dir') {
					xchown($typepath, $uid, $gid);
				}
				chown($typepath, $uid);
				chgrp($typepath, $gid);
			}
		}
		return true;
	}

	function fappend($filename, $content) {
	    $fp = fopen($filename, 'a');
	    $result = fwrite($fp, "$content\n");
	    fclose($fp);
	    return $result;
	}

	function getfile($filename) {
		global $fakeroot;
		return $fakeroot.$filename;
	}

	function getAllDirs($in_dir, $max_levels=3, $ignore=array('.','..','.svn','CVS','logs')) {
		if(!$in_dir) return false;
		$arr = array();
		$in_dir = rtrim($in_dir, '/');
		if (!$max_levels) return false;
	    if (!is_dir($in_dir)) return false;
		$d = dir($in_dir);
		while( $f = $d->read() ) {
			if(in_array($f, $ignore)) continue;
			if(!is_dir($in_dir.'/'.$f)) continue;
			$arr[] = realpath($in_dir.'/'.$f);
			$sub = ZFile::getAllDirs($in_dir.'/'.$f, $max_levels-1);
			if(is_array($sub)) $arr = array_merge($arr,$sub);
		}
		$d->close();
		return $arr;
	}

	function getUserDirs($user) {
		global $fakeroot;
		$arr = ZFile::getAllDirs($fakeroot.'/home/'.$user);
	    if(!$arr) $arr = array();
	    array_unshift($arr, '~/');
		foreach($arr as $k=>$v) {
			$arr[$k] = str_replace('\\','/',str_replace(realpath($fakeroot.'/home/'.$user),'~', $v));
		}
		return $arr;
	}
}

class ZSystem {
	function getSettings() {
		global $pdo;
		return $pdo->fetchRow("SELECT * FROM system WHERE id=1");
	}

	function updateSettings($data) {
		global $pdo;
		return $pdo->update('system', $data, 'id=1');
	}
}

class ZSqlite {
	/**
	 * PDO Object instance
	 *
	 * @var PDO
	 */
	var $pdo;

	function ZSqlite() {
		$this->pdo = new PDO("sqlite:".ZH_TMP."/zhpanel.db");
	}

	function query($sql) {
		return $this->pdo->query($sql);
	}

	function fetchAll($sql) {
		$sth = $this->pdo->prepare($sql);
		if(!$sth) return $this->error();
		$sth->execute();
		return $sth->fetchAll(PDO::FETCH_OBJ);
	}

	function fetchRow($sql) {
		$sth = $this->pdo->prepare($sql);
		if(!$sth) return $this->error();
		$sth->execute();
		return $sth->fetch(PDO::FETCH_OBJ);
	}

	function fetchList($sql, $col) {
		$arr = $this->fetchAll($sql);
		$ret = array();
		foreach($arr as $v) {
			$ret[] = $v->$col;
		}
		return $ret;
	}

	function fetchOne($sql, $col) {
		$sth = $this->pdo->prepare($sql);
		if(!$sth) return $this->error();
		$sth->execute();
		$arr = $sth->fetch();
		return $arr[$col];
	}

	function delete($table, $where) {
		$sql = "DELETE FROM $table WHERE ".$where;
		$sth = $this->pdo->prepare($sql);
		if(!$sth) return $this->error();
		$sth->execute();
		return $sth->rowCount();
	}

	function insert($table, $row) {
		$sql = "SELECT sql FROM sqlite_master WHERE type='table' AND name='$table' LIMIT 1";
		$table_structure = $this->fetchOne($sql, 'sql');
		preg_match_all("#\[([^\]]+)\]#", $table_structure, $out);
		$tbl_cols = $out[1];
		$row = (array) $row;
		$columns = array_uintersect($tbl_cols, array_keys($row), 'strcmp');
		$values = array();
		foreach ($columns as $v) {
			$values[] = $row[$v];
		}
		$columns = join(',',$columns);
		$values = join("','", $values);
		$sql = "INSERT INTO $table ($columns) VALUES ('$values')";
		$sth = $this->pdo->prepare($sql);
		if(!$sth) return $this->error();
		$sth->execute();
		return $sth->rowCount();
	}

	function update($table, $row, $where='1') {
		$sql = "SELECT sql FROM sqlite_master WHERE type='table' AND name='$table' LIMIT 1";
		$table_structure = $this->fetchOne($sql, 'sql');
		preg_match_all("#\[([^\]]+)\]#", $table_structure, $out);
		$tbl_cols = $out[1];
		$row = (array) $row;
		$columns = array_uintersect($tbl_cols, array_keys($row), 'strcmp');
		$arr = array();
		foreach ($columns as $v) {
			$arr[] = "$v='{$row[$v]}'";
		}
		$set = join(',', $arr);
		$sql = "UPDATE $table SET $set WHERE $where";
		$sth = $this->pdo->prepare($sql);
		if(!$sth) return $this->error();
		$sth->execute();
		return $sth->rowCount();
	}

	function error() {
		global $sqlite_errors;
		$error = $this->pdo->errorInfo();
		echo '<h3>Database Error</h3>';
		echo sprintf('<b>Error</b>: %s<br /><b>Message</b>: %s', $sqlite_errors[$error[1]], $error[2]);
		die();
		return false;
	}
}

$sqlite_errors = array(
	0 => 'Successful result',
	1 => 'SQL error or missing database',
	2 => 'An internal logic error in SQLite',
	3 => 'Access permission denied',
	4 => 'Callback routine requested an abort',
	5 => 'The database file is locked',
	6 => 'A table in the database is locked',
	7 => 'A malloc() failed',
	8 => 'Attempt to write a readonly database',
	9 => 'Operation terminated by ()',
	10 => 'Some kind of disk I/O error occurred',
	11 => 'The database disk image is malformed',
	12 => '(Internal Only) Table or record not found',
	13 => 'Insertion failed because database is full',
	14 => 'Unable to open the database file',
	15 => 'Database lock protocol error',
	16 => '(Internal Only) Database table is empty',
	17 => 'The database schema changed',
	18 => 'Too much data for one row of a table',
	19 => 'Abort due to contraint violation',
	20 => 'Data type mismatch',
	21 => 'Library used incorrectly',
	22 => 'Uses OS features not supported on host',
	23 => 'Authorization denied',
	100 => '() has another row ready',
	101 => '() has finished executing'
);

class ZPager {
	var $first;
	var $last;
	var $next;
	var $prev;
	var $current;
	function ZPager($page, $total) {
		$this->current = $page;
		$this->first = 1;
		$this->last = ceil($total/PERPAGE);
		$this->next = $page+1>$this->last ? $this->last : $page+1;
		$this->prev = $page-1<1 ? 1 : $page-1;
	}

	function getHtml() {
		$html[] = $this->getLink($this->first);
		$html[] = $this->getLink($this->prev);
		$html[] = $this->current;
		$html[] = $this->getLink($this->next);
		$html[] = $this->getLink($this->last);
		return join(' ', $html);
	}

	function getLink($page) {
		$get = parse_str($_SERVER['QUERY_STRING']);
		$get['page'] = $page;
		$link = '?'.http_build_query($get);
		return "<a href='$link'>$page</a>";
	}
}

class OS {
	/* system user */
	function useradd($user) {
		$PASS = $user->pass;
		$USER = $user->user;
		$SHELL = $user->shell;
		$HOME = $user->home;
		$CGI_SYS = $zh."/etc/fcgi";
		$pass_crypted = crypt($PASS);
		zexec( "useradd -m -d $HOME -s $SHELL -p '$pass_crypted' $USER", "Adding user account..." );
		zexec( "cp -r $CGI_SYS/TEMPLATE $CGI_SYS/$USER", "Copying template files..." );
		zexec( "sed -i 's/TEMPLATE/$USER/g' $CGI_SYS/$USER/*", "Updating template files..." );
		zexec( "chown -R $USER.$USER $CGI_SYS/$USER", "Fixing ownership..." );
		zexec( "chmod -R 755 $CGI_SYS/$USER", "Fixing permissions..." );
		zexec( "chmod 711 $HOME", "Fixing home directory ownership..." );
	}

	function userdel($user) {
		$USER = $user->user;
		zexec( "mv -f /home/$USER /home/backup/", 'Backup user\'s files...');
		zexec( "userdel -rf $USER", 'Deleting user account...');
		zexec( "rm -rf $CGI_SYS/$USER", "Deleting user's fcgid wrappers..." );
	}
	function usermod($user) {
		$PASS = $user->pass;
		$USER = $user->user;
		$SHELL = $user->shell;
		$HOME = $user->home;
		$pass_crypted = crypt($PASS);
		zexec( "usermod -d $HOME -s $SHELL -p '$pass_crypted' $USER", 'Updating user...' );
		zexec( "chmod 711 $HOME", "Fixing home directory ownership..." );
	}
	/* service control */
	function ftpctl() {
		$cmd = "/zh/bin/ctl/proftpd ";
		switch($data) {
			case 'start':
			case 'stop':
			case 'restart':
				$cmd .= $data;
				break;
			default:
				return false;
		}
		return shell_exec($cmd);
	}
	function httpdctl() {
		$cmd = "/zh/bin/ctl/apache2 ";
		switch($data) {
			case 'start':
			case 'stop':
			case 'restart':
			case 'graceful':
			case 'graceful-stop':
			case 'startssl':
			case 'sslstart':
			case 'start-SSL':
			case 'configtest':
			case 'status':
			case 'fullstatus':
				$cmd .= $data;
				break;
			default:
				return false;
		}
		return shell_exec($cmd);
	}
	function mysqlctl() {
	}
	/* permissions */
	function permfix() {
	}
}