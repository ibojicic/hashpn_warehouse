<div id='cursellection' class ="mesgdialog" title='Current Selection'>
	<?php if ($selections['rulesearch'].$selections['textsearch'] != "") echo "<h3>Applied Rule:</h3><fieldset>".$selections['rulesearch'].$selections['textsearch'] ."</fieldset>"; ?>
	<?php if ($selections['displayposition']) echo "<fieldset>".$selections['displayposition']."</fieldset>"; ?>
	<h3>Selected Samples:</h3>
	<fieldset>
		<?php echo $setSelection->showSelectedSamples;?>
	</fieldset>
</div>