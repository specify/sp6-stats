<?php

include("/etc/myauth.php");

global $mysql_hst, $mysql_usr, $mysql_pwd, $mysql_db;

if ($mysql_hst == "localhost")
{
	$mysql_hst = "127.0.0.1";
}
//$mysql_db = "KU_Fish_Tissue";

$collectionId = 0;

// Note: See http://www.php.net/manual/en/features.file-upload.common-pitfalls.php
// for discussion of PHP server configuration directives that you will probably need
// to set for file uploads.

// ****  Configuration  ********************************************


// TODO: Put IP Address ranges here.

// Set debug to true for more debugging information in output.
// Debug = true can cause output to be proceeded by an error message, and generate unexpected header.
$debug = false;

// *** End Configuration **********************************************

// initialize variables so that they can't be provided in any other route.
$header    = "OK";
$action    = "ping";
$dir       = "";
$doJustZip = TRUE;

$SQLERRNO  = -1;
$SQLERRMSG = "";


class result
{
   // Structure to hold results of handler
   public $ipaddr;
   public $action;
   public $spuser;
   public $collection;
   public $success = "false";
   public $message;
   public $storeName;
   public $storePath;
   public $hash;
   public $debug;
}

// Define an empty results instance.
$result = new result();

// translations for upload error codes
$error_codes = array(
    0=>'No Error code.',
    1=>'The uploaded file size exceeds the upload_max_filesize directive in php.ini.',
    2=>'The uploaded file size exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
    3=>'The uploaded file was only partially uploaded.',
    4=>'No file was uploaded.',
    6=>'Missing temporary directory.',
    7=>'Failed to write uploaded file to disk.',
    8=>'A PHP extension stopped the file upload.'
);
/*
$_POST = array();
$_POST['action'] = 'adduser';
$_POST['usrname'] = 'rods';
$_POST['pwd'] = 'rods';
$_POST['inst'] = 'NHM';

  var_dump($_GET);
  echo "<br>";
*/

// Obtain variables from post.
// Sanitize all input to known good values.

$legalFilenameChars="/[^A-Za-z0-9\.\-]/";
$action    = $_GET['action'];      // action for handler to take

// Note: Remote address is trivial to spoof.
// Somewhat better access control is available through .htaccess
$ipaddr = $_SERVER["REMOTE_ADDR"];
// PJM Note: sending output before invoking header() will send headers
// immediately and result in an error message on invocation of header().

$result->ipaddr =   $ipaddr ;
// TODO: Refactor into a configuration array variable

$dbName = "KU_Fish_Tissue";
$dbId = null;
if (isset($_GET['dbid']))
{
    $dbId = $_GET['dbid'];
} else if (isset($_POST['dbid']))
{
    $dbId = $_POST['dbid'];
}

if (isset($dbId))
{
    $databases = array(0 => "KU_Fish_Tissue",
                       1 => "kuinvp4_dbo_6",
                       2 => "KANULichenDB",
                       3 => "KANUVascularPlantDB",
                       4 => "KUBirds",
                       5 => "KUHerps",
                       6 => "KUVP_dbo_6",
                       7 => "entosp_dbo_6",
                       8 => "kumam_dbo_6",
                       9 => "trichomycetes_dbo_6");
    $dbName = $databases[$dbId];
}
//echo "[" . $dbName . "]<BR>";

$db = new mysqli($mysql_hst, $mysql_usr, $mysql_pwd, $dbName);
if ($mysqli->connect_errno) {
    printf("Connect failed: %s\n", $mysqli->connect_error);
    exit();
}

$db2 = new mysqli($mysql_hst, $mysql_usr, $mysql_pwd, $dbName);
if ($mysqli->connect_errno) {
    printf("Connect failed: %s\n", $mysqli->connect_error);
    exit();
}

$tableName    = "taxon";
$tableNameCap = "Taxon";

if (isset($_GET['cln']))
{
    $collectionId = $_GET['cln'];
} else if (isset($_GET['cln']))
{
    $collectionId = $_POST['cln'];
}

$doTesting = 0;
if ($doTesting)
{
    doTests($db);

} else
{
    $output = "";

    switch ($action)
    {
        case "getco":
            $output = retrieveCOJSON($db, $_GET);
            break;

        case "searchco":
            $output = getSearchCOJSON($db, $_GET);
            break;

        case "searchtx":
            $output = getSearchTaxaJSON($db, $_GET);
            break;

        case "rootnode":
            $output = getRootTreeNodeJSON($db, $_GET);
            break;

        case "treenode":
            $output = getTreeNodeJSON($db, $_GET);
            break;

        case "databases":
            $output = getDatabasesJSON($db, $_GET);
            break;

        case "collections":
            $output = getCollectionsJSON($db, $_GET);
            break;

        case "cofornode":
            $output = retrieveCollObjsFromTreeNode($db, $_GET);
            break;

        case "cofornoderange":
            $output = retrieveCollObjsFromTreeNodeRange($db, $_GET);
            break;

        case "treeranks":
            $output = retrieveRankMap($db, $_GET);
            break;

        case "ping":
            $output = pingJSON($_POST);
            break;
    }

    // Begin output with an appropriate header.
    switch ($header)
    {
       case "OK":
          @header("HTTP/1.1 200 OK");
          break;
       case "401":
          @header("HTTP/1.1 401 Unauthorized");
          break;
       case "417":
       default;
          @header("HTTP/1.1 417 Expectation Failed");
    }
    echo $output;
}

// Return results of handler action.
// TODO: Serialize as XML?
if ($debug)
{
    echo "IP [$result->ipaddr]<BR>\n";
    echo "ACTION [$result->action]<BR>\n";
    echo "SPUSER [$result->spuser]<BR>\n";
    echo "COLLECTION [$result->collection]<BR>\n";
    echo "SUCCESS [$result->success]<BR>\n";
    echo "MESSAGE [$result->message]<BR>\n";
    echo "FILENAME [$result->storeName]<BR>\n";
    echo "PATH [$result->storePath]<BR>\n";
    echo "HASH [$result->hash]<BR>\n";
    echo "DEBUG [$result->debug";
}

