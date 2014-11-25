<?php
	require_once("OcriControllerChart.php");
	class IndJobsAttenteProductionController extends OcriControllerChart{
	
		protected $rootName;
		
		public function getResult($parameters){
			$this->setData($parameters);
			if(count($parameters['results'])>0){
				$parameters['chartPath']=$this->createChart($parameters);
				$display=$this->getView('IndJobsAttenteProductionTable', $parameters);
			}else $display = "Veuillez vérifier les paramètres, la requête ne retourne aucun résultat";
			
			return $display;
		}
		
		private function setData(&$parameters){
			$parameters['mode']=isset($parameters['mode'])?true:false;
			$parameters['results'] = $this->adaptator->jobsByHours($parameters['cluster'],$parameters['month'],$parameters['year'],$parameters['mode']);
			$this->rootName.= 'indJobsAttenteProduction_'.$parameters['cluster']."_".$parameters['year']."-".$parameters['month'];
		}
		
		private function createChart($parameters){
			/* Create and populate the pData object */
			$myData = new pData();
			$mode = $parameters['mode'];
			foreach($parameters['results'] as $row){
				$hours[]				=date("d-m", strtotime($row['step']));
				$nbJobsProd[]			=$row['jobs_prod'];
				$nbJobsWait[]			=$row['jobs_wait'];
				$nbCpuProd[]			=$row['nb_cpus_prod'];
				$nbCpuWait[]			=$row['nb_cpus_wait'];
			}
			
			//axe Y;
			$myData->addPoints($hours,"Heures");
			$myData->setSerieDescription("Heures","Heures");
			$myData->setAbscissa("Heures");
			
			//axe X
			//jobs en prod
			$myData->addPoints($nbJobsProd,"nbJobsProd");
			$myData->setSerieDescription("nbJobsProd","Jobs en production");
			$myData->setPalette("nbJobsProd",array("R"=>205,"G"=>0,"B"=>0));

			//job en attente
			$myData->addPoints($nbJobsWait,"nbJobsWait");
			$myData->setSerieDescription("nbJobsWait","Jobs en attente");
			$myData->setPalette("nbJobsWait",array("R"=>55,"G"=>91,"B"=>127));
			
			$myData->setAxisName(0,"# Jobs");
			$ScaleSettings  = array("Mode"=>SCALE_MODE_ADDALL_START0,"LabelingMethod"=>LABELING_DIFFERENT,"GridR"=>180,"GridG"=>180,"GridB"=>180);
			
			$chartPathJobs=$this->rootName.'_jobs.png';
			
			/* Create the pChart object: Jobs */
			$myJobsPicture = new pImage(1100,600,$myData);
			//$myPicture->drawRectangle(0,0,799,599,array("R"=>200,"G"=>200,"B"=>200));

			/* Write the picture title */ 
			$myJobsPicture->setFontProperties(array("FontName"=>"librairies/pChart/fonts/Forgotte.ttf","FontSize"=>$this->fontSizeScale));
			$myJobsPicture->drawText(60,35,"
				Suivi horaire du nombre de jobs en attente et en production pour ".$parameters['cluster']." du ".$parameters['month']."-".$parameters['year'],
				array("FontSize"=>20,"Align"=>TEXT_ALIGN_BOTTOMLEFT)
			);

			/* Do some cosmetic and draw the chart */
			$myJobsPicture->setGraphArea(60,40,1070,550);
			//$myPicture->drawFilledRectangle(60,40,770,570,array("R"=>255,"G"=>255,"B"=>255,"Surrounding"=>-200,"Alpha"=>10));
			$myJobsPicture->drawScale($ScaleSettings);
			$myJobsPicture->setShadow(TRUE,array("X"=>2,"Y"=>2,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));
			$myJobsPicture->setFontProperties(array("FontName"=>"librairies/pChart/fonts/Forgotte.ttf","FontSize"=>$this->fontSizeLegend));
			$myJobsPicture->drawStackedBarChart();
			/* Write the chart legend */ 
			$myJobsPicture->drawLegend(300,585,array("Style"=>LEGEND_ROUND,"Mode"=>LEGEND_HORIZONTAL));
			$myJobsPicture->Render($chartPathJobs);

			$chartPath[]=$chartPathJobs;
			
			$myData->removeSerie("nbJobsProd");
			$myData->removeSerie("nbJobsWait");
			$myData->setAxisName(0,"Heures CPU");
			
			//CPU en prod
			$myData->addPoints($nbCpuProd,"nbCpuProd");
			$myData->setSerieDescription("nbCpuProd","CPU en production");
			$myData->setPalette("nbCpuProd",array("R"=>205,"G"=>0,"B"=>0));

			//CPU en attente
			$myData->addPoints($nbCpuWait,"nbCpuWait");
			$myData->setSerieDescription("nbCpuWait","CPU en attente");
			$myData->setPalette("nbCpuWait",array("R"=>55,"G"=>91,"B"=>127));
			
			$chartPathCpu=$this->rootName.'_cpu.png';
			
			/* Create the pChart object: CPU */
			$myCpuPicture = new pImage(1100,600,$myData);
			//$myPicture->drawRectangle(0,0,799,599,array("R"=>200,"G"=>200,"B"=>200));

			/* Write the picture title */ 
			$myCpuPicture->setFontProperties(array("FontName"=>"librairies/pChart/fonts/Forgotte.ttf","FontSize"=>$this->fontSizeScale));
			$myCpuPicture->drawText(60,35,"
				Suivi horaire des heures CPU en attente et en production pour ".$parameters['cluster']." du ".$parameters['month']."-".$parameters['year'],
				array("FontSize"=>20,"Align"=>TEXT_ALIGN_BOTTOMLEFT)
			);

			/* Do some cosmetic and draw the chart */
			$myCpuPicture->setGraphArea(60,40,1070,550);
			//$myPicture->drawFilledRectangle(60,40,770,570,array("R"=>255,"G"=>255,"B"=>255,"Surrounding"=>-200,"Alpha"=>10));
			$myCpuPicture->drawScale($ScaleSettings);
			$myCpuPicture->setShadow(TRUE,array("X"=>2,"Y"=>2,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));
			$myCpuPicture->setFontProperties(array("FontName"=>"librairies/pChart/fonts/Forgotte.ttf","FontSize"=>$this->fontSizeLegend));
			$myCpuPicture->drawStackedBarChart();
			/* Write the chart legend */ 
			$myCpuPicture->drawLegend(300,585,array("Style"=>LEGEND_ROUND,"Mode"=>LEGEND_HORIZONTAL));
			$myCpuPicture->Render($chartPathCpu);
			
			$chartPath[]=$chartPathCpu;
			
			return $chartPath;
		}
		
		private function createCsv($parameters){
			$handle = fopen($this->rootName.".csv", "w");
			//columns title
			$titleArray[]="Heure";
			$titleArray[]="Production: Jobs";
			$titleArray[]="Production: CPU";
			$titleArray[]="Attente: Jobs";
			$titleArray[]="Attente: CPU";
		
			fputcsv($handle,$titleArray,';');
			//data

			foreach($parameters['results'] as $row){
				$data=array();
				$data[]=date($this->csvDateFormat, strtotime($row['step']));
				$data[]=$row['jobs_prod'];
				$data[]=$row['nb_cpus_prod'];
				$data[]=$row['jobs_wait'];
				$data[]=$row['nb_cpus_wait'];
				
				fputcsv($handle,$row,';');
				$i++;
			}
			fclose($handle);
		}
		
		public function exportToZip($parameters){
			$this->setData($parameters);
			$this->createCsv($parameters);
			$list=$this->createChart($parameters);
			$list[]=$this->rootName.'.csv';
			$zipPath = $this->rootName.'.zip'; // chemin système (local) vers le fichier
			//the function take for principle that the chart and the result are already displayed
			$this->createZip($zipPath,$list);
			$this->headerForZip($zipPath);
			exit; // nécessaire pour être certain de ne pas envoyer de fichier corrompu
		}
	}
?>