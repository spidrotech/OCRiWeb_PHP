<?php foreach($results as $month => $monthResults){?>
<table border="1" class="resultsTable">
	<caption>Noeuds les plus utilisés<?php echo " pour le ".date('m-Y', strtotime($month)) ?>  </caption>
	<thead><tr>
		<th># CPU</th>
		<th>Heures d'attente moyenne</th>
		<th># Jobs</th>
	</tr></thead>
	<tbody>
	<?php foreach($monthResults as $row){ ?>
		<tr>
			<td><?php echo $row['nb_cpus'] ?></td>
			<td><?php echo number_format($row['hours'],2,',',' ')?></td>
			<td><?php echo $row['count'] ?></td>
		</tr>
	<?php } ?>
	</tbody>
</table>
<?php }?>