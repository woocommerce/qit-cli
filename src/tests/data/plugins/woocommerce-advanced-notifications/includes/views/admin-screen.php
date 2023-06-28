<div class="wrap woocommerce advanced_notifications">
	<div id="icon-woocommerce" class="icon32 icon32-woocommerce-email"></div>
	<h2>
    	<?php esc_html_e( 'Notifications', 'woocommerce-advanced-notifications' ); ?>

    	<a href="<?php echo esc_url( admin_url( 'admin.php?page=advanced-notifications&amp;add=true' ) ); ?>" class="add-new-h2"><?php esc_html_e('Add notification', 'woocommerce-advanced-notifications'); ?></a>
    </h2><br/>

    <form method="post">
    <?php
	    $table = new WC_Advanced_Notifications_Table();
	    $table->prepare_items();
	    $table->display()
    ?>
    </form>
</div>
<script type="text/javascript">

	jQuery(function () {
		jQuery('a.submitdelete').off('click').on('click', function(){
			var answer = confirm('<?php esc_html_e( 'Are you sure you want to delete this notification?', 'woocommerce-advanced-notifications' ); ?>');
			if (answer){
				return true;
			}
			return false;
		});
	});

</script>