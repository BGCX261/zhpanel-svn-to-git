<!--{if $task!='random'}--><!--{template header}--><!--{/if}-->
<!--{if $task=='cron'}-->
<table border='1' width='100%'>
<tr><th><!--{t User}--></th><th><!--{t Job Title}--></th><th><!--{t Frequency}--></th><th><!--{t Status}--></th><th><!--{t Operations}--></th>
<!--{loop $cron_lists $c}-->
	<tr>
	<td><a href="user.php?task=edit&amp;name=$user->user">$c->user</a></td>
	<td>$c->name</td>
	<td>$c->frequency</td>
	<td align="center"><!--{if $c->state}--><!--{s 1,name=$c->user&target=cron}--><!--{else}--><!--{s 0,name=$c->user&target=cron}--><!--{/if}--></td>
	<td align="center">
		<!--{if $isadmin}--><!--{b switch,id=$c->id}--><!--{/if}-->
		<!--{b edit,name=id=$c->id}-->
		<!--{b delete,name=id=$c->id}-->
	</td>
	</tr>
<!--{/loop}-->
</table>
<br />
<form method="post" action="">
<table border='1' width='100%'>
<tr><th colspan='4'><!--{t Add New}--></th></tr>
<tr><td><!--{t Name}--></td><td><input type="text" name="name" value="$cron->name" /></td></tr>
<tr><td><!--{t User}--></td><td>
<select name="user">
<!--{loop $users $u}-->
<option value="$u->user"<!--{if $user->xx!=$cron->user}--> selected="selected"<!--{/if}-->>$u->user</option>
<!--{/loop}-->
</select>
</td></tr>
<tr><td><!--{t Cmd}--></td><td><input type="text" name="cmd" value="$cron->cmd" /></td></tr>
<tr><td><!--{t When to run}--></td><td>
<select name="frequency">
<!--{loop $times $v}-->
<option value="$v"<!--{if $v!=$cron->frequency}--> selected="selected"<!--{/if}-->>$v</option>
<!--{/loop}-->
</select>
</td></tr>
<tr><td colspan='2'><input type="hidden" name="token" value="$token" />
<input type="hidden" name="op" value="$op" />
<input type="submit" value="Submit" /></td></tr>
</table>
</form>
<!--{elseif $task=='svn'}-->
<table border='1' width='100%'>
<tr><th><!--{t Project URL}--></th><th><!--{t Name}--></th><th><!--{t Type}--></th><th><!--{t Status}--></th><th><!--{t Operations}--></th></tr>
<!--{loop $users $user}-->
	<tr>
	<td><a href="user.php?task=edit&amp;name=$user->user">$user->user</a></td>
	<td>$user->packageName</td>
	<td>$user->shell</td>
	<td align="center"><!--{if $user->state}--><!--{s 1,name=$user->user}--><!--{else}--><!--{s 0,name=$user->user}--><!--{/if}--></td>
	<td align="center">
		<!--{if $isadmin}--><!--{b switch,name=$user->user}--><!--{/if}-->
		<!--{b edit,name=$user->user}-->
		<!--{b delete,name=$user->user}-->
	</td>
	</tr>
<!--{/loop}-->
</table>
<!--{elseif $task=='tpl'}-->
<form method="post" action="">
	<select name="file" onchange="location='?task=tpl&tpl='+this.value">
<!--{loop $files $file}-->
<option value="$file" <!--{if $file==$f}-->selected<!--{/if}-->>$file</option>
<!--{/loop}-->
</select>
<br />
<textarea name="content" rows="20" cols="60">$content</textarea><br />
<input type="hidden" name="token" value="$token" /><input type="submit" value="<!--{t Submit}-->" />
</form>
<!--{elseif $task=='tasks' && isadmin()}-->
<table border='1' width='100%'>
<tr><th><!--{t ID}--></th><th><!--{t Task}--></th><th><!--{t Data}--></th><th><!--{t Result}--></th></tr>
<!--{loop $tasks $t}-->
	<tr>
	<td>$t->id</td>
	<td>$t->func</td>
	<td>$t->data</td>
	<td>$t->msg</td>
	</tr>
<!--{/loop}-->
</table>
<br />
<form method="post" action="">
<table border='1' width='100%'>
<tr><th colspan='4'><!--{t Add New}--></th></tr>
<tr><td><!--{t Task}--></td><td><input type="text" name="func" /></td></tr>
<tr><td><!--{t Data}--></td><td><input type="text" name="data" /></td></tr>
<tr><td><!--{t Result}--></td><td><input type="text" name="msg" /></td></tr>
<tr><td colspan='2'><input type="hidden" name="token" value="$token" /><input type="submit" value="<!--{t Submit}-->" /></td></tr>
</table>
</form>
<!--{elseif $task=='random'}-->
$pass
<br />
<input type="button" value="<!--{t Get Another}-->" onclick="location=location.href;" />
<br />
<input type="button" value="<!--{t Select}-->" onclick="window.opener.document.getElementById('$id').value='$pass';" />
<!--{elseif $task=='login'}-->
<form method="post" action="" name='f'>
	<div id="login">
<ul>
<dt><!--{t Username}--></dt>
<dd>
	<input type="text" name="user" value="" />
</dd>
<dt><!--{t Password}--></dt>
<dd>
	<input type="password" name="pass" />
</dd>
<dt>&nbsp;</dt>
<dd>
	<input type="submit" value="<!--{t Login}-->" />
	<input type="hidden" name="token" value="$token" />
</dd>
</ul>
</div>
</form>
<script type="text/javascript">
var x=document.f.user;x.focus();
</script>
<!--{elseif $task=='login'}-->

<!--{/if}-->
<!--{if $task!='random'}--><!--{template footer}--><!--{/if}-->