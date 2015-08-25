#!/bin/sh
PHPRC="/home/{user}/etc/php5.ini"
export PHPRC
exec /zh/php5/bin/php-cgi $@