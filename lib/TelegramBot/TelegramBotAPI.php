<?php
namespace TelegramBot;
use Utils\Logger;
/**
All queries to the Telegram Bot API must be served over HTTPS and need to be presented in this form: https://api.telegram.org/bot<token>/METHOD_NAME.
**/
class TelegramBotAPI {
    private $token;
    private const BOTADDR="https://api.telegram.org/bot";
    private const ANSWERDELAY=1;
    private $lastResult;
    private $debug=false;
    
    public function __construct($token) {
	$this->token=$token;
    }
    
    public function setWebhook($hookUrl) {
	return file_get_contents($this->genUrl("setWebhook?url=".$hookUrl));
    }
    
    public function deleteWebhook() {
	return file_get_contents($this->genUrl("deleteWebhook"));
    }
    
    public function getWebhookInfo() {
    	return file_get_contents($this->genUrl("getWebhookInfo"));
    }
    
    private function genUrl($method) {
	return self::BOTADDR.$this->token."/".$method;
    }
    
    public function getLastResult() {
	return $this->lastResult;
    }
    
    public function send(TelegramMessage $message) {
	$curl=new \Curl\Curl();
	
	$url=$this->genUrl("sendMessage");
	

//	Logger::logStatic("Curl",$message->toArray());
//	Logger::logStatic("Curl",$curl->POST($url,$message->toJSON()));
	Logger::logStatic("Curl",$curl->POST($url,$message->toArray()));
	
    }
}
?>