<?php
	session_start();
	/*
	 * Including all modules
	 */
	use PHPMailer\PHPMailer\PHPMailer;
	use PHPMailer\PHPMailer\Exception;
	require 'inc.d/model/phpmailer/Exception.php';
	require 'inc.d/model/phpmailer/PHPMailer.php';
	require 'inc.d/model/phpmailer/SMTP.php';
	include 'inc.d/func.php';
	include 'inc.d/settings.php';
	include 'inc.d/model/lang.class.php';
	include 'inc.d/model/image.class.php';
	include 'inc.d/model/location.class.php';
	include 'inc.d/model/report.class.php';
	include 'inc.d/model/result.class.php';
	include 'inc.d/model/stat.class.php';
	include 'inc.d/model/api.class.php';
	include 'inc.d/model/admin.class.php';

	/*
	 * Create a new instance of the API and run it
	 */
	$api = new API();
	$api->run();