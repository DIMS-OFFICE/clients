<?php
	date_default_timezone_set("Asia/Vladivostok");

	$res = shell_exec("ps -ef | grep -v grep | grep whatsapp_bot.php | wc -l");

	if ($res>3 && isset($_POST["tel_nom"])==false) {
		exit();
	}

	$dir=realpath(dirname(__FILE__) . '/../..');

	require($dir."/php/pdo_db_connect.php");
	require($dir."/php/cron_job_check.php");

	require './vendor/autoload.php';

	$greenApi = new GreenApi\RestApi\GreenApiClient(1101825572, "0aa356e52ece4d8ea40d557578ce2da10d2ebcfa7d274a50ac");

	$greenApi->webhooks->startReceivingNotifications(function($typeWebhook, $body) {
		global $db;
		global $greenApi;

		echo $typeWebhook."\n";

		if ($typeWebhook == 'incomingMessageReceived') {
			$tel_nom=onIncomingMessageReceived($body);

			$s=$db->prepare("SELECT balance, spended FROM clients.clients WHERE tel_nom=:tel_nom GROUP BY tel_nom");
			$s->bindValue(":tel_nom", $tel_nom);
			$s->execute();

			if ($s->rowCount()>0) {
				$result=$s->fetch(PDO::FETCH_ASSOC);

				$txt="Начислено: ".$result["spended"]."\n";
				$txt.="Текущий баланс: ".$result["balance"]."\nС уважением,\nДистанция Мобильной Связи";
			} else {
				$txt="Ваш номер в системе не зарегистрирован\nС уважением,\nДистанция Мобильной Связи";
			}

			echo $txt."\n";

			$result = $greenApi->sending->sendMessage($tel_nom.'@c.us', $txt);
	
			print_r($result);

			if ($result->code==200) {
				print_log($db, "Запрос баланса. Номер: ".$tel_nom.". Статус запроса: OK");
			} else {
				print_log($db, "Запрос баланса. Номер: ".$tel_nom.". ".$result);
			}
		} elseif ($typeWebhook == 'deviceInfo') {
			onDeviceInfo($body);
		} elseif ($typeWebhook == 'incomingCall') {
			onIncomingCall($body);
		} elseif ($typeWebhook == 'outgoingAPIMessageReceived') {
			onOutgoingAPIMessageReceived($body);
		} elseif ($typeWebhook == 'outgoingMessageReceived') {
			//onOutgoingMessageReceived($body);
		} elseif ($typeWebhook == 'outgoingMessageStatus') {
			onOutgoingMessageStatus($body);
		} elseif ($typeWebhook == 'stateInstanceChanged') {
			onStateInstanceChanged($body);
		} elseif ($typeWebhook == 'statusInstanceChanged') {
			onStatusInstanceChanged($body);
		}
	});

	function onIncomingMessageReceived($body) {
		print_r($body);

		$idMessage = $body->idMessage;
		$eventDate = date('Y-m-d H:i:s', $body->timestamp);
		$tel_nom = str_replace("@c.us", "", $body->senderData->sender);
		$messageData =  $body->messageData->textMessageData->textMessage;

		echo "TEL_NOM: ".$tel_nom."\n";
		echo "TIME: ".$eventDate."\n";
		echo "messageData: ".$messageData."\n";

		return $tel_nom;
	}

	function onIncomingCall($body) {
		$idMessage = $body->idMessage;
		$eventDate = date('Y-m-d H:i:s', $body->timestamp);
		$fromWho = $body->from;
		print($idMessage . ': Call from ' . $fromWho . ' at ' . $eventDate).PHP_EOL;
	}

	function onOutgoingAPIMessageReceived($body) {
		$idMessage = $body->idMessage;
		$eventDate = date('Y-m-d H:i:s', $body->timestamp);
		$senderData = $body->senderData;
		$messageData =  $body->messageData;
		print($idMessage . ': At ' . $eventDate . ' Incoming from '. json_encode($senderData, JSON_UNESCAPED_UNICODE) . ' message = ' . json_encode($messageData, JSON_UNESCAPED_UNICODE)).PHP_EOL;
	}

	function onDeviceInfo( $body ) {
		$eventDate = date('Y-m-d H:i:s', $body->timestamp);
		$deviceData = $body->deviceData;
		print('At ' . $eventDate . ': ' . json_encode($deviceData, JSON_UNESCAPED_UNICODE)).PHP_EOL;
	}

	function onOutgoingMessageReceived($body) {
		$idMessage = $body->idMessage;
		$eventDate = date('Y-m-d H:i:s', $body->timestamp);
		$tel_nom = str_replace("@c.us", "", $body->senderData->sender);
		$messageData =  $body->messageData->extendedTextMessageData->text;

		echo "TEL_NOM: ".$tel_nom."\n";
		echo "TIME: ".$eventDate."\n";
		echo "messageData: ".$messageData."\n";

		return $tel_nom;
	}

	function onOutgoingMessageStatus($body) {
		$idMessage = $body->idMessage;
		$status = $body->status;
		$eventDate = date('Y-m-d H:i:s', $body->timestamp);
		print($idMessage . ': At ' . $eventDate . ' status = ' . $status).PHP_EOL;
	}

	function onStateInstanceChanged($body) {
		$eventDate = date('Y-m-d H:i:s', $body->timestamp);
		$stateInstance = $body->stateInstance;
		print('At ' . $eventDate . ' state instance = ' . $stateInstance).PHP_EOL;
	}

	function onStatusInstanceChanged($body) {
		$eventDate = date('Y-m-d H:i:s', $body->timestamp);
		$statusInstance = $body->stateInstance;
		print('At ' . $eventDate . ' status instance = ' . $statusInstance).PHP_EOL;
	}


	function print_log($db, $txt) {
		$s=$db->prepare("INSERT INTO logs (txt, script_name) VALUES (:txt, :script_name)");
		$s->bindValue(":txt", $txt);
		$s->bindValue(":script_name", $_SERVER['SCRIPT_NAME']);
		$s->execute();
	}
?>