<?php

error_reporting(E_ALL);

include('sendMsg.php');

$sendMsg = new sendMsg();

$sendMsg->simpleSend('sender@hotmail.com', 'password', 'recipient@hotmail.com','message');

echo $sendMsg->result.' '.$sendMsg->error;

?>
