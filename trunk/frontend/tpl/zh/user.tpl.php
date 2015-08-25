<!--{template header}-->
<!--{if $task=='add'}-->
<script>
function get_random_pass(o) {
	var writeTo = o.parentNode.childNodes[0];
	writeTo.value = getPassword(8, 1, 1, 1, 1, 1, 1, 1, 1, 1);
	
	function getRandomNum(lbound, ubound) {
		return (Math.floor(Math.random() * (ubound - lbound)) + lbound);
	}

	function getRandomChar(number, lower, upper, other, extra) {
		var numberChars = "0123456789";
		var lowerChars = "abcdefghijklmnopqrstuvwxyz";
		var upperChars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		var otherChars = "~!@#$^&*()-=+[{]}\\|;:\,<.>/";
		var charSet = extra;
		if (number == true) charSet += numberChars;
		if (lower == true) charSet += lowerChars;
		if (upper == true) charSet += upperChars;
		if (other == true) charSet += otherChars;
		return charSet.charAt(getRandomNum(0, charSet.length));
	}

	function getPassword(length, extraChars, firstNumber, firstLower, firstUpper, firstOther,
	latterNumber, latterLower, latterUpper, latterOther) {
		var rc = "";
		if (length > 0)
		rc = rc + getRandomChar(firstNumber, firstLower, firstUpper, firstOther, extraChars);
		for (var idx = 1; idx < length; ++idx) {
			rc = rc + getRandomChar(latterNumber, latterLower, latterUpper, latterOther, extraChars);
		}
		return rc;
	}
}
</script>
<form method="post" action="">
<dl>
	<dt><!--{t Username}--></dt>
	<dd><input name="name" type="text" value="$_REQUEST[name]" />
	<!--{if $err[name]}--><b class="invalid">$err[name]</b><!--{/if}-->
	</dd>
	<dt><!--{t Password}--></dt>
	<dd><input name="password" type="text" value="$_REQUEST[password]" id="password" /> <a href="#" onclick="return get_random_pass(this);"><!--{t Random}--></a>
	<!--{if $err[password]}--><b class="invalid">$err[password]</b><!--{/if}-->
	</dd>
	<dt><!--{t Domain}--></dt>
	<dd><input name="domain" type="text" value="$_REQUEST[domain]" />
	<!--{if $err[domain]}--><b class="invalid">$err[domain]</b><!--{/if}-->
	<input type="hidden" name="shell" value="/bin/bash" />
	</dd>
	<dt><!--{t Hosting Package}--></dt><dd><select name="package">
	<!--{loop $packages $pkg}-->
		<option value="$pkg->id">$pkg->name [$pkg->desc]</option>
	<!--{/loop}-->
	</select>
	<!--{if $err[package]}--><b class="invalid">$err[package]</b><!--{/if}-->
	</dd>
    <dt>&nbsp;</dt><dd><input type="submit" value="<!--{t Submit}-->" />
	<input type="hidden" name="token" value="$token" /></dd>
</dl>
</form>
<!--{elseif $task=='check'}-->
<pre>$response</pre>
<!--{elseif $task=='list'}-->
<!--{if $isadmin}-->
<div>
	<span><a href="?type=admin"><!--{t Admins List}--></a></span>
	<span><a href="?type=reseller"><!--{t Resellers List}--></a></span>
	<span><a href="?type=user"><!--{t Users List}--></a></span>
</div>
<!--{/if}-->
<table border='1' width='100%'>
<tr><th><!--{t User Name}--></th><th><!--{t Hosting Package}--></th><th><!--{t Shell Type}--></th><th><!--{t Status}--></th><th><!--{t Operations}--></th>
<!--{loop $users $user}-->
	<tr>
	<td><a href="user.php?task=edit&amp;name=$user->user">$user->user</a></td>
	<td>$user->packageName</td>
	<td>$user->shell</td>
	<td align="center"><!--{if $user->state}--><!--{s 1,name=$user->user}--><!--{else}--><!--{s 0,name=$user->user}--><!--{/if}--></td>
	<td align="center">
		<!--{if $isadmin}--><!--{b switch,name=$user->user}--><!--{/if}-->
		<!--{b edit,name=$user->user}-->
		<!--{if $isadmin}--><!--{b delete,name=$user->user}--><!--{/if}-->
	</td>
	</tr>
