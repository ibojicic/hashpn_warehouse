
<section id="objectinfo">
        <article class="module width_infocontainer noborder">

            <!--**************** DISPLAY NOTIFICATIONS **********************-->
            <?php 
                if ($EditRecs->runresponse) {
                    foreach ($EditRecs->runresponse as $notification) echo $notification;
                }
            ?>
            <!--**************** DISPLAY NOTIFICATIONS **********************-->

		<div id="tabs" class = " <?php echo $pageName;?>" style="display: none;">
					<ul>
						<li><a href="#tab1">Gallery</a></li>
						<li><a href="#tab2">Fits  Files</a></li>
						<?php if ($plots or $splinkstable) echo "<li><a href='#tab3'>Spectra</a></li>"; ?>
						<li><a href="#tab4">Notes</a></li>
						<?php if ($isAdmin == 1 or ($EditRecs and $EditRecs->owner)) echo "<li><a href='#tab6'>Basic Data</a></li>"; ?>
						<li><a href="#tab7">General Data</a></li>
						<?php if ($isAdmin == 1 ) echo "<li><a href='#tab8'>Full Data</a></li>"; ?>
					</ul>

	
			<div id="tab1" class="tab_content">
				<button class="helpbutton topbutton righttop" value="Hgallery">?</button>
				<div class='tabcontainer'>
				<?php
				if ($galeryBox) {
					echo "<script type='text/javascript' src='javascript/jquerythings.js'></script>";
					echo $galeryBox;
				} else {
					echo "No image currently available...";
				}
				?>
				</div>
			</div>

			<div id="tab2" class="tab_content">
				<button id='buttab2' class="helpbutton topbutton righttop" value="Hfitslinks">?</button>
				<div class='tabcontainer'>
					<?php echo $fitslinks; ?>
				</div>
			</div>
			
			<?php if ($plots or $splinkstable)
			{
				echo "<div id='tab3' class='tab_content'>";
				
				echo "<button id='buttab3' class='helpbutton topbutton righttop' value='Hspectra'>?</button>";
				echo "<div class='tabcontainer'>";
				echo $plots['plots'];

				if (isset ($plots['flags']['spctr']) and $plots['flags']['spctr']) echo "
													<div>
                                                                                                            <h5>1D Spectra</h5>
                                                                                                            <div id='sp_placeholdersp' class='spectra_plot'></div><br>
                                                                                                            $deflines
                                                                                                        <div id='spectrachoices'>
                                                                                                            <table id='choicessp' class='".$mydbConfig['cssclasses']['splinerefs']."'>
														<tr><th></th><th>No</th><th>Reference</th><th>Fits</th><th>Tel/Inst</th><th>ObsDate</th><th>Range(A)</th><th>rebin</th></tr>
                                                                                                            </table>
                                                                                                        </div>
													</div>";
				
				
				if (isset ($plots['flags']['relative']) and $plots['flags']['relative']) echo "
                                                                                                        <hr>
													<div>
                                                                                                            <h5>eELCAT Spectra</h5>
                                                                                                            <div id='sp_placeholder' class='spectra_plot'></div><br>
                                                                                                                <div id='spectrachoices'>
                                                                                                                    <table id='choices' class='".$mydbConfig['cssclasses']['splinerefs']."'>
															<tr><th></th><th></th><th>No</th><th>Reference</th><th>Year</th><th>Scale</th><th>Ext.</th><th>No</th></tr>
                                                                                                                    </table>
                                                                                                                </div>
													</div>";
				if ($splinkstable) echo "
                                    <hr>
                                    <div>
                                        <h5>Literature Spectra</h5>
                                        <div id='sp_placeholder'>".$splinkstable."</div>
                                    </div>";
				echo "</div>";
			echo "</div>";
	
				 
			}
			?>

			<div id="tab4" class="tab_content">
				<button id='buttab4' class="helpbutton topbutton righttop" value="Hnotes">?</button>
				<div class='tabcontainer'>
				<?php if ($tabNotes != "") echo $tabNotes; ?>
				</div>
			</div>
			
			<?php if ($isAdmin == 1 or ($EditRecs and $EditRecs->owner)) 
			{
				echo "<div id='tab6' class='tab_content'>";
				echo "<button id='buttab6' class='helpbutton topbutton righttop' value='Hbasdata'>?</button>";
				echo "<div class='tabcontainer'>";				
				echo $fullinfo['maindata'];
				echo "</div>"; 
				echo "</div>";
			}
			?>

			<div id="tab7" class="tab_content">
				<button id='buttab7' class="helpbutton topbutton righttop" value="Hgendata">?</button>
				<!--<div class='tabcontainer'>-->
					<?php echo $fullinfo['gendata']; ?>
				<!--</div>-->
			</div>
                        
                        <?php if ($isAdmin == 1 and $fullinfo['fulldata']) {
                            echo "<div id='tab8' class='tab_content'>";
                            echo "<button id='buttab8' class='helpbutton topbutton righttop' value='Hfulldata'>?</button>";
                            echo $fullinfo['fulldata'];
                            echo "</div>";
                        }
                        ?>


		</div>
		<script type="text/javascript">
			$(function() {
				$("#tabs").tabs();
				$("#tabs").css('display', 'block');
			});
                </script>
	</article>
</section>
