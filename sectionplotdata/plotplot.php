<article class="module width_infocontainer noborder">
    <?php echo $plot['plots']; ?>
    <div id='sp_placeholder' class='spectra_plot'></div><br>
    <div id='spectrachoices'>
        <table id='plotchoices' class='<?php echo $mydbConfig['cssclasses']['splinerefs']; ?>'>
            <tr>
                <th></th>
                <th>Label</th>
                <th>Description</th>
                <th>User</th>
                <th>No data points</th>
            </tr>
        </table>
    </div>
</article>
