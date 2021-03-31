<aside id="sidebar_object">
    <div class="module_content">
        <h2>Plot selection</h2>
        <fieldset>
        <table>
            <!--     SELECT PLOT -->
            <tr>
                <td>
                    <form id ='form' action='plotDataPage.php' method = 'POST'>
                            <h3>Select existing plot:</h3><?php echo $selectplot; ?>
                            <input type='hidden' class="extendbutton" name='selection' value='selectplot'>
                    </form>
                </td>
            </tr>
            <tr><td><hr></td></tr>

            <!--     ADD NEW PLOT -->
            <tr>
                <td>
                    <div id='createplot-form' class ="tabdialog create-plot" title='Create New Plot'>
                        <form id ='form' action='plotDataPage.php' method = 'POST'>
                            <fieldset>
                                <table>
                                    <tr>
                                        <td>Label (max 8 char):</td><td><input type="text" name="plotlabel" maxlength="8"></td><td></td>
                                    </tr>
                                    <tr>
                                        <td>Description:</td><td><textarea maxlength="250" name="description"></textarea></td><td></td>
                                    </tr>
                                    <tr>

                                        <td>X var:</td><td><input type="text" name="xvar" id="xvar"></td><td><?php echo $selectvarsX; ?></td>
                                    </tr>
                                    <tr>
                                        <td>Y var:</td><td><input type="text" name="yvar" id="yvar"></td><td><?php echo  $selectvarsY; ?></td>
                                    </tr>
                                    <tr>
                                        <td>Add current sample:</td><td><input type="checkbox" name="addcurrent" checked></td><td></td>
                                    </tr>
                                    <tr>
                                        <td>Label of the current sample:</td><td><input type="text" name="samplename"></td><td></td>
                                    </tr>
                                    <tr>
                                        <td>Desc. of the current sample:</td><td><textarea maxlength="250" name="sampledesc"></textarea></td><td></td>
                                    </tr>
                                </table>
                                <input type='hidden' name='selection' value='createplot'>
                            </fieldset>
                            <h5 id="inpboxnote">
                                Info
                            </h5>
                        </form>
                    </div>
                    <button id="tabbutton" class="extendbutton" value="createplot-form">Create New Plot</button>
                </td>
            </tr>

            <!--     ADD CURRENT DATA TO PLOT -->
            <tr>
                <td>
                    <div id='adddata-form' class ="tabdialog adddata-plot" title='Add Selected Sample to Plot'>
                        <form id ='form' action='plotDataPage.php' method = 'POST'>
                            <fieldset>
                                Label of the current sample:<input type="text" name="samplename">
                                Desc. of the current sample:<textarea maxlength="250" name="sampledesc"></textarea>
                                <input type='hidden' name='selection' value='adddata'>
                                <input type='hidden' name='selectplot' value='<?php echo $currplot; ?>'>
                            </fieldset>
                            <h5 id="inpboxnote">
                                Info
                            </h5>
                        </form>
                    </div>
                    <button id="tabbutton" class="extendbutton" value="adddata-form">Add Selected Sample to Plot</button>
                </td>
            </tr>
            <!--     EDIT PLOT -->
            <tr>
                <td>
                    <div id='editplot-form' class ="tabdialog editplot-plot" title='Delete Plot'>
                        <form id ='form' action='plotDataPage.php' method = 'POST'>
                            <fieldset>
                                <table>
                                    <tr>
                                        <td><font color="red">You are about to delete the current plot (<?php echo $data["plotLabel"]; ?>).<br>Please confirm.</font></td>
                                    </tr>
                                </table>
                                <input type='hidden' name='selection' value='deleteplot'>
                                <input type='hidden' name='selectdelete' value='<?php echo $data["iduserPlots"]; ?>'>
                            </fieldset>
                        </form>
                    </div>
                    <button id="tabbutton" class="extendbutton" value="editplot-form">Delete Plot</button>
                </td>
            </tr>

        </table>
        </fieldset>
    </div>
</aside><!-- end of sidebar -->


