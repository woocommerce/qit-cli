[![QIT CLI Code tests](https://github.com/woocommerce/qit-cli/actions/workflows/code-tests.yml/badge.svg)](https://github.com/woocommerce/qit-cli/actions/workflows/code-tests.yml)
[![QIT Self-Tests - Activation](https://github.com/woocommerce/qit-cli/actions/workflows/qit-self-test-activation.yml/badge.svg)](https://github.com/woocommerce/qit-cli/actions/workflows/qit-self-test-activation.yml)
[![QIT Self-Tests - API](https://github.com/woocommerce/qit-cli/actions/workflows/qit-self-test-woo-api.yml/badge.svg)](https://github.com/woocommerce/qit-cli/actions/workflows/qit-self-test-woo-api.yml)
[![QIT Self-Tests - E2E](https://github.com/woocommerce/qit-cli/actions/workflows/qit-self-test-woo-e2e.yml/badge.svg)](https://github.com/woocommerce/qit-cli/actions/workflows/qit-self-test-woo-e2e.yml)
[![QIT Self-Tests - PHPStan](https://github.com/woocommerce/qit-cli/actions/workflows/qit-self-test-phpstan.yml/badge.svg)](https://github.com/woocommerce/qit-cli/actions/workflows/qit-self-test-phpstan.yml)
[![QIT Self-Tests - Security](https://github.com/woocommerce/qit-cli/actions/workflows/qit-self-test-security.yml/badge.svg)](https://github.com/woocommerce/qit-cli/actions/workflows/qit-self-test-security.yml)
[![QIT Self-Tests - PHPCompatibilityWP](https://github.com/woocommerce/qit-cli/actions/workflows/qit-self-test-phpcompatibility.yml/badge.svg)](https://github.com/woocommerce/qit-cli/actions/workflows/qit-self-test-phpcompatibility.yml)
[![QIT Self-Tests - Malware](https://github.com/woocommerce/qit-cli/actions/workflows/qit-self-test-malware.yml/badge.svg)](https://github.com/woocommerce/qit-cli/actions/workflows/qit-self-test-malware.yml)

<p align="center"><img src="https://woo.com/wp-content/themes/woo/images/logo-woocommerce-bubble.svg" alt="WooCommerce" style="width:100px;height:auto;"></p>

## Quality Insights Toolkit (QIT)

QIT is a testing platform for WordPress Plugins and Themes developed by WooCommerce that allows developers to run a series of managed tests out-of-the-box. We are currently in closed beta operating only in the Woo Marketplace.

#### Managed Tests

Run tests out-of-the box with zero configuration.

- [Woo E2E Test](https://qit.woo.com/docs/test-types/woo-e2e)
- [Woo API Test](https://qit.woo.com/docs/test-types/woo-api)
- [Activation Test](https://qit.woo.com/docs/test-types/activation)
- [Security Test](https://qit.woo.com/docs/test-types/security)
- [PHPStan Test](https://qit.woo.com/docs/test-types/phpstan)
- [PHP Compatibility Test](https://qit.woo.com/docs/test-types/phpcompatibility)
- [Malware Test](https://qit.woo.com/docs/test-types/malware)
- _Performance Tests (Work-in-progress)_

#### **Custom E2E Tests** _(Coming Soon)_

Write your own E2E tests and share them with the community, or leverage pre-built ones to effortlessly increase your test coverage. Run compatibility tests with other plugins, and, with a little bit of configuration, create a matrix to run the same tests using various versions of PHP, WP and Woo, for increased coverage. [Read more about Custom E2E Tests](https://qit.woo.com/docs/custom-tests/introduction).

#### Local Test Environment _(Coming Soon)_

Fast, disposable and configurable local test environment that aims to do one thing well: Run tests.

- Very small docker images based on Alpine, which makes them perfect for running on CI.
- Our custom-built Docker images are published on ARM64 and AMD64 CPU architectures, granting maximum performance on newer Mac CPUs.
- The Docker volumes are optimized for reducing I/O bottleneck on environments where Docker runs in a virtualized context, such as on Macs.

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
2. Execute `./vendor/bin/qit` to authenticate with your Woo.com Partner Developer account.

You can use these parameters individually or in combination to create different scenarios for your tests. Run `qit run:<test-type> --help` to see all the available options. Different test types will have different options to choose from.

## Can I use QIT?

Most features of QIT requires you to log-in as a Partner Developer of the Woo.com Marketplace, but we have plans to open it to all developers in the future.

The QIT Local Test Environment does not require you to be connected to Woo.com, although to install Woo.com Premium plugins and themes on your test environment you will need to be connected as a Partner Developer of the Woo.com Marketplace (and have access to the extensions you want to test).

## Support

If you need help with QIT, open an issue on this [GitHub repository](https://github.com/woocommerce/qit-cli/issues/new).
