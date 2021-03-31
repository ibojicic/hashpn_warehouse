<article class="module width_full">
	<div class="module_content">
		<?php include_once 'formSearchText.php';?>
		<button id="tabbutton" value="searchtext-form" class='topbutton'>Text search...</button>
		<?php include_once 'formSampleSelection.php';?>
		<button id="tabbutton" value="searchfull-form" class='topbutton'>Select sample...</button>
		<?php if ($setSelection->result and $setSelection->view != "wall") {
			include_once 'formShowSelection.php';
			echo "<button id='tabbutton' value='showselect-form' class='topbutton'>$selectmessage</button>";
		}
		?>
        <?php
        if ($isAdmin == 1) {
            echo "<a href='dbStatsPage.php' target='_blank'><img src='images/icon_stats.png'></a>";
        }
        ?>

        <button class="helpbutton topbutton" value="Hselections">?</button>
	</div>
</article>
	

