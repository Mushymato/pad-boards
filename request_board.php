<?php
include 'boards_common.php';
include 'sql_reader.php';
$size = array_key_exists('size', $_POST) ? $_POST['size'] : 'm';
if(!in_array($size, ['s', 'm', 'l'])){header('results: 0');exit;}
/*$style = array_key_exists('style', $_POST) ? $_POST['style'] : 'combo';
if(!in_array(['combo', 'vdp', 'tpa', 'l', 'fua', 'row']){header('results: 0'); exit;}*/
$orb_counts = array();
foreach($orb_list as $color){
	if(array_key_exists($color, $_POST) && strlen($_POST[$color]) <= 2 && ctype_digit($_POST[$color])){
		$orb_counts[$color] = intval($_POST[$color]);
	}
}
$conn = connect_sql($host, $user, $pass, $schema);
$sql = '';
$tbl = 'boards';
if(sizeof($orb_counts) > 0){
	$first_color = array_keys($orb_counts)[0];
	$tbl = $first_color;
	$sql = 'select ' . $tbl . '.pattern from (select boards.bID, boards.pattern, boards.size from boards inner join orbs on boards.bID=orbs.bID where (color,count) = ("' . $first_color . '", ' . $orb_counts[$first_color] . ')) as ' . $tbl;
	foreach($orb_counts as $color => $count){
		if($color == $first_color){continue;}
		$sql .= ' inner join (select boards.bID, boards.pattern from boards inner join orbs on boards.bID=orbs.bID where (color,count) = ("' . $color . '", ' . $count . ')) as ' . $color . ' on ' . $first_color . '.bID=' . $color . '.bID';
	}
}else{
	$sql .= 'select ' . $tbl . '.pattern from boards';
}
$sql .= ' where ' . $tbl . '.size=?;';
$stmt = $conn->prepare($sql);
if(!$stmt){header('results: 0');exit;}
$stmt->bind_param('s', $size);
$results = execute_select_stmt($stmt);
$stmt->close();
header('results:' . sizeof($results));
header('sql:' . $sql);
foreach($results as $r){
	echo $r['pattern'] . '|';
}
$conn->close();
exit;
?>