<?php
	$test = getopt("t:");

	if (PHP_SAPI === 'cli' && $test["t"]!="test") {//Запуск вручную
		$s=$db->prepare("SELECT `on` FROM cron_jobs WHERE LOCATE(:script, command)");
		$s->bindValue(":script", $_SERVER ["SCRIPT_FILENAME"]);
		$s->execute();

		$on=$s->fetch(PDO::FETCH_COLUMN);

		if ($on==0) {
			echo "ЗАДАЧА ВЫКЛЮЧЕНА В ПЛАНИРОВЩИКЕ!!!\n";
			exit();
		}
	}
?>