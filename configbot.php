<?php 
include("autoloader.php");

$token=\Utils\Config::getOption("token");

$API=new TelegramBot\TelegramBotAPI($token);

echo sprintf("Before: %s\n",$API->getWebhookInfo());
//echo sprintf("Setting... %s\n",$API->setWebhook("https://berendeevdom.ru/turnip-bot/webhook.php"));
//echo sprintf("After: %s\n",$API->getWebhookInfo());

?>