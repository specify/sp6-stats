<?php
include ("/etc/myauth.php");
date_default_timezone_set('America/Chicago');

if ($_GET != '') {

            $mysqli = new mysqli($mysql_hst, $mysql_usr, $mysql_pwd, "exception");

            if ($mysqli->connect_errno) {
                die("failed to connect to mysql" . $mysqli->connect_error);
            }

            echo "<html>\n";
            echo "<html><body><table border=1>\n";
            echo "<style>";
            echo " table {border-right: solid 1px gray; }";
            echo " table {border-bottom: solid 1px gray; }";
            echo " td    { border-left: 1px solid gray; border-top: 1px solid gray; }\n";
            echo " th    { border-left: 1px solid gray; border-top: 1px solid gray; }\n";
            echo "</style>";
            echo "<body><table border=0 cellspacing=0>\n";

            date_default_timezone_set('UTC');
            $date30DaysPast = time() - 2073600;
            $dateStr = date( 'Y-m-d 00:00:00', $date30DaysPast);

            $whereClause = "TimestampCreated > '" . $dateStr . "' AND IP NOT LIKE '129.237.%' AND " .
                         "ClassName <> 'edu.ku.brc.specify.ui.HelpMgr' AND " .
                         "StackTrace NOT LIKE 'java.lang.RuntimeException: Two controls have the same name%' AND " .
                         "StackTrace NOT LIKE 'Multiple %' AND StackTrace NOT LIKE 'edu.ku.brc.ui.forms.persist.FormCell%' AND ".
                         "StackTrace NOT LIKE 'java.lang.RuntimeException: Two controls have the same id%' ";

            $sql = "SELECT ExceptionID,StackTrace FROM exception WHERE " . $whereClause . " ORDER BY ExceptionID DESC";

            echo $sql . "<br>";
            $result = $mysqli->query($sql);

            $cnts = array();
            $ids  = array();
            $locs  = array();
            $keys = array();

            echo "<tr><th>File Location</th><th>Count</th><th>Error</th><th>Ids</th></tr>";
            while ( $row = $result->fetch_row() )
            {
                $id = $row[0];
                $st = $row[1];

                $inx = strpos($st, "edu.ku.brc");
                if ($inx > -1)
                {
                    $i = strpos($st, "(", $inx);
                    $eInx = strpos($st, ")", $inx);
                    if ($i > -1 && $eInx > -1)
                    {
                        $fileLoc = substr($st, $i+1, ($eInx - $i -1));
                        $errStr  = substr($st, $inx, ($i - $inx));
                        if (isset($cnts[$errStr]))
                        {
                            $cnts[$errStr]++;
                        } else {
                            $cnts[$errStr] = 1;
                        }
                        $locs[$errStr] = $fileLoc;
                        $ids[$errStr] .= $id . ",";
                        #echo $id . " " . $fileLoc . "-" . $errStr ."<BR>";
                        $keys[] = $errStr;
                    }
                }
            }
            $result->close();

            $mapping  = array();
            $cntKeys  = array();
            foreach ( $cnts AS $key=>$value )
            {

     			$s =  "<tr><td>" . $locs[$key] . "</td><td>" . $value . "</td><td>" . $key . "</td><td>";
                $indents = explode(",", $ids[$key]);
                foreach($indents as $i) {
                    if (strlen($i) > 0) {
                        $s .= "<a href='#$i'>$i</a>, ";
                    }
                }
                $s .= "</td></tr>\n";
                $cntKeys[] = $value;
                $mappings[$value] = $s;
            }
            arsort($cntKeys);
            foreach ($cntKeys AS $n)
            {
                echo $mappings[$n];
            }
            echo "</table>";

            echo "<br><table border=0 cellspacing=0>\n";
            $sql = "SELECT * FROM exception WHERE " . $whereClause . " ORDER BY ExceptionID DESC";
            $result = $mysqli->query($sql);

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
                            if (strcmp($key,  "ExceptionID") == 0) {
                            	echo "<td><a name='$value'>$value</td>";
                            } else {
                                echo "<td>$value</td>";
                            }
                        }
                    }
                }
                echo "</tr>";
            }
            $result->close();
            echo "</table></body></html>";
            $mysqli->close();
}


?>
