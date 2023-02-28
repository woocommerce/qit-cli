### How to release a new version of the QIT CLI

Let's suppose that the latest release is 0.1. To release 0.2, you would do:

- `git checkout trunk`
- Create the file `./docs/changelogs/0.2.md` with the changelog notes for this release.
- `git tag -a 0.2`
- `git push origin 0.2`

A GitHub Action is listening for new tags being pushed to the repo, it will build the QIT CLI and create a new release.

Assert that you see your new release in https://github.com/woocommerce/qit-cli/releases. Download the `phar` and validate it locally.
