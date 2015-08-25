<!--{template header}-->
<!--{if $task=='add'}-->
<form method="post" action="">
<dl>
	<dt><!--{t Username}--></dt>
	<dd><input name="name" type="text" value="$_REQUEST[name]" />
	<!--{if $err[name]}--><b class="zerror">$err[name]</b><!--{/if}-->
	</dd>
	<dt><!--{t Password}--></dt>
	<dd><input name="password" type="text" value="$_REQUEST[password]" />
	<!--{if $err[password]}--><b class="invalid">$err[password]</b><!--{/if}-->
	</dd>
    <dt>&nbsp;</dt><dd><input type="submit" value="<!--{t Submit}-->" />
	<input type="hidden" name="token" value="$token" /></dd>
</dl>
</form>
<!--{elseif $task=='check'}-->
<pre>$response</pre>
<!--{elseif $task=='error'}-->
<b style="color: red">$mysql_error</b>
<!--{elseif $task=='list'}-->
<table border='1' width='100%'>
<tr><th><!--{t User Name}--></th><th><!--{t Operations}--></th>
<!--{loop $users $user}-->
	<tr>
	<td><a href="db.php?task=edit&amp;name=$user">$user</a></td>
	<td align="center">
		<!--{b edit,name=$user}-->
		<!--{b delete,name=$user}-->
	</td>
	</tr>
<!--{/loop}-->
</table>
<!--{elseif $task=='edit'}-->
<input type="button" value="<!--{t Back}-->" onclick="history.back()" />
	<!--{if count($user_obj)}-->
	<h3><!--{t Details}--></h3>
	<form method="POST" action="">
	<dl>
		<dt><!--{t Username}--></dt><dd>$name</dd>
		<dt><!--{t Host}--></dt>
		<dd>
			<input type="text" name="host" value="$user_obj->Host" />
			<input type="hidden" name="old_host" value="$user_obj->Host" />
			<input type="hidden" name="op" value="host" />
			<input type="hidden" name="token" value="$token" />
			<input type="submit" value="Ìá½»" />
		</dd>
		<dt><!--{t Databases}--></dt><dd>$user_obj->Db</dd>
	</dl>
	</form>
	<h3><!--{t Databases List}--></h3>
	<ul>
	<!--{loop $databases $dbname}-->
		<li>$dbname <a href="?task=drop&db=$dbname&name=$name" onclick="if(!confirm('$del_confirm')) return false;"><img src="assets/images/drop.png" alt="Delete" /></a></li>
	<!--{/loop}-->
	</ul>
	<h3><!--{t Create database}--></h3>
	<form method="post" action="">
	<dl>
		<dt><!--{t Database Name}--></dt><dd>$prefix<input name="dbname" type="text" value="" />
		<!--{if $err[dbname]}--><b class="invalid">$err[dbname]</b><!--{/if}-->
		<input type="hidden" name="op" value="createdb" />
		</dd>
		<dt>&nbsp;</dt><dd><input type="submit" value="<!--{t Submit}-->" />
		<input type="hidden" name="token" value="$token" /></dd>
	</dl>
	</form>
	<h3><!--{t Change password}--></h3>
	<form method="post" action="">
	<dl>
		<dt><!--{t Change To}--></dt><dd><input name="password" type="text" value="" />
		<input type="hidden" id="host" name="host" value="$user_obj->Host" />
		<!--{if $err[password]}--><b class="invalid">$err[password]</b><!--{/if}-->
		<input type="hidden" name="op" value="chpasswd" />
		</dd>
		<dt>&nbsp;</dt><dd><input type="submit" value="<!--{t Submit}-->" />
		<input type="hidden" name="token" value="$token" /></dd>
	</dl>
	</form>
	<h3><!--{t Removal}--></h3>
	<form method="post" action="">
	<dl>
		<dd><label for="deldb"><!--{t Also Delete Databases}-->(USER_*)</label>
		<input name="deldb" id="deldb" type="checkbox" value="1" />
		<input type="hidden" name="op" value="deluser" />
		</dd>
		<dd><input type="submit" value="<!--{t Remove}-->" />
		<input type="hidden" id="host" name="host" value="$user_obj->Host" />
		<input type="hidden" name="token" value="$token" /></dd>
	</dl>
	</form>
	<!--{else}-->
	<!--{t No such database user.}-->
	<!--{/if}-->
<!--{/if}-->
<!--{template footer}-->
