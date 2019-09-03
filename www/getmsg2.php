<?php
    include ("/etc/myauth.php");

  if ($_POST != '') {

    $cnt = 0;
    foreach (array_keys($_POST) as $p) {
        $cnt++;
    }

    # echo  "ip=" . $_SERVER['REMOTE_ADDR'] . "<BR>";

    $Id = $_POST['id'];

    if ($cnt > -1)
    {
         if (strcmp($_POST["app_version"], "6.4.13") < 0 ) {
             echo "It is highly recommended that you upgrade to the latest release.";
             return;
         }

         #if (strcmp($_POST["java_version"], "1.6.0_18") != 0 ) {
             #echo "NOMSG";
             #return;
         #}
        #if (1) return;

        $connection = mysql_connect($mysql_hst, $mysql_usr, $mysql_pwd);
        if (!$connection) {
            die ("Couldn't connect" . mysql_error());
        }

        $db_select = mysql_select_db("stats");
        if (!$db_select) {
          die ("Couldn't 'select_db' " . mysql_error());
        }

        $query = "SELECT MessageID,Message FROM messages WHERE Type = 1";
        $result = mysql_query($query) or die(mysql_error());
        if ($result)
        {
            $row = mysql_fetch_row(($result));
            if ($row) {
                echo $row[1];
                mysql_close($connection);
                return;
            }
        }

        $query = "SELECT Message FROM messages WHERE Type = 0 AND SingleUserIdent = '$Id'";
        $result = mysql_query($query) or die(mysql_error());
        if ($result)
        {
            $row = mysql_fetch_row(($result));
            if ($row) {
                $convInfoId = $row[0];
                $query = "SELECT Message FROM messages WHERE MessagesID = $Id";
                $result = mysql_query($query) or die(mysql_error());
                if ($result)
                {
                    $row = mysql_fetch_row(($result));
                    if ($row) {
                        $convInfoId = $row[0];
                    }
                } else {
                }

            } else {
                #echo "couldn't find the row with the highest key\n";
            }
        } else {
            echo "`couldn't find the highest key\n";
        }
        mysql_close($connection);
    }
    echo "NOMSG";

  } else {
    echo "No arguments!<br>";
  }

?>
