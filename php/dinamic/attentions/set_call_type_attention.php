<?php
	require("../../pdo_db_connect.php");

	$s=$db->prepare("UPDATE call_types SET attention=:status WHERE call_type LIKE :call_type AND service LIKE :service AND operator=:operator");
	$s->bindValue(":call_type", $_POST["call_type"]);
	$s->bindValue(":service", $_POST["service"]);
	$s->bindValue(":operator", $_POST["operator"]);
	$s->bindValue(":status", $_POST["status"]);
	$s->execute();

	if ($s->rowCount()>0) {
		echo "OK";
	} else {
		echo "error";
	}
?>