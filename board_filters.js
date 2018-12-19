var orb_list = ["R", "B", "G", "L", "D"];
function changeColor(base, target){
	for(let orb of orb_list){
		$("[data-orb^='" + base + "']").removeClass(orb);
	}
	$("[data-orb^='" + base + "']").addClass(target);
	var tmp = $("[data-row-color^='" + base + "']");
	if(tmp.length > 0){
		tmp.each(function(index){
			$(this).attr("src", "img/" + target + "RE.png");
		});
	}
}
function refreshColor(orb){
	changeColor(orb, window.localStorage.getItem("orb-" + orb));
}
function disableSelectedOrb(orb, old_target, new_target){
	for(let orb1 of orb_list){
		if(orb1 != orb){
			var tmp = $("input[data-attribute^='" + orb1 + "-" + old_target + "']");
			tmp.prop("disabled", false);
			tmp.parent().css("opacity", 1);
			tmp = $("input[data-attribute^='" + orb1 + "-" + new_target + "']");
			tmp.prop("disabled", true);
			tmp.parent().css("opacity", 0.5);
		}
	}
}
function addChangeColorListeners(){
	$("input[data-attribute]").each(function(index) {
		$(this).on("click", function(){
			var data = $(this).attr("data-attribute").split("-");
			var old = window.localStorage.getItem("orb-" + data[0]);
			window.localStorage.setItem("orb-" + data[0], data[1]);
			refreshColor(data[0]);
			disableSelectedOrb(data[0], old, data[1]);
		});
	});
}
function refreshAllColors(){
	for(let orb of orb_list){
		if(window.localStorage.getItem("orb-" + orb) === null){
			window.localStorage.setItem("orb-" + orb, orb);
		}
		var target = window.localStorage.getItem("orb-" + orb);
		changeColor(orb, target);
		var tmp = $("input[data-attribute^='" + orb + "-" + target + "']");
		if(tmp.length > 0){
			tmp.prop("checked", true);
			disableSelectedOrb(orb, target, target);
		}
	}
}

function minOrbFilter(){
	$(".board-box").show();
	for(let orb of orb_list.concat("H")){
		var tmp = $("[data-orb-base^='" + orb + "'] > .orb-count > input[type^='text']");
		if(tmp.length > 0){
			var params = [orb, parseInt(tmp.val())];
			$(".board-box").each(function(){
				var ratio_attr = ("data-ratio-" + params[0]).toLowerCase();
				var count = parseInt($(this).attr(ratio_attr));
				count = isNaN(count) ? 0 : count;
				if(count < params[1]){
					$(this).hide();
				}
			}, params);
		}
	}
}
function addMinOrbListeners(){
	$(".orb-count > input[type^='range']").each(function(index){
		$(this).on("mouseup", function() {
			$(this).prev().val($(this).val());
			minOrbFilter();
		});
	});
	$(".orb-count > input[type^='text']").each(function(index){
		$(this).on("focusout", function() {
			minOrbFilter();
		});
	});
}