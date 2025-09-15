#!/bin/bash

# Test network connectivity and report the actual state
# This script is used by test packages to verify network restrictions

echo "=== Network Connectivity Test ==="

# The script runs from within the test package directory
echo "Current directory: $(pwd)"

# Test external network connectivity using wget (more reliable in containers)
if wget -q --spider --timeout=5 https://www.google.com 2>/dev/null; then
    echo "Network test: ONLINE - External network is accessible"
    NETWORK_STATUS="online"
    TEST_MESSAGE="Network is ONLINE - External requests are allowed"
else
    echo "Network test: OFFLINE - External network is blocked"
    NETWORK_STATUS="offline"
    TEST_MESSAGE="Network is OFFLINE - External requests are blocked"
fi

# Generate CTRF JSON report with network state
cat > ./results/ctrf.json << EOF
{
  "results": {
    "tool": {
      "name": "network-check"
    },
    "summary": {
      "tests": 1,
      "passed": 1,
      "failed": 0,
      "pending": 0,
      "skipped": 0,
      "other": 0,
      "start": 1000000000000,
      "stop": 1000000001000
    },
    "tests": [
      {
        "name": "network-connectivity-check-${NETWORK_STATUS}",
        "status": "passed",
        "duration": 100,
        "message": "${TEST_MESSAGE}"
      }
    ]
  }
}
EOF

echo "Test completed. Network was: $NETWORK_STATUS"
echo "Results saved to: $(pwd)/results/ctrf.json"

# Verify the file was created
if [ -f ./results/ctrf.json ]; then
    echo "✓ CTRF JSON file created successfully"
else
    echo "✗ ERROR: CTRF JSON file was not created!"
    exit 1
fi

# Exit successfully
exit 0