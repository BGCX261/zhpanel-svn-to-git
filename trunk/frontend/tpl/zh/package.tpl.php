<!--{template header}-->
<!--{if $task=='add'}-->
<form method="post" action="">
<dl>
	<dt><!--{t Package Name}--></dt>
	<dd><input name="name" type="text" value="$_REQUEST[name]" />
	<!--{if $err[name]}--><b class="invalid">$err[name]</b><!--{/if}-->
	</dd>
	<dt><!--{t Description}--></dt>
	<dd><input name="desc" type="text" value="$_REQUEST[desc]" />
	<!--{if $err[desc]}--><b class="invalid">$err[desc]</b><!--{/if}-->
	</dd>
	<dt><!--{t Disk}--></dt>
	<dd><input name="space" type="text" value="$_REQUEST[space]" /><!--{t e.g. 100M, 20G}-->
	<!--{if $err[space]}--><b class="invalid">$err[space]</b><!--{/if}-->
	</dd>
	<dt><!--{t Bandwidth}--></dt>
	<dd><input name="bandwidth" type="text" value="$_REQUEST[bandwidth]" /><!--{t e.g. 100M, 20G}-->
	<!--{if $err[bandwidth]}--><b class="invalid">$err[bandwidth]</b><!--{/if}-->
	</dd>
	<dt><!--{t Sites}--></dt>
	<dd><input name="site" type="text" value="$_REQUEST[site]" />
	<!--{if $err[site]}--><b class="invalid">$err[site]</b><!--{/if}-->
	</dd>
	<dt><!--{t FTP}--></dt>
	<dd><input name="ftp" type="text" value="$_REQUEST[ftp]" />
	<!--{if $err[ftp]}--><b class="invalid">$err[ftp]</b><!--{/if}-->
	</dd>
	<dt><!--{t Databases}--></dt>
	<dd><input name="sql" type="text" value="$_REQUEST[sql]" />
	<!--{if $err[sql]}--><b class="invalid">$err[sql]</b><!--{/if}-->
	</dd>
    <dt>&nbsp;</dt><dd><!--{t *Value -1 for unlimited}--></dd>
    <dt>&nbsp;</dt><dd><input type="submit" value="<!--{t Submit}-->" />
	<input type="hidden" name="token" value="$token" /></dd>
</dl>
</form>
<!--{elseif $task=='list'}-->
<table border='1' width='100%'>
<tr><th><!--{t Package Name}--></th><th><!--{t Description}--></th><th><!--{t Status}--></th><th><!--{t Operations}--></th>
<!--{loop $packages $pkg}-->
	<tr>
	<td><a href="package.php?task=edit&amp;id=$pkg->id">$pkg->name</a></td>
	<td>$pkg->desc</td>
	<td align="center"><!--{if $pkg->state}--><!--{s 1,id=$pkg->id}--><!--{else}--><!--{s 0,id=$pkg->id}--><!--{/if}--></td>
	<td align="center">
		<!--{b edit,id=$pkg->id}-->
		<!--{b delete,id=$pkg->id}-->
	</td>
	</tr>
<!--{/loop}-->
</table>
<!--{elseif $task=='edit'}-->
<input type="button" value="<!--{t Back}-->" onclick="history.back()" />
	<!--{if $package}-->
<h3><!--{t Details}--></h3>
<form method="post" action="">
<dl>
	<dt><!--{t Package Name}--></dt>
	<dd><input name="name" type="text" value="$package->name" />
	<!--{if $err[name]}--><b class="invalid">$err[name]</b><!--{/if}-->
	</dd>
	<dt><!--{t Description}--></dt>
	<dd><input name="desc" type="text" value="$package->desc" />
	<!--{if $err[desc]}--><b class="invalid">$err[desc]</b><!--{/if}-->
	</dd>
	<dt><!--{t Disk}--></dt>
	<dd><input name="space" type="text" value="$package->space" /><!--{t e.g. 100M, 20G}-->
	<!--{if $err[space]}--><b class="invalid">$err[space]</b><!--{/if}-->
	</dd>
	<dt><!--{t Bandwidth}--></dt>
	<dd><input name="bandwidth" type="text" value="$package->bandwidth" /><!--{t e.g. 100M, 20G}-->
	<!--{if $err[bandwidth]}--><b class="invalid">$err[bandwidth]</b><!--{/if}-->
	</dd>
	<dt><!--{t Sites}--></dt>
	<dd><input name="site" type="text" value="$package->site" />
	<!--{if $err[site]}--><b class="invalid">$err[site]</b><!--{/if}-->
	</dd>
	<dt><!--{t FTP}--></dt>
	<dd><input name="ftp" type="text" value="$package->ftp" />
	<!--{if $err[ftp]}--><b class="invalid">$err[ftp]</b><!--{/if}-->
	</dd>
	<dt><!--{t Databases}--></dt>
	<dd><input name="sql" type="text" value="$package->sql" />
	<!--{if $err[sql]}--><b class="invalid">$err[sql]</b><!--{/if}-->
	</dd>
	<dt><!--{t Enabled}--></dt>
	<dd><input name="state" type="checkbox" value="1" <!--{if $package->state}-->checked<!--{/if}--> />
	</dd>
    <dt>&nbsp;</dt><dd><!--{t *Value -1 for unlimited}--></dd>
    <dt>&nbsp;</dt><dd><input type="submit" value="<!--{t Submit}-->" />
	<input type="hidden" name="op" value="edit" />
	<input type="hidden" name="token" value="$token" /></dd>
</dl>
</form>
<h3><!--{t Removal}--></h3>
<form method="post" action="">
<input type="hidden" name="op" value="remove" />
<input type="hidden" name="token" value="$token" />
<input type="submit" value="<!--{t Remove}-->" />
</form>
	<!--{else}-->
	<!--{t No such package defined.}-->
	<!--{/if}-->
<!--{/if}-->
<!--{template footer}-->