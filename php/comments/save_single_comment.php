<?php
	require("../pdo_db_connect.php");

	$s=$db->prepare("UPDATE clients.clients SET comment=:comment WHERE tel_nom=:tel_nom");
	$s->bindValue(":comment", $_POST["new_single_comment"]);
	$s->bindValue(":tel_nom", $_POST["tel_nom"]);
	$s->execute();

	if ($s->rowCount()>0) {
		echo "OK";
	} else {
		echo "Error";
	}
?>