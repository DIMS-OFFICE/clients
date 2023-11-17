<html>
	<title>
		Клиентское приложение
	</title>
	<head>
		<script type="text/javascript" src="https://офис.димс.рф/js/jquery-2.1.3.min.js"></script>
		<script type="text/javascript" src="js/jquery.cookie.js.min"></script>

		<style>
			html {
		        background: url(https://офис.димс.рф/img/bg1.jpg) no-repeat center center fixed;
		        -webkit-background-size: cover;
		        -moz-background-size: cover;
		        -o-background-size: cover;
		        background-size: cover;
			}

			body {
				overflow-y: hidden;
			}

			.main_div {
				width: 100%;
			}

			#auth_div {
				position: absolute;
				background: radial-gradient(white, lightgrey);
				margin-top: -270px;
				margin-left: 10%;
				padding-top: 30px;
				width: 80%;
				min-width: 320px;
				height: 200px;
				border-radius: 10px;
				box-shadow: 0 0 0 60px rgb(0 0 0 / 0%) inset, 0.1em 0.1em 0.2em darkslategrey;
			}

			#reg_div {
				position: absolute;
				background: radial-gradient(white, lightgrey);
				margin-left: 10%;
				margin-top: -270px;
				padding-top: 30px;
				width: 80%;
				min-width: 350px;
				height: 200px;
				border-radius: 10px;
				box-shadow: 0 0 0 60px rgb(0 0 0 / 0%) inset, 0.1em 0.1em 0.2em darkslategrey;
			}

			#restore_div {
				position: absolute;
				background: radial-gradient(white, lightgrey);
				margin-left: 10%;
				margin-top: -270px;
				padding-top: 30px;
				width: 80%;
				min-width: 350px;
				height: 200px;
				border-radius: 10px;
				box-shadow: 0 0 0 60px rgb(0 0 0 / 0%) inset, 0.1em 0.1em 0.2em darkslategrey;
			}

			.form_tbl {
				margin: 0 auto;
			}

			.error_msg {
				font-weight:bold; 
				color:red;
			}

			input {
				border-radius: 5px;
				background: yellow;
			}

			button {
				border-radius: 5px;
				width: 150px;
			}
		</style>

		<script>
			$(document).ready(function(){
				auth();
			});

			function registration_form_show() {
				$("#auth_div").animate({"margin-top": "-270"}, 1000, function(){
					$("#reg_div").animate({"margin-top": "20%"}, 1000);
				});
			}

			function registration_form_hide() {
				$("#reg_div").animate({"margin-top": "-270"}, 1000, function(){
					$("#auth_div").animate({"margin-top": "20%"}, 1000);
				});
			}

			function restore_form_show() {
				$("#auth_div").animate({"margin-top": "-270"}, 1000, function(){
					$("#restore_div").animate({"margin-top": "20%"}, 1000);
				});
			}

			function restore_form_hide() {
				$("#restore_div").animate({"margin-top": "-270"}, 1000, function(){
					$("#auth_div").animate({"margin-top": "20%"}, 1000);
				});
			}

			function auth() {
				tel_nom=$("#auth_div .tel_nom").val();
				password=$("#auth_div .password").val();

				if (tel_nom=="") {
					if ($.cookie('hash') == null) {
						console.log("****");
						$("#auth_div").animate({"margin-top": "20%"}, 1000);

						$("#auth_div .tel_nom").focus();

						return false;
					}

					hash=$.cookie('hash');

					by_hash=1;

					tel_nom="";
					password="";
				} else {
					by_hash=0;

					hash="";
				}

				$.ajax({
					url:"/mobile/php/auth.php",
					data:{tel_nom:tel_nom, password:password, hash:hash, by_hash:by_hash},
					type:"POST",
					async:false,
					success: function(data) {
						data=JSON.parse(data);

						if (data["status"]=="OK") {
							$.cookie('hash', data["hash"]);

							window.location.href="index.php";
						} else if (data["status"]=="wrong_hash") {
							$("#auth_div").animate({"margin-top": "20%"}, 1000);

							$("#auth_div .tel_nom").focus();
						} else if (data["status"]=="wrong_number") {
							$("#auth_div .error_msg").text(data["desc"]);

							setTimeout(function(){
								$("#auth_div .error_msg").text("");
							},7000);
						}
					}
				});
			}

			function registration() {
				tel_nom=$("#reg_div .tel_nom").val();
				password=$("#reg_div .password").val();
				email=$("#reg_div .email").val();

				if (password.length<6) {
					$("#reg_div .error_msg").text("Пароль должен содержать мин. 6 знаков");

					setTimeout(function(){
						$("#reg_div .error_msg").text("");
					},7000);

					return false;
				}

				var re = /^[\w-\.]+@[\w-]+\.[a-z]{2,4}$/i;
			    if (re.test(email)==false) {
			    	$("#reg_div .error_msg").text("Некорректный E-mail");

					setTimeout(function(){
						$("#reg_div .error_msg").text("");
					},7000);

					return false;
			    }

				$.ajax({
					url:"/mobile/php/registration.php",
					data:{tel_nom:tel_nom, password:password, email:email},
					type:"POST",
					async:false,
					success: function(data) {
						data=JSON.parse(data);

						if (data["status"]!="ОК") {
							$("#reg_div .error_msg").text(data["desc"]);

							setTimeout(function(){
								$("#reg_div .error_msg").text("");
							},7000);
						} else {
							$("#reg_div .error_msg").text(data["desc"]);

							setTimeout(function() {
								registration_form_hide();
							},1000);
						}
					}
				});
			}

			function restore_password() {
				tel_nom=$("#restore_div .tel_nom").val();

				$.ajax({
					url:"/mobile/php/restore_password.php",
					data:{tel_nom:tel_nom},
					type:"POST",
					async:false,
					success: function(data) {
						data=JSON.parse(data);

						if (data["status"]!="ОК") {
							$("#restore_div .error_msg").text(data["desc"]);

							setTimeout(function(){
								$("#restore_div .error_msg").text("");
							},7000);
						} else {
							$("#restore_div .error_msg").text(data["desc"]);

							setTimeout(function() {
								restore_form_hide();
								$("#restore_div .error_msg").text("");
							},1000);
						}
					}
				});				
			}
		</script>
	</head>

	<body>
		<div class='main_div'>
			<div id='auth_div'>
				<table class='form_tbl'>
					<tr>
						<td>Номер телефона</td>
						<td><input class="tel_nom" placeholder="Например, 79141223344"></td>
					</tr>
					<tr>
						<td>Пароль</td>
						<td><input class="password"></td>
					</tr>
					<tr style="height:50px;">
						<td style="text-align:center"><a href="javascript:" onclick="registration_form_show()">Регистрация</a></td>
						<td style="text-align:center"><a href="javascript:" onclick="restore_form_show()">Забыли пароль?</a></td>
					</tr>
						<td colspan=2 style="text-align:center">
							<button onclick="auth()">Вход</button>
						</td>					
					<tr>
						<td colspan=2 style="text-align:center">
							<span class="error_msg"></span>
						</td>
					</tr>							
				</table>
			</div>

			<div id='reg_div'>
					<table class='form_tbl'>
						<tr>
							<td>Номер телефона</td>
							<td><input class="tel_nom" placeholder="Например, 79141223344"></td>
						</tr>
						<tr>
							<td>Пароль (мин. 6 знаков)</td>
							<td><input class="password"></td>
						</tr>
						<tr>
							<td>E-Mail (для восстановления пароля)</td>
							<td><input class="email"></td>
						</tr>
						<tr>
							<td colspan=2 style="text-align:center">
								<button onclick="registration()">Регистрация</button>
							</td>
						</tr>
						<tr>
							<td colspan=2 style="text-align:center">
								<button onclick="registration_form_hide()">Назад</button>
							</td>
						</tr>
						<tr>
							<td colspan=2 style="text-align:center">
								<span class="error_msg"></span>
							</td>
						</tr>							
					</table>
				</div>
			</div>

			<div id='restore_div'>
					<table class='form_tbl'>
						<tr>
							<td>Номер телефона</td>
							<td><input class="tel_nom" placeholder="Например, 79141223344"></td>
						</tr>
						<tr>
							<td colspan=2 style="text-align:center">
								Пароль будет отправлен на E-Mail, указанный при регистрации
							</td>
						</tr>						
						<tr>
							<td colspan=2 style="text-align:center">
								<button onclick="restore_password()">Восстановить</button>
							</td>
						</tr>
						<tr>
							<td colspan=2 style="text-align:center">
								<button onclick="restore_form_hide()">Назад</button>
							</td>
						</tr>						
						<tr>
							<td colspan=2 style="text-align:center">
								<span class="error_msg"></span>
							</td>
						</tr>							
					</table>
				</div>
			</div>			
		</div>
	</body>
</html>