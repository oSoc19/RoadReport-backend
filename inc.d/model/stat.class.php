<?php
	/**
	 * 
	 */
	class Stat
	{
		static function topDangerousness($until)
		{
			$cxn = API::getConnection();
			$time = Stat::until2time($until);

			$sql = <<<SQL
				SELECT 
					`l`.`street`,
					COUNT(*) AS `nb_report`
				FROM 
					`report` AS `r` 
				INNER JOIN 
					`location` AS `l`
					ON (`r`.`location` = `l`.`lid`)
				WHERE
					`r`.`timestamp` > :time
				GROUP BY
					`l`.`city`, `l`.`street`
				ORDER BY
					`nb_report` DESC
				LIMIT 6
SQL;
			$q = $cxn->prepare($sql);
			$q->bindParam(':time', $time, PDO::PARAM_INT);
			$q->execute();
			$data = [];
			if ($tmp = $q->fetchAll(PDO::FETCH_ASSOC))
				$data = $tmp;
			$res = array(
				'info' => array(
					'until' => $time,
					'mode' => Stat::mode($data, 'street', 'nb_report')
				),
				'data' => $data
			);
			return Result::jsonSuccess($res);
		}

		static public function topProblem($until)
		{
			$cxn = API::getConnection();
			$time = Stat::until2time($until);

			$sql = <<<SQL
				SELECT 
					`r`.`problem`,
					COUNT(*) AS `nb_problem`
				FROM 
					`report` AS `r` 
				WHERE
					`r`.`timestamp` > :time
				GROUP BY
					`r`.`problem`
				ORDER BY
					`nb_problem` DESC
				LIMIT 6
SQL;
			$q = $cxn->prepare($sql);
			$q->bindParam(':time', $time, PDO::PARAM_INT);
			$q->execute();
			$data = [];
			if ($tmp = $q->fetchAll(PDO::FETCH_ASSOC))
				$data = $tmp;
			$res = array(
				'info' => array(
					'until' => $time
				),
				'data' => $data
			);
			return Result::jsonSuccess($res);
		}

		static private function until2time($until)
		{
			$time = 0;
			$until = strtoupper($until);
			if ($until == 'WEEK')
				$time = strtotime('-'.date('w').' days');
			elseif ($until == 'MONTH')
				$time = strtotime(date("Y-m-01"));
			else // TODAY
				$time = strtotime(date("Y-m-d"));
			return $time;
		}

		static public function mode($arr, $k, $v)
		{
			if (!is_array($arr) || !isset($arr[0]) || !isset($arr[0][$v]) || !isset($arr[0][$k]))
				return [];
			$max = $arr[0][$v];
			for ($i = 1; $i < count($arr); $i++)
			{ 
				if (!isset($arr[$i][$v]))
					continue;
				if ($max < $arr[$i][$v])
					$max = $arr[$i][$v];
			}
			$tmp = array();
			foreach ($arr as $val)
				if (isset($val[$v])&&isset($val[$k])&&$val[$v]==$max)
					array_push($tmp, $val[$k]);
			return $tmp;
		}
	}