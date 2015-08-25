<?php
require_once 'lib/init.php';

$norender = false;
initVars('domain','docroot');
$error_documents = array(401, 403, 404, 500);

header('Cache-Control: private');

switch($task) {
	case 'add':
		if(!checkPackage('site')) setmsg(t('The package limitation reached.'), 'warning');
		$dirs = ZFile::getUserDirs($me);

		if (checktoken()) {
			$domain = $_REQUEST['domain'];
			$docroot = $_REQUEST['docroot'];
			// check domain
			$break = false;
			if( true!==($res=ZVhosts::chkDomain($domain)) ) {
				$err['domain'] = t('Domain Invalid.');
				if ($res=='occupied') $err['domain'] = t('Domain Occupied.');
				$break = true;
			}
			// check docroot
			if( !in_array($docroot, $dirs) ) {
				$err['docroot'] = t('Please select a valid document root.');
				$break = true;
			}
			$docroot = str_replace('~/', "/home/$me/", $docroot);
			if ($break) break;
			if (!ZVhosts::addsite($me, $domain, $docroot)) setmsg(t('Can not add this site to system.'));
			setmsg(t('New site created!'), 'notice');
		}
		break;
	case 'edit':
		$name = preg_replace("/[^0-9a-z\-\.]/", '', $_REQUEST['name']);
		if(!$name || $name=='default') $name = '';
		if (!in_array($name, ZVhosts::listMyVhosts(true)) && !isadmin()) setmsg(t('No such domain hosted.'));
		if (checktoken() && 'alias'==$_REQUEST['op']) {
			$alias = strtolower(trim($_REQUEST['alias']));
			$alias = preg_replace("/[^a-z0-9\.\-\*]+/", " ", $alias);
			$pieces = explode(' ', $alias);
			$removed = array();
			$aliases = ZVhosts::getAliases($name);
			foreach($pieces as $k=>$v) {
				if(true!==ZVhosts::chkDomain($v) && !in_array($v, explode(' ', $aliases))) {
					$removed[] = $v;
					unset($pieces[$k]);
				}
			}
			$alias = join(' ', $pieces);
			if(!$alias) $alias = 'x';
			$res = $pdo->update('site', array('aliases'=>$alias), "name='$name'");
			if ($res) $message = t("Httpd configuration saved.");
			if ($removed) $message = t("Alias invalid or occupied: ")."<br />".join(', ', $removed);
			$type = $removed ? 'warning' : 'notice';
			sync();
			setmsg($message, $type, 'self');
		}
		if (checktoken() && 'remove'==$_REQUEST['op']) {
			if(ZVhosts::removeVhost($name)) setmsg(t('Site removed.'), 'notice');
			else setmsg(t('Error'));
		}
		if (checktoken() && 'chDocRoot'==$_REQUEST['op']) {
			$root = $_REQUEST['root'];
			if(ZVhosts::changeDocRoot($name, $root)) setmsg(t('Site updated.'), 'notice');
			else setmsg(t('Error'));
		}
		if (checktoken() && 'suspend'==$_REQUEST['op']) {
			if(ZVhosts::suspendVhost($name, !intval($_REQUEST['suspend']))) setmsg('', 'notice');
			else setmsg(t('Error'));
		}
		if (checktoken() && 'default'==$_REQUEST['op'] && isadmin()) {
			if(ZVhosts::setDefaultSite($name)) setmsg('Updated', 'notice');
			else setmsg(t('Error'));
		}
		$deny_begin = '####ZHPANEL DENY BEGIN';
		$deny_end = '####ZHPANEL DENY END';
		$token = token();
		$vhost = ZVhosts::getVhost($name);
		$aliases = ZVhosts::getAliases($name);
		$htaccess = "$home/$vhost->owner/$name/.htaccess";
		if (checktoken() && 'denyips'==$_REQUEST['op']) {
			$denyips = $_REQUEST['denyips'];
			$patten_ip = "@^[0-9\./a-f:]+$@";
			$arr_ips = explode("\n", $denyips);
			foreach($arr_ips as $k=>$ip) {
				$arr_ips[$k] = $ip = trim($ip);
				if(!preg_match($patten_ip, $ip)) unset($arr_ips[$k]);
			}
			$arr_ips[] = '0.0.0.0';
			$ips = 'Deny from ' . join(' ', (array)$arr_ips);
			if(file_exists($htaccess)) $content = file_get_contents($htaccess);
			else $content = '';
			$content = preg_replace("@$deny_begin(.*)$deny_end@sU", '', $content);
			$content = preg_replace("/\n\n+/", "\n\n", $content);
			$deny_block = array("", $deny_begin, $ips, $deny_end);
			$content .= join("\n", $deny_block);

			$written = file_put_contents($htaccess, $content);
			if($written) {
				chown($htaccess, $vhost->owner);
				chgrp($htaccess, $vhost->owner);
				chmod($htaccess, 0755);
				setmsg(t('Saved'), 'notice', "?task=edit&name=$name");
			}
		}
		$hotlink_begin = '####ZHPANEL HOTLINK BEGIN';
		$hotlink_end = '####ZHPANEL HOTLINK END';
		if (checktoken() && 'hotlink'==$_REQUEST['op']) {
			$hotlinks = $_REQUEST['hotlink'];
			$patten_site = "@^[a-z0-9\-\.]+\.[a-z0-9\-\.]+$@";
			$arr_hotlinks = explode("\n", $hotlinks);
			$rule_allow = $_REQUEST['rule']=='allow';
			$rule = $rule_allow ? 'whitelist' : 'blacklist';
			$str = "\n\n$hotlink_begin\n# $rule\n";
			foreach($arr_hotlinks as $k=>$v) {
				$v = trim($v);
				if(!$v) continue;
				$arr_hotlinks[$k] = $v;
				if(!preg_match($patten_site, $v)) unset($arr_hotlinks[$k]);
				$str .= "SetEnvIf Referer \"^http://(.)*.?$v/\" local_ref=1\n";
			}
			$str .= '<FilesMatch "\.(mp3|wmv|png|gif|jpg|jpeg|avi|bmp|ram|rmvb|rm|rar|zip)">' . "\n";
			$str .= $rule_allow ? "\tOrder Deny,Allow\n\tDeny from all\n\tAllow from env=local_ref\n"
								: "\tOrder Allow,Deny\n\tAllow from all\n\tDeny from env=local_ref\n";
			$str .= '</FilesMatch>' . "\n";
			$str .= "$hotlink_end\n";

			if(file_exists($htaccess)) $content = file_get_contents($htaccess);
			else $content = '';
			$content = preg_replace("@$hotlink_begin(.*)$hotlink_end@sU", '', $content);
			$content = preg_replace("/\n\n+/", "\n\n", $content);
			$content .= $str;

			$written = file_put_contents($htaccess, $content);
			if($written) {
				chown($htaccess, $vhost->owner);
				chgrp($htaccess, $vhost->owner);
				chmod($htaccess, 0755);
				setmsg(t('Saved'), 'notice', "?task=edit&name=$name");
			}
		}
		
		$redirect_begin = '####ZHPANEL REDIRECT BEGIN';
		$redirect_end = '####ZHPANEL REDIRECT END';
		if (checktoken() && 'redirect'==$_REQUEST['op']) {
			$redirect = trim($_REQUEST['redirect']);
			$redirect = str_replace("\n", "\nRedirect ", $redirect);
			$str = "\n\n$redirect_begin\n";
			$str .= trim($redirect);
			$str .= "\n$redirect_end\n";

			if(file_exists($htaccess)) $content = file_get_contents($htaccess);
			else $content = '';
			$content = preg_replace("@$redirect_begin(.*)$redirect_end@sU", '', $content);
			$content = preg_replace("/\n\n+/", "\n\n", $content);
			$content .= $str;

			$written = file_put_contents($htaccess, $content);
			if($written) {
				chown($htaccess, $vhost->owner);
				chgrp($htaccess, $vhost->owner);
				chmod($htaccess, 0755);
				setmsg(t('Saved'), 'notice', "?task=edit&name=$name");
			}
		}
		
		if($aliases=='x') $aliases='';
		if(file_exists($htaccess)) $content = file_get_contents($htaccess);
		else $content = '';
		##########################################
		$ips = $content;
		preg_match("@$deny_begin\s*(.*)\s*$deny_end@sU", $ips, $out);
		$ips = array_pop($out);
		$ips = str_replace(array('Deny from ', '0.0.0.0', " "), array('','',"\n"), $ips);
		##########################################
		$hotlink = $content;
		preg_match("@$hotlink_begin\s*(.*)\s*$hotlink_end@sU", $hotlink, $out);
		$block = trim(array_pop($out));
		$bWhitelist = (!strncmp('# whitelist', $block, 11));
		preg_match_all("@\?([^\?/]+)/@", $block, $out);
		$hotlink_list = array_pop($out);
		$hotlinks = join("\n", $hotlink_list);
		#########################################
		$redirect = $content;
		preg_match("@$redirect_begin\s*(.*)\s*$redirect_end@sU", $redirect, $out);
		$block = trim(array_pop($out));
		//var_dump($block);
		$redirect = str_replace('Redirect ', '', $block);
		#########################################
		break;
	case 'sync':
		if(syncVhosts()) {
			setmsg(t('Httpd configuration updated!'), 'notice');
		} else {
			setmsg(t('Can not write the configuration file.'), 'error');
		}
		break;
	case 'secure':
		$sites = isadmin() ? ZVhosts::getVhosts() : ZVhosts::listMyVhosts();
		foreach($sites as $v) {
			chmod("/home/{$v->owner}", 0711);
			chgrp($v->root, 'www');
			chmod($v->root, 0750);
		}
		setmsg(t('saved'), 'notice');
		break;
	case 'errdoc':
		$name = preg_replace("/[^0-9a-z\-\.]/", '', $_REQUEST['name']);
		$do = preg_replace("/[^0-9a-z\-\.]/", '', $_REQUEST['do']);
		$token = token();
		$vhost = ZVhosts::getVhost($name);
		$item = intval($_REQUEST['item']);
		$doc = (in_array($item, $error_documents) ? $item : 404).'.shtml';
		$file = "$home/$vhost->owner/$name/$doc";
		if(checktoken()) {
			$content = $_REQUEST['content'];
			$written = file_put_contents($file, $content);
			if($written) {
				chown($file, $vhost->owner);
				chgrp($file, $vhost->owner);
				chmod($file, 0755);
				setmsg(t('Saved'), 'notice', "?task=edit&name=$name");
			}
		}
		if($do=='reset') {
			$def_dir = ZH."/data/errdoc";
			$user_dir = "$home/$vhost->owner/$name";
			foreach($error_documents as $doc) {
				$docfile = intval($doc).'.shtml';
				if(file_exists($def_dir."/$docfile")) {
					copy($def_dir."/$docfile", $user_dir."/$docfile");
					chown($user_dir."/$docfile", $vhost->owner);
					chgrp($user_dir."/$docfile", $vhost->owner);
					chmod($user_dir."/$docfile", 0755);
				}
			}
			setmsg(t('Saved'), 'notice', "?task=edit&name=$name");
		}
		if($do=='delete') {
			$user_dir = "$home/$vhost->owner/$name";
			foreach($error_documents as $doc) {
				$docfile = intval($doc).'.shtml';
				if(file_exists($user_dir."/$docfile")) {
					@unlink($user_dir."/$docfile");
				}
			}
			setmsg(t('Saved'), 'notice', "?task=edit&name=$name");
		}
		$content = is_file($file) ? file_get_contents($file) : '';
		break;
	case 'webalizer':
		if ( !isadmin() ) break;
		$sites = ZVhosts::getVhosts();
		$output = array();
		set_time_limit(0);
		foreach( $sites as $s ) {
			$output[] = 'Processing site ' . $s->name;
			$log_dir = $home . "/{$s->owner}/logs/{$s->name}";
			$stat_dir = $home . "/{$s->owner}/logs/{$s->name}/html";
			$webalizer_path = $zh . "/webalizer/bin/webalizer";
			if ( !is_dir($log_dir) ) continue;
			if ( !is_dir($stat_dir) ) {
				mkdir($stat_dir, 0710, true);
			}
			$d = dir( $log_dir );
			$output[] = '<blockquote>';
			while ( false !== ($f = $d->read()) ) {
				$file = PHP_OS=='WINNT' ? 'access.log' : 'access_log';
				$log_file = $log_dir . "/$f/$file";
				if ( is_file( $log_file ) ) {
					$output[] = "";
					$output[] = "Processing {$s->name}/$f ...";
					$cmd = "$webalizer_path -o $stat_dir -n {$s->name} $log_file";
					$output[] = $cmd;
					$output[] = array_pop( explode("\n", trim(shell_exec($cmd)) ) );
				} else {
					//var_dump($log_file);
				}
			}
			$output[] = "</blockquote>\n";
			$d->close();
		}
		$output = nl2br(join("\n", $output));
		break;
	case 'list':
	default:
		$task = 'list';
		$sites = isadmin() ? ZVhosts::getVhosts() : ZVhosts::listMyVhosts();
		break;
}

if(!$norender) include template('site');

