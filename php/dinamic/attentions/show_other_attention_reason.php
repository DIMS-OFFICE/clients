<?php
	require("../../pdo_db_connect.php");

	if ($_POST["operator"]=="mts") {
		$order='service, call_type';
	} else if ($_POST["operator"]=="bee" || $_POST["operator"]=="bee+") {
		$order='service, call_type';
	} else if ($_POST["operator"]=="meg") {
		$order='call_type, service';
	}  else if ($_POST["operator"]=="tele2") {
		$order='call_type, service';
	}

	$s=$db->prepare("SELECT id, call_type, service FROM call_types WHERE operator=:operator AND attention=1 ORDER BY ".$order);
	$s->bindValue(":operator", $_POST["operator"]);

	$s->execute();

	$res=$s->fetchAll(PDO::FETCH_ASSOC);

	echo json_encode($res);
?>