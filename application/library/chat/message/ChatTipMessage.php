<?php
namespace app\library\chat\message;
class ChatTipMessage extends ChatMessage{
	
	private $text ;
	
	public function __construct($text, $to){
		parent::__construct($to);
		$this->text = $text;
	}
	
	public function getText(){
		return $this->text;
	}
	
	public function setText($text){
		$this->text = $text;
	}
	
	public function getMobParams(){
		$this->setAttr("type", "tip");
		return ['type' => 'txt', 'msg' => $this->text];
	}
}