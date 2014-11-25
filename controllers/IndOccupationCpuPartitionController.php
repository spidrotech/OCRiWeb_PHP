<?php
	require_once("OcriControllerChart.php");
	class IndOccupationCpuPartitionController extends OcriControllerChart{
	
		protected $rootName;
		private function flatResultToByElement($results){
			$finalResults=array();
			foreach($results as $row){
				$finalResults[$row['step']][$row['partition']]['occupation']=$row['occupation'];
				$finalResults[$row['step']][$row['partition']]['indispo']=$row['indispo'];
			}
			return $finalResults;
		}
		
		public function getResult($parameters){
			$this->setData($parameters);
			if(count($parameters['results'])>0){
				$parameters['chartPath']=$this->createChart($parameters);
				$display=$this->getView('IndOccupationCpuPartitionTable', $parameters);
			}else $display = "Veuillez vérifier les paramètres, la requête ne retourne aucun résultat";
			
			return $display;
		}
		
		private function setData(&$parameters){
			$parameters['results'] = $this->adaptator->occupationByPartition($parameters['cluster'],$parameters['month'],$parameters['year']);
			$this->rootName.= 'indOccupationCpuPartition_'.$parameters['cluster']."_".$parameters['year']."-".$parameters['month'];
			$parameters['results']=$this->flatResultToByElement($parameters['results']);
		}
		
		private function createChart($parameters){
		
			/* Create and populate the pData object */
			$theoryByPartitionData	= new pData();
			$realByPartitionData	= new pData();
			foreach($parameters['results'] as $step=>$row){
				$days[]				=$step;
				foreach($row as $partition=>$taux){
					$theoryByPartition[$partition][]=$taux['occupation']*100;
					$realByPartition[$partition][]=($taux['occupation']+$taux['indispo'])*100;
				}
			}
			
			//axe Y;
			$theoryByPartitionData->addPoints($days,"Jours");
			$theoryByPartitionData->setSerieDescription("Jours","Jours");
			$theoryByPartitionData->setAbscissa("Jours");
			
			//axe Y;
			$realByPartitionData->addPoints($days,"Jours");
			$realByPartitionData->setSerieDescription("Jours","Jours");
			$realByPartitionData->setAbscissa("Jours");
			
			//axe X
			$theoryByPartitionData->setAxisName(0,"% Occupation théorique");
			$realByPartitionData->setAxisName(0,"% Occupation réelle");
			$AxisBoundaries = array(0=>array("Min"=>0,"Max"=>100));
			$ScaleSettings  = array("Mode"=>SCALE_MODE_START0,"ManualScale"=>$AxisBoundaries,"GridR"=>180,"GridG"=>180,"GridB"=>180);
			//$myData->setAxisDisplay(0,AXIS_FORMAT_CURRENCY);
			
			foreach($theoryByPartition as $partition=>$taux){
				$theoryByPartitionData->addPoints($taux,"$partition");
				$theoryByPartitionData->setSerieDescription("$partition","$partition");
				$theoryByPartitionData->setSerieWeight("$partition",1);
			}
			
			foreach($realByPartition as $partition=>$taux){
				$realByPartitionData->addPoints($taux,"$partition");
				$realByPartitionData->setSerieDescription("$partition","$partition");
				$realByPartitionData->setSerieWeight("$partition",1);
			}
			
			$picturesPath[]	=$this->rootName.'_Theory.png';
			$picturesPath[]	=$this->rootName.'_Real.png';
			
			$stringParameters = $parameters['cluster']." du ".$parameters['month']."-".$parameters['year'];
			$titleTheory = "Taux d'occupation théorique des CPUs par partition pour $stringParameters";
			$titleReal = "Taux d'occupation réel des CPUs par partition pour $stringParameters";
			
			$this->picture($picturesPath[0],$titleTheory,$ScaleSettings,$theoryByPartitionData,$parameters);
			$this->picture($picturesPath[1],$titleReal,$ScaleSettings,$realByPartitionData,$parameters);
			
			//$this->createCsv($parameters['results'],'indispo');
			//$this->createCsv($parameters['results'],'occupation');
			return $picturesPath;
		}
		
		private function picture($chartPath,$title,$ScaleSettings,$myData,$parameters){
			/* Create the pChart object */
			$myPicture = new pImage(1100,600,$myData);
			//$myPicture->drawGradientArea(0,0,1200,600,DIRECTION_VERTICAL,array("StartR"=>220,"StartG"=>220,"StartB"=>220,"EndR"=>255,"EndG"=>255,"EndB"=>255,"Alpha"=>100));
			//$myPicture->drawRectangle(0,0,799,599,array("R"=>200,"G"=>200,"B"=>200));

			/* Write the picture title */ 
			$myPicture->setFontProperties(array("FontName"=>"librairies/pChart/fonts/Forgotte.ttf","FontSize"=>$this->fontSizeScale));
			$myPicture->drawText(60,35,$title,array("FontSize"=>20,"Align"=>TEXT_ALIGN_BOTTOMLEFT));

			/* Do some cosmetic and draw the chart */
			$myPicture->setGraphArea(60,40,1070,550);
			//$myPicture->drawFilledRectangle(60,40,770,570,array("R"=>255,"G"=>255,"B"=>255,"Surrounding"=>-200,"Alpha"=>10));
			$myPicture->drawScale($ScaleSettings);
			$myPicture->setShadow(TRUE,array("X"=>2,"Y"=>2,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));
			$myPicture->setFontProperties(array("FontName"=>"librairies/pChart/fonts/Forgotte.ttf","FontSize"=>$this->fontSizeLegend));
			$myPicture->drawSplineChart();
			$myPicture->setShadow(FALSE);
			/* Write the chart legend */ 
			$myPicture->drawLegend(300,580,array("Style"=>LEGEND_ROUND,"Mode"=>LEGEND_HORIZONTAL));
			$myPicture->Render($chartPath);
		}
		
		private function createCsv($results){
			$csvFile=$this->rootName.".csv";
			$handle = fopen($csvFile, "w");
			//columns title
			$titleArray[]="Jour";
			foreach($results[1] as $partition => $taux){
				$titleArray[]=$partition.": occupation";
				$titleArray[]=$partition.": indisponibilite";
			}
			fputcsv($handle,$titleArray,';');
			//data

			foreach($results as $jour =>$partition){
				$line=array();
				$jour=$row['step']=date($this->csvDateFormat, strtotime($jour));
				$line[]=$jour;
				foreach($partition as $taux){
					$row['occupation']=number_format($row['occupation'],4,',',' ');
					$row['indispo']=number_format($row['indispo'],4,',',' ');
				}
				fputcsv($handle,$line,';');
				$i++;
			}
			fclose($handle);
			return $csvFile;
		}
		
		public function exportToZip($parameters){
			$this->setData($parameters);
			$list=$this->createChart($parameters);
			$list[]=$this->createCsv($parameters['results']);
			$zipPath = $this->rootName.'.zip'; // chemin système (local) vers le fichier
			//the function take for principle that the chart and the result are already displayed
			$this->createZip($zipPath,$list);
			$this->headerForZip($zipPath);
			exit; // nécessaire pour être certain de ne pas envoyer de fichier corrompu
		}
	}
?>