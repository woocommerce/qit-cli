#!/bin/bash

# Exit immediately if a command exits with a non-zero status.
set -e

echo "Running Composer install..."
composer install --working-dir=src

echo "Enabling dev mode..."
php src/qit-cli.php dev

echo "Connecting to Staging QIT..."
php src/qit-cli.php backend:add --environment="staging" --qit_secret="$1" --manager_url="$2"

echo "Adding 'qit.test' to hosts file..."
echo "127.0.0.1 qit.test" | sudo tee -a /etc/hosts
