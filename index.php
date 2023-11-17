<?php
	ob_start();
?>
<HTML>
<HEAD>
<title>DiMS Клиенты</title>
<meta charset="utf8">
<meta name="robots" content="noindex, nofollow, noarchive"/>
<meta name="google-site-verification" content="pLej8ME0DMBdjOeMBJlrWSSeNXT6B-0GIZ9XKWpHuCs" />

	<?php
		Header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); //Дата в прошлом
		Header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
		Header("Pragma: no-cache"); // HTTP/1.1
		Header("Last-Modified: ".gmdate("D, d M Y H:i:s")."GMT");
		ob_end_flush();
	?>

	<script src="https://офис.димс.рф/js/jquery-2.1.3.min.js" type="text/javascript"></script>
	<script src="https://офис.димс.рф/js/jquery-ui.js" type="text/javascript"></script>
	<script src="https://офис.димс.рф/js/check_auth.js" type="text/javascript"></script>

	<link href="https://офис.димс.рф/css/index.css" rel="stylesheet">
	<link  href="https://офис.димс.рф/css/jquery-ui.css" rel="stylesheet">

	<link rel="icon" type="vnd.microsoft.icon" href="img/icon.png">

	<script>
		var mnp_check_loading=false;

		$(window).focus = function(){
			$("#mnp_check_input").focus();
		}

		$(document).ready(function() {
			check_auth();
			
			$("#alert_dialog").dialog({
				modal: true,
				resizable: false,
				autoOpen: false,
				buttons: {
					Ok: function() {
						$(this).dialog("close");
					}
				},
				resizable: true,
				close: function () {
					$(this).dialog("option", "width", 300);
				},
				maxHeight: 600
			});

			//server_time();
			//setInterval(server_time, 10000);
		});

		function server_time() {
			$.ajax({
				url:"http://офис.димс.рф/php/server_time.php",
				data: {},
				type: "POST",
				success: function(data) {
					data=JSON.parse(data);

					$("#server_time").html("Время сервера: "+data["time"]+"<BR>Использовано: "+data["available_space"]+" из "+data["full_space"]+" ("+data["percent_space"]+")<BR>Размер БД: "+data["db_size"]+"G");
				}
			});
		}

		function exit() {
			$.ajax({
				url:"http://клиент.димс.рф/php/exit.php",
				data: {hash:localStorage["session_hash"]},
				type: "POST",
				async: false,
				success: function(data) {
					if (data=="OK") {
						window.location.href="start.php";
					}
				}
			});
		}

		animate_in_process=false;

		function exit_animate(direction, elem) {
			if (animate_in_process==true) {
				return false;
			}

			animate_in_process=true;

			start_width=$("#"+elem).css("width");
			if (direction==1) {
				$("#"+elem).animate({width:start_width.replace("px","")*1.2+"px"},150, function(){
					animate_in_process=false;
				});
			} else {
				$("#"+elem).animate({width:start_width.replace("px","")/1.2+"px"},150, function(){
					animate_in_process=false;
				});
			}
		}
	</script>
</HEAD>
<BODY>
	<div id="alert_dialog"></div>

	<img src='http://офис.димс.рф/img/exit.png' id="exit" style='width:70px; position:absolute; cursor:pointer' onclick='exit()' onmouseover='exit_animate(1, "exit")' onmouseout='exit_animate(2, "exit")'/>

	<div id="server_time" style="position:absolute; font-weight:bold; font-size:14px; width:85%; right:20px; text-align:right"></div>

	<div style="width:100%; height:90%; margin:0 auto">
		<table id='mnp_tbl' style="width:100%; margin:0 auto">
			<tr>
				<td colspan=4 style="height:120px;">
					<img src="http://офис.димс.рф/img/dims_logo.png">
				</td>
			</tr>
			<tr>
				<td colspan="3">
					<table style="margin:0 auto; width:75%;">
						<tr>
							<td><img src="http://офис.димс.рф/img/mts_logo.png"/></td>
							<td><img src="http://офис.димс.рф/img/bee_logo.png"/></td>
							<td><img src="http://офис.димс.рф/img/megafon_logo.png"/></td>
							<td><img src="http://офис.димс.рф/img/tele2_logo.png"/></td>
						</tr>
						<tr>
							<td class='clickable'><a href="client.php?operator=mts&by=&value=<?php echo date("Y-m-d");?>" target="_blank">БАЛАНСЫ</a></td>
							<td class='clickable'><a href="client.php?operator=bee&by=&value=<?php echo date("Y-m-d");?>" target="_blank">БАЛАНСЫ</a></td>
							<td class='clickable'><a href="client.php?operator=meg&by=&value=<?php echo date("Y-m-d");?>" target="_blank">БАЛАНСЫ</a></td>
							<td class='clickable'><a href="client.php?operator=tele2&by=&value=<?php echo date("Y-m-d");?>" target="_blank">БАЛАНСЫ</a></td>
						</tr>

						<tr style="height:200px">
						</tr>
						
						<tr>
							<td colspan=4>
								<table style='margin:0 auto; width:75%'>
									<tr style='height:200px'>
										<td><a href='1s.php' target='_blank'><img id='1s_logo' src="img/1s_logo.png" style='width:150px; cursor:pointer' title='Загрузить отчёт 1С'/></a></td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		
		</table>
	</div>

	<HEAD>

		<META HTTP-EQUIV="PRAGMA" CONTENT="NO-CACHE">

	</HEAD>

</BODY>
</HTML>