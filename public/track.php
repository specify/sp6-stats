<?php
    ini_set("memory_limit", "500M");
    include ("/etc/myauth.php");
    date_default_timezone_set('America/Chicago');
    $myFile = "/home/anhalt/track.dat";


function getArrCount ($arr, $depth=1)
{
      if (!is_array($arr) || !$depth) return 0;

     $res=count($arr);

      foreach ($arr as $in_ar)
         $res+=getArrCount($in_ar, $depth-1);

      return $res;
}
function str_sandwich($input, $leftString, $rightString)
{
	$startsAt = strpos($input, $leftString) + strlen($leftString);
	$endsAt = strpos($input, $rightString, $startsAt);
	if($startsAt === false or $endsAt === false)
	{
		return false;
	}
	$result = substr($input, $startsAt, $endsAt - $startsAt);
	return $result;
}
function formatBytes($bytes, $precision = 2)
{
	$units = array('B', 'KB', 'MB', 'GB', 'TB');

	$bytes = max($bytes, 0);
	$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
	$pow = min($pow, count($units) - 1);

	//Uncomment one of the following alternatives
	//$bytes /= pow(1024, $pow);
	$bytes /= (1 << (10 * $pow));

	return round($bytes, $precision) . ' ' . $units[$pow];
}

  if (false && $_GET != '') {
    if (isset($_GET["dmp"])) {
        if ($_GET["dmp"] == 1) {
            $fh = fopen($myFile, 'r') or die("Unable to open file.");
            if ($fh) {
		fseek($fh, -1024 * 1000, SEEK_END);
		$array = explode("---------------", fread($fh, 1024 * 1000));
            }
	    $results = array_slice($array, 0);
	    $size = count($results) - 1;
	    echo "Number of records shown: " . $size . "<br>Total Records (in last 1MB of file): " . count($array) . "<br>File Size: " . formatBytes(filesize($myFile), 2) . "<br>";
	    foreach($results as $key => $value)
	    {
		if($key != 0)
		{
			echo str_replace("\n", "<br>", $value);
		}
	    }
            fclose($fh);
        }
	elseif($_GET["dmp"] == 2) {
            $fh = fopen($myFile, 'r') or die("Unable to open file.");
            //Read the data for Registration into a string
            $data_reg = fread($fh, filesize($myFile));
            $data_reg = substr($data_reg, strpos($data_reg, "date=13/07/29 09:45:23"));
            //echo str_sandwich($data_reg, "ip=", "\n");
            $data_reg = str_replace("\n", "<br>", $data_reg);
            $array = explode("---------------<br>", $data_reg);
	    $count = 0;
            foreach($array as $key => $value)
            {
                $ip = str_sandwich($value, "ip=", "<br>");
		if(strpos($ip, "129.237.201.") === false)
                {
			$count++;
			if(str_sandwich($value, "app_version=", "<br>") == "6.5.00")
			{
				$array2[$ip] = $value;
			}
                }
            }
            echo "Workstations at 6.5.00: " . count($array2) . "<br>";
            foreach($array2 as $key => $value)
	    {
		$os = str_sandwich($value, "os_name=", "<br>");
                $array3[$key] = $os;
                $array4[$os] = 0;

	    }
	    echo "-----Workstations running 6.5.00-----<br>";
	    foreach($array4 as $osType => $osCount)
            {
                foreach($array3 as $value)
                {
                        if($value == $osType)
                        {
                                $osCount += 1;
                        }
                }
                echo $osType . " workstations: " . $osCount . "<br>";
            }
	    echo "===================================<br>";
	    foreach($array2 as $value)
            {
                echo  $value . "===================================<br>";
            }
            fclose($fh);
	}
	elseif($_GET["dmp"] == 3) {
            $fh = fopen($myFile, 'r') or die("Unable to open file.");
            //Read the data for Registration into a string
            $data_reg = fread($fh, filesize($myFile));
            $data_reg = substr($data_reg, strpos($data_reg, "date=13/01/01"));
            //echo str_sandwich($data_reg, "ip=", "\n");
            $data_reg = str_replace("\n", "<br>", $data_reg);
            $array = explode("---------------<br>", $data_reg);
	    //$count = 0;
            foreach($array as $key => $value)
            {
		$ip = str_sandwich($value, "ip=", "<br>");
                if(strpos($ip, "129.237.201.") === false)
                {
			//$count++;
                        //if(str_sandwich($value, "app_version=", "<br>") != "6.5.00")
                        //{
                                $array2[$ip] = $value;
                        //}
                }
            }
            echo "Workstations logged in from Jan 1, 2013 to now: " . count($array2)/* . "<br>Entries since the 6.5.00 release date: " . $count*/ . "<br>";
            foreach($array2 as $key => $value)
            {
                $os = str_sandwich($value, "os_name=", "<br>");
		if(substr_count($value, "<br>") > 3)
		{
			$array3[$key] = $os;
	                $array4[$os] = 0;
		}

            }
            //echo "-----Workstations NOT running 6.5.00-----<br>";
	    echo "-----Workstations-----<br>";
            foreach($array4 as $osType => $osCount)
            {
                foreach($array3 as $value)
                {
                        if($value == $osType)
                        {
                                $osCount += 1;
                        }
                }
                echo $osType . " workstations: " . $osCount . "<br>";
            }
            echo "===================================<br>";
            foreach($array2 as $value)
            {
                echo  $value . "===================================<br>";
            }
            fclose($fh);
        }
	elseif($_GET["dmp"] == 4) {
	    $fh = fopen($myFile, 'r') or die("Unable to open file.");
            //Read the data for Registration into a string
            $data_reg = fread($fh, filesize($myFile));
            $data_reg = substr($data_reg, strpos($data_reg, "date=13/01/01"));
            //echo str_sandwich($data_reg, "ip=", "\n");
            $data_reg = str_replace("\n", "<br>", $data_reg);
            $array = explode("---------------<br>", $data_reg);
            foreach($array as $key => $value)
            {
                $ip = str_sandwich($value, "ip=", "<br>");
                if(strpos($ip, "129.237.201.") === false)
                {
                	$array2[$ip] = $value;
                }
            }
            foreach($array2 as $key => $value)
            {
                $inst_name = str_sandwich($value, "Institution_name=", "<br>");
                if(substr_count($value, "<br>") > 3)
                {
			if(strpos($value, "Institution_name=") === false)
			{
				$inst_name = "Unknown";
			}
                        $array3[$key] = $inst_name;
                        $array4[$inst_name] = 0;
                }

            }
            echo "Number of Institutions tracked since Jan 1, 2013: " . count($array4) . "<br>-----Workstations per Institution-----<br>";
            foreach($array4 as $instType => $instCount)
            {
                foreach($array3 as $value)
                {
                        if($value == $instType)
                        {
                                $instCount += 1;
                        }
                }
                echo $instType . ": " . $instCount . "<br>";
            }
            echo "===================================<br>";
            /*foreach($array2 as $value)
            {
                echo  $value . "===================================<br>";
            }*/
            fclose($fh);
        }
	elseif($_GET["dmp"] == 5) {
            $fh = fopen($myFile, 'r') or die("Unable to open file.");
            $data_reg = fread($fh, filesize($myFile));
            $data_reg = substr($data_reg, strpos($data_reg, "date=13/01/01"));
            $data_reg = str_replace("\n", "<br>", $data_reg);
            $array = explode("---------------<br>", $data_reg);
            foreach($array as $key => $value)
            {
		$ip = str_sandwich($value, "ip=", "<br>");
		$usr = str_sandwich($value, "specifyuser=", "<br>");
		$inst = str_sandwich($value, "Institution_name=", "<br>");
                if(strpos($ip, "129.237.201.") === false)
                {
			if(strpos($value, "Institution_name=") === false)
			{
				$inst = "Unknown";
			}
			if(strpos($value, "specifyuser=") === false)
			{
				$usr = "Unknown";
			}
			$array5[$inst][$usr] = 0;
                }
            }
	    $count = count($array5, COUNT_RECURSIVE) - count($array5);
            echo "Users who logged in from Jan 1, 2013 to now: " . $count . "<br>";
	    foreach($array5 as $key => $usrCount)
	    {
		echo $key . " (" . count($usrCount) . "):<br>";
		foreach($usrCount as $subkey => $value)
		{
			echo $subkey . "<br>";
		}
		echo "===================================<br>";
	    }
	    echo "===================================<br>";
            fclose($fh);
        }
	elseif($_GET["dmp"] == 6) {
            $fh = fopen($myFile, 'r') or die("Unable to open file.");
            $data_reg = fread($fh, filesize($myFile));
            $data_reg = substr($data_reg, strpos($data_reg, "date=13/01/01"));
            $data_reg = str_replace("\n", "<br>", $data_reg);
            $array = explode("---------------<br>", $data_reg);
            foreach($array as $key => $value)
            {
		$os = str_sandwich($value, "os_name=", "<br>");
                $ip = str_sandwich($value, "ip=", "<br>");
                $usr = str_sandwich($value, "specifyuser=", "<br>");
                $inst = str_sandwich($value, "Institution_name=", "<br>");
                if(strpos($ip, "129.237.201.") === false)
                {
                        if(strpos($value, "os_name=") === false)
                        {
                                $os = "Unknown";
                        }
                        if(strpos($value, "specifyuser=") === false)
                        {
                                $usr = "Unknown";
                        }
                        $array5[$os][$usr] = 0;
                }
            }
	    $count = count($array5, COUNT_RECURSIVE) - count($array5);
            echo "Users who logged in from Jan 1, 2013 to now: " . $count . "<br>";
            foreach($array5 as $key => $usrCount)
            {
                echo $key . " (" . count($usrCount) . "):<br>";
                foreach($usrCount as $subkey => $value)
                {
                        echo $subkey . ", ";
                }
                echo "<br>===================================<br>";
            }
            echo "===================================<br>";
            fclose($fh);
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

    $fh = fopen($myFile, 'a') or die("can't open file");
    fwrite($fh, $data);
    fclose($fh);

    if ($cnt > 0)
    {
        $mysqli = new mysqli($mysql_hst, $mysql_usr, $mysql_pwd, "stats");

        if ($mysqli->connect_errno) {
            die("failed to connect to mysql" . $mysqli->connect_error);
        }

        $tr_id = $_POST['id'];
        $query = $mysqli->prepare("SELECT TrackID, CountAmt FROM track WHERE Id = ?");
        $query->bind_param("s", $tr_id);
        if(!$query->execute()) throw new Exception($mysqli->error);

        $rg_number    = "";
        $numStatsKeys = array();
        $doInsert     = 1;
        $result       = $query->get_result();
        if ($result) {
            $row = $result->fetch_row();
            $result->close();
            if ($row) {
                $doInsert = 0;
                $trackId  = $row[0];
                $count    = $row[1] + 1;
                $timestampModified = date("Y-m-d H:i:s");
                $updateStr = $mysqli->prepare("UPDATE track SET CountAmt=?, TimestampModified=? WHERE TrackID = ?");
                $updateStr->bind_param("isi", $count, $timestampModified, $trackId);
                $result = $updateStr->get_result();

                foreach (array_keys($_POST) as $p) {

                    $doItemInsert = 1;

                    if (substr($p, 0, 4) == "num_") {
                       $numStatsKeys[] = $p;
                    }

                    if ($p == "Collection_number") {
                        $rg_number = $_POST[$p];
                    }

                    $valStr       = $_POST[$p];

                    $query = $mysqli->prepare("SELECT TrackItemID FROM trackitem WHERE TrackID = ? AND Name = ?");
                    $query->bind_param("is", $trackId, $p);
                    if(!$query->execute()) throw new Exception($mysqli->error);
                    $result = $query->get_result();
                    if ($result) {
                        $row = $result->fetch_row();
                        $result->close();
                        if ($row)
                        {
                            $doItemInsert = 0;
                            $trackItemId  = $row[0];
                            if (strlen($valStr) && is_numeric($valStr) && !stripos($p, "_number", 0))
                            {
                                $updateStr = $mysqli->prepare("UPDATE trackitem SET CountAmt=?, Value=NULL WHERE TrackItemID = ?");
                                $updateStr->bind_param("ii", $valStr, $trackItemId);
                                if(!$updateStr->execute()) throw new Exception($mysqli->error);
                            } else {
                                $updateStr = $mysqli->prepare("UPDATE trackitem SET Value=?, CountAmt=NULL WHERE TrackItemID = ?");
                                $updateStr->bind_param("si", $valStr, $trackItemId);
                                if(!$updateStr->execute()) throw new Exception($mysqli->error);
                            }
                        }
                    }

                    if ($doItemInsert) {
                        if (strlen($valStr) && is_numeric($valStr) && !stripos($p, "_number", 0))
                        {
                            $updateStr = $mysqli->prepare("INSERT INTO trackitem (TrackID, Name, Value, CountAmt) VALUES (?,?,NULL,?)");
                            $updateStr->bind_param("isi", $trackId, $p, $valStr);
                            if(!$updateStr->execute()) throw new Exception($mysqli->error);
                        } else {
                            $updateStr = $mysqli->prepare("INSERT INTO trackitem (TrackID, Name, Value, CountAmt) VALUES (?,?,?, NULL)");
                            $updateStr->bind_param("iss", $trackId, $p, $valStr);
                            if(!$updateStr->execute()) throw new Exception($mysqli->error);
                        }
                   }
                }
            }
        }

        if ($doInsert) {
            $dateStr = date("Y-m-d H:i:s");
            $updateStr = $mysqli->prepare("INSERT INTO track (Id, TimestampCreated, CountAmt, IP) VALUES(?, ?, 1, ?)");
            $updateStr->bind_param("sss", $tr_id, $dateStr, $_SERVER['REMOTE_ADDR']);
            if(!$updateStr->execute()) throw new Exception($mysqli->error);

            $query = "SELECT TrackID FROM track ORDER BY TrackID DESC LIMIT 0,1";
            $result2 = $mysqli->query($query);
            if ($result)
            {
                $row2 = $result2->fetch_row();
                $result2->close();
                if ($row2) {
                    $trackId = $row2[0];

                    foreach (array_keys($_POST) as $p) {
                        $valStr = $_POST[$p];

                        if (substr($p, 0, 4) == "num_") {
                            $numStatsKeys[] = $p;
                        }

                        if ($p == "Collection_number") {
                            $rg_number = $_POST[$p];
                        }

                        if (strlen($valStr) && is_numeric($valStr) && !stripos($p, "_number", 0))
                        {
                            $updateStr = $mysqli->prepare(
                                "INSERT INTO trackitem (TrackID, Name, Value, CountAmt) VALUES (?, ?, NULL, ?)"
                            );
                            $updateStr->bind_param("isi", $trackId, $p, $valStr);
                            if(!$updateStr->execute()) throw new Exception($mysqli->error);
                        } else {
                            $updateStr = $mysqli->prepare(
                                "INSERT INTO trackitem (TrackID, Name, Value, CountAmt) VALUES (?, ?, ?, NULL)"
                            );
                            $updateStr->bind_param("iss", $trackId, $p, $valStr);
                            if(!$updateStr->execute()) throw new Exception($mysqli->error);
                        }
                    }
                } else {
                    echo "couldn't find the row with the highest key\n";
                }
            } else {
                echo "`couldn't find the highest key\n";
            }
        }

            if (count($numStatsKeys) > 0) {

                $query = $mysqli->prepare("SELECT RegisterID FROM register WHERE RegNumber = ?");
                $query->bind_param("s", $rg_number);

                if(!$query->execute()) throw new Exception($mysqli->error);
                $result = $query->get_result();

                if ($result) {
                    $row = $result->fetch_row();
                    $result->close();

                    if ($row) {
                        $doInsert = 0;
                        $registerId  = $row[0];
                        $count    = $row[1] + 1;

                        foreach ($numStatsKeys as $p) {

                            $doItemInsert = 1;

                            $query = $mysqli->prepare("SELECT RegisterItemID FROM registeritem WHERE RegisterID = ? AND Name = ?");
                            $query->bind_param("is", $registerId, $p);
                            if(!$query->execute()) throw new Exception($mysqli->error);

                            $result = $query->get_result();
                            if ($result) {
                                $row = $result->fetch_row();
                                $result->close();
                                if ($row)
                                {
                                    $doItemInsert    = 0;
                                    $registerItemId  = $row[0];
                                    $updateStr = $mysqli->prepare("UPDATE registeritem SET CountAmt=? WHERE RegisterItemID = ?");
                                    $updateStr->bind_param("si", $_POST[$p], $registerItemId);
                                    if(!$updateStr->execute()) throw new Exception($mysqli->error);
                                }
                            }

                            if ($doItemInsert) {
                                $updateStr = $mysqli->prepare(
                                    "INSERT INTO registeritem (RegisterID, Name, Value, CountAmt) VALUES (?, ?, NULL, ?)"
                                );
                                $updateStr->bind_param("isi", $registerId, $p, $_POST[$p]);
                                if(!$updateStr->execute()) throw new Exception($mysqli->error);
                           }
                        }
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
