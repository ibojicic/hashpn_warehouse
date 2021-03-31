<?php
	if (isset ($displayRes) and $displayRes != '')
	{
		echo "<script type='text/javascript' charset='utf-8' src='javascript/tableconstructor.js'></script>";
		echo "<article class='module width_full'>";
		echo "<div class='module_content'>";
		// ********** DISPLAY RESULTS ****************************************
		echo $displayRes;
		echo "</div>";
		echo "</article>"; // end of <article class="module width_full">
	}

?>