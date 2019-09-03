<?php
        /*==================================================
                Developed by Drew Wallace
          ==================================================
        */
        include ("/etc/myauth.php");

        $connection = mysql_connect($mysql_hst, $mysql_usr, $mysql_pwd);
        if (!$connection) {
                die ("Couldn't connect" . mysql_error());
        }

        $db_select = mysql_select_db("stats");
        if (!$db_select) {
          die ("Couldn't 'select_db' " . mysql_error());
        }
		$query2 = "SELECT distinct ti.Value as 'spversion'
                           FROM trackitem ti
			   where ti.name = 'app_version'
			   and ti.value like '6%'
			   order by ti.value desc;";
		$info2 = mysql_query($query2) or die(mysql_error());
		while($results2 = mysql_fetch_array($info2)) {
				$spversions[] = $results2[0];
		}
?>
<html>
<head>
<script src="//code.jquery.com/jquery-2.1.0.js"></script>
<script src="//code.jquery.com/ui/1.11.2/jquery-ui.js"></script>
<link rel="stylesheet" href="//code.jquery.com/ui/1.11.2/themes/smoothness/jquery-ui.css">
<script>
function changeAddr(name, page) {
	var date1 = $('#datepicker1').val();
    var date2 = $('#datepicker2').val();
    var version1 = document.getElementById('versions1').selectedIndex;
    var version2 = document.getElementById('versions2').selectedIndex;
    var isa = document.getElementById('isa').selectedIndex;
	
	if(page == 'inst') {
		history.pushState({ state: "Colls",
							colls: name,
							d1: date1,
							d2: date2,
							v1: version1,
							v2: version2,
							i: isa}, "", "user_stats_new.php?state=Colls");
		instQuery(name);
	} else if(page == 'trackID') {
		history.pushState({ state: "Entry",
							tid: name,
							d1: date1,
							d2: date2,
							v1: version1,
							v2: version2,
							i: isa}, "", "user_stats_new.php?state=Entry");
		idQuery(name);
	}
}
function query() {
	document.getElementById('loadingImg').style.display = 'block';
	document.getElementById("Insts").innerHTML = "";
    var date1 = $('#datepicker1').val();
    var date2 = $('#datepicker2').val();
    var version1 = $('#versions1').val();
    var version2 = $('#versions2').val();
    var isa = $('#isa').val();

        if (window.XMLHttpRequest) {
            // code for IE7+, Firefox, Chrome, Opera, Safari
            xmlhttp = new XMLHttpRequest();
        } else {
            // code for IE6, IE5
            xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        }
        xmlhttp.onreadystatechange = function() {
            if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
				var response = xmlhttp.responseText;
				document.getElementById('loadingImg').style.display = 'none';
                document.getElementById("Insts").innerHTML = response;
				console.log(document.getElementById("query").value)
            }
        }
        xmlhttp.open("GET","getInsts.php?date1="+date1+"&date2="+date2+"&version1="+version1+"&version2="+version2+"&isa="+isa,true);
	xmlhttp.overrideMimeType('text/xml; charset=iso-8859-1');
        xmlhttp.send();
}
function instQuery(inst) {
	document.getElementById('loadingImg').style.display = 'block';
	document.getElementById("Insts").innerHTML = "";
    var date1 = $('#datepicker1').val();
    var date2 = $('#datepicker2').val();
    var version1 = $('#versions1').val();
    var version2 = $('#versions2').val();
    var isa = $('#isa').val();

        if (window.XMLHttpRequest) {
            // code for IE7+, Firefox, Chrome, Opera, Safari
            xmlhttp = new XMLHttpRequest();
        } else {
            // code for IE6, IE5
            xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        }
        xmlhttp.onreadystatechange = function() {
            if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                var response = xmlhttp.responseText;
				document.getElementById('loadingImg').style.display = 'none';
                document.getElementById("Insts").innerHTML = response;
				console.log(document.getElementById("query").value)
            }
        }
        xmlhttp.open("GET","getInsts.php?Institution_name="+inst+"&date1="+date1+"&date2="+date2+"&version1="+version1+"&version2="+version2+"&isa="+isa,true);
        xmlhttp.overrideMimeType('text/xml; charset=iso-8859-1');
        xmlhttp.send();
}
function idQuery(trackID) {
	document.getElementById('loadingImg').style.display = 'block';
	document.getElementById("Insts").innerHTML = "";
    var date1 = $('#datepicker1').val();
    var date2 = $('#datepicker2').val();
    var version1 = $('#versions1').val();
    var version2 = $('#versions2').val();
    var isa = $('#isa').val();

        if (window.XMLHttpRequest) {
            // code for IE7+, Firefox, Chrome, Opera, Safari
            xmlhttp = new XMLHttpRequest();
        } else {
            // code for IE6, IE5
            xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        }
        xmlhttp.onreadystatechange = function() {
            if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                var response = xmlhttp.responseText;
				document.getElementById('loadingImg').style.display = 'none';
                document.getElementById("Insts").innerHTML = response;
				console.log(document.getElementById("query").value)
				var arr = document.getElementById("Insts").getElementsByTagName('script')
				for (var n = 0; n < arr.length; n++)
					eval(arr[n].innerHTML)//run script inside div
            }
        }
        xmlhttp.open("GET","getInsts.php?trackID="+trackID+"&date1="+date1+"&date2="+date2+"&version1="+version1+"&version2="+version2+"&isa="+isa,true);
        xmlhttp.overrideMimeType('text/xml; charset=iso-8859-1');
        xmlhttp.send();
}

