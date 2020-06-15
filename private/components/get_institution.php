<?php

if(count($_GET)==0)
	exit;

if($_GET['no_head']=='true')
	define('NO_HEAD',TRUE);

const DATABASE = 'stats';
require_once('header.php');


function getFormattedDate(
	$date,
	$default
){

	if(!array_key_exists($date, $_GET) || $_GET[$date] == '')
		return "$default";
	else
		return date('Y-m-d H:i:s', strtotime($_GET[$date]));

}

$date_1 = getFormattedDate('date1', '0000-00-00 00:00:00');
$date_2 = getFormattedDate('date2', date("Y-m-d H:i:s", time()));


function getVersion(
	$version,
	$default
){

	if(!array_key_exists($version, $_GET) || $_GET[$version] == '')
		return "$default";
	else
		return strval($_GET[$version]);
}

$version_1 = getVersion('version1', '6.0.01');
$version_2 = getVersion('version2', '6.9.99');


$hide_invalid_institutions_query = "
	AND `ti2`.`value` != ''
	AND `ti2`.`value` != '\n'
	AND `ti2`.`value` != ' '
	AND `ti2`.`value` != '.'
	AND `ti2`.`value` != '?'
	AND `ti2`.`value` != '-'
	AND `ti2`.`value` IS NOT NULL
";


