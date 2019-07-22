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
		private $longitude;	//float
		private $latitude;	//float
		
		function __construct($street = '', $number = '', $city = '', $long = 0, $lat = 0) {
			if (empty($street)||empty($city))
				return;
			$this->setStreet($street);
			$this->setNumber($number);
			$this->setCity($city);
			$this->setLongitude($long);
			$this->setLatitude($lat);
			$this->save();
		}

		public function setLid($id)
		{
			$this->lid = $id;
		}
		public function setStreet($street)
		{
			$this->street = htmlspecialchars(strim($street));
		}
		public function setNumber($nb)
		{
			$this->number = htmlspecialchars(strim($nb));
		}
		public function setCity($city)
		{
			$this->city = htmlspecialchars(strim($city));
		}
		public function setLongitude($long)
		{
			$this->longitude = $long;
		}
		public function setLatitude($lat)
		{
			$this->latitude = $lat;
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
			$i = $cxn->prepare("INSERT INTO `location`(`street`, `number`, `city`, `longitude`, `latitude`) VALUES(:street, :number, :city, :lon, :lat)");
			$i->bindParam(':street', $this->street, PDO::PARAM_STR, 63);
			$i->bindParam(':number', $this->number, PDO::PARAM_STR, 8);
			$i->bindParam(':city', $this->city, PDO::PARAM_STR, 63);
			$i->bindValue(':lon', strval($this->longitude), PDO::PARAM_STR);
			$i->bindValue(':lat', strval($this->latitude), PDO::PARAM_STR);
			if ($i->execute())
				return $cxn->lastInsertId();
			return -1; //Exception
		}
		public function getLid()
		{
			return $this->lid;
		}
		public function getStreet()
		{
			return $this->street;
		}
		public function getNumber()
		{
			return $this->number;
		}
		public function getCity()
		{
			return $this->city;
		}
		public function getLongitude()
		{
			return $this->longitude;
		}
		public function getLatitude()
		{
			return $this->latitude;
		}
		public function toArray()
		{
			return array(
				"lid"	=>	$this->lid,
				"street"=>	$this->street,
				"number"=>	$this->number,
				"city"	=>	$this->city,
				"longitude"=>$this->longitude,
				"latitude"=>$this->latitude
			);
		}
		public function __toString()
		{
			return json_encode($this->toArray());
		}
	}
	?>