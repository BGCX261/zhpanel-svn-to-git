#{name}_start
<VirtualHost *>
        ServerAdmin     webmaster@{name}
        DocumentRoot    {root}
        ServerName      {name}
        ServerAlias     {aliases}
        ErrorLog        "|/usr/local/sbin/cronolog /home/{owner}/logs/{name}-%Y%m%d-error_log"
        CustomLog       "|/usr/local/sbin/cronolog /home/{owner}/logs/{name}-%Y%m%d-access_log" common
        CBandUser       {owner}
        SuexecUserGroup {owner} {owner}
        <Directory />
                FCGIWrapper      /zh/cgi-system/{owner}/php5.cgi .php
        </Directory>
</VirtualHost>
#{name}_end
