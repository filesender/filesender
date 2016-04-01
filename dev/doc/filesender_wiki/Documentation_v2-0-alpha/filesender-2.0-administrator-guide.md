#Admin reference guide


#Table of contents
---


---
---
#Appearance
---
---
##Custom stylesheet

Each template uses “Foundation”.  There are template overrides.  

When you want to override css or add script, like for Fonts: create skin directory in 
www/skin and put it there.  CSS must be named “styles.css”.  In skin: skin/script.js and 
styles.css are immediately interpreted by FileSender.  

Can have other scripts, but must include them in your files or tweak templates/header.php
 to include them.  Can copy it in config/templates/header.php and tweak there.  Start 
 of page with HTML headers etc.  Can add scripts and styles you want there.
 

##Custom scripts
e.g. to ask for feedback etc.


##How upload works
* open file in browser
* ask browser to for the file with fileid

##Logs

[2015-11-16 16:01:29] [rest:info] [user dickyh@ntnu.no] File#54 chunk[96.710.164.480..96715407360] written

Just wrote data for file with Id 54 in database, we wrote chukn from offset <first number> to offset <last number> in bytes.  In this case 5 MB.

Statlog
: list all events in the "event" column

##Tips for maintenance
use config directive "maintenance" to put the instance in maintenance mode.  

##Email details

###Email address validation: uses the PHP built-in facility for checking email address validity 
which these days works well.  The PHPfunction used is filter_var with the 
filter FILTER_VALIDATE_EMAIL. 


#Security considerations

## Unique ID generation

Using random_uid_generation (6 calls to mt_rand , build X-string, put dashes.  
Unique IDs were generated before without collision checking.  Now we check for that 
until we get a real unique one.