if(!array_key_exists('Institution_name', $_GET) && !array_key_exists('trackID', $_GET)){
	$isa = $_GET['isa']; ?>

	<h2>Select an Institution:</h2><?php

	if($isa == 'both')
		$query = "
			SELECT `ti2`.`value`                AS 'Inst name', 
			       `ti`.`value`                 AS 'Disc name', 
			       `ti3`.`value`                AS 'Col name', 
			       SUBSTRING_INDEX(GROUP_CONCAT(`ti`.`trackid` ORDER BY `timestampmodified` DESC), 
			       ',', 1 
			       )                        AS 'tid', 
			       MAX(`t`.`timestampmodified`) AS 'time' 
			FROM   `trackitem` `ti`, 
			       `trackitem` `ti2`, 
			       `trackitem` `ti3`, 
			       `trackitem` `ti5`, 
			       `track` `t`, 
			       (SELECT MAX(`ti`.`trackid`) AS 'maxtid', 
			               COUNT(*) 
			        FROM   `trackitem` `ti`, 
			               `track` `t`, 
			               `trackitem` `ti2`, 
			               `trackitem` `ti3` 
			        WHERE  `t`.`trackid` = `ti`.`trackid` 
			               AND `ti`.`trackid` = `ti2`.`trackid` 
			               AND `ti`.`trackid` = `ti3`.`trackid` 
			               AND NOT ( ( `t`.`ip` <= '129.237.201.999' 
			                           AND `t`.`ip` >= '129.237.201.0' ) 
			                          OR ( `t`.`ip` <= '129.237.229.999' 
			                               AND `t`.`ip` >= '129.237.229.0' ) ) 
			               AND `t`.`timestampmodified` >= '" . $date_1 . "' 
			               AND `t`.`timestampmodified` <= '" . $date_2 . "' 
			               AND `ti3`.`name` = 'app_version' 
			               AND `ti3`.`value` >= '" . $version_1 . "' 
			               AND `ti3`.`value` <= '" . $version_2 . "' 
			               AND `ti`.`name` = 'user_name' 
			               AND `ti`.`value` NOT IN ( 'rods', 'tlammer' ) 
			        GROUP  BY `ti2`.`trackid`) `ti6` 
			WHERE  `t`.`trackid` = `ti`.`trackid` 
			       AND `ti2`.`trackid` = `ti`.`trackid` 
			       AND `ti3`.`trackid` = `ti`.`trackid` 
			       AND `ti5`.`trackid` = `ti`.`trackid` 
			       AND `ti6`.`maxtid` = `ti`.`trackid` 
			       AND `ti`.`name` = 'Discipline_name' 
			       AND `ti2`.`name` = 'Institution_name' 
			       AND `ti3`.`name` = 'Collection_name' 
			       AND `ti5`.`name` = 'Collection_number' 
			       ".$hide_invalid_institutions_query."
			GROUP  BY `ti5`.`value` 
			ORDER  BY MAX(`timestampmodified`) DESC";

	elseif($isa == 'ISA')
		$query = "
			SELECT `ti2`.`value`                AS 'Inst name', 
			       `ti`.`value`                 AS 'Disc name', 
			       `ti3`.`value`                AS 'Col name', 
			       `ti4`.`value`                AS 'ISA', 
			       SUBSTRING_INDEX(GROUP_CONCAT(`ti`.`trackid` ORDER BY `timestampmodified` DESC), 
			       ',', 1 
			       )                        AS 'tid', 
			       MAX(`t`.`timestampmodified`) AS 'time' 
			FROM   `trackitem` `ti`, 
			       `trackitem` `ti2`, 
			       `trackitem` `ti3`, 
			       `trackitem` `ti4`, 
			       `track` `t`, 
			       (SELECT MAX(`ti`.`trackid`) AS 'maxtid' 
			        FROM   `trackitem` `ti`, 
			               `trackitem` `ti2`, 
			               `trackitem` `ti3`, 
			               `track` `t` 
			        WHERE  `t`.`trackid` = `ti`.`trackid` 
			               AND `ti2`.`trackid` = `ti`.`trackid`
			               AND `ti3`.`trackid` = `ti`.`trackid` 
			               AND `ti`.`name` = 'ISA_number' 
			               AND NOT ( ( `t`.`ip` <= '129.237.201.999' 
			                           AND `t`.`ip` >= '129.237.201.0' ) 
			                          OR ( `t`.`ip` <= '129.237.229.999' 
			                               AND `t`.`ip` >= '129.237.229.0' ) ) 
			               AND `t`.`timestampmodified` >= '" . $date_1 . "' 
			               AND `t`.`timestampmodified` <= '" . $date_2 . "' 
			               AND `ti`.`value` != '' 
			               AND `ti2`.`name` = 'app_version' 
			               AND `ti3`.`value` >= '" . $version_1 . "' 
			               AND `ti3`.`value` <= '" . $version_2 . "' 
			               AND `ti3`.`name` = 'user_name' 
			               AND `ti3`.`value` NOT IN ( 'rods', 'tlammer' ) 
			        GROUP  BY `ti`.`value`) ti6 
			WHERE  `t`.`trackid` = `ti`.`trackid` 
			       AND `ti2`.`trackid` = `ti`.`trackid` 
			       AND `ti3`.`trackid` = `ti`.`trackid` 
			       AND `ti4`.`trackid` = `ti`.`trackid` 
			       AND `ti6`.`maxtid` = `ti`.`trackid` 
			       AND `ti`.`name` = 'Discipline_name' 
			       AND `ti2`.`name` = 'Institution_name' 
			       AND `ti3`.`name` = 'Collection_name' 
			       AND `ti4`.`name` = 'ISA_number' 
			       AND `ti4`.`value` != ''  
			       ".$hide_invalid_institutions_query."
			GROUP  BY `ti4`.`value` 
			ORDER  BY MAX(`timestampmodified`) DESC";

	elseif($isa == 'not')
		$query = "
			SELECT `ti2`.`value`                AS 'Inst Name', 
			       `ti`.`value`                 AS 'Disc name', 
			       `ti3`.`value`                AS 'Col name', 
			       SUBSTRING_INDEX(GROUP_CONCAT(`ti`.`trackid` ORDER BY `timestampmodified` DESC), 
			       ',', 1 
			       )                        AS 'tid', 
			       MAX(`t`.`timestampmodified`) AS 'time' 
			FROM   `trackitem` `ti`, 
			       `trackitem` `ti2`, 
			       `trackitem` `ti3`, 
			       `trackitem` `ti5`, 
			       `track` `t`, 
			       (SELECT MAX(`ti`.`trackid`) AS 'maxtid' 
			        FROM   `trackitem` `ti`, 
			               `track` `t`, 
			               `trackitem` `ti3`, 
			               ((SELECT DISTINCT `trackid` 
			                 FROM   `trackitem` 
			                 WHERE  `trackid` NOT IN (SELECT `trackid` 
			                                        FROM   `trackitem` 
			                                        WHERE  `name` = 'ISA_number')) 
			                UNION 
			                (SELECT DISTINCT `trackid` 
			                 FROM   `trackitem`
			                 WHERE  `name` = 'ISA_number' 
			                        AND `value` = '')) `ti2` 
			        WHERE  `t`.`trackid` = `ti`.`trackid` 
			               AND `ti`.`trackid` = `ti2`.`trackid` 
			               AND `ti`.`trackid` = `ti3`.`trackid` 
			               AND NOT ( ( `t`.`ip` <= '129.237.201.999' 
			                           AND `t`.`ip` >= '129.237.201.0' ) 
			                          OR ( `t`.`ip` <= '129.237.229.999' 
			                               AND `t`.`ip` >= '129.237.229.0' ) ) 
			               AND `t`.`timestampmodified` >= '" . $date_1 . "' 
			               AND `t`.`timestampmodified` <= '" . $date_2 . "' 
			               AND `ti3`.`name` = 'app_version' 
			               AND `ti3`.`value` >= '" . $version_1 . "' 
			               AND `ti3`.`value` <= '" . $version_2 . "' 
			               AND `ti`.`name` = 'user_name' 
			               AND `ti`.`value` NOT IN ( 'rods', 'tlammer' ) 
			        GROUP  BY `ti2`.`trackid`) `ti6` 
			WHERE  `t`.`trackid` = `ti`.`trackid` 
			       AND `ti`.`trackid` = `ti2`.`trackid` 
			       AND `ti2`.`trackid` = `ti3`.`trackid` 
			       AND `ti3`.`trackid` = `ti5`.`trackid` 
			       AND `ti5`.`trackid` = `ti6`.`maxtid` 
			       AND `ti`.`name` = 'Discipline_name' 
			       AND `ti2`.`name` = 'Institution_name' 
			       AND `ti3`.`name` = 'Collection_name' 
			       AND `ti5`.`name` = 'Collection_number'  
			       ".$hide_invalid_institutions_query."
			GROUP  BY `ti5`.`value` 
			ORDER  BY MAX(`timestampmodified`) DESC";


	echo '<input id="query" type="hidden" value="' . $query . '">';

	$info = $mysqli->query($query);

	if($info === FALSE)
		exit();

	$number_of_columns = $info->num_rows;

	echo '<h5># of Collections: ' . $number_of_columns . '</h5>
	<ul class="Inst">';

	if($number_of_columns == 0)
		exit();

	$data = [];
	while($results = $info->fetch_row()){

		$institution_name = $results[0];

		if(!$institution_name)
			$institution_name = "-null-";
		elseif($institution_name == '')
			$institution_name = "-blank-";
		elseif($institution_name == "\n")
			$institution_name = "-new_line-";
		elseif($institution_name == "-")
			$institution_name = "-dash-";
		elseif($institution_name == ' ')
			$institution_name = "-space-";

		if(!array_key_exists($institution_name,$data))
			$data[$institution_name] = '';

		$data[$institution_name] .= '<ul>';
		$disName = $results[1];
		$collName = $results[2];

		if(array_key_exists(5, $results)){
			$isa_number = $results[3];
			$track_id = $results[4];
			$date = $results[5];
			$data[$institution_name] .= '<li><a href="#trackID" data-track_id="' . $track_id . '">[' . $isa_number . '] ' . $collName . ' (' . $disName . '): ' . $date . '</a></li>';
		}

		else {
			$track_id = $results[3];
			$date = $results[4];
			$data[$institution_name] .= '<li><a href="#trackID" data-track_id="' . $track_id . '">' . $collName . ' (' . $disName . '): ' . $date . '</a></li>';
		}

		$data[$institution_name] .= '</ul>';

	}

	ksort($data);
	foreach($data as $key => $value)
		echo '<li><a href="#inst" data-track_id="' . str_replace('&', '%26', $key) . '" data-track_target="inst">' . $key . '</a>' . $value . '</li>';


	$info->close();
	echo "</ul>";

}


