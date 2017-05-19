<?php
	function closest($array, $number) {
		sort($array);
		foreach ($array as $a) {
	    	if ($a >= $number) return $a;
    	}
    	return end($array);
	}

	function isJapanese($word) {
		return preg_match('/[\x{4E00}-\x{9FBF}\x{3040}-\x{309F}\x{30A0}-\x{30FF}]/u', $word);
	}

	function showOrdinal($num) {
		$theNum = (string) $num;
		$lastDigit = substr($theNum, -1, 1);
		if (strlen($theNum)>1) {
			$nextToLast = substr($theNum, -2, 1);
		}
		else {
			$nextToLast = "";
		}
		
		switch($lastDigit) {
			case "1":
				switch($nextToLast) {
					case "1":
						$theNum.="th";
					break;
					default:
						$theNum.="st";
				}
			break;
			case "2":
				switch($nextToLast) {
					case "1":
						$theNum.="th";
					break;
					default:
						$theNum.="nd";
				}
			break;
			case "3":
				switch($nextToLast) {
					case "1":
						$theNum.="th";
					break;
					default:
						$theNum.="rd";
				}
			break;
			default:
				$theNum.="th";
		}
		
		return $theNum;
	}

	function changeCover($cover) {
		$cutted = substr($cover, 7);
		$exploded = explode(".", $cutted);
		if ($exploded[0] == 'userserve-ak') {
			$replaced = str_replace("/126/", "/174s/", $cover);
			return $replaced;
		}
		else {
			return $cover;
		}
	}

	function cutString($string, $limit) {
		$length = strlen($string);
		if ($length > $limit) {
			$cut = -($length - ($limit-3)); 
			$string = substr($string, 0, $cut);
			$string .= "...";
		}
		return $string;
	}
?>