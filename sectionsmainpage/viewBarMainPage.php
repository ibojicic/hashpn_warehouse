<section id='secondary_bar'>
    <div class='views_container'>
        <button class="viewbutton topbutton" name="view" value="table"/>Table</button>
        <button class="viewbutton topbutton" name="view" value="image">Images</button>
        <button class="viewbutton topbutton" name="view" value="groupimage">Grouped Images</button>
        <?php include_once 'formWallSelect.php'; ?>
        <button id ="tabbutton" class="topbutton" value="wall-form">Wall</button>
        <button class="viewbutton topbutton" name="restart" value="RESTART">Restart Samples</button>
        <a href="3dviewPage.php" target="_blank">3D View</a>


    </div>

    <div class="extras_container">
        <div id="bigextras" />
        <?php include_once 'messageCurrentSelection.php'; ?>
        <button id="tabbutton" class ="topbutton" value="cursellection">Current Selection</button>
        <?php
        if ($isAdmin == 1) {
            echo "<button id='tabbutton' class ='topbutton' value='checkobjects' >Check Objects</button>";
            include_once 'formCheckObjects.php';
        }

        echo "<button id='tabbutton' value='usersamples-form' class='topbutton'>User samples</button>";
        include_once 'formUserSamples.php';

        echo "<button id='showsmplcheck' value='showhide' class='topbutton'>Toggle check boxes</button>";

        if ($setSelection->view == "table") {
            echo "<button id='tabbutton' class ='topbutton' value='exportdata'>Export Data</button>";
            include_once 'formExportData.php';
        }

        if ($isAdmin == 1) {
            echo "<button id='tabbutton' class ='topbutton' value='addnewobj' >Add New Object</button>";
            include_once 'formAddNewObject.php';
        }

        if ($isAdmin == 1) {
            echo "<button class='linkbutton topbutton' name='view' value='plotDataPage'>Plot Data</button>";
        }
        ?>					

        <button class="helpbutton topbutton" value="Hextras">?</button>

    </div>
    <div id="smallextras">
        <button data-jq-dropdown="#jq-dropdown-1" class="topbutton">Extras</button>
        <div id="jq-dropdown-1" class="jq-dropdown jq-dropdown-tip">
            <ul class="jq-dropdown-menu">
                <?php include_once 'messageCurrentSelection.php'; ?>
                <li><button id="tabbutton" class ="topbutton extendbutton" value="cursellection">Current Selection</button></li>
                <?php
                if ($isAdmin == 1) {
                    echo "<li><button id='tabbutton' class ='topbutton extendbutton' value='checkobjects' >Check Objects</button></li>";
                }
                echo "<li><button id='tabbutton' value='usersamples-form' class='topbutton extendbutton'>User samples</button></li>";
                echo "<li><button id='showsmplcheck' value='showhide' class='topbutton extendbutton'>Toggle check boxes</button></li>";
                if ($setSelection->view == "table") {
                    echo "<li><button id='tabbutton' class ='topbutton extendbutton' value='exportdata'>Export Data</button></li>";
                }
                if ($isAdmin == 1) {
                    echo "<li><button id='tabbutton' class ='topbutton extendbutton' value='addnewobj' >Add New Object</button></li>";
                }
                if ($isAdmin == 1) {
                    echo "<li><button class='linkbutton topbutton extendbutton' name='view' value='plotDataPage'>Plot Data</button></li>";
                }
                ?>					

            </ul>
        </div>
        <button class="helpbutton topbutton" value="Hextras">?</button>
    </div>
</div>
</section>




<!-- end of secondary bar -->
<!--
                        <button id="tabbutton" class ="topbutton" value="cursellection" title="Current Selection"><i class="icon-filter"></i></button>
<?php if ($isAdmin == 1) echo "<button id='tabbutton' class ='topbutton' value='checkobjects' title='Check Objects'><i class='icon-timeline'></i></button>"; ?>
<?php echo "<button id='tabbutton' value='usersamples-form' class='topbutton' title='User samples'><i class='icon-userfilter'></i></button>"; ?>
<?php echo "<button id='showsmplcheck' value='showhide' class='topbutton' title='Show check boxes'><i class='icon-check'></i></button>"; ?>
<?php if ($setSelection->view == "table") echo "<button id='tabbutton' class ='topbutton' value='exportdata' title='Export Data'><i class='icon-exportfile'></i></button>"; ?>
<?php if ($isAdmin == 1) echo "<button id='tabbutton' class ='topbutton' value='addnewobj' title='Add New Object'><i class='icon-databaseadd'></i></button>"; ?>				
<?php if ($isAdmin == 1) echo "<button class='linkbutton topbutton' name='view' value='plotDataPage' title='Plot Data'><i class='icon-statistics'></i></button>"; ?>
                <div class="extras_container">
-->



