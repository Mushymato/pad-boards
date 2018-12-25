var orb_base_list = ["R", "B", "G", "L", "D"];
var orb_map = {};
var board_filters = {};
function initializeOrbMap(){
	if(window.localStorage.getItem("orb_map") === null){
		window.localStorage.setItem("orb_map", JSON.stringify({"R" : "R", "B" : "B", "G" : "G", "L" : "L", "D" : "D"}));
	}
	orb_map = JSON.parse(window.localStorage.getItem("orb_map"));
}
function intializeFilters(){
	if(window.sessionStorage.getItem("board_filters") === null){
		window.sessionStorage.setItem("board_filters", JSON.stringify({}));
	}
	board_filters = JSON.parse(window.sessionStorage.getItem("board_filters"));
	for(let color of Object.keys(board_filters)){
		for(let style of Object.keys(board_filters[color])){
			if(style === "count"){
				var tmp = $("[data-orb-base^='" + color + "'] > .orb-count > input");
				console.log(tmp);
				if(tmp.length > 0){
					tmp.val(board_filters[color][style]);
				}else{
					delete board_filters[color][style];
				}
			}else if(board_filters[color][style]){
				var tmp = $("[data-orb-base^='" + color + "'] > .style-buttons > .style-button > input[data-style^='" + style + "']");
				console.log(color + "-" + style);
				console.log(tmp);
				if(tmp.length > 0){
					tmp.prop("checked", board_filters[color][style]);
				}else{
					delete board_filters[color][style];
				}
			}
		}
	}
}
function resetColors(){
	window.localStorage.clear();
	initializeOrbMap();
	updateOrbColors();
	updateOrbRadios();
}
function resetFilters(){
	window.sessionStorage.clear();
	$(".style-button > input").prop("checked", false);
	$(".orb-count > input").val(0);
	intializeFilters();
	updateFilters();
}
function updateOrbColors(){
	for(let orb of orb_base_list){
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
	var tmp = $("input[data-attribute]");
	tmp.prop("checked", false);
	tmp.parent().addClass("disabled");
	for(let base of Object.keys(orb_map)){
		for(let orb of orb_base_list){
			if(orb == base){
				tmp = $("input[data-attribute^='" + orb + "-" + orb_map[base] + "']");
				tmp.prop("checked", true);
				tmp.parent().removeClass("disabled");
			}
		}
	}
}
function updateFilters(){
	$(".board-box").show();
	for(let color of Object.keys(board_filters)){
		for(let style of Object.keys(board_filters[color])){
			if(style === "count"){
				var params = [color, board_filters[color][style]];
				$(".board-box").each(function(){
					var ratio_attr = ("data-ratio-" + params[0]).toLowerCase();
					var count = parseInt($(this).attr(ratio_attr));
					count = isNaN(count) ? 0 : count;
					if(count < params[1]){
						$(this).hide();
					}
				}, params);
			}else if(board_filters[color][style]){
				$(".board-box:not([data-styles*='" + color + "-" + style + "'])").hide();
			}
		}
	}
}
function addFilterListeners(){
	$("input[data-attribute]").each(function(index) {
		$(this).on("click", function(){
			var data = $(this).attr("data-attribute").split("-");
			for(let base of Object.keys(orb_map)){
				if(orb_map[base] == data[1]){
					orb_map[base] = orb_map[data[0]];
				}
			}
			orb_map[data[0]] = data[1]; 
			window.localStorage.setItem("orb_map", JSON.stringify(orb_map));
			updateOrbColors();
			updateOrbRadios();
		});
	});
	$(".orb-count > input[type^='range']").each(function(index){
		$(this).on("change", function() {
			$(this).prev().val($(this).val());
			var color = $(this).parent().parent().attr("data-orb-base");
			if(board_filters[color] == undefined){
				board_filters[color] = {};
			}
			board_filters[color]["count"] = $(this).val();
			window.sessionStorage.setItem("board_filters", JSON.stringify(board_filters));
			updateFilters();
		});
	});
	$(".orb-count > input[type^='text']").each(function(index){
		$(this).on("change", function() {
			$(this).next().val($(this).val());
			var color = $(this).parent().parent().attr("data-orb-base");
			if(board_filters[color] == undefined){
				board_filters[color] = {};
			}
			board_filters[color]["count"] = $(this).val();
			window.sessionStorage.setItem("board_filters", JSON.stringify(board_filters));
			updateFilters();
		});
	});
	$(".style-buttons > .style-button > input[type^='checkbox']").each(function(index){
		$(this).on("click", function() {
			var color = $(this).parent().parent().parent().attr("data-orb-base");
			var style = $(this).attr("data-style");
			if(board_filters[color] == undefined){
				board_filters[color] = {};
			}
			if(!board_filters[color][style]){
				board_filters[color][style] = true;
			}else{
				board_filters[color][style] = false;
			}
			window.sessionStorage.setItem("board_filters", JSON.stringify(board_filters));
			updateFilters();
		});
	});
	$(".reset-colors").on("click", resetColors);
	$(".reset-filters").on("click", resetFilters);
}