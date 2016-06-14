<div class="wrap">
	<h1><?php _e('Newsletter campaigns' , 'dsz-newsletter'); ?> <a href="<?php echo admin_url('admin.php?page=hirlevel-add'); ?>" class="page-title-action"><?php _e('Add new', 'dsz-newsletter'); ?></a></h1>
	<div id="poststuff">
		<div class="metabox-holder columns-2">
			<div id="post-body-content" class="postbox-container" style="width: 98%;">
				<div class="meta-box-sortables ui-sortable">
				<?php if(!empty($messages)): ?>
			        <?php foreach ($messages as $message): ?>
			            <div class="notice <?php echo $message->type; ?>">
			        		<p class="nl-message"><?php echo $message->text; ?></p>
			            </div>
			        <?php endforeach; ?>
				<?php endif; ?>

				<?php echo $list_table; ?>
				</div>
			</div>
		</div>
	</div>
</div>
