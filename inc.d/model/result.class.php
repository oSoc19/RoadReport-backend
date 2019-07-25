<?php
	/**
	 * 
	 */
	class Result
	{
		private $result = "error";
		function __construct() { }

		public static function jsonError($info)
		{
			return json_encode(array("result" => "error", "errorInfo" => $info));
		}
		public static function jsonSuccess($content)
		{
			return Lang::replaceTags(json_encode(array("result" => "success", "content" => $content)));
		}
	}
	?>