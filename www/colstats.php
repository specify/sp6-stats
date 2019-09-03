<?php
    include ("/etc/myauth.php");

  if ($_POST != '') {

    $cnt = 0;
    foreach (array_keys($_POST) as $p) {
        $cnt++;
    }

    $dateTime =  "date=" . date("y/m/d") ." " . date("H:i:s") . "\n";
    $data = "---------------\n" . $dateTime;
    $data = $data . "ip=" . $_SERVER['REMOTE_ADDR'] . "\n";
    if ($cnt == 0) {
        //echo "No arguments!<br>";
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

        $db_select = mysql_select_db("stats");
        if (!$db_select) {
          die ("Couldn't 'select_db' " . mysql_error());
        }

        $tr_id = $_POST['id'];
        $query = "SELECT ColStatsID, CountAmt FROM colstats WHERE Id = '" . $tr_id . "'";

        $rg_number    = "";
        $numStatsKeys = array();
        $doInsert     = 1;
        $result       = mysql_query($query);
        if ($result) {
            $row = mysql_fetch_row(($result));
            if ($row) {
                $doInsert = 0;
                $colstatsId  = $row[0];
                $count    = $row[1] + 1;
		        $timestampModified = "'20" . date("y-m-d") ." " . date("H:i:s") . "'";
                $updateStr = "UPDATE colstats SET CountAmt=" . $count . ", TimestampModified=" . $timestampModified . " WHERE ColStatsID = " . $colstatsId;
                $result = mysql_query($updateStr) or die(mysql_error());  

                foreach (array_keys($_POST) as $p) {
                
                    $doItemInsert = 1;
                    $prefix       = substr($p, 0, 5);
                    $isStat       = $prefix == "catby" || $prefix == "audit";
                    //echo $p . " [" . $prefix . "]  isStat[" . $isStat . "]\n";
                    
                    if (substr($p, 0, 4) == "num_") {
                       $numStatsKeys[] = $p;
                    }
                    
                    if ($p == "Collection_number") {
                        $rg_number = $_POST[$p];
                    }

                    $query = "SELECT ColStatsItemID FROM colstatsitem WHERE ColStatsID = ".$colstatsId." AND Name ='" . $p ."'"; 
                    #echo "SEL: " . $query . "\n";
                    $result = mysql_query($query);
                    if ($result) {
                        $row = mysql_fetch_row(($result));
                        if ($row)
                        {
                            $doItemInsert = 0;
                            $colstatsItemId  = $row[0];
                            $updateStr    = "UPDATE colstatsitem SET ";
                            $valStr       = $_POST[$p];
                            
                            if (strlen($valStr) && is_numeric($valStr) && !stripos($p, "_number", 0))
                            {
                                $updateStr .= "CountAmt=" . $valStr . ", Value=NULL, Stat=NULL";
                                
                            } else if ($isStat) {
                            
                                $updateStr .= "Stat='" . $valStr . "', CountAmt=NULL, Value=NULL";
                            
                            } else {
                                $updateStr .= "Value='" . $valStr . "', CountAmt=NULL, Stat=NULL";
                            }
                            $updateStr .= " WHERE ColStatsItemID = " . $colstatsItemId;
                        }
                    }

                    if ($doItemInsert) {

                        $updateStr = "INSERT INTO colstatsitem (ColStatsID, Name, Value, CountAmt, Stat) VALUES (" . $colstatsId . ", '" . $p . "', ";
                        if ($isStat) {
                        
                            $updateStr .= "NULL, NULL, '" . $valStr . "')";

                        } else if (strlen($valStr) && is_numeric($valStr) && !stripos($p, "_number", 0))
                        {
                            $updateStr .= "NULL, " . $valStr . ", NULL)";
                        
                        } else {
                            $updateStr .= "'" . $valStr . "', NULL, NULL)";
                        }
                   }
                   //echo $isStat . " UP: " . $updateStr . "\n\n";
                   $result = mysql_query($updateStr) or die(mysql_error());  
                }
            }
        }

        if ($doInsert) {
        
            $updateStr = "INSERT INTO colstats (Id, TimestampCreated, CountAmt, IP) VALUES('$tr_id', ";
            $dateStr   = $_POST['date'];
            
            $updateStr .= "'20" . date("y-m-d") ." " . date("H:i:s") . "', 1, '" . $_SERVER['REMOTE_ADDR'] . "')";
            #echo "INSERT-> " . $updateStr . "\n";
            $result = mysql_query($updateStr) or die(mysql_error());
            
            if ($result)
            {
                $query = "SELECT ColStatsID FROM colstats ORDER BY ColStatsID DESC LIMIT 0,1";
                $result2 = mysql_query($query) or die(mysql_error());
                if ($result)
                {
                    $row2 = mysql_fetch_row(($result2));
                    if ($row2) {
                        $colstatsId = $row2[0];

                        foreach (array_keys($_POST) as $p) {
                            $valStr = $_POST[$p];
                            $prefix       = substr($p, 0, 5);
                            $isStat       = $prefix == "catby" || $prefix == "audit";
                            //echo $p . " [" . $prefix . "]  isStat[" . $isStat . "]\n";
                            
                            if (substr($p, 0, 4) == "num_") {
                               $numStatsKeys[] = $p;
                            }
                            
                            if ($p == "Collection_number") {
                                $rg_number = $_POST[$p];
                            }                            

                            $updateStr = "INSERT INTO colstatsitem (ColStatsID, Name, Value, CountAmt, Stat) VALUES (" . $colstatsId . ", '" . $p . "', ";
                            if ($isStat) {
                            
                                $updateStr .= "NULL, NULL, '" . $valStr . "')";
    
                            } else if (strlen($valStr) && is_numeric($valStr) && !stripos($p, "_number", 0))
                            {
                                $updateStr .= "NULL, " . $valStr . ", NULL)";
                            
                            } else {
                                $updateStr .= "'" . $valStr . "', NULL, NULL)";
                            }

                            //echo $isStat . " INSERT-> " . $updateStr . "\n";

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
            #echo "Count " . count($numStatsKeys) . " RN: " . $rg_number . "\n";

            if (count($numStatsKeys) > 0) {
            
                $query  = "SELECT RegisterID FROM register WHERE RegNumber = '" . $rg_number . "'";
                $result = mysql_query($query);
                if ($result) {
                    $row = mysql_fetch_row(($result));
                    
                    if ($row) {
                        $doInsert = 0;
                        $registerId  = $row[0];
                        $count    = $row[1] + 1;
        
                        foreach ($numStatsKeys as $p) {
                        
                            $doItemInsert = 1;
                            
                            $query = "SELECT RegisterItemID FROM registeritem WHERE RegisterID = ".$registerId." AND Name ='" . $p ."'"; 
                            #echo "SEL: " . $query . "\n";
                            $result = mysql_query($query);
                            if ($result) {
                                $row = mysql_fetch_row(($result));
                                if ($row)
                                {
                                    $doItemInsert    = 0;
                                    $registerItemId  = $row[0];
                                    $updateStr = "UPDATE registeritem SET CountAmt='" . $_POST[$p] . "' WHERE RegisterItemID = " . $registerItemId;
                                }
                            }
        
                            if ($doItemInsert) {
        
                                $updateStr = "INSERT INTO registeritem (RegisterID, Name, Value, CountAmt) VALUES (" . $registerId . ", '" . $p . "', ";
                                $updateStr .= "NULL, "  . $_POST[$p] . ")";
                           }
                           #echo "UP: " . $updateStr . "\n\n";
                           $result = mysql_query($updateStr) or die(mysql_error());  
                        }
                    }
                }
            }

        mysql_close($connection);
    }
    echo "ok";

  } else {
    echo "No arguments!<br>";
  }

?>
