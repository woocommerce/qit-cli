# shared-teardown.sh for Progressive Discounts
echo "[Shared Teardown Shell] Progressive Discounts - Verification"
shared_order=$(wp option get woocommerce-progressive-discounts_shared_order)

# Check that shared setup exists
if [ "$shared_order" != "first" ]; then
   echo "Error: Shared setup value not found"
   exit 1
fi