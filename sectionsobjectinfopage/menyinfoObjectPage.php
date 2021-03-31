<aside id="sidebar_object">
	<div id='infodiv'>
		<div id='headertable_info'>
			<?php echo $headerTable; ?>
		</div>
		<hr>
		<div id='coordstable_info'>
                        <h4>Centroid Coords</h4>
			<?php echo $coordsTable; ?>
		</div>		
                <hr>
                <?php if ($CScoordsTable) {
                        echo "<div id='coordstable_info'>\n";
                        echo "<h4>Central Star Coords</h4>\n";
                        echo $CScoordsTable;
                        echo "</div>\n";
                        echo "<hr>\n";
                    }
                ?>
		<div id='object_links'>
			<?php echo $objLinks; ?>
		</div>
		<hr>
		<?php echo $extlinks; ?>
	</div>

</aside><!-- end of sidebar -->
