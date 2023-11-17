<?php
	header('Access-Control-Allow-Origin: *');
	
	require("pdo_db_connect.php");

	$s=$db->prepare("SELECT blocks FROM ".$_POST["operator"]."_counters_actual WHERE tel_nom=:tel_nom LIMIT 1");
	$s->bindValue(":tel_nom", $_POST["tel_nom"]);
	$s->execute();

	$blocks=$s->fetch(PDO::FETCH_COLUMN);

	if ($_POST["operator"]=="meg") {
		$_POST["operator"]="megafon";
	}

	$s=$db->prepare("SELECT AccountNumber, Contract FROM ".$_POST["operator"]."_phones WHERE PhoneNumber=:tel_nom");
	$s->bindValue(":tel_nom", $_POST["tel_nom"]);
	$s->execute();

	$tel_nom=$s->fetch(PDO::FETCH_ASSOC);

	$s=$db->prepare("SELECT fio, info FROM users_profiles WHERE tel_nom=:tel_nom");
	$s->bindValue(":tel_nom", $_POST["tel_nom"]);
	$s->execute();

	if ($s->rowCount()>0) {
		$fio=$s->fetch(PDO::FETCH_ASSOC);
	} else {
		$fio["fio"]="";
		$fio["info"]="";
	}

	$s=$db->prepare("SELECT update_date, update_time FROM clients.clients WHERE tel_nom=:tel_nom");
	$s->bindValue(":tel_nom", $_POST["tel_nom"]);
	$s->execute();

	$actual=$s->fetch(PDO::FETCH_ASSOC);

	$s=$db->prepare("SELECT client_group, start_date FROM clients.groups_history WHERE tel_nom=:tel_nom ORDER BY id DESC");
	$s->bindValue(":tel_nom", $_POST["tel_nom"]);
	$s->execute();

	if ($s->rowCount()>0) {
		$client_groups1=$s->fetchAll(PDO::FETCH_ASSOC);

		$client_groups=Array();
		foreach ($client_groups1 as $cg) {
			if ($cg["client_group"]=="") {
				$cg["client_group"]="Не в группе";
			}

			$client_groups[]=$cg["client_group"]." (с ".$cg["start_date"].")";
		}

		$client_groups=implode("\n", $client_groups);

		if ($client_groups1[0]["client_group"]=="") {
			$client_groups1[0]["client_group"]="Не в группе";
		}

		$client_group=$client_groups1[0]["client_group"]." (с ".$client_groups1[0]["start_date"].")";
	} else {
		$client_group="";
	}


	if ($actual["update_date"]=="1970-01-01") {
		$actual="Не обновлялся";
	} else {
		$actual="Обновлён: ".$actual["update_date"]." ".$actual["update_time"];
	}

	$s=$db->prepare("SELECT id FROM phones100.".$_POST["operator"]."_phones WHERE PhoneNumber=:tel_nom AND contract=0");
	$s->bindValue(":tel_nom", $_POST["tel_nom"]);
	$s->execute();

	if ($s->rowCount()>0) {
		$blocks="Удалён";
	}

	$result=Array(
		"account" => $tel_nom["AccountNumber"],
		"contract" => $tel_nom["Contract"],
		"blocks" => $blocks,
		"info" => $fio,
		"actual" => $actual,
		"clients_group" => $client_group,
		"clients_groups" => $client_groups
	);

	echo json_encode($result);
?>