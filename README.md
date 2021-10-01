=======
# Beware - will probably break your project and delete stuff


=======
# Project Configuration Installer: jpmschuler/typo3-extdev-helper

composer package to force-drop CI/CD and QA related stuff into an extension. Beware! This overwrites a lot,
take a look at the payload folder, especially payload/composer.override.json and payload/package.override.json.

This uses an additional package.json as npm offers wonderful git and semver tools.

This is in no way configurable currently, but rather serves as a proof-of-concept. All changes to the files should be overwritten during the next composer install/update probably.
. 
It is focussed on plain typo3 extensions, not sitepackages.

It is focussed on gitlab and requires some env vars set.

Installation:
```sh
composer req jpmschuler/typo3-extdev-helper:*@dev
$(composer config bin-dir)/typo3-extdev-helper-init
```