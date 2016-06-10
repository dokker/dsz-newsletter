<div class="wrap">
<h2><?php echo $page_title; ?></h2>
<div id="poststuff">
<div class="metabox-holder columns-2">
<div class="postbox-container" style="width: 98%;">
  <div class="meta-box-sortables ui-sortable">
	<form action="" method="post" class="nl-form">
	<?php if(!empty($messages)): ?>
        <?php foreach ($messages as $message): ?>
            <div class="notice <?php echo $message->type; ?>">
        		<p class="nl-message"><?php echo $message->text; ?></p>
            </div>
        <?php endforeach; ?>
	<?php endif; ?>

<?php if (isset($action)): ?>
    <?php switch ($action):  
    case 'edit': ?>
        <?php echo $admin_title; ?>
        <?php echo $selector; ?>
        <?php echo $lead; ?>
        <?php echo $featured; ?>
        <?php echo $recommendations; ?>
        <?php echo $youtube; ?>
        <?php echo $mc_template; ?>
        <input type="submit" name="nl-form-save" value="<?php _e('Edit campaign', 'dsz-newsletter'); ?>" class="button button-primary" />
    <?php break; ?>
    <?php endswitch; ?>
<?php else: ?>
    <?php switch ($phase):  
    case 0: ?>
        <?php echo $selector; ?>
    <?php break; ?>
    <?php case 1: ?>
        <?php echo $admin_title; ?>
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
        <div class="preview-area" style="padding-top: 30px;"><?php echo $preview; ?></div>
    <?php break; ?>
    <?php case 3: ?>
    <?php break; ?>
    <?php endswitch; ?>
<?php endif; ?>

		<input type="hidden" name="nl-phase" value="<?php echo $phase; ?>" />
    </form>
  </div>
</div>
</div>
</div>
</div>
