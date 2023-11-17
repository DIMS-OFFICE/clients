<?php
	require("../pdo_db_connect.php");

	$s=$db->prepare("SELECT tel_nom, comment_date FROM comments WHERE id=:comment_id");
	$s->bindValue(":comment_id", $_POST["comment_id"]);
	$s->execute();

	$comment=$s->fetch(PDO::FETCH_ASSOC);

	$s=$db->prepare("DELETE FROM comments WHERE id=:comment_id");
	$s->bindValue(":comment_id", $_POST["comment_id"]);
	$s->execute();

	$log_str=$comment["tel_nom"].". Удалён комментарий за ".$comment["comment_date"];
	
	if ($s->rowCount()>0) {
		$s=$db->prepare("INSERT INTO user_log (date_time, user_name, operation, tel_nom) VALUES (NOW(), :name, :log_str, :tel_nom)");
		$s->bindValue(":name", $_POST["user_name"]);
		$s->bindValue(":log_str", $log_str);
		$s->bindValue(":tel_nom", $comment["tel_nom"]);
		$s->execute();

		echo "OK";
	}
?>