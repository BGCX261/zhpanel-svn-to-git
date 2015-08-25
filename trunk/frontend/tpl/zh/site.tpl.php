<!--{template header}-->
<!--{if $task=='add'}-->
<form method="post" action="">
<dl>
	<dt><!--{t Domain}--></dt>
	<dd><input name="domain" type="text" value="$_REQUEST[domain]" />
	<!--{if $err[domain]}--><b class="invalid">$err[domain]</b><!--{/if}-->
	</dd>
	<dt><!--{t Document Root}--></dt>
	<dd><!-- <input name="docroot" type="text" value="$_REQUEST[docroot]" /> -->
	<select id="docroot" name="docroot">
	<!--{loop $dirs $d}-->
		<option value="$d">$d</option>
	<!--{/loop}-->
	</select>
	<!--{if $err[docroot]}--><b class="invalid">$err[docroot]</b><!--{/if}-->
	</dd>
    <dt>&nbsp;</dt><dd><input type="submit" value="<!--{t Submit}-->" />
	<input type="hidden" name="token" value="$token" /></dd>
</dl>
</form>
<!--{elseif $task=='check'}-->
<pre>$response</pre>
<!--{elseif $task=='list'}-->
<table border='1' width='100%'>
<tr><th><!--{t Domain}--></th><th><!--{t Owner}--></th><th><!--{t Document Root}--></th><th><!--{t Status}--></th><th><!--{t Operations}--></th>
<!--{loop $sites $site}-->
	<tr>
	<td><a href="site.php?task=edit&amp;name=$site->name">$site->name</a></td>
	<td><a href="user.php?task=edit&amp;name=$site->owner">$site->owner</a></td>
	<td>$site->root</td>
	<td align="center"><!--{if $site->state}--><!--{s 1,name=$site->name}--><!--{else}--><!--{s 0,name=$site->name}--><!--{/if}--></td>
	<td align="center">
		<!--{b edit,name=$site->name}-->
		<!--{b delete,name=$site->name}-->
	</td>
	</tr>
<!--{/loop}-->
</table>
<!--{elseif $task=='webalizer'}-->
<div>$output</div>
<!--{elseif $task=='errdoc'}-->
<input type="button" value="<!--{t Back}-->" onclick="history.back()" />
<h3><!--{t Error Document}--></h3>
<form method="post" action="">
<p><!--{t File}-->: $vhost->name/$doc</p>
<p><textarea name="content" rows="10" cols="60" class="clearfix">$content</textarea></p>
<p><input type="submit" value="<!--{t Submit}-->" /></p>
</dl>
<input type="hidden" name="op" value="chDocRoot" />
<input type="hidden" name="token" value="$token" />
</form>
<!--{elseif $task=='edit'}-->
<input type="button" value="<!--{t Back}-->" onclick="history.back()" />
	<!--{if $vhost}-->
	<h3><!--{t Details}--></h3>
	<form method="post" action="">
	<dl>
		<dt><!--{t Server Name}--></dt><dd>$vhost->name</dd>
		<dt><!--{t Aliases}--></dt><dd>$vhost->aliases</dd>
		<dt><!--{t Owner}--></dt><dd>$vhost->owner</dd>
		<dt><!--{t Document Root}--></dt><dd><input type="text" name="root" value="$vhost->root" /><input type="submit" value="<!--{t Submit}-->" /></dd>
	</dl>
	<input type="hidden" name="op" value="chDocRoot" />
	<input type="hidden" name="token" value="$token" />
	</form>
	<h3><!--{t Aliases}--></h3>
	<form method="post" action="">
	<dl>
		<dt><!--{t Aliases}--></dt><dd>$aliases</dd>
		<dt><!--{t Change To}--></dt><dd><input name="alias" type="text" value="$aliases" />
		<input type="hidden" name="op" value="chpasswd" />
		</dd>
		<dt>&nbsp;</dt><dd><input type="submit" value="<!--{t Submit}-->" />
		<input type="hidden" name="token" value="$token" />
		<input type="hidden" name="op" value="alias" />
		</dd>
	</dl>
	</form>
	
	<h3><!--{t Error Document}--></h3>
	<ul>
	<!--{loop $error_documents $doc}-->
		<li>
			<a href="http://$vhost->name/$doc.shtml" target="_blank">$doc</a> 
			[<a href="http://$vhost->name/$doc.shtml" target="_blank"><!--{t View}--></a> 
			<a href="?task=errdoc&item=$doc&name=$vhost->name"><!--{t Edit}--></a>]
		</li>
	<!--{/loop}-->
	<li><a href="?task=errdoc&do=reset&name=$vhost->name"><!--{t Reset all to default}--></a></li>
	<li><a href="?task=errdoc&do=delete&name=$vhost->name"><!--{t Delete all}--></a></li>
	</ul>

	<h3><!--{t Deny IP}--></h3>
	<form method="post" action="">
	<p><!--{t IP blacklist}-->:</p>
	<textarea name="denyips" rows="5" cols="30">$ips</textarea>
	<input type="hidden" name="op" value="denyips" />
	<input type="hidden" name="token" value="$token" />
	<input type="submit" value="<!--{t Submit}-->" />
	</form>

	<h3><!--{t Hotlink Protection}--></h3>
	<form method="post" action="">
	<p><select name="rule">
		<option value="allow"><!--{t Allow}--></option>
		<option value="deny" <!--{if !$bWhitelist}--> selected="selected"<!--{/if}-->><!--{t Deny}--></option>
	</select> <!--{t access from these sites}-->:</p>
	<textarea name="hotlink" rows="5" cols="30">$hotlinks</textarea>
	<input type="hidden" name="op" value="hotlink" />
	<input type="hidden" name="token" value="$token" />
	<input type="submit" value="<!--{t Submit}-->" />
	</form>

	<h3><!--{t Redirect}--></h3>
	<form method="post" action="">
	<p><!--{t Redirect to}-->:</p>
	<textarea name="redirect" rows="5" cols="30">$redirect</textarea>
	<input type="hidden" name="op" value="redirect" />
	<input type="hidden" name="token" value="$token" />
	<input type="submit" value="<!--{t Submit}-->" />
	</form>
	
	<h3><!--{t Removal}--></h3>
	<form method="post" action="">
	<input type="hidden" name="op" value="remove" />
	<input type="hidden" name="token" value="$token" />
	<input type="submit" value="<!--{t Remove}-->" onclick="return confirm('<!--{t Are you sure the REMOVE this site?}-->')" />
	</form>
	
	<!--{else}-->
	<!--{t No such domain hosted.}-->
	<!--{/if}-->
<!--{/if}-->
<!--{template footer}-->
