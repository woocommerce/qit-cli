# teardown.sh
echo "[Isolated Teardown Shell] Progressive Discounts" # or Amazon S3
# Verify shared order exists before setting teardown
shared_order=$(wp option get woocommerce-progressive-discounts_shared_order)
if [ "$shared_order" != "first" ]; then
   echo "Error: Shared order missing before teardown"
   exit 1
fi

wp option update "woocommerce-progressive-discounts_teardown_order" "third"