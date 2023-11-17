<?php
	require("pdo_db_connect.php");

	if ($_POST["operator"]=="meg") {
		$operator="megafon";
	} else {
		$operator=$_POST["operator"];
	}

	$s=$db->prepare("SELECT tel_nom FROM clients.services WHERE code=:service_id AND status='Активная' AND operator=:operator");
	$s->bindValue(":service_id", $_POST["service_id"]);
	$s->bindValue(":operator", $_POST["operator"]);
	$s->execute();

	$tel_noms1=$s->fetchAll(PDO::FETCH_COLUMN);

	$tel_noms=implode(",",$tel_noms1);

	if ($_POST["operator"]=="mts" || $_POST["operator"]=="tele2") {
		$s=$db->prepare("SELECT PhoneNumber, AccountNumber as account, contract FROM ".$operator."_phones WHERE PhoneNumber IN (".$tel_noms.")");
	} else if ($_POST["operator"]=="bee") {
		$s=$db->prepare("SELECT PhoneNumber, AccountNumber as account, (CASE WHEN contract = 1 THEN 'Основной' WHEN contract = 2 THEN 'Бандиты' ELSE contract END) as contract FROM ".$operator."_phones WHERE PhoneNumber IN (".$tel_noms.")");
	} else if ($_POST["operator"]=="meg") {
		$s=$db->prepare("SELECT PhoneNumber, AccountNumber as account, (CASE WHEN contract = 1 THEN 'DiMS' WHEN contract = 2 THEN 'DiMS+'  WHEN contract > 2 THEN 'DiMS++' ELSE contract END) as contract FROM ".$operator."_phones WHERE PhoneNumber IN (".$tel_noms.")");
	}

	$s->execute();

	$tel_noms_res=$s->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);

	/*if ($_POST["have_no_service"]==1) {
		$tel_noms_array=array_keys($tel_noms_res);

		$tel_noms_str=implode(",",$tel_noms_array);

		if ($_POST["operator"]=="meg") {
			$_POST["operator"]="megafon";
		}

		if ($_POST["operator"]=="mts" || $_POST["operator"]=="tele2") {
			$s=$db->prepare("SELECT PhoneNumber as tel_nom, AccountNumber as account, (CASE WHEN contract = 1 THEN 'DiMS' WHEN contract = 2 THEN 'DiMS+' ELSE contract END) as contract FROM ".$_POST["operator"]."_phones WHERE PhoneNumber NOT IN (".$tel_noms_str.") AND status=0 AND SUBSTR(PhoneNumber,1,2)!='71'");
		} else if ($_POST["operator"]=="bee") {
			$s=$db->prepare("SELECT PhoneNumber as tel_nom, AccountNumber as account, (CASE WHEN contract = 1 THEN 'Основной' WHEN contract = 2 THEN 'Бандиты' ELSE contract END) as contract FROM ".$_POST["operator"]."_phones WHERE PhoneNumber NOT IN (".$tel_noms_str.") AND status=0");
		} else if ($_POST["operator"]=="meg") {
			$s=$db->prepare("SELECT PhoneNumber as tel_nom, AccountNumber as account, (CASE WHEN contract = 1 THEN 'DiMS' WHEN contract = 2 THEN 'DiMS+'  WHEN contract > 2 THEN 'DiMS++' ELSE contract END) as contract FROM ".$_POST["operator"]."_phones WHERE PhoneNumber NOT IN (".$tel_noms_str.") AND status=0");
		}

		$s->execute();

		$tel_noms_res=$s->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);
	}*/

	$tel_noms=array_keys($tel_noms_res);

	$tel_noms=implode(",",$tel_noms);

	$s=$db->prepare("SELECT tel_nom, tarif FROM ".$_POST["operator"]."_counters_actual WHERE tel_nom IN (".$tel_noms.") GROUP BY tel_nom");
	$s->execute();

	$tarifs_res=$s->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);

	$result=Array(
		"tel_noms" => $tel_noms_res,
		"count" => count($tel_noms_res),
		"tarifs" => $tarifs_res
	);

	echo json_encode($result);
?>