<!--{/loop}-->
</table>
<!--{elseif $task=='edit'}-->
<input type="button" value="<!--{t Back}-->" onclick="history.back()" />
	<!--{if count($user)}-->
	<h3><!--{t Details}--></h3>
	<dl>
		<dt><!--{t Username}--></dt><dd>$name</dd>
		<dt><!--{t Uid}--></dt><dd>$user->uid</dd>
		<dt><!--{t Gid}--></dt><dd>$user->gid</dd>
		<dt><!--{t Home}--></dt><dd>$user->home</dd>
		<dt><!--{t Shell Type}--></dt><dd>$user->shell</dd>
	</dl>
	<h3><!--{t Change Password}--></h3>
	<form method="post" action="">
	<dl>
		<!-- <dt><!--{t Username}--></dt><dd><input name="name" type="text" value="$name" readonly /></dd> -->
		<dt><!--{t Change To}--></dt><dd><input name="password" type="password" value="" />
		<input type="hidden" name="op" value="chpasswd" />
		</dd>
		<dt>&nbsp;</dt><dd><input type="submit" value="<!--{t Submit}-->" />
		<input type="hidden" name="token" value="$token" /></dd>
	</dl>
	</form>
	<!--{if $isadmin}-->
    <h3><!--{t Hosting Package}--></h3>
	<form method="post" action="">
    <dl>
        <dt><!--{t Hosting Package}--></dt><dd>$package</dd>
        <dt><!--{t Change To}--></dt><dd>
        <select name="package">
            <!--{loop $packages $pkg}-->
            <option value="$pkg->id" <!--{if $pkg->name==$package}-->selected<!--{/if}-->>$pkg->name ($pkg->desc)</option>
            <!--{/loop}-->
        </select>
        </dd>
		<dt>&nbsp;</dt><dd><input type="submit" value="<!--{t Submit}-->" />
		<input type="hidden" name="op" value="setpkg" />
		<input type="hidden" name="token" value="$token" /></dd>
    </dl>
	</form>
	<!--{/if}-->
    <h3><!--{t Shell Type}--></h3>
	<form method="post" action="">
    <dl>
        <dt><!--{t Shell Type}--></dt><dd>$user->shell</dd>
		<!--{if $isadmin}-->
        <dt><!--{t Change To}--></dt>
        <dd>
		<select name="shell">
			<!--{loop $shells $shell}-->
			<option value="$shell"  <!--{if $user->shell==$shell}-->selected<!--{/if}-->>$shell</option>
			<!--{/loop}-->
		</select>
        </dd>
		<dt>&nbsp;</dt><dd><input type="submit" value="<!--{t Submit}-->" />
		<input type="hidden" name="op" value="shell" />
		<input type="hidden" name="token" value="$token" /></dd>
		<!--{/if}-->
    </dl>
	</form>
	<!--{else}-->
	<!--{t No such user.}-->
	<!--{/if}-->
<!--{elseif $task=='remove'}-->
<!--{t Are you sure to REMOVE user $user->user?}-->
<form method="post" action="">
	<p>
	<label><input type="checkbox" name="ftp" value="1" /><!--{t Also remove ftp accounts}--></label>
	<label><input type="checkbox" name="site" value="1" /><!--{t Also remove sites}--></label>
	<label><input type="checkbox" name="site" value="1" /><!--{t Also remove contents}--></label>
	</p>
	<input type="hidden" name="token" value="$token" />
	<input type="submit" value="<!--{t REMOVE USER}-->" />
</form>
<!--{/if}-->
<!--{template footer}-->
