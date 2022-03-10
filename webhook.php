<?php 
include("autoloader.php");

$token=\Utils\Config::getOption("token");
$debug=\Utils\Config::getOption("debug");
$data = file_get_contents('php://input');
if ($debug) file_put_contents("log/debug.json",$data);

$bot=new TelegramBot\RegtimeSRSBot($token);
$bot->receiveMessage($data);
?>