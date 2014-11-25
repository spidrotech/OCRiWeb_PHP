<?php
	require_once("OcriControllerChart.php");
	class IndJobsByFailController extends OcriControllerChart{
	
		protected $rootName;
		
		public function getResult($parameters){
			$this->setData($parameters);
			if(count($parameters['results'])>0){
				$parameters['chartPath']=$this->createChart($parameters);
				$display=$this->getView('IndJobsByFailTable', $parameters);
			}else $display = "Veuillez vérifier les paramètres, la requête ne retourne aucun résultat";
			
			return $display;
		}
		
		private function setData(&$parameters){
			$flatResults=$this->adaptator->jobsByFail($parameters['cluster'],$parameters['monthStart'],$parameters['monthEnd'],$parameters['year']);
			$resultsByMonth=array();
			if(count($flatResults)>0){
				foreach($flatResults as $row){
					$resultsByMonth['listState'][$row['state']][$row['month']]=$row['count'];
					$resultsByMonth['listMonth'][$row['month']]=(isset($resultsByMonth['listMonth'][$row['month']]))?$resultsByMonth['listMonth'][$row['month']]+$row['count']:$row['count'];
				}
				
				//second loop for purifying not set value, and create % value
				foreach($resultsByMonth['listState'] as $state => $monthValues){
					foreach($resultsByMonth['listMonth'] as $month=>$total){
						if(!isset($monthValues[$month])){
							$resultsByMonth['listState'][$state][$month]=0;
						}
					}
				}
			}
			
			$parameters['results'] =$resultsByMonth;
			$parameters['yearEnd'] =$this->adaptator->endYear($parameters['monthStart'],$parameters['monthEnd'],$parameters['year']);
			$this->rootName.= 'IndJobsByFail'.'_'.$parameters['cluster']."_".$parameters['year'].'-'.$parameters['monthStart'].'_'.$parameters['yearEnd'].'-'.$parameters['monthEnd'];
		}
		
		private function createChart($parameters){
			/* Create and populate the pData object */
			$myData = new pData();
			$listMonth= array();
			
			foreach($parameters['results']['listMonth'] as $month=>$total) $listMonth[]=date("m-y", strtotime($month));
			//axe Y;
			$myData->addPoints($listMonth,"Mois");
			$myData->setSerieDescription("Mois","Mois");
			$myData->setAbscissa("Mois");
			
			//axe X
			$myData->setAxisName(0,"% Consommation");
			$AxisBoundaries = array(0=>array("Min"=>0,"Max"=>100));
			$ScaleSettings  = array("Mode"=>SCALE_MODE_START0,"ManualScale"=>$AxisBoundaries,"GridR"=>180,"GridG"=>180,"GridB"=>180);
			
			foreach($parameters['results']['listState'] as $state => $hoursCpuByMonth){
				$onlyValues=array();
				foreach($hoursCpuByMonth as $month => $value){
					$onlyValues[]=$value;
				}
				$myData->addPoints($onlyValues,$state);
				$myData->setSerieDescription($state,$state);
			}

			$chartPath=$this->rootName.'.png';
			
			/* Create the pChart object */
			$myPicture = new pImage(1100,600,$myData);

			/* Write the picture title */ 
			$myPicture->setFontProperties(array("FontName"=>"librairies/pChart/fonts/Forgotte.ttf","FontSize"=>$this->fontSizeScale));
			$myPicture->drawText(60,35,"
				Répartition des jobs en échec par cause ".$parameters['cluster']." du ".$parameters['monthStart']."-".$parameters['year'].' au '.$parameters['monthEnd']."-".$parameters['yearEnd'] ,
				array("FontSize"=>20,"Align"=>TEXT_ALIGN_BOTTOMLEFT)
			);

			/* Do some cosmetic and draw the chart */
			$myPicture->setGraphArea(60,40,1070,550);
			//$myPicture->drawFilledRectangle(60,40,770,570,array("R"=>255,"G"=>255,"B"=>255,"Surrounding"=>-200,"Alpha"=>10));
			$myPicture->drawScale($ScaleSettings);
			$myPicture->drawBarChart();
			$myPicture->setFontProperties(array("FontName"=>"librairies/pChart/fonts/Forgotte.ttf","FontSize"=>$this->fontSizeLegend));
			$myPicture->setShadow(FALSE);
			/* Write the chart legend */ 
			$myPicture->drawLegend(300,580,array("Style"=>LEGEND_ROUND,"Mode"=>LEGEND_HORIZONTAL));
			$myPicture->Render($chartPath);
			return $chartPath;
		}
		
		private function createCsv($parameters){
			$results=$parameters['results'];
			$handle = fopen($this->rootName.'.csv', "w");
			$titles=array("Mois");
			foreach($results['listState'] as  $state => $value){
				$titles[]=$state;
			}
			//columns title
			fputcsv($handle,$titles,';');
			//data
			foreach($results['listMonth'] as $month=>$total){
				$data=array(date($this->csvDateFormat, strtotime($month)));
				foreach($results['listState'] as $state=>$resultsByMonth){
					$data[] = number_format($resultsByMonth[$month],0,',',' '); 
				}
				fputcsv($handle,$data,';');
			}
			fclose($handle);
		}
		
		public function exportToZip($parameters){
			$this->setData($parameters);
			$this->createCsv($parameters);
			$this->createChart($parameters);
			$zipPath = $this->rootName.'.zip'; // chemin système (local) vers le fichier
			//the function take for principle that the chart and the result are already displayed
			$this->createZip($zipPath,array($this->rootName.'.csv',$this->rootName.'.png'));
			$this->headerForZip($zipPath);
			exit; // nécessaire pour être certain de ne pas envoyer de fichier corrompu
		}
	}
?>