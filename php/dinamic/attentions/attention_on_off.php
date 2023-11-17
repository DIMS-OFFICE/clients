<?php
	require("../../pdo_db_connect.php");

	$s=$db->prepare("SELECT name FROM service_users WHERE id=(SELECT user_id FROM service_users_sessions WHERE hash=:hash LIMIT 1)");
	$s->bindValue(":hash", $_POST["hash"]);
	$s->execute();

	if ($s->rowCount()==0) {
		echo "session_expaired";
		exit();
	}
	
	$user_name=$s->fetch(PDO::FETCH_COLUMN);

	if (isset($_POST["attention_ids"]) && $_POST["attention_ids"]!="") {//Отжатие оптом через админку
		$s=$db->prepare("UPDATE clients.attentions SET done=1, user=:user_name WHERE id IN (".$_POST["attention_ids"].")");
		$s->bindValue(":user_name", $user_name);
		$s->execute();

		if ($s->rowCount()>0) {
			echo "OK";
		}
	} else {
		if ($_POST["attention_id"]==-1) {
			$s=$db->prepare("SELECT id, tel_nom, type, txt, operator, date_time FROM clients.attentions WHERE tel_nom=:tel_nom AND done=0 AND (type!='no_dinamic_data0' OR type IS NULL) ORDER BY date_time DESC LIMIT 1");
			$s->bindValue(":tel_nom", $_POST["tel_nom"]);
			$s->execute();

			$_POST["attention_id"]=$s->fetch(PDO::FETCH_COLUMN);
		}

		$s=$db->prepare("SELECT id, done FROM clients.attentions WHERE id=:id ORDER BY id DESC LIMIT 1");
		$s->bindValue(":id", $_POST["attention_id"]);
		$s->execute();

		$attention=$s->fetch(PDO::FETCH_ASSOC);

		if ($attention["done"]==0 || $_POST["only_off"]==1) {
			$s=$db->prepare("UPDATE clients.attentions SET done=1, user=:user_name WHERE id=:id");
			$s->bindValue(":id", $_POST["attention_id"]);
			$s->bindValue(":user_name", $user_name);
			$s->execute();

			if ($s->rowCount()>0) {
				echo "off";
			}
		} else {
			$s=$db->prepare("UPDATE clients.attentions SET done=0, user=:user_name WHERE id=:id");
			$s->bindValue(":id", $_POST["attention_id"]);
			$s->bindValue(":user_name", $user_name);
			$s->execute();

			if ($s->rowCount()>0) {
				echo "on";
			}
		}
	}
?>