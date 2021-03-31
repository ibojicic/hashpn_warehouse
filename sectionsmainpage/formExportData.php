<div id='exportdata' class ="tabdialog" title='Export Data'>
	<form id ='form' action='dbMainPage.php' target='_self' method = 'POST'>
            <input type='hidden' name='exportdata' value='export'>
		<fieldset>
			<h4> Choose format: </h4>
			<table class="<?php echo $mydbConfig['cssclasses']['exportdata'];?>">
				<tr><td><input type="radio" name="format" value="csv" checked>.csv</td><td>Coma separated values</td></tr>
				<!--<tr><td><input type="radio" name="format" value="xml" disabled>.xml</td><td>XML</td></tr>-->
				<!--<tr><td><input type="radio" name="format" value="ascii" disabled>.ascii</td><td>ASCII</td></tr>-->
				<!--<tr><td><input type="radio" name="format" value="pdf" disabled>.pdf</td><td>Adobe PDF</td></tr>-->
			</table>
		</fieldset>
	</form>
</div>
