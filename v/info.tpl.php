<!-- dump info -->
<?php if (isset($info) && !empty($info)) { ?>
<div id="pageAlert" class="alert alert-block alert-info fade in"> 
	<a class="close" href="#" data-dismiss="alert" style="text-decoration: none;">&times;</a> 
	<h4 class="alert-heading">Heads up:</h4>
	<?php echo $info; ?>
</div>
<?php } ?>