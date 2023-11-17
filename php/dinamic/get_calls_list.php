<?php
		ini_set("display_errors", 1);
		error_reporting(E_ALL);

		ini_set('memory_limit', '3000M');

		require("../pdo_db_connect.php");

		$from_date=$_POST["from_date"];
		$to_date=$_POST["to_date"];

		if ($_POST["to_excel"]==1 || $_POST["edited_calls"]==1 || $_POST["loss"]==1 || $_POST["filter"]=="no_subscribered") {
			$limit="";
		} else {
			$limit="LIMIT ".($_POST["page_nom"]*100).", 100";
		}

		if ($_POST["adresat"]=="пусто") {
			$_POST["adresat"]="";
			$adresat_filter="(from_number=:adresat OR to_number=:adresat)";
		} else {
			if (strlen($_POST["adresat"])>0 && is_numeric($_POST["adresat"])==false) {//Фильтр по адресату является строкой - ищем соответствующий номер
				$s=$db->prepare("SELECT tel_nom FROM notebook WHERE LOCATE(:adresat, note) LIMIT 1");
				$s->bindValue(":adresat", $_POST["adresat"]);
				$s->execute();

				if ($s->rowCount()>0) {
					if ($_POST["operator"]=="bee" || $_POST["operator"]=="bee+") {
						$_POST["adresat"]=substr($s->fetch(PDO::FETCH_COLUMN),1);
					} else {
						$_POST["adresat"]=$s->fetch(PDO::FETCH_COLUMN);
					}
				}
			}

			$adresat_filter="(LOCATE(:adresat, from_number) OR LOCATE(:adresat, to_number))";
		}

		$paying_filter=false;
		if (strpos($_POST["filter"],'PAYING')!==false) {
			$_POST["filter"]=str_replace("PAYING","1",$_POST["filter"]);
			$paying_filter=true;

			$_POST["from_date"]=$_POST["totals_year"]."-".addZero($_POST["totals_month"])."-01";
			$_POST["to_date"]=$_POST["totals_year"]."-".addZero($_POST["totals_month"])."-".cal_days_in_month(CAL_GREGORIAN, $_POST["totals_month"], $_POST["totals_year"]);
		
			$limit="";
		}

		$edited_call_prices_filter=false;
		if (strpos($_POST["filter"],'EDITED_CALL_PRICES')!==false) {
			$_POST["filter"]=str_replace("EDITED_CALL_PRICES","1",$_POST["filter"]);
			$edited_call_prices_filter=true;

			$_POST["from_date"]=$_POST["totals_year"]."-".addZero($_POST["totals_month"])."-01";
			$_POST["to_date"]=$_POST["totals_year"]."-".addZero($_POST["totals_month"])."-".cal_days_in_month(CAL_GREGORIAN, $_POST["totals_month"], $_POST["totals_year"]);
		
			$limit="";
		}

		$in_exceptions_filter=false;
		if (strpos($_POST["filter"],'IN_EXCEPTIONS')!==false) {
			$_POST["filter"]=str_replace("IN_EXCEPTIONS","1",$_POST["filter"]);
			$in_exceptions_filter=true;

			$_POST["from_date"]=$_POST["totals_year"]."-".addZero($_POST["totals_month"])."-01";
			$_POST["to_date"]=$_POST["totals_year"]."-".addZero($_POST["totals_month"])."-".cal_days_in_month(CAL_GREGORIAN, $_POST["totals_month"], $_POST["totals_year"]);
		
			$limit="";
		}

		if ($_POST["operator"]=="bee" || $_POST["operator"]=="bee+") {	
			if ($_POST["filter"]=='') {
				$filter="";
			} else {
				$filter=" AND 1=1";
				
				if ($_POST["loss"]==0 && $_POST["edited_calls"]==0 && $edited_call_prices_filter==false && $paying_filter==false && $in_exceptions_filter==false) {
					$s=$db->prepare("SELECT CONCAT(CHAR(34),call_type,'-',service,CHAR(34)) as txt FROM call_types WHERE type_nom IN (".$_POST["filter"].")");
					
					$s->execute();
					if ($s->rowCount()>0) {
						$filter_res=$s->fetchAll(PDO::FETCH_COLUMN);
						$filter_res=implode(",",$filter_res);
						$filter.=" AND CONCAT(call_type,'-',service) IN (".$filter_res.")";
					}
				}
			}

			if ($adresat_filter=="(from_number=:adresat OR to_number=:adresat)") {
				$s=$db->prepare("SELECT id, tel_nom, call_date, DATE_FORMAT(call_date, '%m-%d') as call_date_form, call_time, from_number, to_number, call_type, service, price, call_length, value, bs_number, removed, update_date, update_time FROM ".str_replace("+","",$_POST["operator"])."_detal WHERE tel_nom=:tel_nom AND call_date!='1970-01-01' AND call_date>=:from_date AND call_date<=:to_date AND ".$adresat_filter." ".$filter." ORDER BY call_date DESC, call_time DESC ".$limit);

				$s->bindValue(":tel_nom", $_POST["tel_nom"]);
				$s->bindValue(":from_date", $_POST["from_date"]);
				$s->bindValue(":to_date", $_POST["to_date"]);
				$s->bindValue(":adresat", $_POST["adresat"]);
			} else {
				$s=$db->prepare("SELECT id, tel_nom, call_date, DATE_FORMAT(call_date, '%m-%d') as call_date_form, call_time, from_number, to_number, call_type, service, price, call_length, value, bs_number, removed, update_date, update_time FROM ".str_replace("+","",$_POST["operator"])."_detal WHERE tel_nom=:tel_nom AND call_date!='1970-01-01' AND call_date>=:from_date AND call_date<=:to_date AND ((LOCATE(:adresat, from_number) AND from_number!=:self) OR (LOCATE(:adresat, to_number) AND to_number!=:self)) ".$filter." ORDER BY call_date DESC, call_time DESC ".$limit);
			
				$s->bindValue(":tel_nom", $_POST["tel_nom"]);
				$s->bindValue(":from_date", $_POST["from_date"]);
				$s->bindValue(":to_date", $_POST["to_date"]);
				$s->bindValue(":adresat", $_POST["adresat"]);
				$s->bindValue(":self", substr($_POST["tel_nom"],1,10));
			}

			$s->execute();
		} else if ($_POST["operator"]=="mts") {
			if ($_POST["filter"]=='') {
				$filter="";
			} else {
				$filter=" AND 1=1";
				
				if ($_POST["loss"]==0 && $_POST["edited_calls"]==0 && $edited_call_prices_filter==false && $paying_filter==false && $in_exceptions_filter==false && $_POST["filter"]!='no_subscribered') {
					$s=$db->prepare("SELECT CONCAT(CHAR(34),call_type,'-',service,'-',zone,CHAR(34)) as txt FROM call_types WHERE type_nom IN (".$_POST["filter"].")");
					
					$s->execute();
					if ($s->rowCount()>0) {
						$filter_res=$s->fetchAll(PDO::FETCH_COLUMN);
						$filter_res=implode(",",$filter_res);
						$filter.=" AND CONCAT(service_type,'-',service,'-',service_provider) IN (".$filter_res.")";
					}
				}
			}

			$s=$db->prepare("SELECT id, tel_nom, call_date, DATE_FORMAT(call_date, '%m-%d') as call_date_form, call_time, from_number, to_number, direction, service, service_type as call_type, price, unit, call_length, value, service_provider, gmt, removed, update_date, update_time FROM ".$_POST["operator"]."_detal WHERE tel_nom=:tel_nom AND call_date!='1970-01-01' AND call_date>=:from_date AND call_date<=:to_date AND ".$adresat_filter." AND LOCATE('платеж', service_type)=0 ".$filter." ORDER BY call_date DESC, call_time DESC ".$limit);
			$s->bindValue(":tel_nom", $_POST["tel_nom"]);
			$s->bindValue(":from_date", $_POST["from_date"]);
			$s->bindValue(":to_date", $_POST["to_date"]);
			$s->bindValue(":adresat", $_POST["adresat"]);
			$s->execute();
		} else if ($_POST["operator"]=="meg") {
			if ($_POST["filter"]=='') {
				$filter="";
			} else {
				$filter=" AND 1=1";
				
				if ($_POST["loss"]==0 && $_POST["edited_calls"]==0 && $edited_call_prices_filter==false && $paying_filter==false && $in_exceptions_filter==false) {
					$s=$db->prepare("SELECT CONCAT(CHAR(34),call_type,'-',service,'-',zone,CHAR(34)) as txt FROM call_types WHERE type_nom IN (".$_POST["filter"].")");
					
					$s->execute();
					if ($s->rowCount()>0) {
						$filter_res=$s->fetchAll(PDO::FETCH_COLUMN);
						$filter_res=implode(",",$filter_res);
						$filter.=" AND CONCAT(call_type,'-',service,'-',service_provider) IN (".$filter_res.")";
					}
				}
			}

			$s=$db->prepare("SELECT id, tel_nom, call_date, DATE_FORMAT(call_date, '%m-%d') as call_date_form, call_time, from_number, to_number, direction, service, call_type, price, unit, call_length, value, service_provider, removed, update_date, update_time FROM ".$_POST["operator"]."_detal WHERE tel_nom=:tel_nom AND call_date!='1970-01-01' AND call_date>=:from_date AND call_date<=:to_date AND ".$adresat_filter." ".$filter." ORDER BY call_date DESC, call_time DESC ".$limit);
			$s->bindValue(":tel_nom", $_POST["tel_nom"]);
			$s->bindValue(":from_date", $_POST["from_date"]);
			$s->bindValue(":to_date", $_POST["to_date"]);
			$s->bindValue(":adresat", $_POST["adresat"]);
			$s->execute();
		} else if ($_POST["operator"]=="tele2") {
			if ($_POST["filter"]=='') {
				$filter="";
			} else {
				$filter=" AND 1=1";
				
				if ($_POST["loss"]==0 && $_POST["edited_calls"]==0 && $edited_call_prices_filter==false && $paying_filter==false && $in_exceptions_filter==false) {
					$s=$db->prepare("SELECT CONCAT(CHAR(34),call_type,'-',service,CHAR(34)) as txt FROM call_types WHERE type_nom IN (".$_POST["filter"].")");
					
					$s->execute();
					if ($s->rowCount()>0) {
						$filter_res=$s->fetchAll(PDO::FETCH_COLUMN);
						$filter_res=implode(",",$filter_res);
						$filter.=" AND CONCAT(call_type,'-',service) IN (".$filter_res.")";
					}
				}
			}

			$s=$db->prepare("SELECT id, tel_nom, call_date, DATE_FORMAT(call_date, '%m-%d') as call_date_form, call_time, from_number, to_number, direction, service, call_type, price, unit, call_length, value, service_provider, removed, update_date, update_time FROM ".$_POST["operator"]."_detal WHERE tel_nom=:tel_nom AND call_date!='1970-01-01' AND call_date>=:from_date AND call_date<=:to_date AND ".$adresat_filter." ".$filter." ORDER BY call_date DESC, call_time DESC ".$limit);

			$s->bindValue(":tel_nom", $_POST["tel_nom"]);
			$s->bindValue(":from_date", $_POST["from_date"]);
			$s->bindValue(":to_date", $_POST["to_date"]);
			$s->bindValue(":adresat", $_POST["adresat"]);
			$s->execute();
		}

		$calls=$s->fetchAll(PDO::FETCH_ASSOC);

		$s_exceptions=$db->prepare("SELECT exception FROM clients.exceptions");
		$s_exceptions->execute();
		$exceptions=$s_exceptions->fetchAll(PDO::FETCH_COLUMN);

		if ($_POST["operator"]=="mts" || $_POST["operator"]=="meg") {
			$s=$db->prepare("SELECT CONCAT(call_type,'-',service,'-',zone) as txt, type, type_nom, items_count, items_summa, unit_price FROM call_types WHERE operator=:operator");
		} else {
			$s=$db->prepare("SELECT CONCAT(call_type,'-',service,'-Любой') as txt, type, type_nom, items_count, items_summa, unit_price FROM call_types WHERE operator=:operator");
		}

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
//print_r($call_types_prices);
//exit();
		//Коэффициенты типов вызовов
		$s=$db->prepare("SELECT call_type, kef FROM clients.call_types_kefs WHERE tel_nom=:tel_nom");
		$s->bindValue(":tel_nom", $_POST["tel_nom"]);
		$s->execute();

		$call_types_kefs=$s->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);

		//Изменённые цены вызовов
		$s=$db->prepare("SELECT CONCAT(call_length, '*', call_date, '*', call_time, '*', phone, '*', call_type, '*', service) as txt, unit_price FROM clients.edited_calls_prices WHERE tel_nom=:tel_nom AND call_date BETWEEN :from_date AND :to_date");
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
		$items_summa=0;
		$no_subscrible=0;
		$paying_count=0;

		$calls_result["calls"]=Array();

		$call_nom=0;

		foreach ($calls as $call) {
			$phone="";
			$unit_price["value"]=0;
			$unit_price["type"]="nominal";
			$unit_price["old_value"]="";

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

			if ($_POST["operator"]=="bee" || $_POST["operator"]=="tele2") {
				$call['service_provider']="Любой";
			}

			/*echo $call['call_type']."-".$call['service']."-".$call['service_provider']."\n";
			print_r($call_types);
			exit();*/
			if (isset($call_types[$call['call_type']."-".$call['service']."-".$call['service_provider']])) {
				if ($_POST["filter"]=="no_subscribered") {
					continue;
				}

				$index=$call["call_length"]."*".$call["call_date"]."*".$call["call_time"]."*".$phone."*".$call["call_type"]."*".$call["service"];

				if ($_POST["operator"]=="mts") {
					$ct=$call_types[$call['call_type']."-".$call['service']."-".$call['service_provider']][0]['type'];

					if (is_payable($call, $phone, $ct, $_POST["operator"])==true || $ct=="SMS-900" || isset($edited_calls_prices[$index])) {
						$unit_price=get_unit_price($index, $call, $phone, $ct, $_POST["operator"]);
					}

					if (stripos($ct,"GPRS")!==false) {
						$call_length=call_length_calc($call);
					} else {
						$call_length=eval("return {$call_types[$call['call_type']."-".$call['service']."-".$call['service_provider']][0]['items_count']};");
					}
				} else {
					$ct=$call_types[$call['call_type']."-".$call['service']."-".$call['service_provider']][0]['type'];

					if (is_payable($call, $phone, $ct, $_POST["operator"])==true || $ct=="SMS-900" || isset($edited_calls_prices[$index])) {
						$unit_price=get_unit_price($index, $call, $phone, $ct, $_POST["operator"]);
					}

					$call_length=eval("return {$call_types[$call['call_type']."-".$call['service']."-".$call['service_provider']][0]['items_count']};");
				}	
			}

			$item_price=$unit_price["value"]*$call_length;

			if ($in_exceptions_filter==true && is_in_exceptions($call["from_number"], $call["to_number"])==false) {
				continue;
			}

			if ($paying_filter==true && $item_price==0) {
				continue;
			}

			if ($edited_call_prices_filter==true && $unit_price["type"]=="nominal") {
				continue;
			}

			$edited_period=get_edited_period($call, $phone, $ct, $_POST["operator"]);

			//echo $call["call_date"]." ".$call["call_time"]." - ".$unit_price["type"]." - ".$edited_period["year_edited"]."\n";

			if ($_POST["edited_calls"]==1 && $unit_price["type"]=="nominal" && $edited_period["year_edited"]==0) {
				continue;
			}

			//echo $call["call_date"]." ".$call["call_time"].":".$call["price"]."<".($item_price+1)."\n";

			if ($_POST["loss"]==1 && $call["price"]<=$item_price+1) {
				continue;
			}

			if (isset($call["bs_number"])==false) {
				$bs_number="";
			} else {
				$bs_number=$call["bs_number"];
			}

			$calls_result["calls"][$call_nom]=Array(
				"id" => $call["id"],
				"call_date" => $call["call_date"],
				"call_time" => $call["call_time"],
				"call_date_form" => $call["call_date_form"],
				"phone" => $phone,
				"direction" => $call["direction"],
				"service" => $call["service"],
				"call_type" => $call["call_type"],
				"unit" => $call["unit"],
				"value" => $value,
				"call_length" => $call["call_length"],
				"service_provider" => $call["service_provider"],
				"gmt" => $call["gmt"],
				"removed" => $call["removed"],
				"update_date" => $call["update_date"],
				"update_time" => $call["update_time"],
				"bs_number" => $bs_number,
				"price" => number_format($item_price, 2, ',', ''),
				"unit_price" => number_format($unit_price["value"], 2, ',', ''),
				"office_price" => number_format($call["price"], 2, ',', ''),
				"old_unit_price" => $unit_price["old_value"],
				"year_edited" => $edited_period["year_edited"],
				"month_edited" => $edited_period["month_edited"],
				"type" => $ct,
				"edited_status" => $unit_price["type"]
			);

			$call_nom++;
		}

		echo json_encode($calls_result); 


		function is_in_exceptions($from_number, $to_number) {
			global $exceptions;

			if ($to_number!=="") {
				foreach ($exceptions as $ex) {
					if (stristr($to_number, $ex)!==false) {
						return true;
					} else if (stristr("7".$to_number, $ex)!==false) {
						return true;
					} else if (stristr(substr($to_number,1), $ex)!==false) {
						return true;
					}
				}
			}

			/*if ($from_number!=="") {
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
			}*/

			return false;
		}

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
				//if (stripos($call_type,"ИСХ-ВСР")!==false || stripos($call_type,"ИСX-ЗГП")!==false || strpos($call_type,"ПЕР-ВСР")!==false || strpos($call_type,"ПЕР-ЗГП")!==false) {
				if (stripos($call_type,"ИСХ-ВСР")!==false || stripos($call_type,"ИСX-ЗГП")!==false) {
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

		function call_length_calc($call) {
			if ($call['unit']=='гигабайт') {
				return $call['call_length']*1024;
			} else if ($call['unit']=='мегабайт') {
				return $call['call_length'];
			} else if ($call['unit']=='килобайт' || $call['unit']=='байт') {
				return $call['call_length']/1024;
			} else if ($call['unit']=='факт') {
				return $call['call_length']/1024/1024;
			}
		}

		if (isset($_POST["tel_nom"])!=false && $_POST["operator"]=="bee") {
			$s=$db->prepare("SELECT Contract FROM bee_phones WHERE PhoneNumber=:tel_nom");
			$s->bindValue(":tel_nom", $_POST["tel_nom"]);
			$s->execute();

			$contract=$s->fetch(PDO::FETCH_COLUMN);

			if ($contract==2) {
				$_POST["operator"]="bee+";
			}
		}

		function time_to_sec($time) {
			$parts=explode(":",$time);
			return intval($parts[0])*3600+intval($parts[1])*60+$parts[2];
		}

		function value($v) {
			if ($v==0) {
				return 1;
			} else {
				return $v;
			}
		}
		
		function addZero($n) {
			if ($n<10) {
				return "0".$n;
			} else {
				return $n;
			}
		}
?>