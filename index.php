<?php
	/*$json = @file_get_contents('php://input');
		$icxn = mysqli_connect("127.0.0.1", "tmaas_user", "zB40~2R4jw!=NH[n", "tmaas_database");
	if (!empty($json)) {
		header("Content-Type: application/json");
		// JUST FOR DEBUG DON'T KILL ME :(
		$v = addslashes($json);
		$fuckme = (mysqli_query($icxn, "INSERT INTO post_json(value)VALUES('{$v}')"));
			
	} */



	include 'inc.d/func.php';
	include 'inc.d/settings.php';
	include 'inc.d/model/location.class.php';
	include 'inc.d/model/report.class.php';
	include 'inc.d/model/result.class.php';
	include 'inc.d/model/stat.class.php';
	include 'inc.d/model/api.class.php';

	$api = new API();
	$api->run();