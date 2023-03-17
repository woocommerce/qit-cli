[![QIT CLI Code tests](https://github.com/woocommerce/qit-cli/actions/workflows/code-tests.yml/badge.svg)](https://github.com/woocommerce/qit-cli/actions/workflows/code-tests.yml)

<p align="center"><img src="https://woocommerce.com/wp-content/themes/woo/images/logo-woocommerce-bubble.svg" alt="WooCommerce" style="width:100px;height:auto;"></p>

## Introduction

The Quality Insights Toolkit (QIT) is an initiative by WooCommerce designed to provide extension developers in the [Woo Marketplace](https://woocommerce.com/products/) with managed automated tests.

Example: `./qit run:e2e foo --php_version=8.2 --woocommerce_version=7.4-RC1`

This will start up a managed end-to-end test on our servers, creating a temporary test environment with the provided PHP, WooCommerce, and WordPress versions, with your extension activate. We then execute the same tests that WooCommerce itself runs before releases, and you can view the result and a report containing any PHP notices, warnings, or errors that happens during the test.

Here are the different tests you can run so far:

- [End-to-End Test](https://woocommerce.github.io/qit-documentation/#/test-types/e2e)
- [Activation Test](https://woocommerce.github.io/qit-documentation/#/test-types/activation)
- [Security Test](https://woocommerce.github.io/qit-documentation/#/test-types/security)
- [PHPStan Test](https://woocommerce.github.io/qit-documentation/#/test-types/phpstan)
- API Test
- Compatibility Test
- _Performance Test (Coming soon)_

### Quick Start Guide

1. `composer require woocommerce/qit-cli`
2. `./vendor/bin/qit partner-add`
3. `./vendor/bin/qit run:e2e YOUR_EXTENSION`

## Documentation

For a more comprehensive documentation, go to [woocommerce.github.io/qit-documentation](https://woocommerce.github.io/qit-documentation/#/).

## Support

You can open a GitHub issue in this repository if you need support with Quality Insights Toolkit.
