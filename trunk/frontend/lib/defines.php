<?php
// encrypt
$fakeroot = PHP_OS=='WINNT' ? 'D:\www\fakeroot' : '';
$zh = $fakeroot.'/zh';
$home = $fakeroot.'/home';

define('CRYPT_KEY','!@$f!%&%GD');

define('ZH_DATA', ZH.'/../data');
define('ZH_TMP', ZH_DATA);

define('PATH_HTTPD', '/usr/sbin/apache2');
define('CONF_CBAND', '');

define('DB_HOST', 'localhost');
define('DB_USER', 'zh');
define('DB_PASS', 'zhpanel');

define('PERPAGE', 20);

$admins = array('seaprince', 'sp');
$sess_key = 'FQXUxtqx_rq';
$dbhost = 'localhost';	// for user

$zh_version = '0.2beta';

$roles = array('Member', 'Administrator', 'Reseller');
