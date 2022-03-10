<?php
namespace TelegramBot;

use Utils\Logger;
class RegtimeSRSBot {
    private $API;
    private $regtimeSRSAPI;
    private $lastError;
    
    public function __construct($token) {
	$this->API=new TelegramBotAPI($token);
	$this->regtimeSRSAPI=NULL;
    }
    
    public function receiveMessage($data) {
	Logger::logStatic("RegtimeSRSBotDebug","Received: ".$data);
	$this->parseMessage($data);
    }
    
    public function sendMessage($chatId,$data) {
	Logger::logStatic("RegtimeSRSBotDebug","Sending: ".$data);
	$this->API->send(new TelegramMessage($chatId,$data));
    }
    
    private function genSenderName($decoded) {
//	file_put_contents("log/debug.log",var_export($decoded,true));
//	file_put_contents("log/debug.log",var_export($decoded,true));
	$firstName=isset($decoded["message"]["from"]["first_name"]) ? $decoded["message"]["from"]["first_name"]:"Unknown";
	$lastName=isset($decoded["message"]["from"]["last_name"]) ? $decoded["message"]["from"]["last_name"]:"User";
	return $firstName." ".$lastName;
    }
    public function parseMessage($data) {
	$decoded=json_decode($data,true);
	$reply="";
	$validRequests=array(
	    "/start",
	    "/renew",
	    "/update_ns",
	    "/info",
	    "/balance",
	    "/status",
	);
	
	if (!$this->isValid($decoded)) return false;
	
	$chatId=$decoded["message"]["chat"]["id"];
	$text=explode(" ",$decoded["message"]["text"],2);
	$from=$this->genSenderName($decoded);
	
	logger::logStatic("RegtimeSRSBot",sprintf("%s %s %s",$chatId,$from,$decoded["message"]["text"]));
	
	$command=strtolower($text[0]);
	$parms=isset($text[1]) ? $text[1]:NULL;
//	if (!in_array(strtolower($text[0])) return false;
	
	switch ($command) {
	    case "/start":
//		$reply="Uhm. Hello?";
		$reply=file_get_contents("data/greeting.txt");
		break;
	    case "/renew":
//		$reply=!is_null($parms) ? "Renewing $text[1]":"No domain to renew";
		
		$reply=$this->renewDomain($chatId,$parms);
		break;
	    case "/update_ns":
//		$reply=!is_null($parms) ? "Updating NS for $text[1]":"No domain to update nameservers";
		$reply=$this->updateNS($chatId,$parms);
		break;
	    case "/info":
//		$reply=!is_null($parms) ? "Info for $text[1]":"No domain to get info";
		$reply=$this->infoDomain($chatId,$parms);
		break;
	    case "/status":
		$reply=$this->getChatStatus($chatId);
		break;
	    case "/login":
//		$reply=$this->authorizeChat($chatId,$parms) ? "Authorized":"Error: ".$this->lastError;
		$this->authorizeChat($chatId,$parms);
		$reply=$this->getChatStatus($chatId);
		break;
	    case "/logout":
		$this->unAuthorizeChat($chatId);
		$reply=$this->getChatStatus($chatId);
		break;
	    case "/balance":
		$reply=$this->getBalance($chatId);
		break;
	    default:
		$reply="I don't know $command command";
		break;
	}
	
	$this->sendMessage($chatId,$reply);
    }
    
    public function updateNS($chatId,$parms) {
	if (!$api=$this->getRegtimeSRSAPI($chatId)) return $this->lastError;
	$subdata=explode(" ",trim($parms),2);
	if ($subdata[0] == '' ) {
	    return "No domain to update NS";
	}
	
	$domainName=$subdata[0];
	$message=new \RegtimeSRS\RegtimeSRSMessage(array(
                new \RegtimeSRS\RegtimeSRSField("thisPage","pispRedelegation"),
                new \RegtimeSRS\RegtimeSRSField("domain_name",$domainName),
        	)
	);
	
	$cnt=0;
	foreach (explode(" ",$subdata[1]) as $nserver) {
	    $message->addField(new \RegtimeSRS\RegtimeSRSField("ns".$cnt++,$nserver));
	}
	
//	file_put_contents("log/debug.log",var_export($message,true));

	$res=$api->sendMessage($message);
	file_put_contents("log/debug.log",var_export($message,true));
	return $res;
    }
    private function genRegtimeSRSAPI($chatId) {
    	if (!$this->isAuthorized($chatId)) {
	    $this->lastError="Unauthorized";
    	    return false;
    	}
	$credentials=$this->getRegtimeSRSCredentials($chatId);
	$api=new \RegtimeSRS\RegtimeSRSAPI($credentials["username"],$credentials["password"]);
	$api->setInterfaceLang("ru");
	$api->setInterfaceRevision("1");
	
	return $api;
    }
    
