<?php foreach($results as $month => $monthResults){?>
<table border="1" class="resultsTable">
	<caption>Top des utilisateurs selon <?php echo "$typeCaption pour le ".date('m-Y', strtotime($month)) ?>  </caption>
	<thead><tr>
		<th>Utilisateurs</th>
		<th># Jobs</th>
		<?php if($type!='nbjobs') echo "<th>$typeColumn</th>" ?>
	</tr></thead>
	<tbody>
	<?php foreach($monthResults as $row){ ?>
		<tr>
			<td><?php echo $row['name'] ?></td>
			<td><?php echo $row['nbjobs'] ?></td>
			<?php if($type!='nbjobs') echo '<td>'.number_format($row[$type],2,',',' ')."</td>\n" ?>
		</tr>
	<?php } ?>
	</tbody>
</table>
<?php }?>