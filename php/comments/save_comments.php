<?php
	require("../pdo_db_connect.php");

	if ($_POST["operator"]=="meg") {
		$operator="megafon";
	} else if ($_POST["operator"]=="bee") {
		$operator="bee";
	} else if ($_POST["operator"]=="mts") {
		$operator="mts";
	} else {
		$operator="tele2";
	}

	$s=$db->prepare("SELECT id FROM ".$operator."_phones WHERE PhoneNumber=:tel_nom");
	$s->bindValue(":tel_nom", $_POST["tel_nom"]);
	$s->execute();

	if ($s->rowCount()==0) {
		$s=$db->prepare("SELECT id FROM ".$operator."_phones WHERE PhoneNumber=:tel_nom");
		$s->bindValue(":tel_nom", "7".$_POST["tel_nom"]);
		$s->execute();

		if ($s->rowCount()==0) {
			$res=Array(
				"result" => "wrong_number"
			);

			echo json_encode($res);
			exit();
		} else {
			$_POST["tel_nom"]="7".$_POST["tel_nom"];
		}
	}

	if ($_POST["old_date"]!=$_POST["date"] && $_POST["replace"]!=1) {
		$s_sel=$db->prepare("SELECT id, comment_text, comment_date FROM comments WHERE tel_nom=:tel_nom AND comment_date=:comment_date");
		$s_sel->bindValue(":comment_date", $_POST["date"]);
		$s_sel->bindValue(":tel_nom", $_POST["tel_nom"]);
		$s_sel->execute();

		if ($s_sel->rowCount()>0) {
			$comment=$s_sel->fetch(PDO::FETCH_ASSOC);

			$res=Array(
				"result" => "already_exists",
				"comment_text" => $comment["comment_text"],
				"comment_id" => $comment["id"],
				"comment_date" => $comment["comment_date"]
			);

			echo json_encode($res);

			exit();
		}
	}

	if ($_POST["replace"]==1) {
		$s_del=$db->prepare("DELETE FROM comments WHERE tel_nom=:tel_nom AND comment_date=:comment_date");
		$s_del->bindValue(":comment_date", $_POST["old_date"]);
		$s_del->bindValue(":tel_nom", $_POST["tel_nom"]);
		$s_del->execute();

		$s_del=$db->prepare("DELETE FROM comments WHERE tel_nom=:tel_nom AND comment_date=:comment_date");
		$s_del->bindValue(":comment_date", $_POST["date"]);
		$s_del->bindValue(":tel_nom", $_POST["tel_nom"]);
		$s_del->execute();

		$s_insert=$db->prepare("INSERT INTO comments (comment_text, operator, tel_nom, comment_date, show_in_reports, user_name) VALUES (:comment_text, :operator, :tel_nom, :comment_date, 0, '')");
		$s_insert->bindValue(":comment_text", $_POST["comment_text"]);
		$s_insert->bindValue(":operator", $_POST["operator"]);
		$s_insert->bindValue(":comment_date", $_POST["date"]);
		$s_insert->bindValue(":tel_nom", $_POST["tel_nom"]);
		$s_insert->execute();

		$log_str=$_POST["tel_nom"].". Изменён комментарий за ".$_POST["date"];

		$s=$db->prepare("INSERT INTO user_log (date_time, user_name, operation, tel_nom) VALUES (NOW(), :name, :log_str, :tel_nom)");
		$s->bindValue(":name", $_POST["user_name"]);
		$s->bindValue(":log_str", $log_str);
		$s->bindValue(":tel_nom", $_POST["tel_nom"]);
		$s->execute();
	} else {
		$s_sel=$db->prepare("SELECT id FROM comments WHERE id=:comment_id");
		$s_sel->bindValue(":comment_id", $_POST["comment_id"]);
		$s_sel->execute();

		if ($s_sel->rowCount()>0) {
			$s_update=$db->prepare("UPDATE comments SET comment_text=:comment_text, comment_date=:comment_date, tel_nom=:tel_nom, show_in_reports=:show_in_reports WHERE id=:comment_id");
			$s_update->bindValue(":comment_text", $_POST["comment_text"]);
			$s_update->bindValue(":comment_date", $_POST["date"]);
			$s_update->bindValue(":tel_nom", $_POST["tel_nom"]);
			$s_update->bindValue(":comment_id", $_POST["comment_id"]);
			$s_update->bindValue(":show_in_reports", $_POST["show_in_reports"]);
			$s_update->execute();

			$log_str=$_POST["tel_nom"].". Изменён комментарий за ".$_POST["date"];

			$s=$db->prepare("INSERT INTO user_log (date_time, user_name, operation, tel_nom) VALUES (NOW(), :name, :log_str, :tel_nom)");
			$s->bindValue(":name", $_POST["user_name"]);
			$s->bindValue(":log_str", $log_str);
			$s->bindValue(":tel_nom", $_POST["tel_nom"]);
			$s->execute();
		} else {
			$s_insert=$db->prepare("INSERT INTO comments (comment_text, operator, tel_nom, comment_date, show_in_reports, user_name, date_time) VALUES (:comment_text, :operator, :tel_nom, :comment_date, :show_in_reports, '', NOW())");
			$s_insert->bindValue(":comment_text", $_POST["comment_text"]);
			$s_insert->bindValue(":operator", $_POST["operator"]);
			$s_insert->bindValue(":comment_date", $_POST["date"]);
			$s_insert->bindValue(":tel_nom", $_POST["tel_nom"]);
			$s_insert->bindValue(":show_in_reports", $_POST["show_in_reports"]);
			$s_insert->execute();

			$log_str=$_POST["tel_nom"].". Новый комментарий за ".$_POST["date"];

			$s=$db->prepare("INSERT INTO user_log (date_time, user_name, operation, tel_nom) VALUES (NOW(), :name, :log_str, :tel_nom)");
			$s->bindValue(":name", $_POST["user_name"]);
			$s->bindValue(":log_str", $log_str);
			$s->bindValue(":tel_nom", $_POST["tel_nom"]);
			$s->execute();
		}
	}

	$res=Array(
		"result" => "OK"
	);

	echo json_encode($res);
?>
	