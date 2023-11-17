<?php
	require("../pdo_db_connect.php");

	$s=$db->prepare("SELECT id FROM clients.call_types_kefs WHERE tel_nom=:tel_nom AND call_type=:call_type");
	$s->bindValue(":tel_nom", $_POST["tel_nom"]);
	$s->bindValue(":call_type", $_POST["call_type"]);
	$s->execute();

	if ($s->rowCount()>0) {
		$id=$s->fetch(PDO::FETCH_COLUMN);

		$s=$db->prepare("UPDATE clients.call_types_kefs SET kef=:kef WHERE id=:id");
		$s->bindValue(":kef", $_POST["kef"]);
		$s->bindValue(":id", $id);
		$s->execute();
	} else {
		$s=$db->prepare("INSERT INTO clients.call_types_kefs (tel_nom, call_type, kef) VALUES (:tel_nom, :call_type, :kef)");
		$s->bindValue(":tel_nom", $_POST["tel_nom"]);
		$s->bindValue(":call_type", $_POST["call_type"]);
		$s->bindValue(":kef", $_POST["kef"]);
		$s->execute();
	}

	if ($s->rowCount()>0) {
		echo "OK";
	} else {
		echo "error";
	}
?>