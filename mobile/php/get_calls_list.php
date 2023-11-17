<?php
		require("../../php/pdo_db_connect.php");

		$s=$db->prepare("SELECT id FROM clients.clients_logins WHERE hash=:hash");
		$s->bindValue(":hash", $_POST["hash"]);

		$s->execute();

		if ($s->rowCount()==0) {
			$res=Array(
				"status" => "error",
				"desc" => "wrong_hash"
			);

			echo json_encode($res);

			exit();
		} else {
			$id=$s->fetch(PDO::FETCH_COLUMN);

			$s=$db->prepare("UPDATE clients.clients_logins SET last_activity=NOW() WHERE id=:id");
			$s->bindValue(":id", $id);

			$s->execute();
		}

		$call_date=$_POST["year"]."-".addZero($_POST["month"])."-".addZero($_POST["day"]);

		if ($_POST["operator"]=="bee") {
			$s=$db->prepare("SELECT tel_nom, call_date, call_time, from_number, to_number, service, call_type, call_length, value FROM ".$_POST["operator"]."_detal WHERE call_date=:call_date AND tel_nom=:tel_nom ORDER BY call_time DESC");
		} else if ($_POST["operator"]=="mts") {
			$s=$db->prepare("SELECT tel_nom, call_date, call_time, from_number, to_number, service_type as call_type, call_length call_length, value, direction FROM ".$_POST["operator"]."_detal WHERE call_date=:call_date AND tel_nom=:tel_nom ORDER BY call_time DESC");
		} else {
			$s=$db->prepare("SELECT tel_nom, call_date, call_time, from_number, to_number, service, call_type, call_length, value, direction FROM ".$_POST["operator"]."_detal WHERE call_date=:call_date AND tel_nom=:tel_nom ORDER BY call_time DESC");
		}

		$s->bindValue(":tel_nom", $_POST["tel_nom"]);
		$s->bindValue(":call_date", $call_date);
		$s->execute();

		$calls=$s->fetchAll(PDO::FETCH_ASSOC);

		$s_exceptions=$db->prepare("SELECT exception FROM clients.exceptions");
		$s_exceptions->execute();
		$exceptions=$s_exceptions->fetchAll(PDO::FETCH_COLUMN);

		$s=$db->prepare("SELECT CONCAT(call_type,'-',service) as txt, type, type_nom, items_count, items_summa, unit_price FROM call_types WHERE operator=:operator");
		$s->bindValue(":operator", $_POST["operator"]);
		$s->execute();
		$call_types=$s->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);

		if ($_POST["operator"]=="mts") {
			$cols="service_type as call_type, service";
		} else {
			$cols="call_type, service";
		}

		$s=$db->prepare("SELECT call_type, `date`, price FROM clients.call_types_prices WHERE operator=:operator GROUP BY call_type, `date` ORDER BY call_type ASC, `date` ASC");
		$s->bindValue(":operator", $_POST["operator"]);
		$s->execute();

		$call_types_prices=$s->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);

		//Коэффициенты типов вызовов
		$s=$db->prepare("SELECT call_type, kef FROM clients.call_types_kefs WHERE tel_nom=:tel_nom");
		$s->bindValue(":tel_nom", $_POST["tel_nom"]);
		$s->execute();

		$call_types_kefs=$s->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);

		//Изменённые цены вызовов
		$s=$db->prepare("SELECT CONCAT(call_date, '*', call_time, '*', phone, '*', call_type, '*', service) as txt, unit_price FROM clients.edited_calls_prices WHERE tel_nom=:tel_nom AND call_date BETWEEN :from_date AND :to_date");
		$s->bindValue(":tel_nom", $_POST["tel_nom"]);
		$s->bindValue(":from_date", $from_date);
		$s->bindValue(":to_date", $to_date);
		$s->execute();

		$edited_calls_prices=$s->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);

		//Изменённые периоды
		$s=$db->prepare("SELECT CONCAT(call_date, '*', call_time, '*', phone, '*', call_type, '*', service) as txt, year_edited, month_edited FROM clients.edited_calls_periods WHERE tel_nom=:tel_nom AND call_date BETWEEN :from_date AND :to_date");
		$s->bindValue(":tel_nom", $_POST["tel_nom"]);
		$s->bindValue(":from_date", $from_date);
		$s->bindValue(":to_date", $to_date);
		$s->execute();

		$edited_calls_periods=$s->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);

		$s=$db->prepare("SELECT service_code, start_date, finish_date FROM clients.services_history WHERE tel_nom=:tel_nom GROUP BY service_code, `start_date`");
		$s->bindValue(":tel_nom", $_POST["tel_nom"]);
		$s->execute();

		$number_services_settings["services_history"]=$s->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);

		$call_length=0;

		$calls_result["calls"]=Array();

		$call_nom=0;

		foreach ($calls as $call) {
			$phone="";

			if ($_POST["operator"]=="bee") {
				if ($call["from_number"]==$call["tel_nom"] || "7".$call["from_number"]==$call["tel_nom"]) {
					$phone=$call["to_number"];
					$call["direction"]="O";
				} else if ($call["to_number"]==$call["tel_nom"] || "7".$call["to_number"]==$call["tel_nom"]) {
					$phone=$call["from_number"];
					$call["direction"]="I";
				} else if ($call["to_number"]=="") {
					$phone=$call["from_number"];
					$call["direction"]="I";
				} else if ($call["from_number"]=="") {
					$phone=$call["to_number"];
					$call["direction"]="O";
				} else {
					$phone=$call["from_number"];
					$call["direction"]="I";
				}
			} else {
				if ($call["from_number"]==$call["tel_nom"]) {
					$phone=$call["to_number"];
				}
				if ($call["to_number"]==$call["tel_nom"]) {
					$phone=$call["from_number"];
				}
			}

			if ($_POST["operator"]=="bee") {
				if (stripos($call["service"], "интернет")!==false) {
					$value=$call["value"];
				} else if (stripos($call["service"], "сообщения")===false) {
					$parts=explode(":", $call["call_length"]);

					$value=ceil((intval($parts[0])*60*60+intval($parts[1])*60+intval($parts[2]))/60);
				} else {
					$value=1;
				}

				if ($value==0) {
					$value=1;
				}

				$value=number_format($value, 2, ",", "");
				
				$call["unit"]="";
				$call["service_provider"]="";
				$call["gmt"]="";
			} else if ($_POST["operator"]=="mts") {
				if ($call["value"]<0) {
					$value=ceil($call["call_length"]);
				} else {
					if ($call["unit"]=="секунда") {
						$value=$call["call_length"];
						$call["call_length"]=$call["value"];
					} else {
						$value=$call["value"];
					}
				}

				if ($call["unit"]=="байт") {
					$call["unit"]="килобайт";
				} else {
					$call["unit"]=$call["unit"];
				}
			} else if ($_POST["operator"]=="meg") {
				if (stripos($call["unit"], "Секунд")!==false) {
					$value=ceil($call["call_length"]/60);
				} else {
					$value=$call["value"];
				}

				$value=number_format($value, 2, ".", "");
				
				$call["gmt"]="";
			} else if ($_POST["operator"]=="tele2") {
				if (stripos($call["call_type"], "Internet")!==false) {
					$value=ceil($call["value"]/1024);
				} else if (stripos($call["call_type"], "SMS")===false) {
					$parts=explode(":", $call["call_length"]);
					
					$value=ceil((intval($parts[0])*60*60+intval($parts[1])*60+intval($parts[2]))/60);
				} else {
					$value=1;
				}

				if ($value==0) {
					$value=1;
				}

				$call["gmt"]="";
			}

			$ct="";

			if (isset($call_types[$call['call_type']."-".$call['service']])) {
				$index=$call["call_date"]."*".$call["call_time"]."*".$phone."*".$call["call_type"]."*".$call["service"];

				if ($_POST["operator"]=="mts") {
					$ct=$call_types[$call['call_type']."-".$call['service']][0]['type'];

					if (is_payable($call, $phone, $ct, $_POST["operator"])==true || $ct=="SMS-900" || isset($edited_calls_prices[$index])) {
						$unit_price=get_unit_price($index, $call, $phone, $ct, $_POST["operator"]);
					}

					if (stripos($ct,"GPRS")!==false) {
						$call_length=call_length_calc($call);
					} else {
						$call_length=eval("return {$call_types[$call['call_type']."-".$call['service']][0]['items_count']};");
					}
				} else {
					$ct=$call_types[$call['call_type']."-".$call['service']][0]['type'];

					if (is_payable($call, $phone, $ct, $_POST["operator"])==true || $ct=="SMS-900" || isset($edited_calls_prices[$index])) {
						$unit_price=get_unit_price($index, $call, $phone, $ct, $_POST["operator"]);
					}

					$call_length=eval("return {$call_types[$call['call_type']."-".$call['service']][0]['items_count']};");
				}	
			}

			$item_price=$unit_price["value"]*$call_length;

			$edited_period=get_edited_period($call, $phone, $ct, $_POST["operator"]);

			$calls_result["calls"][$call_nom]=Array(
				"call_time" => $call["call_time"],
				"phone" => $phone,
				"direction" => $call["direction"],
				"call_length" => $call["call_length"],
				"price" => number_format($item_price, 2, '.', '')
			);

			$call_nom++;
		}

		echo json_encode($calls_result);

		function is_payable($call, $phone, $call_type, $operator) {
			global $number_services_settings;
			global $exceptions;
			global $edited_calls_prices;
			global $codes;

			if (is_in_exceptions($call["from_number"], $call["to_number"])) {
				return false;
			}

			$call_date=strtotime($call["call_date"]);
			
			$available_services=Array();

			foreach ($number_services_settings["services_history"] as $service_code => $service_date_items) {
				foreach ($service_date_items as $key => $service_date_item) {
					$start_date=strtotime($service_date_item["start_date"]);
					$finish_date=strtotime($service_date_item["finish_date"]);

					if ($start_date<=$call_date && $finish_date>=$call_date) {
						$available_services[]=$service_code;
					}
				}
			}

			$g1000=false;
			$sms500=false;
			$svoya_set=false;
			$bl_minut=false;
			$l_roaming=false;
			$g_roaming=false;

			if (count($available_services)) {
				//G1000, G3000, БЛ интернет или GPRS БЛ
				$codes=Array(
					"mts" => Array(7, 8, 25, 30),
					"bee" => Array(36, 41, 37, 45),
					"meg" => Array(51, 56, 59, 60),
					"tele2" => Array(66, 71, 67, 75)
				);

				foreach ($codes[$operator] as $code) {
					if (in_array($code, $available_services)!==false) {
						$g1000=true;
						break;
					}
				}

				//G-роуминг
				$codes=Array(
					"mts" => Array(83),
					"bee" => Array(),
					"meg" => Array(),
					"tele2" => Array()
				);

				foreach ($codes[$operator] as $code) {
					if (in_array($code, $available_services)!==false) {
						$g_roaming=true;
						break;
					}
				}

				//SMS 300, SMS 500 или 1000 SMS
				$codes=Array(
					"mts" => Array(9, 31, 82),
					"bee" => Array(38, 46),
					"meg" => Array(53, 61),
					"tele2" => Array(68, 76)
				);

				foreach ($codes[$operator] as $code) {
					if (in_array($code, $available_services)!==false) {
						$sms500=true;
						break;
					}
				}

				//Своя сеть
				$codes=Array(
					"mts" => Array(5),
					"bee" => Array(34),
					"meg" => Array(49),
					"tele2" => Array(64)
				);

				foreach ($codes[$operator] as $code) {
					if (in_array($code, $available_services)!==false) {
						$svoya_set=true;
						break;
					}
				}

				//БЛ Минут
				$codes=Array(
					"mts" => Array(29),
					"bee" => Array(44),
					"meg" => Array(59),
					"tele2" => Array(74)
				);

				foreach ($codes[$operator] as $code) {
					if (in_array($code, $available_services)!==false) {
						$bl_minut=true;
						break;
					}
				}
			}


			$result=true;

			$index=$call["call_date"]."*".$call["call_time"]."*".$phone."*".$call["call_type"]."*".$call["service"];

			if (isset($edited_calls_prices[$index]) && $edited_calls_prices[$index]>0) {
				$result=true;
			} else if (strpos($call_type,"ВХ")!==false) {
				$result=false;
			} else if (strpos($call_type,"РОУМ-GPRS")!==false) {
				if ($g_roaming==true) {
					return false;
				} else {
					return true;
				}
			} else if (strpos($call_type,"GPRS")!==false) {
				if ($g1000==true) {//Если подключены G1000, G3000, БЛ интернет или GPRS БЛ, то не бесплатный
					$result=false;
				}
			} else if (strpos($call_type,"SMS")!==false) {
				if ($sms500==true) {//Если подключено SMS 500 или 1000 SMS, то бесплатный
					$result=false;
				}
			} else if ($svoya_set==true) {//Если подкючена Своя сеть
				if (stripos($call_type,"ИСХ-ВСР")!==false || stripos($call_type,"ИСX-ЗГП")!==false || strpos($call_type,"ПЕР-ВСР")!==false || strpos($call_type,"ПЕР-ЗГП")!==false) {
					$result=false;
				} else {
					$result=true;
				}
			} else if ($bl_minut==true) {//Если подкючены БЛ минуты
				if (strpos($call_type,"ИСХ-ВСР")!==false || strpos($call_type,"ИСX-ЗГП")!==false || strpos($call_type,"ИСХ-СРДО")!==false) {
					$result=false;
				} else {
					$result=true;
				}
			} else {
				$result=true;
			}

			//echo "RES:".$result."(".$call_type."):".date("Y-m-d",$call_date).":".implode(",",$available_services)."\n";

			return $result;
		}

		function is_in_exceptions($from_number, $to_number) {
			global $exceptions;

			if ($from_number!=="") {
				if (in_array($from_number, $exceptions)) {
					return true;
				} else if (in_array("7".$from_number, $exceptions)) {
					return true;
				} else if (in_array(substr($from_number,1), $exceptions)) {
					return true;
				}
			}

			if ($to_number!=="") {
				if (in_array($to_number, $exceptions)) {
					return true;
				} else if (in_array("7".$to_number, $exceptions)) {
					return true;
				} else if (in_array(substr($to_number,1), $exceptions)) {
					return true;
				}
			}

			return false;
		}

		function get_edited_period($call, $phone, $ct, $operator) {
			global $edited_calls_periods;

			$index=$call["call_date"]."*".$call["call_time"]."*".$phone."*".$call["call_type"]."*".$call["service"];

			if (isset($edited_calls_periods[$index])) {
				$edited_period["year_edited"]=$edited_calls_periods[$index][0]["year_edited"];
				$edited_period["month_edited"]=$edited_calls_periods[$index][0]["month_edited"];
			} else {
				$edited_period["year_edited"]=0;
				$edited_period["month_edited"]=0;
			}

			return $edited_period;
		}

		function get_unit_price($index, $call, $phone, $ct, $operator) {
			global $call_types_prices;
			global $edited_calls_prices;
			global $call_types_kefs;

			//print_r($edited_calls_prices);
			
			//echo $index."\n";

			$unit_price["type"]="nominal";
			$unit_price["value"]=0;
			$unit_price["old_value"]="";

			if (isset($edited_calls_prices[$index])) {
				$unit_price["type"]="edited";
				$unit_price["value"]=$edited_calls_prices[$index][0]["unit_price"];
			}

			if (isset($call_types_prices[$ct])) {
				for ($ii=0;$ii<count($call_types_prices[$ct]);$ii++) {
					if (strtotime($call["call_date"])>=strtotime($call_types_prices[$ct][$ii]["date"])) {
						if ($unit_price["type"]=="nominal") {
							$unit_price["value"]=round($call_types_prices[$ct][$ii]["price"],2);
						} else {
							$unit_price["old_value"]=round($call_types_prices[$ct][$ii]["price"],2);
						}
					}
				}
			} else {
				if ($unit_price["type"]=="nominal") {
					$unit_price["value"]=0;
				} else {
					$unit_price["old_value"]=0;
				}
			}

			if (isset($call_types_kefs[$ct])) {
				$unit_price["value"]=$unit_price["value"]*$call_types_kefs[$ct][0]["kef"];
			}

			return $unit_price;
		}

	    function addZero($num) {
	    	if ($num<10) {
	    		return "0".$num;
	    	} else {
	    		return $num;
	    	}
	    }
?>