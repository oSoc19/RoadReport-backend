<?php
	/**
	 * 
	 */
	class Report
	{
		private $rid;		// int
		private $problem;	// int // object 'Problem'?
		private $comment;	// string
		private $location;	// Location
		private $timestamp;	// int
		private $picture_link;//char[55]
		private $status;
		private $status_date;

		function __construct($problem = '', $comment = '', $location = [], $picture = '') {
			if (empty($problem) || $location == [])
				return;
			$this->setProblem($problem);
			$this->setComment($comment);
			$this->setLocation($location['street'], $location['number'], $location['city'], $location['longitude'], $location['latitude']);
			$this->timestamp = time();
			$this->setPicture($picture);
			if (!$this->save())
				throw new Exception("Can't be saved");	// need to be more explicit
		}

		public function setProblem($problem)
		{
			$this->problem = htmlspecialchars(strim($problem));
		}

		public function setComment($comment)
		{
			$this->comment = htmlspecialchars(strim($comment));
		}

		public function setLocation($street, $number, $city, $long, $lat)
		{
			$this->location = new Location($street, $number, $city, $long, $lat);
		}

		public function updateStatus($status)
		{
			$this->status = strtoupper(trim($status));
			$this->status_date = time();
			$cxn = API::getConnection();
			$q = $cxn->prepare("UPDATE `report` SET `status` = :s, `status_date` = :sd WHERE `rid` = :id LIMIT 1");
			$q->bindParam(':s', $this->status, PDO::PARAM_STR);
			$q->bindParam(':sd', $this->status_date, PDO::PARAM_INT);
			$q->bindValue(':id', $this->getID());
			return !!$q->execute();
		}

		public function setPicture($data)
		{
			$data = trim($data);
			if (empty($data))
				return;
			$im = new Image($data, 1920, 1080);
			$path = "image/".date("Y/m/d/");
			$filename = $im->md5().".jpg";
			if (!is_dir($path))
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

		public static function get($id)
		{
			if (!is_numeric($id))
				return false;
			$cxn = API::getConnection();
			$sql = <<<SQL
				SELECT 
					`r`.`rid`, 
					`r`.`problem`, 
					`r`.`comment`, 
					`r`.`timestamp`, 
					`r`.`status`, 
					`r`.`status_date`, 
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
					`r`.`rid` = :id
				LIMIT 1
SQL;
			$s = $cxn->prepare($sql);
			$s->bindParam(':id', $id, PDO::PARAM_INT);
			$s->setFetchMode(PDO::FETCH_CLASS, __CLASS__);
			$s->execute();
			return $s->fetch();
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
					`r`.`status`, 
					`r`.`status_date`, 
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
					`r`.`status`, 
					`r`.`status_date`, 
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
					'last' => "$u?page=".max( 1,floor($ct/50)),
				)
			);
			if ($page > 1)
				$arr['view']['previous'] = "$u?page=".urlencode($page - 1);
			if ($page < floor($ct/50))
				$arr['view']['next'] = "$u?page=".urlencode($page + 1);
			foreach ($probs as $p) {
				$l = $p->getLocation();
				$a = array(
					'@id' => "/problem/".$p->getID(),
					'event' => Lang::replaceTags($p->getProblemTagName(), 'en'),
					'currentStatus' => array(
						'status' => $p->getStatus()
					),
					'comment' => $p->getComment(),
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
				if (!empty($p->getStatusDate())&&$p->getStatusDate()>0)
				{
					$a['currentStatus']['update'] = date(DATE_RFC3339, $p->getStatusDate());
				}
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
		public static function quickMap()
		{
			$cxn = API::getConnection();
			$q = $cxn->prepare("SELECT `r`.`status`, `l`.`longitude`, `l`.latitude FROM `report` `r` INNER JOIN `location` AS `l` ON (`r`.`location` = `l`.`lid`) WHERE `status` <> 'REMOVE'");
			$q->execute();
			return $q->fetchAll(PDO::FETCH_ASSOC);
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
		public function getStatus()
		{
			return $this->status;
		}
		public function getStatusDate()
		{
			return $this->status_date;
		}
		public function getProblemTagName()
		{
			if ($this->problem == null || !is_numeric($this->problem))
				return null;
			$cxn = API::getConnection();
			$q = $cxn->prepare("SELECT `tag_name` FROM `problem` WHERE `id` = :id");
			$q->bindParam(':id', $this->problem, PDO::PARAM_INT);
			$q->execute();
			if ($r = $q->fetch(PDO::FETCH_ASSOC))
				return $r['tag_name'];
			return null;
		}

		public function save()
		{
			if ($this->rid != null)
				return true; // Exist?
			$cxn = API::getConnection();
			$i = $cxn->prepare("INSERT INTO `report`(`problem`, `comment`, `location`, `timestamp`, `picture_link`) VALUES(:problem, :comment, :location, :time, :picture)");
			$this->location->save();
			$loc = $this->location->getLid();
			$i->bindParam(':problem', $this->problem, PDO::PARAM_INT);
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
				throw new Exception("The request is empty or malformed be submitted to the server");
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