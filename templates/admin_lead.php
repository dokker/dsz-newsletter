    <div class="postbox">
      <div class="handlediv" title="Bővebben kikapcsolás/bekapcsolás"><br></div>
      <h3 class="hndle ui-sortable-handle"><span>Lead előadás</span></h3>
      <div class="inside">
        <div class="lead">
          <div class="submeta-area">
            <h4>Előadás részletei:</h4>
            <p class="lead-title">Cím: <span><?php echo $lead_show['title']; ?></span></p>
            <p class="lead-date">Időpont: <span><?php echo $lead_show['date']; ?></span></p>
            <p class="lead-location">Helyszín: <span><?php echo $lead_show['location']; ?></span></p>
            <div class="lead-image"><img src="<?php echo $lead_show['image']; ?>" alt="<?php echo $lead_show['title']; ?>" /></div>
            <button class="lead-image-reset button">Alapértelmezett kép visszaállítása</button>
            <label class="screen-reader-text" for="upload_image">Fejléckép</label>
            <input id="upload_image" type="hidden" size="36" name="upload_image" value="" />
            <input id="upload_image_button" class="button" type="button" value="Lead kép cseréje" />
          </div>
          <div class="submeta-area">
            <h4>További választható előadások:</h4>
            <?php echo $list_shows_recommended_lead; ?>
            <input id="lead-show-custom" class="button" type="button" value="Lead előadás cseréje" />
          </div>
        </div>
      </div>
    </div>
