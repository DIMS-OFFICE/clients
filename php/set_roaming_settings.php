<?php
	require("pdo_db_connect.php");

	$s=$db->prepare("UPDATE clients.countries SET min_price=:min_price WHERE id=:id");
	$s->bindValue(":id", $_POST["id"]);
	$s->bindValue(":min_price", $_POST["min_price"]);
	$s->execute();

	if ($s->rowCount()>0) {
		echo "OK";
	} else {
		echo "error";
	}
?>