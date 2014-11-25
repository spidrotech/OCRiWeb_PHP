<?php
	require_once("OcriControllerChart.php");
	class IndTopConsoController extends OcriControllerChart{
	
		protected $rootName;
		protected function setData(&$parameters){
			$resultsByMonth=array();
			$parameters['results']= $this->adaptator->topUsers(
			$parameters['cluster'],$parameters['type'],
			$parameters['monthStart'],$parameters['yearStart'],
			$parameters['monthEnd']);
			$parameters['yearEnd']=$this->adaptator->endYear($parameters['monthStart'],$parameters['monthEnd'],$parameters['yearStart']);
			$lastMonth = $parameters['results'][0]['mois'];
			$limitVariable = $parameters['limit'];
			foreach($parameters['results'] as $i => $row){
				if($lastMonth!=$row['mois']){
					$limitVariable=$i+$parameters['limit'];
					$lastMonth = $row['mois'];
				}
				if ($i < $limitVariable){
					$resultsByMonth[$row['mois']][]=$row;
				}
			}
			
			switch($parameters['type']){
				case 'nbjobs':
					$parameters['typeCaption']	='le nombres de jobs terminés';
				break;
				case 'consommation':
					$parameters['typeCaption']	='les heures CPU consommées';
					$parameters['typeColumn']	= 'Heures CPU';
				break;
				case 'attente':
					$parameters['typeCaption']	='la moyenne du temps d\'attente';
					$parameters['typeColumn']	= 'Attente(h)';
				break;
			}
			$parameters['results']=$resultsByMonth;
			$this->rootName.= 'indTopConso_'.$parameters['type'].'_'.$parameters['cluster']."_".$parameters['yearStart'].'-'.$parameters['monthStart'].'_'.$parameters['yearEnd'].'-'.$parameters['monthEnd'];
		}
		
		
		public function getResult($parameters){
			$this->setData($parameters);
			if(count($parameters['results'])>0){
				$display=$this->getView('IndTopConsoTable', $parameters);
			}else $display = "Veuillez vérifier les paramètres, la requête ne retourne aucun résultat";
			
			return $display;
		}
		
		private function createCsv($parameters){
			$csvFiles= array();
			foreach($parameters['results'] as $month => $monthResults){
				$filename=$this->rootName.'_'.date('m', strtotime($month)).'.csv';
				$handle = fopen($filename, "w");
				//columns title
				$titles = array("Utilisateurs", "# Jobs");
				if($parameters['type']!='nbjobs') $titles[]=$parameters['typeColumn'];
				fputcsv($handle,$titles,';');
				//data
				foreach($monthResults as $row){
					$data= array($row['name'],$row['nbjobs']);
					if($parameters['type']!='nbjobs') $data[]=number_format($row[$parameters['type']],2,',',' ');
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