<?php
	require("pdo_db_connect.php");

	$s=$db->prepare("UPDATE clients.call_types_prices SET `date`=:start_date, finish_date=:finish_date WHERE id=:id");
	$s->bindValue(":start_date", $_POST["start_date"]);
	$s->bindValue(":finish_date", $_POST["finish_date"]);
	$s->bindValue(":id", $_POST["id"]);
	$s->execute();

	if ($s->rowCount()>0) {
		echo "OK";
	} else {
		echo "error";
	}
?>