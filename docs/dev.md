### `detected dubious ownership in repository` error during `make build

If you encounter this error during `make build`. Edit `./_build/box.json` and remove
line `"git-tag": "QIT_CLI_VERSION",`, then run `make build` again.
