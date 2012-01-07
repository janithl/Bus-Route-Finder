<?php

require('./dbconn.php');

/******************************************************************************************

	Colombo Bus Route Finder by Janith Leanage (http://janithl.blogspot.com)
	and Thimal Jayasooriya (https://github.com/thimal)

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

$locs	= array();
$list	= '<script type="text/javascript">!window.jQuery && document.write(\'<script src="jquery/jquery-1.4.3.min.js"><\/script>\');';

$dbconn	= new DBConn();
$res	= $dbconn->query("SELECT pid, name FROM place", array());

if($res)
{
	while(($row = $resultset->fetch()) != false)
	{
		$locs[] = "${row[1]} (${row[0]})";
	}

	$list .= '<!-- $(function () { $(\'#f, #t\').tagSuggest({ tags: ["' . implode('","', $locs) . '"] }); }); //-->';
}

$list .= '</script>';

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>Bus Route Finder</title>
<link rel="stylesheet" href="style.css" type="text/css" charset="utf-8" />
<link href="img/bus.ico" rel="icon" type="image/vnd.microsoft.icon"/>
<link href='http://fonts.googleapis.com/css?family=Cabin&v1' rel='stylesheet' type='text/css' />
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js"></script>
<script type="text/javascript" src="jquery/tags.js"></script>
<?php echo $list; ?>
</head>

<body>
<div id="header">
<form id="left" action="query.php" method="get" enctype="text/plain" autocomplete="off">
<h1>I want to go on a bus...</h1>
<label for="f">from</label><input type="text" id="f" name="f" value="" /><br/>
<label for="t">to</label><input type="text" id="t" name="t" value="" /><br/>
<button type="submit">find a bus</button>
</form>
</div>

<div id="footer">
<p>Disclaimer: This service is still in the beta stage, so please use it at your own risk.<br/><br/>
<a href="./mobile.php">Mobile Version</a> | <a href="http://janithl.blogspot.com/2011/07/bus-route-finder.html">More Info</a> | <a href="https://github.com/janithl">Source Code</a></p>
</div>
</body>
</html>
