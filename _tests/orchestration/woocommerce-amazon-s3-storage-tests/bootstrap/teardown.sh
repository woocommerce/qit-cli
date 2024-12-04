# Amazon S3 teardown.sh
echo "[Isolated Teardown Shell] Amazon S3 Storage"
# Verify shared order exists before setting teardown
shared_order=$(wp option get woocommerce-amazon-s3-storage_shared_order)
if [ "$shared_order" != "first" ]; then
   echo "Error: Shared order missing before teardown"
   exit 1
fi

wp option update "woocommerce-amazon-s3-storage_teardown_order" "third"