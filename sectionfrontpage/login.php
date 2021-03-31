<div id="wrapper">

	<form name="login-form" class="login-form" action="<?php echo $linkurl; ?>" method="POST">
		<input type="hidden" name="action" value="login">
		<input type="hidden" name="userRemember" value="yes">
		<div class="header">
		<h1>#PN Login</h1>
		</div>

		<div class="content">
		<input name="userName" type="text" class="input username" placeholder="Username" />
		<input name="userPass" type="password" class="input password" placeholder="Password" />
		</div>

		<div class="footer">
		<input type="submit" name="submit" value="Login" class="button" />
		<a href="regPage.php?action=newreg" style="color:red; margin-left: 50px;" />Register</a>
		</div>

	</form>

</div>
