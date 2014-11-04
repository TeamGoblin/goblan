<?php global $warning; ?>
<!-- dump warning -->
<?php if (isset($warning) && !empty($warning)) { ?>
<div id="pageAlert" class="alert alert-block alert-warning fade in"> 
	<a class="close" href="#" data-dismiss="alert" style="text-decoration: none;">&times;</a> 
	<h4 class="alert-heading">Warning:</h4>
	<?php echo $warning; ?>
</div>
<?php } ?>