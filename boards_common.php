<?php
$size_list = array('s' => array(5,4), 'm' => array(6,5), 'l' => array(7,6));
$orb_list = array('R', 'B', 'G', 'L', 'D', 'H', 'J', 'X', 'P', 'M');
$var_orb_list = array('R', 'B', 'G', 'L', 'D');
$style_list = array('MISC', 'TPA', 'CROSS', 'L', 'VDP', 'SFUA', 'FUA', 'ROW');
function get_board($pattern, $size = 'm'){
	$out = '<div class="board size-' . $size . '">';
	foreach(str_split($pattern) as $o){
		$out = $out . '<div class="orb ' . $o . '" data-orb="' . $o . '"></div>';
	}
	$out = $out . '</div>';
	return $out;
}
function get_board_arr($p_arr, $size = 'm'){
	$out = '<div class="board size-' . $size . '">';
	foreach($p_arr as $o){
		$out = $out . '<div class="orb ' . $o . '" data-orb="' . $o . '"></div>';
	}
	$out = $out . '</div>';
	return $out;
}
function count_orbs($pattern, $ol){
	$counts = array();
	foreach($ol as $orb){
		$counts[$orb] = 0;
	}
	foreach(str_split($pattern) as $o){
		if(in_array($o, $ol)){
			$counts[$o] += 1;
		}
	}
	return $counts;
}
function reorder($pattern, $order){
	global $var_orb_list;
	$i = 0;
	foreach($order as $orb => $count){
		$pattern = str_replace($orb, strval($i), $pattern);
		$i++;
	}
	foreach($var_orb_list as $idx => $orb){
		$pattern = str_replace($idx, $orb, $pattern);
	}
	return $pattern;
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
						$styles[] = 'L';
					}else if(
						in_array($this->convertPosition($x+1, $y), $combo['positions']) &&
						in_array($this->convertPosition($x+2, $y), $combo['positions']) &&
						in_array($this->convertPosition($x+2, $y+1), $combo['positions']) &&
						in_array($this->convertPosition($x+2, $y+2), $combo['positions'])){
						$styles[] = 'L';
					}else if(
						in_array($this->convertPosition($x+1, $y), $combo['positions']) &&
						in_array($this->convertPosition($x+2, $y), $combo['positions']) &&
						in_array($this->convertPosition($x, $y+1), $combo['positions']) &&
						in_array($this->convertPosition($x, $y+2), $combo['positions'])){
						$styles[] = 'L';
					}
				}
				if($x >= 2 && $y >= 0 && $x <= $this->wh[0]-1 && $y <= $this->wh[1]-3){
					if(	in_array($this->convertPosition($x, $y+1), $combo['positions']) &&
						in_array($this->convertPosition($x, $y+2), $combo['positions']) &&
						in_array($this->convertPosition($x-1, $y+2), $combo['positions']) &&
						in_array($this->convertPosition($x-2, $y+2), $combo['positions'])){
						$styles[] = 'L';
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
						if($combo['color'] == 'H'){
							$styles[] = 'SFUA';
						}else{
							$styles[] = 'VDP';
						}
					}
				}
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
function select_boards_by_size($conn, $size = 'm'){
	$sql = 'select boards.bID, boards.size, boards.pattern from boards left join orbs on boards.bID=orbs.bID where boards.size=? and orbs.color="R" order by orbs.count desc;';
	$stmt = $conn->prepare($sql);
	$stmt->bind_param('s', $size);
	$boards = execute_select_stmt($stmt);
	$stmt->close();
	
	$sql = 'select boards.bID, orbs.color, orbs.count from boards left join orbs on boards.bID=orbs.bID where boards.size=?;';
	$stmt = $conn->prepare($sql);
	$stmt->bind_param('s', $size);
	$board_orbs = execute_select_stmt($stmt, 'bID');
	$stmt->close();
	
	$sql = 'select boards.bID, combos.color, styles.style, count(combos.cID) style_counts from boards inner join combos on boards.bID=combos.bID left join styles on combos.cID=styles.cID where boards.size=? group by boards.bID,combos.color,styles.style;';
	$stmt = $conn->prepare($sql);
	$stmt->bind_param('s', $size);
	$board_styles = execute_select_stmt($stmt, 'bID');
	$stmt->close();
	
	foreach($boards as &$board){
		$board['orbs'] = array();
		foreach($board_orbs[$board['bID']] as $orb){
			$board['orbs'][$orb['color']] = $orb['count'];
		}
		$board['styles'] = array();
		foreach($board_styles[$board['bID']] as $style){
			if($style['style'] == null){
				$board['styles']['MISC'][$style['color']] = $style['style_counts'];
			}else{
				$board['styles'][$style['style']][$style['color']] = $style['style_counts'];
			}
		}
	}
	
	return $boards;
}
function display_boards($boards){
	$output = '';
	foreach($boards as $board){
		$output = $output . '<div class="board-box"><div class="board-info float">';
		$ratio_str = '';
		foreach($board['orbs'] as $color => $count){
			$ratio_str = $ratio_str . $count . '-';
		}
		$combo = 0;
		$style_str = '';
		foreach($board['styles'] as $name => $style){
			foreach($style as $color => $count){
				$combo += $count;
				if($name == 'MISC'){
					continue;
				}
				$style_str = $style_str . '<div data-orb="' . $color . '" class="style-box orb-bg ' . $color . '">' . $name . '[' . $count . ']</div>';
			}
		}	
		$output = $output . '<div class="board-ratio-combo">' . substr($ratio_str, 0, -1) . '(' . $combo . 'c)</div><div class="float">' . $style_str . '</div></div>';
		$output = $output . '<a class="board-url" href="solve_boards.php?pattern=' . $board['pattern'] . '">' . get_board($board['pattern'], $board['size']) . '</a>';
		$output = $output . '</div>';
	}
	return '<div class="float">' . $output . '</div>';
}
function att_radios($att_num, $checked = ''){
	$out = '';
	global $var_orb_list;
	foreach ($var_orb_list as $i => $orb){
		$out = $out . '<div><input type="radio" name="attribute-' . $var_orb_list[$att_num] . '" data-attribute="' . $var_orb_list[$att_num] . '-' . $orb . '" value="' . $orb . '"><p class="orb-bg ' . $orb . '"></p></div>';
	}
	return $out;
}
function get_att_change_radios($boards){
	global $var_orb_list;
	$colors = array();
	foreach($boards as $board){
		for($i = 0; $i < sizeof($var_orb_list); $i++){
			if(array_key_exists($var_orb_list[$i], $board['orbs']) && !in_array($i, $colors)){
				$colors[] = $i;
			}
		}
	}
	$out = '';
	foreach($colors as $i){
		$out = $out . att_radios($i, $i);
	}
	$out = '<div class="grid atts">' . $out . '</div>';
	
	return $out;
}
?>