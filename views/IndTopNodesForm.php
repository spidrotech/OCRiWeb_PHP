<form id="indTopNodesForm" class="formInd" action="index.php">
	<input type="hidden" name="ind" value="TopNodes">
	<input type="hidden" name="parametized" value="1">
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
	<label for="yearStart">Année début</label>
	<select id="yearStart" name="yearStart">
	<?php for($i=2011;$i<=$currentYear;$i++){ ?>
		<option value="<?php echo $i ?>" <?php if(isset($yearStart) && $yearStart==$i) echo "selected" ?>><?php echo $i ?></option>
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
	<label for="cluster">Calculateur</label>
	<select id="cluster" name="cluster">
	<?php foreach($clusterList as $clusterName){ ?>
		<option value="<?php echo $clusterName ?>" <?php if(isset($clusterName) && isset($cluster) && $clusterName==$cluster) echo "selected" ?>><?php echo $clusterName ?></option>
	<?php } ?>
	</select>
	<label for="limit">Nombre de résultats par mois</label>
	<select id="limit" name="limit">
		<option value="10" <?php if(isset($limit) && $limit==10) echo "selected" ?>>10</option>
		<option value="25" <?php if(isset($limit) && $limit==25) echo "selected" ?>>25</option>
		<option value="50" <?php if(isset($limit) && $limit==50) echo "selected" ?>>50</option>
		<option value="100" <?php if(isset($limit) && $limit==100) echo "selected" ?>>100</option>
	</select>
	<input type="Submit" value="Afficher" name="action">
	<input type="Submit" value="Exporter" name="action">
</form>