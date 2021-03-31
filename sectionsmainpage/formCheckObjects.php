<div id='checkobjects' class ="tabdialog" title='Check Objects'>
	<form id ='form' action='objectCheckPage.php' target='_self' method = 'GET'>
		<input type='hidden' name='checkselect' value='submit'>
			<fieldset>
				<?php echo $MainPage->createCheckSampleCheckBox();?>
			</fieldset>
	</form>
</div>
