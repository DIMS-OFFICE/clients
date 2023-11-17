<?php
	require("pdo_db_connect.php");

	$s=$db->prepare("SELECT user_id FROM service_users_sessions WHERE hash=:hash");
	$s->bindValue(":hash", $_POST["hash"]);
	$s->execute();
	$user_id=$s->fetch(PDO::FETCH_COLUMN);

	$s=$db->prepare("SELECT name FROM service_users WHERE id=:user_id");
	$s->bindValue(":user_id", $user_id);
	$s->execute();
	$user_name=$s->fetch(PDO::FETCH_COLUMN);

	$s=$db->prepare("DELETE FROM service_users_sessions WHERE hash=:hash");
	$s->bindValue(":hash", $_POST["hash"]);
	$s->execute();

	$s=$db->prepare("INSERT INTO user_log (date_time, user_name, operation) VALUES (NOW(), :name, 'Выход из системы (IP: ".$_SERVER["REMOTE_ADDR"].", User-Agent: ".$_SERVER["HTTP_USER_AGENT"].")')");
	$s->bindValue(":name", $user_name);
	$s->execute();

	$s=$db->prepare("UPDATE job_time SET finish_time=TIME(NOW()) WHERE name=:name AND `date`=DATE(NOW())");
	$s->bindValue(":name", $user_name);
	$s->execute();

	echo "OK";
?>