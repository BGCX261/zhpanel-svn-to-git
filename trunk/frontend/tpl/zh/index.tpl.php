<!--{template header}-->
<!--{if $task=='settings'}-->
<form method="post" action="">
<table>
	<tr>
		<td colspan="3" class="table_header"><!--{t HTTPd Settings:}--></td>
	</tr>
	<tr>
		<td width="250" align="right"><!--{t httpd.conf path:}--></td>
		<td width="250">
			<input name="httpd_conf" type="text" value="$sys->httpd_conf" />
		</td>
		<td>
			<!--{if $err[httpd_conf]}--><b class="invalid">$err[httpd_conf]</b><!--{/if}-->
		</td>
	</tr>
	<tr>
		<td colspan="3" class="table_header"><!--{t FTPd Settings:}--></td>
	</tr>
	<tr>
		<td align="right"><!--{t FTPd Software:}--></td>
		<td>
			<!-- <input name="ftp_software" type="text" value="$sys->ftp_software" /> -->
			<select name="ftp_software">
				<option value="proftpd" selected="selected">proftpd</option>
				<option value="pureftpd">pureftpd</option>
			</select>
		</td>
		<td>
			<!--{if $err[ftp_software]}--><b class="invalid">$err[ftp_software]</b><!--{/if}-->
		</td>
	</tr>
	<tr>
		<td align="right"><!--{t pureftpd.passwd path:}--></td>
		<td>
			<input name="ftp_passwd" type="text" value="$sys->ftp_passwd" />
		</td>
		<td>
			<!--{if $err[ftp_passwd]}--><b class="invalid">$err[ftp_passwd]</b><!--{/if}-->
		</td>
	</tr>
	<tr>
		<td colspan="3" class="table_header"><!--{t MySQL Settings:}--></td>
	</tr>
	<tr>
		<td align="right"><!--{t DB Host:}--></td>
		<td>
			<input name="dbhost" type="text" value="$sys->dbhost" />
		</td>
		<td>
			<!--{if $err[dbhost]}--><b class="invalid">$err[dbhost]</b><!--{/if}-->
		</td>
	</tr>
	<tr>
		<td align="right"><!--{t DB User:}--></td>
		<td>
			<input name="dbuser" type="text" value="$sys->dbuser" />
		</td>
		<td>
			<!--{if $err[dbuser]}--><b class="invalid">$err[dbuser]</b><!--{/if}-->
		</td>
	</tr>
	<tr>
		<td align="right"><!--{t DB Pass:}--></td>
		<td>
			<input name="dbpass" type="password" value="$sys->dbpass" />
		</td>
		<td>
			<!--{if $err[dbpass]}--><b class="invalid">$err[dbpass]</b><!--{/if}-->
		</td>
	</tr>
	<tr>
		<td colspan="3" class="table_header"><!--{t ZhAlert Settings: (Provides system event notifications via MSN, SMS.)}--></td>
	</tr>
	<tr>
		<td align="right"><!--{t MSN Robot Sender:}--></td>
		<td>
			<input name="msn_sender" type="text" value="$sys->msn_sender" />
		</td>
		<td>
			<!--{if $err[msn_sender]}--><b class="invalid">$err[msn_sender]</b><!--{/if}-->
		</td>
	</tr>
	<tr>
		<td align="right"><!--{t MSN Robot Password:}--></td>
		<td>
			<input name="msn_password" type="password" value="$sys->msn_password" />
		</td>
		<td>
			<!--{if $err[msn_password]}--><b class="invalid">$err[msn_password]</b><!--{/if}-->
		</td>
	</tr>
	<tr>
		<td align="right"><!--{t MSN Alert To:}--></td>
		<td>
			<input name="msn_sendto" type="text" value="$sys->msn_sendto" />
		</td>
		<td>
			<!--{if $err[msn_sendto]}--><b class="invalid">$err[msn_sendto]</b><!--{/if}-->
		</td>
	</tr>
	<tr>
		<td align="right"><!--{t SMS Alert Sender To(xxx@139.com):}--></td>
		<td>
			<input name="sms_sendto" type="text" value="$sys->sms_sendto" />
		</td>
		<td>
			<!--{if $err[sms_sendto]}--><b class="invalid">$err[sms_sendto]</b><!--{/if}-->
		</td>
	</tr>
	<tr>
		<td colspan="3" class="table_header"><!--{t Appearance Settings:}--></td>
	</tr>
	<tr>
		<td align="right"><!--{t Language:}--></td>
		<td>
			<!-- <input name="language" type="text" value="$sys->language" /> -->
			<select name="language">
				<option value="en" selected="selected">English</option>
			</select>
		</td>
		<td>
			<!--{if $err[language]}--><b class="invalid">$err[language]</b><!--{/if}-->
		</td>
	</tr>
	<tr>
		<td align="right"><!--{t Theme:}--></td>
		<td>
			<!-- <input name="theme" type="text" value="$sys->theme" /> -->
			<select name="theme">
				<!--{loop $themes $theme}-->
				<option value="$theme->name"<!--{if $sys->theme==$theme->name}--> selected="selected"<!--{/if}-->>$theme->description - $theme->version</option>
				<!--{/loop}-->
			</select>
		</td>
		<td>
			<!--{if $err[theme]}--><b class="invalid">$err[theme]</b><!--{/if}-->
		</td>
	</tr>
	<tr>
		<td colspan='3'>
			<input type="submit" value="<!--{t Update}-->" />
			<input type="hidden" name="token" value="$token" />
		</td>
	</tr>
</table>
</form>
<!--{else}-->
<h3><!--{t Logged as}--> $user [$group]</h3>
<!--{if isadmin()}-->
<p><a href="?refresh=1"><!--{Force refresh services status}--></a></p>
<table>
<tr><th><!--{t Service}--></th><th><!--{t Version}--></th><th><!--{t Uptime}--></th><th><!--{t Status}--></th><th><!--{t Operations}--></th>
<!--{loop $services $s}-->
	<tr>
	<td>$s->name</td>
	<td>$s->version</td>
	<td>$s->uptime</td>
	<td align="center"><!--{if $s->state}--><!--{s 1}--><!--{else}--><!--{s 0}--><!--{/if}--></td>
	<td align="center">
    <a href="?task=ctl&a=$s->name&o=start"><!--{t Start}--></a>
    <a href="?task=ctl&a=$s->name&o=stop"><!--{t Stop}--></a>
    <a href="?task=ctl&a=$s->name&o=restart"><!--{t Restart}--></a>
    </td>
	</tr>
<!--{/loop}-->
</table>
<!--{/if}-->
<h3><!--{t Statistics}--></h3>
<table>
<tr>
	<th><!--{t Item}--></th><th><!--{t Status}--></th>
</tr>
<!--{loop $stats $s}-->
	<tr>
	<td>$s->name</td>
	<td><!--{bar $s->percent}-->$s->state</td>
	</tr>
<!--{/loop}-->
</table>
<!--{/if}-->
<!--{template footer}-->
