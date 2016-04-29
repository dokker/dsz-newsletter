<select name="<?php echo $listdata['name'] ?>" class="<?php echo $listdata['class'] ?>">
	<?php foreach ($listdata['data'] as $item): ?>
		<option value="<?php echo $item['id'] ?>"><?php echo $item['label'] ?></option>
	<?php endforeach; ?>
</select>