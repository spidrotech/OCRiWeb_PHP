<?php

/**
 * Adaptator to HpcStats
 * Adaptator to HpcStats based on postgreSql.
 * @package dataAccess
 */

class HpcStats{
	private $connector;
	private $max_time;
	public function HpcStats($config){
	$this->max_time=$config['max_time'];
		try{
			$this->connector= new PDO('pgsql:host='.$config['db_host'].';port='.$config['db_port'].';dbname='.$config['db_name'], $config['db_user'], $config['db_pass']);
			//$this->connector= new PDO('pgsql:host=127.0.0.1;port=5432;dbname=hpcstatsdb', 'hpcstats');
			$this->connector->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}catch(PDOException $e){
			echo $e->getMessage();
			die;
		}
	}
	
	private function createEndDate($startMonth, $endMonth, $year){
		$sMonth=($endMonth<10)?'0'.$endMonth:$endMonth;
		$endYear=$this->endYear($startMonth, $endMonth, $year);
		return $endYear.'-'.$sMonth.'-'.cal_days_in_month(CAL_GREGORIAN, $endMonth, $endYear).' 23:59:59';
	}
	
	private function createStartDate($month, $year){
		$sMonth=($month<10)?'0'.$month:$month;
		return $year.'-'.$sMonth.'-'.'01 00:00:00';
	}
	
	public function endYear($startMonth, $endMonth, $year){
		return ($endMonth-$startMonth>-1)?$year:$year+1;
	}
	
	/**
	*replace the :variable in the query by ? and bind the corresponding value in the $variables with correct index, and return the statement with binded value
	*$sql String the SQL query with place older like ':variable'
	*$variables Array of tuple ':placeholder' => value
	*@return PDO::Statement
	**/
	private function prepareQueryWithVariables($sql,array $variables){
		$pattern='/';
		foreach($variables as $key => $value) $pattern.='|'.$key;
		$pattern.='/';
		$bindArray=array();

		$callback = function($match) use (&$bindArray,$variables){
			if($match[0]){
				array_push($bindArray,$variables[$match[0]]);
				return $match[0]='?';
			}
		};
		$sql=preg_replace_callback($pattern,$callback,$sql);
		
		try{
			$statement=$this->connector->prepare($sql);
		}catch(PDOException $e){
			echo $e->getMessage();
			die;
		}
		
		foreach($bindArray as $i => $value){
			$statement->bindValue($i+1,$value);
		}
		return $statement;
	}
	
	public function clusterList(){
		$clusterList=array();
		try{
			foreach($this->connector->query('SELECT name FROM clusters ORDER BY name') as $row) {
				array_push($clusterList,$row['name']);
			}
		}catch(PDOException $e){
			echo $e->getMessage();
			die;
		}
		return $clusterList;
	}
	
