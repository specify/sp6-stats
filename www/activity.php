<?php
    include ("/etc/myauth.php");
    date_default_timezone_set('America/Chicago');

    // for testing
    /*$_POST['id']                 = "123-456-789";
    $_POST['Type']               = 0;
    $_POST['Collection_number']  = "10";
    $_POST['Institution_number'] = "11";
    $_POST['user_name']          = "12";

    $remoteIPAddr = "1.1.1.1";
    */
    $remoteIPAddr = $_SERVER['REMOTE_ADDR'];


  if ($_POST != '') {

    $cnt = 0;
    foreach (array_keys($_POST) as $p) {
        $cnt++;
    }

    if ($cnt == 0) {
        echo "No arguments!<br>";
    }

    if ($cnt > 0)
    {
        $mysqli = new mysqli($mysql_hst, $mysql_usr, $mysql_pwd, "stats");

        if ($mysqli->connect_errno) {
            die("failed to connect to mysql" . $mysqli->connect_error);
        }

        $now   = time();
        $currentDateTime = "'20" . date("y-m-d") ." " . date("H:i:s") . "'";

        $tr_id   = $_POST['id'];
        $type    = $_POST['Type'];
        $colNum  = $_POST['Collection_number'];
        $instNum = $_POST['Institution_number'];
        $usrname = $_POST['user_name'];

        $unknownCount   = 0;
        $engagedMinutes = 0;

        // Now Insert or Update an activity record
        $query = "SELECT TrackActivityID,LoginDate,LogoutDate,EngagedMinutes,UnknownCount FROM trackactivity WHERE Id = '" . $tr_id . "'";

        $doInsert     = 1;
        $result       = $mysqli->query($query);
        if ($result) {
            $row = $result->fetch_row();
            $result->close();
            if ($row) {
                $doInsert       = 0;
                $trackId        = $row[0];
                $loginDT        = $row[1];
                $logoutDT       = $row[2];
                $engagedMinutes = $row[3];
                $unknownCount   = $row[4];

                //foreach ($row as $p) {
                //    echo $cnt . " => " . $p . "\n";
                //    $cnt++;
                //}

                if ($type == 0) // Login
                {
                    if ($logoutDT != null) // Never logged out?
                    {
                        $unknownCount++;
                    }
                    $loginDTStr  = $currentDateTime;
                    $logoutDTStr = "NULL";
                    $engagedMinutes = 0;

                } else // logging out
                {
                    $loginDTStr  = "NULL";
                    $logoutDTStr = "NULL";
                    if ($loginDT == null) // Never logged in, strange?
                    {
                        $unknownCount++;
                    } else
                    {
                        $loginTime = strtotime($loginDT);

                        //echo "loginDT-> [" . $loginDT . "] $loginTime " . date("m/d/Y",strtotime($loginDT)) . "\n";
                        if ($loginTime != null)
                        {
                            $deltaTime = round((($now - $loginTime) / 60) + 0.5);
                            //echo "now-> [" . $now . "] loginTime-> [" . $loginTime . "] deltaTime-> [" . $deltaTime . "]\n";

                            if ($deltaTime > 0)
                            {
                                $engagedMinutes += $deltaTime;
                            }
                        }

                    }
                }

                $updateStr = "UPDATE trackactivity SET LoginDate=" . $loginDTStr .
                             ", LogoutDate=" . $logoutDTStr .
                             ", EngagedMinutes=" . $engagedMinutes .
                             ", UnknownCount=" . $unknownCount .
                             " WHERE TrackActivityID = " . $trackId;
                //echo "UPDATE-> [" . $updateStr . "]\n";
                $result = $mysqli->query($updateStr);

            }
        }

        if ($doInsert) {

            $updateStr = "INSERT INTO trackactivity (LoginDate, LogoutDate, EngagedMinutes, ID, IP, InstReg, CollReg, Username, UnknownCount) VALUES(";
            $updateStr .= $currentDateTime . ", NULL, 0, '$tr_id', '" . $remoteIPAddr . "', ";
            $updateStr .= "'$instNum', '$colNum', '$usrname', 0)";

            //echo "INSERT-> [" . $updateStr . "]\n";
            $result = $mysqli->query($updateStr);
        }

        // insert an activity entry record
        $updateStr = "INSERT INTO trackactentry (ActivityDateTime, Type, EngagedMinutes, ID, IP, InstReg, CollReg, Username) VALUES(";
        $updateStr .= $currentDateTime . ", $type, $engagedMinutes, '$tr_id', '" . $remoteIPAddr . "', ";
        $updateStr .= "'$instNum', '$colNum', '$usrname')";
        //echo "UPDATE TA-> [" . $updateStr . "]\n\n\n";
        $result = $mysqli->query($updateStr);


        $mysqli->close();
    }
    echo "ok";

  } else {
    echo "No arguments!<br>";
  }

?>
