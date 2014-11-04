<?php include_once('config.php'); ?>
<!DOCTYPE html>
<!--[if lt IE 9]><html class="ie"><![endif]-->
<!--[if gte IE 9]><!--><html><!--<![endif]-->
<?php include_once(base() . 'v/header.tpl.php'); ?>	
	<body>
		<div class="container">
			<div id="errors"><?php include_once(base() . 'v/error.tpl.php'); ?></div>
			<div id="infos"><?php include_once(base() . 'v/info.tpl.php'); ?></div>
			<div id="warnings"><?php include_once(base() . 'v/warning.tpl.php'); ?></div>
			<div id="successes"><?php include_once(base() . 'v/success.tpl.php'); ?></div>
			<div class="row" id="stuck">
				<form method="post" action="/">
				<div class="col-lg-10 col-lg-offset-1">
					<div class="form-inline center">

						<input id="name" name="name" type="text" placeholder="full name" class="width" />
						<input id="alias" name="alias" type="text" placeholder="alias" class="width" />
						<input id="email" name="email" type="email" placeholder="email" class="width" />
						<button class="btn width btn-success login-btn" type='submit'>Register</button>
					</div>
				</div>
				</form>
			</div>
			<div class="row">
				<div class="col-lg-8 col-lg-offset-2 center">
					<a href="/"><img src="/i/img/logo.png" class="logoImg" /></a><br/>
					<h1>GobLAN 2014</h1><h2>December 19th - 21st</h2>
				</div>
			</div>
			<?php echo $content; ?>
			<?php include_once(base() . 'v/footer.tpl.php'); ?>
		</div> <!-- end of container -->

		<!-- Load javascripts after content has loaded -->
		<?php include_once(base() . 'v/scripts.tpl.php'); ?>

	</body>
	
</html>