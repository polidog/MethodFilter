<?php
require "../vendor/autoload.php";

class Hoge 
{
	public function test1($str) {
		echo "exec:".$str."\n";
	}
}

$hoge = new \Polidog\MethodFilter\MethodFilter(new Hoge());
$hoge->call('addFilter',array('pre','test1',function($arguments) {
		$arguments[0] = "hohohohohohoo";
		return $arguments;
	}
));
$hoge->test1("test");