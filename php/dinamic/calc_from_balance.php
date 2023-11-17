<?php
	require("../pdo_db_connect.php");

	$s=$db->prepare("UPDATE clients.history SET balance=:balance WHERE tel_nom=:tel_nom AND update_date=:history_date");
	$s->bindValue(":tel_nom", $_POST["tel_nom"]);
	$s->bindValue(":balance", $_POST["balance"]);
	$s->bindValue(":history_date", $_POST["history_date"]);

	$s->execute();

	echo "OK";
?>