#### QIT CLI

[![QIT CLI Code tests](https://github.com/woocommerce/qit-cli/actions/workflows/code-tests.yml/badge.svg)](https://github.com/woocommerce/qit-cli/actions/workflows/code-tests.yml)

#### Managed Tests

[![QIT Self-Tests - Activation](https://github.com/woocommerce/qit-cli/actions/workflows/qit-self-test-activation.yml/badge.svg)](https://github.com/woocommerce/qit-cli/actions/workflows/qit-self-test-activation.yml)
[![QIT Self-Tests - API](https://github.com/woocommerce/qit-cli/actions/workflows/qit-self-test-woo-api.yml/badge.svg)](https://github.com/woocommerce/qit-cli/actions/workflows/qit-self-test-woo-api.yml)
[![QIT Self-Tests - E2E](https://github.com/woocommerce/qit-cli/actions/workflows/qit-self-test-woo-e2e.yml/badge.svg)](https://github.com/woocommerce/qit-cli/actions/workflows/qit-self-test-woo-e2e.yml)
[![QIT Self-Tests - PHPStan](https://github.com/woocommerce/qit-cli/actions/workflows/qit-self-test-phpstan.yml/badge.svg)](https://github.com/woocommerce/qit-cli/actions/workflows/qit-self-test-phpstan.yml)
[![QIT Self-Tests - Security](https://github.com/woocommerce/qit-cli/actions/workflows/qit-self-test-security.yml/badge.svg)](https://github.com/woocommerce/qit-cli/actions/workflows/qit-self-test-security.yml)
[![QIT Self-Tests - PHPCompatibilityWP](https://github.com/woocommerce/qit-cli/actions/workflows/qit-self-test-phpcompatibility.yml/badge.svg)](https://github.com/woocommerce/qit-cli/actions/workflows/qit-self-test-phpcompatibility.yml)
[![QIT Self-Tests - Malware](https://github.com/woocommerce/qit-cli/actions/workflows/qit-self-test-malware.yml/badge.svg)](https://github.com/woocommerce/qit-cli/actions/workflows/qit-self-test-malware.yml)
[![QIT Self-Tests - Validation](https://github.com/woocommerce/qit-cli/actions/workflows/qit-self-test-validation.yml/badge.svg)](https://github.com/woocommerce/qit-cli/actions/workflows/qit-self-test-validation.yml)

#### Custom Tests

[![QIT Self-Tests - Custom Tests](https://github.com/woocommerce/qit-cli/actions/workflows/qit-self-test-custom-test.yml/badge.svg)](https://github.com/woocommerce/qit-cli/actions/workflows/qit-self-test-custom-test.yml)

#### Test Environment

[![QIT Environment Test - Linux](https://github.com/woocommerce/qit-cli/actions/workflows/qit-environment-test-linux.yml/badge.svg)](https://github.com/woocommerce/qit-cli/actions/workflows/qit-environment-test-linux.yml)
[![QIT Environment Dangling Test](https://github.com/woocommerce/qit-cli/actions/workflows/qit-environment-dangling-test.yml/badge.svg)](https://github.com/woocommerce/qit-cli/actions/workflows/qit-environment-dangling-test.yml)

<p align="center"><img src="https://woocommerce.com/wp-content/themes/woo/images/logo-woocommerce-bubble.svg" alt="WooCommerce" style="width:100px;height:auto;"></p>

## Quality Insights Toolkit (QIT)

QIT is a testing platform for WordPress Plugins and Themes developed by WooCommerce, designed to simplify and streamline your testing process. We are currently in closed beta operating only in the Woo Marketplace.

#### Managed Tests

Run tests out-of-the box with zero configuration.

- [Woo E2E Test](https://qit.woo.com/docs/managed-tests/woo-e2e) - Runs WooCommerce End-to-End tests alongside your extension.
- [Woo API Test](https://qit.woo.com/docs/managed-tests/woo-api) - Runs WooCommerce API tests with your extension.
- [Activation Test](https://qit.woo.com/docs/managed-tests/activation) - Activates your plugin and logs any PHP notice, warning, and error.
- [Security Test](https://qit.woo.com/docs/managed-tests/security) - Scan your plugin for adherence to best practices in writing secure code.
- [PHPStan Test](https://qit.woo.com/docs/managed-tests/phpstan) - Run PHPStan checks to catch issues early.
- [PHP Compatibility Test](https://qit.woo.com/docs/managed-tests/phpcompatibility) - Run PHPCompatibility tests to detect issues with different PHP versions.
- [Malware Test](https://qit.woo.com/docs/managed-tests/malware) - Scan your code with the Malware check.
- _Performance Tests (Work-in-progress)_

#### **Custom E2E Tests** _(Early Access)_

Write your own E2E tests using frameworks such as [Playwright](https://playwright.dev/), or leverage pre-built tests to effortlessly increase your test coverage.

[Read more about Custom E2E Tests](https://qit.woo.com/docs/custom-tests/introduction).

#### Local Test Environment _(Early Access)_

Fast, disposable and configurable test environment designed to do one thing well: **Run tests.**

Our Alpine-based Docker images are perfect for CI systems, while also offering native ARM64 support for Apple Silicon and optimized Docker volumes for faster performance on Mac, Linux, and Windows WSL for local tests. 

[Read more about the Local Test Environment.](https://qit.woo.com/docs/environment/getting-started)

#### And much more:

- **Configurable Environment**: Configurable PHP, WordPress, WooCommerce versions, and more.
- **In-Depth Analysis**: Tracks all PHP notices, warnings, and errors during testing.
- **Development & Published Versions**: Test with both development and published versions of your plugins.
- **GitHub Integration**: Integrate QIT into your PR reviews with GitHub Actions.
- **Test Reports**: Detailed test reports to help you understand the results.
- **Notifications**: Stay informed with test result notifications.

## Documentation

For more detailed information on QIT and how to use it, refer to the [documentation](https://qit.woo.com/docs/).

<p align="center">
  <img src="https://github.com/woocommerce/qit-cli/assets/9341686/640698a7-01c3-498a-8bb2-7c5e337e0a9c" alt="Qit Quick Demo">
</p>

## Installing QIT

1. Run `composer require woocommerce/qit-cli --dev`
2. Execute `./vendor/bin/qit` to authenticate with your WooCommerce.com Partner Developer account.

You can use these parameters individually or in combination to create different scenarios for your tests. Run `qit run:<test-type> --help` to see all the available options. Different test types will have different options to choose from.

## Can I use QIT?

Most features of QIT requires you to log-in as a Partner Developer of the WooCommerce.com Marketplace, but we have plans to open it to all developers in the future.

The QIT Local Test Environment does not require you to be connected to WooCommerce.com, although to install WooCommerce.com Premium plugins and themes on your test environment you will need to be connected as a Partner Developer of the Woo.com Marketplace (and have access to the extensions you want to test).

## Support

If you need help with QIT, open an issue on this [GitHub repository](https://github.com/woocommerce/qit-cli/issues/new).
