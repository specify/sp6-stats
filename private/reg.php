<?php
    include ("/etc/myauth.php");
    date_default_timezone_set('America/Chicago');
    ini_set("memory_limit", "200M");

$myFile = "/home/anhalt/reg.dat";


            $fh = fopen($myFile, 'r') or die("Unable to open file.");

            //Read the data for Registration into a string
            $data_reg = fread($fh, filesize($myFile));
            echo str_replace("\n", "<br>", $data_reg);//echo $data_reg,  "<br>";
?>