$(function() {
	$("#datepicker1").datepicker();
	$("#datepicker2").datepicker();
	$('#box').hide();
	
	$("#submit").on("click", function(events) {
		var date1 = $('#datepicker1').val();
		var date2 = $('#datepicker2').val();
		var version1 = document.getElementById('versions1').selectedIndex;
		var version2 = document.getElementById('versions2').selectedIndex;
		var isa = document.getElementById('isa').selectedIndex;
		
		history.pushState({ state: "Insts",
							d1: date1,
							d2: date2,
							v1: version1,
							v2: version2,
							i: isa}, "", "user_stats_new.php?state=Insts");
   		events.preventDefault();
		$('#box').show();
		$('#box').val("");
   		query();
	});
	
	document.onclick = function (e) {
	  e = e ||  window.event;
	  var element = e.target || e.srcElement;
	  if (element.tagName == 'A' && element.id != 'ipaddress') {
		$('#box').val("");
		$('#box').hide();
		return false; // prevent default action and stop event propagation
	  }
	};
	
	if(history.state) {
		if(history.state.state == 'Insts') {
			$('#box').show();
			query();
		}else if(history.state.state == 'Colls') {
			instQuery(history.state.colls);
		} else if(history.state.state == 'Entry') {
			idQuery(history.state.tid);
		}
	}
	
	window.onpopstate = function(event) {
		var home = $('#Insts');
		var date1 = $('#datepicker1');
		var date2 = $('#datepicker2');
		var version1 = $('#versions1');
		var version2 = $('#versions2');
		var isa = $('#isa');
		var InstFilter = $('#box');
		
		if(event.state == null) {
			home.html("");
			date1.val("");
			date2.val("");
			date2.val("");
			version1.prop('selectedIndex', 0);
			version2.prop('selectedIndex', 0);
			isa.prop('selectedIndex', 0);
			InstFilter.hide();
			InstFilter.val("");
		} else if(event.state.state == 'Insts') {
			date1.val(event.state.d1);
			date2.val(event.state.d2);
			version1.prop('selectedIndex', event.state.v1);
			version2.prop('selectedIndex', event.state.v2);
			isa.prop('selectedIndex', event.state.i);
			InstFilter.show();
			InstFilter.val("");
			query();
		} else if(event.state.state == 'Colls') {
			date1.val(event.state.d1);
			date2.val(event.state.d2);
			version1.prop('selectedIndex', event.state.v1);
			version2.prop('selectedIndex', event.state.v2);
			isa.prop('selectedIndex', event.state.i);
			InstFilter.hide();
			InstFilter.val("");
			instQuery(event.state.colls);
		} else if(event.state.state == 'Entry') {
			date1.val(event.state.d1);
			date2.val(event.state.d2);
			version1.prop('selectedIndex', event.state.v1);
			version2.prop('selectedIndex', event.state.v2);
			isa.prop('selectedIndex', event.state.i);
			InstFilter.hide();
			InstFilter.val("");
			idQuery(event.state.tid);
		}
	};
	
	$('#box').keyup(function(){
	   var valThis = $(this).val().toLowerCase();
		$('.Inst>li').each(function(){
		 var text = $(this).text().toLowerCase();
			(text.indexOf(valThis) > -1) ? $(this).show() : $(this).hide();            
	   });
	});
});
</script>
</head>
<body>

<form>
Accessed between: <input type="textbox" id="datepicker1"> and <input type="textbox" id="datepicker2"><br>
<br>
Specify versions between:
<select id="versions1">
  <option value="">Select a Specify version:</option>
  <?php foreach($spversions as $value){
  	echo "<option value=\"$value\">$value</option>";
  } ?>
</select>
and
<select id="versions2">
  <option value="">Select a Specify version:</option>
    <?php foreach($spversions as $value){ 
          echo "<option value=\"$value\">$value</option>";
    } ?>
</select><br>
<br>
<select id="isa">
  <option value="both">Either ISA or no ISA</option>
  <option value="ISA">Only ISA</option>
  <option value="not">No ISA</option>
</select><br>
<br>
<input id="submit" type="submit" value="Search">
</form>
<input placeholder="Filter Institutions" id="box" type="text" />
<img id="loadingImg" src="loadingImg.gif" style="display: none;">
<div id="Insts"></div>
</body>
</html>
