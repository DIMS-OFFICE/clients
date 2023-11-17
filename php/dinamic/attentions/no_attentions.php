<?php
	require("../../pdo_db_connect.php");

	if ($_POST["delete"]=="delete") {
		$s=$db->prepare("SELECT type FROM clients.no_attentions WHERE id=:id");
		$s->bindValue(":id", $_POST["no_attention_id"]);
		$s->execute();

		$type=$s->fetch(PDO::FETCH_COLUMN);

		if ($type=="no_active_ls") {
			$s=$db->prepare("SELECT PhoneNumber FROM ".$_POST["operator"]."_phones WHERE AccountNumber=(SELECT AccountNumber FROM ".$_POST["operator"]."_phones WHERE PhoneNumber=:tel_nom) AND status=0");
			$s->bindValue(":tel_nom", $_POST["tel_nom"]);
			$s->execute();
			$tel_noms=$s->fetchAll(PDO::FETCH_COLUMN);

			$tel_noms=implode(",", $tel_noms);

			$s=$db->prepare("DELETE FROM clients.no_attentions WHERE tel_nom IN (".$tel_noms.") AND type='no_active_ls'");
			$s->execute();
		} else {
			$s=$db->prepare("DELETE FROM clients.no_attentions WHERE id=:id");
			$s->bindValue(":id", $_POST["no_attention_id"]);
			$s->execute();
		}

		if ($s->rowCount()>0) {
			echo "OK";
		} else {
			echo "error 1";
		}

		exit();
	}
	
	if ($_POST["type"]=="no_active_ls") {
		if ($_POST["operator"]=="meg") {
			$_POST["operator"]="megafon";
		}

		$s=$db->prepare("SELECT PhoneNumber FROM ".$_POST["operator"]."_phones WHERE AccountNumber=(SELECT AccountNumber FROM ".$_POST["operator"]."_phones WHERE PhoneNumber=:tel_nom) AND status=0");
		$s->bindValue(":tel_nom", $_POST["tel_nom"]);
		$s->execute();

		$tel_noms=$s->fetchAll(PDO::FETCH_COLUMN);

		$s=$db->prepare("SELECT id FROM clients.no_attentions WHERE tel_nom=:tel_nom AND type=:type");
		$s1=$db->prepare("INSERT INTO no_attentions (tel_nom, until_date, type) VALUES (:tel_nom, :until_date, :type)");
		$s2=$db->prepare("UPDATE no_attentions SET until_date=:until_date WHERE tel_nom=:tel_nom AND type=:type");

		foreach ($tel_noms as $tel_nom) {
			$s->bindValue(":tel_nom", $tel_nom);
			$s->bindValue(":type", $_POST["type"]);
			$s->execute();

			if ($s->rowCount()==0) {
				$s1->bindValue(":tel_nom", $tel_nom);
				$s1->bindValue(":until_date", $_POST["date"]);
				$s1->bindValue(":type", $_POST["type"]);
				$s1->execute();
			} else {
				$s2->bindValue(":tel_nom", $tel_nom);
				$s2->bindValue(":until_date", $_POST["date"]);
				$s2->bindValue(":type", $_POST["type"]);
				$s2->execute();
			}
		}

		if ($s1->rowCount()>0 || $s2->rowCount()>0) {
			echo "OK";
		} else {
			echo "error 2";
		}
	} else {
		if ($_POST["date"]==date("Y-m-d")) {
			$s=$db->prepare("DELETE FROM clients.no_attentions WHERE tel_nom=:tel_nom AND type='g_append'");
			$s->bindValue(":tel_nom", $_POST["tel_nom"]);
			$s->execute();

			if ($s->rowCount()>0) {
				echo "OK";
			} else {
				echo "error 3";
			}
		
			exit();
		}

		$s=$db->prepare("SELECT id FROM clients.no_attentions WHERE tel_nom=:tel_nom AND type=:type");
		$s->bindValue(":tel_nom", $_POST["tel_nom"]);
		$s->bindValue(":type", $_POST["type"]);
		$s->execute();

		if ($s->rowCount()==0) {
			$s1=$db->prepare("INSERT INTO clients.no_attentions (tel_nom, until_date, type) VALUES (:tel_nom, :until_date, :type)");
			$s1->bindValue(":tel_nom", $_POST["tel_nom"]);
			$s1->bindValue(":until_date", $_POST["date"]);
			$s1->bindValue(":type", $_POST["type"]);
			$s1->execute();
		} else {
			$s1=$db->prepare("UPDATE clients.no_attentions SET until_date=:until_date WHERE tel_nom=:tel_nom AND type=:type");
			$s1->bindValue(":tel_nom", $_POST["tel_nom"]);
			$s1->bindValue(":until_date", $_POST["date"]);
			$s1->bindValue(":type", $_POST["type"]);
			$s1->execute();
		}

		if ($s1->rowCount()>0) {
			echo "OK";
		} else {
			echo "error 4";
		}
	}
?>