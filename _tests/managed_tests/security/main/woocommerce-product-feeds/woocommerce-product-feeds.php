<?php

/*
 * Plugin name: Security - Plugin A
 * Version: 0.1-test-version
 */

add_action( 'init', static function() {
	if ( isset( $_POST['foo'] ) ) {
		$foo = $_POST['foo']; // Detected usage of a non-sanitized input variable: $_POST['foo']
		$bar = $_POST['bar']; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		echo "Unescaped output! $foo"; // All output should be run through an escaping function
		echo "Unescaped output! $bar"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		wp_set_auth_cookie( 1 ); // Detected usage of a potentially unsafe function.
		wp_set_current_user( 1 ); // Detected usage of a potentially unsafe function.

		throw new \Exception( "Unescaped Exception with $foo" ); // Unescaped exception ignored.
	}
} );

add_filter( 'determine_user', 'callable' ); // Risky filter warning.

// Prepared using "IN" correctly (not flagged)
$results = $wpdb->get_results( $wpdb->prepare(
	"SELECT * FROM [TABLENAME] 
	WHERE mock_key = %s
	AND status IN ( %s )
	LIMIT 100",
	implode( ', ', array_fill( 0, count( $statuses ), '%s' ) ),
	array_merge( array( 'value_for_mock_key' ), $statuses )
) );

// Prepared using "IN" correctly (flagged)
$sql = $wpdb->prepare(
	"SELECT * FROM [TABLENAME] 
	WHERE mock_key = %s
	AND status IN ( %s )
	LIMIT 100",
	implode( ', ', array_fill( 0, count( $statuses ), '%s' ) ),
	array_merge( array( 'value_for_mock_key' ), $statuses )
);
$results = $wpdb->get_results( $sql );

// Prepared using "IN" with dynamic variable (flagged)
$status_placeholders = implode( ', ', array_fill( 0, count( $statuses ), '%s' ) );
$sql = $wpdb->prepare(
	"SELECT * FROM [TABLENAME] 
	WHERE mock_key = %s
	AND status IN ( $status_placeholders )
	LIMIT 100",
	array_merge( array( 'value_for_mock_key' ), $statuses )
);
$results = $wpdb->get_results( $sql );

// Scenario 2 (Flagged)
$placeholder_items = implode(', ', array_fill(0, count($filtered_items), '%d'));
$condition_placeholders = implode(', ', array_fill(0, count($filtered_statuses), '%s'));
if ('item_calc' === $calculation_method) {
	$scheduled_dates = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT DATE_FORMAT(event_date, '%%Y%%m%%d') as formatted_date, COUNT(event_date) as total FROM
            {$wpdb->prefix}custom_table ct, " . esc_sql($database_table) . ' tbl
            WHERE
            ct.item_id = tbl.ID
            AND tbl.' . esc_sql($status_field) . " not in ($condition_placeholders)
            AND NOT (member_id = %s AND is_processed = 1)
            AND ct.item_id NOT IN ($placeholder_items)
            AND event_date >= %s GROUP BY formatted_date ORDER BY formatted_date",
			array_merge(
				array(
					$custom_object->member_id,
					current_time('Y-m-d 00:00:00'),
				),
				$filtered_statuses,
				$filtered_items
			)
		)
	);
}

// Scenario 3 (Not Flagged)
if ('item_calc' === $calculation_method) {
	$scheduled_dates = $wpdb->get_results(
		$wpdb->prepare(
			sprintf(
				"SELECT DATE_FORMAT(event_date, '%%Y%%m%%d') as formatted_date, COUNT(event_date) as total FROM
                {$wpdb->prefix}custom_table ct, " . esc_sql($database_table) . ' tbl
                WHERE
                ct.item_id = tbl.ID
                AND tbl.' . esc_sql($status_field) . " not in (%s)
                AND NOT (member_id = %s AND is_processed = 1)
                AND ct.item_id NOT IN (%s)
                AND event_date >= %s GROUP BY formatted_date ORDER BY formatted_date",
				implode(', ', array_fill(0, count($filtered_statuses), '%s')),
				implode(', ', array_fill(0, count($filtered_items), '%d'))
			),
			array_merge(
				array(
					$custom_object->member_id,
					current_time('Y-m-d 00:00:00'),
				),
				$filtered_statuses,
				$filtered_items
			)
		)
	);
}