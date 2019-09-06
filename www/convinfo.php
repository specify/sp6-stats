<?php
   include ("/etc/myauth.php");

function displayTable($mysqli, $kind)
	{
            $ku = array();
            $ku['entosp_dbo_6'] = 1;
            $ku['kanulichendatabase_dbo_6'] = 1;
            $ku['kanuvascularplantdatabase_dbo_6'] = 1;
            $ku['ku_invert_dbo_6'] = 1;
            $ku['kuherps_dbo_6'] = 1;
            $ku['kui_fish_dbo_6'] = 1;
            $ku['kui_tissue_dbo_6'] = 1;
            $ku['kumam_dbo_6'] = 1;
            $ku['trichomycetes_dbo_6'] = 1;
            $ku['KUVP_dbo_6'] = 1;
            $ku['kuherps_dbo_6'] = 1;
            $ku['kuinvp4_dbo_6'] = 1;
            $ku['kulichen_6'] = 1;
            $ku['kuinvp4_dbo_6'] = 1;

            $sql = "SELECT ConvInfoID, TimestampCreated, IP, ConvTime, NumColObj, CollectionName, IsUploaded, IsConverted, IsReportLoaded, IsVerifiedTried, IsVerifiedOk";
            $sql .= " FROM (SELECT * FROM (SELECT * FROM convinfo c ORDER BY TimestampCreated DESC) T2 GROUP BY CollectionName ORDER BY TimestampCreated DESC) T2";

            $result = $mysqli->query($sql);
            if ($result)
            {

		if ($kind == 1) {
                	echo "<H3>KU Collections</H3>";
 		} else {
                	echo "<H3>Other Collections</H3>";
		}
                echo "<table border='0' cellspacing='0' cellpadding='2'>";
                echo "<tr><TH>Collection</TH><TH>Conversion Date/Time</TH><TH>Num of Col Obj</TH><TH>Is On " . ($kind == 1 ? "Starling" : "Huxley") . "</TH>";
                echo "    <TH>Is Conversion OK</TH><TH>Tried Verify</TH><TH>Is Verified OK</TH>";
                echo "</TR>";

                $i = 0;
                while ($row2 = $result->fetch_row()) {
                    
                    if ($row2) {
                        if (($ku[$row2[5]] == 1 && $kind == 1) || ($ku[$row2[5]] != 1 && $kind == 0)) {
                            $mins = $row2[3] / 60.0;
                            $hrs  = floor($mins / 60.0);
                            if ($hrs > 0)
                            {
                                $mins = $mins - ($hrs * 60);
                            }
                            $tm = sprintf("%02d:%02d", Intval($hrs), Intval($mins));
                            echo "<tr>";
                            #if ($row2[8] == 1) {
                                echo "<td><a href=\"conversions/" . $row2[5] . "\">" . $row2[5] . "</a></td>";
                            #} else {
                            #    echo "<td>" . $row2[5] . "</td>";
                            #}
                            echo "<td>" . $row2[1] . "</td>";
                            #echo "<td>" . $tm . "</td>";
                            echo "<td class=\"r\">" . number_format(Intval($row2[4])) . "</td>";
                            #echo "<td>" . sprintf("%6.2f", ($row2[4] / $row2[3])) . "</td>";
                            echo "<td>" . ($row2[6] == 1 ? "Yes" : "&nbsp;") . "</td>";
                            echo "<td>" . ($row2[7] == 1 ? "Yes" : "&nbsp;") . "</td>";
                            #echo "<td>" . ($row2[8] == 1 ? "Yes" : "&nbsp;") . "</td>";
                            echo "<td>" . ($row2[9] == 1 ? "<a href=\"verify/" . $row2[5]  . "\">Yes</a>" : "&nbsp;") . "</td>";
                            echo "<td>" . ($row2[10] == 1 ? "Yes" : "&nbsp;") . "</td>";
                            $forms_loc = "conversions/" . $row2[5] . "/forms";
                            #echo "<td>" . (file_exists($forms_loc) ? "<a href=\"$forms_loc/index.html\">Forms</a>" : "&nbsp;") . "</td>";
                            echo "</tr>\n";
                        }
                    }
                $i++;
                }
                $result->close();
                echo "</table>";
            }
	}

  if ($_GET != '') {
    if (isset($_GET["dmp"])) {
        if ($_GET["dmp"] == 1) {

            $mysqli = new mysqli($mysql_hst, $mysql_usr, $mysql_pwd, "convinfo");

            if ($mysqli->connect_errno) {
                die("failed to connect to mysql" . $mysqli->connect_error);
            }
            echo "<html><head><title>Conversions</title>";
            echo "<style>\n";
            echo "  body { font-family: arial,helvetica,sans-serif; font-size: 11pt;}\n";
            echo "  td,th { font-family: arial,helvetica,sans-serif; font-size: 11pt; padding-left: 4px; padding-right: 4px;}\n";
            echo "  th { text-align: center; border-top: 1px solid black; border-left: 1px solid black; }\n";
            echo "  td { text-align: center; border-top: 1px solid black; border-left: 1px solid black; }\n";
            echo "  td.r { text-align: right; padding-right: 10px; }\n";
            echo "  table { border-right: 1px solid black; border-bottom: 1px solid black; }\n";
            echo "</style>\n";
            echo "</head><body><center><H2>Converted Collections</H2>\n";
            displayTable($mysqli, 0);
            echo "<BR><BR>";
            displayTable($mysqli, 1);
            echo "</center>";
            echo "</body>";
            echo "</html>";
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
        $mysqli = new mysqli($mysql_hst, $mysql_usr, $mysql_pwd, "convinfo");

        if ($mysqli->connect_errno) {
            die("failed to connect to mysql" . $mysqli->connect_error);
        }

        $doInsert     = 1;
        if ($doInsert) {

            $updateStr = "INSERT INTO convinfo (TimestampCreated, IP) VALUES(";
            $dateStr   = $_POST['date'];

            $updateStr .= "'20" . date("y-m-d") ." " . date("H:i:s") . "', '" . $_SERVER['REMOTE_ADDR'] . "')";
            echo "INSERT-> " . $updateStr . "\n";
            $result = $mysqli->query($updateStr);

            $collName = "";
            $numColObj = 0;
            $convTime  = 0;
            if ($result)
            {
                $query = "SELECT ConvInfoID FROM convinfo ORDER BY ConvInfoID DESC LIMIT 0,1";
                $result2 = $mysqli->query($query);
                if ($result)
                {
                    $row2 = $result2->fetch_row();
                    $result2->close();
                    if ($row2) {
                        $convInfoId = $row2[0];

                        foreach (array_keys($_POST) as $p) {
                            $valStr = $_POST[$p];

                            if (substr($p, 0, 4) == "num_") {
                               $numStatsKeys[] = $p;
                            }

                            if ($p == "CollectionName") {
                                $collName = $_POST[$p];
                            }

                            if ($p == "num_colobj") {
                                $numColObj = $_POST[$p];
                            }

                            if ($p == "num_convtime") {
                                $convTime = $_POST[$p];
                            }

			    if ($valStr != '') {
                                $updateStr = "INSERT INTO convinfoitem (ConvInfoID, Name, Value, CountAmt) VALUES (" . $convInfoId . ", '" . $p . "', ";
                                if (strlen($valStr) && is_numeric($valStr) && !stripos($p, "_number", 0))
                                {
                                    $updateStr .= "NULL, "  . $valStr . ")";
                                } else {
                                    $updateStr .= "'" . $valStr . "', NULL)";
                                }
                                echo "INSERT-> " . $updateStr . "\n";
                                $result = $mysqli->query($updateStr);
			    }
                        }

                        $updateStr = "UPDATE convinfo SET CollectionName='" . $collName . "', NumColObj=" . $numColObj . ", ConvTime=" .
                                     $convTime . ", IsUploaded=0, IsConverted=1, IsReportLoaded=0 WHERE ConvInfoID = " . $convInfoId;
                        $result = $mysqli->query($updateStr);

                    } else {
                        echo "couldn't find the row with the highest key\n";
                    }
                } else {
                     echo "`couldn't find the highest key\n";
                }
            }
        }

        $mysqli->close();
    }
    echo "ok";

  } else {
    echo "No arguments!<br>";
  }

?>
