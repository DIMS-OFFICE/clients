<?php
	$dir=realpath(dirname(__FILE__)."/../..");

    require($dir."/php/pdo_db_connect.php");

    $s_1s=$db->prepare("TRUNCATE clients.1s");
    $s_1s->execute();

    $s_payments=$db->prepare("TRUNCATE clients.payments");
    $s_payments->execute();

    $s_1s=$db->prepare("INSERT INTO clients.1s (tel_nom, `date`, year, month, summ, diff) (SELECT tel_nom, `date`, year, month, summ, diff FROM clients.1s_history WHERE revision=:revision)");
   	$s_1s->bindValue(":revision", $_POST["revision"]);
    $s_1s->execute();

    $s_payments=$db->prepare("INSERT INTO clients.payments (account, tel_nom, summ, payment_date, payment_time, source, append_time, removed, remove_time, operator) (SELECT account, tel_nom, summ, payment_date, payment_time, source, append_time, removed, remove_time, operator FROM clients.payments_history WHERE revision=:revision)");
    $s_payments->bindValue(":revision", $_POST["revision"]);
    $s_payments->execute();

    if ($s_1s->rowCount()>0 && $s_payments->rowCount()>0) {
    	echo "OK";
    } else {
    	echo "error";
    }
?>