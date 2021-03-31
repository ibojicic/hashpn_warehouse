function alertMissingVals(fields)
{
    var goflag = true;
    for (id in fields) {
        if ($('#' + fields[id]).val().length == 0) {
            $('#' + fields[id]).css("border","1px solid red");
            goflag = false;
        } else {
            $('#' + fields[id]).css("border","1px solid #BBB");
        }
    }
    return goflag;
}

$(function() {

    $( ".inpdialog" ).dialog({
        autoOpen: false,
        modal: true,
        buttons: {
            "Submit": function() {
                $($(this).find("form")).submit();
                $( this ).dialog( "close" );
            },
            Cancel: function() {
                $( this ).dialog( "close" );
            }
        }
    });

    var height = $( ".tabdialog" ).dialog( "option", "height" );

    $( ".tabdialog" ).dialog({
        height: 'auto',
        width: 'auto',
        resizable: false,
        autoOpen: false,
        modal: true,
        buttons: [{
            text:"Submit",
            click: function() { 
                var goflag = true;
                switch($(this).attr("id"))
                {
                    case 'addnewobj':
                        goflag = alertMissingVals(['inpos','incoordref','incat']);
                    break;
                }
                if (goflag) {
                    $($(this).find("form")).submit();
                    $( this ).dialog( "close" );
                }
                }
            },{
            text:"Cancel",
            click: function() {
                $( this ).dialog( "close" );
            }
        }]
    });

    $( "button" ).button().click(function() {
        var specheight = 'auto';
        if ($(this).attr("id") == "tabbutton")
            {                
                $( "#" + $(this).val()).dialog( "open" );
                switch($(this).val())
                {
                    case 'searchfull-form':
                        specheight = 450;
                    break;
                    case 'showselect-form':
                        specheight = 300;
                    break;
                }
                $( "#" + $(this).val()  ).dialog( "option", "height", specheight );

            }
    });

    
    $( "button" ).button().click(function() {
        if ($(this).attr("id") == "editdata")
            {
                $( "#" + $(this).val()  ).dialog( "open" );
            }
    });


   $( ".mesgdialog" ).dialog({
        autoOpen: false,
        modal: true,
        height: 'auto',
        width: '500px',
        buttons: {
            'OK': function() {
                $( this ).dialog( "close" );
            }
        }
    });
    
});

/*
$(document).ready(function() {
    $(".accolist").click(function(e) {
        // unhighlight the previous menu selection
        $(".accolist .selected").removeClass("selected");
        // highlight the selected item & its parents
        $(e.target).closest("li").addClass("selected").parent().parent().addClass("selected");
    });
});
*/


$(document).ready(function(){
    $("input[name$='addorfull']").click(function() {
        var test = $(this).val();
        if (test=='fulldb') {
            $(".div-right :input").attr('disabled', true);
            $(".div-right").fadeTo(300, 0.5);
        }
        else if (test=='currsel') {
            $(".div-right :input").removeAttr('disabled');
            $(".div-right").fadeTo(300, 1);
        }
    });

});


$(document).ready(function(){
    $(".newusersample :input").attr('disabled', true);
    $(".newusersample").fadeTo(300, 0.3);
    $("input[name$='addelsel']").click(function() {
        var test = $(this).val();
        if (test=='del' || test=='delsample' || test=='add') {
            $(".newusersample :input").attr('disabled', true);
            $(".newusersample").fadeTo(300, 0.3);
            $(".oldusersample :input").removeAttr('disabled', true);
            $(".oldusersample").fadeTo(300, 1);
            if (test=='delsample') {
                $(".selectobjs :input").attr('disabled', true);
                $(".selectobjs").fadeTo(300, 0.3);
            } else {
                $(".selectobjs :input").removeAttr('disabled', true);
                $(".selectobjs").fadeTo(300, 1);
            }
        }
        else if (test=='addsample') {
            $(".newusersample :input").removeAttr('disabled');
            $(".newusersample").fadeTo(300, 1);
            $(".oldusersample :input").attr('disabled', true);
            $(".oldusersample").fadeTo(300, 0.3);
            $(".selectobjs :input").attr('disabled', true);
            $(".selectobjs").fadeTo(300, 0.3);

        }
    });
});

function recSelectedActionIDs() {
    var chkArray = [];

    $(".checkaction:checked").each(function () {
        chkArray.push($(this).val());
    });

    var checkedids;
    checkedids = chkArray.join(',') + ",";
    if (checkedids.length > 1) {
        $('input[name=checkedobjects]').val(checkedids);
    }


    
}
