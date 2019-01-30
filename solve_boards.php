<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<link rel="stylesheet" type="text/css" href="boards.css">
<link rel="stylesheet" type="text/css" href="style.css">
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css" integrity="sha384-GJzZqFGwb1QTTN6wy59ffF1BuGJpLSa9DkKMp0DgiMDm4iYMj70gZWKYbI706tWS" crossorigin="anonymous">

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
    
<div class="container">
<div class="row justify-content-center">
<?php
include 'boards_common.php';
include 'sql_param.php';
$conn = connect_boards_sql($host, $user, $pass, $schema);
$pattern = array_key_exists('pattern', $_GET) ? $_GET['pattern'] : '1';
echo display_board_solve($conn, $pattern);
?>
<div><a href="../boards/" >Back to PAD Board Database</a></div>
</div>
</div>
</body>
</html>