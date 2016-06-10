    <div class="postbox">
      <div class="handlediv" title="Bővebben kikapcsolás/bekapcsolás"><br></div>
      <h3 class="hndle ui-sortable-handle"><span><?php _e('Segment', 'dsz-newsletter'); ?></span></h3>
      <div class="inside">
        <div class="newsletter-type-selector">
          <?php echo $list_segments; ?>
          <?php if(!isset($action)): ?>
            <input class="button button-secondary add_segment" type="submit" value="Választás" />
          <?php endif; ?>
        </div>
      </div>
    </div>
