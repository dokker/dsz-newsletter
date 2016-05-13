<table border="0" cellpadding="20" cellspacing="0" width="100%" class="recommended-area">
    <?php foreach ($recommended_shows as $show): ?>
    	<tr>
    		<td>
    			<table border="0" cellpadding="0" cellspacing="0" width="100%">
    				<tr>
    					<td class="rec-image"><img src="<?php echo $show->eloadas_kepek[0]; ?>" /></td>
    					<td class="rec-content">
    						<p><?php echo $show->ido; ?> | <?php echo $show->helyszin_nev; ?></p>
    						<h3><?php echo $show->cim; ?></h3>
    						<p><?php echo $show->performers; ?></p>
                            <p><a class="button-buy" href="http://dumaszinhaz.jegy.hu/arrivalorder.php?eid=<?php echo $show->jegyhuid; ?>&amp;template=201311_vasarlas" target="_blank">Jegyvásárlás</a></p>
    					</td>
    				</tr>
    			</table>
    		</td>
    	</tr>
    <?php endforeach; ?>
</table>
