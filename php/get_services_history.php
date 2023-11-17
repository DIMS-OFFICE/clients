<?php
	require("pdo_db_connect.php");

	$s=$db->prepare("SELECT service FROM clients.services_dict WHERE id=:service_code");
	$s->bindValue(":service_code", $_POST["service_code"]);
	$s->execute();

	$service_name=$s->fetch(PDO::FETCH_COLUMN);

	$s=$db->prepare("SELECT id, service_code, start_date, finish_date, kef FROM clients.services_history WHERE tel_nom=:tel_nom AND service_code=:service_code ORDER BY start_date DESC, finish_date DESC");
	$s->bindValue(":tel_nom", $_POST["tel_nom"]);
	$s->bindValue(":service_code", $_POST["service_code"]);
	$s->execute();

	$history=$s->fetchAll(PDO::FETCH_ASSOC);

	foreach ($history as $h) {
		$result[]=Array(
			"history_id" => $h["id"],
			"service" => $service_name,
			"start_date" => $h["start_date"],
			"finish_date" => $h["finish_date"],
			"kef" => $h["kef"]
		);
	}

	echo json_encode($result);
?>