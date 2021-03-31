//check if username is avalilable
$(document).ready(function () {
    var flagusrname = false;
    var flagename = false;
    var flagemail = false;
    var flagequalpass = false;
    var flagchpass = false;

    $('form').on('keyup change',function (){
        var name = $("#Nname").val();
        var email = $("#Eemail").val();
        var oldpass = $("#userPass").val();
        var newpass = $("#repuserPass").val();
        var chpass = $("#chPass").val();
        var filter = /^[\w\-\.\+]+\@[a-zA-Z0-9\.\-]+\.[a-zA-z0-9]{2,4}$/;
        if (!(name == "" || typeof name === "undefined")) {
            flagename = true;
            $(".Nname_icon").removeClass('ui-icon-closethick red').addClass('ui-icon-check green');
        } else {
            flagename = false;
            $(".Nname_icon").removeClass('ui-icon-check green').addClass('ui-icon-closethick red');
        }
        if (filter.test(email) && !(email == ""  || typeof email === "undefined")) {
            flagemail = true;            
            $(".Eemail_icon").removeClass('ui-icon-closethick red').addClass('ui-icon-check green');
        }
        else { 
            flagemail = false;
            $(".Eemail_icon").removeClass('ui-icon-check green').addClass('ui-icon-closethick red');
        }
        
        if (!(oldpass == "" || typeof oldpass === "undefined" || oldpass !== newpass)) {
            flagequalpass = true;
            $(".repuserPass_icon").removeClass('ui-icon-closethick red').addClass('ui-icon-check green');
        } else {
            flagequalpass = false;
            $(".repuserPass_icon").removeClass('ui-icon-check green').addClass('ui-icon-closethick red');
        }
        
        if (chpass == "change") {
            flagchpass = true;
        }
        
        //if (flagename && flagemail && flagusrname && flagequalpass) $('input[name=submitnewuser]').attr('disabled', false);
       if (((flagename && flagemail && flagusrname) || flagchpass) && flagequalpass) $('input[id=rregister]').attr('disabled', false);

    });
    
    $("#newuserName").change(function () {
        $("#usnamemess").html("checking...");
        var username = $("#newuserName").val();
        $.ajax({
            type: "post",
            url: "adminpro/checkUsername.php",
            data: "username=" + username,
            success: function (data) {
                if (data == 0) {
                    $("#usnamemess").html("Username available");
                    flagusrname = true;
                    $(".userName_icon").removeClass('ui-icon-closethick red').addClass('ui-icon-check green');
                    if (flagename && flagemail && flagequalpass) $('input[name=submitnewuser]').attr('disabled', false);
                }
                else {
                    flagusrname = false;
                    $(".userName_icon").removeClass('ui-icon-check green').addClass('ui-icon-closethick red');
                    $("#usnamemess").html("Username already taken");
                    $('input[name=submitnewuser]').attr('disabled', true);
                }
            }
        });
    });
    
});