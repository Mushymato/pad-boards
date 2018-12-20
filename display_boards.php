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
	addOrbRadioListeners();
	updateOrbColors();
	updateOrbRadios();
	addMinOrbListeners();
});
</script>
</head>
<body>
<?php
include 'boards_common.php';
include 'sql_param.php';
$conn = connect_sql($host, $user, $pass, $schema);
$boards = select_boards($conn);
?>
<form><?php echo get_attribute_filters($boards);?></form>
<?php 
echo display_boards($boards);
?>
</body>
</html>