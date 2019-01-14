<?php
include 'boards_common.php';
include 'sql_param.php';
$time_start = microtime(true);
$conn = connect_sql($host, $user, $pass, $schema);
$url_google_sheet = 'https://docs.google.com/spreadsheets/d/e/2PACX-1vQkDdwvr-R6t4SbqlLddS302UtKWvMx-rGIRDKD8_6AszcvNNv_N56SOoffaw1eRZbP0cUmM3eges1G/pub?gid=0&single=true&output=csv';
$url_local = 'boards.csv';
$data = array();
$fieldnames = array();
$fh = null;
if (!($fh = fopen($url_google_sheet, 'r'))) {
	die('No data to sync.');
}
if(!feof($fh)){
	$fieldnames = explode(',',trim(fgets($fh)));
}
//override
$fieldnames = array(
	0 => 'size',
	1 => 'pattern'
);
while (!feof($fh)) {
	$tmp = explode(',',trim(fgets($fh)));
	if(sizeof($tmp) < 2){
		continue;
	}
	$entry = array();
	foreach($fieldnames as $i => $fn){
		$entry[$fn] = $tmp[$i] == '' ? null : $tmp[$i];
	}
	$entry['size'] = $entry['size'] != '' ? strtolower($entry['size']) : 'm';
	$wh = $size_list[$entry['size']];
	if(strlen($entry['pattern']) < $wh[0]*$wh[1]){
		echo $entry['pattern'] . ' too small.' . PHP_EOL;
		continue;
	}
	$entry['pattern'] = substr(strtoupper($entry['pattern']), 0, $wh[0]*$wh[1]);

	$all_colors = permute_board($entry['pattern']);
	foreach($all_colors as $pattern){
		$entry['pattern'] = $pattern;
		/*$entry['orbs'] = count_orbs($entry['pattern'], $orb_list);
		$entry['orb_count'] = sizeof($entry['orbs']);
		$entry['solve'] = solve_board(str_split($entry['pattern']),  $wh);*/
		$data[$pattern] = $entry;
	}
}
fclose($fh);


$conn = connect_sql($host, $user, $pass, $schema);
truncate_tables($conn);

$success = 0;
mysqli_autocommit($conn, FALSE);
$insert_board = $conn->prepare('INSERT INTO boards (size, pattern, orb_count) VALUES (?,?,?);');
$insert_orbs = $conn->prepare('INSERT INTO orbs (bID, color, count) VALUES (?, ?, ?)');
$insert_step = $conn->prepare('INSERT INTO steps (bID, pattern_board, pattern_match) VALUES (?, ?, ?)');
$insert_combo = $conn->prepare('INSERT INTO combos (bID, sID, color, length, pattern_combo) VALUES (?, ?, ?, ?, ?)');
$insert_style = $conn->prepare('INSERT INTO styles (cID, style) VALUES (?, ?)');
foreach($data as $pattern => $entry){
	$wh = $size_list[$entry['size']];
	$solve = solve_board(str_split($pattern), $wh);
	$orbs = count_orbs($entry['pattern'], $orb_list);
	$orb_count = sizeof($orbs);
	
	if(	!$insert_board->bind_param('ssi', $entry['size'], $pattern, $orb_count) ||
		!$insert_board->execute()){
		trigger_error('Insert board(p:' . $pattern . ') failed: ' . $conn->error);
		$conn->rollback(); 
		continue;
	}
	$bID = $insert_board->insert_id;
	
	foreach($orbs as $color => $count){
		if(	!$insert_orbs->bind_param('isi', $bID, $color, $count) ||
			!$insert_orbs->execute()){
			trigger_error('Insert orbs(b:' . $bID . ') failed: ' . $conn->error);
			$conn->rollback(); 
			continue;
		}
	}
	
	foreach($solve as $step){
		$p_board = implode($step['board']);
		$p_match = implode(get_combined_match_pattern($step['solution'], $wh));
		if(	!$insert_step->bind_param('iss', $bID, $p_board, $p_match) ||
			!$insert_step->execute()){
			trigger_error('Insert step(b:' . $bID . ') failed: ' . $conn->error);
			$conn->rollback();
			continue;
		}
		$sID = $insert_step->insert_id;
		foreach($step['solution'] as $combo){
			$length = sizeof($combo['positions']);
			$p_combo = implode(get_match_pattern($combo, $wh));
			if(	!$insert_combo->bind_param('iisis', $bID, $sID, $combo['color'], $length, $p_combo) ||
				!$insert_combo->execute()){
				trigger_error('Insert combo(b:' . $bID . ' s:' . $sID . ') failed: ' . $conn->error);
				$conn->rollback(); 
				continue;
			}
			$cID = $insert_combo->insert_id;
			if($combo['styles']){
				
				foreach($combo['styles'] as $style){
					if(	!$insert_style->bind_param('is', $cID, $style) ||
						!$insert_style->execute()){
						trigger_error('Insert style(b:' . $bID . ' s:' . $sID . ' c:' . $cID . ') failed: ' . $conn->error);
						$conn->rollback(); 
						continue;
					}
				}
			}
		}
	}
	if ($conn->commit()) {
		$success++;
	}
}
$insert_board->close();
$insert_orbs->close();
$insert_step->close();
$insert_combo->close();
$insert_style->close();

echo 'Total execution time in seconds: ' . (microtime(true) - $time_start) . PHP_EOL;
echo 'Imported ' . $success . ' boards out of ' . sizeof($data) . PHP_EOL;
$conn->close();
?>