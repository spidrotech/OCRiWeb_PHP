<?php
	require_once("OcriControllerChart.php");
	class IndDisponibiliteController extends OcriControllerChart{
	
		protected $rootName;
		
		private function flatResultToByElement($results){
			$finalResults=array();
			foreach($results as $row){
				$finalResults[$row['step']][$row['scenario']]['dispo']=$row['dispo'];
				$finalResults[$row['step']][$row['scenario']]['next']=$row['step_next'];
			}
			return $finalResults;
		}
		
		public function __construct($adaptator,$config){
			parent::__construct($adaptator,$config);
			$this->login=$config['disponibility_login'];
		}
		protected function setData(&$parameters){
			$this->results = $this->adaptator->disponibility(
			$parameters['cluster'],$parameters['period'],
			$parameters['monthStart'],$parameters['yearStart'],
			$parameters['monthEnd'],$this->login);
			$parameters['yearEnd']=$this->adaptator->endYear($parameters['monthStart'],$parameters['monthEnd'],$parameters['yearStart']);
			$this->rootName.= 'indDisponibilite_'.$parameters['cluster']."_".$parameters['yearStart'].'-'.$parameters['monthStart'].'_'.$parameters['yearEnd'].'-'.$parameters['monthEnd'];
			$parameters['results']=$this->flatResultToByElement($this->results);
			
		}
		
		public function getResult($parameters){
			$this->setData($parameters);
			if(count($parameters['results'])>0){
				$parameters['chartPath']=$this->createChart($parameters);
				$display=$this->getView('IndDisponibiliteTable', $parameters);
			}else $display = "Veuillez vérifier les paramètres, la requête ne retourne aucun résultat";
			
			return $display;
		}
		
		private function createChart($parameters){
			/* Create and populate the pData object */
			$myData = new pData();
			
			foreach($parameters['results'] as $step=>$row){
				$steps[]		=date("d-m-y", strtotime($step));
				foreach($row as $scenario=>$taux){
					$dispoByScenario[$scenario][]=$taux['dispo']*100;
				}
			}
			$labelSkip=intval(count($steps)/60);
			
			//axe X;
			$myData->addPoints($steps,"Etapes");
			$myData->setSerieDescription("Etapes","Etapes");
			$myData->setAbscissa("Etapes");
			
			//axe Y: dispo
			$myData->setAxisName(0,"Disponibilité");
			
			$ScaleSettings  = array("Mode"=>SCALE_MODE_START0,"LabelRotation"=>90,"LabelSkip"=>$labelSkip,"GridR"=>180,"GridG"=>180,"GridB"=>180,"DrawYLines"=>array(0));
			
			//dispo
			foreach($dispoByScenario as $scenario=>$taux){
				$myData->addPoints($taux,"$scenario");
				$myData->setSerieDescription("$scenario","$scenario");
				$myData->setSerieWeight("$scenario",1);
			}
			$chartPath=$this->rootName.'.png';
			
			/* Create the pChart object */
			$myPicture = new pImage(1100,630,$myData);
			//$myPicture->drawGradientArea(0,0,1200,600,DIRECTION_VERTICAL,array("StartR"=>220,"StartG"=>220,"StartB"=>220,"EndR"=>255,"EndG"=>255,"EndB"=>255,"Alpha"=>100));
			//$myPicture->drawRectangle(0,0,1099,599,array("R"=>200,"G"=>200,"B"=>200));

			/* Write the picture title */ 
			$myPicture->setFontProperties(array("FontName"=>"librairies/pChart/fonts/Forgotte.ttf","FontSize"=>$this->fontSizeScale));
			$myPicture->drawText(60,35,
				"Disponibilité de ".$parameters['cluster']." du ".$parameters['monthStart'].'-'.$parameters['yearStart'].' au '.$parameters['monthEnd'].'-'.$parameters['yearEnd'],
				array("FontSize"=>20,"Align"=>TEXT_ALIGN_BOTTOMLEFT)
			);

			/* Do some cosmetic and draw the chart */
			$myPicture->setGraphArea(60,40,1040,520);
			$myPicture->drawScale($ScaleSettings);
			$myPicture->setShadow(TRUE,array("X"=>2,"Y"=>2,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));
			$myPicture->setFontProperties(array("FontName"=>"librairies/pChart/fonts/Forgotte.ttf","FontSize"=>$this->fontSizeLegend));
			$myPicture->drawLineChart();
			$myPicture->setShadow(FALSE);
			/* Write the chart legend */ 
			$myPicture->drawLegend(300,600,array("Style"=>LEGEND_ROUND,"Mode"=>LEGEND_HORIZONTAL));
			$myPicture->Render($chartPath);
			return $chartPath;
		}
		
		private function createCsv($results){
			$csvFile=$this->rootName.".csv";
			$handle = fopen($csvFile, "w");
			//columns title
			$titleArray[]="Etape";
			foreach(current($results) as $scenario => $taux){
				$titleArray[]=$scenario;
			}
			fputcsv($handle,$titleArray,';');
			
			//data
			foreach($results as $step =>$scenario){
				$line=array();
				$line[]=date($this->csvDateFormat, strtotime($step));
				foreach($scenario as $taux){
					$line[]=number_format($taux['dispo'],4,',',' ');
				}
				fputcsv($handle,$line,';');
			}
			fclose($handle);
			return $csvFile;
		}
		
		public function exportToZip($parameters){
			$this->setData($parameters);
			$this->createCsv($parameters['results']);
			$zipPath = $this->rootName.'.zip'; // chemin système (local) vers le fichier
			//the function take for principle that the chart and the result are already displayed
			$this->createZip($zipPath,array($this->rootName.'.csv',$this->rootName.'.png'));
			$this->headerForZip($zipPath);
			exit; // nécessaire pour être certain de ne pas envoyer de fichier corrompu
		}
		
		public function details($parameters){
			$parameters['results']=$this->adaptator->detailsJobsDisponibility($parameters['cluster'],$parameters['start'],$parameters['end'],$this->login);
			return $this->getView('IndDisponibiliteDetails', $parameters);
		}
	}
?>