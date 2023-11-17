<?php
	require("../../pdo_db_connect.php");

	/*if ($_POST["date"]=="") {
		$_POST["date"]=date("Y-m-d", time());
	}*/

	if ($_POST["from_dinamic"]==1) {//Вызов со страницы номера в динамике
		if ($_POST["show_all"]==0) {
			$s=$db->prepare("SELECT * FROM clients.attentions WHERE tel_nom=:tel_nom AND done=:done ORDER BY id DESC LIMIT 500");
			$s->bindValue(":tel_nom",$_POST["tel_nom"]);
			$s->bindValue(":done",$_POST["done"]);
		} else {
			$s=$db->prepare("SELECT * FROM clients.attentions WHERE tel_nom=:tel_nom ORDER BY id DESC LIMIT 500");
			$s->bindValue(":tel_nom",$_POST["tel_nom"]);
		}
	} else {//Вызов из админки
		$str=Array();

		if (strlen($_POST["txt"])>0) {
			$str[]="LOCATE('".$_POST["txt"]."', txt)";
		}

		if (strlen($_POST["date"])>0) {
			$str[]="`date`='".$_POST["date"]."'";
		}

		if (strlen($_POST["tel_nom"])>0) {
			$str[]="LOCATE('".$_POST["tel_nom"]."', tel_nom)";
		}

		if ($_POST["done"]!="all") {
			$str[]="done=".$_POST["done"];
		}

		if ($_POST["operator"]!="all") {
			$str[]="operator='".$_POST["operator"]."'";
		}

		if ($_POST["user"]!="all") {
			$str[]="user='".$_POST["user"]."'";
		}

		$arr=implode(" AND ", $str);

		if (count($str)>0) {//Если есть хотя бы один фильтр
			$s=$db->prepare("SELECT * FROM clients.attentions WHERE ".$arr." ORDER BY id DESC LIMIT 250");
		} else {
			$s=$db->prepare("SELECT * FROM clients.attentions ORDER BY id DESC LIMIT 250");
		}
	}

	$s->execute();

	$res=$s->fetchAll(PDO::FETCH_ASSOC);

	echo json_encode($res);
?>