<?php
	function strim($str)
	{
		$str = preg_replace("/\s+/", ' ', $str);
		return trim($str);
	}
	function median($arr, $p = 50)
	{
		$n = count($arr);
		sort($arr);
		$reste = (fmod($n +1) * $p / 100);
		$key_inf = floor(($n +1) * $p / 100);
		if ($reste != 0)
		{ 
			return ($arr[$key_inf] + $arr[$key_inf +1]) / 2;
		} 
		else
		{
			$ord = ($n +1) * $p / 100;
			return 	$arr[$ord];
		}
	}
	?>