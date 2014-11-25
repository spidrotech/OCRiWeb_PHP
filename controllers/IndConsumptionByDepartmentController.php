<?php
	require_once("OcriControllerChart.php");
	class IndConsumptionByDepartmentController extends OcriControllerChart{
	
		protected $rootName;
		
		public function getResult($parameters){
			$this->setData($parameters);
			if(count($parameters['results'])>0){
				$parameters['chartPath']=$this->createChart($parameters);
				$display=$this->getView('IndConsumptionByDepartmentTable', $parameters);
			}else $display = "Veuillez vérifier les paramètres, la requête ne retourne aucun résultat";
			
			return $display;
		}
		
		private function setData(&$parameters){
			$flatResults=$this->adaptator->consumptionByDepartment($parameters['cluster'],$parameters['monthStart'],$parameters['monthEnd'],$parameters['year']);
			$resultsByMonth=array();
			if(count($flatResults)>0){
				foreach($flatResults as $row){
					$resultsByMonth['listDepartment'][$row['department']][$row['month']]['hours']=$row['duree'];
					$resultsByMonth['listMonth'][$row['month']]=(isset($resultsByMonth['listMonth'][$row['month']]))?$resultsByMonth['listMonth'][$row['month']]+$row['duree']:$row['duree'];
				}
				
				//second loop for purifying not set value, and create % value, and create the value for top
				foreach($resultsByMonth['listDepartment'] as $department => $monthValues){
					foreach($resultsByMonth['listMonth'] as $month=>$total){
						if(isset($monthValues[$month]['hours'])){
							$resultsByMonth['listDepartment'][$department][$month]['%']=$monthValues[$month]['hours']*100/$total;
						}else{
							$resultsByMonth['listDepartment'][$department][$month]['hours']=0;
							$resultsByMonth['listDepartment'][$department][$month]['%']=0;
						}
						$topValues[$month][$department]=$resultsByMonth['listDepartment'][$department][$month]['%'];
					}
				}
				
				//third loop to sort the top value, select only the top five values for each month;
				foreach($topValues as $month => $valuesByDepartement){
					arsort($valuesByDepartement,SORT_NUMERIC);
					$sortedValues[$month]=array();
					$i=0;
					$resultsByMonth['Autres'][$month]=0;
					foreach($valuesByDepartement as $department => $value){
						if($i<5){
							$resultsByMonth['top'][$department][$month]	=$value;
							$topList[]= $department;
						}else{
							$resultsByMonth['Autres'][$month]			+=$value;
							$resultsByMonth['top'][$department][$month] =0;
						}	
						$i++;
					}
				}
				
				//last loop to delete the department which values are never in the top
				foreach($resultsByMonth['top'] as $department => $valuesByMonth){
					if(!in_array($department,$topList)) unset($resultsByMonth['top'][$department]);
				}
			}
			$parameters['results'] =$resultsByMonth;
			$parameters['yearEnd'] =$this->adaptator->endYear($parameters['monthStart'],$parameters['monthEnd'],$parameters['year']);
			$this->rootName.= 'IndConsumptionByDepartment'.'_'.$parameters['cluster']."_".$parameters['year'].'-'.$parameters['monthStart'].'_'.$parameters['yearEnd'].'-'.$parameters['monthEnd'];
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
			$ScaleSettings  = array("Mode"=>SCALE_MODE_MANUAL,"ManualScale"=>$AxisBoundaries,"GridR"=>180,"GridG"=>180,"GridB"=>180);
			
			foreach($parameters['results']['top'] as $department => $valuesByMonth){
				$onlyValues=array();
				foreach($valuesByMonth as $month => $value){
					$onlyValues[]=$value;
				}
				$myData->addPoints($onlyValues,$department);
				$myData->setSerieDescription($department,$department);
			}
			
			//Add others data
			$myData->addPoints(array_values($parameters['results']['Autres']),'Autres');
			$myData->setSerieDescription('Autres','Autres');
			
			//used for round the value correctly
			$myData->normalize(100,"%"); 

			$chartPath=$this->rootName.'.png';
			
			/* Create the pChart object */
			$myPicture = new pImage(1100,600,$myData);

			/* Write the picture title */ 
			$myPicture->setFontProperties(array("FontName"=>"librairies/pChart/fonts/Forgotte.ttf","FontSize"=>$this->fontSizeScale));
			$myPicture->drawText(60,35,"
				Consommation CPU par département ".$parameters['cluster']." du ".$parameters['monthStart']."-".$parameters['year'].' au '.$parameters['monthEnd']."-".$parameters['yearEnd'] ,
				array("FontSize"=>20,"Align"=>TEXT_ALIGN_BOTTOMLEFT)
			);

			/* Do some cosmetic and draw the chart */
			$myPicture->setGraphArea(60,40,1070,545);
			//$myPicture->drawFilledRectangle(60,40,770,570,array("R"=>255,"G"=>255,"B"=>255,"Surrounding"=>-200,"Alpha"=>10));
			$myPicture->drawScale($ScaleSettings);
			$myPicture->drawStackedBarChart();
			$myPicture->setFontProperties(array("FontName"=>"librairies/pChart/fonts/Forgotte.ttf","FontSize"=>14));
			$myPicture->setShadow(FALSE);
			/* Write the chart legend */ 
			$myPicture->drawLegend(5,575,array("Style"=>LEGEND_ROUND,"Mode"=>LEGEND_HORIZONTAL));
			$myPicture->Render($chartPath);
			return $chartPath;
		}
		
		private function createCsv($parameters){
			$results=$parameters['results'];
			$handle = fopen($this->rootName.'.csv', "w");
			$titles=array("Mois");
			foreach($results['listDepartment'] as  $department => $taux){
				$titles[]=$department.": Heures CPU";
				$titles[]=$department.": Occupation";
			}
			//columns title
			fputcsv($handle,$titles,';');
			//data
			foreach($results['listMonth'] as $month=>$total){
				$data=array(date($this->csvDateFormat, strtotime($month)));
				foreach($results['listDepartment'] as $department=>$resultsByMonth){
					$data[] = number_format($resultsByMonth[$month]['hours'],2,',',' '); 
					$data[] = number_format($resultsByMonth[$month]['%'],2,',',' ');
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