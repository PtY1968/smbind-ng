# SMBind-ng
This is a forked project from [smbind](http://sourceforge.net/projects/smbind/).

This fork has many improvements, security changes and new features

## Improvements
 * New design (css based, customizable)
 * New .js library
 * Fully separated parts (one .js, one .css and the HTML part)
 * No inline CSS and JS (except the login captcha)
 * Key bindings
 * CSS based inline graphical elements

## Security changes
 * Login with Google ReCaptcha validation
 * Password policy (JS based checking)
 * No password traffic in HTTP channel (JS based encryption)
 * Strict login and session checking

## New features
 * Master and slave zones handling in one application
 * Zone preview
 * Zone checking before commit
 * Users can commit only their checked zones, no others
 * Admins can commit all checked zones (except deleted zones)
 * DNSSEC capabilities - it generetes automatically
  * Roller daemon handling
  * viewing zones with/without DNSSEC encryption
 * Import master zones from many bind sources
 * Check slave zones with zonetransfer
 * IDN capabilities - you just use UTF-8 characters

## Attention
The main repository there is in [my own](https://git.myonline.hu/pty/smbind-ng)
site. Please use that mainly.

## Installation
See [INSTALL.md](INSTALL.md)