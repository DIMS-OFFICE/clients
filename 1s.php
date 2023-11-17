<html>
	<head>
		<meta http-equiv="Content-type" content="text/html; charset=utf-8">

		<title>1С загрузка</title>
		
		<link href="https://офис.димс.рф/css/jquery-ui.css" rel="stylesheet">
		<link href="/css/1s.css" rel="stylesheet" type="text/css">
		<link href="/css/file_upload.css" rel="stylesheet" type="text/css">

		<link rel="icon" type="vnd.microsoft.icon" href="/img/1s.png">

		<script src="https://офис.димс.рф/js/jquery-2.1.3.min.js" type="text/javascript"></script>
		<script src="https://офис.димс.рф/js/jquery-ui.js" type="text/javascript"></script>
		<script type="text/javascript" src="/js/jquery.form.js"></script>
		<script type="text/javascript" src="/js/file_upload.js"></script>
		<script src="https://офис.димс.рф/js/check_auth.js" type="text/javascript"></script>

		<script>
			$(document).ready(function() {
				check_auth();

				hash=localStorage["session_hash"];

				$("#hash").val(hash);

				$('#uploadImage').on('change', function() {
					if (this.files[0].size / 1024 / 1024>20) {
						$("#error_msg").html("Размер файла не должен превышать 20 Мб");
				  		
				  		$("#uploadImage").val("");
				  	} else {
				  		$("#submitButton").prop("disabled", false);
				  	}
				});


				$('#submitButton').click(function () {
					$("#progressDivId").show();

				   	$('#uploadForm').ajaxForm({
				    	url: 'php/upload_file.php',
				    	uploadProgress: function (event, position, total, percentComplete) {
				    	    var percentValue = percentComplete + '%';
				    	    $("#progressBar").animate({width: (Math.ceil(percentComplete)*0.9) + '%'}, {duration: 1000, easing: "linear", step: function (x) {
				    	        $("#submitButton").prop("disabled", true);

				    	        $("#progressDivId").show();

				    	        $("#percent").text(percentComplete + "%");
				    	    }});
				    	},

				    	error: function (response, status, e) {
				    	    $("#error_msg").html('Ошибка подключения к серверу');

						    $("#submitButton").prop("disabled", false);

						    $("#progressBar").animate({width:'0%'}, 0);

						    $("#percent").text("");
				    	},
				    	        
				    	complete: function (xhr) {
				    		console.log(xhr);

				    		if (action=="documents") {
								get_docs_list();
							} else {
								data=JSON.parse(xhr.responseText);

								if (data["status"]=="OK") {
									$.ajax({
										url: "https://клиент.димс.рф/temp/1s_report.csv",
										data: {},
										method: "GET",
										success: function (data) {
											lines=data.split("\n");

											txt="";
											
											for (i=0;i<lines.length;i++) {
												if (i==50) {
													break;
												}

												parts=lines[i].split(";");
												
												txt+="<tr>";
												for (j=0;j<parts.length;j++) {
													txt+="<td>"+parts[j]+"</td>";
												}
												txt+="</tr>";
											}

											$("#preview_tbl tbody").html(txt);

											$("#ss_to_db_btn").show();
										}
									});
								} else if (data["status"]=="SESSION_ERROR") {
									window.location.href=window.location.origin+"/auth.php";
								} else {
									$("#error_msg").html(data["desc"]);
								}
							}
				    	}
				    });
				});

				$("#alert_dialog").dialog({
					modal: true,
					resizable: false,
					autoOpen: false,
					maxHeight: 500,
					width:500,
					buttons: {
						Ok: function() {
							$(this).dialog("close");
							$("#alert_dialog").dialog("option", "width", 500);

							$("#preview_tbl tbody").empty();
							$("#submitButton").prop("disabled", false);
							$("#progressBar").css("width", 0);
							$("#percent").text("");
						}
					},
					open:function(event,ui) {
						$(this).dialog("option", "position", "center");
					}
				});			
			});

			function get_1s_history() {
				$.ajax({
					url:"/php/1s/get_1s_history.php",
					data: {},
					type:"POST",
					success: function(data) {
						data=JSON.parse(data);

						$("#revisions_list").remove();

						txt="<div id='revisions_list' style='width:300px; height:400px; position: absolute; z-index: 1000; top: 25%; left: 41%; background: grey; border-radius:10px; border:2px solid black'>";
						txt+="<div style='width:99%; text-align:right'>";
						txt+="<img class='btn_close' src='img/cancel.png' style='width:30px; cursor:pointer' title='Закрыть'>";
						txt+="</div>";
						txt+="<table style='margin: 0 auto;'>";

						for (i=0; i<data.length; i++) {
							txt+="<tr><td revision='"+data[i]["revision"]+"'' class='clickable' title='Откатить сюда'>"+data[i]["date_time"]+"</td></tr>";
						}

						txt+="</table>";
						txt+="</div>";

						$("body").append(txt);

						$("#revisions_list .clickable").click(function(){
							$("#revisions_list").remove();

							revision=$(this).attr("revision");

							downgrade_1s(revision);
						});

						$("#revisions_list .btn_close").click(function(){
							$("#revisions_list").remove();
						});
					}
				});
			}

			function downgrade_1s(revision) {
				$.ajax({
					url:"/php/1s/downgrade_1s.php",
					data: {revision:revision},
					type:"POST",
					success: function(data) {
						txt="<div id='result_form' style='width:200px; height:85px; position: absolute; z-index: 1000; top: 25%; left: 40%; background: grey; border-radius:10px; border:2px solid black; text-align:center; padding-top: 15px;'>";

						if (data=="OK") {
							txt+="<span style='color:white'>Откатились</span>";
						} else {
							txt+="<span style='color:white'>Какая-то ошибка</span>";
						}

						txt+="<BR><button style='margin-top: 10px'>Зактыть</button>";
						txt+="</div>";

						$("body").append(txt);

						$("#result_form button").click(function(){
							$("#result_form").remove();
						});
					}
				})
			}

			function ss_to_db() {
				$("#ss_to_db_btn").hide();

				$.ajax({
					url:"/php/1s/ss_to_db.php",
					data: {},
					type:"POST",
					success: function(data) {
						data=JSON.parse(data);

						if (data["status"]=="OK") {
							$("#alert_dialog").dialog("option", "title", "Успешно");

							txt="Загружено новых реализаций 1С: "+data["ss_inserted_count"]+"<span class='opers_list_show_hide' direction=1 type=1>&#9660;</span><BR>";

							txt+="<div class='ss_inserted_div'>";
							txt+="<table><thead><th>ПО</th><th>Дата</th><th>Сумма</th></thead>";
							for (i=0; i<data["ss_inserted_count"]; i++) {
								txt+="<tr><td class='tel_nom' operator='"+data["ss_inserted"][i]["operator"]+"'>"+data["ss_inserted"][i]["tel_nom"]+"</td><td>"+data["ss_inserted"][i]["new_date"]+"</td><td>"+data["ss_inserted"][i]["new_summ"]+"</td></tr>";
							}
							txt+="</table>";
							txt+="</div>";

							txt+="Обновлено реализаций 1С: "+data["ss_updated_count"]+"<span class='opers_list_show_hide' direction=1 type=2>&#9660;</span><BR>";

							txt+="<div class='ss_updated_div'>";
							txt+="<table><thead><th>ПО</th><th>Старая дата</th><th>Новая дата</th><th>Старая сумма</th><th>Новая сумма</th></thead>";
							for (i=0; i<data["ss_updated_count"]; i++) {
								txt+="<tr><td class='tel_nom' operator='"+data["ss_updated"][i]["operator"]+"'>"+data["ss_updated"][i]["tel_nom"]+"</td><td>"+data["ss_updated"][i]["old_date"]+"</td><td>"+data["ss_updated"][i]["new_date"]+"</td><td>"+data["ss_updated"][i]["old_summ"]+"</td><td>"+data["ss_updated"][i]["new_summ"]+"</td></tr>";
							}
							txt+="</table>";
							txt+="</div>";

							txt+="Загружено новых платежей: "+data["pay_inserted_count"]+"<span class='opers_list_show_hide' direction=1 type=3>&#9660;</span><BR>";

							txt+="<div class='pay_inserted_div'>";
							txt+="<table><thead><th>ПО</th><th>Дата</th><th>Сумма</th></thead>";
							for (i=0; i<data["pay_inserted_count"]; i++) {
								txt+="<tr><td class='tel_nom' operator='"+data["pay_inserted"][i]["operator"]+"'>"+data["pay_inserted"][i]["tel_nom"]+"</td><td>"+data["pay_inserted"][i]["new_date"]+"</td><td>"+data["pay_inserted"][i]["new_summ"]+"</td></tr>";
							}
							txt+="</table>";
							txt+="</div>";

							/*txt+="Обновлено платежей: "+data["pay_updated_count"]+"<span class='opers_list_show_hide' direction=1 type=4>&#9660;</span>";

							txt+="<div class='pay_updated_div'>";
							txt+="<table><thead><th>ПО</th><th>Старая дата</th><th>Новая дата</th><th>Старая сумма</th><th>Новая сумма</th></thead>";
							for (i=0; i<data["pay_updated_count"]; i++) {
								txt+="<tr><td>"+data["pay_updated"][i]["tel_nom"]+"</td><td>"+data["pay_updated"][i]["old_date"]+"</td><td>"+data["pay_updated"][i]["new_date"]+"</td><td>"+data["pay_updated"][i]["old_summ"]+"</td><td>"+data["pay_updated"][i]["new_summ"]+"</td></tr>";
							}
							txt+="</table>";
							txt+="</div>";*/

							$("#alert_dialog").html(txt);

							$("#alert_dialog .tel_nom").bind("click", function(){
								tel_nom=$(this).text();
								operator=$(this).attr("operator");

								window.open("client_details.php?operator="+operator+"&tel_nom="+tel_nom, "_blank");
							});

							$("#alert_dialog .opers_list_show_hide").on("click", function(){
								type=$(this).attr("type");
								direction=$(this).attr("direction");

								if (direction==1) {
									$(this).attr("direction", 2);
									$(this).html("&#9650;");
								} else {
									$(this).attr("direction", 1);
									$(this).html("&#9660;");
								}

								opers_list_show_hide(type, direction);
							});
						} else {
							$("#alert_dialog").dialog("option", "title", "Ошибка");

							$("#alert_dialog").html(data["desc"]);
						}

						$("#alert_dialog").dialog("open");
					}
				});
			}

			function opers_list_show_hide(type, direction) {
				if (type==1) {
					if (direction==1) {
						$("#alert_dialog .ss_inserted_div").animate({height:'300px'}, 1000);
					} else {
						$("#alert_dialog .ss_inserted_div").animate({height:'0px'}, 1000);
					}
				} else if (type==2) {
					if (direction==1) {
						$("#alert_dialog .ss_updated_div").animate({height:'300px'}, 1000);						
					} else {
						$("#alert_dialog .ss_updated_div").animate({height:'0px'}, 1000);
					}
				} else if (type==3) {
					if (direction==1) {
						$("#alert_dialog .pay_inserted_div").animate({height:'300px'}, 1000);						
					} else {
						$("#alert_dialog .pay_inserted_div").animate({height:'0px'}, 1000);
					}
				} else if (type==4) {
					if (direction==1) {
						$("#alert_dialog .pay_updated_div").animate({height:'300px'}, 1000);						
					} else {
						$("#alert_dialog .pay_updated_div").animate({height:'0px'}, 1000);
					}
				}
			}
		</script>
	</head>
	<body>
		<div id="alert_dialog"></div>

		<div id="main_div">
			<div id="upload_container">
				<div id="header_txt">
					Загрузка ведомости
				</div>

				<div class="form-container">
					<div>
						<form action="php/upload_file.php" id="uploadForm" name="frmupload" method="post" enctype="multipart/form-data">
							<input type="file" id="uploadImage" name="uploadImage"/>
							<input type="hidden" id="action" name="action" value="1s_report"/>
							<input type="hidden" id="hash" name="hash" value=""/>
							<input id="submitButton" type="submit" name="btnSubmit" value="Загрузить" disabled/>
						</form>
					</div>

					<div class="progress" id="progressDivId" style="display:none">
						<div style="text-align:center">
							<div class="progress-bar" id="progressBar"></div>
							<div class="percent" id="percent"></div>
							<div style="height: 10px;"></div>
							<div id="error_msg"></div>
						</div>
					</div>

					<button id="ss_to_db_btn" onclick="ss_to_db()" style="display:none">Загрузить в БД</button>
					<button id="downgrade_btn" onclick="get_1s_history()">Откатить</button>
				</div>
			</div>

			<div id="preview_div">
				<table id="preview_tbl">
					<tbody>
					</tbody>
				</table>
			</div>
		</div>
	</body>
</html>