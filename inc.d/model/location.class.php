<?php
	/**
	 * 
	 */
	class Location
	{
		private $lid;		//int
		private $street;	//string
		private $number;	//string
		private $city;		//string
		
		function __construct($street = '', $number = '', $city = '') {
			if (empty($street)||empty($city))
				return;
			$this->setStreet($street);
			$this->setNumber($number);
			$this->setCity($city);
			$this->save();
		}

		public function setLid($id)
		{
			$this->lid = $id;
		}
		public function setStreet($street)
		{
			$this->street = strim($street);
		}
		public function setNumber($nb)
		{
			$this->number = strim($nb);
		}
		public function setCity($city)
		{
			$this->city = strim($city);
		}
		public function save()	// potatato function, need to be improve
		{
			if ($this->lid != null)
				return $this->lid;
			$cxn = API::getConnection();
			$s = $cxn->prepare("SELECT `lid` FROM `location` WHERE `street`=:street AND `number`=:number AND `city`=:city LIMIT 1");
			$s->bindParam(':street', $this->street, PDO::PARAM_STR, 63);
			$s->bindParam(':number', $this->number, PDO::PARAM_STR, 8);
			$s->bindParam(':city', $this->city, PDO::PARAM_STR, 63);
			$s->execute();
			if ($r = $s->fetch(PDO::FETCH_ASSOC))
				return $this->lid = $r['lid'];
			$i = $cxn->prepare("INSERT INTO `location`(`street`, `number`, `city`) VALUES(:street, :number, :city)");
			$i->bindParam(':street', $this->street, PDO::PARAM_STR, 63);
			$i->bindParam(':number', $this->number, PDO::PARAM_STR, 8);
			$i->bindParam(':city', $this->city, PDO::PARAM_STR, 63);
			if ($i->execute())
				return $cxn->lastInsertId();
			return -1; //Exception
		}
		public function getLid()
		{
			return $this->lid;
		}
		public function toArray()
		{
			return array(
				"lid"	=>	$this->lid,
				"street"=>	$this->street,
				"number"=>	$this->number,
				"city"	=>	$this->city
			);
		}
		public function __toString()
		{
			return json_encode($this->toArray());
		}
	}
	?>