elseif(array_key_exists('Institution_name', $_GET)) {

	$isa = $_GET['isa'];
	$institution_name = $_GET['Institution_name'];
	echo '<h2>Select a Collection</h2>
		<h4>Note:<br>
		Each entry is in the form of: [ISA number] Collection name (Discipline name): Date Last accessed.<br>
		The entries are sorted by most recent collection accessed.</h4>';

	$institution_name = str_replace('%20', '', $institution_name);
	$institution_name = str_replace("\\", '', $institution_name);
	$institution_name = str_replace("'s", "\\'s", $institution_name);

	if($institution_name == '-null-')
		$institution_name = 'is null';
	elseif($institution_name == '-blank-')
		$institution_name = "= ''";
	elseif($institution_name == '-new-line-')
		$institution_name = "= '\n'";
	elseif($institution_name == '-dash-')
		$institution_name = "= '-'";
	elseif($institution_name == '-space-')
		$institution_name = "= ' '";
	else
		$institution_name = "= '$institution_name'";


	if($isa == 'both')
		$query2 = "
			SELECT   `ti`.`value`                                                                               AS 'Disc name',
			         `ti3`.`value`                                                                              AS 'Col name',
			         SUBSTRING_INDEX( GROUP_CONCAT( `ti`.`trackid` ORDER BY `timestampmodified` DESC ), ',', 1) AS 'tid',
			         MAX(`t`.`timestampmodified`)                                                               AS 'time'
			FROM     `trackitem` `ti`, 
			         `trackitem` `ti2`, 
			         `trackitem` `ti3`, 
			         `trackitem` `ti5`, 
			         `track` `t`, 
			         ( 
			                  SELECT   MAX(`ti`.`trackid`) AS 'maxtid', 
			                           COUNT(*) 
			                  FROM     `trackitem` `ti`, 
			                           `track` `t`, 
			                           `trackitem` `ti2`, 
			                           `trackitem` `ti3` 
			                  WHERE    `t`.`trackid` = `ti`.`trackid` 
			                  AND      `ti`.`trackid` = `ti2`.`trackid` 
			                  AND      `ti`.`trackid` = `ti3`.`trackid` 
			                  AND      NOT (( 
			                                             `t`.`ip` <= '129.237.201.999' 
			                                    AND      `t`.`ip` >= '129.237.201.0') 
			                           OR       ( 
			                                             `t`.`ip` <= '129.237.229.999' 
			                                    AND      `t`.`ip` >= '129.237.229.0')) 
			                  AND      `t`.`timestampmodified` >= '" . $date_1 . "' 
			                  AND      `t`.`timestampmodified` <= '" . $date_2 . "' 
			                  AND      `ti3`.`name` = 'app_version' 
			                  AND      `ti3`.`value` >= '" . $version_1 . "' 
			                  AND      `ti3`.`value` <= '" . $version_2 . "' 
			                  AND      `ti`.`name` = 'user_name' 
			                  AND      `ti`.`value` NOT IN ('rods', 
			                                            'tlammer') 
			                  GROUP BY `ti2`.`trackid`) `ti6` 
			WHERE    `t`.`trackid` = `ti`.`trackid` 
			AND      `ti2`.`trackid` = `ti`.`trackid` 
			AND      `ti3`.`trackid` = `ti`.`trackid` 
			AND      `ti5`.`trackid` = `ti`.`trackid` 
			AND      `ti6`.`maxtid` = `ti`.`trackid` 
			AND      `ti`.`name` = 'Discipline_name' 
			AND      `ti2`.`name` = 'Institution_name' 
			AND      `ti3`.`name` = 'Collection_name' 
			AND      `ti5`.`name` = 'Collection_number' 
			AND      `ti2`.`value` $institution_name 
			".$hide_invalid_institutions_query."
			GROUP BY ti5.value 
			ORDER BY max(timestampmodified) DESC";

	elseif($isa == 'ISA')
		$query2 = "
			SELECT   `ti`.`value`                                                                             AS 'Disc name',
			         `ti3`.`value`                                                                            AS 'Col name',
			         `ti4`.`value`                                                                            AS 'ISA',
			         SUBSTRING_INDEX( GROUP_CONCAT(`ti`.`trackid` ORDER BY `timestampmodified` DESC), ',', 1) AS 'tid',
			         MAX(`t`.`timestampmodified`)                                                             AS 'time'
			FROM     `trackitem` `ti`, 
			         `trackitem` `ti2`, 
			         `trackitem` `ti3`, 
			         `trackitem` `ti4`, 
			         `track` `t`, 
			         ( 
			                  SELECT   MAX(`ti`.`trackid`) AS 'maxtid' 
			                  FROM     `trackitem` `ti`, 
			                           `trackitem` `ti2`, 
			                           `trackitem` `ti3`, 
			                           `track` `t` 
			                  WHERE    `t`.`trackid` = `ti`.`trackid` 
			                  AND      `ti2`.`trackid` = `ti`.`trackid` 
			                  AND      `ti3`.`trackid` = `ti`.`trackid` 
			                  AND      `ti`.`name` = 'ISA_number' 
			                  AND      NOT (( 
			                                             `t`.`ip` <= '129.237.201.999' 
			                                    AND      `t`.`ip` >= '129.237.201.0') 
			                           OR       ( 
			                                             `t`.`ip` <= '129.237.229.999' 
			                                    AND      `t`.`ip` >= '129.237.229.0')) 
			                  AND      `t`.`timestampmodified` >= '" . $date_1 . "' 
			                  AND      `t`.`timestampmodified` <= '" . $date_2 . "' 
			                  AND      `ti`.`value` != '' 
			                  AND      `ti2`.`name` = 'app_version' 
			                  AND      `ti2`.`value` >= '" . $version_1 . "' 
			                  AND      `ti2`.`value` <= '" . $version_2 . "' 
			                  AND      `ti3`.`name` = 'user_name' 
			                  AND      `ti3`.`value` NOT IN ('rods', 
			                                             'tlammer') 
			                  GROUP BY `ti`.`value`) `ti6` 
			WHERE    `t`.`trackid` = `ti`.`trackid` 
			AND      `ti2`.`trackid` = `ti`.`trackid` 
			AND      `ti3`.`trackid` = `ti`.`trackid` 
			AND      `ti4`.`trackid` = `ti`.`trackid` 
			AND      `ti6`.`maxtid` = `ti`.`trackid` 
			AND      `ti`.`name` = 'Discipline_name' 
			AND      `ti2`.`name` = 'Institution_name' 
			AND      `ti3`.`name` = 'Collection_name' 
			AND      `ti4`.`name` = 'ISA_number' 
			AND      `ti4`.`value` != '' 
			AND      `ti2`.`value` $institution_name 
			GROUP BY `ti4`.`value` 
			ORDER BY MAX(`timestampmodified`) DESC";

	elseif($isa == 'not')
		$query2 = "
			SELECT   `ti`.`value`                                                                           AS 'Disc name',
			         `ti3`.`value`                                                                          AS 'Col name',
			         SUBSTRING_INDEX( GROUP_CONCAT(`ti`.`trackid` ORDER BY timestampmodified DESC), ',', 1) AS 'tid',
			         MAX(`t`.`timestampmodified`)                                                           AS 'time'
			FROM     `trackitem` `ti`, 
			         `trackitem` `ti2`, 
			         `trackitem` `ti3`, 
			         `trackitem` `ti5`, 
			         `track` `t`, 
			         ( 
			                  SELECT   MAX(`ti`.`trackid`) AS 'maxtid' 
			                  FROM     `trackitem` `ti`, 
			                           `track` `t`, 
			                           `trackitem` `ti3`, 
			                           ( 
			                           ( 
			                                           SELECT DISTINCT `trackid` 
			                                           FROM            `trackitem` 
			                                           WHERE           `trackid` NOT IN 
			                                                           ( 
			                                                                  SELECT `trackid` 
			                                                                  FROM   `trackitem` 
			                                                                  WHERE  `name` = 'ISA_number')) 
			           UNION 
			                    ( 
			                                    SELECT DISTINCT `trackid` 
			                                    FROM            `trackitem` 
			                                    WHERE           `name` = 'ISA_number' 
			                                    AND             `value` = '')) `ti2` 
			                  WHERE    `t`.`trackid` = `ti`.`trackid` 
			                  AND      `ti`.`trackid` = `ti2`.`trackid` 
			                  AND      `ti`.`trackid` = `ti3`.`trackid` 
			                  AND      NOT (( 
			                                             `t`.`ip` <= '129.237.201.999' 
			                                    AND      `t`.`ip` >= '129.237.201.0') 
			                           OR       ( 
			                                             `t`.`ip` <= '129.237.229.999' 
			                                    AND      `t`.`ip` >= '129.237.229.0')) 
			                  AND      `t`.`timestampmodified` >= '" . $date_1 . "' 
			                  AND      `t`.`timestampmodified` <= '" . $date_2 . "' 
			                  AND      `ti3`.`name` = 'app_version' 
			                  AND      `ti3`.`value` >= '" . $version_1 . "' 
			                  AND      `ti3`.`value` <= '" . $version_2 . "' 
			                  AND      `ti`.`name` = 'user_name' 
			                  AND      `ti`.`value` NOT IN ('rods', 'tlammer') 
			                  GROUP BY `ti2`.`trackid`) `ti6` 
			WHERE    `t`.`trackid` = `ti`.`trackid` 
			AND      `ti2`.`trackid` = `ti`.`trackid` 
			AND      `ti3`.`trackid` = `ti`.`trackid` 
			AND      `ti5`.`trackid` = `ti`.`trackid` 
			AND      `ti6`.`maxtid` = `ti`.`trackid` 
			AND      `ti`.`name` = 'Discipline_name' 
			AND      `ti2`.`name` = 'Institution_name' 
			AND      `ti3`.`name` = 'Collection_name' 
			AND      `ti5`.`name` = 'Collection_number' 
			AND      `ti2`.`value` $institution_name 
			GROUP BY `ti5`.`value` 
			ORDER BY MAX(`timestampmodified`) DESC";

	echo '<input id="query" type="hidden" value="' . $query2 . '">';

	$collections = [];


	$info2 = $mysqli->query($query2);

	while($results2 = $info2->fetch_row()){

		$disName = $results2[0];
		$collName = $results2[1];

		if(array_key_exists(4, $results2)){
			$isa_number = $results2[2];
			$track_id = $results2[3];
			$date = $results2[4];
			$collections[$date . $track_id] = '<a href="#trackID" data-track_id="' . $track_id . '">['.$isa_number.'] '.$collName.' ('.$disName.'): '.$date.'</a><br>';
		}

		else {
			$track_id = $results2[2];
			$date = $results2[3];
			$collections[$date . $track_id] = '<a href="#trackID" data-track_id="' . $track_id . '">'.$collName.' ('.$disName.'): '.$date.'</a><br>';
		}

	}

	$info2->close();


	krsort($collections);
	foreach($collections as $value)
		echo $value . "\n";

}


