Structure
---------

action
 - login
	- user - the username
	- pass - the password
 - logout
 - main - required: login
 - tag
	- userid - the uesrid to whom the tags belong
	- tags - list of tags | if any one tag is private, then I login is required
 - search
	- [optional] list - the list to search
	- query - the string to search for
 - add
	- userid - the user to add the bookmark for
	- tags - list of tags to add to the bookmark
	- displaytext - text to display (should defautl to the page's title, or the url if the page doesn't have a title)
	- url - the url to the page
	- imageurl - url to the image that accompanies the bookmark, a screenshot of the website is used if this is left blank or omitted
 - remove
	- userid - the userid of the user for which to perform the action
	- bookmarkid - the bookmark to remove
 - import
 	- userid - the userid performing the action
	- POST data - the file to import
 - export
	- userid - the userid of the user performing the action
	- format - the format to use when exporting
		- html - a HTML file
	- tags - list of tags to export, or the special keyword 'all'
 

Tags
----
### Types
 - public
	 - Anyone can see everything in this tag
 - private
	 - Only you can see things in this tag

This means that if a bookmark is assigned a single public tag, then it will be public even if it has any number of private tags since that one public tag is viewable by those who aren't logged in

### The GET parameter `tags`
The get parameter `tags` should contain a list of tags. Only bookmarks with all of these tags will be selected. Multiple lists of tags can be selected by separating them with the `|` (bar) character. e.g. `news,tech` would select all bookmarks with both the `news` and `tech` tags. `news,tech|code` would select all the bookmarks with both the `news` and the `tech` tags, or the `code` tag. Tags may be negated with an exclamation mark, so `news,!tech` would selected all those with the `news` tag, but if a bookmark contains the `tech` tag, it will be omitted.

`news,tech,!music|cooking,!savoury` selects all those with both the `news` and the `tech` tags, but not the `music` tag, or those with the `cooking` tag, but not the `savoury` tag.