	public function occupation($cluster,$monthStart,$monthEnd,$year,$period,$mode){
	
		$startDate= $this->createStartDate($monthStart,$year);
		$endDate= $this->createEndDate($monthStart,$monthEnd,$year);
		$period=(in_array($period, array('day','week','month')))?$period:'month';
	
		$sqlOccupation=
			"WITH jobs_cluster AS(\n".
			"	SELECT id, running_datetime, end_datetime, nb_cpus, state FROM jobs\n".
			"	WHERE running_datetime>'1970-01-01 01:00:00' AND clustername= :cluster\n".
			"	AND ((end_datetime BETWEEN :start AND :end ) OR (running_datetime BETWEEN :start AND :end))\n".
			"), steps AS(\n".
			"	SELECT step, ((EXTRACT (EPOCH FROM step+ '1 $period'::interval) - EXTRACT(EPOCH FROM step)))*sum(cpu) AS cpu\n". 
			"	FROM generate_series( :start ,LEAST( :end ,now()), '1 $period') step,nodes\n".
			"	WHERE cluster= :cluster GROUP BY step\n".
			")\n".
			"SELECT step, count(id), (EXTRACT(epoch FROM sum(CASE WHEN end_datetime!= '1970-01-01 01:00:00'\n".
			"	THEN (LEAST(step + '1 $period'::interval, end_datetime) - GREATEST(step,running_datetime))*nb_cpus/cpu\n".
			"	ELSE (step + '1 $period'::interval - GREATEST(step,running_datetime))*nb_cpus/cpu\n".
			"END))) AS occupation FROM steps\n".
			"LEFT JOIN jobs_cluster ON CASE WHEN end_datetime!= '1970-01-01 01:00:00'\n".
			"	THEN (step BETWEEN running_datetime AND end_datetime) OR (running_datetime BETWEEN step and (step + '1 $period'::interval))\n".
			"	ELSE step >= running_datetime AND state='RUNNING'\n".
			"END GROUP BY step ORDER BY step";
		
		$statementOccupation=$this->prepareQueryWithVariables($sqlOccupation,array(':cluster'=>$cluster,':end'=>$endDate,':start'=>$startDate));
		set_time_limit($this->max_time);
		try{
			$statementOccupation->execute();
			$occupation=$statementOccupation->fetchAll(PDO::FETCH_ASSOC);
		}catch(PDOException $e){
			echo $e->getMessage();
			$statementOccupation->debugDumpParams();
			die;
		}
		
		if($mode){
			$sqlEvents=
				"WITH steps AS (\n".
				"	SELECT step, ((EXTRACT (EPOCH FROM step+ '1 $period'::interval) - EXTRACT(EPOCH FROM step)))*sum(cpu) AS cpu\n".
				"	FROM generate_series( :start ,LEAST( :end ,now()), '1 $period') step,nodes\n".
				"	WHERE cluster= 'ivanoe' GROUP BY step\n".
				")\n".
				"SELECT step,(EXTRACT(epoch FROM sum(CASE WHEN t_end!= '1970-01-01 01:00:00'\n".
				"	THEN (LEAST(step + '1 $period'::interval, t_end) - GREATEST(step,t_start))*nb_cpus/steps.cpu\n".
				"	ELSE (step + '1 $period'::interval - GREATEST(step,t_start))*nb_cpus/steps.cpu\n".
				"END))) AS occupation\n".
				"FROM steps LEFT JOIN nodes ON cluster= :cluster \n".
				"LEFT JOIN events ON events.node = nodes.name AND cluster= :cluster AND t_start > '1970-01-01 01:00:00'\n".
				"AND CASE WHEN t_end!= '1970-01-01 01:00:00'\n".
				"	THEN (step BETWEEN t_start AND t_end) OR (t_start BETWEEN step and (step + '1 $period'::interval))\n".
				"	ELSE step >= t_start\n".
				"END GROUP BY step ORDER BY step";
				
			$statementEvents=$this->prepareQueryWithVariables($sqlEvents,array(':cluster'=>$cluster,':end'=>$endDate,':start'=>$startDate));
		
			set_time_limit($this->max_time);
			try{
				$statementEvents->execute();
				$EventsOccupation=$statementEvents->fetchAll(PDO::FETCH_ASSOC);
			}catch(PDOException $e){
				echo $e->getMessage();
				$statementEvents->debugDumpParams();
				die;
			}
			
			for($i=0;$i<count($occupation);$i++) $occupation[$i]['indispo']= $EventsOccupation[$i]['occupation'];
		}
		
		return $occupation;
	}
	
