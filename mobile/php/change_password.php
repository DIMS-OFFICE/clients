<?php
	require("../../php/pdo_db_connect.php");

	$s=$db->prepare("SELECT id FROM clients.clients_logins WHERE hash=:hash");
	$s->bindValue(":hash", $_POST["hash"]);

	$s->execute();

	if ($s->rowCount()==0) {
		$res=Array(
			"status" => "wrong_hash",
			"desc" => "wrong_hash"
		);

		echo json_encode($res);

		exit();
	} else {
		$id=$s->fetch(PDO::FETCH_COLUMN);

		$s=$db->prepare("UPDATE clients.clients_logins SET password=:password WHERE id=:id");
		$s->bindValue(":id", $id);
		$s->bindValue(":password", md5($_POST["new_password"]));

		$s->execute();

		if ($s->rowCount()>0) {
			$res=Array(
				"status" => "OK",
				"desc" => ""
			);
		} else {
			$res=Array(
				"status" => "error",
				"desc" => "Ошибка"
			);
		}

		echo json_encode($res);
	}

?>