<?php

/////////////// USER MANAGEMENT
function getUsers() {
	return ZUser::getUsers();
}

function suspendUser($name, $state) {
	return ZUser::suspendUser($name, $state);
}

function getUser($name) {
	return ZUser::getUser($name);
}

function getuid($name) {
	return ZUser::getUid($name);
}

function chkUsername($username, $lenlimit='{1,10}') {
	return ZUser::chkUsername($username, $lenlimit);
}

function copyskel($user) {
	return ZUser::copyskel($user);
}

function createfcgid($user) {
	return ZUser::createfcgid($user);
}

function getlastuid() {
	return ZUser::getlastuid();
}

function adduser($user, $pass) {
	return ZUser::adduser($user, $pass);
}

function adduser_request($user, $pass, $package) {
	return ZUser::adduser_request($user, $pass, $package);
}

function chkPassword($password) {
	return ZUser::chkPassword($password);
}

function chpasswd($user, $pass) {
	ZUser::chpasswd($user, $pass);
}

function chpasswd_request($user, $pass) {
	return ZUser::chpasswd_request($user, $pass);
}

function removeUser($name, $rmFtp=1, $rmSite=1, $rmContent=1) {
	return ZUser::removeUser($name, $rmFtp, $rmSite, $rmContent);
}

///////////// VHOSTS MANAGEMENT
function getDomains() {
	return ZVhosts::getDomains();
}

function getVhosts($where='') {
	return ZVhosts::getVhosts($where);
}

function saveVhosts($entries) {
	return ZVhosts::saveVhosts($entries);
}

function getVhost($domain) {
	return ZVhosts::getVhost($domain);
}

function removeVhost($domain) {
	return ZVhosts::removeVhost($domain) && syncVhosts();
}

function suspendVhost($domain, $state=0) {
	return ZVhosts::suspendVhost($domain, $state) && syncVhosts();
}

function listMyVhosts($name_only=false) {
	return ZVhosts::listMyVhosts($name_only);
}

function getAliases($domain) {
	return ZVhosts::getAliases($domain);
}

function chkDomain($domain) {
	return ZVhosts::chkDomain($domain);
}

function getCbandUsers() {
	return ZVhosts::getCbandUsers();
}

function addvhost($user, $domain) {
	return ZVhosts::addvhost($user, $domain);
}

function addsite($user, $domain, $docroot) {
	return ZVhosts::addsite($user, $domain, $docroot);
}

function addcband($user) {
	return ZVhosts::addcband($user);
}

function mkwebroot($user, $domain) {
	return ZVhosts::mkwebroot($user, $domain);
}

function mklogroot($user, $domain) {
	return ZVhosts::mklogroot($user, $domain);
}

function changeDocRoot($domain, $newRoot) {
	return ZVhosts::changeDocRoot($domain, $newRoot);
}

function setDefaultSite($name) {
	return ZVhosts::setDefaultSite($name);
}

function restart_request() {
	return ZVhosts::restart_request();
}

function restart() {
	return ZVhosts::restart();
}

///////////////////////////// FTP ACCOUNTS
function getFtpAccounts($owner='') {
	return ZFtp::getFtpAccounts($owner);
}

function suspendFtpUser($name, $state) {
	return ZFtp::suspendFtpUser($name, $state);
}

function checkFtpExistence($ftpuser) {
	return ZFtp::checkExistence($ftpuser);
}

function getFtpAccount($name) {	// trust the name passed here
	return ZFtp::getFtpAccount($name);
}

function chkFtpRoot($path, $owner) {
	return ZFtp::chkFtpRoot($path, $owner);
}

function addFtpAccount($owner, $ftpuser, $passwd, $dir='', $writable) {
	return ZFtp::addFtpAccount($owner, $ftpuser, $passwd, $dir, $writable);
}

function updateFtpAccount($owner, $ftpuser, $passwd='', $dir='', $writable=true) {
	return ZFtp::updateFtpAccount($owner, $ftpuser, $passwd, $dir, $writable);
}

function removeFtpAccount($user) {
	return ZFtp::removeFtpAccount($user);
}

function syncFtpAccounts() {
	return ZFtp::syncFtpAccounts();
}

//////////////////////////// PACKAGES
function getPackages($name_only=false, $state=1) {
	return ZPackage::getPackages($name_only, $state);
}

function suspendPackage($id, $state) {
	return ZPackage::suspendPackage($id, $state);
}

function getPackage($name) {
	return ZPackage::getPackage($name);
}

function insertPackage($id, $package) {
	return ZPackage::insertPackage($id, $package);
}

function updatePackage($id, $package) {
	return ZPackage::updatePackage($id, $package);
}

function removePackage($name) {
	return ZPackage::removePackage($name);
}

function getUserPackage($user) {
	return ZPackage::getUserPackage($user);
}

function setUserPackage($user, $package) {
	return ZPackage::setUserPackage($user, $package);
}

///////////////////////// SHELL
function getShells() {
	return ZShell::getShells();
}
function getUserShell($user) {
	return ZShell::getUserShell($user);
}

function setUserShell($user, $shell) {
	return ZShell::setUserShell($user, $shell);
}

////////////////////////// DATABASES
function getDbUsers() {
	return ZDatabase::getDbUsers();
}

function addmysqluser($user, $pass) {
	return ZDatabase::addmysqluser($user, $pass);
}

////////////////////////// FILE SYSTEM
function xCopy($source, $destination, $child=1){
	return ZFile::xCopy($source, $destination, $child);
}

function xchown($mypath, $uid, $gid) {
	return ZFile::xchown($mypath, $uid, $gid);
}

function backupConf($type) {
	return ZFile::backupConf($type);
}

function fappend($filename, $content) {
    return ZFile::fappend($filename, $content);
}

function getfile($filename) {
	return ZFile::getfile($filename);
}

function getAllDirs($in_dir, $max_levels=3, $ignore=array('.','..','.svn','CVS','logs')) {
	return ZFile::getAllDirs($in_dir, $max_levels, $ignore);
}

function getUserDirs($user) {
	return ZFile::getUserDirs($user);
}

//////////// SYSTEM SETTINGS
function getSettings() {
	return ZSystem::getSettings();
}

function updateSettings($data) {
	return ZSystem::updateSettings($data);
}
