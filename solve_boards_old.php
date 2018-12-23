<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" type="text/css" href="boards.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js" type="text/javascript"></script>
<script src="board_filters.js" type="text/javascript"></script>
<script>
$(document).ready(function(){
	initializeOrbMap();
	updateOrbColors();
});
</script>
</head>
<body>
<?php
include 'boards_common.php';
$time_start = microtime(true);
if(array_key_exists('pattern', $_GET)){
	$pattern_array = str_split($_GET['pattern']);
	$solve = solve_board($pattern_array, $size_list['m']);
	$step_boards = array(get_board_arr($pattern_array));
	$match_boards = array();
	$styles = array();
	foreach($solve as $step){
		$step_boards[] = get_board_arr($step['board']);
		if($step['solution']){
			$match_boards[] = get_board_arr(get_combined_match_pattern($step['solution']));
			foreach($step['solution'] as $combo){
				if($combo['styles']){
					foreach($combo['styles'] as $style){
						$styles[] = '<div class="style-box">' . orb_style_icon($combo['color'], $style) . '</div>';
					}
				}
			}
		}
	}
	echo '<div>Total Combos ' . count_combos($solve) . '</div>';
	echo 'Steps:<div class="float">' . implode($step_boards) . '</div>';
	if(sizeof($match_boards) > 0){echo 'Matched:<div class="float">' . implode($match_boards) . '</div>';}
	if(sizeof($styles) > 0){echo 'Styles:<div class="float board-info">' . implode($styles) . '</div>';}
}
echo '<p>Total execution time in seconds: ' . (microtime(true) - $time_start) . '</p>' . PHP_EOL;
?>
<div><a href="display_boards.php">Back</a></div>
</body>
</html>