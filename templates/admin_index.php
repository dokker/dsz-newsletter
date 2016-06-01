<div class="wrap">
<h2>Hírlevél generálás</h2>
<div id="poststuff">
<div class="metabox-holder columns-2">
<div class="postbox-container" style="width: 98%;">
  <div class="meta-box-sortables ui-sortable">
	<form action="" method="post" class="nl-form">
	<?php if(!empty($message)): ?>
		<p class="nl-message"><?php echo $message; ?></p>
	<?php endif; ?>

<?php switch ($phase):  
case 0: ?>
	<?php echo $selector; ?>
<?php break; ?>
<?php case 1: ?>
    <?php echo $title; ?>
    <?php echo $lead; ?>
    <?php echo $featured; ?>
    <?php echo $recommendations; ?>
    <?php echo $youtube; ?>
    <?php echo $mc_template; ?>
    <input type="hidden" name="input-segment" value="<?php echo $segment; ?>" />
    <input type="submit" name="nl-form-save" value="Kampány létrehozása" class="button button-primary" />
<?php break; ?>
<?php case 2: ?>
	<input type="hidden" name="nl-campaign-id" value="<?php echo $campaign_id; ?>" />
    <input type="submit" name="nl-form-execute" value="Hírlevél kiküldése" class="button button-primary" />
<?php break; ?>
<?php case 3: ?>
<?php break; ?>
<?php endswitch; ?>

		<input type="hidden" name="nl-phase" value="<?php echo $phase; ?>" />
    </form>
  </div>
</div>
</div>
</div>
</div>
