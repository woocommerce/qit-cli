# Integration Test Status

## Fast Tests (`make test`) — ALL PASS ✅

| Group | Tests | Status | Time |
|---|---|---|---|
| env-up (UpEnvironmentCommandTest, ExtensionResolutionTest, EnvUpPrecedenceTest) | 50 | ✅ All pass | ~52s |
| remote-test (RemoteTestPrecedenceTest, RunActivationPrecedenceTest, RunGroupCommandTest) | 8 | ✅ All pass | ~15s |

## Docker Tests — RunE2ECommandTest ✅

| # | Test | Status |
|---|---|---|
| 1 | test_developer_creates_first_custom_test | ✅ Pass |
| 2 | test_developer_tests_specific_woo_version | ✅ Pass |
| 3 | test_developer_quick_smoke_test | ✅ Pass |
| 4 | test_run_e2e_with_local_package_and_custom_plugin | ✅ Pass |
| 5 | test_ci_cd_integration_workflow | ✅ Pass |
| 6 | test_plugin_ecosystem_testing | ✅ Pass |
| 7 | test_developer_copies_example_test | ✅ Pass |
| 8 | test_simple_slug_resolution | ✅ Pass |
| 9 | test_local_plugin_with_inferred_slug | ✅ Pass |
| 10 | test_developer_tests_in_plugin_repo | ✅ Pass |
| 11 | test_mixed_plugin_sources | ✅ Pass |
| 12 | test_config_file_integration | ✅ Pass |
| 13 | test_run_e2e_without_config_full | ✅ Pass |
| 14 | test_run_e2e_with_minimal_config_full | ✅ Pass |
| 15 | test_run_e2e_multiple_packages_no_config_full | ✅ Pass |
| 16 | test_invalid_plugin_specifications | ✅ Pass |

## Docker Tests — UpEnvironmentCommandTest ✅

| # | Test | Status |
|---|---|---|
| 17 | test_env_up_real_environment_verification | ✅ Pass |

## Docker Tests — RunE2EPrecedenceTest ✅

| # | Test | Status | Notes |
|---|---|---|---|
| 1 | test_cli_overrides_config_for_run_e2e | ✅ Pass | |
| 2 | test_profile_defaults_for_run_e2e | ✅ Pass | |
| 3 | test_cli_overrides_config_and_profile_scalars | ✅ Pass | |
| 4 | test_cli_sut_slug_overrides_qit_json_sut | ✅ Pass | |
| 5 | test_cli_plugins_extend_and_dedupe | ✅ Pass | |
| 6 | test_object_cache_and_tunnel_flags | ✅ Pass | Fixed: tunnel type `cloudflare` → `cloudflared-docker`, added tunnel URL normalizer |
| 7 | test_env_var_and_env_file_merging | ✅ Pass | Fixed: assertions match actual envs structure (associative map) |
| — | test_php_extensions_merge_and_dedupe | Removed | Duplicate of EnvUpPrecedenceTest |
| — | test_volume_mappings_merge_and_dedupe | Removed | Duplicate of EnvUpPrecedenceTest |
| 8 | test_object_cache_flag_propagates | ✅ Pass | |
| — | test_defaults_when_no_cli_or_config_values | Removed | Duplicate of EnvUpPrecedenceTest |
| 9 | test_cli_slug_wins_and_emits_warning | ✅ Pass | Fixed: use local source, implemented warning in RunE2ECommand (stderr) |
| — | test_sut_dir_flag_overrides_everything | Removed | Tested unimplemented --zip override behavior |
| 10 | test_invalid_sut_type_throws | ✅ Pass | Fixed: use wccom slug, match schema validation error |
| 11 | test_root_level_sut_used_when_cli_slug_missing | ✅ Pass | Fixed: use woocommerce slug with wporg source |
| 12 | test_missing_sut_causes_error | ✅ Pass | Fixed: added SUT validation in RunE2ECommand (fail fast) |

## Unit Tests — ALL PASS ✅

181/181 pass.

## Manager Tests — ALL PASS ✅

703/703 pass.
