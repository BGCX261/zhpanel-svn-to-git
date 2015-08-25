<!--{template header}-->
<!--{if $task=='add'}-->
<form method="post" action="">
<dl>
	<dt><!--{t Username}--></dt>
	<dd>$prefix<input name="name" type="text" value="$_REQUEST[name]" />
	<!--{if $err[name]}--><b class="invalid">$err[name]</b><!--{/if}-->
	</dd>
	<dt><!--{t Password}--></dt>
	<dd><input name="password" type="text" value="$_REQUEST[password]" />
	<!--{if $err[password]}--><b class="invalid">$err[password]</b><!--{/if}-->
	</dd>
	<dt><!--{t Directory}--></dt>
	<dd><select id="docroot" name="docroot">
	<!--{loop $dirs $d}-->
		<option value="$d">$d</option>
	<!--{/loop}-->
	</select>
	<!--{if $err[docroot]}--><b class="invalid">$err[docroot]</b><!--{/if}-->
	</dd>
    <dt>&nbsp;</dt><dd><input type="submit" value="<!--{t Submit}-->" />
	<input type="hidden" name="token" value="$token" /><input type="hidden" name="op" value="edit" /></dd>
</dl>
</form>
<!--{elseif $task=='list'}-->
<table border='1' width='100%'>
<tr><th><!--{t Login Name}--></th><th><!--{t Directory}--></th><th><!--{t Status}--></th><th><!--{t Operations}--></th>
<!--{loop $accounts $account}-->
	<tr>
	<td><a href="ftp.php?task=edit&amp;name=$account->user">$account->user</a></td>
	<td>$account->home</td>
	<td align="center"><!--{if $account->state}--><!--{s 1,name=$account->user}--><!--{else}--><!--{s 0,name=$account->user}--><!--{/if}--></td>
	<td align="center">
		<!--{b edit,name=$account->user}-->
		<!--{b delete,name=$account->user}-->
	</td>
	</tr>
<!--{/loop}-->
</table>
<!--{elseif $task=='edit'}-->
<input type="button" value="<!--{t Back}-->" onclick="history.back()" />
<h3><!--{t Details}--></h3>
<form method="post" action="">
<dl>
	<dt><!--{t Username}--></dt>
	<dd><input name="name" type="text" value="$account->user" readonly />
	<!--{if $err[name]}--><b class="invalid">$err[name]</b><!--{/if}-->
	</dd>
	<dt><!--{t Password}--></dt>
	<dd><input name="password" type="text" value="" />
	<!--{if $err[password]}--><b class="invalid">$err[password]</b><!--{/if}-->
	</dd>
	<dt><!--{t Directory}--></dt>
	<dd><select id="docroot" name="docroot">
	<!--{loop $dirs $d}-->
		<option value="$d" <!--{if $d==$docroot}-->selected<!--{/if}-->>$d</option>
	<!--{/loop}-->
	</select>
	<!--{if $err[docroot]}--><b class="invalid">$err[docroot]</b><!--{/if}-->
	</dd>
	<!-- optional details
    <dt><!--{t Upload Bandwidth}--></dt>
	<dd><input type="text" name="upload_bandwidth" value="$account->upload_bandwidth" /></dd>
    <dt><!--{t Download Bandwidth}--></dt>
	<dd><input type="text" name="download_bandwidth" value="$account->download_bandwidth" /></dd>
    <dt><!--{t Upload Ratio}--></dt>
	<dd><input type="text" name="upload_ratio" value="$account->upload_ratio" /></dd>
    <dt><!--{t Download Ratio}--></dt>
	<dd><input type="text" name="download_ratio" value="$account->download_ratio" /></dd>
    <dt><!--{t Max Number of Connections}--></dt>
	<dd><input type="text" name="max_conn" value="$account->max_conn" /></dd>
    <dt><!--{t Files Quota}--></dt>
	<dd><input type="text" name="files_quota" value="$account->files_quota" /></dd>
    <dt><!--{t Size Quota}--></dt>
	<dd><input type="text" name="size_quota" value="$account->size_quota" /></dd>
    <dt><!--{t Authorized local IPs}--></dt>
	<dd><input type="text" name="auth_local_ips" value="$account->auth_local_ips" /></dd>
    <dt><!--{t Refused local IPs}--></dt>
	<dd><input type="text" name="refused_local_ips" value="$account->refused_local_ips" /></dd>
    <dt><!--{t Authorized client IPs}--></dt>
	<dd><input type="text" name="auth_client_ips" value="$account->auth_client_ips" /></dd>
    <dt><!--{t Refused client IPs}--></dt>
	<dd><input type="text" name="refused_client_ips" value="$account->refused_client_ips" /></dd>
    <dt><!--{t Time Restrictions}--></dt>
	<dd><input type="text" name="time_restrictions" value="$account->time_restrictions" /></dd>
	-->

    <dt>&nbsp;</dt><dd><input type="submit" value="<!--{t Submit}-->" />
	<input type="hidden" name="token" value="$token" /><input type="hidden" name="op" value="edit" /></dd>
</dl>
</form>
<h3><!--{t Removal}--></h3>
<form method="post" action="">
<input type="hidden" name="op" value="removeaccount" />
<input type="hidden" name="token" value="$token" />
<input type="submit" value="<!--{t Remove}-->" />
</form>
<!--{/if}-->
<!--{template footer}-->
