	<div id='searchtext-form' class ="tabdialog text-search" title='Text search...'>
	<form id ='form' action='dbMainPage.php' enctype="multipart/form-data" method = 'POST'>
		<input type='hidden' name='intextsearch' value='SEARCH'>
		<fieldset>
			<table>
				<tr>
                                    <td><input type='text' name='textsearch' size='35' id='textsearch' class='text ui-widget-content ui-corner-all' placeholder="e.g. ngc 1"/></td>
                                    <td>in</td><td><?php echo $MainPage->textSearchOptions();?></td>
				</tr>
                                <tr>
                                    <td colspan="3">Upload File:<input type="file" name="txtuploadfile"></td>
                                </tr>
			</table>
		</fieldset>
		<fieldset>
			<input type='checkbox' name="addsesame" value='y'/>Add results from <a href="http://cds.u-strasbg.fr/cgi-bin/Sesame">Sesame</a> Name Resolver
		</fieldset>
		<h5 id="inpboxnote">
			Note: Text search is ALWAYS performed on the full database <br>
			i.e. your current sample selection will be lost!!
		</h5>
	</form>
</div>


