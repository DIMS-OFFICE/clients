<?php
	require("pdo_db_connect.php");

	$s=$db->prepare("DELETE FROM clients.services_history WHERE id=:id");
	$s->bindValue(":id", $_POST["id"]);
	$s->execute();

	if ($s->rowCount()>0) {
		echo "OK";

		$s=$db->prepare("SELECT tel_nom FROM clients.services_history WHERE service_code=:service_code AND tel_nom=:tel_nom");
		$s->bindValue(":tel_nom", $_POST["tel_nom"]);
		$s->bindValue(":service_code", $_POST["service_code"]);
		$s->execute();

		$records_count=$s->rowCount();

		if ($records_count==0) {
			$s=$db->prepare("DELETE FROM clients.services WHERE code=:service_code AND tel_nom=:tel_nom");
			$s->bindValue(":service_code", $_POST["service_code"]);
			$s->bindValue(":tel_nom", $_POST["tel_nom"]);
			$s->execute();
		}
	}
?>