<?php
define("ZH", realpath(dirname(__FILE__)."/../"));
require_once ZH."/lib/defines.php";

if ($argc==3) {
	//
	$zhd = new ZHDaemon();
	$func = $argv[1];
	$data = $argv[2];
	if(!method_exists($zhd, $func)) {echo 'No such func '.$func.'.';exit;}
	$zhd->$func($data);
	exit;
}

// 
require_once ZH."/lib/functions.php";
require_once ZH."/lib/classes.php";

// check for php.ini changes
//$sites = ZVhost::getVhosts();

// no changes
if ( !is_file(ZH_TMP."/run/is_dirty") ) exit(0);


error_log(date('Y-m-d H:i:s')."\n", 3, '/var/log/zhpanel.log');

class ZHDaemon {
	function apache($data) {
		$cmd = "/etc/init.d/apache2 ";
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

	function vhost($data) {
		$data = unserialize($data);
		if(!is_object($data) || !$data->name) return false;
		$dir = $data->root;
		if($dir && !is_dir($dir)) {
			mkdir($dir, 0750, true);	// Block other's access to here
			chgrp($dir, 'www');
			file_put_contents($dir.'/index.php', getTpl('userdefault', $data));
		}
		$cmd = "chown -R {$data->owner} $dir";
		return shell_exec($cmd);
	}

	function pureftpd($data) {
		$cmd = "/etc/init.d/pureftpd ";
		switch($data) {
			case 'start':
			case 'stop':
			case 'restart':
			case 'mkdb':
				$cmd .= $data;
				break;
			default:
				return false;
		}
		return shell_exec($cmd);
	}

	function proftpd($data) {
		$cmd = "/etc/init.d/proftpd ";
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

	function mysql($data) {
		$cmd = "/etc/init.d/mysql ";
		switch($data) {
			case 'start':
			case 'stop':
			case 'restart':
			case 'reload':
			case 'force-reload':
			case 'status':
				$cmd .= $data;
				break;
			default:
				return false;
		}
		return shell_exec($cmd);
	}

	function user($data) {
		$data = unserialize($data);
		if(!is_object($data) || !isset($data->user)) return false;
		$cmd = "/usr/sbin/";
		$result = false;
		$USER = $data->user;
		$PASS = $data->pass;
		$CGI_SYS = ZH_DATA . "/cgi-system";
		if (!is_dir($CGI_SYS)) {
			mkdir($CGI_SYS, 0700, true);
		}
		switch($data->action) {
			case 'add':
				log_msg( "Adding user account...");
				$pass_crypted = crypt($PASS);
				$cmd0 = "/usr/sbin/useradd -m -s /bin/bash -p '$pass_crypted' $USER";
				log_msg( $cmd0 );
				$result = shell_exec($cmd0);	// ADD USER
				//if(!$result) return;

				/*echo "Setting password...\n";
				$cmd0 = "echo '$USER:$PASS' | chpasswd";
				echo "$cmd0\n\n";
				$result = shell_exec($cmd0);	// SET PASSWORD
				//if(!$result) return;*/

				log_msg( "Copying template files..." );
				$cmd0 = "cp -r $CGI_SYS/TEMPLATE $CGI_SYS/$USER";
				log_msg( $cmd0 );
				$result = shell_exec($cmd0);	// MAKE FASTCGI WRAPPERS
				//if(!$result) return;

				log_msg( "Updating template files..." );
				$cmd0 = "sed -i 's/TEMPLATE/$USER/g' $CGI_SYS/$USER/*";
				log_msg( $cmd0 );
				$result = shell_exec($cmd0);		// DO UPDATE
				//if(!$result) return;

				log_msg( "Fixing ownership..." );
				$cmd0 = "chown -R $USER.$USER $CGI_SYS/$USER";
				log_msg( $cmd0 );
				$result = shell_exec($cmd0);		// FIX PERMISSIONS
				//if(!$result) return;
				
				log_msg( "Fixing permissions..." );
				$cmd0 = "chmod -R 755 $CGI_SYS/$USER";
				log_msg( $cmd0 );
				$result = shell_exec($cmd0);		// FIX PERMISSIONS
				//if(!$result) return;
				
				log_msg( "Fixing ownership..." );
				$cmd0 = "chmod 711 /home/$USER";
				log_msg( $cmd0 );
				$result = shell_exec($cmd0);		// FIX PERMISSIONS
				//if(!$result) return;
				
				$passwd = file_get_contents('/etc/passwd');
				preg_match("#\n$USER:x:(\d+):(\d+)#", $str, $out);
				$uid = $out[1];
				$gid = $out[2];
				global $pdo;
				$pdo->update('user', array('uid'=>$uid, 'gid'=>$gid), "user='$USER'");
				syncFtpUsers(false);
				global $zhd;
				$zhd->proftpd('restart');
				break;
			case 'mod':
				$pass_crypted = getcrypted($PASS);
				$cmd .= "/usr/sbin/usermod -p '$pass_crypted' $USER";
				$result = shell_exec($cmd);

				syncFtpUsers(false);
				global $zhd;
				$zhd->proftpd('restart');
				break;
			case 'del':	// DANGEROUS
				log_msg( 'Backup user\'s files...' );
				$cmd0 = "mv -f /home/$USER /home/backup/";
				log_msg( $cmd0 );
				$result = shell_exec($cmd0);	// DO A BACKUP FIRST
				
				log_msg( "Deleting user account..." );
				$cmd0 = "/usr/sbin/userdel -rf $USER";
				log_msg( $cmd0 );
				$result = shell_exec($cmd0);

				log_msg( "Deleting user's fcgid wrappers..." );
				$cmd0 = "rm -rf $CGI_SYS/$USER";
				log_msg( $cmd0 );
				$result = shell_exec($cmd0);
				break;
			case 'jail':
				$service .= $cmd;
				break;
			case 'unjail':
				$service .= $cmd;
				break;
			default:
				return false;
		}
		return $result;
	}
}

$zhd = new ZHDaemon();

$sql = "SELECT * FROM task WHERE state=0";
$data = $pdo->fetchAll($sql);

foreach($data as $t) {
	$func = $t->func;
	if(!method_exists($zhd, $func)) continue;
	$zhd->$func($t->data);
	$set = array('state'=>1, 'msg'=>'Done. '.date('Y-m-d H:i:s'));
	$pdo->update('task', $set, "func='{$func}' AND data='{$t->data}' AND id={$t->id}");
}

unlink(ZH_TMP."/run/is_dirty");


function log_msg($msg) {
	printf("[%s] %s\n", date('Y-m-d H:i:s'), $msg);
}