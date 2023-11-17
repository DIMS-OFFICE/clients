<?php
	$dir=realpath(dirname(__FILE__) . '/..');

	$file_name=$_FILES['userfile']["name"];

	$parts=explode(".", $file_name);

	$ext=$parts[count($parts)-1];

	if (move_uploaded_file($_FILES['userfile']['tmp_name'], $dir."/caller/phones.".$ext)) {

		if ($ext=="xls") {
			exec("LANG=ru_RU.UTF-8; xls2csv ".$dir."/caller/phones.".$ext." > ".$dir."/caller/phones.txt");
		} else if ($ext=="xlsx") {
			exec("xlsx2csv ".$dir."/caller/phones.".$ext." --delimiter ';' > ".$dir."/caller/phones.txt");
		} else if ($ext=="csv") {
			copy($dir."/caller/phones.".$ext, $dir."/caller/phones.txt");
		}

		if ($ext!="txt") {
			unlink ($dir."/caller/phones.".$ext);
		}

	    $out = "Файл загружен";
	} else {
	    $out = "Ошибка загрузки файла";
	}

	echo $out;
?>