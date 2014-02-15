This method of installation is basic and is only provided for contributors who wish to install the forum on their own development server. An install process for general users will be in development once the project reaches a more mature stage.

# Fork the repo
In order to get the source code you will need to `fork` the repository to your own account. You will then need to `pull` the files from your fork onto your local machine.

# Install dependencies
The dependencies are installed via [Composer](https://getcomposer.org/). You will need to download composer in order to install the forum.
Once downloaded you can browse to the directory where you have the source files. From the `root` run `composer install`.

# Add the database tables
Create a local database and run the `sql` found in `install/sql`. This will create the structure for all the tables.

# Add your config
Create a `config` directory in the `root` of the project. Inside it you will need to create a `development.json` file with the [example config](https://github.com/cjmarkham/asimpleforum/wiki/Config-Example). Note: You must set `APP_ENV` in your vhosts to `development`. If you do not do this then A Simple Forum will assume a production environment and you will not have debugging enabled. For trouble shooting this see this [Stack Overflow](http://stackoverflow.com/questions/2378871/set-application-env-via-virtual-host-config-and-read-this-in-php) question.

Thats it. The forum will now be installed. You will need to create a new user (Using the signup form is easier as multiple tables are updated with a new user) and manually assign them admin permissions in the database (`UPDATE user SET perm_group=1 WHERE id=1`). This will be done automatically with the full release version of the install software.

Please note that there are only basic moderation tools present and no admin section. Any database changes will have to be done manually.

