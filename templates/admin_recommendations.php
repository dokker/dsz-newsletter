    <div class="postbox">
      <div class="handlediv" title="Bővebben kikapcsolás/bekapcsolás"><br></div>
      <h3 class="hndle ui-sortable-handle"><span>Ajánlott előadások</span></h3>
      <div class="inside">
      	<ul class="nl-sortable recommendations"><?php if(isset($list_selected_recommended)) echo $list_selected_recommended; ?></ul>
      	<div class="choices">
      		<?php echo $list_shows_recommended; ?>
      		<button class="button button-secondary add_recommendation">Hozzáadás</button>
          <input type="hidden" name="input-recommendations" class="input-recommendations" />
      	</div>
      </div>
    </div>
