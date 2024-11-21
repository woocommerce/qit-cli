#!/bin/bash
# shared-setup.sh
echo "[Shared Setup Shell] Amazon S3 Storage"
wp option update "woocommerce-amazon-s3-storage_shared_order" "first"
wp option get "woocommerce-amazon-s3-storage_shared_order"