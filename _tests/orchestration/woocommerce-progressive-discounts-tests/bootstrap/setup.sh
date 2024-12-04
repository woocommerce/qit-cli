# setup.sh for both plugins
echo "[Isolated Setup Shell] Progressive Discounts"
shared_order=$(wp option get woocommerce-progressive-discounts_shared_order)
if [ "$shared_order" != "first" ]; then
    echo "Error: Shared setup did not run first"
    exit 1
fi
wp option update woocommerce-progressive-discounts_isolated_order "second"