<?php
$_CONF['db_type']       = 'mysql';
$_CONF['db_user']       = 'smbind';
$_CONF['db_pass']       = 'smbind';
$_CONF['db_host']       = '10.21.32.7';
$_CONF['db_port']       = '3306';
$_CONF['db_db']         = 'smbind';
$_CONF['smarty_path']   = '/usr/share/php/smarty3/';
$_CONF['peardb_path']   = '/usr/share/php';
$_CONF['title']         = 'MyOnline Primary NS management';
$_CONF['footer']        = '<b>PRIMARY site</b> - Switch to <a class=attention href="https://service.myonline.hu/dnsslave/"><b>SECONDARY site</b></a> | You are from: <b>' . $_SERVER['REMOTE_ADDR'] . '</b>';
$_CONF['recaptcha']     = true;
$_CONF['rc_pubkey']     = '6LectQcTAAAAAGLTGJQpjM8eh8YEozuXYFqpLDqG';
$_CONF['rc_privkey']    = '6LectQcTAAAAAGBDl7u5lrbVQmNYtlF37K2RkArR';
$_CONF['nocaptcha']     = array (
                            '78.131.57.83',
                            '194.149.54.36',
                          );
$_CONF['path']          = '/etc/smbind/zones/';
$_CONF['conf']          = '/etc/smbind/smbind.conf';
$_CONF['rollerconf']    = '/etc/smbind/rollrec/zones.rollrec';
?>
