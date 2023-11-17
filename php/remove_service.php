<?php
	require("pdo_db_connect.php");

	$s=$db->prepare("DELETE FROM clients.services_dict WHERE id=:id");
	$s->bindValue(":id", $_POST["service_id"]);
	$s->execute();

	if ($s->rowCount()>0) {
		echo "OK";
	} else {
		echo "error";
	}
?>