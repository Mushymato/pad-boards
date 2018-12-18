<?php
include 'boards_common.php';
include 'sql_param.php';
$time_start = microtime(true);
$conn = connect_sql($host, $user, $pass, $schema);
$url = 'https://docs.google.com/spreadsheets/d/e/2PACX-1vQkDdwvr-R6t4SbqlLddS302UtKWvMx-rGIRDKD8_6AszcvNNv_N56SOoffaw1eRZbP0cUmM3eges1G/pub?gid=0&single=true&output=csv';
$data = array();
$fieldnames = array();
if ($fh = fopen($url, 'r')) {
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
		$entry = array();
		foreach($fieldnames as $i => $fn){
			$entry[$fn] = $tmp[$i] == '' ? null : $tmp[$i];
		}
		$entry['size'] = strtolower($entry['size']);
		$entry['pattern'] = strtoupper($entry['pattern']);
		$entry['orbs'] = array_filter(count_orbs($entry['pattern'], $var_orb_list));
		arsort($entry['orbs']);
		$entry['pattern'] = reorder($entry['pattern'], $entry['orbs']);
		$entry['orbs'] = array_filter(count_orbs($entry['pattern'], $var_orb_list));
		$data[$entry['pattern']] = $entry;
		$entry['orbs'] = array_filter(count_orbs($entry['pattern'], $var_orb_list));
		asort($entry['orbs']);
		$entry['pattern'] = reorder($entry['pattern'], $entry['orbs']);
		$entry['orbs'] = array_filter(count_orbs($entry['pattern'], $var_orb_list));
		$data[$entry['pattern']] = $entry;
	}
	fclose($fh);
}else{
	trigger_error('Failed to open google sheet.');
	return false;
}

$conn = connect_sql($host, $user, $pass, $schema);
truncate_tables($conn);
$success = 0;
$insert_board = $conn->prepare('INSERT INTO boards (size, pattern) VALUES (?,?);');
$insert_orbs = $conn->prepare('INSERT INTO orbs (bID, color, count) VALUES (?, ?, ?)');
$insert_step = $conn->prepare('INSERT INTO steps (bID, pattern_board, pattern_match) VALUES (?, ?, ?)');
$insert_combo = $conn->prepare('INSERT INTO combos (bID, sID, color, length, pattern_combo) VALUES (?, ?, ?, ?, ?)');
$insert_style = $conn->prepare('INSERT INTO styles (cID, style) VALUES (?, ?)');
foreach($data as $pattern => $entry){
	$complete_success = true;
	
	$wh = $size_list[$entry['size']];
	
	$solve = solve_board(str_split($pattern), $wh);
	
	if(	!$insert_board->bind_param('ss', $entry['size'], $pattern) ||
		!$insert_board->execute()){
		trigger_error('Insert board(p:' . $pattern . ') failed: ' . $conn->error);
		continue;
	}
	$bID = $insert_board->insert_id;
	
	foreach($entry['orbs'] as $color => $count){
		if(	!$insert_orbs->bind_param('isi', $bID, $color, $count) ||
			!$insert_orbs->execute()){
			trigger_error('Insert orbs(b:' . $bID . ') failed: ' . $conn->error);
			$complete_success = false;
			continue;
		}
	}
	
	foreach($solve as $step){
		$p_board = implode($step['board']);
		$p_match = implode(get_combined_match_pattern($step['solution'], $wh));
		if(	!$insert_step->bind_param('iss', $bID, $p_board, $p_match) ||
			!$insert_step->execute()){
				die();
			trigger_error('Insert step(b:' . $bID . ') failed: ' . $conn->error);
			$complete_success = false;
			continue;
		}
		$sID = $insert_step->insert_id;
		foreach($step['solution'] as $combo){
			$length = sizeof($combo['positions']);
			$p_combo = implode(get_match_pattern($combo, $wh));
			if(	!$insert_combo->bind_param('iisis', $bID, $sID, $combo['color'], $length, $p_combo) ||
				!$insert_combo->execute()){
				trigger_error('Insert combo(b:' . $bID . ' s:' . $sID . ') failed: ' . $conn->error);
				$complete_success = false;
				continue;
			}
			$cID = $insert_combo->insert_id;
			if($combo['styles']){
				
				foreach($combo['styles'] as $style){
					if(	!$insert_style->bind_param('is', $cID, $style) ||
						!$insert_style->execute()){
						trigger_error('Insert style(b:' . $bID . ' s:' . $sID . ' c:' . $cID . ') failed: ' . $conn->error);
						$complete_success = false;
						continue;
					}
				}
			}
		}
	}
	$success = $success + ($complete_success ? 1 : 0);
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