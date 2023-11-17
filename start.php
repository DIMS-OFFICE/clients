<html>
	<head>
		<meta charset="UTF-8">
		<title>Вход</title>
		<script type="text/javascript" src="https://офис.димс.рф/js/jquery-2.1.3.min.js"></script>
		<script type="text/javascript" src="https://офис.димс.рф/js/jquery-ui.js"></script>

		<link href="https://офис.димс.рф/css/index.css" rel="stylesheet">
		<link href="https://офис.димс.рф/css/jquery-ui.css" rel="stylesheet">

		<link rel="icon" type="vnd.microsoft.icon" href="https://офис.димс.рф/img/icon.png">
	</head>
	<body>
		<script>
			requested_url=<?php
				if (isset($_GET["requested_url"])) { 
					echo '"'.$_GET["requested_url"].'"';
				} else {
					echo '"/"';
				}
			?>;

			requested_url=decodeURIComponent(requested_url);

			$(document).ready(function() {
				$("#alert_dialog").dialog({
					modal: true,
					resizable: false,
					autoOpen: true,
					buttons: {
						"Вход": function() {
							auth();
						}
					},
					resizable: true,
					close: function () {
						//return false;
					},
					title: "Авторизация"
				});

				$(".ui-dialog-titlebar-close").hide();

				$(".login_div input").keyup(function(event){
					if (event.which==13) {
						auth();
					}
				});
			});

			function auth(){
				login=$("#login").val();
				password=$("#password").val();

				$.ajax({
					url: "/php/auth.php?a="+Math.random(),
					data: {hash:localStorage["session_hash"], login:login, password:password},
					type: "POST",
					timeout: 5000,
					success: function (data) {
						data=JSON.parse(data);

						if (data["result"]=="session_remain") {
							localStorage["session_hash"]=data["hash"];
							window.location.href=requested_url;
						} else if (data["result"]=="new_session") {
							localStorage["session_hash"]=data["hash"];
							window.location.href=requested_url;
						} else if (data["result"]=="wrong_pass") {
							$("#alert_dialog #msg").html("Неверный логин или пароль");
						} else if (data["result"].indexOf("blocked_until")>-1) {
							$("#alert_dialog #msg").html("Превышено кол-во попыток");
						}
					}
				});
			}
		</script>

		<div id="alert_dialog">
			<div class='login_div' style='width:100%; text-align:center'>
				Логин:<BR>
				<input id='login'/><BR>
				Пароль:<BR>
				<input id='password'/>
				<span id="msg" style='font-size:13px; color:red; font-weight:bold'></span>
			</div>
		</div>
	</body>
</html>