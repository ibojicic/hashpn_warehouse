    $(function(){
        $('.column').equalHeight();
    });
    
    function pushvalue(data,inpname) {
        document.getElementById(inpname).value += data.value;
    }
    $(function () {
        $('.checkall').on('click', function () {
            $(this).closest('div').find(':checkbox').prop('checked', this.checked);
        });
    });
    
    $(function () {
        $(".accolist").accordion({
            collapsible: true,
            autoHeight: false,
            navigation: true,
            active: false
        });
    });
    
    $(function() {
        $(".helpbutton").click(function() {
            window.open("dbHelpPage.php?#" + $(this).val());
        });
    });
    
    $(function () {
        $(".viewbutton").click(function () {
            var viewinput = "<form action='dbMainPage.php' method='POST'><input type='hidden' name='" + $(this).attr('name') + "' value= '" + $(this).val() + "' /></form>";
            $(viewinput).appendTo('body').submit();
        });
    });
    
    $(function () {
        $(".linkbutton").click(function () {
            window.open($(this).val() + ".php");
        });
    });
        	
    $(document).ready(function () {
        $('#showsmplcheck').click(function () {
            $('.actionclass').toggle();
        });
    });
    
    /* show hide page ellements depending on the page size*/
    $(document).ready(function() {
    // This will fire when document is ready:
    $(window).resize(function() {
        // This will fire each time the window is resized:
        if($(window).width() >= 1240) {
            // if larger or equal
            $('#bigextras').show();
            $('#smallextras').hide();
        } else {
            // if smaller
            $('#bigextras').hide();            
            $('#smallextras').show();            
            if ($(window).width() < 900) {
                $('#logo').hide();
            } else {
                $('#logo').show();                
            }
        }
    }).resize(); // This will simulate a resize to trigger the initial run.
});

    /*
    jQuery(document).ready(function($) {
        $("#p-list a.cat").next().hide();
        $("#p-list a.cat").click(function() { $(this).next().slideToggle(); });
    });
    */
    
    /*
    function reloadPage() {
        location.reload();
    }
    */
   



	

	

	

  
   



