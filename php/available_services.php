<?php
	require("pdo_db_connect.php");

	$s=$db->prepare("SELECT code FROM clients.services WHERE tel_nom=:tel_nom AND status='Активная' ORDER BY status");
	$s->bindValue(":tel_nom", $_POST["tel_nom"]);
	$s->execute();

	if ($s->rowCount()>0) {
		$active=$s->fetchAll(PDO::FETCH_COLUMN);
		$active=implode(",",$active);
	} else {
		$active=0;
	}

	$s=$db->prepare("SELECT id as code, service FROM clients.services_dict WHERE id NOT IN (".$active.") AND operator=:operator ORDER BY sort ASC");
	$s->bindValue(":operator", $_POST["operator"]);
	$s->execute();

	$available=$s->fetchAll(PDO::FETCH_ASSOC);

	$s=$db->prepare("SELECT service_code, price FROM clients.services_prices WHERE id IN (SELECT MAX(id) FROM clients.services_prices GROUP BY service_code)");
	$s->execute();

	$last_prices=$s->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);

	$codes=Array();
	$i=0;
	foreach ($available as $av) {
		$result[$i]["code"]=$av["code"];
		$result[$i]["service"]=$av["service"];
		$result[$i]["price"]=$last_prices[$av["code"]][0]["price"];

		$i++;
	}

	echo json_encode($result);
?>