	public function occupationByPartition($cluster,$month,$year){
		$sqlOccupation=
			"WITH jobs_partition AS (\n".
			"	SELECT DISTINCT partition,jobs.id, running_datetime, end_datetime, sum(nodes.cpu) AS cpus_occupy, state FROM job_nodes\n".
			"	JOIN nodes ON job_nodes.node=nodes.name AND cluster= :cluster \n".
			"	JOIN jobs ON job_nodes.job=jobs.id AND running_datetime > '1970-01-01 01:00:00' AND clustername= :cluster\n".
			"	AND ((end_datetime BETWEEN :start_date AND :end_date ) OR (running_datetime BETWEEN :start_date AND :end_date ))\n".
			"	GROUP BY partition,jobs.id,running_datetime,end_datetime,state\n".
			"), steps AS(\n".
			"	SELECT DISTINCT step, partition, sum(cpu)*3600*24 AS cpu_dispo FROM generate_series( :start_date ::timestamp,LEAST( :end_date ,now()), '1 day') step, nodes\n".
			"	WHERE cluster= :cluster GROUP BY step,partition\n".
			")\n".
			"SELECT EXTRACT(day FROM step) AS step,steps.partition, (EXTRACT(epoch FROM sum(CASE WHEN end_datetime!= '1970-01-01 01:00:00'\n".
			"	THEN (LEAST(step + '1 day'::interval, end_datetime) - GREATEST(step,running_datetime))*cpus_occupy/cpu_dispo\n".
			"	ELSE (step + '1 day'::interval - GREATEST(step,running_datetime))*cpus_occupy/cpu_dispo\n".
			"END))) AS occupation FROM steps\n".
			"LEFT JOIN jobs_partition ON steps.partition=jobs_partition.partition AND CASE WHEN end_datetime!= '1970-01-01 01:00:00'\n".
			"	THEN (step BETWEEN running_datetime AND end_datetime) OR (running_datetime BETWEEN step and (step + '1 day'::interval))\n".
			"	ELSE step >= running_datetime AND state='RUNNING' AND running_datetime!='1970-01-01 01:00:00'\n".
			"END GROUP BY step,steps.partition ORDER BY steps.partition,step";
			
		$sqlEvents=
			"WITH steps AS (\n".
			"	SELECT DISTINCT step, partition, sum(cpu)*3600*24 AS cpu FROM generate_series( :start_date ::timestamp,LEAST( :end_date ,now()), '1 day') step, nodes\n".
			"	WHERE cluster= :cluster GROUP BY step,partition\n".
			")\n".
			"SELECT EXTRACT(day FROM step) AS step,steps.partition, (EXTRACT(epoch FROM sum((LEAST(step + '1 day'::interval, t_end) - GREATEST(step,t_start))*nb_cpus/steps.cpu))) AS occupation FROM steps\n".
			"LEFT JOIN nodes ON cluster = :cluster AND steps.partition=nodes.partition \n".
			"LEFT JOIN events ON CASE WHEN t_end!= '1970-01-01 01:00:00'\n".
			"	THEN (step BETWEEN t_start AND t_end) OR (t_start BETWEEN step and (step + '24 hours'::interval))\n".
			"	ELSE step >= t_start\n".
			"END\n".
			"AND t_start > '1970-01-01 01:00:00' AND nodes.name=events.node\n".
			"GROUP BY step,steps.partition ORDER BY steps.partition,step";
		
		$startDate= $this->createStartDate($month,$year);
		$endDate= $this->createEndDate($month,$month,$year);
		
		$statementOccupation=$this->prepareQueryWithVariables($sqlOccupation,array(':cluster'=>$cluster,':end_date'=>$endDate,':start_date'=>$startDate));
		$statementEvents=$this->prepareQueryWithVariables($sqlEvents,array(':cluster'=>$cluster,':end_date'=>$endDate,':start_date'=>$startDate));
		
		set_time_limit($this->max_time);
		try{
			$statementOccupation->execute();
			$occupation=$statementOccupation->fetchAll(PDO::FETCH_ASSOC);
		}catch(PDOException $e){
			echo $e->getMessage();
			$statementOccupation->debugDumpParams();
			die;
		}
		set_time_limit($this->max_time);
		try{
			$statementEvents->execute();
			$EventsOccupation=$statementEvents->fetchAll(PDO::FETCH_ASSOC);
		}catch(PDOException $e){
			echo $e->getMessage();
			$statementEvents->debugDumpParams();
			die;
		}
		
		for($i=0;$i<count($occupation);$i++) $occupation[$i]['indispo']= $EventsOccupation[$i]['occupation'];
		
		return $occupation;
	}
	
