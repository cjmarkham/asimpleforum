# ASimpleForum

## Architecture

ASF is a forum built on top of Silex, MySql, JQuery and of course PHP.  It also optionally uses MongoDB as a caching layer.

## Apache Configuration

An example of the minimum needed in your apache vhost configuration.

```
<VirtualHost *:80>
    DocumentRoot "SITE_ROOT"
    ServerName yoursite.com

    SetEnv APP_ENV "development" # Only needed if you want debugging enabled

    <Directory "SITE_ROOT">

        Options Indexes FollowSymLinks  # otherwise rewrite rules fail
        AllowOverride All    # otherwise .htaccess won't work

        Order allow,deny 
	Allow from all 

    </Directory>
</VirtualHost>
```

## Silex

Silex is a Micro Framework built on PHP. [Silex website](http://silex.sensiolabs.org/)

### Assetic

A Simple Forum uses Symfony's Assetic for CSS and JS compression with the help of the Silex Service Provider.

Users have the option to choose their compression method from within their [Config File](Config-Example).
If you choose to use either the YUI or Google Closure compression methods you will need to specify the path to the jar file and path to your installed Java. You can choose basic CSS and JS minification if you don't want to use these methods.

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


# Cache flushing

A Simple Forum caches data to avoid pointless data fetches from the database. If the user chooses to use MongoDB then the data will be stored in a Mongo Collection. If the user chooses to use disk caching (may be removed later) then the data will be stored in the cache folder located in the root.

To clear this cache you can add ?purge to the end of a url and refresh. This will also force a rebuild of the CSS and Javascript concat files.
