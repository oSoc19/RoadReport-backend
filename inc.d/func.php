<?php
	function strim($str)
	{
		$str = preg_replace("/\s+/", ' ', $str);
		return trim($str);
	}
	?>