<?php
    include ("/etc/myauth.php");

  if ($_POST != '') {

    $cnt = 0;
    foreach (array_keys($_POST) as $p) {
        $cnt++;
    }

    $dateTime =  "date=" . date("y/m/d") ." " . date("H:i:s") . "\n";
    $data = "---------------\n" . $dateTime;
    $data = $data . "ip=" . $_SERVER['REMOTE_ADDR'] . "\n";
    if ($cnt == 0) {
        echo "No arguments!<br>";
    } else {
        foreach (array_keys($_POST) as $p) {
             $data = $data . "$p=$_POST[$p]\n";
        }
    }

    if ($cnt > 0)
    {
        $mysqli = new mysqli($mysql_hst, $mysql_usr, $mysql_pwd, "feedback");

        if ($mysqli->connect_errno) {
            die("failed to connect to mysql" . $mysqli->connect_error);
        }

        $Timestamp   = date("Y-m-d H:i:s");
        $Subject     = $_POST['title'];
        $Issue       = $_POST['bug'];
        $Component   = $_POST['task_name'];
        $Comments    = $_POST['comments'];
        $Id          = $_POST['id'];
        $OSName      = $_POST['os_name'];
        $OSVersion   = $_POST['os_version'];
        $JavaVersion = $_POST['java_version'];
        $JavaVendor  = $_POST['java_vendor'];
        $AppVersion  = $_POST['app_version'];
        $Collection  = $_POST['collection'];
        $Discipline  = $_POST['discipline'];
        $Division    = $_POST['division'];
        $Institution = $_POST['institution'];

        $updateStr = $mysqli->prepare(
            "INSERT INTO feedback ( " .
            "TimestampCreated,Subject,Component,Issue,Comments,Id,OSName,OSVersion,JavaVersion," .
            "JavaVendor,AppVersion,Collection,Discipline,Division,Institution) " .
            "VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $updateStr->bind_param(
            "sssssssssssssss",
            $Timestamp, $Subject, $Component, $Issue, $Comments, $Id, $OSName, $OSVersion, $JavaVersion,
            $JavaVendor, $AppVersion, $Collection, $Discipline, $Division, $Institution
        );
        if(!$updateStr->execute()) throw new Exception($mysqli->error);
        $mysqli->close();
    }
    echo "ok";

  } else {
    echo "No arguments!<br>";
  }

?>
