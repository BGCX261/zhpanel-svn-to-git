<?php
require_once 'lib/phprpc/phprpc_client.php';

$client = new PHPRPC_Client('http://localhost/zh2/rpc.php');

echo $client->do_login();