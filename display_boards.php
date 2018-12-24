<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" type="text/css" href="boards.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js" type="text/javascript"></script>
<!--<script src="jquery.min.js" type="text/javascript"></script>-->
<script src="board_filters.js" type="text/javascript"></script>
<script>
$(document).ready(function(){
	initializeOrbMap();
	intializeFilters();
	addFilterListeners();
	updateOrbColors();
	updateOrbRadios();
	updateFilters();
});
</script>
</head>
<body>
<?php
function option($value, $text, $check){
	return '<option value="'. $value .'"' . ($check == $value ? ' selected' : ' ') . '>' . $text . '</option>' . PHP_EOL;
}
include 'boards_common.php';
include 'sql_param.php';
$conn = connect_sql($host, $user, $pass, $schema);
$size = array_key_exists('board_size', $_GET) ? $_GET['board_size'] : 'm';
$orb_count = array_key_exists('orb_count', $_GET) ? intval($_GET['orb_count']) : 2;
$hearts = array_key_exists('hearts', $_GET) ? boolval($_GET['hearts']) : false;
$boards = select_boards($conn, $size, $orb_count, $hearts);
?>
<form action='' class="grid fset">
	<fieldset>
		<legend>Select Board Type</legend>
		<select name="orb_count">
			<?php
				echo option(2, 'Bicolor', $orb_count);
				echo option(3, 'Tricolor', $orb_count);
			?>
		</select>
		<select name="board_size">
			<?php
				echo option('s', '5x4', $size);
				echo option('m', '6x5', $size);
				echo option('l', '7x6', $size);
			?>
		</select>
		<select name="hearts">
			<?php
				echo option('0', 'no hearts', $hearts);
				echo option('1', 'with hearts', $hearts);
			?>
		</select>
		<button type="submit" value="Submit">Submit</button>
	</fieldset>
	<?php echo get_attribute_filters($boards);?>
</form>
<?php 
echo '<div class="boards float size-' . $size . '">' . display_boards($boards) . '</div>';
?>
</body>
</html>