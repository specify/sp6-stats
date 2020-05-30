<?php

if(file_exists("/etc/myauth.php"))
	include("/etc/myauth.php");

if(!isset($mysql_hst))
	$mysql_hst = 'host.docker.internal';

if(!isset($mysql_usr))
	$mysql_usr = 'root';

if(!isset($mysql_pwd))
	$mysql_pwd = 'root';

if(!defined('DATABASE'))
	exit();

$mysqli = new mysqli($mysql_hst, $mysql_usr, $mysql_pwd, DATABASE);

if ($mysqli->connect_error)
	die("Connection failed: " . $mysqli->connect_error);