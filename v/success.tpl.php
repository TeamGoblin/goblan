<?php global $success; ?>
<!-- dump success -->
<?php if (isset($success) && !empty($success)) { ?>
<div id="pageAlert" class="alert alert-block alert-success fade in"> 
	<a class="close" href="#" data-dismiss="alert" style="text-decoration: none;">&times;</a> 
	<h4 class="alert-heading">Congrats:</h4>
	<?php echo $success; ?>
</div>
<?php } ?>