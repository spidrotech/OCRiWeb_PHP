<image id="ind12Chart" class="chartIndicator" src="<?php echo $chartPath ?>">
<table border="1" id="indDisponibiliteTable" class="resultsTable">
	<caption>Disponibilité de bout en bout pour <?php echo $cluster." du ".$monthStart."-".$yearStart.' au '.$monthEnd."-".$yearEnd ?> </caption>
	<thead><tr>
		<th>Etape</th>
		<?php foreach(current($results) as $scenario=>$taux){ ?>
		<th><?php echo $scenario ?></th>
		<?php } ?>
		<th>Voir les jobs</th>
	</tr></thead>
	<tbody>
	<?php foreach($results as $step => $taux){ ?>
		<tr>
			<?php $format=($period!='hour')?"d-m-y":"d-m-y H"; ?>
			<td><?php echo date($format, strtotime($step)); ?></td>
			<?php foreach($taux as $scenario => $taux){ ?>
			<td><?php echo number_format($taux['dispo']*100,2,',',' '); ?></td>
			<?php } ?>
			<td><form target="_blank"> 
				<input type="hidden" name="ind" value="Disponibilite" />
				<input type="hidden" name="cluster" value="<?php echo $cluster ?>" />
				<input type="hidden" name="start" value="<?php echo $step ?>" />
				<input type="hidden" name="end" value="<?php echo $taux['next'] ?>" />
				<input type="Submit" value="Details" name="action">
			</form></td>
		</tr>
	<?php } ?>
	</tbody>
</table>