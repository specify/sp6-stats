<?php
    include ("/etc/myauth.php");
    date_default_timezone_set('America/Chicago');

  if ($_GET != '') {
    if (isset($_GET["dmp"])) {
        if ($_GET["dmp"] == 1) {
            $connection = mysql_connect($mysql_hst, $mysql_usr, $mysql_pwd);
            if (!$connection) {
                die ("Couldn't connect" . mysql_error());
            }

            $db_select = mysql_select_db("exception");
            if (!$db_select) {
              die ("Couldn't 'select_db' " . mysql_error());
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
            $result = mysql_query($sql) or die(mysql_error());

            $cnts = array();
            $ids  = array();
            $locs  = array();
            $keys = array();

	    echo "<tr><th>File Location</th><th>Count</th><th>Error</th><th>Ids</th></tr>";
            while ( $row = mysql_fetch_array($result) )
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
            $result = mysql_query($sql) or die(mysql_error());

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

        $db_select = mysql_select_db("exception");
        if (!$db_select) {
          die ("Couldn't 'select_db' " . mysql_error());
        }

        $Timestamp   = date("y-m-d") ." " . date("H:i:s");
        $TaskName    = $_POST['task_name'];
        $Title       = $_POST['title'];
        $Bug         = $_POST['bug'];
        $Comments    = $_POST['comments'];
        $StackTrace  = $_POST['stack_trace'];
        $ClassName   = $_POST['class_name'];
        $Id          = $_POST['id'];
        $OSName      = $_POST['os_name'];
        $OSVersion   = $_POST['os_version'];
        $JavaVersion = $_POST['java_version'];
        $JavaVendor  = $_POST['java_vendor'];
        $UserName    = $_POST['user_name'];
        $IP          = $_POST['ip'];
        $AppVersion  = $_POST['app_version'];
        $Collection  = $_POST['collection'];
        $Discipline  = $_POST['discipline'];
        $Division    = $_POST['division'];
        $Institution = $_POST['institution'];

	if (!isset($IP) || strlen($IP) == 0) {
                $IP = $_SERVER['REMOTE_ADDR'];
        }


        $updateStr = "INSERT INTO exception ( " .
                     "TimestampCreated,TaskName,Title,Bug,Comments,Id,StackTrace,ClassName,OSName,OSVersion,JavaVersion," .
                     "JavaVendor,UserName,IP,AppVersion,Collection,Discipline,Division,Institution) " .

         "VALUES('$Timestamp', '$TaskName', '$Title', '$Bug', '$Comments', '$Id', '$StackTrace', '$ClassName', '$OSName', '$OSVersion', '$JavaVersion', " .
         "'$JavaVendor', '$UserName', '$IP', '$AppVersion', '$Collection', '$Discipline', '$Division', '$Institution') ";

        echo "INSERT-> " . $updateStr . "\n";
        $result = mysql_query($updateStr) or die(mysql_error());

        mysql_close($connection);
    }
    echo "ok";

  } else {
    echo "No arguments!<br>";
  }

?>
