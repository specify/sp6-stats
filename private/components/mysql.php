<?php

global $mysqli;

if(!isset($database) && !defined('DATABASE'))
	exit('DATABASE constant is not defined');
elseif(!isset($database))
	$database = DATABASE;

$mysqli = new mysqli(MYSQL_HOST, MYSQL_USER, MYSQL_PASSWORD, $database);

if ($mysqli->connect_error)
	die("Connection failed: " . $mysqli->connect_error);