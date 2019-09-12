<?php
    include ("/etc/myauth.php");
            $mysqli = new mysqli($mysql_hst, $mysql_usr, $mysql_pwd, "feedback");

            if ($mysqli->connect_errno) {
                die("failed to connect to mysql" . $mysqli->connect_error);
            }

            $sql = "select count(*) from feedback";
            $result = $mysqli->query($sql);

            while ( $row = $result->fetch_assoc() )
            {
                foreach ( $row AS $key=>$value )
                {
                    echo "Number of Entries: $value <br>";
                    break;
                }
            }
            $result->close();
            $sql = "SELECT * FROM feedback ORDER BY FeedbackID DESC";
            $result = $mysqli->query($sql);

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
            while ( $row = $result->fetch_assoc() )
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
            $result->close();
            echo "</table></body></html>";
            $mysqli->close();
?>
