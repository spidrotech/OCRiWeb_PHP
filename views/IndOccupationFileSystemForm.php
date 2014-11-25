<form id="indOccupationFileSystemForm" class="formInd" action="index.php">
	<input type="hidden" name="ind" value="OccupationFileSystem">
	<input type="hidden" name="parametized" value="1">
	<label for="month">Mois</label>
	<select id="month" name="month">
		<option value="1" <?php if(isset($month) && $month==1) echo "selected" ?>>Janvier</option>
		<option value="2" <?php if(isset($month) && $month==2) echo "selected" ?>>Février</option>
		<option value="3" <?php if(isset($month) && $month==3) echo "selected" ?>>Mars</option>
		<option value="4" <?php if(isset($month) && $month==4) echo "selected" ?>>Avril</option>
		<option value="5" <?php if(isset($month) && $month==5) echo "selected" ?>>Mai</option>
		<option value="6" <?php if(isset($month) && $month==6) echo "selected" ?>>Juin</option>
		<option value="7" <?php if(isset($month) && $month==7) echo "selected" ?>>Juillet</option>
		<option value="8" <?php if(isset($month) && $month==8) echo "selected" ?>>Août</option>
		<option value="9" <?php if(isset($month) && $month==9) echo "selected" ?>>Septembre</option>
		<option value="10" <?php if(isset($month) && $month==10) echo "selected" ?>>Octobre</option>
		<option value="11" <?php if(isset($month) && $month==11) echo "selected" ?>>Novembre</option>
		<option value="12" <?php if(isset($month) && $month==12) echo "selected" ?>>Décembre</option>
	</select>
	<label for="year">Année</label>
	<select id="year" name="year">
	<?php for($i=2011;$i<=$currentYear;$i++){ ?>
		<option value="<?php echo $i ?>" <?php if(isset($year) && $year==$i) echo "selected" ?>><?php echo $i ?></option>
	<?php } ?>
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