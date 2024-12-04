# Amazon S3 shared-teardown.sh
echo "[Shared Teardown Shell] Amazon S3 Storage - Verification"
shared_order=$(wp option get "woocommerce-amazon-s3-storage_shared_order")

# Check that shared setup exists AND ONLY shared setup
if [ "$shared_order" != "first" ]; then
    echo "Error: Missing or incorrect option 'woocommerce-amazon-s3-storage_shared_order' before cleanup"
    exit 1
fi

# Try to get isolated order - should fail
if wp option get "woocommerce-amazon-s3-storage_isolated_order" &> /dev/null; then
    echo "Error: Isolated option still exists when it should have been cleaned up"
    exit 1
fi

# Try to get teardown order - should fail
if wp option get "woocommerce-amazon-s3-storage_teardown_order" &> /dev/null; then
    echo "Error: Teardown option still exists when it should have been cleaned up"
    exit 1
fi