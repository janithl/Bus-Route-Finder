<?php

require('./dbconn.php');

/******************************************************************************************

	Colombo Bus Route Finder by Janith Leanage (http://janithl.blogspot.com).

	Project started: 27/Jun/11	Code rewritten from scratch: 08/Jul/11

	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.

******************************************************************************************/

// Main Function

$dbconn	= new DBConn();

if(isset($_GET['t']) && isset($_GET['f']))
{
	$pat = "/[^0-9]/";
	
	$to = preg_replace($pat, "", $_GET['t']);
	$from = preg_replace($pat, "", $_GET['f']);

	if($to == $from)
	{
		head("Error!");
		error("You have set the same location as the source and destination.");
		tail();
	}
	elseif(is_numeric($to) && is_numeric($from) && ($to + $from) < 999999)
	{
		$name1 = place($from);			//halt name from
		$name2 = place($to);			//halt name to
		head("I want to go from $name1 to $name2");
		
		$return1 = level1($from, $to);

		if($return1 != true)
		{
			$return2 = level2($from, $to);
			$return3 = level3($from, $to, $return2);
		
			if(($return3 != true) && ($return2 == 9999))
			{
				error("We're extremely sorry, but your destination is unreachable using 3 buses or less. We suggest you take a trishaw or taxi to get to your destination.");
			}
		}
		tail();		
	}
	else
	{
		head("Error");
		error("There is a problem with your input. Please use the dropdown menu to select locations.");
		tail();
	}			
}
else
{
	head("Error");
	error("There is an issue with your query. Make sure that you have entered your starting location and your destination.");
	tail();
}


//////////////////////////////////////////////////
//						//
//		Application Logic		//
//						//
//////////////////////////////////////////////////

// Level 1 - Go from location A to B using one bus

function level1($from, $to)
{
	if(($bus = find1Bus($from, $to)) != false)
	{


		$busid = $bus[0];
		$nstops = $bus[1];

		//Output
		echo '<div id="entry"><h2>Suggested Route - 1 bus, '.$nstops.' halts</h2>';
		display($busid, $from, $to, $nstops);
		echo '</div>';

		return true;
	}
}

// Level 2 - Go from location A to B using two buses and 
// select the bus combination with the minimum number of halts

function level2($from, $to)
{
	if(($bus = find2Bus($from, $to)) != false)
	{
		$busid1 = $bus[0];
		$busid2 = $bus[1];
		$change = $bus[2];
		$nstops1 = $bus[3];
		$nstops2 = $bus[4];

		//Output
		echo '<div id="entry"><h2>Suggested Route - 2 buses, '.($nstops1 + $nstops2).' halts</h2>';
		display($busid1, $from, $change, $nstops1);
		display($busid2, $change, $to, $nstops2);
		echo '</div>';

		return ($nstops1 + $nstops2);
	}
	else 
	{
		return 9999;
	}
}

// Level 3 - Go from location A to B using three buses and 
// select the bus combination with the minimum number of halts

function level3($from, $to, $nstops)
{

	if(($bus = find3Bus($from, $to)) != false)
	{


		$busid1 = $bus[0];
		$busid2 = $bus[1];
		$busid3 = $bus[2];
		$change1 = $bus[3];
		$change2 = $bus[4];
		$nstops1 = $bus[5];
		$nstops2 = $bus[6];
		$nstops3 = $bus[7];

		if(($nstops1 + $nstops2 + $nstops3) < ($nstops - 5))
		{
			//Output

			if($nstops == 9999)
			{
				echo '<div id="entry"><h2>Suggested Route - 3 buses, '.($nstops1 + $nstops2 + $nstops3).' halts</h2>';
			}
			else
			{
				echo '<div id="entry"><h2>Alternative Route - 3 buses, '.($nstops1 + $nstops2 + $nstops3).' halts</h2>';
			}
			display($busid1, $from, $change1, $nstops1);
			display($busid2, $change1, $change2, $nstops2);
			display($busid3, $change2, $to, $nstops3);
			echo '</div>';

			return true;
		}
	}

	return false;

}



//////////////////////////////////////////////////
//						//
//		Database Functions		//
//						//
//////////////////////////////////////////////////
	

// Returns place name when given place id

