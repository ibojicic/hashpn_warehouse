<?php
	//if ($curUser !== "ivan") exit();
	
	$rememberorder = "";
	if ($setSelection->view == "wall" and $maxpages == 1) {
		$rememberorder = "<fieldset><input type='submit' name='rememberorder' value='Remember current order' size='25' class='text ui-widget-content ui-corner-all'/></fieldset>";
	}
?>
		
	<div id='usersamples-form' class ='tabdialog' title='User samples...'>
			<form id ='form' action='dbMainPage.php' method = 'GET'>
				<input type='hidden' name='usersamples' value='submit'>
				<input type='hidden' name='checkedobjects' value=''>
				<?php echo $rememberorder;?>
				<fieldset>
					<ul style='padding:5px 10px; list-style:none;'>
						<li><input type='radio' name='addelsel' value='addsample' size='25' class='text ui-widget-content ui-corner-all'/>Add new sample</li>
						<li><input type='radio' name='addelsel' value='add' size='25' class='text ui-widget-content ui-corner-all' checked/>Add objects to a sample</li>
						<li><input type='radio' name='addelsel' value='del' size='25' class='text ui-widget-content ui-corner-all'/>Delete objects from a sample</li>
						<li><input type='radio' name='addelsel' value='delsample' size='25' class='text ui-widget-content ui-corner-all'/>Delete whole sample</li>
					</ul>
				</fieldset>
				<div class='selectobjs'>
					<fieldset>
						<ul style='padding:5px 10px; list-style:none;'>
							<li><input type='radio' name='sellall' value='selected' size='25' class='text ui-widget-content ui-corner-all' checked/>Apply to selected objects</li>
							<li><input type='radio' name='sellall' value='all' size='25' class='text ui-widget-content ui-corner-all'/>Apply to all objects (in the current sample)</li>
						</ul>
					</fieldset>
				</div>
				<div class='oldusersample'>
					<fieldset>
						<h4>existing sample</h4>
						<?php echo $MainPage->selectUserSample();?>
					</fieldset>
				</div>
				<div class='newusersample'>
					<fieldset>
						<h4>new sample</h4>
						<span style='white-space:nowrap;'>
							<label for='samplename'>Sample Name</label>
							<input type='text' id='samplename'  name='samplename' class='text ui-widget-content ui-corner-all' size='25' />
						</span>
						<span style='white-space:nowrap;'>
							<label for='sampledesc'>Sample Desc.</label>
							<input type='text' id='sampledesc' name='sampledesc' class='text ui-widget-content ui-corner-all' size='25' />
						</span>				
					</fieldset>
				</div>
			</form>
		</div>