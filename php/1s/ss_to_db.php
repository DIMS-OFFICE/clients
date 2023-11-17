<?php
	$dir=realpath(dirname(__FILE__)."/../..");

    require($dir."/php/pdo_db_connect.php");

    try {
    	$s_1s=$db->prepare("SELECT DISTINCT revision FROM clients.1s_history");
    	$s_1s->execute();

    	$revisions=$s_1s->fetchAll(PDO::FETCH_COLUMN);

    	$s_1s=$db->prepare("SELECT MIN(revision) as min_revision, MAX(revision) as max_revision FROM clients.1s_history");
    	$s_1s->execute();

    	if ($s_1s->rowCount()>0) {
    		$revision=$s_1s->fetch(PDO::FETCH_ASSOC);
    	} else {
    		$revision["max_revision"]=0;
    	}

    	if (count($revisions)>9) {//Если ревизий больше 9, то удаляем первую
    		$s_1s=$db->prepare("DELETE FROM clients.1s_history WHERE revision=:revision");
    		$s_1s->bindValue(":revision", $revision["min_revision"]);
    		$s_1s->execute();

    		$s_payments=$db->prepare("DELETE FROM clients.payments_history WHERE revision=:revision");
    		$s_payments->bindValue(":revision", $revision["min_revision"]);
    		$s_payments->execute();
    	}

    	$s_1s=$db->prepare("INSERT INTO clients.1s_history (tel_nom, `date`, year, month, summ, diff, date_time, revision) (SELECT tel_nom, `date`, year, month, summ, diff, NOW(), :revision FROM clients.1s)");
    	$s_1s->bindValue(":revision", $revision["max_revision"]+1);
    	$s_1s->execute();

    	$s_payments=$db->prepare("INSERT INTO clients.payments_history (account, tel_nom, summ, payment_date, payment_time, source, append_time, removed, remove_time, operator, date_time, revision) (SELECT account, tel_nom, summ, payment_date, payment_time, source, append_time, removed, remove_time, operator, NOW(), :revision FROM clients.payments)");
    	$s_payments->bindValue(":revision", $revision["max_revision"]+1);
    	$s_payments->execute();



		$file=file_get_contents($dir."/temp/1s_report.csv");

		$lines=explode(PHP_EOL, $file);

		$s_oper1=$db->prepare("SELECT id FROM phones100.mts_phones WHERE PhoneNumber=:tel_nom");
		$s_oper2=$db->prepare("SELECT id FROM phones100.bee_phones WHERE PhoneNumber=:tel_nom");
		$s_oper3=$db->prepare("SELECT id FROM phones100.megafon_phones WHERE PhoneNumber=:tel_nom");
		$s_oper4=$db->prepare("SELECT id FROM phones100.tele2_phones WHERE PhoneNumber=:tel_nom");

		$sel_1s=$db->prepare("SELECT id, `date`, summ, diff FROM clients.1s WHERE tel_nom=:tel_nom AND year=:year AND month=:month");

		$new_1s=$db->prepare("INSERT INTO clients.1s (tel_nom, `date`, summ, year, month, diff) VALUES (:tel_nom, :date1, :summ, :year, :month, :diff)");

		$update_1s=$db->prepare("UPDATE clients.1s SET summ=:summ, diff=:diff WHERE id=:id");

		$s_pay_sel=$db->prepare("SELECT id, tel_nom, payment_date, summ FROM clients.payments WHERE tel_nom=:tel_nom AND payment_date=:payment_date");

		$s_pay=$db->prepare("INSERT IGNORE INTO clients.payments (account, tel_nom, summ, payment_date, append_time, source, removed, operator) VALUES (:account, :tel_nom, :summ, :payment_date, NOW(), 1, :removed, :operator)");

		$s_pay_remove=$db->prepare("UPDATE clients.payments SET removed=1 WHERE id=:id");

		$s_calculated_spended=$db->prepare("SELECT SUM(sum) FROM clients.spended WHERE tel_nom=:tel_nom AND year=:year AND month=:month");

		$pay_inserted_count=0;
		$pay_updated_count=0;
		$ss_inserted_count=0;
		$ss_updated_count=0;
		$pay_updated=Array();
		$pay_inserted=Array();
		$ss_updated=Array();
		$ss_inserted=Array();

		foreach ($lines as $line) {
			$parts=explode(";",$line);

			if (isset($parts[1])==false) {//Не указан номер телефона - пропускаем
				continue;
			}

			//if (trim($parts[1])!="" && mb_substr(trim($parts[1]),0,1,"UTF-8")=="№") {
			if (preg_match("/^[0-9]{2}\.[0-9]{2}\.[0-9]{4}$/", $parts[0])==false && preg_match("/^[0-9]{2}\.[0-9]{2}\.[0-9]{2}$/", $parts[0])==false) {//В первой колонке не дата - значит из этой строки достаём номер телефона
				$tel_nom="7".str_replace(Array("№.00", "№."),"",trim($parts[1]));

				//echo $tel_nom."\n";

				$s_oper1->bindValue(":tel_nom", $tel_nom);
				$s_oper1->execute();

				if ($s_oper1->rowCount()>0) {
					$oper="mts";
				} else {
					$s_oper2->bindValue(":tel_nom", $tel_nom);
					$s_oper2->execute();

					if ($s_oper2->rowCount()>0) {
						$oper="bee";
					} else {
						$s_oper3->bindValue(":tel_nom", $tel_nom);
						$s_oper3->execute();

						if ($s_oper3->rowCount()>0) {
							$oper="meg";
						} else {
							$s_oper4->bindValue(":tel_nom", $tel_nom);
							$s_oper4->execute();

							if ($s_oper4->rowCount()>0) {
								$oper="tele2";
							} else {
								$oper="";
							}
						}
					}
				}

				if ($oper=="") {
					continue;
				}

				if ($oper=="meg") {
					$oper1="megafon";
				} else {
					$oper1=$oper;
				}
			} else if (preg_match("/^[0-9]{11}$/", $tel_nom)) {//Обработка пустых и некорректных номеров - для них ничего не добавляется
				$date=explode(".",$parts[0]);

				if (isset($date[2])==false) {
					continue;
				}

				if (preg_match("/^[0-9]{2}\.[0-9]{2}\.[0-9]{4}$/", $parts[0])) {//Если год из двух цифр, то добавляем к нему 20
					$year=$date[2];
					$month=intval($date[1]);
					
					$date=$date[2]."-".$date[1]."-".$date[0];
				} else {
					$year="20".$date[2];
					$month=intval($date[1]);
					
					$date="20".$date[2]."-".$date[1]."-".$date[0];
				}

				$ss=str_replace(",",".",str_replace(" ","",trim($parts[2])));
				$payment=str_replace(",",".",str_replace(" ","",trim($parts[3])));

				if ($ss=="") {
					$s_acc=$db->prepare("SELECT AccountNumber FROM phones100.".$oper1."_phones WHERE PhoneNumber=:tel_nom");
					$s_acc->bindValue(":tel_nom", $tel_nom);
					$s_acc->execute();
					$acc=$s_acc->fetch(PDO::FETCH_COLUMN);

					$s_pay_sel->bindValue(":tel_nom", $tel_nom);
					$s_pay_sel->bindValue(":payment_date", $date);

					$s_pay_sel->execute();

					if ($s_pay_sel->rowCount()==0) {
						$s_pay->bindValue(":account", $acc);
						$s_pay->bindValue(":tel_nom", $tel_nom);
						$s_pay->bindValue(":summ", $payment);
						$s_pay->bindValue(":payment_date", $date);
						$s_pay->bindValue(":operator", $oper);
						$s_pay->bindValue(":removed", 0);
						$s_pay->execute();

						if ($s_pay->rowCount()>0) {
							$pay_inserted[]=Array(
								"tel_nom" => $tel_nom,
								"new_date" => $date,
								"new_summ" => number_format($payment, 2, ",", ""),
								"operator" => $oper
							);

							$pay_inserted_count++;
						}
					} else {
						//ALREADY EXISTS;

						$old_payment=$s_pay_sel->fetch(PDO::FETCH_ASSOC);

						if ($old_payment["summ"]!=$payment) {//Если платёж за эту дату уже есть, а сумма другая, то старый платёж помечаем как удалённый и добавляем новый
							$s_pay->bindValue(":account", $acc);
							$s_pay->bindValue(":tel_nom", $tel_nom);
							$s_pay->bindValue(":summ", $payment);
							$s_pay->bindValue(":payment_date", $date);
							$s_pay->bindValue(":operator", $oper);
							$s_pay->bindValue(":removed", 0);
							$s_pay->execute();

							$s_pay_remove->bindValue(":id", $old_payment["id"]);
							$s_pay_remove->execute();

							if ($s_pay->rowCount()>0) {
								$pay_inserted[]=Array(
									"tel_nom" => $tel_nom,
									"new_date" => $date,
									"new_summ" => number_format($payment, 2, ",", ""),
									"operator" => $oper
								);

								$pay_inserted_count++;
							}
						}				
					}
				} else {
					$sel_1s->bindValue(":tel_nom", $tel_nom);
					$sel_1s->bindValue(":year", $year);
					$sel_1s->bindValue(":month", $month);
					$sel_1s->execute();

					if ($sel_1s->rowCount()==0) {
						$s_acc=$db->prepare("SELECT AccountNumber FROM phones100.".$oper1."_phones WHERE PhoneNumber=:tel_nom");
						$s_acc->bindValue(":tel_nom", $tel_nom);
						$s_acc->execute();
						$acc=$s_acc->fetch(PDO::FETCH_COLUMN);

						$s_calculated_spended->bindValue(":tel_nom", $tel_nom);
						$s_calculated_spended->bindValue(":year", $year);
						$s_calculated_spended->bindValue(":month", $month);
						$s_calculated_spended->execute();

						$calculated_spended=$s_calculated_spended->fetch(PDO::FETCH_COLUMN);

						$diff=$calculated_spended-$ss;

						$new_1s->bindValue(":tel_nom", $tel_nom);
						$new_1s->bindValue(":date1", $date);
						$new_1s->bindValue(":summ", $ss);
						$new_1s->bindValue(":year", $year);
						$new_1s->bindValue(":month", $month);
						$new_1s->bindValue(":diff", $diff);
						$new_1s->execute();

						if ($new_1s->rowCount()>0) {
							$ss_inserted[]=Array(
								"tel_nom" => $tel_nom,
								"new_date" => $date,
								"new_summ" => number_format($ss, 2, ",", ""),
								"operator" => $oper
							);

							$ss_inserted_count++;
						}
					} else {
						//ALREADY EXISTS;

						$old_1s=$sel_1s->fetch(PDO::FETCH_ASSOC);

						$s_calculated_spended->bindValue(":tel_nom", $tel_nom);
						$s_calculated_spended->bindValue(":year", $year);
						$s_calculated_spended->bindValue(":month", $month);
						$s_calculated_spended->execute();

						$calculated_spended=$s_calculated_spended->fetch(PDO::FETCH_COLUMN);

						$diff=$calculated_spended-$ss;

						$update_1s->bindValue(":id", $old_1s["id"]);
						$update_1s->bindValue(":summ", $ss);
						$update_1s->bindValue(":diff", $diff);
						$update_1s->execute();

						if ($update_1s->rowCount()>0) {
							$ss_updated[]=Array(
								"tel_nom" => $tel_nom,
								"old_date" => $old_1s["date"],
								"old_summ" => number_format($old_1s["summ"], 2, ",", ""),
								"new_date" => $date,
								"new_summ" => number_format($ss, 2, ",", ""),
								"operator" => $oper
							);

							$ss_updated_count++;
						}
					}
				}
			}
		}
	} catch (Exception $e) {
		$result=Array(
			"status" => "error",
			"desc" => $e->getMessage()
		);

		echo json_encode($result);

		exit();
	}

	usort($pay_inserted, "cmp");
	//usort($pay_updated, "cmp");
	usort($ss_inserted, "cmp");
	usort($ss_updated, "cmp");

	$result=Array(
		"status" => "OK",
		"pay_inserted_count" => $pay_inserted_count,
		"pay_updated_count" => $pay_updated_count,
		"ss_inserted_count" => $ss_inserted_count,
		"ss_updated_count" => $ss_updated_count,
		"pay_inserted" => $pay_inserted,
		//"pay_updated" => $pay_updated,
		"ss_inserted" => $ss_inserted,
		"ss_updated" => $ss_updated		
	);

	unlink($dir."/temp/1s_report.csv");
	unlink($dir."/temp/1s_report.xlsx");

	echo json_encode($result);

	function cmp($a, $b) {
		if ($a["tel_nom"]>$b["tel_nom"]) {
			return true;
		} else {
			return false;
		}
	}
?>