function place($pid)
{
	global $dbconn;
	
	$res	= $dbconn->query("SELECT name FROM place WHERE pid = :id", array(':id' => $pid));
	$return = false;

	if($res && ($row = $res->fetch()) != false)
	{
		$return = $row[0];
	}

	return $return;
}

// Returns all details on any busID

function busDet($busid)
{
	global $dbconn;
	
	$res	= $dbconn->query("SELECT `busid`, `routeno`, `from`, `to` FROM bus WHERE busid = :id", array(':id' => $busid));
	$return = false;

	if($res && ($row = $res->fetch()) != false)
	{
		$return = $row;
	}

	return $return;
}

// Function to find a bus link from location A to 
// location B (a bus that travels in the correct direction)

// ...using one bus
	
function find1Bus($from, $to)
{

	global $dbconn;

	$sql = <<<SQL
SELECT s1.`bid` as busid, (s2.`stopNo` - s1.`stopNo`) as dist
FROM `stop` AS s1 INNER JOIN `stop` AS s2
ON s1.`bid` = s2.`bid`
WHERE s1.`pid` = :from AND s2.`pid` = :to AND s2.`stopNo` > s1.`stopNo`
ORDER BY dist;
SQL;
	
	$res	= $dbconn->query($sql, array(':from' => $from, ':to' => $to));
	$return = false;

	if($res && ($row = $res->fetch()) != false)
	{
		$return = $row;
	}

	return $return;
}


// ... or two buses

function find2Bus($from, $to)
{
	global $dbconn;

	$sql = <<<SQL
SELECT s1.`bid` as busid1, s3.`bid` as busid2, ch1.`changeid` as changeid, 
(s2.`stopNo` - s1.`stopNo`) as dist1, (s4.`stopNo` - s3.`stopNo`) as dist2
FROM `changeover` AS ch1, `stop` AS s1 INNER JOIN `stop` AS s2
ON s1.`bid` = s2.`bid`
INNER JOIN `stop` AS s3
ON s2.`pid` = s3.`pid`
INNER JOIN `stop` AS s4
ON s3.`bid` = s4.`bid`  
WHERE s1.`pid` = :from AND s2.`pid` = ch1.`changeid` AND s4.`pid` = :to 
AND s2.`stopNo` > s1.`stopNo` AND s4.`stopNo` > s3.`stopNo`
ORDER BY (dist1 + dist2);
SQL;
	
	$res	= $dbconn->query($sql, array(':from' => $from, ':to' => $to));
	$return = false;

	if($res && ($row = $res->fetch()) != false)
	{
		$return = $row;
	}

	return $return;
}


// ... or three buses

function find3Bus($from, $to)
{

	global $dbconn;

	$sql = <<<SQL
SELECT s1.`bid` as busid1, s3.`bid` as busid2, s5.`bid` as busid3, 
ch1.`changeid` as changeid1, ch2.`changeid` as changeid2, 
(s2.`stopNo` - s1.`stopNo`) as dist1, 
(s4.`stopNo` - s3.`stopNo`) as dist2, 
(s6.`stopNo` - s5.`stopNo`) as dist3
FROM `changeover` AS ch1, `changeover` AS ch2, `stop` AS s1 INNER JOIN `stop` AS s2
ON s1.`bid` = s2.`bid`
INNER JOIN `stop` AS s3
ON s2.`pid` = s3.`pid`
INNER JOIN `stop` AS s4
ON s3.`bid` = s4.`bid`
INNER JOIN `stop` AS s5
ON s4.`pid` = s5.`pid`
INNER JOIN `stop` AS s6
ON s5.`bid` = s6.`bid`  
WHERE s1.`pid` = :from AND s2.`pid` = ch1.`changeid` AND s4.`pid` = ch2.`changeid` 
AND s6.`pid` = :to AND s2.`stopNo` > s1.`stopNo` AND s4.`stopNo` > s3.`stopNo` 
AND s6.`stopNo` > s5.`stopNo` ORDER BY (dist1 + dist2 + dist3);
SQL;

	$res	= $dbconn->query($sql, array(':from' => $from, ':to' => $to));
	$return = false;

	if($res && ($row = $res->fetch()) != false)
	{
		$return = $row;
	}

	return $return;
}

// Geolocation using Google Maps

