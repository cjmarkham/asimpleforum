# ASimpleForum

## Architecture

ASF is a forum built on top of Silex, MySql, JQuery and of course PHP.  It also optionally uses MongoDB as a caching layer.

## Apache Configuration

I needed to add this in one of the apache config files.  In my case with the vhosts section
for the website.

```
<VirtualHost *:80>
    DocumentRoot "..."
    ServerName ...
    ErrorLog "logs/asimpleforum-error.log"
    CustomLog "logs/asimpleforum-access.log" common

    # Uncomment below for rewrite rule logging
    #LogLevel alert rewrite:trace5
    SetEnv APP_ENV "development"
    <Directory "...">

        Options Indexes FollowSymLinks  # otherwise rewrite rules fail
        AllowOverride All    # otherwise .htaccess won't work

        Order deny,allow
        Allow from all
        require all granted
        satisfy any


    </Directory>
</VirtualHost>
```

ok, the "..." is where I have left blank because you will put your own details there but don't leave as "..."

You will need mod-expires.so to be enabled in Apache.  In my case I added:

```
LoadModule expires_module modules/mod_expires.so ```
To httpd.conf file.  This is needed to support the Expires... directives in the .htaccess file.
 
Since the addition of the ExpiresByType directives 
## Silex 

Silex is a MVC framework (citation?).
 
### SilexAssetic

ASF uses SilexAssetic for css and js compression.  [More documentation](http://code.ohloh.net/file?fid=Vq0SbQn5NS8JRHAIEmrSd8uHAUI&cid=-rl2u2Pu4rw&s=)

*Not yet sure whether this means Java is a dependency or not.*

### Directory Layout

All the ASF project files are in the src/ folder.  The entry point for PHP code is the index.php from within the public/ folder.  (It's good practice to put most of the php files in a folder that is not directly  accessible by the web server for security reasons.)

### Routing

## Database Layout

![Data Model](http://splat.splats-vps.info/web_images/ASF-data-model.png)
The direction of the arrow, points to the table with the primary key.  You can also think of it as the arrow points to the "one" part of the one-to-many relationship.

## 'forum' table

The forums and categories are all recorded in the *forums* table.  The *parent* field is set to the *id* of the parent form if it is a sub-forum and is set to 0 otherwise.

The *left* and *right* fields are node numbers are a separate thing and allow the forum record to be easily placed into a tree like structure at run time.  They have no bearing on forum parentage but are kind of affected by it.

For a new forum the left and right node numbers are determined this way: the node numbers of the new forum node  become (parent.right, parent.right + 1) for the new left and right nodes respectively.  The immediate parent node numbers become (parent.left, parent.right+2).  All the nodes occuring after the parent node, in the tree, have +2 added to both node numbers.

A similar thing happens when deleting a forum.  All nodes later in the tree have 2 subtracted from both node numbers. The immediate parent has 2 subtracted only from the right number.

The fields *added* and *updated* store data in PHP time() function format.

![Forum Node Tree](http://splat.splats-vps.info/web_images/ASF-Node-Tree.png)

In the example above, a new forum is created under "2 Forum 5".  The 2 being the left node number and 5 being the right. After adding the forum "5 Forum 6" then the right value for the following nodes are incremented by 2.  A similar scenario, but in reverse, when forums are deleted.


# Browser Page Refreshes

During development you often want to change files and immediately see the result of those changes in the web browser.  If using 'disk' caching (specified in development.json file) you  generally want to delete the files in cache/ folder to ensure your code changes are  immediately  visible in your browser.