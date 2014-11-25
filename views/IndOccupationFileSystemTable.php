<image class="chartIndicator" src="<?php echo $chartPath ?>">
<table border="1" id="indOccupationFileSystemTable" class="resultsTable">
	<caption>Taux d'occupation des systèmes de fichier pour <?php echo $cluster ?> <br> Du <?php echo $month.'-'.$year ?> </caption>
	<thead><tr>
		<th>Jour</th>
		<?php foreach($results['01'] as $partition=>$taux){ ?>
		<th><?php echo $partition ?></th>
		<?php } ?>
	</tr></thead>
	<tbody>
	<?php foreach($results as $step=>$row){ ?>
		<tr>
			<td><?php echo $step; ?></td>
			<?php foreach($results[$step] as $taux){ ?>
			<td><?php echo number_format($taux,2,',',' ') ?></td>
			<?php } ?>
		</tr>
	<?php } ?>
	</tbody>
</table>