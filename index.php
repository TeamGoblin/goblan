<?php include_once('config.php'); ?>
<!DOCTYPE html>
<!--[if lt IE 9]><html class="ie"><![endif]-->
<!--[if gte IE 9]><!--><html><!--<![endif]-->
<?php include_once(base() . 'v/header.tpl.php'); ?>	
	<body>
		<div class="container">
			<?php if ($user->id) { include_once(base(). 'v/content_header.tpl.php'); }?>
			<div id="errors"><?php include_once(base() . 'v/error.tpl.php'); ?></div>
			<div id="infos"><?php include_once(base() . 'v/info.tpl.php'); ?></div>
			<div id="warnings"><?php include_once(base() . 'v/warning.tpl.php'); ?></div>
			<div id="successes"><?php include_once(base() . 'v/success.tpl.php'); ?></div>
			<?php echo $content; ?>
			<?php include_once(base() . 'v/footer.tpl.php'); ?>
		</div> <!-- end of container -->

		<!-- Load javascripts after content has loaded -->
		<?php include_once(base() . 'v/scripts.tpl.php'); ?>

	</body>
	
</html>