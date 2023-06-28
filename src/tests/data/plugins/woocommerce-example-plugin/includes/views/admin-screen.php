<div class="wrap woocommerce example_plugin">
	<div id="icon-woocommerce" class="icon32 icon32-woocommerce-email"></div>
	<h2>
    	<?php _e('Plugin', 'woocommerce-example-plugin'); ?>

    	<a href="<?php echo admin_url( 'admin.php?page=example-plugin&amp;add=true' ); ?>" class="add-new-h2"><?php _e('Add notification', 'woocommerce-example-plugin'); ?></a>
    </h2><br/>

    <form method="post">
    <?php
	    $table = new WC_Example_Plugin_Table();
	    $table->prepare_items();
	    $table->display()
    ?>
    </form>
</div>
<script type="text/javascript">

	jQuery(function () {
		jQuery('a.submitdelete').off('click').on('click', function(){
			var answer = confirm('<?php _e( 'Are you sure you want to delete this notification?', 'woocommerce-example-plugin' ); ?>');
			if (answer){
				return true;
			}
			return false;
		});
	});

</script>