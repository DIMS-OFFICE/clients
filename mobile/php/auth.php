<?php
	require("../../php/pdo_db_connect.php");

	if ($_POST["by_hash"]==1) {
		$s=$db->prepare("SELECT tel_nom FROM clients.clients_logins WHERE hash=:hash");
		$s->bindValue(":hash", $_POST["hash"]);

		$s->execute();

		if ($s->rowCount()==0) {
			$res=Array(
				"status" => "wrong_hash",
				"desc" => "Неверный ХЭШ"
			);

			echo json_encode($res);
		} else {
			$tel_nom=$s->fetch(PDO::FETCH_COLUMN);

			$hash=md5(time());

			$s=$db->prepare("UPDATE clients.clients_logins SET hash=:hash, last_activity=NOW() WHERE tel_nom=:tel_nom");
			$s->bindValue(":hash", $hash);
			$s->bindValue(":tel_nom", $tel_nom);

			$s->execute();

			$res=Array(
				"status" => "OK",
				"hash" => $hash,
				"tel_nom" => $tel_nom
			);

			echo json_encode($res);
		}
	} else {
		$s=$db->prepare("SELECT tel_nom FROM clients.clients_logins WHERE tel_nom=:tel_nom AND password=:password");
		$s->bindValue(":tel_nom", $_POST["tel_nom"]);
		$s->bindValue(":password", md5($_POST["password"]))	;

		$s->execute();

		if ($s->rowCount()==0) {
			$res=Array(
				"status" => "wrong_number",
				"desc" => "Неверный номер или пароль"
			);

			echo json_encode($res);
		} else {
			$tel_nom=$s->fetch(PDO::FETCH_COLUMN);

			$hash=md5(time());

			$s=$db->prepare("UPDATE clients.clients_logins SET hash=:hash, last_activity=NOW() WHERE tel_nom=:tel_nom");
			$s->bindValue(":hash", $hash);
			$s->bindValue(":tel_nom", $tel_nom);

			$s->execute();

			$res=Array(
				"status" => "OK",
				"hash" => $hash,
				"tel_nom" => $tel_nom
			);

			echo json_encode($res);
		}
	}
?>