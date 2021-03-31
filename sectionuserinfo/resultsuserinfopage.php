
<section id="objectinfo">

	<article class="module width_infocontainer noborder">
            <div id="usertable_info" >
            <h1>username: <?php echo $curUser;?></h1>
            <h1>name: <?php echo $userdata[$curUser]['firstName']." ".$userdata[$curUser]['lastName'];?></h1>
            <h1>email: <?php echo $userdata[$curUser]['email'];?></h1>
            <h1>affiliation: <?php echo $userdata[$curUser]['affiliation'];?></h1>
            <h1>status: <?php echo $userdata[$curUser]['isAdmin'] == 1 ? "admin" : "user";?></h1>
            <h2>change password:</h2>
            <form id="changeuserdetails" action="dbMainPage.php?action=changedetails" method="POST">
                <input type="hidden" id="Nname" name="Nname" value="<?php echo $curUser; ?>">
                <input type="hidden" id="Eemail" name="Eemail" value="<?php echo $userdata[$curUser]['email']; ?>">
                <input type="hidden" name="chmit" value="<?php echo $userdata[$curUser]['email']; ?>">                
                <input type="hidden" id="chPass" value="change">

                <table>
                    <tr>
                        <th>New Password</th><td><input type="password" name="userPass" id="userPass" class="input password" autocomplete="off"/></td>
                    </tr>                    
                    <tr>
                        <th>Repeat New Password</th><td><input type="password" name="repuserPass" id="repuserPass" class="input repassword"  autocomplete="off"/></td>
                        <td><span name ="repuserPass_icon" class="repuserPass_icon ui-icon ui-icon-closethick red"></span></td>                            
                    </tr>
                    <tr>
                        <td></td><td><input type="submit" name="changesubmit" id="rregister" value="Submit Changes" disabled/></td>
                    </tr>
                </table>
                <!--name="changesubmit"<input type="submit" id="rregister" name="submitnewuser" value="Submit Changes" >-->

            </form>
            </div>

	</article>
</section>
