<?php global $l; ?>
<div class="row">
	<div class="col-lg-4 col-lg-offset-4 logo">
		<a href="/"><img src="/i/img/logo@2x.png" class="logoImg" /></a>
	</div>
</div>
<div class="row" style="padding-top: 10px;">
	<div class="col-lg-4 col-lg-offset-4">
		<div class="card-form">
			<div id="login">
				<form action="/user/login" method="post">
				<span class="title">Login to your account</span>
				<input id="loginEmail" name="email" type="text" placeholder="email" class="width" />
				<input id="loginPassword" name="password" type="password" placeholder="password" class="width" />
				<label class="checkbox inline" for="keep">
					<input type="checkbox" value="t" id="keep" name="keep" data-toggle="checkbox">&nbsp;
					Remember me
				</label>
				<a id="forgotLink" href="#forgot">Forgot Password</a>
				<button class="btn width btn-success login-btn">Let's Go</button>
				</form>
				<div class="form_base"><a id="registerLink" href="/register">Need an account?</a></div>
			</div><!-- /login -->

			<div id="forgot">
				<form action="/user/forgot/email" method="post">
				<span class="title">Reset your password</span>
				<input id="forgotEmail" name="email" type="text" placeholder="email" class="width" />
				<button class="btn width btn-success login-btn">Reset Password</button>
				</form>
				<div class="form_base"><a id="loginLink" href="#login">Remembered password?</a></div>
			</div><!-- /forgot -->
		</div>
	</div>
</div> <!-- end of row -->