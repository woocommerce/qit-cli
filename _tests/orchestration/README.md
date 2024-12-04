# Orchestration Tests

This directory contains compatibility test suites to test orchestration. To run it, do `qit run:e2e woocommerce-amazon-s3-storage` in this directory.

## Overview

Each test suite verifies that plugins can coexist and handle their resources properly. The test lifecycle works as follows:

1. **Shared Setup**: Each plugin establishes its shared state
2. **DB Export**: A DB snapshot is created
3. **Plugin A - Full Cycle**: Setup → Test → Teardown
4. **DB Import**: The DB snapshot is restored
5. **Plugin B - Full Cycle**: Setup → Test → Teardown
6. **Shared Teardown**: Cleanup in reverse order