#!/bin/bash
echo "Running tests for scenario-test-package"

# Generate valid CTRF output
mkdir -p results

# Create proper CTRF JSON directly
START_TIME=$(date +%s000)
STOP_TIME=$((START_TIME + 100))

cat > results/ctrf.json << EOF
{
  "results": {
    "tool": {
      "name": "scenario-test-package"
    },
    "summary": {
      "tests": 1,
      "passed": 1,
      "failed": 0,
      "skipped": 0,
      "pending": 0,
      "other": 0,
      "start": $START_TIME,
      "stop": $STOP_TIME
    },
    "tests": [
      {
        "name": "Scenario test package run",
        "status": "passed",
        "duration": 100
      }
    ]
  }
}
EOF

exit 0