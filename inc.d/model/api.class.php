<?php
	class API
	{
		private static $cxn = null;
		public function __construct()
		{
			global $settings;
			if ($settings['json_post'])
				API::JSONInput2POST();
			//self::getConnection();
		}
		public function run()
		{
			if (!isset($_GET['query'])){
					header("Content-Type: application/json");
				echo Result::jsonError("No query provided");
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
							try {
								new Report($report['problem'], $report['comment'], $report['location']);
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
							echo Result::jsonError("Query is no complete");
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
					include 'inc.d/script/monitor.php';
					break;
				default:
					header("Content-Type: application/json");
					echo Result::jsonError("Unkwon Query");
					break;
			}
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
			$json = array_map(function($value)
			{
				return (is_array($value)&&count($value)==1&&isset($value['constent']))?$value['content']:$value;
			}, $json);
			$_POST = $json;	// Override global $_POST variable
		}
	}
	?>