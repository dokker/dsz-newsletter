    <div class="postbox">
      <div class="handlediv" title="Bővebben kikapcsolás/bekapcsolás"><br></div>
      <h3 class="hndle ui-sortable-handle"><span>Akciós előadások</span></h3>
      <div class="inside">
        <ul class="nl-sortable nnews"><?php if(isset($list_selected_nnews)) echo $list_selected_nnews; ?></ul>
        <div class="choices">
          <p>Hírlevél hírek</p>
          <?php echo $list_nnews; ?>
          <button class="button button-secondary add_nnews">Hozzáadás</button>
          <input type="hidden" name="input-nnews" class="input-nnews" />
        </div>
        <ul class="nl-sortable featured"><?php if(isset($list_selected_featured)) echo $list_selected_featured; ?></ul>
        <div class="choices">
          <p>Előadások</p>
          <?php echo $list_shows_featured; ?>
          <button class="button button-secondary add_featured">Hozzáadás</button>
          <input type="hidden" name="input-featured" class="input-featured" />
        </div>
      </div>
    </div>
