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
- API Test
- Compatibility Test
- _Performance Test (Coming soon)_

### Quick Start

To use QIT, follow these steps:

1. Install the QIT CLI tool with `composer require woocommerce/qit-cli`.
2. Add your partner credentials with `./vendor/bin/qit partner-add`.
3. Run a test on your extension with `./vendor/bin/qit run:e2e YOUR_EXTENSION`.

For some tests, you can specify the PHP, WooCommerce, and WordPress versions you want to use.

## Documentation

For more detailed information on QIT and how to use it, refer to the [official documentation](https://woocommerce.github.io/qit-documentation/#/).

## Support

If you need help with QIT, you can open an issue on this [GitHub repository](https://github.com/woocommerce/qit-cli).
