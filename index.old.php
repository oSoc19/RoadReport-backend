<?php
	$json = @file_get_contents('php://input');
		$icxn = mysqli_connect("127.0.0.1", "tmaas_user", "zB40~2R4jw!=NH[n", "tmaas_database");
	if (!empty($json)) {
		header("Content-Type: application/json");
		// JUST FOR DEBUG DON'T KILL ME :(
		$v = addslashes($json);
		if (mysqli_query($icxn, "INSERT INTO post_json(value)VALUES('{$v}')"))
			echo '{"result":"success"}';
		else
			echo '{"result":"error"}';
	} else {
		header("Content-Type: application/json; charset=utf-8");
		header("Refresh:5");
		$q = mysqli_query($icxn, "SELECT * FROM post_json");
		echo "[";
		while ($r = mysqli_fetch_array($q))
			echo ($r['value']).",\n\r";
		echo "0]";

		/*echo "GET:\n\r";
		var_dump($_GET);
		echo "POST:\n\r";
		var_dump($_POST);*/
	}