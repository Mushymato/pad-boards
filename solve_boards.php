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
include 'sql_param.php';
$conn = connect_sql($host, $user, $pass, $schema);
$bID = array_key_exists('id', $_GET) ? $_GET['id'] : '1';
echo display_board_solve($conn, $bID);
?>
<div><a href="display_boards.php">Back</a></div>
</body>
</html>