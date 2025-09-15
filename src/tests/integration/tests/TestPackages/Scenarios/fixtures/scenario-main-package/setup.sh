#!/bin/bash
echo "Running setup for scenario-main-package"
echo "scenario-main-package:setup:$(date +%s)" >> /tmp/qit-scenario-phases.log
# Create a database marker for env:reset testing
wp option update qit_main_package_setup_marker "setup_complete_$(date +%s)" --path=/var/www/html
exit 0