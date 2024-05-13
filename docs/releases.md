### How to release a new version of the QIT CLI

Let's suppose that the latest release is 0.4. To release 0.5, you would do:

- `git checkout trunk`
- Create the file `./docs/changelogs/0.5.0.md` with the changelog notes for this release.
- Build the CLI with `make build VERSION=0.5.0`
- Commit it to the repo
- Create a tag `git tag -a 0.5.0`
- Publish the tag `git push origin 0.5.0`

A GitHub Action is listening for new tags being pushed to the repo, it will build the QIT CLI and create a new release.

Assert that you see your new release in https://github.com/woocommerce/qit-cli/releases. Download the `phar` and validate it locally.
