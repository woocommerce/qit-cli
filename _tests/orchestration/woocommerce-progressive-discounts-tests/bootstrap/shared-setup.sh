# shared-setup.sh
echo "[Shared Setup Shell] Progressive Discounts"
wp option update "woocommerce-progressive-discounts_shared_order" "first"
wp option get "woocommerce-progressive-discounts_shared_order"