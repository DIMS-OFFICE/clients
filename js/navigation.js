    	function make_navigation() {
            navigation="<div id='navigation_div'>";
            navigation+="<table>";
            navigation+="<tr style='font-size:16px; font-weight:bold; cursor:pointer;'><td onclick='show_navigation()'>Навигация</td></tr>";
            navigation+="<tr style='font-weight:bold'><td onclick='navigation1(0,0)' style='cursor:pointer'>Главная</td></tr>";
            navigation+="<tr style='font-weight:bold'><td>Балансы</td></tr>";
            navigation+="<tr class='clickable'><td onclick='navigation1(1,1)'>&nbsp;&nbsp;МТС</td></tr>";
            navigation+="<tr class='clickable'><td onclick='navigation1(1,2)'>&nbsp;&nbsp;Билайн</td></tr>";
            navigation+="<tr class='clickable'><td onclick='navigation1(1,3)'>&nbsp;&nbsp;Мегафон</td></tr>";
            navigation+="<tr class='clickable'><td onclick='navigation1(1,4)'>&nbsp;&nbsp;Теле2</td></tr>";
            navigation+="</table>";
            navigation+="</div>";

            $("#main_tabs li[tabindex='-1']").after(navigation);
        };

        function show_navigation() {
    		if ($("#navigation_div").css("height")=="125px") {
    			$("#navigation_div").animate({height:"23px"},1000);
    		} else {
    			$("#navigation_div").animate({height:"125px"},1000);
    		}
    	}

    	function navigation1(action, operator) {
    		if (operator==0) {
    		  window.location.href="index.php"
    		} else {
    		  a=Array("", "client.php");
    		  o=Array("", "mts", "bee", "meg", "tele2");

                  d=new Date();
    		  window.location.href=a[action]+"?operator="+o[operator]+"&by=&value="+d.getFullYear()+"-"+(d.getMonth()+1)+"-"+d.getDate();
    		}
    	}
