# Changelog

## [alpha.5](https://github.com/tvillega/einar2/releases/tag/alpha.5) (2026-08-04)
## What's Changed
* Upgraded docker image to `tvillega/einar2:alpha.5`
* Fixed routers crashing on first launch
* Added developer mode to use custom Einar2 images
* Added docker image as variable of service archetypes

## [alpha.4](https://github.com/tvillega/einar2/releases/tag/alpha.4) (2026-07-27)
## What's Changed
* Upgraded docker image to `tvillega/einar2:alpha.4`
* Fixed inconsistency across navigation for redirecting &submitted query incorrectly
* Added CSS tables to align form elements into columns
* Moved sanity checks next to forms
* Added check for duplicated networks in submitted form
* Added guard against undefined elements during form checks
* Added check for duplicated services names
* Added check for duplicated interface assignation on service configuration
* Deprecated summary view on service form submittion
* Added check for duplicated ip assignation across service device types

## [alpha.3](https://github.com/tvillega/einar2/releases/tag/alpha.3) (2026-07-26)
## What's Changed
* Upgraded docker image to `tvillega/einar2:alpha.3`
* Fixed service creation iterating over switches rather than device interfaces
