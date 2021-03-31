
$(function ()
{
    //$( "#tabs" ).tabs();

    var options = {
        series: {lines: {show: true, lineWidth: 1},points: {show: true,radius: 0.5}},
        legend: {noColumns: 3},
        xaxis: {tickDecimals: 0},
        yaxis: {min: 0},
        selection: {mode: "xy"},
        grid: {aboveData: false, color: null, hoverable: true, clickable: true},
    };


    // elcat plot
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
    $.each(datasets, function(key, val) {
        choiceContainer.append(
        '<tr><td><input type="checkbox" name="' + key + '" id="id' + key + '" ' + val.checked + ' ></td>' +
        '<td><label for="id' + key + '"></label></td>'+
        '<td>' + val.label + '</td>' +
        '<td><a href=\'' + val.link +  '\' target="_blank">' + val.ref + '</a></td>' +
        '<td>' + val.year + '</td>' +
        '<td>' + val.int_scale + '</td>' +
        '<td>' + val.extinction_applied + '</td>' +
        '<td>' + val.noLines + '</td></tr>');
    });

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
                              yaxis: {min: 0, max: ranges.yaxis.to}//,

                          }));
    });



    placeholder.bind("plotunselected", function (event) {
           plot = $.plot(placeholder, newdata, options)
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
                                item.series.label + ": " + x + "A => " + y + " (" + ionid + ")");
                }
            }
            else {
                $('#' + tltp).remove();
                previousPoint = null;
            }
    });

    plotAccordingToChoices();
    }


 // spectrum plot


 if (typeof(filesp) != "undefined" && filesp !== null) {

     var speclines = {
         "Hbeta"    : 4861,
         "[OIII]a"  : 4959,
         "[OIII]b"  : 5007,
         "Halpha"   : 6563,
         "HeII"     : 4686
     };

     var linetitles = {
         "H&beta;"  : 4861,
         "[OIII]"   : 4970,
         "H&alpha;" : 6563,
         "HeII"     : 4686
     };

     var optionssp = {
        series: {lines: {show: true, lineWidth: 0.5},points: {show: false}},
        legend: {noColumns: 3},
        xaxis: {tickDecimals: 0},
        /*yaxis: {min: 0},*/
        selection: {mode: "xy"}
    };
    
    var datasetssp = jQuery.parseJSON(
        jQuery.ajax({
            url: filesp,
            async: false,
            dataType: 'json'
        }).responseText
    );

    if (datasetssp) {
        var isp = 2;
    $.each(datasetssp, function(key, val) {
        val.color = isp;
        ++isp;
    });
    }
    var newdatasp = [];
    var placeholdersp = $("#" + plchsp);
    var choiceContainersp = $("#" + chCnrsp);
    var choiceLabels = $("#choiceLabels");
    
    $.each(datasetssp, function(key, val) {
        choiceContainersp.append(
        '<tr>' +
        '<td><input type="checkbox" name="' + key +'" checked="checked" id="id' + key + '"></td>' +
        '<td><label for="id' + key + '">' + val.label + '</label></td>'+
        '<td><a href=\'http://adsabs.harvard.edu/abs/' + val.ref +  '\' target="_blank">' + val.ref + '</a></td>' +
        '<td><a href=\'' + val.link +  '\'>' + val.file + '</a></td></tr>'
        //+ '<td>' + val.telescope + ' / ' + val.instrument + '</td>' +
        //'<td>' + val.dateobs + '</td></tr>'
        );
    });
    
    
    $.each(speclines, function(key, val) {
        choiceLabels.append(
        '<tr>' +
        '<td><input type="checkbox" name="' + key +'" checked="checked" id="id' + key + '"></td>' +
        '<td><label for="id' + key + '">' + val + '</label></td><tr>'
        );
    });
    

    function plotAccordingToChoicessp() {
        var data = [];
        choiceContainersp.find("input:checked").each(function () {
            var key = $(this).attr("name");

            if (key && datasetssp[key])
                data.push(datasetssp[key]);
        });
        newdatasp = data;

        plot = $.plot(placeholdersp,newdatasp,optionssp);

            var axes = plot.getAxes();

            var starty = axes.yaxis.max;
            var stopy = axes.yaxis.max - (axes.yaxis.max - axes.yaxis.min) / 10.;

            var markings = [];

            $.each(speclines, function(key,val) {
                markings.push({ color: "#000", lineWidth: 1, xaxis: { from: val, to: val }, yaxis: { from: stopy, to: starty }});
            });

            plot = $.plot(placeholdersp, newdatasp,
                          $.extend(true, {}, optionssp, {
                              grid: {aboveData: false, color: null, hoverable: false, clickable: false, markings: markings}}
                          ));

            $.each(linetitles, function(key,val) {
                o = plot.pointOffset({x: val, y: starty});
                placeholdersp.append("<div style='position:absolute;left:" + (o.left - 7) + "px;top:" + (o.top - 15) + "px;color:#666;font-size:xx-small'>" + key + "</div>");
            });

    }
    
    choiceContainersp.find("input").click(plotAccordingToChoicessp);


    placeholdersp.bind("plotselected", function (event, ranges) {
            var starty = ranges.yaxis.to;
            var stopy = ranges.yaxis.to - (ranges.yaxis.to - ranges.yaxis.from) / 10.;
            var markings = [];
            
            $.each(speclines, function(key,val) {
                markings.push({ color: "#000", lineWidth: 1, xaxis: { from: val, to: val }, yaxis: { from: stopy, to: starty }});
            });

            plot = $.plot(placeholdersp, newdatasp,
                          $.extend(true, {}, optionssp, {
                              grid: {aboveData: false, color: null, hoverable: false, clickable: false, markings: markings}},{
                              xaxis: {min: ranges.xaxis.from, max: ranges.xaxis.to},
                              yaxis: {min: ranges.yaxis.from, max: ranges.yaxis.to}
                          }));

            $.each(linetitles, function(key,val) {
                o = plot.pointOffset({x: val, y: starty});
                placeholdersp.append("<div style='position:absolute;left:" + (o.left - 7) + "px;top:" + (o.top - 15) + "px;color:#666;font-size:smaller'>" + key + "</div>");
            });
    });


    placeholdersp.bind("plotunselected", function (event) {
           plot = $.plot(placeholdersp, newdatasp, optionssp);
           var axes = plot.getAxes();

            var starty = axes.yaxis.max;
            var stopy = axes.yaxis.max - (axes.yaxis.max - axes.yaxis.min) / 10.;

            var markings = [];

            $.each(speclines, function(key,val) {
                markings.push({ color: "#000", lineWidth: 1, xaxis: { from: val, to: val }, yaxis: { from: stopy, to: starty }});
            });

            plot = $.plot(placeholdersp, newdatasp,
                          $.extend(true, {}, optionssp, {
                              grid: {aboveData: false, color: null, hoverable: false, clickable: false, markings: markings}}
                          ));

            $.each(linetitles, function(key,val) {
                o = plot.pointOffset({x: val, y: starty});
                placeholdersp.append("<div style='position:absolute;left:" + (o.left - 7) + "px;top:" + (o.top - 15) + "px;color:#666;font-size:smaller'>" + key + "</div>");
            });

    });

    plotAccordingToChoicessp();
    }

});


