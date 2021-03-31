	<script type='text/javascript' charset='utf-8' src='javascript/tableconstructor.js'></script>
	<article class='module width_full'>
		<div class='module_content'>
			<h4 class='alert_error'>No results or error in query! Please <a href='dbMainPage.php'>go back</a> or <a href='dbMainPage.php?view=table&restart=RESTART'>restart</a> view...</h4>
			<h4 class='alert_error'>
				<?php 
					if (!(empty($selections['errormessage']))) echo implode("<br>",$selections['errormessage'])."</br>";
					echo "In Selected Samples: </br>".$setSelection->showSelectedSamples."</h4>";
					if ($isAdmin == 1) 	echo "<h4 class='alert_error'>MySQL query: <br> " . $setSelection->sqlSearchFull ." </h4>\n";
				?>
		</div>
	</article> <!-- end of <article class="module width_full"> -->
