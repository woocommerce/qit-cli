# Comprehensive Test Table for QIT Test Packages

| Test ID | Test Description | Test Type | Package Type | Execution Phase | Expected Result | Special Requirements |
|---------|------------------|-----------|--------------|-----------------|-----------------|---------------------|
| TP-001 | Basic test package execution in all phases | Functional | Local | All (globalSetup, setup, run, teardown, globalTeardown) | All phases execute successfully | Local test package with all phases defined |
| TP-002 | Published test package execution | Functional | Published | All | Executes successfully from registry | Published test package |
| TP-003 | Mixed local and published packages | Functional | Mixed | All | Both package types execute successfully | One local, one published package |
| TP-004 | Multiple test packages in same run | Functional | Local | All | All packages execute without interference | 2+ local test packages |
| TP-005 | Bootstrap-only package execution | Functional | Local | globalSetup only | Only globalSetup runs | Package with empty phases except globalSetup |
| TP-006 | CTRF result collection from all phases | Result Collection | Local | All | CTRF JSON contains results from all executed phases | Test package that generates CTRF output |
| TP-007 | CTRF result collection from published packages | Result Collection | Published | All | CTRF JSON contains results from published package | Published test package with CTRF output |
| TP-008 | CTRF merging from multiple packages | Result Collection | Mixed | All | Single merged CTRF with results from all packages | Multiple packages with CTRF output |
| TP-009 | PHP log collection during globalSetup phase | Logging | Local | globalSetup | PHP logs captured and included in results | Script that generates PHP errors/warnings |
| TP-010 | PHP log collection during setup phase | Logging | Local | setup | PHP logs captured and included in results | Script that generates PHP errors/warnings |
| TP-011 | PHP log collection during run phase | Logging | Local | run | PHP logs captured and included in results | Script that generates PHP errors/warnings |
| TP-012 | PHP log collection during teardown phase | Logging | Local | teardown | PHP logs captured and included in results | Script that generates PHP errors/warnings |
| TP-013 | PHP log collection during globalTeardown phase | Logging | Local | globalTeardown | PHP logs captured and included in results | Script that generates PHP errors/warnings |
| TP-014 | PHP log collection from published packages | Logging | Published | All | PHP logs captured from published package | Published package that generates PHP errors |
| TP-015 | PHP log collection with mixed package types | Logging | Mixed | All | PHP logs captured from all package types | Mix of local/published packages with PHP errors |
| TP-016 | Allure report collection from local packages | Result Collection | Local | All | Allure reports collected and stored | Test package with Allure output |
| TP-017 | Allure report collection from published packages | Result Collection | Published | All | Allure reports collected and stored | Published package with Allure output |
| TP-018 | Empty phase handling | Edge Case | Local | setup, run, teardown | Empty phases skipped gracefully | Package with empty array phases |
| TP-019 | Package with failing setup phase | Error Handling | Local | setup | Test run continues, error reported | Setup script that exits with error code |
| TP-020 | Package with failing run phase | Error Handling | Local | run | Test run continues, error reported | Run script that exits with error code |
| TP-021 | Package with failing teardown phase | Error Handling | Local | teardown | Test run continues, error reported | Teardown script that exits with error code |
| TP-022 | Global setup failure handling | Error Handling | Local | globalSetup | Test run stops, error reported | Global setup that exits with error |
| TP-023 | Global teardown failure handling | Error Handling | Local | globalTeardown | Environment cleanup continues, error logged | Global teardown that exits with error |
| TP-024 | Test package timeout handling | Performance | Local | run | Test times out gracefully | Run script with infinite loop |
| TP-025 | Large test package handling | Performance | Local | All | Package executes without memory issues | Package with large dependencies |
| TP-026 | Test package with custom environment variables | Configuration | Local | All | Environment variables accessible | Package that reads custom env vars |
| TP-027 | Test package with required secrets | Security | Local | All | Secrets properly passed to container | Package requiring API keys |
| TP-028 | Package phase execution order validation | Functional | Local | All | Phases execute in correct order | Package with identifiable markers in each phase |
| TP-029 | Concurrent test package execution | Performance | Local | All | Multiple packages run concurrently without issues | Multiple packages with timing markers |
| TP-030 | Database state isolation between packages | Functional | Local | All | Database state properly restored between packages | Packages that modify database |
| TP-031 | File system isolation between packages | Functional | Local | All | File system changes don't leak between packages | Packages that modify files |
| TP-032 | Network isolation between packages | Functional | Local | All | Network calls properly handled | Packages that make external API calls |
| TP-033 | Test package with plugin dependencies | Functional | Local | All | Dependencies properly installed and activated | Package requiring specific plugins |
| TP-034 | Test package with theme dependencies | Functional | Local | All | Dependencies properly installed and activated | Package requiring specific themes |
| TP-035 | WordPress version compatibility testing | Compatibility | Local | All | Package works across WordPress versions | Package tested on multiple WP versions |
| TP-036 | PHP version compatibility testing | Compatibility | Local | All | Package works across PHP versions | Package tested on multiple PHP versions |
| TP-037 | WooCommerce version compatibility testing | Compatibility | Local | All | Package works across WooCommerce versions | Package tested on multiple WC versions |
| TP-038 | Test package manifest validation | Validation | Local | N/A | Invalid manifests properly rejected | Package with invalid manifest |
| TP-039 | Test package publishing workflow | Functional | Local → Published | All | Package can be published and executed | Full publish → run workflow |
| TP-040 | Test package deletion workflow | Functional | Published | N/A | Package properly removed from registry | Published package deletion |

## Test Categories Summary

1. **Execution Tests**: Verify packages execute in all phases correctly
2. **Result Collection Tests**: Ensure CTRF and Allure results are properly collected
3. **Logging Tests**: Validate PHP log collection in each phase
4. **Package Type Tests**: Cover local, published, and mixed package scenarios
5. **Error Handling Tests**: Test failure scenarios in each phase
6. **Performance Tests**: Check timeout handling and resource usage
7. **Configuration Tests**: Validate environment variables and secrets
8. **Isolation Tests**: Ensure proper state isolation between packages
9. **Compatibility Tests**: Test across different WordPress/PHP/WooCommerce versions
10. **Workflow Tests**: Cover publishing, listing, and deletion workflows