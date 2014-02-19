### Fork the repo
Fork the repo to your own account. This will enable you to recieve all the latest changes when they are pushed up and you will be able to push your own changes.

### Install
Install the forum as described [here](https://github.com/cjmarkham/asimpleforum/wiki/Installation)

### Add upstream remote
`git remote add upstream git@github.com:cjmarkham/asimpleforum.git`

### Commit your changes
When you change a file you will need to commit them using `git add {THE_FILE_NAME}; git commit -m {THE_COMMIT_MESSAGE}`

### Fetching latest changes and merge them
`git fetch upstream master; git merge upstream master`

### Submitting your changes
When you have committed your changes you can submit a `pull request` detailing what you have changed and why. This will then be reviewed and merged into the correct branch.