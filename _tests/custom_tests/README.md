### Running tests

1. Copy `.env.example` to `.env` and update the values
2. Run `./vendor/bin/paratest`

### Updating snapshots

1. Append "UPDATE_SNAPSHOTS" env var, eg: `UPDATE_SNAPSHOTS=true ./vendor/bin/paratest`