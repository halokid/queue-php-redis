<?php

if( isset($_SERVER['REMOTE_ADDR'] )) {
	die('NOT REMOTE ADDR');
}


$_SERVER['PATH_INFO'] = $_SERVER['REQUEST_URI'] = $argv[1];
require dirname(__FILE__).'/index.php';

?>