elseif(array_key_exists('trackID', $_GET)) {

	$track_id = $_GET["trackID"];

	$usageStats = [];
	$query4 = "SELECT DISTINCT `ti`.`name`
				FROM `trackitem` `ti`
				LEFT JOIN
				((SELECT DISTINCT `name` FROM `trackitem` WHERE `name` IN('id', 'os_name', 'os_version', 'java_version', 'java_vendor', 'app_version', 'user_name', 'specifyuser', 'ip', 'tester'))
					UNION
					(SELECT DISTINCT `name` FROM `trackitem` WHERE `name` LIKE 'num%' OR `name` = 'Collection_estsize' OR `name` LIKE 'audit_%' OR `name` LIKE 'catby%')
					UNION
					(SELECT DISTINCT `name` FROM `trackitem` WHERE (`name` LIKE '%name' AND `name` NOT IN ('os_name', 'user_name')) OR `name` LIKE '%number' OR `name` LIKE '%website' OR `name` LIKE '%portal' OR `name` LIKE '%guid' OR `name` LIKE '%email'))
				`ti2`
				ON `ti`.`name` = `ti2`.`name`
				WHERE `ti2`.`name` IS NULL
				ORDER BY `name`";

	$info4 = $mysqli->query($query4);
	while(($row = $info4->fetch_row()))
		$usageStats[] = $row[0];
	$info4->close();

	$muInfo = [];
	$query4 = "SELECT DISTINCT `name` FROM `trackitem` WHERE `name` IN('id', 'os_name', 'os_version', 'java_version', 'java_vendor', 'app_version', 'user_name', 'specifyuser', 'ip', 'tester');";
	$info4 = $mysqli->query($query4);
	while(($row = $info4->fetch_row()))
		$muInfo[] = $row[0];
	$info4->close();

	$dbStats = [];
	$query4 = "SELECT DISTINCT `name` FROM `trackitem` WHERE `name` LIKE 'num%' OR `name` = 'Collection_estsize' OR `name` LIKE 'audit_%' OR `name` LIKE 'catby%';";
	$info4 = $mysqli->query($query4);
	while(($row = $info4->fetch_row()))
		$dbStats[] = $row[0];
	$info4->close();

	$dbInfo = [];
	$query4 = "SELECT DISTINCT `name` FROM `trackitem` WHERE (`name` LIKE '%name' AND `name` NOT IN ('os_name', 'user_name')) OR `name` LIKE '%number' OR `name` LIKE '%website' OR `name` LIKE '%portal' OR `name` LIKE '%guid' OR `name` LIKE '%email';";
	$info4 = $mysqli->query($query4);
	while(($row = $info4->fetch_row()))
		$dbInfo[] = $row[0];
	$info4->close();

	$query4 = 'SELECT `t`.`TimestampModified`, `t`.`IP` FROM `track` `t` WHERE `t`.`TrackID` = '.$track_id;
	var_dump('SELECT `t`.`TimestampModified`, `t`.`IP` FROM `track` `t` WHERE `t`.`TrackID` = '.$track_id);
	$info4 = $mysqli->query($query4);
	$results4 = $info4->fetch_row();
	$info4->close();

	$date = $results4[0];
	$IP = $results4[1];
	$date = 'Date Last Accessed: ' . $date . '<br>';

	$query5 = 'SELECT `Name`, `CountAmt`, `Value` FROM `trackitem` `t` WHERE `trackid` = '.$track_id;
	echo '<input id="query" type="hidden" value="' . $query4 . '\n' . $query5 . '">';
	$info5 = $mysqli->query($query5);

	$data = [];
	while($results5 = $info5->fetch_assoc()){
		$name = $results5['Name'];
		$countAmt = $results5['CountAmt'];
		$value = $results5['Value'];
		$data[$name] = ($countAmt == NULL
			? $value
			: $countAmt);
	}

	$info5->close(); ?>

	<h1 style="text-align: center"><?= $data['Institution_name']?></h1>
	<h4 style="text-align: center"><?=$date?></h4> <?php

	if($IP != ''){ ?>

		<p style="text-align: center">
			IP address: <a id="ipaddress" href="http://<?=$IP?>.ipaddress.com"><?=$IP?></a>
		</p>
		<p id="org" style="text-align: center"></p>

		<script>
			$.ajax( {
				type : "GET",
				url : "../components/get_url.php?ip=<?php echo $IP; ?>",
				dataType : "text",
				success : function ( data ) {
					const orgArr = /Organization.*?<\/td>/g.exec( data );
					if ( orgArr ) {
						const org = /d>.*(?=<)/g.exec( orgArr[ 0 ] )[ 0 ].substr( 2 );
						$( "#org" ).text( "Possible Institution: " + $( "<div>" ).html( org ).text() );
					}
				},
			} );
			</script> <?php

	} ?>

	<table class="table table-striped">
		<thead>
			<tr>
				<th>Database Info</th>
				<th>Database Stats</th>
			</tr>
		</thead>
		<tbody>

		<tbody>
			<tr>
				<td style="text-align:left;vertical-align:top"> <?php

					foreach($data as $key => $value)
						foreach($dbInfo as $value2)
							if($key == $value2)
								echo $key . ': ' . $value . '<br>'; ?>

				</td>

				<td style="text-align:left;vertical-align:top"> <?php

					foreach($data as $key => $value)
						foreach($dbStats as $value2)
							if($key == $value2)
								echo $key . ': ' . $value . '<br>'; ?>

				</td>
			</tr>
		</tbody>
	</table>


	<table class="table table-striped">
		<thead>
			<tr>
				<th>Machine/User Info</th>
				<th>Usage Stats</th>
			</tr>
		</thead>
		<tbody>
			<tr>

				<td style="text-align:left;vertical-align:top"> <?php

					foreach($data as $key => $value)
						foreach($muInfo as $value2)
							if($key == $value2)
								echo $key . ': ' . $value . '<br>'; ?>

				</td>

				<td style="text-align:left;vertical-align:top">  <?php

					foreach($data as $key => $value)
						foreach($usageStats as $value2)
							if($key == $value2)
								echo $key . ': ' . $value . '<br>'; ?>

				</td>
			</tr>
		</tbody>
	</table> <?php


}


footer();