<?php
	/**
	 * 
	 */
	class Dashboard
	{
		private $logged = false;

		public function __construct(){}

		public function isLogged($status = null)
		{
			if ($status == null)
				return $this->logged;
			$this->logged = !!$status;
			return $this->logged;
		}

		public function getAllowedIPList()
		{
			if (!$this->logged)
				return;
			$cxn = API::getConnection();
			$q = $cxn->prepare("SELECT `ip_address` FROM `iptable_whitelist`");
			$q->execute();
			$tmp = array();
			while ($r = $q->fetch(PDO::FETCH_ASSOC))
			{
				array_push($tmp, $r['ip_address']);
			}
			return $tmp;
		}

		public function addAllowedIP($ip)
		{
			if (!$this->logged)
				return;
			if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4))
				return false;
			$cxn = API::getConnection();
			$q = $cxn->prepare("INSERT INTO `iptable_whitelist` (`ip_address`) VALUES(:ip)");
			$q->bindParam(':ip', $ip, PDO::PARAM_STR, 15);
			return !! $q->execute();
		}

		public function removeAllowedIP($ip)
		{
			if (!$this->logged)
				return;
			if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4))
				return false;
			$cxn = API::getConnection();
			$q = $cxn->prepare("DELETE FROM `iptable_whitelist` WHERE `ip_address`=:ip");
			$q->bindParam(':ip', $ip, PDO::PARAM_STR, 15);
			return !! $q->execute();
		}

		public function setArea($geojson)
		{
			global $settings;
			if (!$this->logged || empty($geojson))
				return false;
			// IMG PART (cache)
			$geoobj = json_decode($geojson, true);
			$minX = $minY = 180;
			$maxX = $maxY = -180;
			$polygons = array();
			for ($i=0; $i < count($geoobj['features']); $i++) { 
				$polygons[$i] = array();
				$points = $geoobj['features'][$i]['geometry']['coordinates'][0];
				for ($j=0; $j < count($points); $j++) { 
					$minX = min($minX, $points[$j][0]);
					$maxX = max($maxX, $points[$j][0]);
					$minY = min($minY, $points[$j][1]);
					$maxY = max($maxY, $points[$j][1]);
					array_push($polygons[$i], $points[$j][0]);
					array_push($polygons[$i], $points[$j][1]);
				}
			}
			$im = imagecreatetruecolor(floor(($maxX - $minX)*$settings['area']['cache_ratio']), floor(($maxY-$minY)*$settings['area']['cache_ratio']));
			$red = imagecolorallocate($im, 255, 0, 0);
			for ($i=0; $i < count($polygons); $i++) {
				for ($j=0; $j < count($polygons[$i]); $j+=2) {
					$polygons[$i][$j] = (int) round(($polygons[$i][$j] - $minX) * $settings['area']['cache_ratio']);
					$polygons[$i][$j+1] = (int) round(($polygons[$i][$j+1] - $minY) * $settings['area']['cache_ratio']);
				}
				imagefilledpolygon($im, $polygons[$i], count($polygons[$i])/2, $red);
			}
			imagepng($im, 'image/area_cache.png');
			imagedestroy($im);
			// SQL PART
			$cxn = API::getConnection();
			$qx = $cxn->prepare("UPDATE `params` SET `v` = :ox WHERE `k` = 'area_offsetX' LIMIT 1");
			$qx->bindParam(':ox', $minX, PDO::PARAM_STR);
			$qx->execute();
			$qy = $cxn->prepare("UPDATE `params` SET `v` = :oy WHERE `k` = 'area_offsetY' LIMIT 1");
			$qy->bindParam(':oy', $minY, PDO::PARAM_STR);
			$qy->execute();
			$qa = $cxn->prepare("UPDATE `params` SET `v` = :geoj WHERE `k` = 'area' LIMIT 1");
			$qa->bindParam(':geoj', $geojson, PDO::PARAM_STR);
			return !!$qa->execute();
		}

		public function getArea()
		{
			if (!$this->logged)
				return '{}}';
			$cxn = API::getConnection();
			$q = $cxn->prepare("SELECT `v` as `geojson` FROM `params` WHERE `k` = 'area' LIMIT 1");
			$q->execute();
			if ($r = $q->fetch(PDO::FETCH_ASSOC))
				return $r['geojson'];
			return '{}';
		}

		public function login($user, $pass)
		{
			$cxn = API::getConnection();
			$hash = $this->hash_pass($pass);
			$q = $cxn->prepare("SELECT COUNT(*) AS `check` FROM `params` WHERE (`k` = 'dash_user' AND `v` = :user) OR (`k` = 'dash_pass' AND `v` = :hash)");
			$q->bindParam(':user', $user, PDO::PARAM_STR, 255);
			$q->bindParam(':hash', $hash, PDO::PARAM_STR, 255);
			$q->execute();
			if ($r = $q->fetch(PDO::FETCH_ASSOC))
				return $this->isLogged($r['check'] == 2);
			return false;
		}

		private function hash_pass($pass)
		{
			return md5($pass);
		}
	}