[![QIT CLI Code tests](https://github.com/woocommerce/qit-cli/actions/workflows/code-tests.yml/badge.svg)](https://github.com/woocommerce/qit-cli/actions/workflows/code-tests.yml)

<p align="center"><img src="https://woocommerce.com/wp-content/themes/woo/images/logo-woocommerce-bubble.svg" alt="WooCommerce" style="width:100px;height:auto;"></p>

## Overview

The Quality Insights Toolkit (QIT) is an automated testing tool developed by WooCommerce for extension developers in the [Woo Marketplace](https://woocommerce.com/products/). It allows you to run various types of tests on your extension in a managed environment and get feedback on potential issues or errors.

### Supported Tests

QIT currently supports the following types of tests:

- [End-to-End Test](https://woocommerce.github.io/qit-documentation/#/test-types/e2e)
- [Activation Test](https://woocommerce.github.io/qit-documentation/#/test-types/activation)
- [Security Test](https://woocommerce.github.io/qit-documentation/#/test-types/security)
- [PHPStan Test](https://woocommerce.github.io/qit-documentation/#/test-types/phpstan)
- [API Test](https://woocommerce.github.io/qit-documentation/#/test-types/api)
- Compatibility Test
- _Performance Test (Coming soon)_

In addition, QIT offers the following features:

- For certain test types, you can select specific versions of PHP, WooCommerce, and WordPress to test against, including Release Candidates of WooCommerce.
- During certain test types, any PHP notices, warnings, or errors that occur will be displayed.
- QIT can be integrated into your development process using the [GitHub Workflows integration](https://woocommerce.github.io/qit-documentation/#/workflows/getting-started).
- You can create and share application passwords with your development team, which allows them to use the QIT CLI without having full access to your WooCommerce.com account.
- For certain types of tests, you can choose to activate other WordPress or WooCommerce extensions alongside your own to check for potential conflicts.

### Quick Start

To use QIT, follow these steps:

1. Install the QIT CLI tool with `composer require woocommerce/qit-cli`.
2. Add your partner credentials with `./vendor/bin/qit partner-add`.
3. Run a test on your extension with `./vendor/bin/qit run:e2e YOUR_EXTENSION`.

## Documentation

For more detailed information on QIT and how to use it, refer to the [official documentation](https://woocommerce.github.io/qit-documentation/#/).

## Support

If you need help with QIT, you can open an issue on this [GitHub repository](https://github.com/woocommerce/qit-cli).
