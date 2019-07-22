<?php
	error_reporting(E_ALL);
	inset("display_errors", 1);
	$settings = array(
		"json_post" => true,
		"lang_default"	=> "nl",
		"lang_path"		=> "inc.d/lang/",

		"area"	=> array(
			"cache_ratio" => 6000
		),

		"my" => array(
			"hostname" => "localhost",
			"database" => "",
			"username" => "",
			"password" => "",
			"instance"	   => null
		),
		"mail" => array(
			"ReportTo"	=> "contact@m-leroy.pro",
			"from"		=> "noreply@tmaas.m-leroy.pro",
			"SMTPDebug"	=> 0,
			"isSMTP"	=> false,
			"Host"		=> "ssl0.ovh.net",
			"SMTPAuth"	=> false,
			"Username"	=> "noreply@tmaas.m-leroy.pro",
			"Password"	=> "",
			"CharSet"	=> "UTF-8",
			"Encoding"	=> "base64",
			"Port" 		=> 587,
			"instance"	=> null
		),

		"api" => array(
			"mapbox"	=> "pk.eyJ1IjoibGExNzkzMzEiLCJhIjoiY2p5NWwwY3hvMDgydTNtbXZ4NHBjNjI3dyJ9.EielE76GDtb81oiYEFVm-w"
		)
	);
	?>