	public function disponibility($cluster,$period,$monthStart,$yearStart,$monthEnd,$login){
		$period=(in_array($period, array('hour','day','month')))?$period:'month';
			
		$sql =
			"WITH steps AS(\n".
			"	SELECT step, (step+ '1 $period'::interval) AS step_next, ((EXTRACT (EPOCH FROM step+ '1 $period'::interval) - EXTRACT(EPOCH FROM step))/(3600))*12 AS total\n".
			"	FROM generate_series( :start ,LEAST( :end ,now()), '1 $period') step\n".
			"), jobs AS(\n".
			"	SELECT date_trunc('$period', submission_datetime) AS date_step, count(id)::real AS completed, name FROM jobs\n".
			"	WHERE clustername= :cluster AND (submission_datetime BETWEEN :start AND LEAST( :end ,now()))\n".
			"	AND login= :login AND state='COMPLETE' AND end_datetime>'1970-01-01 01:00:00'\n".
			"	GROUP BY name, date_step\n".
			")\n".
			", scenarios AS(\n".
			"	SELECT DISTINCT name AS scenario FROM jobs WHERE name!=''\n".
			")\n".
			"SELECT step, step_next, completed/total AS dispo, scenario FROM steps\n".
			"JOIN scenarios ON TRUE\n".
			"LEFT JOIN jobs ON date_step>=step AND date_step< step_next AND name=scenario";
		
		$startDate= $this->createStartDate($monthStart,$yearStart);
		$endDate= $this->createEndDate($monthStart,$monthEnd,$yearStart);
		
		$statement=$this->prepareQueryWithVariables($sql,array(':cluster'=>$cluster,':start'=>$startDate,':end'=>$endDate,':period'=>$period, ':login'=>$login));
		try{
			$statement->execute();
			$results=$statement->fetchAll(PDO::FETCH_ASSOC);
		}catch(PDOException $e){
			echo $e->getMessage();
			$statement->debugDumpParams();
			die;
		}
		return $results;
	}
	
	public function detailsJobsDisponibility($cluster,$start,$end,$login){
		$sql="SELECT * FROM jobs WHERE clustername= :cluster AND (submission_datetime BETWEEN :start AND :end ) AND login= :login ";
		$statement=$this->prepareQueryWithVariables($sql,array(':cluster'=>$cluster,':start'=>$start,':end'=>$end, ':login'=>$login));
		try{
			$statement->execute();
			$results=$statement->fetchAll(PDO::FETCH_ASSOC);
		}catch(PDOException $e){
			echo $e->getMessage();
			$statement->debugDumpParams();
			die;
		}
		return $results;
	}
	
	public function topUsers($cluster,$type,$monthStart,$yearStart,$monthEnd){
		switch($type){
			case 'nbjobs':
				$sqlSelect	='COUNT(id) AS nbjobs';
				$sqlOrderBy	= 'nbjobs desc';
			break;
			case 'consommation':
				$sqlSelect	=' EXTRACT(EPOCH FROM SUM((end_datetime-running_datetime)*nb_cpus))/3600 AS consommation, COUNT(id) AS nbjobs';
				$sqlOrderBy	= 'consommation desc';
			break;
			case 'attente':
				$sqlSelect	='EXTRACT(EPOCH FROM AVG(running_datetime-submission_datetime))/3600 AS attente, COUNT(id) AS nbjobs';
				$sqlOrderBy	= 'attente desc';
			break;
		}
		$sql=
			"SELECT date_trunc('month',end_datetime) AS mois, $sqlSelect, users.name FROM jobs, users\n".
			"WHERE (end_datetime BETWEEN :start AND :end ) AND running_datetime > '1970-01-01 01:00:00'\n".
			"AND CASE WHEN users.deletion > '1970-01-01 01:00:00'\n".
			"	THEN jobs.submission_datetime >= users.creation AND jobs.submission_datetime <= users.deletion\n".
			"	ELSE jobs.submission_datetime >= users.creation\n".
			"END\n".
			"AND clustername = :cluster AND users.cluster = :cluster AND jobs.uid=users.uid\n".
			"GROUP BY mois, users.login, users.name ORDER BY mois, $sqlOrderBy";
		
		$start= $this->createStartDate($monthStart,$yearStart);
		$end= $this->createEndDate($monthStart,$monthEnd,$yearStart);
		
		$statement=$this->prepareQueryWithVariables($sql,array(':cluster'=>$cluster,':start'=>$start,':end'=>$end));
		try{
			$statement->execute();
			$results=$statement->fetchAll(PDO::FETCH_ASSOC);
		}catch(PDOException $e){
			echo $e->getMessage();
			$statement->debugDumpParams();
			die;
		}
		return $results;
	}
	
