Below are the definitions of a config file. You can find the actual file in the repository `development.dist.json`

# Definitions

* `debug`: Prints detailed information on error. Set to false for production.

***

* `cookie.name`: The name of the sites cookie
* `cookie.domain`: The domain path for this cookie

***

* `board.doublePost`: Should users be allowed to double post? Values are `allow`, `disallow`, `merge`.
* `board.logo`: The logo file located in `public/images`
* `board.name`: The name of the board
* `board.url`: The absolute url to your forums home page
* `board.postsPerPage`: How many posts to show per page
* `board.topicsPerPage`: How many topics to show per page
* `board.confirmEmail`: Whether or not users have to confirm their email to login
* `board.base`: The base directory of the forum

***

* `database.host`: The host name for your database
* `database.user`: The username for your database
* `database.password`: The password for your database
* `database.name`: The name of your database

***

* `mongo.host`: The host name for your mongodb setup
* `mongo.port`: The port number for your mongodb setup

***

* `defaults.cache`: The default caching method. Values are `mongo` and `disk` Set to disk if mongodb is not installed.
* `defaults.language`: The language to use. Should reflect the file in `public/languages`
* `defaults.salt`: The salt used in hashing passwords

***

* `emails.noReply`: The no reply email

***

* `navLinks` An array of links to be shown on the top of the forum
    * `link`: The link to the page`
    * `title`: The text for the link`
    * `section`: The name of it's section

***

* `sidebars`: An array of sections and their sidebars.
    * `sectionName`: `[sidebarFile]`