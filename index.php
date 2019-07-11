<?php
	/*
	 * Including all modules
	 */
	include 'inc.d/func.php';
	include 'inc.d/settings.php';
	include 'inc.d/model/image.class.php';
	include 'inc.d/model/location.class.php';
	include 'inc.d/model/report.class.php';
	include 'inc.d/model/result.class.php';
	include 'inc.d/model/stat.class.php';
	include 'inc.d/model/api.class.php';

	/*
	 * Create a new instance of the API and run it
	 */
	$api = new API();
	$api->run();