<?php if(!defined('ZH')) exit; ?><html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>$title</title>
<link href="tpl/zh/style.css" rel="stylesheet" type="text/css" media="all">
</head>
<!--{eval $isadmin=isadmin()}-->
<body>
<div class="zmain">
  <div class="zheader">
  	<img src="assets/images/logo2.png" alt="<!--{t ZhPanel}-->" title="<!--{t ZhPanel}-->" width="183" height="53" />
    <i><!--{t Welcome to Zhpanel Control Panel.}--> 
	<!--{if $me}--><!--{t Logged as}--> <big>$me</big><!--{/if}-->
	<!--{if isswitch()}--> (<!--{t Switched from}--> $switch_from. <a href="user.php?task=leave"><!--{t Leave}--></a>)<!--{/if}-->
	</i>
  </div>
  <div id="tabnav">
      <ul>
		<!--{if $basename=='login'}-->
        <li><a href="misc.php?task=login"$style->login><!--{t Login}--></a></li>
		<!--{else}-->
        <li><a href="index.php"$style->index><!--{t Home}--></a></li>
        <!--{if $isadmin}--><li><a href="package.php"$style->package><!--{t Packages}--></a></li><!--{/if}-->
        <li><a href="user.php"$style->user><!--{t Users}--></a></li>
        <li><a href="ftp.php"$style->ftp><!--{t FTP Accounts}--></a></li>
        <li><a href="site.php"$style->site><!--{t Sites}--></a></li>
        <li><a href="db.php"$style->db><!--{t Databases}--></a></li>
        <li><a href="misc.php"$style->misc><!--{t Misc}--></a></li>
        <li><a href="misc.php?task=logout"><!--{t Logout}--></a></li>
		<!--{/if}-->
      </ul>
	  <!--{if $basename=='index'}-->
	  <span class="index_sub">
		<a href="index.php"><!--{t Dashboard}--></a>
		<!--{if isadmin()}-->
		<a href="index.php?task=settings"><!--{t Settings}--></a>
		<a href="index.php?task=sync"><!--{t Sync Data}--></a>
		<a href="index.php?task=svn"><!--{t SVN Update}--></a>
		<a href="user.php?task=sync"><!--{t Sync Users}--></a>
		<a href="misc.php?task=tpl"><!--{t Templates}--></a>
		<a href="misc.php?task=tasks"><!--{t Task Queue}--></a>
		<!--{/if}-->
	  </span>
	  <!--{elseif $basename=='package' && $isadmin}-->
	  <span class="package_sub">
		<a href="package.php?task=add"><!--{t Add Hosting Package}--></a>
		<a href="package.php"><!--{t List All Packages}--></a>
	  </span>
	  <!--{elseif $basename=='user'}-->
	  <span class="user_sub">
		<!--{if $isadmin}--><a href="user.php?task=add"><!--{t Add User}--></a><!--{/if}-->
		<a href="user.php"><!--{t Users List}--></a>
	  </span>
	  <!--{elseif $basename=='ftp'}-->
	  <span class="ftp_sub">
		<a href="ftp.php?task=add"><!--{t Add FTP Account}--></a>
		<a href="ftp.php"><!--{t FTP Accounts List}--></a>
	  </span>
	  <!--{elseif $basename=='site'}-->
	  <span class="site_sub">
		<a href="site.php?task=add"><!--{t Add Site}--></a>
		<a href="site.php"><!--{t Sites List}--></a>
		<!--{if $isadmin}--><a href="site.php?task=webalizer"><!--{t Generates Webalizer Stats}--></a><!--{/if}-->
	  </span>
	  <!--{elseif $basename=='db'}-->
	  <span class="db_sub">
		<a href="db.php?task=add"><!--{t Add MySQL User}--></a>
		<a href="db.php"><!--{t List MySQL Users}--></a>
	  </span>
	  <!--{elseif $basename=='misc'}-->
	  <span class="misc_sub">
		<a href="misc.php?task=cron"><!--{t Cron}--></a>
		<a href="misc.php?task=svn"><!--{t Subversion}--></a>
		<a href="misc.php?task=1click"><!--{t 1-click Install}--></a>
	  </span>
	  <!--{/if}-->
  </div>
  <div class="zcontent">
  <!--{if $_SESSION[zmessage]}-->$_SESSION[zmessage]
  	<br />
  <!--{/if}-->
