<table border="0" cellpadding="0" cellspacing="0" width="100%" class="featured-area">
	<?php foreach ($featured_shows as $show): ?>
		<tr>
			<td>
				<table border="0" cellpadding="0" cellspacing="0" width="100%">
					<tr>
						<td class="featured-percent"></td>
						<td class="featured-content">
							<table border="0" cellpadding="0" cellspacing="0">
								<tr>
									<td>
										<p><?php echo $show->ido; ?> | <?php echo $show->helyszin_nev; ?></p>
										<h3><?php echo $show->cim; ?></h3>
										<p><?php echo $show->performers; ?></p>
										<p><a class="button-buy" href="http://dumaszinhaz.jegy.hu/arrivalorder.php?eid=<?php echo $show->jegyhuid; ?>&amp;template=201311_vasarlas" target="_blank">Jegyvásárlás</a></p>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	<?php endforeach; ?>
</table>
