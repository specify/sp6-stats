<?php
    include ("/etc/myauth.php");
  if ($_GET != '') {
    if (isset($_GET["dmp"])) {
        if ($_GET["dmp"] == 1) {
            $connection = mysql_connect($mysql_hst, $mysql_usr, $mysql_pwd);
            if (!$connection) {
                die ("Couldn't connect" . mysql_error());
            }

            $db_select = mysql_select_db("feedback");
            if (!$db_select) {
              die ("Couldn't 'select_db' " . mysql_error());
            }

            $sql = "select count(*) from feedback";
            $result = mysql_query($sql) or die(mysql_error());

            while ( $row = mysql_fetch_array($result) )
            {
                foreach ( $row AS $key=>$value )
                {
                    echo "Number of Entries: $value <br>";
                    break;
                }
            }

            $sql = "SELECT * FROM feedback ORDER BY FeedbackID DESC";
            $result = mysql_query($sql) or die(mysql_error());

            echo "<html>\n";
            echo "<html><body><table border=1>\n";
            echo "<style>";
            echo " table {border-right: solid 1px gray; }";
            echo " table {border-bottom: solid 1px gray; }";
            echo " td    { border-left: 1px solid gray; border-top: 1px solid gray; }\n";
            echo " th    { border-left: 1px solid gray; border-top: 1px solid gray; }\n";
            echo "</style>";
            echo "<body><table border=0 cellspacing=0>\n";
            $printed_headers = 0;
            while ( $row = mysql_fetch_array($result) )
            {
                if (!$printed_headers) {
                    //print the headers once:
                    echo "<tr>";
                    foreach ( array_keys($row) AS $header )
                    {
                        //you have integer keys as well as string keys because of the way PHP 
                        //handles arrays.
                        if ( !is_int($header) )
                        {
                            echo "<th>$header</th>";
                        }
                    }
                    echo "</tr>";
                    $printed_headers = true;
                }

                //print the data row
                echo "<tr>";
                foreach ( $row AS $key=>$value )
                {
                    if ( !is_int($key) )
                    {
                        if (strlen($value) == 0) {
                            echo "<td>&nbsp;</td>";
                        } else {
                            echo "<td>$value</td>";
                        }
                    }
                }
                echo "</tr>"; 
            }
            echo "</table></body></html>";
            mysql_close($connection);
        }
        return;
    }
  }

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
        $connection = mysql_connect($mysql_hst, $mysql_usr, $mysql_pwd);
        if (!$connection) {
            die ("Couldn't connect" . mysql_error());
        }

        $db_select = mysql_select_db("feedback");
        if (!$db_select) {
          die ("Couldn't 'select_db' " . mysql_error());
        }
        
        $Timestamp   = date("y-m-d") ." " . date("H:i:s");
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

        $updateStr = "INSERT INTO feedback ( " .
                     "TimestampCreated,Subject,Component,Issue,Comments,Id,OSName,OSVersion,JavaVersion," .
                     "JavaVendor,AppVersion,Collection,Discipline,Division,Institution) " .

         "VALUES('$Timestamp', '$Subject', '$Component', '$Issue', '$Comments', '$Id', '$OSName', '$OSVersion', '$JavaVersion', " .
         "'$JavaVendor', '$AppVersion', '$Collection', '$Discipline', '$Division', '$Institution') ";

        echo "INSERT-> " . $updateStr . "\n";
        $result = mysql_query($updateStr) or die(mysql_error());
            
        mysql_close($connection);
    }
    echo "ok";

  } else {
    echo "No arguments!<br>";
  }

?>
