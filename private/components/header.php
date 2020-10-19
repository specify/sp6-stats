<?php


require_once(dirname(__FILE__).'/functions.php');
require_file('../config/required.php');
require_file('../config/optional.php');


if(!DEVELOPMENT || SHOW_ERRORS_IN_PRODUCTION){
	error_reporting(E_ALL | E_STRICT);
	ini_set('display_errors', 1);
}

if(DEVELOPMENT || USE_NON_MINIFIED_FILES_IN_PRODUCTION){
	define('CSS_EXTENSION','.css');
	define('JS_EXTENSION','.js');
}
else {
	define('CSS_EXTENSION','.min.css');
	define('JS_EXTENSION','.min.js');
}

if(!file_exists(WORKING_DIRECTORY))
	mkdir(WORKING_DIRECTORY,0755,TRUE);

if(defined('MEMORY_LIMIT'))
	ini_set('memory_limit', MEMORY_LIMIT);

if(defined('TIMEZONE'))
	date_default_timezone_set(TIMEZONE);
else
	date_default_timezone_set('America/Chicago');

if(defined('DATABASE'))
	require(dirname(__FILE__).'/mysql.php');

if(!defined('NO_HEAD') || NO_HEAD!==TRUE){

?><!-- Developed by Specify Software (https://www.specifysoftware.org/) -->
<!DOCTYPE html>
<html lang="en">
<head>

	<meta charset="utf-8">
	<title>Specify 6 Stats</title>
	<meta
			name="viewport"
			content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta
			name="author"
			content="Specify Software">
	<meta
			name="theme-color"
			content="#145a8d"/>
	<meta
			name="robots"
			content="noindex,nofollow">
	<meta
			name="apple-mobile-web-app-title"
			content="Specify 6 Stats">
	<meta
			name="application-name"
			content="Specify 6 Stats">
	<meta
			name="description"
			content="Specify 6 Stats">
	<link
			rel="icon"
			type="image/png"
			sizes="150x150"
			href="https://www.specifysoftware.org/wp-content/uploads/2017/06/sp_project_square-1-150x150.png">
	<link
				rel="stylesheet"
				href="<?=LINK?>static/css/main<?=CSS_EXTENSION?>"> <?php

	if(defined('CSS')) { ?>
		<link
				rel="stylesheet"
				href="<?=LINK?>static/css/<?=CSS?><?=CSS_EXTENSION?>"> <?php
	}

	if(!defined('BOOTSTRAP') || BOOTSTRAP){ ?>
		<link
				rel="stylesheet"
				href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/css/bootstrap.min.css"
				integrity="sha256-aAr2Zpq8MZ+YA/D6JtRD3xtrwpEz2IqOS+pWD/7XKIw="
				crossorigin="anonymous"/> <?php
	}

	if(defined('JQUERY') && JQUERY){ ?>
		<script
				src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.slim.min.js"
				integrity="sha256-4+XzXVhsDmqanXGHaHvgh1gMQKX40OUvDEBTu8JcmNs="
				crossorigin="anonymous"></script> <?php
	}

	if(defined('JS')){ ?>
		<script src="<?=LINK?>static/js/<?=JS?><?=JS_EXTENSION?>"></script> <?php
	} ?>

</head>
<body class="mb-4">
	<h1>Specify 6 Feedback Stats</h1><?php

}