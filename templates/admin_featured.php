    <div class="postbox">
      <div class="handlediv" title="Bővebben kikapcsolás/bekapcsolás"><br></div>
      <h3 class="hndle ui-sortable-handle"><span>Akciós előadások</span></h3>
      <div class="inside">
        <ul class="nl-sortable featured"><?php if(isset($list_selected_featured)) echo $list_selected_featured; ?></ul>
        <div class="choices">
          <?php echo $list_shows_featured; ?>
          <button class="button button-secondary add_featured">Hozzáadás</button>
          <input type="hidden" name="input-featured" class="input-featured" />
        </div>
      </div>
    </div>
