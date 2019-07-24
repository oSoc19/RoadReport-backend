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
				ob_start();
				include 'inc.d/script/website.php';
				$html = ob_get_contents();
				ob_end_clean();
				echo Lang::replaceTags($html);
				return;
			}
			
			$path = explode('/', substr($_GET["query"], 1));
			//var_dump($path);
			switch ($path[0]) {
				case 'debug':
					$cxn = API::getConnection();
					API::mail("contact@m-leroy.pro", "Test send", "Hello from the hell");
					die;
					$q = $cxn->prepare("SELECT * FROM post_json where id = 89");
					$q->execute();
					if ($r = $q->fetch(PDO::FETCH_ASSOC))
					{
						var_dump($r);
						die;
					}
					break;
				case 'problem':
					header("Content-Type: application/json");
					switch ($path[1]) {
						case 'send':
							if ($_SERVER['HTTP_CONTENT_TYPE']=="multipart/form-data")
							{
								$in = @file_get_contents('php://input');
								$ln = explode("\n", str_replace("\r", '', $in));
								for ($i=0; $i < count($ln); $i++)
								{ 
									if (preg_match("/name=\"([^\"]*)\"/i", $ln[$i], $name))
									{
										if (isset($ln[$i+2]))
										{
											$_POST[$name[1]] = $ln[$i+=2];
										}
									}
								}
							}
							try {
								// adapt to contexts:
								if (isset($_POST['data']))
								{
									$_POST['report'] = json_decode($_POST['data'], true)["report"];
								}
								if (!isset($_POST['report']))
								{
									echo Result::jsonError("Request doesn't contains report");
									return;
								}
								$report = $_POST['report'];
								if (isset($_FILES['file']))
									$report['picture'] = $_FILES['file']['tmp_name'];
								if (!isset($report['picture']))
									$report['picture'] = '';
								// check fields
								if (@empty($report['problem']))
									throw new Exception('No problem provided');
								if (@empty($report['location']['street'])||@empty($report['location']['city']))
									throw new Exception('No address provided, please select in the list');
								$r = new Report($report['problem'], $report['comment'], $report['location'], $report['picture']);
								echo '{"result":"success"}';
								API::mail("contact@m-leroy.pro", "New report", $r);
							} catch (Exception $ex) {
								echo Result::jsonError($ex->getMessage());
							}
						break;
						case 'last':
							$res = Report::getLast(isset($path[2])?$path[2]: 0, 50);
							echo json_encode($res);
							break;
						case 'map':
							header("Cache-Control: public, max-age=300");
							echo json_encode(Report::quickMap());
							break;
						case 'update':
							if (!$this->hasSuperAccess())
							{
								echo Result::jsonError("Access forbidden");
								return;
							}
							if (!(isset($_POST['id'])&&isset($_POST['status'])&&is_numeric($_POST['id'])))
							{
								echo Result::jsonError("ID or Status not specified");
								return;
							}
							if ($r = Report::get($_POST['id']))
							{
								if ($r->updateStatus($_POST['status']))
								{
									echo '{"result":"success"}';
									break;
								}
							}
							echo Result::jsonError("The report can't be update");
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
							elseif (preg_match("/([0-9]+)/", $path[1], $output_id))
							{
								$r = Report::get($output_id[1]);
								echo $r;
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
		private function hasSuperAccess()
		{
			$cxn = API::getConnection();
			$q = $cxn->prepare("SELECT 'OK' as `result` FROM `iptable_whitelist` WHERE `ip_address` = :ip");
			$q->bindParam(':ip', $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR, 15);
			$q->execute();
			return !!$q->fetch(PDO::FETCH_ASSOC);
		}

		public static function getAreaData()
		{
			global $settings;
			$cxn = API::getConnection();
			$q = $cxn->prepare("SELECT `k`, `v` FROM `params` WHERE `k` IN ('area_offsetX', 'area_offsetY', 'area_width', 'area_height')");
			$q->execute();
			$tmp = array();
			while ($r = $q->fetch(PDO::FETCH_ASSOC))
				$tmp[substr($r['k'], 5)] = $r['v'];
			$tmp['ratio'] = $settings['area']['cache_ratio'];
			return $tmp;
		}

		public static function get_select_tag_problem($name = "problem")
		{
			$cxn = API::getConnection();
			$tmp = '<select name="'.htmlspecialchars($name).'"">';
			$q = $cxn->prepare("SELECT * FROM `problem_category`");
			$q->execute();
			while ($cat = $q->fetch(PDO::FETCH_ASSOC)) {
				$tmp.='<optgroup label="'.$cat['tag_name'].'">';
				$q2 = $cxn->prepare("SELECT * FROM `problem` WHERE `category` = :cat");
				$q2->bindParam(':cat', $cat['id'], PDO::PARAM_INT);
				$q2->execute();
				while ($p = $q2->fetch(PDO::FETCH_ASSOC)) {
					$tmp.='<option value="'.$p['id'].'">'.$p['tag_name'].'</option>';
				}
				$tmp.='</optgroup>';
			}
			$tmp.= '</select>';
			return $tmp;
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
			if ($settings['my']['instance'] == null)
			{
				$pdo = new PDO("mysql:dbname={$settings['my']['database']};host={$settings['my']['hostname']}", $settings['my']['username'], $settings['my']['password'], array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
				$settings['my']['instance'] = $pdo;
			}
			return $settings['my']['instance'];
		}

		public static function mail($to, $subject, $message)
		{
			if (@empty($message)||is_array($message))
				return false;
			global $settings;
			$mail = $settings['mail']['instance'];
			if ($settings['mail']['instance'] == null)
			{
				$mail = new PHPMailer\PHPMailer\PHPMailer();
				$mail->SMTPDebug= $settings['mail']['SMTPDebug'];
				$mail->isSMTP($settings['mail']['isSMTP']);
				$mail->Host		= $settings['mail']['Host'];
				$mail->SMTPAuth	= $settings['mail']['SMTPAuth'];
				$mail->Username = $settings['mail']['Username'];
				$mail->Password = $settings['mail']['Password'];
				$mail->CharSet	= $settings['mail']['CharSet'];
				$mail->Encoding = $settings['mail']['Encoding'];
				$mail->SMTPSecure=$settings['mail']['SMTPSecure'];
				$mail->Port		= $settings['mail']['Port'];
			}
			$mail->setFrom($settings['mail']['from'], 'Noreply');
			$mail->addAddress($to, 'City Service');
			$mail->addReplyTo($settings['mail']['from'], 'Noreply');
			$mail->isHTML(true);
			$mail->Subject = $subject;
			$mail->Body = $message;
			$mail->AltBody = @strip_tags(@preg_replace('#<br\s*/?>#i', "\n", $message));
			return $mail->send();
		}

		public function JSONInput2POST()	//potato name
		{
			$json = @file_get_contents('php://input');
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