	public function jobsByHours($cluster,$month,$year,$mode){
	
		$start= $this->createStartDate($month,$year);
		$end= $this->createEndDate($month,$month,$year);
		
		$sqlWait=
			"WITH steps AS(\n".
			"	SELECT step FROM generate_series( :start ::timestamp, :end , '1 hour') step\n".
			"), filtered_jobs AS(\n".
			"	SELECT id, nb_cpus,submission_datetime,running_datetime,end_datetime,state FROM jobs\n".
			"	WHERE end_datetime> :start AND submission_datetime< :end \n".
			"	AND running_datetime!='1970-01-01 01:00:00' AND clustername= :cluster".
			")\n".
			"SELECT step, count(id) AS jobs_wait, sum(nb_cpus) AS nb_cpus_wait FROM steps\n".
			"LEFT JOIN filtered_jobs ON (step BETWEEN submission_datetime AND running_datetime)\n";
		$sqlProd=
			"WITH steps AS(\n".
			"	SELECT step FROM generate_series( :start ::timestamp, :end , '1 hour') step\n".
			"), filtered_jobs AS(\n".
			"	SELECT id, nb_cpus,submission_datetime,running_datetime,end_datetime,state FROM jobs\n".
			"	WHERE end_datetime> :start AND submission_datetime< :end \n".
			"	AND running_datetime!='1970-01-01 01:00:00' AND clustername= :cluster".
			")\n".
			"SELECT step, count(id) AS jobs_prod, sum(nb_cpus) AS nb_cpus_prod FROM steps\n".
			"LEFT JOIN filtered_jobs ON CASE WHEN end_datetime!= '1970-01-01 01:00:00'\n".
			"	THEN (step BETWEEN running_datetime AND end_datetime)\n".
			"	ELSE step >= running_datetime AND STATE='RUNNING'\n".
			"END\n";
			
		if($mode=='new'){
			$sqlNewCondition= "AND (submission_datetime BETWEEN :start AND :end ) ";
			$sqlWait.=$sqlNewCondition;
			$sqlProd.=$sqlNewCondition;
		}
		$sqlWait.="GROUP BY step ORDER BY step;";
		$sqlProd.="GROUP BY step ORDER BY step;";
		
		$statementWait=$this->prepareQueryWithVariables($sqlWait,array(':cluster'=>$cluster,':end'=>$end,':start'=>$start));
		$statementProd=$this->prepareQueryWithVariables($sqlProd,array(':cluster'=>$cluster,':end'=>$end,':start'=>$start));
		
		set_time_limit($this->max_time);
		try{
			$statementWait->execute();
			$results=$statementWait->fetchAll(PDO::FETCH_ASSOC);
		}catch(PDOException $e){
			echo $e->getMessage();
			$statementWait->debugDumpParams();
			die;
		}
		set_time_limit($this->max_time);
		try{
			$statementProd->execute();
			$prod=$statementProd->fetchAll(PDO::FETCH_ASSOC);
		}catch(PDOException $e){
			echo $e->getMessage();
			$statementProd->debugDumpParams();
			die;
		}
		
		for($i=0;$i<count($results);$i++){
			$results[$i]['jobs_prod']	= $prod[$i]['jobs_prod'];
			$results[$i]['nb_cpus_prod']= $prod[$i]['nb_cpus_prod'];
		}
		
		return $results;
	}
	
	public function consumptionByDepartment($cluster,$monthStart,$monthEnd,$year){
		$start	= $this->createStartDate($monthStart,$year);
		$end	= $this->createEndDate($monthStart,$monthEnd,$year);
		
		$sql=
			"SELECT department,date_trunc('month',end_datetime) AS month, extract(EPOCH FROM sum((end_datetime-running_datetime)*nb_cpus/3600)) AS duree FROM jobs, users\n".
			"WHERE (end_datetime between :start and :end )\n".
			"AND running_datetime > '1970-01-01 01:00:00' and clustername = :cluster and users.cluster= :cluster and jobs.uid=users.uid\n".
			"AND CASE WHEN users.deletion > '1970-01-01 01:00:00'\n".
			"	THEN jobs.submission_datetime >= users.creation AND jobs.submission_datetime <= users.deletion\n".
			"	ELSE jobs.submission_datetime >= users.creation\n".
			"END group by month, department order by department, month ";
		set_time_limit($this->max_time);
		$statement=$this->prepareQueryWithVariables($sql,array(':cluster'=>$cluster,':end'=>$end,':start'=>$start));		
		
		try{
			$statement->execute();
			$results=$statement->fetchAll(PDO::FETCH_ASSOC);
		}catch(PDOException $e){
			echo $e->getMessage();
			$statement->debugDumpParams();
			die;
		}
		return $results;
	}
	
