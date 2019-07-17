<?php
	/**
	 * 
	 */
	class Report
	{
		private $rid;		// int
		private $problem;	// string
		private $comment;	// string
		private $location;	// Location
		private $timestamp;	// int
		private $picture_link;//char[55]

		function __construct($problem = '', $comment = '', $location = [], $picture = '') {
			if (empty($problem) || $location == [])
				return;
			$this->setProblem($problem);
			$this->setComment($comment);
			$this->setLocation($location['street'], $location['number'], $location['city']);
			$this->timestamp = time();
			$this->setPicture($picture);
			if (!$this->save())
				throw new Exception("Can't be saved");	// need to be more explicit
		}

		public function setProblem($problem)
		{
			$this->problem = strim($problem);
		}

		public function setComment($comment)
		{
			$this->comment = strim($comment);
		}

		public function setLocation($street, $number, $city)
		{
			$this->location = new Location($street, $number, $city);
		}

		public function setPicture($data)
		{
			$data = trim($data);
			if (empty($data))
				return;
			$im = new Image($data, 1920, 1080);
			$path = "image/".date("Y/m/d/");
			$filename = $im->md5().".jpg";
			mkdir($path, 0777, true);
			$this->picture_link = $path.$filename;
			$im->save($this->picture_link);
		}

		public function __set($name, $value)
		{
			if ($this->location == null)
				$this->location = new Location();
			if (substr($name, 0, 3)=="loc" && method_exists($this->location, 'set'.substr($name, 3)))
				call_user_func(array($this->location, 'set'.substr($name, 3)), $value);
		}

		public static function getLast($from = 0, $limit = 50)
		{
			// exception nan | math max
			$cxn = API::getConnection();
			$sql = <<<SQL
				SELECT 
					`r`.`rid`, 
					`r`.`problem`, 
					`r`.`comment`, 
					`r`.`timestamp`, 
					`l`.`lid` AS `locLid`, 
					`l`.`street` AS `locStreet`, 
					`l`.`number` AS `locNumber`, 
					`l`.`city` AS `locCity`, 
					`l`.`longitude` AS `locLongitude`, 
					`l`.`latitude` AS `locLatitude` 
				FROM 
					`report` AS `r` 
				INNER JOIN 
					`location` AS `l`
					ON (`r`.`location` = `l`.`lid`)
				WHERE
					`r`.`timestamp` > :from
				ORDER BY
					`r`.`timestamp`
				LIMIT :limit
SQL;
			$s = $cxn->prepare($sql);
			$s->bindParam(':from', $from, PDO::PARAM_INT);
			$s->bindParam(':limit', $limit, PDO::PARAM_INT);
			$s->execute();
			$res = $s->fetchAll(PDO::FETCH_CLASS, __CLASS__);
			$res = array_map(function($r)
			{
				return $r->toArray();
			}, $res);
			return $res;
		}

		public static function openFormat($time, $page)
		{
			$cxn = API::getConnection();
			$afterday = $time + 3600*24;
			$limit = ($page-1)*50;
			$sql = <<<SQL
				SELECT 
					`r`.`rid`, 
					`r`.`problem`, 
					`r`.`comment`, 
					`r`.`timestamp`, 
					`l`.`lid` AS `locLid`, 
					`l`.`street` AS `locStreet`, 
					`l`.`number` AS `locNumber`, 
					`l`.`city` AS `locCity`, 
					`l`.`longitude` AS `locLongitude`, 
					`l`.`latitude` AS `locLatitude` 
				FROM 
					`report` AS `r` 
				INNER JOIN 
					`location` AS `l`
					ON (`r`.`location` = `l`.`lid`)
				WHERE
					`r`.`timestamp` BETWEEN
						:day AND :afterday
				ORDER BY
					`r`.`timestamp`
				LIMIT :limit, 50
SQL;
			$s = $cxn->prepare($sql);
			$s->bindParam(':day', $time, PDO::PARAM_INT);
			$s->bindParam(':afterday', $afterday, PDO::PARAM_INT);
			$s->bindParam(':limit', $limit, PDO::PARAM_INT);
			$s->execute();
			$probs = $s->fetchAll(PDO::FETCH_CLASS, __CLASS__);
			$ct = Report::countInterval($time, $afterday);
			$u = "https://{$_SERVER['SERVER_NAME']}/problem/".date('Y-m-d', $time);
			$arr = array(
				'@context' => array(
					"https://www.w3.org/ns/activitystreams", 
					"http://www.w3.org/ns/hydra/context.jsonld"
				),
				'@id' => "https://tmaas.m-leroy.pro/problem",
				'summary' => "Road Report Gent App, database dump",
				'@type' => "Collection",
				'update' => date(DATE_RFC3339, $ct == 0 ? null : $probs[count($probs)-1]->timestamp),
				'totalItems' => min(50, count($probs)),
				'member' => array(),
				'view' => array(
					'@id' => "$u?page=".urlencode($page),
					'@type' => "PartialCollectionView",
					'first' => "$u?page=1",
					'last' => "$u?page=".floor($ct/50),
				)
			);
			if ($page > 1)
				$arr['view']['previous'] = "$u?page=".urlencode($page - 1);
			if ($page < floor($ct/50))
				$arr['view']['next'] = "$u?page=".urlencode($page + 1);
			foreach ($probs as $p) {
				$l = $p->getLocation();
				$a = array(
					'@id' => "/problem/".$page,
					'event' => $p->getProblem(),
					'currentStatus' => "OTHER",
					'comment' => $p->getProblem(),
					'published' => date(DATE_RFC3339, $p->getTime()),
					'location' => array(
						'@id' => "/location/".$l->getLid(),
						'type' => "Place",
						'name' => $l->getStreet().', '.$l->getCity(),
						'longitude' => $l->getLongitude(),
						'latitude' => $l->getLatitude(),
						'address' => array(
							'street' => $l->getStreet(),
							'houseNumber' => $l->getNumber(),
							'city' => $l->getCity()
						),
						'geojson' => array(
							'type' => "FeatureCollection",
							'features' => array(
								array(
									'type' => "Feature",
									'properties' => array(),
									'geometry' => array(
										'type' => "Point",
										'coordinates' => array(
											$l->getLongitude(),
											$l->getLatitude()
										)
									)
								)
							)
						),
						'wkt' => "POINT ({$l->getLongitude()} {$l->getLatitude()})"
					)
				);
				if (!empty($p->getPicture()))
				{
					$a['picture'] = array(
						'type' => "Image",
						'name' => "Picture attached",
						'url' => array(
							'type' => "Link",
							"href" => $p->getPicture(),
							"mediaType" => "image/jpeg"
						)
					);
				}
				array_push($arr['member'], $a);
			}
			return json_encode($arr);
		}

		public static function countInterval($from, $to)
		{
			$cxn = API::getConnection();
			$q = $cxn->prepare("SELECT COUNT(*) as `count` FROM `report` WHERE `timestamp` BETWEEN :from AND :to");
			$q->bindParam(':from', $from, PDO::PARAM_INT);
			$q->bindParam(':to', $to, PDO::PARAM_INT);
			$q->execute();
			if ($r = $q->fetch(PDO::FETCH_ASSOC))
				return $r['count'];
			return 0;
		}
		public function getID()
		{
			return $this->rid;
		}
		public function getProblem()
		{
			return $this->problem;
		}
		public function getComment()
		{
			return $this->comment;
		}
		public function getTime()
		{
			return $this->timestamp;
		}
		public function getPicture()
		{
			return $this->picture_link;
		}
		public function getLocation()
		{
			return $this->location;
		}

		public function save()
		{
			if ($this->rid != null)
				return true; // Exist?
			$cxn = API::getConnection();
			$i = $cxn->prepare("INSERT INTO `report`(`problem`, `comment`, `location`, `timestamp`, `picture_link`) VALUES(:problem, :comment, :location, :time, :picture)");
			$this->location->save();
			$loc = $this->location->getLid();
			$i->bindParam(':problem', $this->problem, PDO::PARAM_STR, 63);
			$i->bindParam(':comment', $this->comment, PDO::PARAM_STR, 512);
			$i->bindParam(':location', $loc, PDO::PARAM_INT);
			$i->bindParam(':time', $this->timestamp, PDO::PARAM_INT);
			$i->bindParam(':picture', $this->picture_link, PDO::PARAM_STR, 54);
			if ($i->execute())
			{
				return true;
			}
			else
			{
				throw new Exception($i->debugDumpParams());
			}
		}

		public function toArray()
		{
			return array(
				"rid"	=>	$this->rid,
				"problem"=>	$this->problem,
				"comment"	=>	$this->comment,
				"location"	=>	$this->location->toArray(),
				"timestamp"	=>	$this->timestamp
			);
		}

		public function __toString()
		{
			return json_encode($this->toArray());
		}
	}
	?>