<div id='showselect-form' class ='tabdialog' title='<?php echo $selectmessage;?>'>
	<fieldset>
	<form id ='form' action='dbMainPage.php' method = 'POST'>
		<input type='hidden' name='<?php echo $selecttype;?>' value='submit'>
		<?php echo $selectBox; ?>
	</form>
	</fieldset>
</div>

	