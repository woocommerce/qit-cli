echo "[Isolated Setup Shell] Amazon S3 Storage"
shared_order=$(wp option get woocommerce-amazon-s3-storage_shared_order)
if [ "$shared_order" != "first" ]; then
   echo "Error: Shared setup did not run first"
   exit 1
fi
wp option update woocommerce-amazon-s3-storage_isolated_order "second"