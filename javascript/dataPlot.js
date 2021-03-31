
$(function ()
{

    // hard-code color indices to prevent them from shifting as
    // options are turned on/off
    if (typeof(file1) != "undefined" && file1 !== null)  {
    var datasets = jQuery.parseJSON(
        jQuery.ajax({
            url: file1,
            async: false,
            dataType: 'json'
        }).responseText
    );

    var newdata = [];
    var placeholder = $("#" + plch);
    var choiceContainer = $("#" + chCnr);

    if (datasets) {
    var i = 0;
    $.each(datasets, function(key, val) {
        val.color = i;
        ++i;
    });
    }

    // insert checkboxes
    var xaxisName = "X axis";
    var yaxisName = "Y axis";
    
    $.each(datasets, function(key, val) {
        xaxisName = val.xaxis;
        yaxisName = val.yaxis;
        
        choiceContainer.append(
        '<tr><td><input type="checkbox" name="' + key + '" id="id' + key + '" ' + val.checked + ' ></td>' +
        '<td>' + val.label + '</td>' +
        '<td>' + val.description + '</td>' +
        '<td>' + val.user + '</td>' +
        '<td>' + val.noPoints + '</td></tr>');
    });
    
    
    
    var options = {
        series: {lines: {show: false},points: {show: true,radius: 1.5, symbol: 'square'}},
        legend: {noColumns: 3},
        xaxis: {
            tickDecimals: 0,
            axisLabel: xaxisName,
            axisLabelUseCanvas: true,
            axisLabelPadding:20,
            min:minX,
            max:maxX
        },
        yaxis: {
            axisLabel: yaxisName,
            axisLabelUseCanvas: true,
            axisLabelPadding:12,
            min:minY,
            max:maxY
        },
        selection: {mode: "xy"},
        grid: {aboveData: false, color: null, hoverable: true, clickable: true}
    };

    function plotAccordingToChoices() {
        var data = [];

        choiceContainer.find("input:checked").each(function () {
            var key = $(this).attr("name");
            if (key && datasets[key])
                data.push(datasets[key]);
        });
        newdata = data;

        $.plot(placeholder,newdata,options)

    }

    choiceContainer.find("input").click(plotAccordingToChoices);


     placeholder.bind("plotselected", function (event, ranges) {
            plot = $.plot(placeholder, newdata,
                          $.extend(true, {}, options, {
                              xaxis: {min: ranges.xaxis.from, max: ranges.xaxis.to},
                              yaxis: {min: ranges.yaxis.from, max: ranges.yaxis.to}//,

                          }));
    });



    placeholder.bind("plotunselected", function (event) {
           plot = $.plot(placeholder, newdata, options)
    });
    
    placeholder.bind("plotclick", function (event, pos, item) {
        if (item) { 
            window.open("objectInfoPage.php?id=" + item.series.data[item.dataIndex][2]);
        }
    });

    function showTooltip(x, y, contents) {
        $('<div id=' + tltp + '>' + contents + '</div>').css( {
            position: 'absolute',
            display: 'none',
            top: y + 5,
            left: x + 5,
            border: '1px solid #fdd',
            padding: '2px',
            'background-color': '#fee',
            opacity: 0.80
        }).appendTo("body").fadeIn(200);
    }


    var previousPoint = null;

    $(placeholder).bind("plothover", function (event, pos, item) {

            if (item) {
                if (previousPoint != item.dataIndex) {
                    previousPoint = item.dataIndex;

                    $('#' + tltp).remove();

                    //alert(item.toSource())

                    var ionid = item.series.data[item.dataIndex][2];

                    var x = item.datapoint[0].toFixed(2),
                        y = item.datapoint[1].toFixed(2);


                    showTooltip(item.pageX, item.pageY,
                                item.series.label + ": id=" + ionid);
                }
            }
            else {
                $('#' + tltp).remove();
                previousPoint = null;
            }
    });


    plotAccordingToChoices();

    }

});
