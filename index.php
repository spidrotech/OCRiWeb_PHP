<?php
	//l'index fait office de front controller
	//il n'est pas prevu pour l'instant de connection a des bases differentes
	
	//configuration
	require_once("adaptators/HpcStats.php");
	$config = parse_ini_file('config/OCRI.ini');
	$adaptator= new HpcStats($config);
	
	if(isset($_GET['ind'])){
		$indicator = $_GET['ind'];
		$className = 'Ind'.$indicator.'Controller';
		require_once("controllers/".$className.".php");
		$indicatorController= new $className($adaptator,$config);
		switch ($_GET['action']){
			default:
			case 'form':
				$form = $indicatorController->getForm($_GET);
				include_once("views/caneva.php");
			break;
			case 'Afficher':
				$form = $indicatorController->getForm($_GET);
				$result=$indicatorController->getResult($_GET);
				include_once("views/caneva.php");
			break;
			case 'Exporter':
				$indicatorController->exportToZip($_GET);
			break;
			case 'Details':
				echo $indicatorController->details($_GET);
			break;
		}
	}else include_once("views/caneva.php");
?>