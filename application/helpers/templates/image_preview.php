
<?php $onclick = 'document.getElementById(\'remove_image\').click()'; ?>

<div id="image_preview_area">
	<img id="image_preview" alt=" "  />
	<div class="remove_image_area">
		<input id="remove_image" style="display: none" />
		<input type="button" class="btn btn-danger btn-sm" onclick="<?php echo $onclick; ?>" value="X"
			title="Remove image" />
	</div>
</div>