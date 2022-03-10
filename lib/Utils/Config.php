<?php
namespace Utils;
class Config {
    private static $instance;
    private $options;
    
    public function __construct($filename="data/configuration.ini") {
	$this->options=array();
	$this->loadConfig($filename);
    }
    
    final public function loadConfig($filename) {
	if (!file_exists($filename)) return false;
	
	$data=file_get_contents($filename);
	foreach (explode("\n",$data) as $confline) {
	    if (trim($confline)=="") continue;
	    
	    $namevalue=explode("=",trim($confline),2);
	    $this->setConfigOption($namevalue[0],$namevalue[1]);
	}
	return true;
    }
    
    public static function getOption($name) {
	$instance=self::getInstance();
	
	return $instance->getConfigOption($name);
    }
    public static function saveConfig($filename) {
	$data="";
	$instance=self::getInstance();
	foreach ($instance->options as $name=>$value) {
	    $data.=sprintf("%s=%s\n",$name,$value);
	}
	file_put_contents($filename,$data);
    }
    
    public static function dumpConfig() {
	$data="";
	$instance=self::getInstance();
	foreach ($instance->options as $name=>$value) {
	    $data.=sprintf("%s=%s\n",$name,$value);
	}
	echo $data;
    }
    private function getInstance() {
	if (!isset(self::$instance)) self::$instance=new Config();
	
	return self::$instance;
    }
    
    public function getConfigOption($name) {
	if (!isset($this->options[$name])) return false;
	
	return $this->options[$name];
    }
    
    public function setConfigOption($name,$value) {
	$this->options[$name]=$value;
    }
}
?>