<?php
	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	$settings = array(
		'json_post' => true,

		'area'	=> array(
			'cache_ratio' => 6000
		),

		'my' => array(
			"hostname" => "localhost",
			"database" => "",
			"username" => "",
			"password" => "",
			"cxn"	   => null
		),

		'api' => array(
			'mapbox'	=> ""
		)
	);
	?>