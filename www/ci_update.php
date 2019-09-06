<?php
    include ("/etc/myauth.php");

  if ($_GET != '') {
    if (isset($_GET["update"])) {
        if ($_GET["update"] == 1) {

            $mysqli = new mysqli($mysql_hst, $mysql_usr, $mysql_pwd, "convinfo");

            if ($mysqli->connect_errno) {
                die("failed to connect to mysql" . $mysqli->connect_error);
            }

            $sql = "SELECT ConvInfoID, TimestampCreated, IP, ConvTime, NumColObj, CollectionName, IsUploaded, IsConverted, IsReportLoaded, IsVerifiedTried, IsVerifiedOk"; 
            $sql .= " FROM (SELECT * FROM (SELECT * FROM convinfo c ORDER BY TimestampCreated DESC) T2 GROUP BY CollectionName ORDER BY TimestampCreated DESC) T2";

            $result = $mysqli->query($sql);

             if ($result)
            {
                echo "<html><head><title>Conversions</title>";
                echo "<style> td { text-align: center; }</style>";
                echo "</head><body><center><H2>Converted Collections<H2>";
                echo "<table border='1'>";
                echo "<tr><TH>Collection</TH><TH>Conversion Date/Time</TH><TH>Time to Convert<BR>in Minutes</TH><TH>Num of Col Obj</TH><TH>Is On Starling</TH>";
                echo "    <TH>Is Conversion OK</TH><TH>Report Loaded</TH><TH>Tried Verify</TH><TH>Is Verified OK</TH>";
                echo "    <TH>Updated</TH>";
                echo "</TR>";

                while ($row2 = $result->fetch_row()){
                    if ($row2) {

                        $isUploaded  = isset($_GET["isonstarling_$row2[0]"]) ? 1 : 0;
                        $isConverted = isset($_GET["isuploaded_$row2[0]"]) ? 1 : 0; 
                        $isReportLoaded = isset($_GET["isreportloaded_$row2[0]"]) ? 1 : 0; 
                        $isVerifiedTried = isset($_GET["isverifiedtried_$row2[0]"]) ? 1 : 0; 
                        $isVerifiedOK = isset($_GET["isverifiedok_$row2[0]"]) ? 1 : 0; 

                        #echo $row2[0] . " [" . $isVerifiedTried ."][". $row2[9] . "]<br>";

                        $tm = sprintf("%6.2f", ($row2[3] / 60.0));
                        echo "<tr>";
                        if ($row2[6] == 1) {
                            echo "<td><a href=\"conversions/" . $row2[5] . "\">" . $row2[5] . "</a></td>";
 			} else {
                            echo "<td>" . $row2[5] . "</td>";
 			}
                        echo "<td>" . $row2[1] . "</td>";
                        echo "<td>" . $tm . "</td>";
                        echo "<td>" . $row2[4] . "</td>";
                        echo "<td>" . ($isUploaded == "1" ? "Yes" : "&nbsp;") . "</td>";
                        echo "<td>" . ($isConverted == 1 ? "Yes" : "&nbsp;") . "</td>";
                        echo "<td>" . ($isReportLoaded == 1 ? "Yes" : "&nbsp;") . "</td>";
                        echo "<td>" . ($isVerifiedTried == 1 ? "<a href=\"verify/" . $row2[5]  . "\">Yes</a>" : "&nbsp;") . "</td>";
                        echo "<td>" . ($isVerifiedOK == 1 ? "Yes" : "&nbsp;") . "</td>";

                        $wasUpdated = 0;
                        if ($isUploaded != $row2[6]) {
                           $updateStr = "UPDATE convinfo SET IsUploaded=$isUploaded WHERE ConvInfoID = $row2[0]";
                           $result2 = $mysqli->query($updateStr);
                           $wasUpdated = 1;
                        }

                        if ($isConverted != $row2[7]) {
                           $updateStr = "UPDATE convinfo SET IsConverted=$isConverted WHERE ConvInfoID = $row2[0]";
                           $result2 = $mysqli->query($updateStr);
                           $wasUpdated = 1;
                        }

                        if ($isReportLoaded != $row2[8]) {
                           $updateStr = "UPDATE convinfo SET IsReportLoaded=$isReportLoaded WHERE ConvInfoID = $row2[0]";
                           $result2 = $mysqli->query($updateStr);
                           $wasUpdated = 1;
                        }

                        if ($isVerifiedTried != $row2[9]) {
                           $updateStr = "UPDATE convinfo SET IsVerifiedTried=$isVerifiedTried WHERE ConvInfoID = $row2[0]";
                           $result2 = $mysqli->query($updateStr);
                           $wasUpdated = 1;
                        }

                        if ($isVerifiedOK != $row2[10]) {
                           $updateStr = "UPDATE convinfo SET IsVerifiedOk=$isVerifiedOK WHERE ConvInfoID = $row2[0]";
                           $result2 = $mysqli->query($updateStr);
                           $wasUpdated = 1;
                        }
                        echo "<td>" . ($wasUpdated == 1 ? "Yes" : "&nbsp;") . "</td>";
                        echo "</tr>";
                    }
                }
                $result->close();
                echo "<table></center>";
                echo "</body>";
                echo "</html>";
            }
        } else {
          echo "Not update";
        }
    } else {
       echo "update not set.";
    }
  } else {
    echo "Not GET";
  }

?>
