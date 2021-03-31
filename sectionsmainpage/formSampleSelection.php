<!-- COMBINED SEARCH -->
<div id='searchfull-form' class ="tabdialog" title='Combined search...'>
    <form action='dbMainPage.php' enctype="multipart/form-data" method = 'POST'>
        <input type='hidden' name='rulesearch' value='SEARCH'>
        <input type='hidden' name='positionsearch' value='SEARCH'>
        <input type='hidden' name='sselect' value='SEARCH'>
        <div class="div-full">
            <div class="div-left">
                <fieldset>
                    <h4> Rule search...</h4>
                    <input type="text" name='condsearch' id='condsearch' class='condpossearch text ui-widget-content ui-corner-all' 
                           size="50" placeholder="e.g. MajD > 10 and hrv < 10" value="<?php echo $selections['rulesearch']; ?>"/>
                    <h5 id="inpboxnote">Available variables...</h5>
                    <div id="infotables">
                        <?php echo $MainPage->createVarLists(); ?>
                    </div>
                </fieldset>
                <fieldset>
                    <h4>Position search...</h4>
                    <table class="condpossearch">
                        <tr>
                            <td colspan="6"><input type='text' name='possearch' id='possearch'  class='condpossearch text ui-widget-content ui-corner-all input-box' size="50" placeholder="e.g. 06:54:28.50 -44:58:32.6" value="<?php echo $selections['possearch']; ?>"/></td>
                        </tr>
                        <tr>
                            <td>System:</td>
                            <td>
                                <select name='poscoords'>
                                    <option value='radec' <?php echo $selections['radec']; ?>>FK5</option>
                                    <option value='galactic' <?php echo $selections['galactic']; ?>>Gal</option>
                                </select>
                            </td>
                            <td>r:</td>
                            <td><input class="textbox" TYPE=TEXT SIZE='4' NAME='posrad' VALUE ="<?php echo $selections['posrad']; ?>"></td>
                            <td>
                                <select name='posunits'>
                                    <option value='sec' <?php echo $selections['sec']; ?>>sec</option>
                                    <option value='min' <?php echo $selections['min']; ?>>min</option>
                                    <option value='deg' <?php echo $selections['deg']; ?>>deg</option>
                                </select>
                            </td>
                            <td>
                                <select name='searchbox'>
                                    <option value='cone' >cone</option>
                                    <option value='box' >box</option>
                                </select>
                            </td>
                        </tr>
                        <tr><td colspan="6"><hr></td></tr>
                        <tr>
                            <td nowrap>Upload File:</td>
                            <td colspan="5"><input type="file" name="posuploadfile"></td>

                        </tr>
                    </table>
                </fieldset>
            </div>
            <div class="div-right">	
                <fieldset>
                    <h4> Select Sample... </h4>
                    <?php echo $MainPage->createSampleCheckBox(); ?>
                </fieldset>
            </div>
            <div class="div-rightbottom">
                <fieldset>
                    <ul style="padding:5px 10px; list-style:none;">
                        <li><input type='radio' name="addorfull" value='currsel' size='25' class='text ui-widget-content ui-corner-all' checked/>Add to current selection</li>
                        <li><input type='radio' name="addorfull" value='fulldb' size='25' class='text ui-widget-content ui-corner-all'/>Search on full db</li>
                    </ul>
                </fieldset>
            </div>
        </div>

    </form>
</div>

