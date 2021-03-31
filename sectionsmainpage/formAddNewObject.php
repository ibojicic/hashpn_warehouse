<div id='addnewobj' class ="tabdialog" title='Add New Object'>
    <form id ='form' name='addnewobj' action='addNewObjPage.php' target='_self' method = 'GET'>
        <input type='hidden' id='uidialogname' name='addnewobj' value='y'>
        <fieldset>
            <h4> Add New Object </h4>
            <table class="<?php echo $mydbConfig['cssclasses']['addnewobject']; ?>">
                <tr>					
                    <td>Coordinates:</td><td colspan="2"><input type='text' id='inpos' name='inpos' class='text ui-widget-content ui-corner-all input-box' size="22"/></td>
                    <td>
                        <select name='incoords'>
                            <option value='radec'>RA/DEC</option>
                            <option value='galactic'>Glon/Glat</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Coord. ref.</td><td colspan="2"><input type='text' id='incoordref' name='incoordref' class='text ui-widget-content ui-corner-all input-box' size="22" value="<?php echo $curUser; ?>"/></td>
                </tr>
                <tr>
                    <td>Catalogue</td><td colspan="2"><input type='text' id='incat' name='incat' class='text ui-widget-content ui-corner-all input-box' size="22"/></td>
                </tr>
                <tr>
                    <td> Domain </td>
                    <td>
                        <select name='indomain'>
                            <option value='Galaxy'>Galaxy</option>
                            <option value='LMC'>LMC</option>
                            <option value='SMC'>SMC</option>
                            <option value='other'>other EG</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td> Status </td>
                    <td>
                        <?php echo $MainPage->getInStatus(); ?>
                    </td>
                </tr>
            </table>			
        </fieldset>
    </form>
</div>
