<?php
include 'boards_common.php';
include 'sql_reader.php';
$conn = connect_sql($host, $user, $pass, $schema);
$board_size = array_key_exists('board_size', $_POST) ? $_POST['board_size'] : 'm';
$orb_count = array_key_exists('orb_count', $_POST) ? intval($_POST['orb_count']) : 2;
$hearts = array_key_exists('hearts', $_POST) ? $_POST['hearts'] == 1 : false;
$combo_count = array_key_exists('combo_count', $_POST) ? $_POST['combo_count'] : null;
$orbs_connected = array_key_exists('orbs_connected', $_POST) ? $_POST['orbs_connected'] : null;
$orbs_left = array_key_exists('orbs_left', $_POST) ? $_POST['orbs_left'] : null;
$style_array = array_key_exists('orbs_left', $_POST) ? $_POST['style_array'] : null;
$result = array();
$boards = select_boards($conn, $board_size, $orb_count, $hearts, $combo_count, $orbs_connected, $orbs_left, $style_array);
$result['boards'] = display_boards($boards);
$result['filters'] = get_filters($boards);
$conn->close();
echo json_encode($result);
?>