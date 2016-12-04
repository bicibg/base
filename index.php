<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


$root = $_SERVER["DOCUMENT_ROOT"];
require("$root/include/head.php");
try{
	if(isset($_REQUEST["page"])){
		if(file_exists("$root/pages/$_REQUEST[page].php")){
			require_once("$root/pages/$_REQUEST[page].php");
			if(function_exists($_REQUEST["page"])){
				$_REQUEST["page"]();
			}
		}elseif(function_exists($_REQUEST["page"])){
			$_REQUEST["page"]();
		}
	}else{
		index();
	}
}catch(Exception $e){
	throw $e;
}


function index()
{
    $_SESSION["lang"] = "ENG";
    translate();
}
require("$root/include/foot.php");
