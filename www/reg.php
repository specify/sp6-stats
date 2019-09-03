<?php
    include ("/etc/myauth.php");
    date_default_timezone_set('America/Chicago');
    ini_set("memory_limit", "200M");

  if ($_GET != '') {
    if (isset($_GET["dmp"])) {
        if ($_GET["dmp"] == 1) {
            $myFile = "reg.dat";
            $fh = fopen($myFile, 'r') or die("Unable to open file.");

            //Read the data for Registration into a string
            $data_reg = fread($fh, filesize($myFile));
            echo str_replace("\n", "<br>", $data_reg);//echo $data_reg,  "<br>";
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

    $reg_number = "";
    $key = "reg_number";
    if (isset($_POST[$key])) {

      $reg_number = $_POST[$key];

    } else {

      $uTime = microtime(true);
      $data  = $data . "reg_number=" . $uTime . "\n";
      $reg_number = $uTime;
    }

    if ($cnt == 0) {
        echo "No arguments!<br>";
    } else {
        foreach (array_keys($_POST) as $p) {
             $data = $data . "$p=$_POST[$p]\n";
        }
    }

    $myFile = "reg.dat";
    $fh = fopen($myFile, 'a') or die("can't open file");
    fwrite($fh, $data);
    fclose($fh);

    if ($cnt > 0)
    {
        $connection = mysql_connect($mysql_hst, $mysql_usr, $mysql_pwd);
        if (!$connection) {
            die ("Couldn't connect" . mysql_error());
        }

        $db_select = mysql_select_db("stats");
        if (!$db_select) {
          die ("Couldn't 'select_db' " . mysql_error());
        }

        $reg_type = $_POST['reg_type'];
        $query    = "SELECT RegisterID FROM register WHERE RegNumber = '" . $reg_number . "'";

        $doInsert = 1;
        $result   = mysql_query($query);
        if ($result) {
            $row = mysql_fetch_row(($result));

            if ($row) {
                $doInsert = 0;
                $registerId  = $row[0];

                foreach (array_keys($_POST) as $p) {
                    $doItemInsert = 1;

                    $query = "SELECT RegisterItemID FROM registeritem WHERE RegisterID = " . $registerId . " AND Name ='" . $p ."'";
                    /* echo "SEL: " . $query . "\n"; */
                    $result = mysql_query($query);
                    if ($result) {
                        $row = mysql_fetch_row(($result));
                        if ($row)
                        {
                            $doItemInsert = 0;
                            $regItemId    = $row[0];
                            $valStr       = $_POST[$p];
                            $updateStr    = "UPDATE registeritem SET ";
                            if (strlen($valStr) && is_numeric($valStr) && !stripos($p, "_number", 0))
                            {
                                $updateStr .= "CountAmt=" . $valStr . ", Value=NULL";
                            } else {
                                $updateStr .= "Value='" . $valStr . "', CountAmt=NULL";
                            }
                            $updateStr .= " WHERE RegisterItemID = " . $regItemId;

                        }
                    }

                    if ($doItemInsert) {
                        $valStr = $_POST[$p];

                        $updateStr = "INSERT INTO registeritem (RegisterID, Name, Value, CountAmt) VALUES (" . $registerId . ", '" . $p . "', ";
                        if (strlen($valStr) && is_numeric($valStr) && !stripos($p, "_number", 0))
                        {
                            $updateStr .= "NULL, "  . $valStr . ")";
                        } else {
                            $updateStr .= "'" . $valStr . "', NULL)";
                        }

                   }
                   /* echo "UP: " . $updateStr . "\n\n"; */
                   $result = mysql_query($updateStr) or die(mysql_error());
                }
            }
        }

        if ($doInsert) {

            $updateStr = "INSERT INTO register (RegNumber, RegType, IP, TimestampCreated) VALUES('$reg_number', '$reg_type', '" . $_SERVER['REMOTE_ADDR'] . "', ";
            $dateStr   = $_POST['date'];

            $updateStr .= "'20" . date("y-m-d") ." " . date("H:i:s") . "')";
            /* echo "INSERT-> " . $updateStr . "\n"; */

            $result = mysql_query($updateStr) or die(mysql_error());

            if ($result)
            {
                $query = "SELECT RegisterID FROM register ORDER BY RegisterID DESC LIMIT 0,1";
                $result2 = mysql_query($query) or die(mysql_error());
                if ($result)
                {
                    $row2 = mysql_fetch_row(($result2));
                    if ($row2) {
                        $registerId = $row2[0];

                        foreach (array_keys($_POST) as $p) {
                            $valStr = $_POST[$p];

                            $updateStr = "INSERT INTO registeritem (RegisterID, Name, Value, CountAmt) VALUES (" . $registerId . ", '" . $p . "', ";
                            if (strlen($valStr) && is_numeric($valStr) && !stripos($p, "_number", 0))
                            {
                                $updateStr .= "NULL, "  . $valStr . ")";
                            } else {
                                $updateStr .= "'" . $valStr . "', NULL)";
                            }
                            /* echo "INSERT-> " . $updateStr . "\n"; */

                            $result = mysql_query($updateStr) or die(mysql_error());
                        }
                    } else {
                        echo "couldn't find the row with the highest key\n";
                    }
                } else {
                     echo "`couldn't find the highest key\n";
                }
            }
        }

        mysql_close($connection);
    }

    echo "1 " . $uTime . "\n";

  } else {
    echo "No arguments!<br>";
  }

?>
