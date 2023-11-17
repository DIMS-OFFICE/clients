<?php
	require("../../php/pdo_db_connect.php");

	$s=$db->prepare("SELECT tel_nom FROM clients.clients WHERE tel_nom=:tel_nom");
	$s->bindValue(":tel_nom", $_POST["tel_nom"]);

	$s->execute();

	if ($s->rowCount()==0) {
		$res=Array(
			"status" => "error",
			"desc" => "Данный номер не зарегистрирован в ДиМС"
		);

		echo json_encode($res);
	} else {
		$s=$db->prepare("SELECT email FROM clients.clients_logins WHERE tel_nom=:tel_nom");
		$s->bindValue(":tel_nom", $_POST["tel_nom"]);

		$s->execute();

		if ($s->rowCount()==0) {
			$res=Array(
				"status" => "error",
				"desc" => "Данный номер не зарегистрирован. Пройдите процедуру регистрации"
			);

			echo json_encode($res);
		} else {
			$data=$s->fetch(PDO::FETCH_ASSOC);

			//mail($data["email"], 'Восстановление пароля ДиМС', 'Ваш пароль: '.$data["password"]);

			$new_password=generateRandomString();

			$s=$db->prepare("UPDATE clients.clients_logins SET password=:password WHERE tel_nom=:tel_nom");
			$s->bindValue(":password", md5($new_password));
			$s->bindValue(":tel_nom", $_POST["tel_nom"]);

			$s->execute();

			if ($s->rowCount()>0) {
				send_mime_mail('Дистанция мобильной связи',
	               'admin@dims.ru',
	               '',
	               $data["email"],
	               'UTF-8',  // кодировка, в которой находятся передаваемые строки
	               'KOI8-R', // кодировка, в которой будет отправлено письмо
	               'Восстановление пароля ДиМС',
	               'Ваш новый пароль: '.$new_password."\nВы можете поменять его в личном кабинете");

				$res=Array(
					"status" => "ОК",
					"desc" => "Пароль отправлен"
				);

				echo json_encode($res);
			} else {
				$res=Array(
					"status" => "error",
					"desc" => "Ошибка восстановления пароля"
				);

				echo json_encode($res);
			}
		}
	}

	function generateRandomString($length = 7) {
	    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	    $charactersLength = strlen($characters);
	    $randomString = '';
	    for ($i = 0; $i < $length; $i++) {
	        $randomString .= $characters[rand(0, $charactersLength - 1)];
	    }
	    return $randomString;
	}

	function send_mime_mail($name_from, // имя отправителя
	                        $email_from, // email отправителя
	                        $name_to, // имя получателя
	                        $email_to, // email получателя
	                        $data_charset, // кодировка переданных данных
	                        $send_charset, // кодировка письма
	                        $subject, // тема письма
	                        $body, // текст письма
	                        $html = FALSE, // письмо в виде html или обычного текста
	                        $reply_to = FALSE
	                        ) {
	  $to = mime_header_encode($name_to, $data_charset, $send_charset)
	                 . ' <' . $email_to . '>';
	  $subject = mime_header_encode($subject, $data_charset, $send_charset);
	  $from =  mime_header_encode($name_from, $data_charset, $send_charset)
	                     .' <' . $email_from . '>';
	  if($data_charset != $send_charset) {
	    $body = iconv($data_charset, $send_charset, $body);
	  }
	  $headers = "From: $from\r\n";
	  $type = ($html) ? 'html' : 'plain';
	  $headers .= "Content-type: text/$type; charset=$send_charset\r\n";
	  $headers .= "Mime-Version: 1.0\r\n";
	  if ($reply_to) {
	      $headers .= "Reply-To: $reply_to";
	  }
	  return mail($to, $subject, $body, $headers);
	}

	function mime_header_encode($str, $data_charset, $send_charset) {
	  if($data_charset != $send_charset) {
	    $str = iconv($data_charset, $send_charset, $str);
	  }
	  return '=?' . $send_charset . '?B?' . base64_encode($str) . '?=';
	}	
?>