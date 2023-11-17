<?php
	require("pdo_db_connect.php");

	$s=$db->prepare("SELECT summ, payment_date FROM clients.payments WHERE id=:id");
	$s->bindValue(":id", $_POST["id"]);
	$s->execute();

	$payment=$s->fetch(PDO::FETCH_ASSOC);

	if ($_POST["remove_from_db"]==0) {
		$s_remove=$db->prepare("UPDATE clients.payments SET removed=1, remove_time=NOW() WHERE id=:id");
	} else {
		$s_remove=$db->prepare("DELETE FROM clients.payments WHERE id=:id");
	}

	$s_remove->bindValue(":id", $_POST["id"]);
	$s_remove->execute();

	if ($_POST["already_canceled"]==0) {
		if ($s->rowCount()>0) {
			$s=$db->prepare("UPDATE clients.history SET balance=balance-:summ, payments=payments-:summ WHERE tel_nom=:tel_nom AND update_date=:update_date");
			$s->bindValue(":tel_nom", $_POST["tel_nom"]);
			$s->bindValue(":update_date", $payment["payment_date"]);
			$s->bindValue(":summ", $payment["summ"]);
			$s->execute();

			$s1=$db->prepare("UPDATE clients.history SET balance=balance-:summ WHERE tel_nom=:tel_nom AND update_date>:update_date");
			$s1->bindValue(":tel_nom", $_POST["tel_nom"]);
			$s1->bindValue(":update_date", $payment["payment_date"]);
			$s1->bindValue(":summ", $payment["summ"]);
			$s1->execute();
		} else {
			echo "error";
			exit();
		}
	}

	if ($s_remove->rowCount()>0) {
		echo "OK";
	} else {
		echo "error";
	}
?>