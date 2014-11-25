<?php foreach($results as $month => $monthResults){?>
<table border="1" class="resultsTable">
	<caption>Noeuds les plus utilisés<?php echo " pour le ".date('m-Y', strtotime($month)) ?>  </caption>
	<thead><tr>
		<th>Noeud</th>
		<th>Heures CPU</th>
	</tr></thead>
	<tbody>
	<?php foreach($monthResults as $row){ ?>
		<tr>
			<td><?php echo $row['node'] ?></td>
			<td><?php echo number_format($row['heurescpu'],2,',',' ') ?></td>
		</tr>
	<?php } ?>
	</tbody>
</table>
<?php }?>