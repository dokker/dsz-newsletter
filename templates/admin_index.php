<div class="wrap">
<h2>Hírlevél generálás</h2>
<div id="poststuff">
<div class="metabox-holder columns-2">
<div class="postbox-container" style="width: 98%;">
  <div class="meta-box-sortables ui-sortable">
	<form action="" method="post" class="nl-form">
	<?php if (!$location): ?>
	    <?php echo $selector; ?>
	<?php else: ?>
	    <?php echo $title; ?>
	    <?php echo $lead; ?>
	    <?php echo $featured; ?>
	    <?php echo $recommendations; ?>
	    <?php echo $youtube; ?>
	    <?php echo $mc_template; ?>
	    <input type="hidden" name="input-segment" value="<?php echo $segment; ?>" />
	    <input type="submit" name="nl-form-save" value="Kampány létrehozása" class="button button-primary" />
	<?php endif; ?>
    </form>
  </div>
</div>
</div>
</div>
</div>