    private function getRegtimeSRSAPI($chatId) {
	if (is_null($this->regtimeSRSAPI)) {
	    if (!$api=$this->genRegtimeSRSAPI($chatId)) return false;
	}

	$this->regtimeSRSAPI=$api;
	return $this->regtimeSRSAPI;
    }
    
    public function infoDomain($chatId,$parms) {
	if (!$api=$this->getRegtimeSRSAPI($chatId)) return $this->lastError;
	$res="";
	
	$domains=explode(" ",trim($parms));
	if ($domains[0] == '' ) {
	    return "No domain to get info";
	}

	
	foreach ($domains as $domainName) {
	    $message=new \RegtimeSRS\RegtimeSRSMessage(array(
                new \RegtimeSRS\RegtimeSRSField("thisPage","pispDomainInfo"),
                new \RegtimeSRS\RegtimeSRSField("domain_name",$domainName),
        	)
	    );
	
	$res.=$api->sendMessage($message);
	}
	return $res;
    }
    public function renewDomain($chatId,$parms) {
	if (!$api=$this->getRegtimeSRSAPI($chatId)) return $this->lastError;

	$res="";

	$domains=explode(" ",trim($parms));
	if ($domains[0] == '' ) {
	    return "No domain to renew";
	}
	
	foreach ($domains as $domainName) {
	    $message=new \RegtimeSRS\RegtimeSRSMessage(array(
                new \RegtimeSRS\RegtimeSRSField("thisPage","pispRenewDomain"),
                new \RegtimeSRS\RegtimeSRSField("domain_name",$domainName),
                new \RegtimeSRS\RegtimeSRSField("period","1"),
        	)
	    );
	
	$res.=$api->sendMessage($message);
	}
	return $res;
    }
    
    private function getBalance($chatId) {
	if (!$api=$this->getRegtimeSRSAPI($chatId)) return $this->lastError;


	$message=new \RegtimeSRS\RegtimeSRSMessage(array(
                new \RegtimeSRS\RegtimeSRSField("thisPage","pispBalance")
            )
        );

	return ($api->sendMessage($message));
//	return "STUB!";

    }
    
    private function getChatDataFilename($chatId) {
	return "data".DIRECTORY_SEPARATOR."chats".DIRECTORY_SEPARATOR.$chatId.".json";
    }
    
    private function isAuthorized($chatId) {
    	$filename=$this->getChatDataFilename($chatId);
	if (!file_exists($filename)) return false;

	return true;
    }

    private function getRegtimeSRSCredentials($chatId) {
	$filename=$this->getChatDataFilename($chatId);
	$credentials=json_decode(file_get_contents($filename),true);
	
	return $credentials;
    }
    private function getChatStatus($chatId) {
	if (!$this->isAuthorized($chatId)) return "Unauthorized";
	
	$credentials=$this->getRegtimeSRSCredentials($chatId);
	
	return sprintf("Authorized as %s",$credentials["username"]);
    }
    
    private function unAuthorizeChat($chatId) {
	$filename=$this->getChatDataFilename($chatId);
	
	if (file_exists($filename)) unlink($filename);
	
	$this->regtimeSRSAPI=NULL;
	return true;
    }
    
    private function authorizeChat($chatId,$parms) {
	if (is_null($parms)) {
	    $this->lastError="No credentials provided";
	    return false;
	}
	
	$credentials=explode(" ",$parms);
	
	if (count($credentials) !=2) {
	    $this->lastError="Incorrect credentials count";
	    return false;
	}
    
	$credentialsOut=array("username"=>$credentials[0],"password"=>$credentials[1]);
	file_put_contents(
	    $this->getChatDataFilename($chatId),
	    json_encode($credentialsOut,JSON_PRETTY_PRINT)
	);
	
	return true;
    }
    
    private function isValid($decodedMessage) {
	if (!isset($decodedMessage["message"])) {
	    $this->lastError="No message data in received message";
	    return false;
	}
	
	if (!isset($decodedMessage["message"]["chat"])) {
	    $this->lastError="No chat data in received message";
	    return false;
	}
	
	if (!isset($decodedMessage["message"]["chat"]["id"])) {
	    $this->lastError="No chat id data in received message";
	    return false;
	}
	
	return true;
    }
    
    public function genResponseText() {
    }
    

}
?>