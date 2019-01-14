<?php
$insert_size = 10;
$size_list = array('s' => array(5,4), 'm' => array(6,5), 'l' => array(7,6));
$orb_list = array('R', 'B', 'G', 'L', 'D', 'H', 'J', 'X', 'P', 'M');
$rgbld_orb_list = array('R', 'B', 'G', 'L', 'D');
$style_list = array('TPA', 'CROSS', 'LA', 'VDP', 'FUA', 'ROW');
function get_board($pattern, $size = 'm'){
	return get_board_arr(str_split($pattern), $size);
}
function get_board_arr($p_arr, $size = 'm'){
	$out = '<div class="board size-' . $size . '">';
	foreach($p_arr as $o){
		$out = $out . '<div class="orb ' . $o . '" data-orb="' . $o . '"></div>';
	}
	$out = $out . '</div>';
	return $out;
}
function get_board_matched($pattern, $matched, $size){
	return get_board_matched_arr(str_split($pattern), str_split($matched), $size);
}
function get_board_matched_arr($p_arr, $m_arr, $size = 'm'){
	global $size_list;
	$s = $size_list[$size][0] * $size_list[$size][1];
	$out = '<div class="board size-' . $size . '">';
	for($p = 0; $p < $s; $p++){
		$o = $m_arr[$p] != '-' ? $m_arr[$p] . ' matched' : $p_arr[$p];
		$out = $out . '<div class="orb ' . $o . '" data-orb="' . $o . '"></div>';
	}
	$out = $out . '</div>';
	return $out;
}
function count_orbs($pattern, $ol){
	$counts = array();
	foreach(str_split($pattern) as $o){
		if(in_array($o, $ol)){
			if(array_key_exists($o, $counts)){
				$counts[$o] += 1;
			}else{
				$counts[$o] = 1;
			}
		}
	}
	return $counts;
}
function check_orbs($pattern, $ol){
	$orbs = array();
	foreach(str_split($pattern) as $o){
		if(in_array($o, $ol) && !in_array($o, $orbs)){
			$orbs[] = $o;
		}
	}
	return $orbs;
}
function reorder($pattern, $order){
	global $rgbld_orb_list;
	$i = 0;
	foreach($order as $orb){
		$pattern = str_replace($orb, strval($i), $pattern);
		$i++;
	}
	foreach($rgbld_orb_list as $idx => $orb){
		$pattern = str_replace($idx, $orb, $pattern);
	}
	return $pattern;
}
function permute_board($pattern){
	global $rgbld_orb_list;
	$orbs = check_orbs($pattern, $rgbld_orb_list);
	$result = array();
	
    $recurse = function($array, $start_i = 0) use (&$result, &$recurse, &$pattern) {
        if ($start_i === count($array)-1) {
            array_push($result, reorder($pattern, $array));
        }

        for ($i = $start_i; $i < count($array); $i++) {
            //Swap array value at $i and $start_i
            $t = $array[$i]; $array[$i] = $array[$start_i]; $array[$start_i] = $t;

            //Recurse
            $recurse($array, $start_i + 1);

            //Restore old order
            $t = $array[$i]; $array[$i] = $array[$start_i]; $array[$start_i] = $t;
        }
    };

    $recurse($orbs);
	return $result;
}

