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

	$s=$db->prepare("SELECT id as code, service, ".$_POST["operator"]."_price as price FROM clients.services_dict WHERE id IN (".$active.") ORDER BY sort ASC");
	$s->execute();

	$enable=$s->fetchAll(PDO::FETCH_ASSOC);

	echo json_encode($enable);
?>