	public function waitingByQueue($cluster,$monthStart,$monthEnd,$year,$mode){
	
		$modesAuthorized=array('avg','sum');
		$mode=in_array($mode,$modesAuthorized)?$mode:'avg';
		
		$start	= $this->createStartDate($monthStart,$year);
		$end	= $this->createEndDate($monthStart,$monthEnd,$year);
		
		$sql=
			"SELECT count(id), running_queue AS queue, date_trunc('month',submission_datetime) AS month, extract(EPOCH FROM $mode(running_datetime-submission_datetime)/3600) AS duree FROM jobs\n".
			"WHERE (submission_datetime between :start and :end ) and clustername = :cluster and running_datetime > '1970-01-01 01:00:00'\n".
			"GROUP BY month, queue ORDER BY month, queue";
		set_time_limit($this->max_time);
		$statement=$this->prepareQueryWithVariables($sql,array(':cluster'=>$cluster,':end'=>$end,':start'=>$start));		
		
		try{
			$statement->execute();
			$results=$statement->fetchAll(PDO::FETCH_ASSOC);
		}catch(PDOException $e){
			echo $e->getMessage();
			$statement->debugDumpParams();
			die;
		}
		return $results;
	}
	
	public function topNodes($cluster,$monthStart,$monthEnd,$year){
		
		$start	= $this->createStartDate($monthStart,$year);
		$end	= $this->createEndDate($monthStart,$monthEnd,$year);
		
		$sql=
			"SELECT date_trunc('month', end_datetime) AS month, EXTRACT(epoch FROM sum((end_datetime-running_datetime)*cpu/3600)) AS heuresCPU, node FROM jobs\n".
			"JOIN job_nodes ON jobs.id=job_nodes.job\n".
			"JOIN nodes ON job_nodes.node=nodes.name AND cluster= :cluster \n".
			"AND (end_datetime BETWEEN :start AND :end )\n".
			"AND running_datetime > :start AND clustername = :cluster \n".
			"GROUP BY month, node ORDER BY month,heuresCPU desc";
		set_time_limit($this->max_time);
		$statement=$this->prepareQueryWithVariables($sql,array(':cluster'=>$cluster,':end'=>$end,':start'=>$start));		
		
		try{
			$statement->execute();
			$results=$statement->fetchAll(PDO::FETCH_ASSOC);
		}catch(PDOException $e){
			echo $e->getMessage();
			$statement->debugDumpParams();
			die;
		}
		return $results;
	}
	
	public function jobsByFail($cluster,$monthStart,$monthEnd,$year){
		
		$start	= $this->createStartDate($monthStart,$year);
		$end	= $this->createEndDate($monthStart,$monthEnd,$year);
		
		$sql=
			"SELECT count(id), jobs.state, date_trunc('month',submission_datetime) AS month, count(id) FROM jobs\n".
			"WHERE (submission_datetime BETWEEN :start AND :end ) AND running_datetime > '1970-01-01 01:00:00'\n".
			"AND clustername = :cluster AND jobs.state<>'COMPLETE'\n".
			"GROUP BY month, jobs.state ORDER BY month, jobs.state";
		set_time_limit($this->max_time);
		$statement=$this->prepareQueryWithVariables($sql,array(':cluster'=>$cluster,':end'=>$end,':start'=>$start));		
		
		try{
			$statement->execute();
			$results=$statement->fetchAll(PDO::FETCH_ASSOC);
		}catch(PDOException $e){
			echo $e->getMessage();
			$statement->debugDumpParams();
			die;
		}
		return $results;
	}
	
	public function consumptionByQueue($cluster,$monthStart,$monthEnd,$year){
		$start	= $this->createStartDate($monthStart,$year);
		$end	= $this->createEndDate($monthStart,$monthEnd,$year);
		
		$sql=
			"SELECT count(id), running_queue AS queue, date_trunc('month',end_datetime) AS month, extract(EPOCH FROM sum(end_datetime-running_datetime)/3600) AS duree FROM jobs\n".
			"WHERE (end_datetime between :start and :end ) and clustername = :cluster and running_datetime > '1970-01-01 01:00:00'\n".
			"GROUP BY month, queue ORDER BY month, queue";
		set_time_limit($this->max_time);
		$statement=$this->prepareQueryWithVariables($sql,array(':cluster'=>$cluster,':end'=>$end,':start'=>$start));		
		
		try{
			$statement->execute();
			$results=$statement->fetchAll(PDO::FETCH_ASSOC);
		}catch(PDOException $e){
			echo $e->getMessage();
			$statement->debugDumpParams();
			die;
		}
		return $results;
	}
	
