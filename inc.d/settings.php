<?php
	error_reporting(E_ALL);
	ini_set("display_errors", 1);
	$settings = array(
		"json_post"		=> true,
		"resize_pics"	=> false,
		"lang_default"	=> "nl",
		"lang_path"		=> "inc.d/lang/",

		"download" => array(
			"visible"	=> false,
			"appstore"	=> "",
			"playstore" => ""
		),

		"area"	=> array(
			"cache_ratio" => 6000
		),

		"my" => array(
			"hostname" => "localhost",
			"database" => "",
			"username" => "",
			"password" => "",
			"instance" => null
		),
		"mail" => array(
			"ReportTo"	=> "",
			"from"		=> "",
			"SMTPDebug"	=> 0,
			"isSMTP"	=> true,
			"Host"		=> "smtp.googlemail.com",
			"SMTPAuth"	=> true,
			"Username"	=> "",
			"Password"	=> "",
			"SMTPSecure"=> "ssl",
			"CharSet"	=> "UTF-8",
			"Encoding"	=> "base64",
			"Port" 		=> 465,
			"instance"	=> null
		),

		"api" => array(
			"mapbox"	=> ""
		)
	);
	?>
