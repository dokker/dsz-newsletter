<table border="0" cellpadding="0" cellspacing="0" width="100%" class="featured-area">
	<?php foreach ($nnews as $item): ?>
		<tr>
			<td>
				<table border="0" cellpadding="0" cellspacing="0" width="100%">
					<tr>
						<td class="featured-percent"></td>
						<td class="featured-content">
							<table border="0" cellpadding="0" cellspacing="0">
								<tr>
									<td>
										<h3><?php echo $item->title; ?></h3>
										<table border="0" cellpadding="0" cellspacing="0" class="button button-buy"><tr><td><a href="<?php echo $item->permalink; ?>?<?php echo $utm; ?>" target="_blank">Tovább ►</a></td></tr></table>
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
