<?php
	include 'inc.d/func.php';
	include 'inc.d/settings.php';
	include 'inc.d/model/location.class.php';
	include 'inc.d/model/report.class.php';
	include 'inc.d/model/result.class.php';
	include 'inc.d/model/stat.class.php';
	include 'inc.d/model/api.class.php';

	$api = new API();
	$api->run();