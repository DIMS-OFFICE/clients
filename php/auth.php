<?php
	header('Access-Control-Allow-Origin: *');

	require("pdo_db_connect.php");

	if (isset($_POST["hash"])==false) {
		$_POST["hash"]=0;
	}

	$s=$db->prepare("SELECT user_id FROM service_users_sessions WHERE hash LIKE :hash");
	$s->bindValue(":hash", $_POST["hash"]);
	$s->execute();

	//Сессия истекла
	if ($s->rowCount()==0) {
		//Вход по паролю
		if (isset($_POST["login"]) && isset($_POST["password"])) {
			$s=$db->prepare("SELECT id, name, type FROM service_users WHERE login=:login AND pass=:pass");
			$s->bindValue(":login", $_POST["login"]);
			$s->bindValue(":pass", $_POST["password"]);
			$s->execute();

			$user=$s->fetch(PDO::FETCH_ASSOC);

			$hash=md5(time());

			$s=$db->prepare("INSERT service_users_sessions SET user_id=:user_id, hash=:hash, start_time=NOW()");
			$s->bindValue(":user_id", $user["id"]);
			$s->bindValue(":hash", $hash);
			$s->execute();

			$res=Array(
				"result" => "session_remain",
				"hash" => $hash,
				"user_name" => $user["name"],
				"user_type" => $user["type"]
			);
			echo json_encode($res);
			exit();
		} else {
			$res=Array(
				"result" => "session_removed"
			);
			echo json_encode($res);
			exit();
		}
	} else {
		$session=$s->fetch(PDO::FETCH_ASSOC);

		$s_update=$db->prepare("UPDATE service_users_sessions SET start_time=NOW() WHERE user_id=:user_id");
		$s_update->bindValue(":user_id", $session["user_id"]);
		$s_update->execute();

		$s=$db->prepare("SELECT name, type FROM service_users WHERE id=:id");
		$s->bindValue(":id", $session["user_id"]);
		$s->execute();

		$user=$s->fetch(PDO::FETCH_ASSOC);

		$res=Array(
			"result" => "session_remain",
			"hash" => $_POST["hash"],
			"user_name" => $user["name"],
			"user_type" => $user["type"]
		);
		echo json_encode($res);
		exit();
	}
?>