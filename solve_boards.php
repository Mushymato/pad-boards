<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" type="text/css" href="boards.css">
<link rel="stylesheet" type="text/css" href="style.css">
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

<div class="header">
	<div class="header-bg">
    </div>
	<div class="content">
        <p class="heading-title">Step-by-step matches</p>
	</div>
</div>
<?php
include 'boards_common.php';
include 'sql_param.php';
$conn = connect_sql($host, $user, $pass, $schema);
$pattern = array_key_exists('pattern', $_GET) ? $_GET['pattern'] : '1';
echo display_board_solve($conn, $pattern);
?>
<div><a href="#" onclick="window.history.back();">Back</a></div>
</body>
</html>