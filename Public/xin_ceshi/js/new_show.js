$(".nav-btn,.nav-slider .bg").click(
	function(){
		if($(".nav-slider").hasClass("fadeIn")){
			$(".nav-slider-inner").animate({"marginRight":"-280px"},300,function(){
					$(".nav-slider").removeClass("fadeIn").fadeOut(300);
					$("#nav-btn-fix").fadeIn();
					$("#nav_home").fadeIn();
				}
			);
		}else{
			$(".nav-slider").css({"overflow-y":"scroll"}).addClass("fadeIn").fadeIn(300,function(){
				$(".nav-slider-inner").animate({"marginRight":"0"},300)
				$("#nav-btn-fix").fadeOut();
				$("#nav_home").fadeOut();
			})
		}
	}	
);