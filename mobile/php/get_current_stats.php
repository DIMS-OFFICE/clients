<?php
	require("../../php/pdo_db_connect.php");

	$s=$db->prepare("SELECT id FROM clients.clients_logins WHERE hash=:hash");
	$s->bindValue(":hash", $_POST["hash"]);

	$s->execute();

	if ($s->rowCount()==0) {
		$res=Array(
			"status" => "error",
			"desc" => "wrong_hash"
		);

		echo json_encode($res);

		exit();
	} else {
		$id=$s->fetch(PDO::FETCH_COLUMN);

		$s=$db->prepare("UPDATE clients.clients_logins SET last_activity=NOW() WHERE id=:id");
		$s->bindValue(":id", $id);

		$s->execute();
	}


	$s=$db->prepare("SELECT balance, spended, operator, client_group FROM clients.clients WHERE tel_nom=:tel_nom");
	$s->bindValue(":tel_nom", $_POST["tel_nom"]);

	$s->execute();

	$res=$s->fetch(PDO::FETCH_ASSOC);

	
	$s=$db->prepare("SELECT blocks FROM ".$res["operator"]."_counters_actual WHERE tel_nom=:tel_nom ORDER BY tel_nom DESC");
	$s->bindValue(":tel_nom", $_POST["tel_nom"]);

	$s->execute();

	$blocks=$s->fetch(PDO::FETCH_COLUMN);

	if (strlen($blocks)>0 && $blocks!="-") {
		$activity_status="Блокирован";
	} else {
		$activity_status="Активен";
	}


	$s=$db->prepare("SELECT tel_nom FROM clients.clients WHERE client_group=:client_group");
	$s->bindValue(":client_group", $res["client_group"]);

	$s->execute();

	$group_tel_noms=$s->fetchAll(PDO::FETCH_COLUMN);

	$result=Array(
		"current_balance" => number_format($res["balance"], 2, ".", " "),
		"current_spended" => number_format($res["spended"], 2, ".", " "),
		"operator" => $res["operator"],
		"activity_status" => $activity_status,
		"group_tel_noms" => $group_tel_noms
	);

	echo json_encode($result);
?>