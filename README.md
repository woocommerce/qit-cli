[![QIT CLI Code tests](https://github.com/woocommerce/qit-cli/actions/workflows/code-tests.yml/badge.svg)](https://github.com/woocommerce/qit-cli/actions/workflows/code-tests.yml)
[![QIT Self-Tests - Activation](https://github.com/woocommerce/qit-cli/actions/workflows/qit-self-test-activation.yml/badge.svg)](https://github.com/woocommerce/qit-cli/actions/workflows/qit-self-test-activation.yml)
[![QIT Self-Tests - API](https://github.com/woocommerce/qit-cli/actions/workflows/qit-self-test-api.yml/badge.svg)](https://github.com/woocommerce/qit-cli/actions/workflows/qit-self-test-api.yml)
[![QIT Self-Tests - E2E](https://github.com/woocommerce/qit-cli/actions/workflows/qit-self-test-e2e.yml/badge.svg)](https://github.com/woocommerce/qit-cli/actions/workflows/qit-self-test-e2e.yml)
[![QIT Self-Tests - PHPStan](https://github.com/woocommerce/qit-cli/actions/workflows/qit-self-test-phpstan.yml/badge.svg)](https://github.com/woocommerce/qit-cli/actions/workflows/qit-self-test-phpstan.yml)
[![QIT Self-Tests - Security](https://github.com/woocommerce/qit-cli/actions/workflows/qit-self-test-security.yml/badge.svg)](https://github.com/woocommerce/qit-cli/actions/workflows/qit-self-test-security.yml)
[![QIT Self-Tests - PHPCompatibilityWP](https://github.com/woocommerce/qit-cli/actions/workflows/qit-self-test-phpcompatibility.yml/badge.svg)](https://github.com/woocommerce/qit-cli/actions/workflows/qit-self-test-phpcompatibility.yml)
[![QIT Self-Tests - Malware](https://github.com/woocommerce/qit-cli/actions/workflows/qit-self-test-malware.yml/badge.svg)](https://github.com/woocommerce/qit-cli/actions/workflows/qit-self-test-malware.yml)

<p align="center"><img src="https://woo.com/wp-content/themes/woo/images/logo-woocommerce-bubble.svg" alt="WooCommerce" style="width:100px;height:auto;"></p>

## Documentation

For more detailed information on QIT and how to use it, refer to the [official documentation](https://woocommerce.github.io/qit-documentation/#/).

## Quality Insights Toolkit (QIT)

Official tool to streamline the testing of plugins and themes, ensuring they meet the high standards of the Woo.com Marketplace.

- **Comprehensive Testing Suite**: Includes various tests to ensure thorough quality checks.
  - [Woo E2E Test](https://woocommerce.github.io/qit-documentation/#/test-types/e2e)
  - [Woo API Test](https://woocommerce.github.io/qit-documentation/#/test-types/api)
  - [Activation Test](https://woocommerce.github.io/qit-documentation/#/test-types/activation)
  - [Security Test](https://woocommerce.github.io/qit-documentation/#/test-types/security)
  - [PHPStan Test](https://woocommerce.github.io/qit-documentation/#/test-types/phpstan)
  - [PHP Compatibility Test](https://woocommerce.github.io/qit-documentation/#/test-types/phpcompatibility)
  - [Malware Test](https://woocommerce.github.io/qit-documentation/#/test-types/malware)
  - _Performance Tests (Work-in-progress)_
  - _Custom E2E Tests (Work-in-progress)_
- **Configurable Environment**: Configurable PHP, WordPress, WooCommerce versions, and more.
- **In-Depth Analysis**: Tracks all PHP notices, warnings, and errors during testing.
- **Feature Flags**: Supports various WooCommerce feature flags for extended coverage.
- **Compatibility Testing**: Support activation of additional WordPress plugins for compatibility testing.
- **Development & Stable Versions**: Test with both development builds and published stable versions.
- **GitHub Integration**: Integrate QIT into your PR reviews with GitHub Actions.
- **Test Reports**: Detailed test reports to help you understand the results.
- **Notifications**: Stay informed with test result notifications.

<p align="center">
  <img src="https://github.com/woocommerce/qit-cli/assets/9341686/640698a7-01c3-498a-8bb2-7c5e337e0a9c" alt="Qit Quick Demo">
</p>

## Installing QIT

1. Run `composer require woocommerce/qit-cli --dev`
2. Execute `./vendor/bin/qit` to authenticate with your Woo.com Partner Developer account.

You can use these parameters individually or in combination to create different scenarios for your tests. Run `qit run:<test-type> --help` to see all the available options. Different test types will have different options to choose from.

## Can I use QIT?

QIT is currently only available for plugins and themes in the Woo.com Marketplace, but we have plans to open it to all developers in the future.

## Support

If you need help with QIT, open an issue on this [GitHub repository](https://github.com/woocommerce/qit-cli/issues/new).