function geolocate($place)
{
	global $dbconn;
	
	$res	= $dbconn->query("SELECT `loc`, `desc` FROM place ".
		"WHERE loc IS NOT NULL AND pid = :id", array(':id' => $place));
	$return = '';

	if($res && ($row = $res->fetch()) != false)
	{
		$url = "http://maps.google.com/maps/api/staticmap?size=320x320&markers=size:mid|color:blue|${row[0]}|&mobile=true&sensor=false";
		$return = "<a class=\"gmap\" title=\"${row[1]}\" href=\"$url\"><img src=\"img/geo.png\" id=\"geo\"/></a>";
	}

	return $return;
}


//////////////////////////////////////////////////
//						//
//		Display Functions		//
//						//
//////////////////////////////////////////////////

// Function to display errors

function error($message)
{

	if(isset($_GET['v']))
	{
		echo <<< OUT
<div>$message</div>
OUT;
	}
	else
	{

		echo <<< OUT
<div id="entry">
<img src='img/404.png'>
<h2>$message</h2>
<br/>
<br/>
</div>

OUT;
	}
}

// Function to display the top of the output html

function head($heading)
{
	if(isset($_GET['v']))
	{
		echo <<< OUT
<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Bus Route Finder Mobile</title>
</head>
<body>
<div><strong>$heading</strong> (<a href="query.php?f=${_GET['t']}&t=${_GET['f']}&v=mobile">Flip Locations</a>)</div>
<br/>
<div>
OUT;
	}
	else
	{

		echo <<< OUT
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>Bus Route Finder</title>
<link href='http://fonts.googleapis.com/css?family=Cabin&v1' rel='stylesheet' type='text/css'>
<link rel="stylesheet" href="style.css" type="text/css" charset="utf-8" /> 
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="img/bus.ico" rel="icon" type="image/vnd.microsoft.icon"/>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js"></script>
<script>
	!window.jQuery && document.write('<script src="jquery/jquery-1.4.3.min.js"><\/script>');
</script>
<script type="text/javascript" src="jquery/fancybox/jquery.fancybox-1.0.0.js"></script>
<link rel="stylesheet" type="text/css" href="jquery/fancybox/fancy.css" media="screen" />
<script type="text/javascript">
$(document).ready(function() {
	$(".gmap").fancybox(); 
});
</script>
</head>
</head>
<body>
<div id="header">
<h1>$heading</h1>
(<a href="query.php?f=${_GET['t']}&t=${_GET['f']}">Flip Locations</a>)
</div>
<div id="cont">
OUT;
	}
}

// Function to display the bottom of the output

function tail()
{
	if(isset($_GET['v']))
	{
		echo <<< OUT
<br/>
<div><a href="mobile.php">Go Back</a></div>
<br/>
<div>Disclaimer: This service is still in the beta stage, so please use it at your own risk.</div>
</body>
</html>
OUT;
	}
	else
	{

		echo <<< OUT
</div>
<div id="footer">
<p><a href="index.php"><button type="button">Go Back</button></a></p>
<p>Disclaimer: This service is still in the beta stage, so please use it at your own risk.</p>
</div>
</body>
</html>

OUT;
	}
}

// Function to display a bus row

function display($busid, $from, $to, $nstops)
{

	$name1 = place($from);			//halt name from
	$name2 = place($to);			//halt name to
	
	if($to > 200 || $from > 200)		//approximate nstops for long distances
	{
		$nstops = 'More than '.$nstops;
	}

	if(($bus = busDet($busid)) != false)
	{
		$tgeo = geolocate($to);
		$fgeo = geolocate($from);

		if(isset($_GET['v']))
		{
			echo "Take the <strong>${bus[1]}</strong> (${bus[2]} - ${bus[3]}) bus. Get on at $name1 ($fgeo) and get off at $name2 ($tgeo).<br/>";
		}
		else
		{

		echo <<< OUT
<ul id="stops">	
	<li id="le"><div id="route">$bus[1]</div></li>
	<li id="le"><h3>Bus Start</h3><br/>$bus[2]</li>
	<li id="le">$fgeo<h3>Get on at</h3><br/>$name1</li>
	<li id="le">$tgeo<h3>Get off at</h3><br/>$name2</li>
	<li id="le"><h3>Bus End</h3><br/>$bus[3]</li>
	<li><h3>No. of halts</h3><br/>$nstops</li>
</ul>
OUT;

		}
	}
}

?>