class FloodFill{
	public $p_arr = array();
	public $comboColor = '';
	public $wh = array();
	public $minimumMatched;
	public $comboPositionList = array();
	public $comboTracker = array();
	public $stack = array();
	public $solutions = array();
	public $track = array();
	function __construct($p_arr, $wh, $minimumMatched, $comboPositionList) {
		$this->p_arr = $p_arr;
		$this->minimumMatched = $minimumMatched;
		$this->wh = $wh;
		$this->comboPositionList = $comboPositionList;
		foreach($comboPositionList as $key => $value){
			$this->comboTracker[$value] = $key;
		}
	}
	function convertXY($p){
		return array($p%$this->wh[0], floor($p/$this->wh[0]));
	}
	function convertPosition($x, $y){
		return intval($y * $this->wh[0] + $x);
	}
	function alreadyFilled($x, $y){
		if ($x<0 || $y<0 || $x>$this->wh[0]-1 || $y>$this->wh[1]-1){
			return true;
		}
		if (!array_key_exists($this->convertPosition($x, $y), $this->comboTracker)){
			return true;
		}
		if ($this->p_arr[$this->convertPosition($x, $y)] != $this->comboColor){
			return true;
		}
		return false;
	}
	function fillPosition ($x, $y){
		if(!$this->alreadyFilled($x, $y)) {
			$p = $this->convertPosition($x, $y);
			unset($this->comboTracker[$p]);
			$this->track[] = $p;
		}
		if(!$this->alreadyFilled($x, $y-1)){
			$this->stack[] = array($x, $y-1);
		}
		if(!$this->alreadyFilled($x+1, $y)){
			$this->stack[] = array($x+1, $y);
		}
		if(!$this->alreadyFilled($x, $y+1)){
			$this->stack[] = array($x, $y+1);
		}
		if(!$this->alreadyFilled($x-1, $y)){
			$this->stack[] = array($x-1, $y);
		}
	}
	function check_combo_styles($combo){
		$styles = array();
		$size = sizeof($combo['positions']);
		$min_p = min($combo['positions']);
		$min_xy = $this->convertXY($min_p);
		$x = $min_xy[0];
		$y = $min_xy[1];
		if($size >= $this->minimumMatched){
			if($size == 4){
				$styles[] = 'TPA';
			}else if($size == 5){
				//Cross
				if($x >= 1 && $y >= 0 && $x <= $this->wh[0]-2 && $y <= $this->wh[1]-3){
					if(	in_array($this->convertPosition($x, $y+1), $combo['positions']) &&
						in_array($this->convertPosition($x-1, $y+1), $combo['positions']) &&
						in_array($this->convertPosition($x+1, $y+1), $combo['positions']) &&
						in_array($this->convertPosition($x, $y+2), $combo['positions'])){
						$styles[] = 'CROSS';
					}
				}
				//L
				if($x >= 0 && $y >= 0 && $x <= $this->wh[0]-3 && $y <= $this->wh[1]-3){
					if(	in_array($this->convertPosition($x, $y+1), $combo['positions']) &&
						in_array($this->convertPosition($x, $y+2), $combo['positions']) &&
						in_array($this->convertPosition($x+1, $y+2), $combo['positions']) &&
						in_array($this->convertPosition($x+2, $y+2), $combo['positions'])){
						$styles[] = 'LA';
					}else if(
						in_array($this->convertPosition($x+1, $y), $combo['positions']) &&
						in_array($this->convertPosition($x+2, $y), $combo['positions']) &&
						in_array($this->convertPosition($x+2, $y+1), $combo['positions']) &&
						in_array($this->convertPosition($x+2, $y+2), $combo['positions'])){
						$styles[] = 'LA';
					}else if(
						in_array($this->convertPosition($x+1, $y), $combo['positions']) &&
						in_array($this->convertPosition($x+2, $y), $combo['positions']) &&
						in_array($this->convertPosition($x, $y+1), $combo['positions']) &&
						in_array($this->convertPosition($x, $y+2), $combo['positions'])){
						$styles[] = 'LA';
					}
				}
				if($x >= 2 && $y >= 0 && $x <= $this->wh[0]-1 && $y <= $this->wh[1]-3){
					if(	in_array($this->convertPosition($x, $y+1), $combo['positions']) &&
						in_array($this->convertPosition($x, $y+2), $combo['positions']) &&
						in_array($this->convertPosition($x-1, $y+2), $combo['positions']) &&
						in_array($this->convertPosition($x-2, $y+2), $combo['positions'])){
						$styles[] = 'LA';
					}
				}
			}else if($size == 9){
				//VDP
				if($x >= 0 && $y >= 0 && $x <= $this->wh[0]-3 && $y <= $this->wh[1]-3){
					$full_box = true;
					for($i = 0; $i < 3; $i++){
						for($j = 0; $j < 3; $j++){
							if(!in_array($this->convertPosition($x+$i, $y+$j), $combo['positions'])){
								$full_box = false;
								break;
							}
						}
					}
					if($full_box){
						$styles[] = 'VDP';
					}
				}
			}else if($size == 12){
				$styles[] = 'CO';
			}
			if($size == $this->wh[1] && $combo['color'] == 'H'){
				//FUA
				for($i = 0; $i < $this->wh[0]; $i++){
					$full_column = true;
					for($j = 0; $j < $this->wh[1]; $j++){
						if(!in_array($this->convertPosition($i, $j), $combo['positions'])){
							$full_column = false;
							break;
						}
					}
					if($full_column){
						$styles[] = 'FUA';
					}
				}
			}
			if($size >= $this->wh[0]){
				//ROW
				for($j = 0; $j < $this->wh[1]; $j++){
					$full_row = true;
					for($i = 0; $i < $this->wh[0]; $i++){
						if(!in_array($this->convertPosition($i, $j), $combo['positions'])){
							$full_row = false;
							break;
						}
					}
					if($full_row){
						$styles[] = 'ROW';
						break;
					}
				}
			}
		}
		if(sizeof($styles) > 0){
			$combo['styles'] = $styles;
		}else{
			$combo['styles'] = false;
		}
		return $combo;
	}
	function floodFill($p){
		$this->comboColor = $this->p_arr[$p];
		if (!array_key_exists($p, $this->comboTracker)){
			return;
		}
		if($this->comboColor == '-'){
			return;
		}
		$this->track = array();
		$xy = $this->convertXY($p);
		$this->fillPosition($xy[0], $xy[1]);
		while(sizeof($this->stack)>0){
			$toFill = array_pop($this->stack);
			$this->fillPosition($toFill[0], $toFill[1]);
		}
		if(sizeof($this->track) > $this->minimumMatched){
			$this->solutions[] = $this->check_combo_styles(array('color' => $this->comboColor, 'positions' => $this->track));
		}
	}
}
function solve_board($p_arr, $wh = array(6,5), $minimumMatched = 2){	
	$comboPositionList = array();
	$comboColor = '';
	$comboPosition = array();
	for($f = 0; $f < $wh[1]; $f++){
		$comboColor = '';
		$comboPosition = array();
		for($i = $f*$wh[0]; $i < $f*$wh[0]+$wh[0]; $i++){
			if ($p_arr[$i] != $comboColor){
				if (sizeof($comboPosition) > $minimumMatched){
					$comboPositionList = array_merge($comboPositionList, $comboPosition);
				}
				$comboColor = $p_arr[$i];
				$comboPosition = array();
			}
			$comboPosition[] = $i;
			if (sizeof($comboPosition) > $minimumMatched && $i == $f*$wh[0]+$wh[0]-1){
				$comboPositionList = array_merge($comboPositionList, $comboPosition);
			}
		}
	}
	for($f = 0; $f < $wh[0]; $f++){
		$comboColor = '';
		$comboPosition = [];
		for($i = 0+$f; $i < $wh[0]*$wh[1]; $i=$i+$wh[0]){
			if ($p_arr[$i] != $comboColor){
				if (sizeof($comboPosition) > $minimumMatched){
					$comboPositionList = array_merge($comboPositionList, $comboPosition);
				}
				$comboColor = $p_arr[$i];
				$comboPosition = array();
			}
			$comboPosition[] = $i;
			if (sizeof($comboPosition) > $minimumMatched && $i > $wh[0]*($wh[1]-1)-1){
				$comboPositionList = array_merge($comboPositionList, $comboPosition);
			}
		}
	}
	
	if (sizeof($comboPositionList) == 0){
		return false;
	}
	$ff = new FloodFill($p_arr, $wh, $minimumMatched, $comboPositionList);
	foreach($comboPositionList as $p){
		$ff->floodFill($p);
	}
	if(sizeof($ff->solutions) == 0){
		return false;
	}
	foreach($ff->solutions as $combo){
		foreach($combo['positions'] as $p){
			$p_arr[$p] = '-';
		}
	}
	for($f = 0; $f < $wh[0]; $f++){
		for($i = $wh[0]*$wh[1] - $f - 1; $i >= 0 + $f; $i=$i-$wh[0]){
			if($p_arr[$i] != '-'){
				continue;
			}
			$n = $i;
			while($n-$wh[0] >= 0 && $p_arr[$n] == '-'){
				$n = $n-$wh[0];
			}
			$p_arr[$i] = $p_arr[$n];
			$p_arr[$n] = '-';
		}
	}

	$res = solve_board($p_arr, $wh);
	if($res){
		return array_merge(array(array('board' => $p_arr, 'solution' => $ff->solutions)), $res);
	}else{
		return array(array('board' => $p_arr, 'solution' => $ff->solutions));
	}
}
function count_combos($solve){
	$count = 0;
	foreach($solve as $step){
		if($step['solution']){
			$count += sizeof($step['solution']);
		}
	}
	return $count;
}
function get_combined_match_pattern($solution, $wh = array(6,5)){
	$p_arr = array_fill(0, $wh[0] * $wh[1], '-');
	foreach($solution as $combo){
		foreach($combo['positions'] as $p){
			$p_arr[$p] = $combo['color'];
		}
	}
	return $p_arr;
}
function get_match_pattern($combo, $wh = array(6,5)){
	$p_arr = array_fill(0, $wh[0] * $wh[1], '-');
	foreach($combo['positions'] as $p){
		$p_arr[$p] = $combo['color'];
	}
	return $p_arr;
}
function connect_sql($host, $user, $pass, $schema){
	// Create connection
	$conn = new mysqli($host, $user, $pass);
	// Check connection
	if ($conn->connect_error) {
		trigger_error('Connection failed: ' . $conn->connect_error);
		header( 'HTTP/1.0 403 Forbidden', TRUE, 403 );
		die('you cannot');
	}
	$conn->set_charset('utf8');
	$conn->select_db($schema);
	return $conn;
}
function execute_select_stmt($stmt, $pk = null){
	if(!$stmt->execute()){
		trigger_error($conn->error . '[select]');
		return false;
	}
	$stmt->store_result();
	if($stmt->num_rows == 0){
		$stmt->free_result();
		return array();
	}
	$fields = array();
	$row = array();
	$meta = $stmt->result_metadata(); 
	while($f = $meta->fetch_field()){
		$fields[] = & $row[$f->name];
	}
	call_user_func_array(array($stmt, 'bind_result'), $fields);
	$res = array();
	while ($stmt->fetch()){ 
		foreach($row as $key => $val){
			$c[$key] = $val; 
		} 
		if($pk != null){
			if(array_key_exists($c[$pk], $res)){
				$res[$c[$pk]][] = $c;
			}else{
				$res[$c[$pk]] = array($c);
			}
		}else{
			$res[] = $c; 
		}
	}
	return $res;
}
function truncate_tables($conn, $tablenames = array('boards', 'orbs', 'steps', 'combos', 'styles')){
	foreach($tablenames as $tablename){
		foreach(array('SET FOREIGN_KEY_CHECKS = 0;', 'TRUNCATE TABLE ' . $tablename . ';', 'SET FOREIGN_KEY_CHECKS = 1;') as $sql){
			if(!$conn->query($sql)){
				trigger_error('Truncate ' . $tablename . ' failed: ' . $conn->error);
				return false;
			}
		}
	}
	return true;
}
function select_boards($conn, $size = 'm', $color_count = 2, $hearts = false){	
	global $rgbld_orb_list;
	if($hearts){
		$sql = 'select boards.bID, boards.size, boards.pattern from boards inner join orbs on boards.bID=orbs.bID where boards.size=? and boards.orb_count=? and orbs.color="R" and boards.bID in (select boards.bID from boards inner join orbs on boards.bID=orbs.bID where orbs.color="H") order by orbs.count asc;';
		$stmt = $conn->prepare($sql);
		$stmt->bind_param('si', $size, $color_count);
		$boards = execute_select_stmt($stmt);
		$stmt->close();
	}else{
		$sql = 'select boards.bID, boards.size, boards.pattern from boards inner join orbs on boards.bID=orbs.bID where boards.size=? and boards.orb_count=? and orbs.color="R" and boards.bID not in (select boards.bID from boards inner join orbs on boards.bID=orbs.bID where orbs.color="H") order by orbs.count asc;';
		$stmt = $conn->prepare($sql);
		$stmt->bind_param('si', $size, $color_count);
		$boards = execute_select_stmt($stmt);
		$stmt->close();
	}
	
	$sql = 'select boards.bID, orbs.color, orbs.count from boards inner join orbs on boards.bID=orbs.bID where boards.size=?;';
	$stmt = $conn->prepare($sql);
	$stmt->bind_param('s', $size);
	$board_orbs = execute_select_stmt($stmt, 'bID');
	$stmt->close();
	
	$sql = 'select combos.bID, count(combos.cID) combo_count from boards inner join combos on boards.bID=combos.bID where boards.size=? group by boards.bID;';
	$stmt = $conn->prepare($sql);
	$stmt->bind_param('s', $size);
	$board_combo_count = execute_select_stmt($stmt, 'bID');
	$stmt->close();

	$sql = 'select boards.bID, combos.color, styles.style, count(combos.cID) style_counts from boards inner join combos on boards.bID=combos.bID inner join styles on combos.cID=styles.cID where boards.size=? group by boards.bID,combos.color,styles.style;';
	$stmt = $conn->prepare($sql);
	$stmt->bind_param('s', $size);
	$board_styles = execute_select_stmt($stmt, 'bID');
	$stmt->close();
	
	$sql = 'select boards.bID, boards.pattern, combos.color, max(combos.length) as orbs_connected from boards inner join combos on boards.bID=combos.bID where boards.size=? group by combos.bID, combos.color;';
	$stmt = $conn->prepare($sql);
	$stmt->bind_param('s', $size);
	$board_orbs_connected = execute_select_stmt($stmt, 'bID');
	$stmt->close();
	
	$sql = 'select boards.bID, steps.sID, steps.pattern_board from boards inner join steps on boards.bID=steps.bID where  boards.size=? and steps.sID in (select max(steps.sID) from steps group by steps.bID);';
	$stmt = $conn->prepare($sql);
	$stmt->bind_param('s', $size);
	$board_final_state = execute_select_stmt($stmt, 'bID');
	$stmt->close();

	foreach($boards as &$board){
		$board['orbs'] = array();
		foreach($board_orbs[$board['bID']] as $orb){
			$board['orbs'][$orb['color']] = $orb['count'];
		}
		$board['combo'] = $board_combo_count[$board['bID']][0]['combo_count'];
		if(array_key_exists($board['bID'], $board_styles)){
			$board['styles'] = array();
			foreach($board_styles[$board['bID']] as $style){
				$board['styles'][$style['style']][$style['color']] = $style['style_counts'];
			}
		}
		$board['orbs_connected'] = array();
		foreach($board_orbs_connected[$board['bID']] as $orb_connected){
			$board['orbs_connected'][$orb_connected['color']] = $orb_connected['orbs_connected'];
		}
		$board['orbs_remaining'] = array_sum(count_orbs($board_final_state[$board['bID']][0]['pattern_board'], $rgbld_orb_list));
	}
	return $boards;
}
function display_board_solve($conn, $pattern){
	$sql = 'select boards.bID, boards.pattern, boards.size, count(combos.cID) combo_count from boards inner join combos on boards.bID=combos.bID where boards.pattern=? group by boards.bID;';
	$stmt = $conn->prepare($sql);
	$stmt->bind_param('s', $pattern);
	$board = execute_select_stmt($stmt);
	$stmt->close();
	if(sizeof($board) == 0){
		global $size_list;
		$output = '';
		$pattern_array = str_split($_GET['pattern']);
		switch(sizeof($pattern_array)){
			case 20:
				$size = 's';
			break;
			case 42:
				$size = 'l';
			break;
			case 30:
			default:
				$size = 'm';
			break;
		}
		$solve = solve_board($pattern_array, $size_list[$size]);
		$combos = count_combos($solve);
		$board_out = array();
		$prev_board = $pattern_array;
		foreach($solve as $step){
			if($step['solution']){
				$style_out = array();
				foreach($step['solution'] as $combo){
					if($combo['styles']){
						foreach($combo['styles'] as $style){
							$style_out[] = '<div class="style-box">' . orb_style_icon($combo['color'], $style) . '</div>';
						}
					}
				}
				$board_out[] = '<div class="board-box"><div class="float board-styles">' . implode($style_out) . '</div>' . get_board_matched_arr($prev_board, get_combined_match_pattern($step['solution'], $size_list[$size]), $size) . '</div>';
			}
			$prev_board = $step['board'];
		}
		$board_out[] = '<div class="board-box"><div class="float board-styles">' . implode($style_out) . '</div>' . get_board_arr($prev_board, $size) . '</div>';
	}else{
		$size = $board[0]['size'];
		$bID = $board[0]['bID'];
		
		$sql = 'select combos.bID, count(combos.cID) combo_count from boards inner join combos on boards.bID=combos.bID where boards.bID=?;';
		$stmt = $conn->prepare($sql);
		$stmt->bind_param('s', $size);
		$combo_count = execute_select_stmt($stmt);
		$stmt->close();
		
		$sql = 'select steps.bID,steps.sID,steps.pattern_board,steps.pattern_match from steps where steps.bID=?;';
		$stmt = $conn->prepare($sql);
		$stmt->bind_param('i', $bID);
		$steps = execute_select_stmt($stmt);
		$stmt->close();
		
		$sql = 'select steps.sID,combos.cID,combos.color,styles.style from steps inner join combos on steps.sID=combos.sID inner join styles on combos.cID=styles.cID where steps.bID=?;';
		$stmt = $conn->prepare($sql);
		$stmt->bind_param('i', $bID);
		$styles = execute_select_stmt($stmt, 'sID');
		$stmt->close();
		
		$combos = $board[0]['combo_count'];
		$board_out = array();
		$prev_board = $board[0]['pattern'];
		foreach($steps as $step){
			$style_out = array();
			if(array_key_exists($step['sID'], $styles)){
				foreach($styles[$step['sID']] as $style){
					$style_out[] = '<div class="style-box">' . orb_style_icon($style['color'], $style['style']) . '</div>';
				}
			}
			$board_out[] = '<div class="board-box"><div class="float board-styles">' . implode($style_out) . '</div>' . get_board_matched($prev_board, $step['pattern_match'], $size) . '</div>';
			$prev_board = $step['pattern_board'];
		}
		$board_out[] = '<div class="board-box"><div class="float board-styles"></div>' . get_board($prev_board, $size) . '</div>';
	}
	$output = '<div>Total Combos ' . $combos . '</div><div class="float">' . implode($board_out) . '</div>';
	//if(sizeof($style_out) > 0){$output = $output . 'Styles:<div class="float board-info">' . implode($style_out) . '</div>';}
	return $output;
}
function orb_style_icon($color, $name){
	$style_icon = $name;
	$data_row = '';
	if($name == 'ROW'){
		$style_icon = $color . 'RE';
		$data_row = 'data-row-color="' . $color . '"';
	}else if($color == 'H'){
		switch($name){
			case 'TPA': $style_icon = 'HOE'; break;
			case 'LA': $style_icon = 'LS'; break;
			case 'VDP': $style_icon = 'SFUA'; break;
			case 'CROSS': $style_icon = 'HCROSS'; break;
		}
	}
	$orb_icon = '<div data-orb="' . $color . '" class="orb-bg ' . $color . '"></div>';
	$style_icon = '<img ' . $data_row . ' src="img/' . $style_icon . '.png" title="' . $style_icon . '" width="20" height="20"/>';
	return $orb_icon . $style_icon;
}
function display_boards($boards){
	global $orb_list;
	global $rgbld_orb_list;
	$output = '';
	foreach($boards as $board){
		$style_str = '';
		$data_style = '';
		if(array_key_exists('styles', $board)){
			foreach($board['styles'] as $name => $style){
				foreach($style as $color => $count){
					if(in_array($color, $rgbld_orb_list) || $color == 'H'){
						$data_style = $data_style . $color . '-' .$name . ' ';
						$style_str = $style_str . '<div class="style-box">' . orb_style_icon($color, $name) . '<span>x' . $count . '</span></div>';
					}
				}
			}
		}
		if(strlen($data_style) > 0){
			$data_style = 'data-styles="' . trim($data_style) . '"';
		}
		$ratio = '';
		$data_ratio = '';
		foreach($orb_list as $color){
			if(array_key_exists($color, $board['orbs'])){
				$ratio = $ratio . $board['orbs'][$color] . '-';
				$data_ratio = $data_ratio . ' data-ratio-' . $color . '="' . $board['orbs'][$color] . '"';
			}
		}
		$orbs_connected = '';
		$data_orbs_connected = '';
		foreach($board['orbs_connected'] as $color => $orb_connected){
			$orbs_connected .= '<div class="style-orb-connected"><div data-orb="' . $color . '" class="orb-bg ' . $color . '"></div><div>x' . $orb_connected . '</div></div>';
			$data_orbs_connected .= ' data-connected-' . $color . '="' . $orb_connected . '"';
		}
		$data_orbs_left = ' data-orbs-left="' . $board['orbs_remaining'] . '"';
		$output = $output . '<div class="board-box"' . $data_ratio . $data_style . $data_orbs_connected . $data_orbs_left . '><div class="board-info float"><div class="board-statistics"><div>' . substr($ratio, 0, -1) . '</div><div>' . $board['combo'] . ' combo</div></div><div class="grid board-styles">' . $style_str . '</div></div><div class="board-info float"><div class="orbs-connected-text">Maximum<br/>Connected</div><div class="float board-connected">' . $orbs_connected . '</div></div>' . '<a class="board-url" href="solve_boards.php?pattern=' . $board['pattern'] . '">' . get_board($board['pattern'], $board['size']) . '</a></div>';
	}
	return $output;
}
function orb_radios($att_orb){
	$out = '';
	global $rgbld_orb_list;
	if(!in_array($att_orb, $rgbld_orb_list)){
		return '<div class="orb-radio orb-bg ' . $att_orb . '"></div>';
	}
	foreach ($rgbld_orb_list as $i => $orb){
		$out = $out . '<label class="orb-radio disabled orb-bg ' . $orb . '"><input type="radio" class="hidden" data-attribute="' . $att_orb . '-' . $orb . '" value="' . $orb . '"><div class="orb-circle"></div></label>';
	}
	return $out;
}
function get_filters($boards){
	global $orb_list;
	global $size_list;
	$colors = array();
	$wh = array(6, 5);
	$styles = array();
	foreach($boards as $board){
		foreach($orb_list as $orb){
			if(array_key_exists($orb, $board['orbs']) && !in_array($orb, $colors)){
				$colors[] = $orb;
			}
		}
		if($size_list[$board['size']][0] > $wh[0]){
			$wh = $size_list[$board['size']];
		}
		if(array_key_exists('styles', $board)){
			foreach($board['styles'] as $name => $style){
				foreach($style as $orb => $count){
					if(!array_key_exists($orb, $styles)){
						$styles[$orb] = array();
					}
					if($name != 'MISC' && !in_array($name, $styles[$orb])){
						$styles[$orb][] = $name;
					}
				}
			}
		}
	}
	$out = '';
	$max = $wh[0]*$wh[1];
	foreach($colors as $orb){
		$out = $out . '<div class="grid filters" data-orb-base="' . $orb . '"><div class="grid atts">' . orb_radios($orb) . '</div><div class="orb-count">&ge;<input type="text" maxlength="2" value="0"><input type="range" min="0" max="' . $max . '" value="0"><div class="selected-styles"></div>' . $max . '</div><div class="float style-buttons">';
		sort($styles[$orb]);
		foreach($styles[$orb] as $name){
			$out = $out . '<div class="style-button"><input type="checkbox" class="hidden" data-style="' . $name . '">' . orb_style_icon($orb, $name) . '</div>';
		}
		$out = $out . '</div></div>';
	}
	
	return '<fieldset><legend>Board Filters</legend>' . $out . '<button type="button" class="reset-colors">Reset Colors</button><button type="button" class="reset-filters">Reset Filters</button></fieldset>';
}
?>