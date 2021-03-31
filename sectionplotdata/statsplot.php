<script>
    $(function () {
        $("#datepickerstart").datepicker({
            minDate: "2014-09-01",
            maxDate: -1,
            dateFormat: "yy-mm-dd",
            defaultDate: "+1w",
            changeYear: true,
            changeMonth: true,
            numberOfMonths: 3,
            onClose: function (selectedDate) {
                $("#datepickerend").datepicker("option", "minDate", selectedDate);
            }
        });
        $("#datepickerend").datepicker({
            minDate: "2014-09-01",
            maxDate: 0,
            dateFormat: "yy-mm-dd",
            defaultDate: "+1w",
            changeYear: true,
            changeMonth: true,
            numberOfMonths: 3,
            onClose: function (selectedDate) {
                $("#datepickerstart").datepicker("option", "maxDate", selectedDate);
            }
        });
    });
</script>
<aside id="sidebar_object">
    <div class="module_content">
        <fieldset>
            <h2> Stats </h2>
            <form id ='form' action='dbStatsPage.php' method = 'GET'>
                <table>
                    <tr><td>User:</td><td><?php echo $links; ?></td></tr>
                    <tr><td>From:</td><td><input size="10" maxlength="10" type="text" id="datepickerstart" name='datestart' value='<?php echo $Stats->mindate; ?>' style="display: inline;" ></td></tr>
                    <tr><td>To:</td><td><input size="10" maxlength="10" type="text" id="datepickerend" name='dateend' value='<?php echo $Stats->maxdate; ?>' style="display: inline;"></td></tr>
                    <tr><td>Bin:</td><td><?php echo $Stats->binlist; ?></td></tr>
                    <tr><td></td><td><input type="submit" value="Submit"></td></tr>
                </table>        
            </form>
        </fieldset>
    </div>
</aside><!-- end of sidebar -->

<article class="module width_infocontainer noborder">
    <?php
    echo $plots["plots"];
    
    foreach ($plots["placeholders"] as $user => $data) {
        echo "<fieldset>";
        echo "<h1> $user </h1>";
        echo implode("", $data);
        echo "</fieldset>";
    }

    echo "<fieldset>";
    echo "<h1>Cronjob Que</h1>";
    echo $ques['jobs'];
    echo $ques['last'];
    echo "</fieldset>";
    ?>

</article>
