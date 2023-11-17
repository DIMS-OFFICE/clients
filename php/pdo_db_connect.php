<?php
	try {
	  //$db = new PDO("mysql:host=localhost;dbname=roomsclu_bets", "roomsclu", "Bz6Rp055iw");	  
	  //$db = new PDO("mysql:host=localhost;dbname=".$db_name, "root", "240282", array(PDO::MYSQL_ATTR_LOCAL_INFILE=>1));
	 $db = new PDO("mysql:host=localhost;dbname=phones100", "root", "DiMS-21093@", array(PDO::MYSQL_ATTR_LOCAL_INFILE=>1));															
	  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	  $db->exec("set names utf8");
	}
	catch(PDOException $e) {
		echo $e->getMessage();
	}
?>
