#!/bin/bash

# Exit immediately if a command exits with a non-zero status.
set -e

# Get and print the output of the env:list command
echo "Getting environment list..."
ENV_LIST_OUTPUT=$(php src/qit-cli.php env:list)
echo "Output of env:list command: '$ENV_LIST_OUTPUT'"

# Assert no environments are listed
if [ "$ENV_LIST_OUTPUT" != "No environments running." ]; then
  echo "Environments are still listed. Cleanup not successful."
  exit 1
fi
echo "No environments listed. Environment cleanup successful."

# Assert no Docker networks with "_qit_network_" in the name
if [ -n "$(docker network ls | grep _qit_network_)" ]; then
  echo "Docker networks with '_qit_network_' in the name still exist. Cleanup not successful."
  exit 1
fi
echo "No Docker networks with '_qit_network_' in the name. Network cleanup successful."

# Assert no Docker containers with "qit_env_" in the name
if [ -n "$(docker ps -a | grep qit_env_)" ]; then
  echo "Docker containers with 'qit_env_' in the name still exist. Cleanup not successful."
  exit 1
fi
echo "No Docker containers with 'qit_env_' in the name. Container cleanup successful."

echo "Dangling cleanup assertion completed successfully."
