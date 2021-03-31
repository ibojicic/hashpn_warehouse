<div id="wrapper register-form">
    <form name="register-form" class="login-form register-form" action="index.php?action=finreg" method="POST">
		<!--<input type="hidden" name="action" value="registration">-->
		<div class="header">
		<h2>Registration</h2>
                <p style="text-align: justify;">
                    To register please enter your details and click the "Register" button. 
                    You should receive an email with link to confirm your registration. 
                </p>
		</div>

		<div class="content">
                    <table style="margin-left: auto;margin-right: auto;">
                        <tr>
                            <td>Name</td>
                            <td><input name="Nname" id="Nname" type="text" class="input name" placeholder="Michael Jordan" autocomplete="off"/></td>
                            <td><span name ="Nname_icon" class="Nname_icon ui-icon ui-icon-closethick red"></span></td>
                        </tr>
                        <tr>
                            <td>Institution</td>
                            <td><input name="Iinstitution" type="text" class="input inst" placeholder="Chicago Bulls" autocomplete="off"/></td>
                        </tr>
                        <tr>
                            <td>Email</td>
                            <td><input name="Eemail" id="Eemail" type="text" class="input email" placeholder="mjordan@email.cpm" autocomplete="off"/></td>
                            <td><span name ="Eemail_icon" class="Eemail_icon ui-icon ui-icon-closethick red"></span></td>
                        </tr>
                        <tr>
                            <td>User name</td>
                            <td><input name="userName" id="newuserName" type="text" class="input username" placeholder="airjordan" autocomplete="off"/></td>
                            <td><span name ="userName_icon" class="userName_icon ui-icon ui-icon-closethick red"></span></td>
                        </tr>
                        <tr>
                            <td><div id="usnamemess"></div></td>
                        </tr>
                        <tr>
                            <td>Password</td>
                            <td><input name="userPass" id="userPass" type="password" class="input password" autocomplete="off"/></td>
                        </tr>
                        <tr>
                            <td>Repeat pass</td> 
                            <td><input name="repuserPass" id="repuserPass" type="password" class="input repassword"  autocomplete="off"/></td>
                            <td><span name ="repuserPass_icon" class="repuserPass_icon ui-icon ui-icon-closethick red"></span></td>                            
                        </tr>
                    </table>
        	</div>

		<div class="footer">

                    <input type="submit" name="submitnewuser" id="rregister" value="Register" class="submitnewuser button" disabled/>
                    <a href="index.php" style="color:red; margin-left: 50px;" />Login</a>
		</div>

	</form>
    
	<form name="changepass-form" class="login-form recpass" action="index.php?action=changepass" method="POST">
		<div class="header">
		<h2>Lost Your Password?</h2>
                <p style="text-align: justify;">
                    If you lost your password, please enter your email address below and click the "Change Password" button. 
                    You should receive an email with the temporary password. 
                </p>
		</div>

		<div class="content">
                   <input name="Eemail" id="Eemail" type="text" class="input email" placeholder="Email" autocomplete="off"/>
        	</div>

		<div class="footer">
                    <input type="submit" name="submitchangepass" id="rrecpass" value="Change Password" class="submitnewuser button" disable/>
		</div>

	</form>

</div>


