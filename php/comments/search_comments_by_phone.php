<?php
	require("../pdo_db_connect.php");

	if (is_numeric($_POST["tel_nom"])==false) {
		$s=$db->prepare("SELECT tel_nom FROM users_profiles WHERE LOCATE(UPPER(:name), UPPER(fio))>0 AND operator=:operator");
		$s->bindValue(":name", $_POST["tel_nom"]);
		$s->bindValue(":operator", $_POST["operator"]);
		$s->execute();

		if ($s->rowCount()>0) {
			$tel_noms=$s->fetchAll(PDO::FETCH_COLUMN);
			$str=implode(",", $tel_noms);

			$s=$db->prepare("SELECT tel_nom, comment_text, comment_date, user_name FROM comments WHERE tel_nom IN (".$str.") AND operator=:operator");
			$s->bindValue(":operator", $_POST["operator"]);
			$s->execute();
		}
	} else {
		$s=$db->prepare("SELECT tel_nom, comment_text, comment_date, user_name FROM comments WHERE LOCATE(:tel_nom, tel_nom)>0 AND operator=:operator ORDER BY comment_date DESC");
		$s->bindValue(":tel_nom", $_POST["tel_nom"]);
		$s->bindValue(":operator", $_POST["operator"]);
		$s->execute();
	}

	$res=$s->fetchAll(PDO::FETCH_ASSOC);

	echo json_encode($res);
?>