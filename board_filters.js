var orb_list = ["R", "B", "G", "L", "D"];
var orb_map = {"R" : "R", "B" : "B", "G" : "G", "L" : "L", "D" : "D"};
function initializeOrbMap(){
	for(let orb of orb_list){
		if(window.localStorage.getItem("orb-" + orb) === null){
			window.localStorage.setItem("orb-" + orb, orb);
		}
		orb_map[orb] = window.localStorage.getItem("orb-" + orb);
	}
}
function reset(){
	console.log("resetting");
	window.localStorage.clear();
	initializeOrbMap();
	updateOrbColors();
	updateOrbRadios();
}
function updateOrbColors(){
	for(let orb of orb_list){
		$("[data-orb]").removeClass(orb);
	}
	for(let base of Object.keys(orb_map)){
		$("[data-orb^='" + base + "']").addClass(orb_map[base]);
		var tmp = $("[data-row-color^='" + base + "']");
		if(tmp.length > 0){
			tmp.each(function(index){
				$(this).attr("src", "img/" + orb_map[base] + "RE.png");
			});
		}
	}
}
function updateOrbRadios(){
	for(let base of Object.keys(orb_map)){
		for(let orb of orb_list){
			if(orb != base){
				var tmp = $("input[data-attribute^='" + orb + "-" + orb_map[base] + "']");
				tmp.parent().css("opacity", 0.5);
			}else{
				var tmp = $("input[data-attribute^='" + orb + "-" + orb_map[base] + "']");
				tmp.prop("checked", true);
				tmp.parent().css("opacity", 1);
			}
		}
	}
}
function toggleFilters(){
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
	$(".style-button > input[type^='checkbox']:checked").each(function(){
		$(".board-box:not([data-styles*='" + $(this).attr("data-style") + "'])").hide();
	});
}
function addFilterListeners(){
	$("input[data-attribute]").each(function(index) {
		$(this).on("click", function(){
			var data = $(this).attr("data-attribute").split("-");
			for(let base of Object.keys(orb_map)){
				if(orb_map[base] == data[1]){
					orb_map[base] = orb_map[data[0]];
					window.localStorage.setItem("orb-" + base, orb_map[base]);
				}
			}
			orb_map[data[0]] = data[1]; 
			window.localStorage.setItem("orb-" + data[0], data[1]);
			updateOrbColors();
			updateOrbRadios();
		});
	});
	$(".orb-count > input[type^='range']").each(function(index){
		$(this).on("change", function() {
			$(this).prev().val($(this).val());
			minOrbFilter();
		});
	});
	$(".orb-count > input[type^='text']").each(function(index){
		$(this).on("change", function() {
			$(this).next().val($(this).val());
			minOrbFilter();
		});
	});
	$(".style-buttons > .style-button").each(function(index){
		$(this).on("click", function() {
			toggleFilters();
		});
	});
	$(".reset-button").on("click", reset);
}