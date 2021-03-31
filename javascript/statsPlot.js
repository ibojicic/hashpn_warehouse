
$(function ()
{
    var options = {
        series: {bars: {show: true},points: {show: true,radius: 1.5, symbol: 'square'}},
        legend: {noColumns: 3},
        xaxis: {
            tickDecimals: 0,
            axisLabel: 't',
            axisLabelUseCanvas: true,
            axisLabelPadding:10,
            mode: "time",
            timeformat: "%y/%m/%d"
        },
        yaxis: {
            axisLabel: 'hits',
            axisLabelUseCanvas: true,
            axisLabelPadding:10
            
        },
        selection: {mode: "xy"},
        grid: {aboveData: false, color: null, hoverable: true, clickable: true}
    };

    
    $.each(inputs, function(key, val) {
        var filestat = val.filename;
        var plch = val.plholder;
        
        if (typeof(filestat) !== "undefined" && filestat !== null)  {
            var input = jQuery.ajax({
                url: filestat,
                async: false,
                dataType: 'json'
            }).responseText;            
            var datasets = jQuery.parseJSON(input);
        }
        var placeholder = $("#" + plch);
        $.plot(placeholder,datasets,options);
    });

});