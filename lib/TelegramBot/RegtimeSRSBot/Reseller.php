<?php
namespace TelegramBot\RegtimeSRSBot;

class Reseller {
    private $name;
    private $username,$password;
    private $chats;
    
    public function __construct($name) {
	$this->name=$name;
	$this->username="";
	$this->password="";
	$this->chats=array();
    }
    
    public function addChat($chatId) {
	if (in_array($chatId,$this->chats)) return false;
	
	$this->chats[]=$chatId;
    }
    
    private function loadResellerData() {
	$filename=$this->getResellerDataFilename();
	if (!file_exists($filename)) return false;
	
	$data=json_decode(file_get_contents($filename),true);
	
	$this->login=$data["username"];
	$this->password=$data["password"];
	
	$this->chats=$data["chats"];
    }
    
    private function saveResellerData() {
	$filename=$this->getResellerDataFilename();
	
	$data=array(
	    "username"=>$this->username,
	    "password"=>$this->password,
	    "chats"=>$this->chats
	);
	
	return file_put_contents($filename,json_encode($data,JSON_PRETTY_PRINT));
    }
    
    private function getResellerDataFilename() {
	return $filename="data".DIRECTORY_SEPARATOR."chats".DIRECTORY_SEPARATOR.$this->name.".json";
    }

}
?>