<div class="wrap woocommerce">
	<div id="icon-woocommerce" class="icon32 icon32-woocommerce-email"></div>
	<h2><?php esc_html_e( 'Add Notification', 'woocommerce-advanced-notifications' ); ?></h2>

	<form class="add" method="post">

		<h3><?php esc_html_e( 'Recipient', 'woocommerce-advanced-notifications' ); ?></h3>
		<p><?php esc_html_e( 'These fields determine who receives the notifications and are required.', 'woocommerce-advanced-notifications' ); ?></p>
		<table class="form-table">
			<tr>
				<th>
					<label for="recipient_name"><?php esc_html_e( 'Recipient name', 'woocommerce-advanced-notifications' ); ?></label>
				</th>
				<td>
					<input type="text" name="recipient_name" id="recipient_name" class="input-text regular-text" value="<?php echo esc_attr( $admin->field_value( 'recipient_name' ) ); ?>" />
					<p class="description"><?php esc_html_e( 'Enter the recipient name.', 'woocommerce-advanced-notifications' ); ?></p>
				</td>
			</tr>
			<tr>
				<th>
					<label for="recipient_email"><?php esc_html_e( 'Recipient email address(s)', 'woocommerce-advanced-notifications' ); ?></label>
				</th>
				<td>
					<input type="text" name="recipient_email" id="recipient_email" class="input-text regular-text" value="<?php echo esc_attr( $admin->field_value( 'recipient_email' ) ); ?>" />
					<p class="description"><?php esc_html_e( 'Enter the recipient(s) email address for this notification (comma separate for multiple addresses).', 'woocommerce-advanced-notifications' ); ?></p>
				</td>
			</tr>
		</table>

		<h3><?php esc_html_e( 'Recipient details', 'woocommerce-advanced-notifications' ); ?></h3>
		<p><?php esc_html_e( 'You can use these fields to store additional information about the recipient.', 'woocommerce-advanced-notifications' ); ?></p>
		<table class="form-table">
			<tr>
				<th>
					<label for="recipient_address"><?php esc_html_e( 'Address', 'woocommerce-advanced-notifications' ); ?></label>
				</th>
				<td>
					<textarea name="recipient_address" id="recipient_address" class="input-text regular-text" cols="25" rows="3" style="width: 25em;"><?php echo esc_textarea( $admin->field_value( 'recipient_address' ) ); ?></textarea>
				</td>
			</tr>
			<tr>
				<th>
					<label for="recipient_phone"><?php esc_html_e( 'Phone', 'woocommerce-advanced-notifications' ); ?></label>
				</th>
				<td>
					<input type="text" name="recipient_phone" id="recipient_phone" class="input-text regular-text" value="<?php echo esc_attr( $admin->field_value( 'recipient_phone' ) ); ?>" />
				</td>
			</tr>
			<tr>
				<th>
					<label for="recipient_website"><?php esc_html_e( 'Website', 'woocommerce-advanced-notifications' ); ?></label>
				</th>
				<td>
					<input type="text" name="recipient_website" id="recipient_website" class="input-text regular-text" placeholder="https://..." value="<?php echo esc_attr( $admin->field_value( 'recipient_website' ) ); ?>" />
				</td>
			</tr>
		</table>

		<h3><?php esc_html_e( 'Notifications', 'woocommerce-advanced-notifications' ); ?></h3>
		<p><?php esc_html_e( 'You can choose which recipients are received in this section.', 'woocommerce-advanced-notifications' ); ?></p>
		<table class="form-table">
			<tr>
				<th>
					<label for="notification_plain_text"><?php esc_html_e( 'Email format', 'woocommerce-advanced-notifications' ); ?></label>
				</th>
				<td>
					<label><input type="checkbox" name="notification_plain_text" value="1" id="notification_plain_text" class="input-checkbox" <?php checked( $admin->field_value( 'notification_plain_text' ), 1 ); ?> /> <?php esc_html_e( 'Plain text', 'woocommerce-advanced-notifications' ); ?></label>
					<p class="description"><?php esc_html_e( 'Enable to send in plain text rather than using the standard HTML email template.', 'woocommerce-advanced-notifications' ); ?></p>
				</td>
			</tr>
			<tr>
				<th>
					<label for="notification_type"><?php esc_html_e( 'Enable notifications', 'woocommerce-advanced-notifications' ); ?></label>
				</th>
				<td>
					<?php $type = (array) $admin->field_value( 'notification_type' ); ?>
					<select id="notification_type" name="notification_type[]" multiple="multiple" style="width:450px;" data-placeholder="<?php esc_attr_e( 'Choose types&hellip;', 'woocommerce-advanced-notifications' ); ?>" class="wc-enhanced-select">
						<option value="purchases" <?php selected( in_array( 'purchases', $type, true ), true ); ?>><?php esc_html_e( 'Purchases', 'woocommerce-advanced-notifications' ); ?></option>
						<option value="low_stock" <?php selected( in_array( 'low_stock', $type, true ), true ); ?>><?php esc_html_e( 'Low stock', 'woocommerce-advanced-notifications' ); ?></option>
						<option value="out_of_stock" <?php selected( in_array( 'out_of_stock', $type, true ), true ); ?>><?php esc_html_e( 'Out of stock', 'woocommerce-advanced-notifications' ); ?></option>
						<option value="backorders" <?php selected( in_array( 'backorders', $type, true ), true ); ?>><?php esc_html_e( 'Backorders', 'woocommerce-advanced-notifications' ); ?></option>
						<option value="refunds" <?php selected( in_array( 'refunds', $type, true ), true ); ?>><?php esc_html_e( 'Refunds', 'woocommerce-advanced-notifications' ); ?></option>
					</select>
					<p class="description"><?php esc_html_e( 'Define which notifications to enable.', 'woocommerce-advanced-notifications' ); ?></p>
				</td>
			</tr>
			<tr>
				<th>
					<label for="notification_triggers"><?php esc_html_e( 'Notification triggers', 'woocommerce-advanced-notifications' ); ?></label>
				</th>
				<td>
					<?php
						$_triggers = (array) $admin->field_value( 'notification_triggers' );
						$triggers  = array();

						foreach ( $_triggers as $key => $trigger ) {
							$triggers[] = str_replace( array( 'product_cat:', 'product_shipping_class:' ), '', str_replace( 'all', '0', $trigger ) );
						}
					?>
					<select id="notification_triggers" name="notification_triggers[]" multiple="multiple" style="width:450px;" data-placeholder="<?php esc_attr_e( 'Choose triggers&hellip;', 'woocommerce-advanced-notifications' ); ?>" class="wc-enhanced-select">
						<option value="all" <?php selected( in_array( '0', $triggers ), true ); ?>><?php esc_html_e( 'All purchases', 'woocommerce-advanced-notifications' ); ?></option>
						<optgroup label="<?php esc_attr_e( 'Product category notifications', 'woocommerce-advanced-notifications' ); ?>">
							<?php
								$terms = get_terms( 'product_cat', array( 'hide_empty' => 0 ) );

								foreach ( $terms as $term ) {
									echo '<option value="product_cat:' . esc_attr( $term->term_id ) . '" ' . selected( in_array( $term->term_id, $triggers ), true, false ) . '>' . esc_html__( 'Category:', 'woocommerce-advanced-notifications' ) . ' ' . esc_html( $term->name ) . '</option>';
								}
							?>
						</optgroup>
						<optgroup label="<?php esc_attr_e( 'Shipping class notifications', 'woocommerce-advanced-notifications' ); ?>">
							<?php
								$terms = get_terms( 'product_shipping_class', array( 'hide_empty' => 0 ) );

								foreach ( $terms as $term ) {
									echo '<option value="product_shipping_class:' . esc_attr( $term->term_id ) . '" ' . selected( in_array( $term->term_id, $triggers ), true, false ) . '>' . esc_html__( 'Class:', 'woocommerce-advanced-notifications' ) . ' ' . esc_html( $term->name ) . '</option>';
								}
							?>
						</optgroup>
					</select>
					<p class="description">
						<?php
							/* translators: %s Product edit URL. */
							printf( wp_kses_post( __( 'Here you can enable global or product notifications. Define recipients for categories and shipping classes here. You can define per-product notifications later by <a href="%s">editing a product</a>.', 'woocommerce-advanced-notifications' ) ), esc_url( admin_url( 'edit.php?post_type=product' ) ) );
						?>
					</p>
				</td>
			</tr>
			<tr>
				<th>
					<label for="notification_include_all_items"><?php esc_html_e( 'Include All Items', 'woocommerce-advanced-notifications' ); ?></label>
				</th>
				<td>
					<label><input type="checkbox" name="notification_include_all_items" value="1" id="notification_include_all_items" class="input-checkbox" <?php checked( $admin->field_value( 'notification_include_all_items' ), 1 ); ?> /> <?php esc_html_e( 'Include All Items', 'woocommerce-advanced-notifications' ); ?></label>
					<p class="description"><?php esc_html_e( 'Enable this to include all order items in the notification emails.', 'woocommerce-advanced-notifications' ); ?></p>
				</td>
			</tr>
			<tr>
				<th>
					<label for="notification_prices"><?php esc_html_e( 'Prices', 'woocommerce-advanced-notifications' ); ?></label>
				</th>
				<td>
					<label><input type="checkbox" name="notification_prices" value="1" id="notification_prices" class="input-checkbox" <?php checked( $admin->field_value( 'notification_prices' ), 1 ); ?> /> <?php esc_html_e( 'Include prices', 'woocommerce-advanced-notifications' ); ?></label>
					<p class="description"><?php esc_html_e( 'Enable this to include product prices in the notification emails.', 'woocommerce-advanced-notifications' ); ?></p>
				</td>
			</tr>
			<tr>
				<th>
					<label for="notification_totals"><?php esc_html_e( 'Totals', 'woocommerce-advanced-notifications' ); ?></label>
				</th>
				<td>
					<label><input type="checkbox" name="notification_totals" value="1" id="notification_totals" class="input-checkbox" <?php checked( $admin->field_value( 'notification_totals' ), 1 ); ?> /> <?php esc_html_e( 'Include order totals', 'woocommerce-advanced-notifications' ); ?></label>
					<p class="description"><?php esc_html_e( 'Enable this to include order totals in the notification emails.', 'woocommerce-advanced-notifications' ); ?></p>
				</td>
			</tr>
		</table>
		<p class="submit">
			<input type="submit" class="button button-primary" name="save_recipient" value="<?php esc_attr_e( 'Save changes', 'woocommerce-advanced-notifications' ); ?>" />
			<?php wp_nonce_field( 'woocommerce_save_recipient' ); ?>
		</p>

	</form>
</div>
