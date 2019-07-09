<?php
	include 'inc.d/settings.php';
	include 'inc.d/model/location.class.php';
	include 'inc.d/model/report.class.php';
	$cxn = new PDO("mysql:dbname={$settings['my']['database']};host={$settings['my']['hostname']}", $settings['my']['username'], $settings['my']['password'], array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8')) or die("DB Connection Failed.");

	$sql = <<<SQL
	SELECT 
		`r`.`rid`, 
		`r`.`problem`, 
		`r`.`comment`, 
		`r`.`timestamp`, 
		`l`.`lid` AS `locLid`, 
		`l`.`street` AS `locStreet`, 
		`l`.`number` AS `locNumber`, 
		`l`.`city` AS `locCity` 
	FROM 
		`report` AS `r` 
	INNER JOIN 
		`location` AS `l`
		ON (`r`.`location` = `l`.`lid`)
SQL;
	header("Content-Type: text/plain");
	$q = $cxn->prepare($sql);
	$q->execute();
	$rep = $q->fetchObject(Report::class);
	echo $rep;

	?>