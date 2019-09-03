<?php
	/*==================================================
		Developed by Drew Wallace
	  ==================================================
	*/
    include ("/etc/myauth.php");

	function getDate1() {
		if(array_key_exists('date1', $_GET)) {
			$date1 = $_GET['date1'];
			if($date1 == '') {
				$date1 = "0000-00-00 00:00:00";
			} else {
				$date1 = date("Y-m-d H:i:s", strtotime($_GET['date1']));
			}
			return "$date1";
		} else {
			return "0000-00-00 00:00:00";
		}
	}

	function getDate2() {
		if(array_key_exists('date2', $_GET)) {
			$date2 = $_GET['date2'];
			if($date2 == '') {
					$date2 = date("Y-m-d H:i:s", time());
			} else {
					$date2 = date("Y-m-d H:i:s", strtotime($_GET['date2']));
			}
			return "$date2";
        } else {
			return date("Y-m-d H:i:s", time()) . "";
		}
	}

	function getVersion1() {
		if(array_key_exists('version1', $_GET)) {
			$version1 = $_GET['version1'];
			if($version1 == '') {
				$version1 = "0.0.00";
			} else {
				$version1 = $_GET['version1'];
			}
			return "$version1";
		} else {
			return "0.0.00";
		}
	}

	function getVersion2() {
		if(array_key_exists('version2', $_GET)) {
			$version2 = $_GET['version2'];
			if($version2 == '') {
				$version2 = "9.9.99";
			} else {
				$version2 = $_GET['version2'];
			}
			return "$version2";
		} else {
			return "9.9.99";
		}
	}

	$connection = mysql_connect($mysql_hst, $mysql_usr, $mysql_pwd);
	if (!$connection) {
		die ("Couldn't connect" . mysql_error());
	}

	$db_select = mysql_select_db("stats");
	if (!$db_select) {
	  die ("Couldn't 'select_db' " . mysql_error());
	}
	if(!array_key_exists('Institution_name', $_GET) and !array_key_exists('trackID', $_GET)) {
		$isa = $_GET['isa'];
		echo "<h2>Select an Institution:</h2>";
		if($isa == 'both') {
			$query = "SELECT ti2.value as 'Inst name', ti.Value as 'Disc name', ti3.value as 'Col name', SUBSTRING_INDEX(group_concat(ti.trackid ORDER BY timestampmodified desc), ',', 1) as 'tid', MAX(t.TimestampModified) as 'time'
					FROM trackitem ti, trackitem ti2, trackitem ti3, trackitem ti5, track t, (SELECT MAX(ti.TrackID) as 'maxtid', count(*)
							FROM trackitem ti, track t, trackitem ti2, trackitem ti3
							WHERE t.TrackID = ti.TrackID
							and ti.trackid = ti2.TrackID
							and ti.trackid = ti3.TrackID
							AND NOT ((t.IP <= '129.237.201.999' and t.IP >= '129.237.201.0')
								or (t.IP <= '129.237.229.999' and t.IP >= '129.237.229.0'))
							and t.timestampmodified >= '".getDate1()."'
							and t.timestampmodified <= '".getDate2()."'
							and ti3.name = 'app_version'
							and ti3.value >= '".getVersion1()."'
							and ti3.value <= '".getVersion2()."'
							and ti.name = 'user_name'
							and ti.value not in ('rods', 'tlammer')
							group by ti2.trackid) ti6
					WHERE t.TrackID = ti.TrackID
					and ti2.trackid = ti.trackid
					and ti3.trackid = ti.trackid
					and ti5.trackid = ti.trackid
					and ti6.maxtid = ti.trackid
					and ti.name = 'Discipline_name'
					and ti2.name = 'Institution_name'
					and ti3.name = 'Collection_name'
					and ti5.name = 'Collection_number'
					group by ti5.value
					order by MAX(TimestampModified) desc;";
		} else if($isa == 'ISA') {
			$query = "SELECT ti2.value as 'Inst name', ti.Value as 'Disc name', ti3.value as 'Col name', ti4.value as 'ISA', SUBSTRING_INDEX(group_concat(ti.trackid ORDER BY timestampmodified desc), ',', 1) as 'tid', MAX(t.TimestampModified) as 'time'
					FROM trackitem ti, trackitem ti2, trackitem ti3, trackitem ti4, track t, (SELECT MAX(ti.TrackID) as 'maxtid'
						FROM trackitem ti, trackitem ti2, trackitem ti3, track t
						WHERE t.TrackID = ti.TrackID
						and ti2.trackid = ti.trackid
						and ti3.trackid = ti.trackid
						and ti.name = 'ISA_number'
						AND NOT ((t.IP <= '129.237.201.999' and t.IP >= '129.237.201.0')
							or (t.IP <= '129.237.229.999' and t.IP >= '129.237.229.0'))
						and t.timestampmodified >= '".getDate1()."'
						and t.timestampmodified <= '".getDate2()."'
						and ti.value != ''
						and ti2.name = 'app_version'
						and ti2.value >= '".getVersion1()."'
						and ti2.value <= '".getVersion2()."'
						and ti3.name = 'user_name'
						and ti3.value not in ('rods', 'tlammer')
						group by ti.value) ti6
					WHERE t.TrackID = ti.TrackID
					and ti2.trackid = ti.trackid
					and ti3.trackid = ti.trackid
					and ti4.trackid = ti.trackid
					and ti6.maxtid = ti.trackid
					and ti.name = 'Discipline_name'
					and ti2.name = 'Institution_name'
					and ti3.name = 'Collection_name'
					and ti4.name = 'ISA_number'
					and ti4.value != ''
					group by ti4.value
					order by MAX(TimestampModified) desc;";
		} else if($isa == 'not') {
			$query = "SELECT ti2.value as 'Inst Name', ti.Value as 'Disc name', ti3.value as 'Col name', SUBSTRING_INDEX(group_concat(ti.trackid ORDER BY timestampmodified desc), ',', 1) as 'tid', MAX(t.TimestampModified) as 'time'
					FROM trackitem ti, trackitem ti2, trackitem ti3, trackitem ti5, track t, (SELECT MAX(ti.TrackID) as 'maxtid'
							FROM trackitem ti, track t, trackitem ti3, ((select distinct trackid from trackitem where trackid not in (select trackid from trackitem where name = 'ISA_number'))
								union
								(select distinct trackid from trackitem where name = 'ISA_number' and value = '')) ti2
							WHERE t.TrackID = ti.TrackID
							and ti.trackid = ti2.trackid
							and ti.trackid = ti3.trackid
							AND NOT ((t.IP <= '129.237.201.999' and t.IP >= '129.237.201.0')
								or (t.IP <= '129.237.229.999' and t.IP >= '129.237.229.0'))
							and t.timestampmodified >= '".getDate1()."'
							and t.timestampmodified <= '".getDate2()."'
							and ti3.name = 'app_version'
							and ti3.value >= '".getVersion1()."'
							and ti3.value <= '".getVersion2()."'
							and ti.name = 'user_name'
							and ti.value not in ('rods', 'tlammer')
							group by ti2.trackid) ti6
					WHERE t.TrackID = ti.TrackID
					and ti.trackid = ti2.trackid
					and ti2.trackid = ti3.trackid
					and ti3.trackid = ti5.trackid
					and ti5.trackid = ti6.maxtid
					and ti.name = 'Discipline_name'
					and ti2.name = 'Institution_name'
					and ti3.name = 'Collection_name'
					and ti5.name = 'Collection_number'
					group by ti5.value
					order by MAX(TimestampModified) desc;";
		}
		echo "<input id=\"query\" type=\"hidden\" value=\"" . $query . "\">";
		$info = mysql_query($query) or die(mysql_error());
		$numColls = mysql_num_rows($info);
		echo "<h5># of Collections: $numColls</h5>";
		echo "<ul class=\"Inst\">";
		if($numColls != 0){
			while($results = mysql_fetch_array($info)) {
				$instName = $results[0];
				if(!$instName) {
					$instName = "-null-";
				} else if($instName == '') {
					$instName = "-blank-";
				} else if($instName == ' ') {
					$instName = "-space-";
				}
				$dataArray[$instName] .= "<ul>";
				$disName = $results[1];
				$collName = $results[2];
				if(array_key_exists(5, $results)) {
					$ISAnum = $results[3];
					$trackID = $results[4];
					$date = $results[5];
					$dataArray[$instName] .= "<li><a href=\"#trackID\" onclick=\"changeAddr($trackID, 'trackID')\">[$ISAnum] $collName ($disName): $date</a></li>";
				} else {
					$trackID = $results[3];
					$date = $results[4];
					$dataArray[$instName] .= "<li><a href=\"#trackID\" onclick=\"changeAddr($trackID, 'trackID')\">$collName ($disName): $date</a></li>";
				}
				$dataArray[$instName] .= "</ul>";
			}
			ksort($dataArray);
			foreach($dataArray as $key => $value) {
				echo "<li><a href=\"#inst\" onclick=\"changeAddr('".str_replace("&", "%26", $key)."', 'inst')\">$key</a>$value</li>";
			}
		}
		echo "</ul>";
	} else if(array_key_exists('Institution_name', $_GET)) {
		$isa = $_GET['isa'];
		$instName = $_GET['Institution_name'];
		echo "<h2>Select a Collection</h2><h4>Note:<br>Each entry is in the form of: [ISA number] Collection name (Discipline name): Date Last accessed.<br>The entries are sorted by most recent collection accessed.</h4>";
		$instName = str_replace("%20", "", $_GET["Institution_name"]);
		$instName = str_replace("\\", "", $instName);
		$instName = str_replace("'s", "\\'s", $instName);
		if($instName == "-null-") {
			$instName = "is null";
		} else if($instName == "-blank-") {
			$instName = "= ''";
		} else if($instName == "-space-") {
			$instName = "= ' '";
		} else {
			$instName = "= '$instName'";
		}
		if($isa == 'both') {
			$query2 = "SELECT ti.Value as 'Disc name', ti3.value as 'Col name', SUBSTRING_INDEX(group_concat(ti.trackid ORDER BY timestampmodified desc), ',', 1) as 'tid', MAX(t.TimestampModified) as 'time'
					FROM trackitem ti, trackitem ti2, trackitem ti3, trackitem ti5, track t, (SELECT MAX(ti.TrackID) as 'maxtid', count(*)
							FROM trackitem ti, track t, trackitem ti2, trackitem ti3
							WHERE t.TrackID = ti.TrackID
							and ti.trackid = ti2.TrackID
							and ti.trackid = ti3.TrackID
							AND NOT ((t.IP <= '129.237.201.999' and t.IP >= '129.237.201.0')
								or (t.IP <= '129.237.229.999' and t.IP >= '129.237.229.0'))
							and t.timestampmodified >= '".getDate1()."'
							and t.timestampmodified <= '".getDate2()."'
							and ti3.name = 'app_version'
							and ti3.value >= '".getVersion1()."'
							and ti3.value <= '".getVersion2()."'
							and ti.name = 'user_name'
							and ti.value not in ('rods', 'tlammer')
							group by ti2.trackid) ti6
					WHERE t.TrackID = ti.TrackID
					and ti2.trackid = ti.trackid
					and ti3.trackid = ti.trackid
					and ti5.trackid = ti.trackid
					and ti6.maxtid = ti.trackid
					and ti.name = 'Discipline_name'
					and ti2.name = 'Institution_name'
					and ti3.name = 'Collection_name'
					and ti5.name = 'Collection_number'
					and ti2.value $instName
					group by ti5.value
					order by MAX(TimestampModified) desc;";
		} else if($isa == 'ISA') {
			$query2 = "SELECT ti.Value as 'Disc name', ti3.value as 'Col name', ti4.value as 'ISA', SUBSTRING_INDEX(group_concat(ti.trackid ORDER BY timestampmodified desc), ',', 1) as 'tid', MAX(t.TimestampModified) as 'time'
					FROM trackitem ti, trackitem ti2, trackitem ti3, trackitem ti4, track t, (SELECT MAX(ti.TrackID) as 'maxtid'
						FROM trackitem ti, trackitem ti2, trackitem ti3, track t
						WHERE t.TrackID = ti.TrackID
						and ti2.trackid = ti.trackid
						and ti3.trackid = ti.trackid
						and ti.name = 'ISA_number'
						AND NOT ((t.IP <= '129.237.201.999' and t.IP >= '129.237.201.0')
							or (t.IP <= '129.237.229.999' and t.IP >= '129.237.229.0'))
						and t.timestampmodified >= '".getDate1()."'
						and t.timestampmodified <= '".getDate2()."'
						and ti.value != ''
						and ti2.name = 'app_version'
						and ti2.value >= '".getVersion1()."'
						and ti2.value <= '".getVersion2()."'
						and ti3.name = 'user_name'
						and ti3.value not in ('rods', 'tlammer')
						group by ti.value) ti6
					WHERE t.TrackID = ti.TrackID
					and ti2.trackid = ti.trackid
					and ti3.trackid = ti.trackid
					and ti4.trackid = ti.trackid
					and ti6.maxtid = ti.trackid
					and ti.name = 'Discipline_name'
					and ti2.name = 'Institution_name'
					and ti3.name = 'Collection_name'
					and ti4.name = 'ISA_number'
					and ti4.value != ''
					and ti2.value $instName
					group by ti4.value
					order by MAX(TimestampModified) desc;";
		} else if($isa == 'not') {
			$query2 = "SELECT ti.Value as 'Disc name', ti3.value as 'Col name', SUBSTRING_INDEX(group_concat(ti.trackid ORDER BY timestampmodified desc), ',', 1) as 'tid', MAX(t.TimestampModified) as 'time'
					FROM trackitem ti, trackitem ti2, trackitem ti3, trackitem ti5, track t, (SELECT MAX(ti.TrackID) as 'maxtid'
							FROM trackitem ti, track t, trackitem ti3, ((select distinct trackid from trackitem where trackid not in (select trackid from trackitem where name = 'ISA_number'))
								union
								(select distinct trackid from trackitem where name = 'ISA_number' and value = '')) ti2
							WHERE t.TrackID = ti.TrackID
							and ti.trackid = ti2.trackid
							and ti.trackid = ti3.trackid
							AND NOT ((t.IP <= '129.237.201.999' and t.IP >= '129.237.201.0')
								or (t.IP <= '129.237.229.999' and t.IP >= '129.237.229.0'))
							and t.timestampmodified >= '".getDate1()."'
							and t.timestampmodified <= '".getDate2()."'
							and ti3.name = 'app_version'
							and ti3.value >= '".getVersion1()."'
							and ti3.value <= '".getVersion2()."'
							and ti.name = 'user_name'
							and ti.value not in ('rods', 'tlammer')
							group by ti2.trackid) ti6
					WHERE t.TrackID = ti.TrackID
					and ti2.trackid = ti.trackid
					and ti3.trackid = ti.trackid
					and ti5.trackid = ti.trackid
					and ti6.maxtid = ti.trackid
					and ti.name = 'Discipline_name'
					and ti2.name = 'Institution_name'
					and ti3.name = 'Collection_name'
					and ti5.name = 'Collection_number'
					and ti2.value $instName
					group by ti5.value
					order by MAX(TimestampModified) desc;";
		}
		echo "<input id=\"query\" type=\"hidden\" value=\"" . $query2 . "\">";
		$info2 = mysql_query($query2) or die(mysql_error());
		while($results2 = mysql_fetch_array($info2)) {
				$disName = $results2[0];
				$collName = $results2[1];
				if(array_key_exists(4, $results2)) {
					$ISAnum = $results2[2];
					$trackID = $results2[3];
					$date = $results2[4];
					$collections[$date.$trackID] = "<a href=\"#trackID\" onclick=\"changeAddr($trackID, 'trackID')\">[$ISAnum] $collName ($disName): $date</a><br>";
				} else {
					$trackID = $results2[2];
					$date = $results2[3];
					$collections[$date.$trackID] = "<a href=\"#trackID\" onclick=\"changeAddr($trackID, 'trackID')\">$collName ($disName): $date</a><br>";
				}
		}
		krsort($collections);
		foreach($collections as $value)
		{
				echo $value . "\n";
		}
	} else if(array_key_exists('trackID', $_GET)) {
		$usageStats = array();
			$query4 = "SELECT DISTINCT ti.name
					FROM trackitem ti
					LEFT JOIN
					((select distinct name from trackitem where name IN('id', 'os_name', 'os_version', 'java_version', 'java_vendor', 'app_version', 'user_name', 'specifyuser', 'ip', 'tester'))
						union
						(select distinct name from trackitem where name like 'num%' or name = 'Collection_estsize' or name like 'audit_%' or name like 'catby%')
						union
						(select distinct name from trackitem where (name like '%name' and name not in ('os_name', 'user_name')) or name like '%number' or name like '%website' or name like '%portal' or name like '%guid' or name like '%email'))
					ti2
					ON ti.name = ti2.name
					WHERE ti2.name IS NULL
					order by name asc;";
			$info4 = mysql_query($query4) or die(mysql_error());
			while(($row =  mysql_fetch_array($info4))) {
				$usageStats[] = $row[0];
			}
			$muInfo = array();
			$query4 = "select distinct name from trackitem where name IN('id', 'os_name', 'os_version', 'java_version', 'java_vendor', 'app_version', 'user_name', 'specifyuser', 'ip', 'tester');";
			$info4 = mysql_query($query4) or die(mysql_error());
			while(($row =  mysql_fetch_array($info4))) {
				$muInfo[] = $row[0];
			}
			$dbStats = array();
			$query4 = "select distinct name from trackitem where name like 'num%' or name = 'Collection_estsize' or name like 'audit_%' or name like 'catby%';";
			$info4 = mysql_query($query4) or die(mysql_error());
			while(($row =  mysql_fetch_array($info4))) {
				$dbStats[] = $row[0];
			}
			$dbInfo = array();
			$query4 = "select distinct name from trackitem where (name like '%name' and name not in ('os_name', 'user_name')) or name like '%number' or name like '%website' or name like '%portal' or name like '%guid' or name like '%email';";
			$info4 = mysql_query($query4) or die(mysql_error());
			while(($row =  mysql_fetch_array($info4))) {
				$dbInfo[] = $row[0];
			}
			$trackID = $_GET["trackID"];
			$query4 = "SELECT t.TimestampModified, t.IP FROM track t where t.TrackID = $trackID;";
			$info4 = mysql_query($query4) or die(mysql_error());
			$results4 = mysql_fetch_array($info4);
			$date = $results4[0];
			$IP = $results4[1];
			$date = "Date Last Accessed: ".$date."<br>";

			$query5 = "SELECT Name, CountAmt, Value FROM trackitem t where trackid = $trackID;";
			echo "<input id=\"query\" type=\"hidden\" value=\"" . $query4 . "\n" . $query5 . "\">";
			$info5 = mysql_query($query5) or die(mysql_error());
			while($results5 = mysql_fetch_array($info5))
			{
				$name = $results5['Name'];
				$countAmt = $results5['CountAmt'];
				$value = $results5['Value'];
				$dataArray[$name] = ($countAmt == null ? $value : $countAmt);
			}
			echo "<pre>";
			echo "</pre><br>";
			echo "<h1 align=\"center\">".$dataArray['Institution_name']."</h1><h4 align=\"center\">$date</h4>";
			if($IP != "") {
				echo "<p align=\"center\">IP address: <a id=\"ipaddress\" href=\"http://$IP.ipaddress.com\">$IP</a></p>";
				echo "<p id='org' align=\"center\">";
				?>
				<script type="text/javascript">
				$.ajax({
				  type:     "GET",
				  url:      "getURL.php?ip=<?php echo $IP; ?>",
				  dataType: "text",
				  success: function(data){
					var orgArr = /Organization.*?<\/td>/g.exec(data);
					if(orgArr) {
						var org = /d>.*(?=<)/g.exec(orgArr[0])[0].substr(2);
						$('#org').text('Possible Institution: ' + $('<div>').html(org).text());
					}
				  }
				});
				</script>

				<?php
				echo "</p>";
			}
			echo "<table align=\"center\" border=\"1\">\n<tr>\n<th>Database Info</th>\n<th>Database Stats</th>\n</tr>\n<tr>\n<td style=\"text-align:left;vertical-align:top\">";
			foreach($dataArray as $key => $value) {
				foreach($dbInfo as $value2) {
					if($key == $value2) {
						echo $key.": ".$value."<br>\n";
					}
				}
			}
			echo "</td>\n<td style=\"text-align:left;vertical-align:top\">";
			foreach($dataArray as $key => $value) {
				foreach($dbStats as $value2) {
					if($key == $value2) {
						echo $key.": ".$value."<br>\n";
					}
				}
			}
			echo "</td>\n</tr>\n<tr>\n<th>Machine/User Info</th>\n<th>Usage Stats</th>\n</tr>\n<tr>\n<td style=\"text-align:left;vertical-align:top\">";
			foreach($dataArray as $key => $value) {
				foreach($muInfo as $value2) {
					if($key == $value2) {
						echo $key.": ".$value."<br>\n";
					}
				}
			}
			echo "</td>\n<td style=\"text-align:left;vertical-align:top\">";
			foreach($dataArray as $key => $value) {
				foreach($usageStats as $value2) {
					if($key == $value2) {
						echo $key.": ".$value."<br>\n";
					}
				}
			}
			echo "</td>\n</tr>\n</table>";
	}
?>
