Blow Worm
=========
This is going to be a bookmark storage system where you can tag your bookmarks, and share them with people. It will be self hosted - all you will need to do is clone this repository.

You are welcome to fork this repository and help out.

**Current Phase:** Writing core client alongside api

## Installation

### Requirements
Blow Worm requires the following PHP modules:
* `openssl` - used to generate secure random numbers for login tokens

## Libraries
This section contains a list of interesting libraries I have found that may or may not be useful when building this thing:

 - http://www.shayanderson.com/php/php-qr-code-generator-class.htm - A qr code generator
 - https://github.com/kylepaulsen/NanoModal - A cool looking modal dialog box controller

### Still to find
 - website screenshot generator


## Notes
This section contains a bunch of notes that I have made / will make while writing blow worm. Eventually, they will slowly disappear as the help pages are written properly or as I don't need them anymore.

### File Structure
- functions.core.php - The core fnctions for the server side API
- api.php - The server side API
- password_cost_finder.php - A simple script to find the cost for password hashing that is right for your server.
- settings.php - A file full of configurable settings that you can change.
- data/
	- sessionkeys.json - A json file full of active session keys
	- userlist.json - A json file that lists all the user accounts
	- users/ - Folder to hold a folder for each user
		- &lt;username&gt;/
			- password - a hashed version of the user's password
			- bookmarks.json - A json file full of bookmarks
			- tags.json - The tag cache
			- isadmin - Contains true if the user is an admin, false otherwise

## Credits
 - Default globe icon - http://findicons.com/icon/454617/globe
