<?php
	require_once("OcriControllerChart.php");
	class IndConsumptionByQueueController extends OcriControllerChart{
	
		protected $rootName;
		
		public function getResult($parameters){
			$this->setData($parameters);
			if(count($parameters['results'])>0){
				$parameters['chartPath']=$this->createChart($parameters);
				$display=$this->getView('IndConsumptionByQueueTable', $parameters);
			}else $display = "Veuillez vérifier les paramètres, la requête ne retourne aucun résultat";
			
			return $display;
		}
		
		private function setData(&$parameters){
			$flatResults=$this->adaptator->consumptionByQueue($parameters['cluster'],$parameters['monthStart'],$parameters['monthEnd'],$parameters['year']);
			$top=array();
			$resultsByMonth=array();
				if(count($flatResults)>0){
				foreach($flatResults as $row){
					$resultsByMonth['listQueue'][$row['queue']][$row['month']]['hours']=$row['duree'];
					$resultsByMonth['listQueue'][$row['queue']][$row['month']]['count']=$row['count'];
					$resultsByMonth['listMonth'][$row['month']]=1;
					
					if(isset($top[$row['queue']])) $top[$row['queue']]+=$row['duree'];
					else $top[$row['queue']]=$row['duree'];
				}
				
				//second loop for purifying not set value
				foreach($resultsByMonth['listQueue'] as $queue => $monthValues){
					foreach($resultsByMonth['listMonth'] as $month=>$ok){
						if(!isset($monthValues[$month]['count'])){
							$resultsByMonth['listQueue'][$queue][$month]['count']=0;
							$resultsByMonth['listQueue'][$queue][$month]['hours']=0;
						}
					}
				}
				
				//classify 5 top waiting
				arsort($top,SORT_NUMERIC);
				array_splice($top,5); 
				$resultsByMonth['top']=$top;
			}
			$parameters['results'] =$resultsByMonth;
			$parameters['yearEnd'] =$this->adaptator->endYear($parameters['monthStart'],$parameters['monthEnd'],$parameters['year']);
			$this->rootName.= 'IndConsumptionByQueue'.'_'.$parameters['cluster']."_".$parameters['year'].'-'.$parameters['monthStart'].'_'.$parameters['yearEnd'].'-'.$parameters['monthEnd'];
		}
		
		private function createChart($parameters){
			/* Create and populate the pData object */
			$timeData = new pData();
			$countData = new pData();
			
			foreach($parameters['results']['top'] as $queue => $totalByQueue){
				
				$onlyValuesCount=array();
				$onlyValuesTime=array();
				
				foreach($parameters['results']['listQueue'][$queue] as $month => $values){
						$onlyValuesCount[]=$values['count'];
						$onlyValuesTime[]=$values['hours'];
					}
				
				$timeData->addPoints($onlyValuesTime,$queue);
				$timeData->setSerieDescription($queue,$queue);
				
				$countData->addPoints($onlyValuesCount,$queue);
				$countData->setSerieDescription($queue,$queue);
			}
			foreach(array_keys($parameters['results']['listMonth']) as $month){
				$listMonth[]=date("m-y", strtotime($month));
			}
			
			//axe Y;
			$timeData->addPoints($listMonth,"Mois");
			$timeData->setSerieDescription("Mois","Mois");
			$timeData->setAbscissa("Mois");
			
			$countData->addPoints($listMonth,"Mois");
			$countData->setSerieDescription("Mois","Mois");
			$countData->setAbscissa("Mois");
			
			//axe X
			$timeData->setAxisName(0,"Durée (h)");
			$countData->setAxisName(0,"# Jobs");

			$chartPathTime	=$this->rootName.'_time.png';
			$chartPathCount	=$this->rootName.'_count.png';
			
			$stringParameters=$parameters['cluster']." du ".$parameters['monthStart']."-".$parameters['year'].' au '.$parameters['monthEnd']."-".$parameters['yearEnd'];
			$titleTime	='Heures CPU mensuelles par queue sur '.$stringParameters;
			$titleCount	='Nombre mensuel de jobs par queue sur '.$stringParameters;

			$chartPath[]=$this->drawChart($titleTime,$chartPathTime,$timeData);
			$chartPath[]=$this->drawChart($titleCount,$chartPathCount,$countData);
			
			return $chartPath;
		}
		
		private function createCsv($parameters){
			$results=$parameters['results'];
			$handle = fopen($this->rootName.'.csv', "w");
			$titles=array("Mois");
			foreach($results['listQueue'] as  $queue => $taux){
				$titles[]=$queue.": Heures CPU";
				$titles[]=$queue.": # Jobs";
			}
			//columns title
			fputcsv($handle,$titles,';');
			//data
			foreach($results['listMonth'] as $month=>$ok){
				$data=array(date($this->csvDateFormat, strtotime($month)));
				foreach($results['listQueue'] as $queue=>$resultsByMonth){
					$data[] = number_format($resultsByMonth[$month]['hours'],2,',',' '); 
					$data[] = number_format($resultsByMonth[$month]['count'],2,',',' ');
				}
				fputcsv($handle,$data,';');
			}
			fclose($handle);
		}
		
		private function drawChart($title,$chartPath,$data){
			$ScaleSettings  = array("Mode"=>SCALE_MODE_START0,"GridR"=>180,"GridG"=>180,"GridB"=>180);
			
			/* Create the pChart object */
			$myPicture = new pImage(1100,600,$data);
			
			/* Write the picture title */ 
			$myPicture->setFontProperties(array("FontName"=>"librairies/pChart/fonts/Forgotte.ttf","FontSize"=>$this->fontSizeScale));
			$myPicture->drawText(60,35, $title,	array("FontSize"=>20,"Align"=>TEXT_ALIGN_BOTTOMLEFT));
			
			/* Do some cosmetic and draw the chart */
			$myPicture->setGraphArea(60,40,1070,550);
			$myPicture->drawScale($ScaleSettings);
			$myPicture->drawBarChart();
			$myPicture->setFontProperties(array("FontName"=>"librairies/pChart/fonts/Forgotte.ttf","FontSize"=>14));
			$myPicture->setShadow(FALSE);
			/* Write the chart legend */ 
			$myPicture->drawLegend(10,580,array("Style"=>LEGEND_ROUND,"Mode"=>LEGEND_HORIZONTAL));
			$myPicture->Render($chartPath);
			return $chartPath;
		}
		
		public function exportToZip($parameters){
			$this->setData($parameters);
			$this->createCsv($parameters);
			$fileList = $this->createChart($parameters);
			$zipPath = $this->rootName.'.zip'; // chemin système (local) vers le fichier
			//the function take for principle that the chart and the result are already displayed
			$fileList[]=$this->rootName.'.csv';
			$this->createZip($zipPath,$fileList);
			$this->headerForZip($zipPath);
			exit; // nécessaire pour être certain de ne pas envoyer de fichier corrompu
		}
	}
?>