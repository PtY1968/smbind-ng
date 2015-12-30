# SMBind-ng Installation Guide

## Requirements
 * Any kind of webserver with php usage abilities (tested on apache2, lighttpd,
   nginx)
 * php interpreter (5.3 or greater - tested on 5.3)
 * php modules
  * one of mysql, pgsql
  * mdb2
  * mdb2 sql drivers (tested on mysql)
  * cgi
 * smarty (version 2 or newer - tested on v2 and v3)
 * bind (9.3 or newer for dnssec abilities)
 * dnssec-tools (optional for securing dns zones)
 * acl (optional for securing dns zones)
 * SQL server (tested on MySQL)

## Installation

### Bind
Set up your bind, and configure it to access other masters and enable zone
transfer for its slaves.

### SMBind-ng PHP code
Unpack contents to somewhere on your server (eg. /var/www/html/smbind-ng) and
setup your virtual server to access by default the *index.php*.

Create the following directories beside the *index.php* and make it writable by
current webserver user:  
   *tmp*  
   *templates_c*
   
All other directories and files can be write protected.

### Configuration directories and files
1. Create a subdirectory with full permission to user of your webserver for
keeping your zones. You need to make it readable for bind. eg.  
   */etc/smbind-ng*  
   Recommended solution: owner of directory let the root user and bind group,
webserver user let the member of the bind group, and the directory let writable
by owned group.
2. Create a file with same permissions in this directory for saving zone
definitions - eg.  
   *touch smbind-ng.conf.*
3. Create a subdirectory for keeping zone files with write permissions by 
www-data and bind group.

### Modify bind configuration
On your bind options set this folder to use with *directory* option and
*managed-keys* option (folder created at the step 3 above).  
Include the master configuration files into your bind config - what created at 
the step 2.

Restart your bind daemon.

#### *Under bind9.9 or later only*
You need to add your options the  
  *masterfile-format text;*  
line, because these versions keep the zone files as binary format, and you
couldn't preview the slave zones as human readable.

Restart your bind daemon

### Database
Create a database user with full permission to access a non existing database
with any name.  
Log in your database server with that user, and create an empty database.
Take the initial database dump, and load it to this schema with this newly
created user.  
*mysql.sql* is for MySQL, *pgsql.sql* is for PostrgeSQL eg. for MySQL:  
   *mysql -h yourserver -u youruser -pYourP@ssW0rd yourdb <mysql.sql*

### Setup the PHP app
See configuration parameters below

### DNSSEC related options

#### Bind options
In your bind configuration set the following options:

*dnssec-enable yes;*  
*dnssec-validation auto;*  
*dnssec-lookaside auto;*

And then restart your bind daemon.

#### Roller daemon
Create a directory for keeping file of roller daemon, and add write permissions
for the webserver user group. eg.

*setfacl -b /etc/rollrecdir*  
*setfacl -m 'www-data:rwx' /etc/rollrecdir*  

Set up this directory for roller daemon to use this directory for rolling zones.
eg. in your /etc/default/rollerd file use similar option with this:

*DAEMON_OPTS="-rrfile /etc/smbind-ng/rollrec/zones.rollrec"*

And then reload your roller daemon.

## Configuration parameters
The application has a *config.php* file in the *config* directory of the root
of your SMBind-ng webapp directory.

Format: $_CONF['variablename'] = value;

Variables (mark **bold** for the required parameters):

**db_type** - Type of the database (eg. 'mysql')  
**db_user** - Name of the owner of database schema (eg. 'smbind')  
**db_pass** - Password of the user above  
db_host - Resolvable name or IP address of the database host (default:
'localhost')  
db_port - Port number of the database server (default: 3306 or 5432 depends on
db_type)  
**db_db** - Name of the database schema  
**smarty_path** - Place of the smarty installation  
**peardb_path** - place of your PEAR db  
tmp_path - Path of your tmp directory (default: install path/tmp)  
roller_conf - Path of your roller daemon config (configured in DAEMON_OPTS).
Required for DNSSEC abilities.  
isdnssec - enable or disable DNSSEC abilities (true/false)  
recaptcha - enable or disable recaptcha at login screen (true/false)  
rc_pubkey - Your public recaptha key (required for recaptcha)  
rc_privkey - Your private recaptcha key (required for recaptcha)  
nocaptcha - Array of your recaptcha whitelist. If you do not want to recaptcha
when you access the webapp from specified hosts, you need to set up their IP
addresses as followings:  
*array(  
'1.2.3.4',  
'2.3.4.5',  
);*  
title - Title string at the top of your SMBind-ng screen (eg. 'My DNS zones')  
footer - Footer string at the bottom of your SMBind-ng screen (eg. 'Company
Name')  
staticdomain - If you want to access your static files (.css and .js) through
other virtual host, then you need to configure it in your webserver, and just
set it (eg. 'static.mydnsservice.local'). There are only two static files in
your SMBind-ng installation, so I think you don't really need this - but who
knows?  
template - .css and .js name in static directory. The default values is
*default*  
path - Where you store your zonefiles. Default: */etc/smbind-ng/zones/*  
conf - Your included config file. Default: */etc/smbind-ng/smbind-ng.conf*  
namedcheckconf - Place of your binary. Default if found: */usr/sbin/named-
checkconf*  
namedcheckzone - Place of your binary. Default if found: */usr/sbin/named-
checkzone*  
rndc - Place of your binary. Default if found: */usr/sbin/rndc*  
zonesigner - Place of your binary. Default if found: */usr/sbin/zonesigner*  
rollinit - Place of your binary. Default if found: */usr/sbin/rollinit*  
dig - Place of your binary. Default if found: */usr/bin/dig*  

## Access your admin application

http(s)://your.virtualhost.here/path

Global admin username: **admin**  
Initial password: **SMBind-ng2016**