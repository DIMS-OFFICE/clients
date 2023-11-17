<?php
	require("pdo_db_connect.php");

	if ($_POST["finish_date"]=="") {
		$_POST["finish_date"]="2030-01-01";
	}

	$s=$db->prepare("UPDATE clients.services_prices SET price=:price, `date`=:start_date, finish_date=:finish_date WHERE id=:id");
	$s->bindValue(":id", $_POST["id"]);
	$s->bindValue(":price", $_POST["price"]);
	$s->bindValue(":start_date", $_POST["start_date"]);
	$s->bindValue(":finish_date", $_POST["finish_date"]);
	$s->execute();

	echo "OK";
?>