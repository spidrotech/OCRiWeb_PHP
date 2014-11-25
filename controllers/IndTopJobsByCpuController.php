<?php
	require_once("OcriControllerChart.php");
	class IndTopJobsByCpuController extends OcriControllerChart{
	
		protected $rootName;
		protected function setData(&$parameters){
			$resultsByMonth=array();
			$parameters['results']= $this->adaptator->topJobsByCpu($parameters['cluster'],$parameters['monthStart'],$parameters['monthEnd'],$parameters['yearStart']);
			$parameters['yearEnd']=$this->adaptator->endYear($parameters['monthStart'],$parameters['monthEnd'],$parameters['yearStart']);
			$lastMonth = $parameters['results'][0]['month'];
			$limitVariable = $parameters['limit'];
			foreach($parameters['results'] as $i => $row){
				if($lastMonth!=$row['month']){
					$limitVariable=$i+$parameters['limit'];
					$lastMonth = $row['month'];
				}
				if ($i < $limitVariable){
					$resultsByMonth[$row['month']][]=$row;
				}
			}
			
			$parameters['results']=$resultsByMonth;
			$this->rootName.= 'indTopJobsByCpu_'.$parameters['cluster']."_".$parameters['yearStart'].'-'.$parameters['monthStart'].'_'.$parameters['yearEnd'].'-'.$parameters['monthEnd'];
		}
		
		public function getResult($parameters){
			$this->setData($parameters);
			if(count($parameters['results'])>0){
				$display=$this->getView('IndTopJobsByCpuTable', $parameters);
			}else $display = "Veuillez vérifier les paramètres, la requête ne retourne aucun résultat";
			
			return $display;
		}
		
		private function createCsv($parameters){
			$csvFiles= array();
			foreach($parameters['results'] as $month => $monthResults){
				$filename=$this->rootName.'_'.date('m', strtotime($month)).'.csv';
				$handle = fopen($filename, "w");
				//columns title
				$titles = array("# CPU", "Heures d'attente moyenne","# Jobs");
				fputcsv($handle,$titles,';');
				//data
				foreach($monthResults as $row){
					$data= array($row['nb_cpus'],number_format($row['hours'],2,',',' '),$row['count']);
					fputcsv($handle,$data,';');
				}
				fclose($handle);
				$csvFiles[]=$filename;
			}
			return $csvFiles;
		}
		
		public function exportToZip($parameters){
			$this->setData($parameters);
			$csvFiles=$this->createCsv($parameters);
			$zipPath = $this->rootName.'.zip'; // chemin système (local) vers le fichier
			//the function take for principle that the chart and the result are already displayed
			$this->createZip($zipPath,$csvFiles);
			$this->headerForZip($zipPath);
			exit; // nécessaire pour être certain de ne pas envoyer de fichier corrompu
		}
	}
?>