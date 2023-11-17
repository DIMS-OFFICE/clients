<?php
	require("pdo_db_connect.php");

	$s=$db->prepare("UPDATE call_types SET unit_price=:unit_price WHERE type_nom=:type_nom");
	$s->bindValue(":type_nom", $_POST["type_nom"]);
	$s->bindValue(":unit_price", $_POST["unit_price"]);
	$s->execute();

	$s=$db->prepare("SELECT type, operator FROM call_types WHERE type_nom=:type_nom LIMIT 1");
	$s->bindValue(":type_nom", $_POST["type_nom"]);
	$s->execute();
	$type=$s->fetch(PDO::FETCH_ASSOC);

	$s_sel=$db->prepare("SELECT id FROM clients.call_types_history WHERE type_nom=:type_nom AND `date`=DATE(NOW())");
	$s_sel->bindValue(":type_nom", $_POST["type_nom"]);
	$s_sel->execute();

	if ($s_sel->rowCount()==0) {
		$s=$db->prepare("INSERT INTO clients.call_types_history (type, type_nom, unit_price, `date`, operator) VALUES (:type, :type_nom, :unit_price, DATE(NOW()), :operator)");
		$s->bindValue(":type", $type["type"]);
		$s->bindValue(":type_nom", $_POST["type_nom"]);
		$s->bindValue(":unit_price", $_POST["unit_price"]);
		$s->bindValue(":operator", $type["operator"]);
		$s->execute();
	} else {
		$s=$db->prepare("UPDATE clients.call_types_history SET unit_price=:unit_price WHERE type_nom=:type_nom AND `date`=DATE(NOW())");
		$s->bindValue(":type_nom", $_POST["type_nom"]);
		$s->bindValue(":unit_price", $_POST["unit_price"]);
		$s->execute();
	}

	if ($s->rowCount()>0) {
		echo "OK";
	} else {
		echo "error";
	}
?>