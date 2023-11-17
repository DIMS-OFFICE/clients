<?php
	require("pdo_db_connect.php");

	$s=$db->prepare("UPDATE clients.services_dict SET periodic=:periodic WHERE id=:service_id");
	$s->bindValue(":periodic", $_POST["periodic"]);
	$s->bindValue(":service_id", $_POST["service_id"]);
	$s->execute();

	if ($s->rowCount()>0) {
		echo "OK";
	} else {
		echo "error";
	}
?>