if($debug)
{
  echo '<pre>';
  var_dump($_FILES);
  echo '</pre>';
  echo '<pre>';
  var_dump($_POST);
  echo '</pre>';
}

    //**** Supporting functions ****

    //----------------------------------------------
    function retrieveFullCOJSON($db, $_GT, $fromSQL, $param)
    {
        global $db2;

        $coId        = null;
        $catDate     = null;
        $catDatePrec = null;
        $countAmt    = null;
        $fieldNum    = null;
        $accNum      = null;
        $ceId        = null;

        $jsonStr = "";
        $status  = "ERROR";
        $stmt    = $db->stmt_init();

        $sql = "SELECT co.CollectionObjectID, co.CatalogNumber, co.CatalogedDate, co.CatalogedDatePrecision, co.CountAmt, co.FieldNumber, ac.AccessionNumber, co.CollectingEventID " . $fromSQL;
        //echo $sql . " ($param)<BR>";
        if ($stmt->prepare($sql)) {
            $stmt->bind_param('s', $param);

            if (!$stmt->execute())
            {
                echo "<br>Execute Error: " . $db->error . " | " .  $db->errno . "<br>";
	        echo "<br>Execute Error: " . $stmt->error . " | " .  $stmt->errno . "<br>";
                $stmt->close();
                return -1;
            }

            $rv = $stmt->bind_result($coId, $catNum, $catDate, $catDatePrec, $countAmt, $fieldNum, $accNum, $ceId);
            if (!$rv)
            {
                echo "<br>Bind Error: " . $db->error . " | " .  $db->errno . "\n";
	            echo "<br>Bind Error: " . $stmt->error . " | " .  $stmt->errno . "\n";
                $stmt->close();
                return null;
            }

            $cnt = 0;
            $jsonStr = "[";
            while ($stmt->fetch())
            {
                if ($cnt > 0) $jsonStr .= ",";

                $fields =  array("coId", "catNum", "catDate", "catDatePrec", "countAmt", "fieldNum", "accNum");
                $values =  array($coId, $catNum, $catDate, $catDatePrec, $countAmt, $fieldNum, $accNum);

                $jsonStr .= "{";
                $jsonStr .= buildJSONObj("co", $fields, $values, $stmt);

                $detStr = getDeterminationsJSON($db2, $coId);
                if ($detStr != "")
                {
                    $jsonStr .= ", ";
                    $jsonStr .= $detStr;
                }
                $prepStr = getPreparationsJSON($db2, $coId);
                if ($prepStr != "")
                {
                    $jsonStr .= ", ";
                    $jsonStr .= $prepStr;
                }
                $ceStr = getCollectingEventJSON($db2, $ceId);
                if ($ceStr != "")
                {
                    $jsonStr .= ", ";
                    $jsonStr .= $ceStr;
                }
                $attStr = getAttachmentsJSON($db2, $coId);
                if ($attStr != "")
                {
                    $jsonStr .= ", ";
                    $jsonStr .= $attStr;
                }
                $jsonStr .= "}";
                $cnt++;
            }
            $jsonStr .= "]";
            $status = "OK";
        } else
        {
            echo "<br>Error: " . $db->error . " | " .  $db->errno . "\n";
	    echo "<br>Error: " . $stmt->error . " | " .  $stmt->errno . "\n";
        }
        $stmt->close();

        return "{ \"status\" : \"" . $status . "\", \"catnum\" : \"$catNum\", \"data\" : $jsonStr }";
    }

    //----------------------------------------------
    function retrieveCOJSON($db, $_GT)
    {
        global $collectionId;

        $catNum = $_GT['catnum'];

        $sql = "FROM collectionobject co LEFT JOIN accession ac ON co.AccessionID = ac.AccessionID WHERE co.CatalogNumber = ?";
        if (isset($_GET['cln']))
        {
            $sql .= " AND co.CollectionID = " . $_GET['cln'] . " ";
        }
        return retrieveFullCOJSON($db, $_GT, $sql, $catNum);
    }

    //----------------------------------------------
    function retrieveCollObjsFromTreeNode($db, $_GT)
    {
        $nodeId   = $_GT['nodeid'];
        $treeType = $_GET['treetype'];

        if ($treeType == 3)
        {
            $sql = "FROM taxon t INNER JOIN determination d ON t.TaxonID = d.TaxonID INNER JOIN collectionobject co ON d.CollectionObjectID = co.CollectionObjectID LEFT JOIN accession ac ON co.AccessionID = ac.AccessionID WHERE ";
            $sql .= "co.CollectionID = " . $_GET['cln'] . " AND t.TaxonID = ? ORDER BY co.CatalogNumber";
            return retrieveFullCOJSON($db, $_GT, $sql, $nodeId);
        }

        if ($treeType == 0)
        {
            $sql = "FROM collectionobject co LEFT JOIN accession ac ON co.AccessionID = ac.AccessionID INNER JOIN collectingevent ce ON co.CollectingEventID = ce.CollectingEventID INNER JOIN locality l ON ce.LocalityID = l.LocalityID INNER JOIN geography g ON l.GeographyID = g.GeographyID WHERE ";
            $sql .= "co.CollectionID = " . $_GET['cln'] . " AND g.GeographyID = ? ORDER BY co.CatalogNumber";
            return retrieveFullCOJSON($db, $_GT, $sql, $nodeId);
        }
        return "";
    }

    //----------------------------------------------
    function retrieveCollObjsFromTreeNodeRange($db, $_GT)
    {
        $nodeId   = $_GT['nodeid'];
        $treeType = $_GET['treetype'];
        $clnId    = $_GT['cln'];
        $nodeNum  = $_GT['nn'];
        $highNN   = $_GT['hn'];

        if ($treeType == 3)
        {
            //$sql = "FROM taxon t INNER JOIN determination d ON t.TaxonID = d.TaxonID INNER JOIN collectionobject co ON d.CollectionObjectID = co.CollectionObjectID LEFT JOIN accession ac ON co.AccessionID = ac.AccessionID WHERE t.TaxonID = ? ORDER BY co.CatalogNumber";
            $sql = "FROM taxon t LEFT OUTER JOIN determination d ON t.TaxonID = d.TaxonID " .
				   "LEFT OUTER JOIN collectionobject co ON d.CollectionObjectID = co.CollectionObjectID " .
//	 			   "LEFT OUTER JOIN preparation p ON co.CollectionObjectID = p.CollectionObjectID " .
	               "LEFT OUTER JOIN taxon pt ON t.ParentID = pt.TaxonID " .
	               "LEFT JOIN accession ac ON co.AccessionID = ac.AccessionID " .
                   "WHERE co.CollectionID = ? AND d.IsCurrent = TRUE AND t.NodeNumber >= " . $nodeNum . " AND t.NodeNumber <= " . $highNN .  " LIMIT 0, 300";
            return retrieveFullCOJSON($db, $_GT, $sql, $clnId);
        }

//         if ($treeType == 0)
//         {
//             $sql = "FROM collectionobject co LEFT JOIN accession ac ON co.AccessionID = ac.AccessionID INNER JOIN collectingevent ce ON co.CollectingEventID = ce.CollectingEventID INNER JOIN locality l ON ce.LocalityID = l.LocalityID INNER JOIN geography g ON l.GeographyID = g.GeographyID WHERE g.GeographyID = ? ORDER BY co.CatalogNumber";
//             return retrieveFullCOJSON($db, $_GT, $sql, $nodeId);
//         }
        return "";
    }

    function getSearchTaxaJSON($db, $_GT)
    {
        $taxa      = $_GT['taxa'];
        $tdfid     = $_GT['tdfid'];

        $txId      = null;
        $fullName  = null;
        $highNN    = null;
        $nodeN     = null;

        $stmt      = $db->stmt_init();

        $adjTaxa  = "%" . $taxa . "%";

        $results = "[";
        $sql = "SELECT TaxonID, FullName, HighestChildNodeNumber, NodeNumber FROM taxon WHERE FullName LIKE ? AND TaxonTreeDefID = " . $tdfid . " ORDER BY FullName";

        if ($stmt->prepare($sql)) {

            $stmt->bind_param('s', $adjTaxa);

            if (!$stmt->execute())
            {
                echo "<br>Execute Error: " . $db->error . " | " .  $db->errno . "\n";
                echo "<br>Execute Error: " . $stmt->error . " | " .  $stmt->errno . "\n";
                $stmt->close();
                return -1;
            }

            $rv = $stmt->bind_result($txId, $fullName, $highNN, $nodeN);
            if (!$rv)
            {
                echo "<br>Bind Error: " . $db->error . " | " .  $db->errno . "\n";
                echo "<br>Bind Error: " . $stmt->error . " | " .  $stmt->errno . "\n";
                $stmt->close();
                return null;
            }

            $cnt = 0;
            while ($stmt->fetch())
            {
                if ($cnt > 0) $results .= ", ";
                $results .= "{\"id\" : " . $txId . ", \"fn\" : \"" . $fullName . "\", \"hn\" : " . $highNN . ", \"nn\" : " . $nodeN . "}";
                $cnt++;
            }
            $results .= "]";

        } else
        {
            echo "<br>Error: " . $db->error . " | " .  $db->errno . "\n";
            echo "<br>Error: " . $stmt->error . " | " .  $stmt->errno . "\n";
        }
        $stmt->close();

        return "{ \"status\" : \"OK\", \"json\" : $results }";
    }

    function getSearchCOJSON($db, $_GT)
    {
        $catNum      = $_GT['catnum'];
        $clnId       = $_GT['cln'];

        $coId        = null;

        $stmt    = $db->stmt_init();

        $adjCatNum  = "%" . $catNum . "%";

        $results = "[";
        $sql = "SELECT co.CollectionObjectID, co.CatalogNumber FROM collectionobject co WHERE CollectionID = " . $clnId . " AND CatalogNumber LIKE ?";

        if ($stmt->prepare($sql)) {

            $stmt->bind_param('s', $adjCatNum);

            if (!$stmt->execute())
            {
                echo "<br>Execute Error: " . $db->error . " | " .  $db->errno . "\n";
                echo "<br>Execute Error: " . $stmt->error . " | " .  $stmt->errno . "\n";
                $stmt->close();
                return -1;
            }

            $rv = $stmt->bind_result($coId, $catNum);
            if (!$rv)
            {
                echo "<br>Bind Error: " . $db->error . " | " .  $db->errno . "\n";
                echo "<br>Bind Error: " . $stmt->error . " | " .  $stmt->errno . "\n";
                $stmt->close();
                return null;
            }

            $cnt = 0;
            while ($stmt->fetch())
            {
                if ($cnt > 0) $results .= ", ";
                $results .= "{\"id\" : \"" . $coId . "\", \"catNum\" : \"" . $catNum . "\"}";
                $cnt++;
            }
            $results .= "]";

        } else
        {
            echo "<br>Error: " . $db->error . " | " .  $db->errno . "\n";
            echo "<br>Error: " . $stmt->error . " | " .  $stmt->errno . "\n";
        }
        $stmt->close();

        return "{ \"status\" : \"OK\", \"catnum\" : \"$catNum\", \"json\" : $results }";
    }


    function getDeterminationsJSON($dbx, $colObjId)
    {

        $detDate        = null;
        $detDatePrec = null;
        $isCurrent    = null;
        $fullName    = null;
        $isAccepted    = null;
        $citesStatus    = null;
        $lastName    = null;
        $firstName    = null;

        $stmt = $dbx->stmt_init();

        $sql = "SELECT d.DeterminedDate, d.DeterminedDatePrecision, d.IsCurrent, t.FullName, t.IsAccepted, t.CitesStatus, a.LastName, a.FirstName FROM determination d LEFT JOIN taxon t ON d.TaxonID = t.TaxonID LEFT JOIN agent a ON d.DeterminerID = a.AgentID WHERE d.CollectionObjectID = ?";
        if ($stmt->prepare($sql)) {

            $stmt->bind_param('s', $colObjId);

            if (!$stmt->execute())
            {
                echo "<br>Execute Error: " . $db->error . " | " .  $db->errno . "\n";
                echo "<br>Execute Error: " . $stmt->error . " | " .  $stmt->errno . "\n";
                $stmt->close();
                return -1;
            }

            $rv = $stmt->bind_result($detDate, $detDatePrec, $isCurrent, $fullName, $isAccepted, $citesStatus, $lastName, $firstName);
            if (!$rv)
            {
                echo "<br>Bind Error: " . $db->error . " | " .  $db->errno . "\n";
                echo "<br>Bind Error: " . $stmt->error . " | " .  $stmt->errno . "\n";
                $stmt->close();
                return null;
            }

            $i = 0;
            $data = "\"dt\" : [";
            while ($stmt->fetch())
            {
                if ($i > 0) $data .= ", ";
                $fields = array("detDate", "detDatePrec", "isCurrent", "fullName", "isAccepted", "citesStatus", "lastName", "firstName");
                $values = array($detDate, $detDatePrec, $isCurrent, $fullName, $isAccepted, $citesStatus, $lastName, $firstName);
                $data  .= buildJSONObj("", $fields, $values, $stmt);
                $i++;
            }
            $data .= "]";
            return $data;
        } else
        {
            echo "<br>Error: " . $db->error . " | " .  $db->errno . "\n";
            echo "<br>Error: " . $stmt->error . " | " .  $stmt->errno . "\n";
        }
        $stmt->close();


        return "";
    }

    function getPreparationsJSON($dbx, $colObjId)
    {
        $countAmt        = null;
        $desc = null;
        $prepDate    = null;
        $prepDatePrec    = null;
        $isLoanable    = null;
        $prepType    = null;
        $firstName    = null;
        $lastName    = null;

        $stmt    = $dbx->stmt_init();

        $sql = " SELECT p.CountAmt, p.Description, p.PreparedDate, p.PreparedDatePrecision, pt.IsLoanable, pt.Name, a.FirstName, a.LastName FROM preparation p INNER JOIN preptype pt ON p.PrepTypeID = pt.PrepTypeID LEFT JOIN agent a ON p.PreparedByID = a.AgentID WHERE p.CollectionObjectID = ?";
        if ($stmt->prepare($sql)) {

            $stmt->bind_param('s', $colObjId);

            if (!$stmt->execute())
            {
                echo "<br>Execute Error: " . $db->error . " | " .  $db->errno . "\n";
                echo "<br>Execute Error: " . $stmt->error . " | " .  $stmt->errno . "\n";
                $stmt->close();
                return -1;
            }

            $rv = $stmt->bind_result($countAmt, $desc, $prepDate, $prepDatePrec, $isLoanable, $prepType, $firstName, $lastName);
            if (!$rv)
            {
                echo "<br>Bind Error: " . $db->error . " | " .  $db->errno . "\n";
                echo "<br>Bind Error: " . $stmt->error . " | " .  $stmt->errno . "\n";
                $stmt->close();
                return null;
            }

            $i = 0;
            $data = "\"pr\" : [";
            while ($stmt->fetch())
            {
                if ($i > 0) $data .= ", ";
                $fields = array("countAmt", "desc", "prepDate", "prepDatePrec", "isLoanable", "prepType", "firstName", "lastName");
                $values = array($countAmt, $desc, $prepDate, $prepDatePrec, $isLoanable, $prepType, $firstName, $lastName);
                $data  .= buildJSONObj("", $fields, $values, $stmt);
                $i++;
            }
            $data .= "]";
            return $data;
        } else
        {
            echo "<br>Error: " . $db->error . " | " .  $db->errno . "\n";
            echo "<br>Error: " . $stmt->error . " | " .  $stmt->errno . "\n";
        }
        $stmt->close();
        return "";
    }

    function getAttachmentsJSON($dbx, $colObjId)
    {
        $attachLoc   = null;
        $attachTitle = null;
        $attachMime  = null;

        $stmt     = $dbx->stmt_init();

        $sql = "SELECT a.AttachmentLocation, a.title, a.MimeType FROM attachment a INNER JOIN collectionobjectattachment ca ON a.AttachmentID = ca.AttachmentID INNER JOIN collectionobject co ON ca.CollectionObjectID = co.CollectionObjectID WHERE co.CollectionObjectID = ?";
        if ($stmt->prepare($sql)) {

            $stmt->bind_param('s', $colObjId);

            if (!$stmt->execute())
            {
                echo "<br>Execute Error: " . $db->error . " | " .  $db->errno . "\n";
                echo "<br>Execute Error: " . $stmt->error . " | " .  $stmt->errno . "\n";
                $stmt->close();
                return -1;
            }

            $rv = $stmt->bind_result($attachLoc, $attachTitle, $attachMime);
            if (!$rv)
            {
                echo "<br>Bind Error: " . $db->error . " | " .  $db->errno . "\n";
                echo "<br>Bind Error: " . $stmt->error . " | " .  $stmt->errno . "\n";
                $stmt->close();
                return null;
            }

            $i = 0;
            $data = "\"at\" : [";
            while ($stmt->fetch())
            {
                if ($i > 0) $data .= ", ";

                $fields = array("loc", "title", "mimetype");
                $values = array($attachLoc, $attachTitle, $attachMime);
                $data  .= buildJSONObj("", $fields, $values, $stmt);
                $i++;
            }
            $data .= "]";
            return $i == 0 ? "" : $data;
        } else
        {
            echo "<br>Error: " . $db->error . " | " .  $db->errno . "\n";
            echo "<br>Error: " . $stmt->error . " | " .  $stmt->errno . "\n";
        }
        $stmt->close();
        return "";
    }



    function getCollectingEventJSON($dbx, $ceId)
    {
        $startDate        = null;
        $startDatePrec = null;
        $endDate    = null;
        $endDatePrec    = null;
        $stnFieldNum    = null;
        $locName    = null;
        $lat    = null;
        $lon    = null;
        $geoName    = null;
        $geoFullName    = null;
        $isoCode    = null;
        $isPrimary    = null;
        $firstName    = null;
        $lastName    = null;

        $stmt    = $dbx->stmt_init();

        $sql = " SELECT ce.StartDate, ce.StartDatePrecision, ce.EndDate, ce.EndDatePrecision, ce.StationFieldNumber, l.LocalityName, l.Latitude1, l.Longitude1, g.Name, g.FullName, g.GeographyCode, cr.IsPrimary, A.FirstName, A.LastName FROM collectingevent ce LEFT JOIN locality l ON ce.LocalityID = l.LocalityID LEFT JOIN collector cr ON ce.CollectingEventID = cr.CollectorID LEFT JOIN agent A ON cr.AgentID = A.AgentID LEFT JOIN geography g ON l.GeographyID = g.GeographyID WHERE ce.CollectingEventID = ?";
        if ($stmt->prepare($sql)) {

            $stmt->bind_param('s', $ceId);

            if (!$stmt->execute())
            {
                echo "<br>Execute Error: " . $db->error . " | " .  $db->errno . "\n";
                echo "<br>Execute Error: " . $stmt->error . " | " .  $stmt->errno . "\n";
                $stmt->close();
                return -1;
            }

            $rv = $stmt->bind_result($startDate, $startDatePrec, $endDate, $endDatePrec, $stnFieldNum, $locName, $lat, $lon, $geoName, $geoFullName, $isoCode, $isPrimary, $firstName, $lastName);
            if (!$rv)
            {
                echo "<br>Bind Error: " . $db->error . " | " .  $db->errno . "\n";
                echo "<br>Bind Error: " . $stmt->error . " | " .  $stmt->errno . "\n";
                $stmt->close();
                return null;
            }

            $i = 0;
            $data = "\"ce\" : [";
            while ($stmt->fetch())
            {
                if ($i > 0) $data .= ", ";
                $data .= "{";
                $fields = array("startDate", "startDatePrec", "endDate", "endDatePrec", "stnFieldNum", "locName", "lat", "lon", "geoName", "geoFullName", "isoCode", "isPrimary", "firstName", "lastName");
                $values = array($startDate,  $startDatePrec,  $endDate,  $endDatePrec,  $stnFieldNum,  $locName,  $lat,  $lon,  $geoName,  $geoFullName,  $isoCode,  $isPrimary,  $firstName,  $lastName);
                $data  .= buildJSONObj("-", $fields, $values, $stmt);
                $data  .= ", \"hasLatLon\": " . (($lat != null && $lon != null && ($lat != 0.0 || $lon != 0.0)) ? "1" : "0");
                $data  .= "}";
                $i++;
            }
            $data .= "]";
            return $data;
        } else
        {
            echo "<br>Error: " . $db->error . " | " .  $db->errno . "\n";
            echo "<br>Error: " . $stmt->error . " | " .  $stmt->errno . "\n";
        }
        $stmt->close();
        return "";
    }

    function getDatabasesJSON()
    {
        $names = array("Ichthyology", "Invert Paleo", "Lichens", "Vascular Plants", "Ornithology", "Herpetology", "Vertebrate Paleo", "Entomology", "Mammals", "Trichomycetes");
        $ids   = array("0",           "1",            "2",       "3",               "4",           "5",           "6",                "7",          "8",       "9");
        $types = array("fish",        "invertpaleo",  "lowerplants", "vascplant",   "bird",        "herpetology", "vertpaleo",        "insect",     "mammal",  "fungi");

        $json = "{";
        $json .= "  \"institution\": \"KU Biodiversity Institute\",\n";
        $json .= "\"databases\" : [";
        for ($x = 0; $x < count($names); $x++) {
            if ($x > 0) $json .= ",";
            $json .= "{\n";
            $json .= "  \"name\": \"" . $names[$x] . "\",\n";
            $json .= "  \"id\": " . $ids[$x] . ",\n";
            $json .= "  \"type\": \"" . $types[$x] . "\"\n";
            $json .= "}\n";
        }
        $json .= "]}";
        return $json;
    }

    function getFieldFromSQL($db, $sql)
    {
        //echo $sql . "<br>";
        $value = null;
        $stmt2 = $db->stmt_init();
        if ($stmt2->prepare($sql))
        {
            //echo "1<br>";
            if (!$stmt2->execute())
            {
                echo "<br>Execute Error: " . $db->error . " | " .  $db->errno . "\n";
                echo "<br>Execute Error: " . $stmt2->error . " | " .  $stmt2->errno . "\n";
                $stmt2->close();
                return -1;
            }
            //echo "2<br>";
            $rv = $stmt2->bind_result($value);
            if (!$rv)
            {
                echo "<br>Bind Error: " . $db->error . " | " .  $db->errno . "\n";
                echo "<br>Bind Error: " . $stmt2->error . " | " .  $stmt2->errno . "\n";
                $stmt2->close();
                return null;
            }
            //echo "3<br>";
            if (!$stmt2->fetch())
            {
                echo "<br>fetch Error: " . $db->error . " | " .  $db->errno . "\n";
                echo "<br>fetch Error: " . $stmt2->error . " | " .  $stmt2->errno . "\n";
                $stmt2->close();
                return null;
            }
        }
        //echo "4 - (" . $value . ")<br>";
        //$stmt2->close();
        return $value;
    }

    function getCollectionsJSON($db, $GT)
    {
        $dbId = $GT['dbid'];

        $collectionId = null;
        $collectionName = null;
        $collectionType = null;
        $isEmbeddedCollectingEvent = null;
        $geographyTreeDefID = null;
        $lithoStratTreeDefID = null;
        $taxonTreeDefID = null;
        $geologicTimePeriodTreeDefID = null;
        $isPaleoContextEmbedded = null;
        $paleoContextChildTable = null;
        $disciplineType = null;
        $instName = null;


        // Get Inst Name
        $sql = "SELECT Name FROM institution";
        $instName = getFieldFromSQL($db, $sql);
        $mediaURL = "http://biimages.biodiversity.ku.edu/";

        $stmt = $db->stmt_init();
        $sql = "SELECT c.collectionId, c.CollectionName, c.CollectionType, c.IsEmbeddedCollectingEvent, d.GeographyTreeDefID, d.LithoStratTreeDefID, d.TaxonTreeDefID, d.GeologicTimePeriodTreeDefID, d.IsPaleoContextEmbedded, d.PaleoContextChildTable, dv.DisciplineType FROM collection c INNER JOIN discipline d ON c.DisciplineID = d.UserGroupScopeId INNER JOIN division dv ON d.DivisionID = dv.UserGroupScopeId ORDER BY c.collectionId";
        if ($stmt->prepare($sql)) {

            if (!$stmt->execute())
            {
                echo "<br>Execute Error: " . $db->error . " | " .  $db->errno . "\n";
                echo "<br>Execute Error: " . $stmt->error . " | " .  $stmt->errno . "\n";
                $stmt->close();
                return -1;
            }
            $rv = $stmt->bind_result($collectionId, $collectionName, $collectionType, $isEmbeddedCollectingEvent, $geographyTreeDefID, $lithoStratTreeDefID, $taxonTreeDefID, $geologicTimePeriodTreeDefID, $isPaleoContextEmbedded, $paleoContextChildTable, $disciplineType);
            if (!$rv)
            {
                echo "<br>Bind Error: " . $db->error . " | " .  $db->errno . "\n";
                echo "<br>Bind Error: " . $stmt->error . " | " .  $stmt->errno . "\n";
                $stmt->close();
                return null;
            }

            $i = 0;
            $data = "{\"institution\" : \"" . $instName . "\",";
            $data .= "\"media_url\" : \"$mediaURL\",";
            $data .= "\"collections\" : [";
            while ($stmt->fetch())
            {
                if ($i > 0) $data .= ", ";
                $fields = array("collectionId", "collectionName", "collectionType", "isEmbeddedCollectingEvent", "geographyTreeDefID", "lithoStratTreeDefID", "taxonTreeDefID", "geologicTimePeriodTreeDefID", "isPaleoContextEmbedded", "paleoContextChildTable", "disciplineType");
                $values = array($collectionId, $collectionName, $collectionType, $isEmbeddedCollectingEvent, $geographyTreeDefID, $lithoStratTreeDefID, $taxonTreeDefID, $geologicTimePeriodTreeDefID, $isPaleoContextEmbedded, $paleoContextChildTable, $disciplineType);
                $data  .= buildJSONObj("", $fields, $values, $stmt);
                $i++;
            }
            $data .= "]}";
            $stmt->close();
            return $data;
        } else
        {
            echo "<br>Error: " . $db->error . " | " .  $db->errno . "\n";
            echo "<br>Error: " . $stmt->error . " | " .  $stmt->errno . "\n";
        }
        $stmt->close();
        return "";
    }

    function setupTreeInfo($treeType)
    {
//    TDTGeography     = 0,
//    TDTGeoTimePeriod = 1,
//    TDTLithostrat    = 2,
//    TDTTaxonomy      = 3,

        global $tableName;
        global $tableNameCap;

        switch ($treeType)
        {
            case 0:
                $tableName    = "geography";
                $tableNameCap = "Geography";
                break;

            case 1:
                $tableName    = "geologictimeperiod";
                $tableNameCap = "GeologicTimePeriod";
                break;

            case 2:
                $tableName    = "lithostrat";
                $tableNameCap = "LithoStrat";
                break;

            case 3:
                $tableName    = "taxon";
                $tableNameCap = "Taxon";
                break;
        }
        //echo "[" . $treeType . "][" . $tableName . "]<BR>";
    }

    function getTreeToCOQuery($treeType, $recId)
    {
//    TDTGeography     = 0,
//    TDTGeoTimePeriod = 1,
//    TDTLithostrat    = 2,
//    TDTTaxonomy      = 3,

        switch ($treeType)
        {
            case 0:
                return "SELECT g.GeographyID, COUNT(g.GeographyID) FROM geography g INNER JOIN locality l ON g.GeographyID = l.GeographyID INNER JOIN collectingevent ce ON l.LocalityID = ce.LocalityID INNER JOIN collectionobject co ON ce.CollectingEventID = co.CollectingEventID WHERE g.ParentID = $recId GROUP BY g.GeographyID";

            case 1:
                $tableName    = "geologictimeperiod";
                $tableNameCap = "GeologicTimePeriod";
                break;

            case 2:
                $tableName    = "lithostrat";
                $tableNameCap = "LithoStrat";
                break;

            case 3:
                $tableName    = "taxon";
                $tableNameCap = "Taxon";
                break;
        }
        //echo "[" . $treeType . "][" . $tableName . "]<BR>";
    }

    function retrieveTaxonTreeNodeJSON($db, $treeType, $recId, $rankId)
    {
        $pTaxonID = null;
        $pCommonName = null;
        $pFullName = null;
        $pIsAccepted = null;
        $pIsHybrid = null;
        $pName = null;
        $pRankID = null;
        $pCitesStatus = null;

        $stmt = $db->stmt_init();

        $where = "";
        if ($recId == -1)
        {
            //$where = "RankID  = " . $rankId;
            $where = "p.ParentID IS NULL";
        } else
        {
            $where = "p.ParentID = " . $recId;
        }

        //-----------------------------------------

        $recIdToCOCountMap = array();
        $sql = "SELECT t.TaxonID, COUNT(t.TaxonID) FROM taxon t INNER JOIN determination d ON t.TaxonID = d.TaxonID WHERE d.IsCurrent = TRUE AND t.ParentID = $recId GROUP BY t.TaxonID";
        //echo $sql . "<br>";
        if ($stmt->prepare($sql))
        {
            if (!$stmt->execute())
            {
                $stmt->close();
                return -1;
            }

            $rv = $stmt->bind_result($pTaxonID, $count);
            if (!$rv)
            {
                $stmt->close();
                return null;
            }

            while ($stmt->fetch())
            {
                //echo " $recIdToCOCountMap[$pTaxonID] = $count<br>";
                $recIdToCOCountMap[$pTaxonID] = $count;
            }
            $stmt->close();
            $stmt = $db->stmt_init();
        }

        //-----------------------------------------
        $sql = "SELECT p.TaxonID, COUNT(p.TaxonID) FROM taxon p INNER JOIN taxon k ON p.TaxonID = k.ParentID WHERE " . $where . " GROUP BY p.TaxonID";
        //echo $sql . "<BR>";
        if ($stmt->prepare($sql))
        {
            if (!$stmt->execute())
            {
                $stmt->close();
                return -1;
            }

            $rv = $stmt->bind_result($pTaxonID, $count);
            if (!$rv)
            {
                $stmt->close();
                return null;
            }

            $recIdMap = array();

            while ($stmt->fetch())
            {
                //echo $pTaxonID . " -> " . $count . "<BR>";
                $recIdMap[$pTaxonID] = $count;
            }
        }
        $stmt->close();

        //-------------------------

        $stmt = $db->stmt_init();
        $sql = "SELECT p.TaxonID, p.CommonName, p.FullName, p.IsAccepted, p.IsHybrid, p.Name, p.RankID, p.CitesStatus FROM taxon p WHERE " . $where . " ORDER BY FullName";
        if ($stmt->prepare($sql))
        {
            if (!$stmt->execute())
            {
                echo "<br>Execute Error: " . $db->error . " | " .  $db->errno . "\n";
                echo "<br>Execute Error: " . $stmt->error . " | " .  $stmt->errno . "\n";
                $stmt->close();
                return -1;
            }

            $rv = $stmt->bind_result($pTaxonID, $pCommonName, $pFullName, $pIsAccepted, $pIsHybrid, $pName, $pRankID, $pCitesStatus);
            if (!$rv)
            {
                echo "<br>Bind Error: " . $db->error . " | " .  $db->errno . "\n";
                echo "<br>Bind Error: " . $stmt->error . " | " .  $stmt->errno . "\n";
                $stmt->close();
                return null;
            }

            $fields = array("id", "commonName", "fullName", "isAccepted", "isHybrid", "name", "rankID", "citesStatus");

            $i = 0;
            $data = "\"data\": [";
            while ($stmt->fetch())
            {
                if ($i > 0) $data .= ", ";
                $data .= "{";
                $values = array($pTaxonID, $pCommonName, $pFullName, $pIsAccepted, $pIsHybrid, $pName, $pRankID, $pCitesStatus);
                $data  .= buildJSONObj("-", $fields, $values, $stmt);

                $hasKids = 0;
                $coCount = 0;
                if (isset($recIdMap[$pTaxonID]))
                {
                    $hasKids = $recIdMap[$pTaxonID] > 0;
                }
                if (isset($recIdToCOCountMap[$pTaxonID]))
                {
                    $coCount = $recIdToCOCountMap[$pTaxonID];
                }
                $data .= ", \"hasKids\": " . ($hasKids > 0 ? "1" : "0");
                $data .= ", \"cos\": " . $coCount . "}";
                $i++;
            }
            $data .= "]";
            $stmt->close();
            return "{ \"status\" : \"OK\", \"id\" : \"$recId\", $data }";
        } else
        {
            echo "<br>Error: " . $db->error . " | " .  $db->errno . "\n";
            echo "<br>Error: " . $stmt->error . " | " .  $stmt->errno . "\n";
        }
        $stmt->close();
        return "{ \"status\" : \"ERROR\", \"id\" : \"$recId\", $data }";
    }


    function retrieveTreeNodeJSON($db, $treeType, $recId, $rankId)
    {
        global $tableName;
        global $tableNameCap;

        setupTreeInfo($treeType);

        $pTaxonID  = null;
        $pFullName = null;
        $pName     = null;
        $pRankID   = null;

        $stmt = $db->stmt_init();

        $where = "";
        if ($recId == -1)
        {
            //$where = "RankID  = " . $rankId;
            $where = "p.ParentID IS NULL";
        } else
        {
            $where = "p.ParentID = " . $recId;
        }

        //-----------------------------------------

        $recIdToCOCountMap = array();
        $sql =  getTreeToCOQuery($treeType, $recId);
        //echo $sql . "<br>";
        if ($stmt->prepare($sql))
        {
            if (!$stmt->execute())
            {
                $stmt->close();
                return -1;
            }

            $rv = $stmt->bind_result($pTaxonID, $count);
            if (!$rv)
            {
                $stmt->close();
                return null;
            }

            while ($stmt->fetch())
            {
                //echo " $recIdToCOCountMap[$pTaxonID] = $count<br>";
                $recIdToCOCountMap[$pTaxonID] = $count;
            }
            $stmt->close();
            $stmt = $db->stmt_init();
        }

        //------------------------------------------

        $sql = "SELECT p." . $tableNameCap . "ID, COUNT(p." . $tableNameCap . "ID) FROM " . $tableName . " p INNER JOIN " .
                $tableName . " k ON p." . $tableNameCap . "ID = k.ParentID WHERE " . $where . " GROUP BY p." . $tableNameCap . "ID";
        //echo $sql . "<BR>";
        if ($stmt->prepare($sql))
        {
            if (!$stmt->execute())
            {
                $stmt->close();
                return -1;
            }

            $rv = $stmt->bind_result($pTaxonID, $count);
            if (!$rv)
            {
                $stmt->close();
                return null;
            }

            $recIdMap = array();

            while ($stmt->fetch())
            {
                //echo $pTaxonID . " -> " . $count . "<BR>";
                $recIdMap[$pTaxonID] = $count;
            }
        }
        $stmt->close();

        //-------------------------

        $stmt = $db->stmt_init();
        $sql = "SELECT p." . $tableNameCap . "ID, p.FullName, p.Name, p.RankID FROM " . $tableName . " p WHERE " . $where . " ORDER BY p.Name";
        if ($stmt->prepare($sql))
        {
            if (!$stmt->execute())
            {
                echo "<br>Execute Error: " . $db->error . " | " .  $db->errno . "\n";
                echo "<br>Execute Error: " . $stmt->error . " | " .  $stmt->errno . "\n";
                $stmt->close();
                return -1;
            }

            $rv = $stmt->bind_result($pTaxonID, $pFullName, $pName, $pRankID);
            if (!$rv)
            {
                echo "<br>Bind Error: " . $db->error . " | " .  $db->errno . "\n";
                echo "<br>Bind Error: " . $stmt->error . " | " .  $stmt->errno . "\n";
                $stmt->close();
                return null;
            }

            $fields = array("id", "fullName", "name", "rankID");

            $i       = 0;
            $data    = "\"data\": [";
            while ($stmt->fetch())
            {
                if ($i > 0) $data .= ", ";
                $data .= "{";
                $values = array($pTaxonID, $pFullName, $pName, $pRankID);
                $data  .= buildJSONObj("-", $fields, $values, $stmt);

                $hasKids = 0;
                if (isset($recIdMap[$pTaxonID]))
                {
                    $hasKids = $recIdMap[$pTaxonID] > 0;
                }
                $coCount = 0;
                if (isset($recIdToCOCountMap[$pTaxonID]))
                {
                    $coCount = $recIdToCOCountMap[$pTaxonID];
                }
                //$data .= ", \"hasKids\": " . ($hasKids > 0 ? "1" : "0") . "}";
                $data .= ", \"hasKids\": " . ($hasKids > 0 ? "1" : "0");
                $data .= ", \"cos\": " . $coCount . "}";
                $i++;
            }
            $data .= "]";
            $stmt->close();
            return "{ \"status\" : \"OK\", \"id\" : \"$recId\", $data }";
        } else
        {
            echo "<br>Error: " . $db->error . " | " .  $db->errno . "\n";
            echo "<br>Error: " . $stmt->error . " | " .  $stmt->errno . "\n";
        }
        $stmt->close();
        return "{ \"status\" : \"ERROR\", \"id\" : \"$recId\", $data }";
    }

    function getTreeNodeJSON($db, $ceId)
    {
        $treeType = $_GET['treetype'];
        $recId    = $_GET['id'];

        if ($treeType == 3)
        {
            return retrieveTaxonTreeNodeJSON($db, $treeType, $recId, 0);
        }
        return retrieveTreeNodeJSON($db, $treeType, $recId, 0);
    }

    function getRootTreeNodeJSON($db, $GT)
    {
        $treeType = $_GET['treetype'];

        $recId = getFieldFromSQL($db, "SELECT TaxonID FROM taxon WHERE ParentID IS NULL");
        if ($treeType == 3)
        {
            return retrieveTaxonTreeNodeJSON($db, $treeType, $recId, 0);
        }
        return retrieveTreeNodeJSON($db, $treeType, $recId, 0);
    }

    function retrieveRankMap($db, $GT)
    {
        global $tableName;
        global $tableNameCap;

        $treeType = $_GET['treetype'];
        $tdId     = $_GET['treedefid'];

        setupTreeInfo($treeType);

        $rankId = null;
        $title  = null;

        // Get Inst Name
        $stmt = $db->stmt_init();
        $sql = "SELECT RankID, Name FROM " . $tableName . "treedefitem WHERE " . $tableNameCap . "TreeDefID = " . $tdId . " ORDER BY RankID";
        if ($stmt->prepare($sql)) {
            if (!$stmt->execute())
            {
                $stmt->close();
                return -1;
            }
            $rv = $stmt->bind_result($rankId, $title);
            if (!$rv)
            {
                $stmt->close();
                return null;
            }

            $i = 0;
            $data = "\"data\" : [";
            while ($stmt->fetch())
            {
                if ($i > 0) $data .= ", ";
                $fields = array("rankId", "title");
                $values = array($rankId, $title);
                $data  .= buildJSONObj("", $fields, $values, $stmt);
                $i++;
            }
            $data .= "]";
            $stmt->close();
            return "{ \"status\" : \"OK\", \"id\" : \"$tdId\", $data }";
        } else
        {
            echo "<br>Error: " . $db->error . " | " .  $db->errno . "\n";
            echo "<br>Error: " . $stmt->error . " | " .  $stmt->errno . "\n";
        }
        $stmt->close();
       return "{ \"status\" : \"ERROR\", \"id\" : \"$tdId\", $data }";
    }



    //----------------------------------------------
    function pingJSON($_PST)
    {
        $pingId = $_PST['ping_id'];
        return "{ \"status\" : \"OK\", \"pingid\" : $pingId }";
    }

    //----------------------------------------------
    function buildJSONObj($key, $fields, $values, $stmt)
    {
        $meta = $stmt->result_metadata();
        $fieldTypes = array();
        while ($field = $meta->fetch_field()) {
            $fieldTypes[] = $field->type;
            //echo $field->name . " - " . $field->type . "<BR>";
        }
        $out = "";
        if ($key != "-")
        {
            if ($key != "")
            {
                $out = " \"$key\" : { ";
            } else
            {
                $out = "{";
            }
        }
        $i = 0;
        foreach ($fields as $field) {
            if ($i > 0) $out .= ", ";
            $out .= "\"$field\": ";
            $out .= $fieldTypes[$i] != 253 && is_numeric($values[$i]) && $field != "catNum" ? $values[$i] : ("\"".  $values[$i] . "\"");
            $i++;
        }

        if ($key != "-") $out .= "}";
        return $out;
    }

    //----------------------------------------------
    function makeJSONDSList($items, $doAddKey, $doJustPair)
    {
        if ($doAddKey) $out   = " \"datasets\" : [";
        $i = 0;
        foreach ($items as $row) {
            if ($i > 0) $out .= ", ";
            $out .= "{";
            $out .= "\"id\" : \"" .       $row[0] . "\",";
            $out .= "\"dsname\" : \"" .   $row[1] . "\",";
            if (!$doJustPair)
            {
                $out .= "\"dirname\" : \"" .  $row[2] . "\",";
                $out .= "\"inst\" : \"" .     $row[3] . "\",";
                $out .= "\"div\" : \"" .      $row[4] . "\",";
                $out .= "\"dsp\" : \"" .      $row[5] . "\",";
                $out .= "\"col\" : \"" .      $row[6] . "\",";
                $out .= "\"isglob\" : \"" .   $row[7] . "\",";
                $out .= "\"icon\" : \"" .     $row[8] . "\",";
                $out .= "\"curator\" : \"" .  $row[9] . "\",";
                $out .= "\"collguid\" : \"" . $row[10] . "\"";
            }
            $out .= "}";
            $i++;
        }
        if ($doAddKey) $out .= "]";
        return $out;
    }
?>
