#!/bin/bash

# Echo the arguments received
echo "Arguments received: $@"

# Create CTRF output with proper timestamps
mkdir -p results
cat > results/ctrf.json << EOF
{
  "results": {
    "tool": {
      "name": "passthrough-local"
    },
    "summary": {
      "tests": 1,
      "passed": 1,
      "failed": 0,
      "skipped": 0,
      "pending": 0,
      "other": 0,
      "start": $(date +%s000),
      "stop": $(($(date +%s000) + 100))
    },
    "tests": [
      {
        "name": "Passthrough test with args: $@",
        "status": "passed",
        "duration": 100
      }
    ]
  }
}
EOF

# Create blob report
mkdir -p blob-report
echo '{"version": 1, "tests": []}' > blob-report/report.json

echo "Test completed successfully"
exit 0