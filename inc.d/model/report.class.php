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

		function __construct($problem = '', $comment = '', $location = []) {
			if (empty($problem) || $location == [])
				return;
			$this->setProblem($problem);
			$this->setComment($comment);
			$this->setLocation($location['street'], $location['number'], $location['city']);
			$this->timestamp = time();
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
					`l`.`city` AS `locCity` 
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

		public function getLocation()
		{
			return $this->location;
		}

		public function save()
		{
			if ($this->rid != null)
				return true; // Exist?
			$cxn = API::getConnection();
			$i = $cxn->prepare("INSERT INTO `report`(`problem`, `comment`, `location`, `timestamp`) VALUES(:problem, :comment, :location, :time)");
			$this->location->save();
			$loc = $this->location->getLid();
			$i->bindParam(':problem', $this->problem, PDO::PARAM_STR, 63);
			$i->bindParam(':comment', $this->comment, PDO::PARAM_STR, 512);
			$i->bindParam(':location', $loc, PDO::PARAM_INT);
			$i->bindParam(':time', $this->timestamp, PDO::PARAM_INT);
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