[![QIT CLI Code tests](https://github.com/woocommerce/qit-cli/actions/workflows/code-tests.yml/badge.svg)](https://github.com/woocommerce/qit-cli/actions/workflows/code-tests.yml)
[![QIT Self-Tests - Activation](https://github.com/woocommerce/qit-cli/actions/workflows/qit-self-test-activation.yml/badge.svg)](https://github.com/woocommerce/qit-cli/actions/workflows/qit-self-test-activation.yml)
[![QIT Self-Tests - API](https://github.com/woocommerce/qit-cli/actions/workflows/qit-self-test-api.yml/badge.svg)](https://github.com/woocommerce/qit-cli/actions/workflows/qit-self-test-api.yml)
[![QIT Self-Tests - E2E](https://github.com/woocommerce/qit-cli/actions/workflows/qit-self-test-e2e.yml/badge.svg)](https://github.com/woocommerce/qit-cli/actions/workflows/qit-self-test-e2e.yml)
[![QIT Self-Tests - PHPStan](https://github.com/woocommerce/qit-cli/actions/workflows/qit-self-test-phpstan.yml/badge.svg)](https://github.com/woocommerce/qit-cli/actions/workflows/qit-self-test-phpstan.yml)
[![QIT Self-Tests - Security](https://github.com/woocommerce/qit-cli/actions/workflows/qit-self-test-security.yml/badge.svg)](https://github.com/woocommerce/qit-cli/actions/workflows/qit-self-test-security.yml)

<p align="center"><img src="https://woocommerce.com/wp-content/themes/woo/images/logo-woocommerce-bubble.svg" alt="WooCommerce" style="width:100px;height:auto;"></p>

## Overview

The Quality Insights Toolkit (QIT) is an automated testing tool developed by WooCommerce for extension developers in the [Woo Marketplace](https://woocommerce.com/products/). It allows you to run various types of tests on your extension in a managed environment and receive feedback on potential issues or errors.

## Installing QIT

You can install QIT in three different ways:

### Per Project

1. Run `composer require woocommerce/qit-cli --dev`
2. Execute `./vendor/bin/qit` to authenticate with your WooCommerce.com Partner Developer account.

### Globally Using Composer

1. Run `composer global require woocommerce/qit-cli`
2. Execute `qit` to authenticate with your WooCommerce.com Partner Developer account. Ensure that the Composer bin folder is in your PATH. [Example](https://stackoverflow.com/a/64545124).

### Globally Using `wget`

_(Pro Tip: Opting for the Composer installation method simplifies the process of [updating QIT](https://woocommerce.github.io/qit-documentation/#/cli/getting-started?id=updating-qit) in the future 😉)_
1. Run `wget https://github.com/woocommerce/qit-cli/raw/trunk/qit`
2. Execute `chmod +x qit`
3. Move the file to a directory in your PATH, such as `sudo mv qit /usr/local/bin/qit`
4. Run `qit` to authenticate with your WooCommerce.com Partner Developer account.

## Examples

- `qit run:e2e my-extension` - Runs the WooCommerce Core E2E tests with your extension active.
- `qit run:e2e my-extension --php_version=8.2` - Same as above, but with PHP 8.2.
- `qit run:e2e my-extension --php_version=8.2 --woocommerce_version=rc --wordpress_version=rc` - Same as above, but with the release candidate versions of WooCommerce and WordPress, or latest stable if none available.
- `qit run:e2e my-extension --zip` - Runs the tests using an unreleased development build zip.
- `qit run:e2e my-extension --additional_wordpress_plugins=gutenberg` - Activates the "Gutenberg" feature plugin in the test environment as well.
- `qit run:e2e my-extension --optional_features=hpos` - Enables the [WooCommerce "High Performance Order Storage"](https://developer.woocommerce.com/roadmap/high-performance-order-storage/) feature in the test site.

You can use these parameters individually or in combination to create different scenarios for your tests. Run `qit run:<test-type> --help` to see all the available options. Different test types will have different options to choose from.

### Supported Tests

QIT currently supports the following types of tests:

- [End-to-End Test](https://woocommerce.github.io/qit-documentation/#/test-types/e2e)
- [Activation Test](https://woocommerce.github.io/qit-documentation/#/test-types/activation)
- [Security Test](https://woocommerce.github.io/qit-documentation/#/test-types/security)
- [PHPStan Test](https://woocommerce.github.io/qit-documentation/#/test-types/phpstan)
- [API Test](https://woocommerce.github.io/qit-documentation/#/test-types/api)
- _PHP Minimum and Maximum Version Compatibility (Coming soon)_
- _Performance Test (Coming soon)_

## Documentation

For more detailed information on QIT and how to use it, refer to the [official documentation](https://woocommerce.github.io/qit-documentation/#/).

### Highlights

#### For End-to-End, Activation, and API Tests

- Choose the PHP version, ranging from 7.4 to 8.2.
- Select the WooCommerce and WordPress versions, including beta releases.
- Opt in to activate other extensions to check for potential conflicts.

#### For all Tests

- Integrate QIT as part of your pull requests with our example [GitHub Workflows integration](https://woocommerce.github.io/qit-documentation/#/workflows/getting-started).
- Create and share application passwords with your development team, allowing them to use the QIT CLI without having full access to your WooCommerce.com account.

## Is QIT Exclusively Available for WooCommerce.com Partner Developers?

QIT represents a revolutionary step forward in facilitating automated testing for WordPress developers.

In its inaugural phase, we've chosen to primarily target Woo Marketplace Partner Developers. However, our broader vision encompasses extending the availability of this tool to plugins outside of the Marketplace as well, in a commitment to make automated testing accessible to the WordPress ecosystem as a whole.

If you're interested in leveraging the potential of QIT and want to further support its broader goals, we welcome you to [submit an issue](https://github.com/woocommerce/qit-cli/issues) in our GitHub repository. Your input is invaluable to us, as it greatly contributes to directing the course of the project's future.

## Support

If you need help with QIT, open an issue on this [GitHub repository](https://github.com/woocommerce/qit-cli/issues/new).
