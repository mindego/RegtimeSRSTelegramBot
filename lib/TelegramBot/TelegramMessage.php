<?php
namespace TelegramBot;

class TelegramMessage {
	private $chat_id;
	private $text;
/*	        $response = array(
            "chat_id" => $decoded["message"]["chat"]["id"],
//          "parse_mode" => "HTML",
//          "parse_mode" => "MarkdownV2",
            "text" => $text
        );*/
        
        public function __construct($chat_id,$text) {
	    $this->chat_id=$chat_id;
	    $this->text=$text;
        }

	public function toJSON() {
	    return json_encode($this->toArray());
	}
	
	public function toArray() {
	    $data=array(
		"chat_id"=>$this->chat_id,
		"text"=>$this->text,
	    );
	    return $data;
	}

}
?>