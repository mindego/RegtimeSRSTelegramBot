<?php
namespace Utils;

class Logger {
    private $logFile;

    
    public function __construct($logFile) {
	$this->logFile=$logFile;
    }

    public function log($data) {
	$logMessage=sprintf("%s %s\n",date(DATE_ATOM),$data);
	
	file_put_contents($this->logFile,$logMessage,FILE_APPEND);
    }
    
    public static function logStatic($logFacility,$data) {
	$instance=new Logger("log".DIRECTORY_SEPARATOR.$logFacility.".log",$data);
	$instance->log($data);
    }
    
}
?>