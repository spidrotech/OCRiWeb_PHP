<form id="indOccupationCpuForm" class="formInd" action="index.php">
	<input type="hidden" name="ind" value="DisponibilityNodes">
	<label for="monthStart">Mois début</label>
	<select id="monthStart" name="monthStart">
		<option value="1" <?php if(isset($monthStart) && $monthStart==1) echo "selected" ?>>Janvier</option>
		<option value="2" <?php if(isset($monthStart) && $monthStart==2) echo "selected" ?>>Février</option>
		<option value="3" <?php if(isset($monthStart) && $monthStart==3) echo "selected" ?>>Mars</option>
		<option value="4" <?php if(isset($monthStart) && $monthStart==4) echo "selected" ?>>Avril</option>
		<option value="5" <?php if(isset($monthStart) && $monthStart==5) echo "selected" ?>>Mai</option>
		<option value="6" <?php if(isset($monthStart) && $monthStart==6) echo "selected" ?>>Juin</option>
		<option value="7" <?php if(isset($monthStart) && $monthStart==7) echo "selected" ?>>Juillet</option>
		<option value="8" <?php if(isset($monthStart) && $monthStart==8) echo "selected" ?>>Août</option>
		<option value="9" <?php if(isset($monthStart) && $monthStart==9) echo "selected" ?>>Septembre</option>
		<option value="10" <?php if(isset($monthStart) && $monthStart==10) echo "selected" ?>>Octobre</option>
		<option value="11" <?php if(isset($monthStart) && $monthStart==11) echo "selected" ?>>Novembre</option>
		<option value="12" <?php if(isset($monthStart) && $monthStart==12) echo "selected" ?>>Décembre</option>
	</select>
	<label for="year">Année début</label>
	<select id="year" name="year">
	<?php for($i=2011;$i<=$currentYear;$i++){ ?>
		<option value="<?php echo $i ?>" <?php if(isset($year) && $year==$i) echo "selected" ?>><?php echo $i ?></option>
	<?php } ?>
	</select>
	<label for="monthEnd">Mois fin</label>
	<select id="monthEnd" name="monthEnd">
		<option value="1" <?php if(isset($monthEnd) && $monthEnd==1) echo "selected" ?>>Janvier</option>
		<option value="2" <?php if(isset($monthEnd) && $monthEnd==2) echo "selected" ?>>Février</option>
		<option value="3" <?php if(isset($monthEnd) && $monthEnd==3) echo "selected" ?>>Mars</option>
		<option value="4" <?php if(isset($monthEnd) && $monthEnd==4) echo "selected" ?>>Avril</option>
		<option value="5" <?php if(isset($monthEnd) && $monthEnd==5) echo "selected" ?>>Mai</option>
		<option value="6" <?php if(isset($monthEnd) && $monthEnd==6) echo "selected" ?>>Juin</option>
		<option value="7" <?php if(isset($monthEnd) && $monthEnd==7) echo "selected" ?>>Juillet</option>
		<option value="8" <?php if(isset($monthEnd) && $monthEnd==8) echo "selected" ?>>Août</option>
		<option value="9" <?php if(isset($monthEnd) && $monthEnd==9) echo "selected" ?>>Septembre</option>
		<option value="10" <?php if(isset($monthEnd) && $monthEnd==10) echo "selected" ?>>Octobre</option>
		<option value="11" <?php if(isset($monthEnd) && $monthEnd==11) echo "selected" ?>>Novembre</option>
		<option value="12" <?php if(isset($monthEnd) && $monthEnd==12) echo "selected" ?>>Décembre</option>
	</select>
	<label for="period">Granularité</label>
	<select id="period" name="period">
		<option value="day" <?php if(isset($period) && $period=='day') echo "selected" ?>>Jour</option>
		<option value="week" <?php if(isset($period) && $period=='week') echo "selected" ?>>Semaine</option>
		<option value="month" <?php if(isset($period) && $period=='month') echo "selected" ?>>Mois</option>
	</select>
	<label for="cluster">Calculateur</label>
	<select id="cluster" name="cluster">
	<?php foreach($clusterList as $clusterName){ ?>
		<option value="<?php echo $clusterName ?>" <?php if(isset($clusterName) && isset($cluster) && $clusterName==$cluster) echo "selected" ?>><?php echo $clusterName ?></option>
	<?php } ?>
	</select>
	<input type="Submit" value="Afficher" name="action">
	<input type="Submit" value="Exporter" name="action">
</form>
<span class="highlight">Attention, la production de cet indicateur peut prendre beaucoup de temps, veuillez être patient.</span>