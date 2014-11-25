<?php
	require_once("OcriControllerChart.php");
	class IndOccupationFileSystemController extends OcriControllerChart{
	
		protected $rootName;
		private function flatResultToByElement($results){
			$finalResults=array();
			foreach($results as $row){
				$finalResults[date("d", strtotime($row['jour']))][$row['mount_point']]=$row['usage'];
			}
			return $finalResults;
		}
		
		public function getResult($parameters){
			$this->setData($parameters);
			if(count($parameters['results'])>0){
				$parameters['chartPath']=$this->createChart($parameters);
				$display=$this->getView('IndOccupationFileSystemTable', $parameters);
			}else $display = "Veuillez vérifier les paramètres, la requête ne retourne aucun résultat";
			
			return $display;
		}
		
		private function setData(&$parameters){
			$parameters['results'] = $this->adaptator->occupationFileSystem($parameters['cluster'],$parameters['month'],$parameters['year']);
			$this->rootName.= 'indOccupationFileSystem_'.$parameters['cluster']."_".$parameters['year']."-".$parameters['month'];
			$parameters['results']=$this->flatResultToByElement($parameters['results']);
		}
		
		private function createChart($parameters){
		
			/* Create and populate the pData object */
			$data	= new pData();
			foreach($parameters['results'] as $step=>$row){
				$days[]				=$step;
				foreach($row as $partition=>$taux){
					$onlyValue[$partition][]=$taux;
				}
			}
			
			//axe Y;
			$data->addPoints($days,"Jours");
			$data->setSerieDescription("Jours","Jours");
			$data->setAbscissa("Jours");
			
			//axe X
			$data->setAxisName(0,"% Occupation théorique");
			$ScaleSettings  = array("Mode"=>SCALE_MODE_START0,"GridR"=>180,"GridG"=>180,"GridB"=>180);
			//$myData->setAxisDisplay(0,AXIS_FORMAT_CURRENCY);
			
			foreach($onlyValue as $partition=>$taux){
				$data->addPoints($taux,"$partition");
				$data->setSerieDescription("$partition","$partition");
				$data->setSerieWeight("$partition",1);
			}
			
			$chartPath	=$this->rootName.'.png';
			
			$this->picture($chartPath,$ScaleSettings,$data,$parameters);

			return $chartPath;
		}
		
		private function picture($chartPath,$ScaleSettings,$myData,$parameters){
			/* Create the pChart object */
			$myPicture = new pImage(1100,600,$myData);
			//$myPicture->drawGradientArea(0,0,1200,600,DIRECTION_VERTICAL,array("StartR"=>220,"StartG"=>220,"StartB"=>220,"EndR"=>255,"EndG"=>255,"EndB"=>255,"Alpha"=>100));
			//$myPicture->drawRectangle(0,0,799,599,array("R"=>200,"G"=>200,"B"=>200));

			/* Write the picture title */ 
			$myPicture->setFontProperties(array("FontName"=>"librairies/pChart/fonts/Forgotte.ttf","FontSize"=>$this->fontSizeScale));
			$myPicture->drawText(60,35,"
				Taux d'occupation des systèmes de fichier pour ".$parameters['cluster']." du ".$parameters['month']."-".$parameters['year'],
				array("FontSize"=>20,"Align"=>TEXT_ALIGN_BOTTOMLEFT)
			);

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
			foreach($results['01'] as $partition => $taux){
				$titleArray[]=$partition;
			}
			fputcsv($handle,$titleArray,';');
			
			//data
			foreach($results as $jour =>$partition){
				$line=array();
				$line[]=date($this->csvDateFormat, strtotime($jour));
				foreach($partition as $taux){
					$line[]=number_format($taux,4,',',' ');
				}
				fputcsv($handle,$line,';');
			}
			fclose($handle);
			return $csvFile;
		}
		
		public function exportToZip($parameters){
			$this->setData($parameters);
			$list[]=$this->createChart($parameters);
			$list[]=$this->createCsv($parameters['results']);
			$zipPath = $this->rootName.'.zip'; // chemin système (local) vers le fichier
			//the function take for principle that the chart and the result are already displayed
			$this->createZip($zipPath,$list);
			$this->headerForZip($zipPath);
			exit; // nécessaire pour être certain de ne pas envoyer de fichier corrompu
		}
	}
?>