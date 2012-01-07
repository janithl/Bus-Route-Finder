<?php

/******************************************************************************************
	
	DB Connection classn- Based on GPL code written by Thimal Jayasooriya 
	for Colombo bus route project (https://github.com/thimal/Bus-Route-Finder)

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

class DBConn
{
	private $db;
	
	/* db connection properties = EDIT HERE */
	private $dbhost = "localhost";
	private $dbname = "testbus";
	private $dbuser = "root";
	private $dbpwd = '';
	
	public function __construct($conn = true) 
	{
		if ($conn)
		{
			$this->connect();
		}
	}


	function connect() 
	{
		$dsn = "mysql:host=" . $this->dbhost . ";dbname=" . $this->dbname;

	    	try 
		{
			$this->db = new PDO($dsn, $this->dbuser, $this->dbpwd);
			$this->db->exec("set names utf8");
		} 
		catch (PDOException $e)
		{
			print "Database connection failed: " . $e->getMessage() . "<br/>";
			die();    
		}
	}

	function query($sql, $params)
	{		
		
		$statement = $this->db->prepare($sql);
	    	$result = $statement->execute($params);
		
		return $result? $statement:$result;
	}
}

?>