	public function topJobsByCpu($cluster,$monthStart,$monthEnd,$year){
		$start	= $this->createStartDate($monthStart,$year);
		$end	= $this->createEndDate($monthStart,$monthEnd,$year);

		$sql=
			"SELECT date_trunc('month',end_datetime) AS month, extract(EPOCH FROM avg(running_datetime-submission_datetime)/3600) AS hours,count(id), nb_cpus FROM jobs\n".
			"WHERE (end_datetime BETWEEN :start and :end ) and running_datetime > '1970-01-01 01:00:00'\n".
			"AND clustername = :cluster GROUP BY month, nb_cpus ORDER BY month, hours desc";
		set_time_limit($this->max_time);
		$statement=$this->prepareQueryWithVariables($sql,array(':cluster'=>$cluster,':end'=>$end,':start'=>$start));		
		
		try{
			$statement->execute();
			$results=$statement->fetchAll(PDO::FETCH_ASSOC);
		}catch(PDOException $e){
			echo $e->getMessage();
			$statement->debugDumpParams();
			die;
		}
		return $results;
	}
	
	public function occupationFileSystem($cluster,$month,$year){
		$startDate= $this->createStartDate($month,$year);
		$endDate= $this->createEndDate($month,$month,$year);
	
		$sql=
			"SELECT date_trunc('day',timestamp) AS Jour, mount_point, usage FROM filesystem_usage\n".
			"JOIN filesystem ON fs_id=id WHERE cluster= :cluster AND (timestamp BETWEEN :start AND :end )\n".
			"ORDER BY mount_point,timestamp";
		
		$statement=$this->prepareQueryWithVariables($sql,array(':cluster'=>$cluster,':end'=>$endDate,':start'=>$startDate));
		
		set_time_limit($this->max_time);
		try{
			$statement->execute();
			$occupation=$statement->fetchAll(PDO::FETCH_ASSOC);
		}catch(PDOException $e){
			echo $e->getMessage();
			$statement->debugDumpParams();
			die;
		}
		
		return $occupation;
	}
	
	public function disponibilityNodes($cluster,$monthStart,$monthEnd,$year,$period){
		$start	= $this->createStartDate($monthStart,$year);
		$end	= $this->createEndDate($monthStart,$monthEnd,$year);
		$period	=(in_array($period, array('day','week','month')))?$period:'month';
		$events	=array();
		
		$sql=
			"WITH steps AS (\n".
			"	SELECT step, ((EXTRACT (EPOCH FROM step+ '1 $period'::interval) - EXTRACT(EPOCH FROM step)))*sum(cpu) AS cpu\n".
			"	FROM generate_series( :start ,LEAST( :end ,now()), '1 $period') step,nodes\n".
			"	WHERE cluster= 'ivanoe' GROUP BY step\n".
			")\n".
			"SELECT step, 1-(EXTRACT(epoch FROM sum(CASE WHEN t_end!= '1970-01-01 01:00:00'\n".
			"	THEN (LEAST(step + '1 $period'::interval, t_end) - GREATEST(step,t_start))*nb_cpus/steps.cpu\n".
			"	ELSE (step + '1 $period'::interval - GREATEST(step,t_start))*nb_cpus/steps.cpu\n".
			"END))) AS dispo\n".
			"FROM steps LEFT JOIN nodes ON cluster= :cluster \n".
			"LEFT JOIN events ON events.node = nodes.name AND cluster= :cluster AND t_start > '1970-01-01 01:00:00'\n".
			"AND CASE WHEN t_end!= '1970-01-01 01:00:00'\n".
			"	THEN (step BETWEEN t_start AND t_end) OR (t_start BETWEEN step and (step + '1 $period'::interval))\n".
			"	ELSE step >= t_start\n".
			"END GROUP BY step ORDER BY step";
		$statement=$this->prepareQueryWithVariables($sql,array(':cluster'=>$cluster,':end'=>$end,':start'=>$start));
	
		set_time_limit($this->max_time);
		try{
			$statement->execute();
			$events=$statement->fetchAll(PDO::FETCH_ASSOC);
		}catch(PDOException $e){
			echo $e->getMessage();
			$statement->debugDumpParams();
			die;
		}
		
		return $events;
	}
}
?>