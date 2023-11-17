var default_colors = 
		["fff","fc0388","6603fc","031cfc","03a9fc","03fc88","4efc03","fcf403",
		"fc6603","c7c5c3","9e9d9b","6b6966","42403e",'990033','ff3366','cc0033',
		'993366','660033','cc3399','ff99cc','ff66cc','ff99ff','ff6699','cc0066',
		'ff0066','ff3399','ff0099','ff33cc','ff00ff','999999','666666','333333',
		'006666','003333','00ffcc','33ffcc','33cc99','00cc99','66ffcc','99ffcc',
		'ff6666','660000','990000','cc0000','ff0000','ff3300','ffffff','cccccc',
		'000000'];

		for (y=0; y<7; y++) {
			$("#color_picker").append("<tr row_nom='0' id='color_picker_row_"+y+"'></tr>");
			for (x=0; x<7; x++) {
				$("#color_picker_row_"+y).append("<td class='color_picker_td' style='background:#"+default_colors[y*7+x]+"'></td>");
			}
		}
		
		$("#color_picker").append("<tr><td colspan=10 class='close_btn' style='cursor:pointer; background:gray; text-align:center; font-weight:bold; height:35px'>Закрыть</td></tr>");

		$(".color_picker_td").click(function(){
			color=$(this).attr("style").replace("background:", "");
			legend_row_id=$("#color_picker").attr("row_nom");
			$("#"+legend_row_id).css("background", color);
			$("#color_picker").hide();
			if ($("#color_picker").attr("type")=="service") {
				change_dinamic_services_legend(legend_row_id.replace("dinamic_legend_services_tr_",""));
			} else {
				change_dinamic_legend(legend_row_id.replace("dinamic_legend_tr_",""));
			}
		});

		$("#color_picker .close_btn").click(function(){
			$("#color_picker").hide();
		});