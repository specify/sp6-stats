<?php
    include ("/etc/myauth.php");

$mysqli = new mysqli($mysql_hst, $mysql_usr, $mysql_pwd, "convinfo");

if ($mysqli->connect_errno) {
    die("failed to connect to mysql" . $mysqli->connect_error);
}



	    echo "<form name=\"update\"  action=\"ci_update.php\" method=\"get\">\n";

            $sql = "SELECT ConvInfoID, TimestampCreated, IP, ConvTime, NumColObj, CollectionName, IsUploaded, IsConverted, IsReportLoaded, IsVerifiedTried, IsVerifiedOk";
            $sql .= " FROM (SELECT * FROM (SELECT * FROM convinfo c ORDER BY TimestampCreated DESC) T2 GROUP BY CollectionName ORDER BY TimestampCreated DESC) T2";

            $result = $mysqli->query($sql);
            if ($result)
            {
                echo "<html><head><title>Conversions</title>";
                echo "<style> td { text-align: center; }</style>";
                echo "</head><body><center><H2>Converted Collections<H2>";
	        echo "<input type=\"hidden\" name=\"update\" value=\"1\">";
                echo "<table border='1'>\n";
                echo "<tr><TH>Collection</TH><TH>Conversion Date/Time</TH><TH>Time to Convert<BR>in Minutes</TH><TH>Num of Col Obj</TH><TH>Is On Starling</TH>";
                echo "    <TH>Is Conversion OK</TH><TH>Report Loaded</TH><TH>Tried Verify</TH><TH>Is Verified OK</TH>";
                echo "</TR>";

                while ($row2 = $result->fetch_row()) {
                    if ($row2) {
                        $tm = sprintf("%6.2f", ($row2[3] / 60.0));
                        echo "<tr>\n";
                        if ($row2[6] == 1) {
                            echo "<td><a href=\"conversions/" . $row2[5] . "\">" . $row2[5] . "</a></td>";
                        } else {
                            echo "<td>" . $row2[5] . "</td>\n";
                        }
                        echo "<td>" . $row2[1] . "</td>\n";
                        echo "<td>" . $tm . "</td>\n";
                        echo "<td>" . $row2[4] . "</td>\n";
                        echo "<td> <input type=\"checkbox\" name=\"isonstarling_$row2[0]\" value=\"" . ($row2[6] != 0 ? "1" : "") . "\"" . ($row2[6] != 0 ? " checked=\"checked\"" : "") . "></td>\n";
                        echo "<td> <input type=\"checkbox\" name=\"isuploaded_$row2[0]\" value=\"" . ($row2[7] != 0 ? "1" : "") . "\"" . ($row2[7] != 0 ? " checked=\"checked\"" : "") . "></td>\n";
                        echo "<td> <input type=\"checkbox\" name=\"isreportloaded_$row2[0]\" value=\"" . ($row2[8] != 0 ? "1" : "") . "\"" . ($row2[8] != 0 ? " checked=\"checked\"" : "") . "></td>\n";
                        echo "<td> <input type=\"checkbox\" name=\"isverifiedtried_$row2[0]\" value=\"" . ($row2[9] != 0 ? "1" : "") . "\"" . ($row2[9] != 0 ? " checked=\"checked\"" : "") . "></td>\n";
                        echo "<td> <input type=\"checkbox\" name=\"isverifiedok_$row2[0]\" value=\"" . ($row2[10] != 0 ? "1" : "") . "\"" . ($row2[10] != 0 ? " checked=\"checked\"" : "") . "></td>\n";

                        echo "</tr>\n";
                    }
                }
                $result->close();
                echo "<table>\n";
                echo "<br><input type=\"submit\" value=\"Submit\" />\n";
                echo "</form></center>\n";
                echo "</body>";
                echo "</html>";
            }

?>
