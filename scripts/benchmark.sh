#!/bin/bash

# URL of the WordPress package
WP_URL="https://wordpress.org/wordpress-6.4.3.tar.gz"

# Temporary directory for downloading
TMP_DIR="/tmp/wp-download"

# Target directory for extraction
TARGET_DIR="/var/www/html"

# Create temporary and target directories if they don't exist
mkdir -p "$TMP_DIR"
mkdir -p "$TARGET_DIR"

# Download the WordPress package
wget -O "$TMP_DIR/wordpress.tar.gz" "$WP_URL"

# Extract the package to the target directory
tar -xzf "$TMP_DIR/wordpress.tar.gz" -C "$TARGET_DIR" --strip-components=1 --no-same-owner

# Clean up the temporary file
rm -f "$TMP_DIR/wordpress.tar.gz"

# Optional: Remove temporary directory
# rm -rf "$TMP_DIR"

echo "WordPress downloaded and extracted to $TARGET_DIR"