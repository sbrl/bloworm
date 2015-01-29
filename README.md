Bloworm
=======
![The Bloworm Logo](https://raw.githubusercontent.com/sbrl/bloworm/master/images/bloworm%20logo.png)

This is going to be a bookmark storage system where you can tag your bookmarks, and share them with people. It will be self hosted - all you will need to do is clone this repository.

You are welcome to fork this repository and help out.

**Current Phase:** Writing core client alongside api

## Installation

### Requirements
Bloworm requires the following PHP modules:
* `openssl` - used to generate secure random numbers for login tokens

## Libraries
This section contains a list of interesting libraries I have found that may or may not be useful when building this thing:

 - http://www.shayanderson.com/php/php-qr-code-generator-class.htm - A qr code generator
 - https://github.com/kylepaulsen/NanoModal - A cool looking modal dialog box controller

### Still to find
 - website screenshot generator


## Notes
This section contains a bunch of notes that I have made / will make while writing bloworm. Eventually, they will slowly disappear as the help pages are written properly or as I don't need them anymore.

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
Bloworm was created by Starbeamrainbowlabs (https://starbeamrainbowlabs.com/). Bloworm uses several things from other places, all of which are in the table below.

Thing						| Creator				| Link
----------------------------|-----------------------|----------------
Starbeamrainbowlabs Logo	| Mythdael				| (n/a)
Logo						| Mythdael				| (n/a)
Default globe icon			| The Working Group		| http://findicons.com/icon/454617/globe
Background					| Transparent Textures	| http://www.transparenttextures.com/
Fonts						| Google Web Fonts		| https://www.google.com/fonts/
Promise Polyfill			| Taylor Hakes			| https://github.com/taylorhakes/promise-polyfill

## License
Bloworm is currently licensed under the **Creative Commons Attribution Sharealike (CC-BY-SA)** license. If anyone can help me to choose a license from github's list of supported licenses that are specifically designed for code, that would be very helpful since I don't really understand any of them.....

Things in the credits table above are probably not under the CC-BY-SA license. You should check their respective websites for licensing information.
