<?php
	class API
	{
		private static $cxn = null;
		public function __construct()
		{
			global $settings;
			if ($settings['json_post'])
				API::JSONInput2POST();
		}
		public function run()
		{
			if (!isset($_GET['query'])){
				include 'inc.d/script/website.php';
				return;//Exception? No query
			}
			$path = explode('/', substr($_GET["query"], 1));
			//var_dump($path);
			switch ($path[0]) {
				case 'problem':
					header("Content-Type: application/json");
					if (count($path)==1){
						echo Result::jsonError("Query is no complete");
						break;//Exception? No complete query
					}
					switch ($path[1]) {
						case 'send':
							if (!isset($_POST['report']))
							{
								echo Result::jsonError("Request doesn't contains report");
								return;
							}
							$report = $_POST['report'];
							// hard fix:
							if (!isset($report['picture']))
								$report['picture'] = '';
							try {
								new Report($report['problem'], $report['comment'], $report['location'], $report['picture']);
								echo '{"result":"success"}';
							} catch (Exception $ex) {
								echo Result::jsonError("Can't be add: ".$ex->getMessage());
							}
						break;
						case 'last':
							$res = Report::getLast(isset($path[2])?$path[2]: 0, 50);
							echo json_encode($res);
							break;
						default:
							if (preg_match("/([0-9]{4})\-([0-9]{2})\-([0-9]{2})/", $path[1], $output_date))
							{
								if (strtotime($output_date[0]) !== false)
								{
									$t = strtotime($output_date[0]);
									if (isset($_GET['page'])&&is_numeric($_GET['page'])&&$_GET['page']>0)
									{
										header("Content-Type: application/ld+json");
										header("Cache-Control: public, max-age=300");
										echo Report::openFormat($t, $_GET['page']);
									}
									else
									{
										header("location: /problem/{$output_date[0]}?page=1");
									}
								}
								else
								{
									echo Result::jsonError("The date can't be parsed.");
								}
							}
							else
							{
								header("location: /problem/".date("Y-m-d").'?page=1');
							}
							break;
					}
				break;
				case 'stats':
					header("Content-Type: application/json");
					if (count($path)==1){
						echo Result::jsonError("Query is no complete");
						break;//Exception? No complete query
					}
					switch ($path[1]) {
						case 'street':
							echo Stat::topDangerousness(isset($path[2])?$path[2]:'TODAY');
							break;
						case 'problem':
							echo Stat::topProblem(isset($path[2])?$path[2]:'TODAY');
							break;
						default:
							echo Result::jsonError("Query is no complete");
							break;
					}
					break;
				case 'monitor':
				case 'dashboard':
					include 'inc.d/script/'.$path[0].'.php';
					break;
				default:
					header("Content-Type: application/json");
					echo Result::jsonError("Unkwon Query");
					break;
			}
		}
		public static function getAPIKey($api)
		{
			global $settings;
			if (isset($settings['api'][$api]))
				return $settings['api'][$api];
			return false;
		}
		public static function getConnection()
		{
			global $settings;
			if ($settings['my']['cxn'] == null) {
				$pdo = new PDO("mysql:dbname={$settings['my']['database']};host={$settings['my']['hostname']}", $settings['my']['username'], $settings['my']['password'], array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
				$settings['my']['cxn'] = $pdo;
			}
			return $settings['my']['cxn'];
		}
		public function JSONInput2POST()	//potato name
		{
			$json = @file_get_contents('php://input');
			//$json = '{"report":{"problem":"Damaged bicycle path","comment":"Problem","location":{"street":"Teststreet","number":"49","city":"Gent"}}}';
			if (empty($json))
				return; // exception?
			// remove useless react object like: somthing:{content:"value"}
			$json = json_decode($json, true);
			$json = @array_map(function($value)
			{
				return (is_array($value)&&count($value)==1&&isset($value['constent']))?$value['content']:$value;
			}, $json);
			if ($json == null)
				return;
			$_POST = $json;	// Override global $_POST variable
		}
	}
	?>