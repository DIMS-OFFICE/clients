<?php
	$token="AQAAAAAoerkPAAUeFjZ7-f1lJUOnjbHKr3Rwo6E";
	$db_pass="DiMS-21093@";

	$dir=realpath(dirname(__FILE__) . '/..');
	$temp_dump_dir="/var/www/ofis.dims.rf/data/www/xn--h1alkk.xn--d1aimu.xn--p1ai/temp_dump";

	require($dir."/php/pdo_db_connect.php");
	
	print_log($db, "Начало создания дампа КЧ");

	//Получаем путь сохранения дампа из БД
	$s_pathes=$db->prepare("SELECT path_value FROM pathes WHERE path_type='dump_path'");
	$s_pathes->execute();
	$pathes=$s_pathes->fetch(PDO::FETCH_ASSOC);
	
	//Архивируем скрипты и кладём их во временную папку для дампа
	$elem=scandir($dir);

	foreach ($elem as $e) {
		if ($e!="." && $e!=".." && $e!="temp" && $e!="temp_dump" && $e!="" && $e!="*") {
			if (is_dir($dir."/".$e)) {
				echo "THIS IS DIR\n";
				system("sudo tar -czvf ".$temp_dump_dir."/clients-".$e.".tar.gz ".$dir."/".$e);
			} else {
				echo "THIS IS FILE\n";
				system("gzip -c -9 ".$dir."/".$e." > ".$temp_dump_dir."/clients-".$e.".gz");
			}
		}
	}

	//Дампим БД.
	$s=$db->prepare("SHOW TABLES FROM clients");
	$s->execute();
	$tbls = $s->fetchAll(PDO::FETCH_ASSOC);

	foreach ($tbls as $tbl) {
		exec("mysqldump -u root -p".$db_pass." clients ".$tbl["Tables_in_clients"]." | gzip -9 > ".$temp_dump_dir."/clients-".$tbl["Tables_in_clients"].".sql.gz");
	}

	//Хранимые процедуры БД
	exec("mysqldump -u root -p".$db_pass." --routines --no-create-info --no-data --no-create-db --skip-opt clients | gzip -9 > ".$temp_dump_dir."/clients-routines.sql.gz");
	
	require($dir."/php/pdo_db_connect.php");
	
	print_log($db, "Окончание создания дампа КЧ");

	function print_log($db, $txt) {
		$s=$db->prepare("INSERT INTO logs (txt, script_name) VALUES (:txt, :script_name)");
		$s->bindValue(":txt", $txt);
		$s->bindValue(":script_name", $_SERVER['SCRIPT_NAME']);
		$s->execute();
	}
?>