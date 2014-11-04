<?php global $error; ?>
<!-- dump errors -->
<?php if (isset($error) && !empty($error)) { ?>
<div id="pageAlert" class="alert alert-block alert-error fade in"> 
	<a class="close" href="#" data-dismiss="alert" style="text-decoration: none;">&times;</a> 
	<h4 class="alert-heading">Oh no!  Something went wrong:</h4>
	<?php echo $error; ?>
</div>
<?php } ?>