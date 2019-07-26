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
			"database" => "tmaas_database",
			"username" => "tmaas_user",
			"password" => "aM7xGyqIXi56V034",
			"instance" => null
		),
		"mail" => array(
			"reportTo"	=> "contact@m-leroy.pro",
			"from"		=> "osoc.tmaas@gmail.com",
			"SMTPDebug"	=> 0,
			"isSMTP"	=> true,
			"Host"		=> "smtp.googlemail.com",
			"SMTPAuth"	=> true,
			"Username"	=> "osoc.tmaas@gmail.com",
			"Password"	=> "Ma48T9f1ByrX2Lg1",
			"SMTPSecure"=> "ssl",
			"CharSet"	=> "UTF-8",
			"Encoding"	=> "base64",
			"Port" 		=> 465,
			"instance"	=> null
		),

		"api" => array(
			"mapbox"	=> "pk.eyJ1IjoibGExNzkzMzEiLCJhIjoiY2p5ZnhkbmtzMWdoeDNtbzNvdzM3YzF6cyJ9.-uSxvCtzNpKyv6WGCTnvkw"
		)
	);
	?>
