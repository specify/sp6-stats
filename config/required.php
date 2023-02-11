<?php

define('DEVELOPMENT',getenv('DEVELOPMENT') == 'TRUE');
define('LINK', getenv('LINK'));
define('TRACK_DAT_LOCATION','/home/specify/track.dat');
define('REG_DAT_LOCATION','/home/specify/reg.dat');

# Set this to an empty folder. This would be the destination for all uncompressed
# access.log and other files created in the process.
# Make sure the web server has write permissions to this folder.
# **Warning!** All of the files present in this directory would be deleted.
const WORKING_DIRECTORY = '/home/specify/working-dir/';

define('MYSQL_USER', getenv('MYSQL_USER'));
define('MYSQL_PASSWORD', getenv('MYSQL_PASSWORD'));
define('MYSQL_HOST', getenv('MYSQL_HOST'));
