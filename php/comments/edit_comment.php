<?php
	require("../pdo_db_connect.php");

	$s=$db->prepare("UPDATE comments SET comment_text=:comment_text WHERE tel_nom=:tel_nom AND comment_date=:comment_date");
	$s->bindValue(":comment_text", $_POST["data"]);
	$s->bindValue(":tel_nom", $_POST["tel_nom"]);
	$s->bindValue(":comment_date", $_POST["date"]);
	$s->execute();

	echo "OK";
?>