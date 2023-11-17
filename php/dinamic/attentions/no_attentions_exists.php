<?php
	require("../../pdo_db_connect.php");

	$s=$db->prepare("SELECT id, until_date, type FROM clients.no_attentions WHERE tel_nom=:tel_nom AND until_date>DATE(NOW())");
	$s->bindValue(":tel_nom", $_POST["tel_nom"]);
	$s->execute();

	$res=$s->fetchAll(PDO::FETCH_ASSOC);

	echo json_encode($res);
?>