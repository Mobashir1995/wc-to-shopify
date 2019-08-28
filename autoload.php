<?php
require 'vendor/autoload.php';


require_once("config.php");

spl_autoload_register(function($class){
	require_once('classes/class-'.$class.'.php');
});