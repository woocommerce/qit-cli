#!/bin/bash

# Exit immediately if a command exits with a non-zero status.
set -e

# Start environment
echo "Starting environment..."
php src/qit-cli.php env:up

# Check if the site is up
SITE_URL=$(php src/qit-cli.php env:list --field=site_url)
echo "Checking if the site is up at $SITE_URL..."
if [ "$(curl -o /dev/null -s -w '%{http_code}\n' $SITE_URL)" -ne 200 ]; then
    echo "Home page is not up."
    exit 1
fi
if [ "$(curl -s $SITE_URL/wp-json | jq -r '.name')" != "WooCommerce Core E2E Test Suite" ]; then
    echo "Name property does not match."
    exit 1
fi
echo "Site is up and running."

# Assert that Docker Containers are all running
echo "Checking if all Docker containers are running..."
CONTAINERS=$(php src/qit-cli.php env:list --field=docker_images)
for container in $CONTAINERS; do
    echo "Checking container: $container"
    if [ "$(docker inspect -f '{{.State.Running}}' $container)" != "true" ]; then
        echo "Container $container is not running."
        exit 1
    fi
done
echo "All containers are running successfully."

# Assert that Docker Network was created
echo "Verifying Docker network creation..."
ENV_ID=$(php src/qit-cli.php env:list --field=env_id)
if [ -z "$(docker network ls | grep $ENV_ID)" ]; then
    echo "Docker network was not created."
    exit 1
fi
echo "Docker network $ENV_ID was created successfully."

echo "Environment setup and checks completed successfully."
