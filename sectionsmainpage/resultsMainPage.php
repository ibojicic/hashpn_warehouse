	<article class='module width_full'>
	<button class="helpbutton topbutton righttop" value="Hresults">?</button>

		<div class='module_content'>
		<!--  ********** DISPLAY RESULTS **************************************** -->
		<script type='text/javascript' charset='utf-8' src='javascript/tableconstructor.js'></script>
		<?php echo $displayRes; ?>
		<!-- ********** DISPLAY PAGINATOR **************************************** -->
			<div id='paginator'>
				<?php
					if ($pages->items_total > 0) echo "<div style='float:left; width:100px;'>Records:".$pages->items_total."</div>";
					echo $pages->display_pages();
					echo "<div style='float:right;'><span class=\"\">".$pages->display_jump_menu().$pages->display_items_per_page()."</span></div>";
				?>
			</div>
		</div>
	</article> <!-- end of <article class="module width_full"> -->
	<script>$(".checkaction").on("click", recSelectedActionIDs );</script>
	
	
