### Autocompletion

We provide basic autocompletion support for Bash/ZSH.

**Testing autocompletion:**

1. If you installed the CLI tool using Composer, run `eval $(./vendor/bin/qit _completion --generate-hook)`. If you installed using the Phar, just replace `./vendor/bin/qit` with the path to the Phar.
2. Assert that autocompletion is working by typing `./vendor/bin/qit` and pressing `<tab>` to see the possible commands.

**Making autocompletion persistent:**

The instructions above will have effect only on the current terminal session. To make autocompletion support persistent:

1. Navigate to the directory where the CLI tool is installed, eg: `cd ./vendor/bin`
2. Run `pwd` to get the full directory path, eg: `/home/johndoe/foo/vendor/bin`
3. Add the following to your terminal config file (`~/.bashrc`, or `~/.zshrc`, for instance, it depends on what terminal you use)
4. `eval $(/home/johndoe/foo/vendor/bin/qit _completion --generate-hook)`
5. Open a new terminal window, and autocompletion for the tool should be working, which you can test by typing `./vendor/bin/qit` and pressing `<tab>` to see the possible commands.
