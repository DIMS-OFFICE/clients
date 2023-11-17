<?php
	require("pdo_db_connect.php");

	$s=$db->prepare("INSERT IGNORE INTO clients.exceptions (`exception`, `date_time`) VALUES (:exception, :date_time)");
	$s->bindValue(":exception", $_POST["exception"]);
	$s->bindValue(":date_time", date("Y-m-d H:i:s", time()));
	$s->execute();

	if ($s->rowCount()>0) {
		echo "Исключение добавлено";
	} else {
		echo "Такое исключение уже есть";
	}
?>