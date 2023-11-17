<?php
	require("../../pdo_db_connect.php");

	if ($_POST["attention_id"]==0) {
		$s=$db->prepare("SELECT id, tel_nom, type, txt, operator, date_time, url FROM clients.attentions WHERE done=0 AND (LOCATE(:tel_nom,txt) OR LOCATE(:tel_nom,tel_nom)) AND (type!='no_dinamic_data0' OR type IS NULL) ORDER BY tel_nom ASC, id ASC");
		$s->bindValue(":tel_nom", $_POST["tel_nom"]);
	} else if ($_POST["attention_id"]==-1) {
		$s=$db->prepare("SELECT id, tel_nom, type, txt, operator, date_time FROM clients.attentions WHERE tel_nom=:tel_nom AND done=0 AND (type!='no_dinamic_data0' OR type IS NULL) ORDER BY date_time DESC LIMIT 1");
		$s->bindValue(":tel_nom", $_POST["tel_nom"]);
	} else {
		$s=$db->prepare("SELECT id, tel_nom, type, txt, operator, date_time FROM clients.attentions WHERE id=:id AND done=0 AND (type!='no_dinamic_data0' OR type IS NULL)");
		$s->bindValue(":id", $_POST["attention_id"]);
	}

	$s->execute();
	$result=$s->fetchAll(PDO::FETCH_ASSOC);

	echo json_encode($result);
?>