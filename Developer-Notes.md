# ASimpleForum

Make sure there are no comments in the development.json file.

I needed to add this in one of the apache config files.  In my case with the vhosts section
for the website.
* SetEnv AppEnv "development"

# Database Layout

## 'forum' table

The forums and categories are all recorded in the *forums* table.  The *parent* field is set to the *id* of the parent form if it is a sub-forum and is set to 0 otherwise.

The *left* and *right* fields are node numbers are a separate thing and allow the forum record to be easily placed into a tree like structure at run time.  They have no bearing on forum parentage but are kind of affected by it.

For a new forum the left and right node numbers are determined this way: the node numbers of the new forum node  become (parent.right, parent.right + 1) for the new left and right nodes respectively.  The immediate parent node numbers become (parent.left, parent.right+2).  All the nodes occuring after the parent node, in the tree, have +2 added to both node numbers.

A similar thing happens when deleting a forum.  All nodes later in the tree have 2 subtracted from both node numbers. The immediate parent has 2 subtracted only from the right number.

The fields *added* and *updated* store data in PHP time() function format.

![Forum Node Tree](http://i1241.photobucket.com/albums/gg517/KiwiHeretic/Screenshots/forumnodes_zpsb6935176.png)

# Refreshing Pages

During development you geberally want to delete the files in cache/ folder to ensure your code changes are visible immediately in your browser.