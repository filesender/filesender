---
title: Maintenance
---

# Maintenance


## Node modules
For maintainability's sake we've used NPM for installing the required webshims / polyfill's for the crypto.subtle functionality which is relied upon for the end-to-end encryption built into filesender.
Normally you wouldn't include the nodemodules folder in your project, however because npm is hardly used in filesender, we felt like it would be a hassle whilst upgrading to the new version. So we've included them in the repository as part of the source.

To upgrade these modules for everyone who pulls the new version simply run the following commands

		cd filesender/www/vendor
		npm update 

This will update all the required node modules, you can simply commit the results.

