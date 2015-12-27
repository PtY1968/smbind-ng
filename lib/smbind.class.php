<?php

    require_once 'MDB2.php';
    require_once 'punycode.class.php';

    $db = array();

    define('COMMENT_PATTERN', '/(\s+;|^;)[^\n]*/i');
    define('ORIGIN_PATTERN', '/^\$ORIGIN\s+(.+)\.\s*/msi');
    define('SOA_BEGINS_PATTERN', '/^([^\s]+)?(\s+[\d\w]+)?\s+IN\s+SOA\s+/msi');
    define('FULL_SOA_PATTERN', '/^([^\s]+)?(\s+[\d\w]+)?\s+IN\s+SOA\s+([-\w\d.]*)\.\s+([-\w\d.]*)\s+\((.*)\)/msi');
    define('TIMES_PATTERN', '/\s*(\d+\w?)\s+(\d+\w?)\s+(\d+\w?)\s+(\d+\w?)\s+(\d+\w?)/msi');
    define('TXT_PATTERN', '/^\"(.*)\"/msi');
    define('MX_PATTERN', '/^(\d+)\s+([^\s]*)/msi');
    define('TYPE_PATTERN', '(A|A6|AAAA|AFSDB|APL|ATMA|AXFR|CERT|CNAME|DNAME|DNSKEY|DS|EID|GPOS|HINFO|ISDN|IXFR|KEY|KX|LOC|MAILB|MINFO|MX|NAPTR|NIMLOC|NS|NSAP|NSAP-PTR|NSEC|NXT|OPT|PTR|PX|RP|RRSIG|RT|SIG|SINK|SRV|SSHFP|TKEY|TSIG|TXT|WKS|X25)');
    define('RECORD_PATTERN', '/^([^\s]+)?(\s+[\d][\d\w]*)?(\s+IN)?\s+'.TYPE_PATTERN.'\s+([^\s].*$)/msi');
    define('BIND_TIME_PATTERN', '/^(\d+)([smhdw])/');
    define('IDN_PUNY_PATTERN', '/[^a-z0-9-]/i');
    define('IDN_UTF_PATTERN', '/[^a-z0-9\x80-\xFF-]/i');
    define('BIND_ZONENAME_PATTERN', '/zone\s+"([^"]+)".*$/msi');
    define('BIND_ZONEDATA_PATTERN', '/^([^\s]+)\s+"?([^"]+)"?$/');
    define('BIND_SLAVEMASTER_PATTERN', '/^\{([^\};]+);}$/');

    function idnToHost($idn) {
        preg_match(IDN_UTF_PATTERN, $idn, $match);
        if (count($match) == 0) {
            $out = ($idn > '') ? Punycode::encodeHostName($idn) : '';
            return ((strlen($out) > 4) && (substr($out, 0, 4) == 'xn--')) ? $out : $idn;
        }
        $tags = explode($match[0], $idn);
        $ret = array();
        foreach ($tags as $tag) {
            $ret[] = ($tag == '') ? '' : idnToHost($tag);
        }
        return implode($match[0], $ret);
    }

    function hostToIdn($host) {
        preg_match(IDN_PUNY_PATTERN, $host, $match);
        if (count($match) == 0) {
            return ((strlen($host) > 4) && (substr($host, 0, 4) == 'xn--')) ? Punycode::decodeHostName($host) : $host;
        }
        $tags = explode($match[0], $host);
        $ret = array();
        foreach ($tags as $tag) {
            $ret[] = ($tag == '') ? '' : hostToIdn($tag, $match[0]);
        }
        return implode($match[0], $ret);
    }

    class bindConfig {

        private $zonedef = array();
        private $err = '';

        public function __debugInfo() {
            return array(
                'zonedef'   => $this->zonedef,
                'err'       => $this->err,
            );
        }

        public function __construct($file = NULL) {
            if (!is_null($file)) {
                return $this->loadConfig($file);
            } else {
                $this->zonedef = array();
                return true;
            }
        }

        public function getErr() {
            return $this->err;
        }

        public function loadConfig($fpath) {
            if (!is_string($fpath)) {
                $this->err .= "Only string parameter accepted\n";
                error_log($this->err);
                return false;
            }
            if ($fpath == '') {
                $this->err .= "Given string is empty\n";
                error_log($this->err);
                return false;
            }
            if (!file_exists($fpath)) {
                $this->err .= "File doesn't exist: '" . $fpath . "'\n";
                error_log($this->err);
                return false;
            }
            $conf = file($fpath,  FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $one = array();
            $name = '';
            foreach ($conf as $line) {
                $line = preg_replace('/(\/\/.*|;)$/', '', trim($line));
                if ($line == '') {
                    continue;
                }
                if ($name == '') {
                    preg_match(BIND_ZONENAME_PATTERN, $line, $match);
                    if (!isset($match[1])) {
                        $this->err .= "Read config error\n";
                        error_log($this->err);
                        return false;
                    }
                    $name = $match[1];
                } else {
                    if ($line == '}') {
                        $this->zonedef[$name] = $one;
                        $one = array();
                        $name = '';
                    } else {
                        preg_match(BIND_ZONEDATA_PATTERN, $line, $match);
                        if ((!isset($match[1])) ||
                            (!isset($match[2]))) {
                            $this->err .= "Read config error\n";
                            error_log($this->err);
                            return false;
                        }
                        $key = $match[1];
                        $data = $match[2];
                        if ($key == 'masters') {
                            preg_match(BIND_SLAVEMASTER_PATTERN, $data, $match);
                            if (!isset($match[1])) {
                                $this->err .= "Read config error\n";
                                error_log($this->err);
                                return false;
                            }
                            $data = $match[1];
                        }
                        $one[$key] = $data;
                    }
                }
            }
            return true;
        }

        public function addConfig($name, $data) {
            $name = strval($name);
            if (($name == '') ||
                (!is_array($data))) {
                $this->err .= "Cannot set configset\n";
                error_log($this->err);
                return false;
            }
            $this->zonedef[$name] = $data;
            return true;
        }

        public function eraseConfig($name) {
            $name = strval($name);
            if ($name == '') {
                $this->err .= "Cannot set configset\n";
                error_log($this->err);
                return false;
            }
            $this->zonedef[$name] = array();
            return true;
        }

        public function saveConfig($fname) {
            if (!is_string($fname)) {
                $this->err .= "Only string parameter accepted\n";
                error_log($this->err);
                return false;
            }
            if ($fname == '') {
                $this->err .= "Given string is empty\n";
                error_log($this->err);
                return false;
            }
            $fh = fopen($fname,'w');
            fwrite($fh, "// SMbind-ng configuration\n\n");
            foreach($this->zonedef as $zone => $def) {
                if (count($def) == 0) {
                    continue;
                }
                fwrite($fh, "// Zone " . hostToIdn($zone) . " (" . $def['type'] . ")\n");
                fwrite($fh, "zone \"" . $zone . "\" {\n");
                foreach ($def as $key => $param) {
                    switch ($key) {
                        case 'file':
                            $data = "\"" . $param . "\"";
                            break;
                        case 'masters':
                            $data = "{" . $param . ";}";
                            break;
                        default:
                            $data = $param;
                    }
                    fwrite($fh, str_pad($key, 10, " ", STR_PAD_LEFT) . " " . $data . ";\n");
                }
                fwrite($fh,"};\n\n");
            }
            fclose($fh);
            return true;
        }
    }

    class Configuration {

        private $info = array();

        public function __debugInfo() {
            return $this->info;
        }

        public function __construct($path) {
            global $db;
            if (is_string($path)) {
                $_CONF['smbind_ng'] = $path;
                $_CONF['title'] = "SMBind-ng";
                $_CONF['footer'] = $_CONF['title'] . " v0.91b";
                $_CONF['marker'] = "Forked by PtY 2015(GPL)";
                $_CONF['template'] = "default";
                $_CONF['recaptcha'] = false;
                $_CONF['tmp_path'] = $path . "tmp";
                $_CONF['nocaptcha'] = array();
                $_CONF['path'] = "/etc/smbind-ng/zones/";
                $_CONF['conf'] = "/etc/smbind-ng/smbind-ng.conf";
                $_CONF['namedcheckconf'] = (is_executable("/usr/sbin/named-checkconf")) ? "/usr/sbin/named-checkconf" : "";
                $_CONF['namedcheckzone'] = (is_executable("/usr/sbin/named-checkzone")) ? "/usr/sbin/named-checkzone" : "";
                $_CONF['rndc'] = (is_executable("/usr/sbin/rndc")) ? "/usr/sbin/rndc" : "";
                $_CONF['zonesigner'] = "/usr/sbin/zonesigner";
                $_CONF['rollinit'] = "/usr/sbin/rollinit";
                $_CONF['isdnssec'] = false;
                $_CONF['dig'] = (is_executable("/usr/bin/dig")) ? "/usr/bin/dig" : "";
                include $path . 'config/config.php';
                $_CONF['zonesigner'] = (is_executable($_CONF['zonesigner'])) ? $_CONF['zonesigner'] : "";
                $_CONF['rollinit'] = (is_executable($_CONF['rollinit'])) ? $_CONF['rollinit'] : "";
                $_CONF['isdnssec'] = (($_CONF['isdnssec'] === true)  && ($_CONF['zonesigner'] != "") && ($_CONF['rollinit'] != "")) ? true : false;
                $_CONF['recaptcha'] = (($_CONF['recaptcha'] === true) && (strlen($_CONF['rc_pubkey']) > 0) && (strlen($_CONF['rc_privkey']) > 0)) ? true : false;
                if(!isset($_CONF['db_host'])) {
                    $_CONF['db_host'] = 'localhost';
                }
                if(!isset($_CONF['db_port'])) {
                    switch ($_CONF['db_type']) {
                        case 'mysql':
                        case 'mysqli':
                            $_CONF['db_port'] = '3306';
                            break;
                        case 'pgsql':
                            $_CONF['db_port'] = '5432';
                            break;
                    }
                }
                $dsn = array (
                    'phptype'  => $_CONF['db_type'],
                    'username' => $_CONF['db_user'],
                    'password' => $_CONF['db_pass'],
                    'database' => $_CONF['db_db'],
                    'hostspec' => $_CONF['db_host'],
                    'port'     => $_CONF['db_port'],
                    'charset'  => 'utf8',
                );
                $dbopt = array('persistent' => true,);
                $db = MDB2::factory($dsn, $dbopt);
                if (MDB2::isError($db)) {
                    die("Database error: " . MDB2::errorMessage($dbconnect));
                } else {
                    $db->setFetchMode(MDB2_FETCHMODE_ASSOC);
                }
                $query = $db->query("SELECT prefkey, prefval FROM options WHERE preftype = 'normal'");
                if (MDB2::isError($query)) {
                    $err = $query->getMessage() . "\n" . $query->getDebugInfo();
                    error_log($err);
                    die($err);
                }
                while ($res = $query->fetchRow()) {
                    $key = $res['prefkey'];
                    switch ($key) {
                        case 'prins':
                        case 'secns':
                            $keyparam = substr($key, 0, 3) . '_d' . substr($key, -2);
                            break;
                        default:
                            $keyparam = $key;
                    }
                    $_CONF[$keyparam] = $res['prefval'];
                }
                $query = $db->query("SELECT prefkey FROM options WHERE prefval = 'on' AND preftype = 'record' ORDER BY prefkey");
                $_CONF['parameters'] = array();
                while ($res = $query->fetchRow()) {
                    $_CONF['parameters'][] = $res['prefkey'];
                }
                $query = $db->query("SELECT DISTINCT type FROM records");
                while ($res = $query->fetchRow()) {
                    $_CONF['parameters'][] = $res['type'];
                }
                $_CONF['parameters'] = array_unique($_CONF['parameters']);
                $_CONF['dsn'] = $dsn;
                $this->info = $_CONF;
            }
        }

        public function __call($method, $args) {
            if (is_string($method)) {
                $m = $this->from_CC(substr($method, 3, strlen($method) - 3));
                return array_key_exists($m, $this->info) ? $this->info[$m] : false;
            }
        }

        public function __set($param, $arg) {
            return true;
        }

        public function __get($param) {
            $name = strtolower($param);
            $return = (array_key_exists($name, $this->info)) ? $this->info[$name] : NULL;
            if (is_null($return)) {
                switch ($name) {
                    case 'range':
                        $return = 10;
                        break;
                    default:
                        $return = '';
                }
            }
            return $return;
        }

        private function from_CC($str) {
            $str = strtolower($str);
            $func = create_function('$c', 'return "_" . strtolower($c[1]);');
            return preg_replace_callback('/([A-Z])/', $func, $str);
        }

        public function isExists($id) {
            return isset($this->info[strtolower($id)]);
        }

    }

    class Session {

        private $usr = '';
        private $psw = '';
        private $inc = NULL;

        public function __debugInfo() {
            return array(
                'usr' => $this->usr,
                'psw' => $this->psw,
                'inc' => $this->inc,
            );
        }

        public function __construct() {
            session_start();
            if ((isset($_SESSION['i'])) && (is_numeric($_SESSION['i'])) && ($_SESSION['i'] > 0)) {
                $this->inc = $_SESSION['i'];
                $this->inc++;
                if ((isset($_SESSION['p'])) && (is_string($_SESSION['p']))) {
                    $this->psw = $_SESSION['p'];
                    if ((isset($_SESSION['u'])) && (is_string($_SESSION['u']))) {
                        $this->usr = $_SESSION['u'];
                    } else {
                        $this->psw = '';
                    }
                }
            } else {
                $this->inc = 1;
            }
            $_SESSION['i'] = $this->inc;
        }

        public function login($user, $pass) {
            $_SESSION['u'] = $user;
            $_SESSION['p'] = $pass;
            $usr = $user;
            $psw = $pass;
        }

        public function destroy() {
            $this->usr = '';
            $this->psw = '';
            $this->inc = 0;
            $_SESSION = array();
            session_destroy();
        }

        public function isEnoughOld() {
            return $this->inc > 3;
        }
    }

    class User {
        private $data   = array(
            'id'        => 0,
            'username'  => '',
            'realname'  => '',
            'password'  => '',
            'admin'     => false,
        );
        private $mzones = array();
        private $szones = array();
        private $err    = '';
        private $db     = array();

        public function __debugInfo() {
            return array(
                'data'      => $this->data,
                'mzones'    => $this->mzones,
                'szones'    => $this->szones,
                'err'       => $this->err,
                'db'        => NULL,
            );
        }

        public function __construct($uid = NULL) {
            global $db;
            $this->db = &$db;
            if ((is_null($uid)) &&
                (isset($_SESSION['i'])) &&
                ($_SESSION['i'] > 1) &&
                (isset($_SESSION['u'])) &&
                ($_SESSION['p'])) {
                $user = $_SESSION['u'];
                $pass = $_SESSION['p'];
                if (is_object($db)) {
                    if ((is_string($user)) && (is_string($pass))) {
                        $res = $this->db->query("SELECT * FROM users WHERE username ='" . $user . "' AND password = '" . $pass . "'");
                        if (MDB2::isError($res)) {
                            $this->err .= $res->getMessage() . "\n" . $res->getDebugInfo();
                            error_log($this->err);
                            return false;
                        } elseif ($res->numRows() == 0) {
                            $this->err .= "Username or password does not match";
                            error_log($this->err);
                            return false;
                        } else {
                            $row = $res->fetchRow();
                            foreach ($row as $key => $value) {
                                switch ($key) {
                                    case 'id':
                                        $this->data[$key] = intval($value);
                                        break;
                                    case 'admin':
                                        $this->data[$key] = ($value == 'yes');
                                        break;
                                    default:
                                        $this->data[$key] = strval($value);
                                }
                            }
                            $this->loadUserZones();
                            return true;
                        }
                    }
                }
            } elseif (((is_array($uid)) &&
                (is_numeric($uid['id']))) ||
                (is_numeric($uid))) {
                $aid = (is_array($uid)) ? $uid : array('id' => $uid);
                if ($aid['id'] == 0) {
                    foreach ($aid as $key => $value) {
                        $this->data[$key] = $value;
                    }
                    $this->data['username'] = ((isset($aid['username'])) && ($aid['username'] > '')) ? $aid['username'] : 'NONE';
                    $this->data['realname'] = ((isset($aid['realname'])) && ($aid['realname'] > '')) ? $aid['realname'] : $this->data['username'];
                    $this->data['password'] = ((isset($aid['password'])) && ($aid['password'] > '')) ? $aid['password'] : 'NONE';
                    $this->data['admin'] = ((isset($aid['admin'])) && ($aid['admin'] > '') && (($aid['admin'] == 'yes') || ($aid['admin'] == 'no'))) ? $aid['admin'] : 'no';
                    $res = $this->db->query("SELECT * FROM users WHERE username='" . $this->data['username'] . "'");
                    if (MDB2::isError($res)) {
                        $this->err .= $res->getMessage() . "\n" . $res->getDebugInfo();
                        error_log($this->err);
                        return false;
                    }
                    if ($res->numRows() > 0) {
                        $this->err .= ($this->data['username'] == 'NONE') ? "Previous error cause a problem\n" : "User already exists\n";
                        error_log($this->err);
                        return false;
                    }
                    $res = $this->db->query("INSERT INTO users (username, realname, admin, password) VALUES ('" .
                        $this->data['username'] . "', '" .
                        $this->data['realname'] . "', '" .
                        $this->data['admin'] . "', '" .
                        $this->data['password'] . "')");
                    if (MDB2::isError($res)) {
                        $this->err .= $res->getMessage() . "\n" . $res->getDebugInfo();
                        error_log($this->err);
                        return false;
                    }
                    $res = $this->db->query("SELECT id FROM users WHERE username='" .
                    $this->data['username'] . "' AND realname='" .
                    $this->data['realname'] . "' AND password='" .
                    $this->data['password'] . "'");
                    if (MDB2::isError($res)) {
                        $this->err .= $res->getMessage() . "\n" . $res->getDebugInfo();
                        error_log($this->err);
                        return false;
                    }
                    $ret = $res->fetchRow();
                    $this->data['id'] = $ret['id'];
                    return true;
                } else {
                    $self->data['id'] = $aid['id'];
                    $res = $this->db->query("SELECT * FROM users WHERE id = '" . $self->data['id'] . "'");
                    if (MDB2::isError($res)) {
                        $this->err .= $res->getMessage() . "\n" . $res->getDebugInfo();
                        error_log($this->err);
                        return false;
                    } elseif ($res->numRows() == 0) {
                        $this->err .= "User not found with this id = " . $self->data['id'];
                        error_log($this->err);
                        return false;
                    } else {
                        $row = $res->fetchRow();
                        foreach ($row as $key => $value) {
                            switch ($key) {
                                case 'id':
                                    $this->data[$key] = intval($value);
                                    break;
                                case 'admin':
                                    $this->data[$key] = ($value == 'yes');
                                    break;
                                default:
                                    $this->data[$key] = strval($value);
                            }
                        }
                    }
                }
            }
        }

        public function loadUserZones() {
            $WHERE = '';
            if (!$this->isAdmin()) {
                $WHERE .= "WHERE owner = '" . $this->getId() . "' ";
            }
            $res = $this->db->query("SELECT id FROM zones " . $WHERE . "ORDER BY name");
            if (MDB2::isError($res)) {
                $this->err .= $res->getMessage() . "\n" . $res->getDebugInfo();
                error_log($this->err);
                return false;
            } else {
                $this->mzones = array();
                while ($rec = $res->fetchRow()) {
                    $this->mzones[] = $rec['id'];
                }
                $res = $this->db->query("SELECT id FROM slave_zones " . $WHERE . "ORDER BY name");
                if (MDB2::isError($res)) {
                    $this->err .= $res->getMessage() . "\n" . $res->getDebugInfo();
                    error_log($this->err);
                    return false;
                } else {
                    $this->szones = array();
                    while ($rec = $res->fetchRow()) {
                        $this->szones[] = $rec['id'];
                    }
                }
            }
            return true;
        }

        public function getUnvalidatedZones($zonetype = NULL) {
            $ret = array();
            if (is_string($zonetype)) {
                $tag = '';
                $db = NULL;
                switch ($zonetype) {
                    case 'slave':
                        $tag = 'slave_';
                        $cnt = sizeof($this->szones);
                        $lst = implode(',', $this->szones);
                        break;
                    case 'master':
                        $cnt = sizeof($this->mzones);
                        $lst = implode(',', $this->mzones);
                        break;
                }
                if ($cnt > 0) {
                    $res = $this->db->query("SELECT id, name FROM " . $tag . "zones WHERE id IN (" . $lst . ") AND valid <> 'yes' and updated <> 'del'");
                    if (MDB2::isError($res)) {
                        $this->err .= $res->getMessage() . "\n" . $res->getDebugInfo();
                        error_log($this->err);
                        return false;
                    } else {
                        while ($recd = $res->fetchRow()) {
                            $recd['name'] = hostToIdn($recd['name']);
                            $ret[] = $recd;
                        }
                    }
                }
            }
            return $ret;
        }
        public function getDeletedZones($zonetype = NULL) {
            $ret = array();
            if (is_string($zonetype)) {
                $tag = '';
                $db = NULL;
                switch ($zonetype) {
                    case 'slave':
                        $tag = 'slave_';
                        $cnt = sizeof($this->szones);
                        $lst = implode(',', $this->szones);
                        break;
                    case 'master':
                        $cnt = sizeof($this->mzones);
                        $lst = implode(',', $this->mzones);
                        break;
                }
                if ($cnt > 0) {
                    $res = $this->db->query("SELECT id, name FROM " . $tag . "zones WHERE owner = " . $this->data['id'] . " AND updated = 'del'");
                    if (MDB2::isError($res)) {
                        $this->err .= $res->getMessage() . "\n" . $res->getDebugInfo();
                        error_log($this->err);
                        return false;
                    } else {
                        while ($recd = $res->fetchRow()) {
                            $recd['name'] = hostToIdn($recd['name']);
                            $ret[] = $recd;
                        }
                    }
                }
            }
            return $ret;
        }

        public function getCommitableZones($zonetype = NULL) {
            $ret = array();
            if (is_string($zonetype)) {
                $tag = '';
                $db = NULL;
                switch ($zonetype) {
                    case 'slave':
                        $tag = 'slave_';
                        $cnt = sizeof($this->szones);
                        $lst = implode(',', $this->szones);
                        break;
                    case 'master':
                        $cnt = sizeof($this->mzones);
                        $lst = implode(',', $this->mzones);
                        break;
                }
                if ($cnt > 0) {
                    $res = $this->db->query("SELECT id, name FROM " . $tag . "zones WHERE id IN (" . $lst . ") AND valid = 'yes' AND updated = 'yes'");
                    if (MDB2::isError($res)) {
                        $this->err .= $res->getMessage() . "\n" . $res->getDebugInfo();
                        error_log($this->err);
                        return false;
                    } else {
                        while ($recd = $res->fetchRow()) {
                            $recd['name'] = hostToIdn($recd['name']);
                            $ret[] = $recd;
                        }
                    }
                }
            }
            return $ret;
        }

        public function eraseUser() {
            $this->loadUserZones();
            $mz =array();
            foreach ($this->mzones as $master) {
                $mz = new masterRecord($master);
                $mz->loadZoneHead();
                $mzh = $mz->getZoneHead();
                $mzh['owner'] = 1;
                $mz->setZoneHead($mzh);
                $mz->saveZoneHead();
            }
            $mz =array();
            $sz =array();
            foreach ($this->szones as $slave) {
                $sz = new slaveRecord($slave);
                $sz->loadZoneHead();
                $szh = $sz->getZoneHead();
                $szh['owner'] = 1;
                $sz->setZoneHead($szh);
                $sz->saveZoneHead();
            }
            $sz =array();
            $res = $this->db->query("DELETE FROM users WHERE id = " . $this->data['id']);
            if (MDB2::isError($res)) {
                $this->err .= $res->getMessage() . "\n" . $res->getDebugInfo();
                error_log($this->err);
                return false;
            }
            return true;
        }

        public function getMasters($type = 'all') {
            $out = array();
            if ($type == 'live') {
                foreach ($this->mzones as $zoneid) {
                    $zone = new masterZone(array('id' => intval($zoneid)));
                    $zone->loadZoneHead();
                    $head = $zone->getZoneHeadRaw();
                    if ($head['updated'] != 'del') {
                        $out[] = intval($zoneid);
                    }
                }
            } else {
                $out = $this->mzones;
            }
            return $out;
        }

        public function getSlaves($type = 'all') {
            $out = array();
            if ($type == 'live') {
                foreach ($this->szones as $zoneid) {
                    $zone = new slaveZone(array('id' => intval($zoneid)));
                    $zone->loadZoneHead();
                    $head = $zone->getZoneHeadRaw();
                    if ($head['updated'] != 'del') {
                        $out[] = intval($zoneid);
                    }
                }
            } else {
                $out = $this->szones;
            }
            return $out;
        }

        public function isOwned($id, $type, $state = 'all') {
            $zarr = array();
            switch ($type) {
                case 'master':
                    $zarr = $this->getMasters($state);
                    break;
                case 'slave':
                    $zarr = $this->getSlaves($state);
                    break;
            }
            foreach ($zarr as $zid) {
                if ($zid == $id) {
                    return true;
                }
            }
            return false;
        }

        public function getName() {
            return $this->data['username'];
        }

        public function getErr() {
            return $this->err;
        }

        public function getFullName() {
            return $this->data['realname'];
        }

        public function isAdmin() {
            return $this->data['admin'];
        }

        public function getId() {
            return $this->data['id'];
        }

        public function getPasswordHash() {
            return $this->data['password'];
        }

        public function getUser() {
            $arr = array();
            foreach (array('id', 'username', 'realname', 'admin') as $key) {
                $arr[$key] = $this->data[$key];
            }
            return $arr;
        }

        public function getAllusers() {
            $out = array();
            $res = $this->db->query("SELECT id, username, realname, admin FROM users ORDER BY realname");
            if (MDB2::isError($res)) {
                $this->err .= $res->getMessage() . "\n" . $res->getDebugInfo();
                error_log($this->err);
            } else {
                while ($row = $res->fetchRow()) {
                    $out[] = $row;
                }
            }
            return $out;
        }

        public function loadUserById() {
            $res = $this->db->query("SELECT * FROM users WHERE id = " . $this->data['id']);
            if (MDB2::isError($res)) {
                $this->err .= $res->getMessage() . "\n" . $res->getDebugInfo();
                error_log($this->err);
                return false;
            } elseif ($res->numRows() == 0) {
                $this->err .= "User not found";
                error_log($this->err);
                return false;
            } else {
                $row = $res->fetchRow();
                foreach ($row as $key => $value) {
                    switch ($key) {
                        case 'id':
                            $this->data[$key] = intval($value);
                            break;
                        case 'admin':
                            $this->data[$key] = ($value == 'yes');
                            break;
                        default:
                            $this->data[$key] = strval($value);
                    }
                }
                return true;
            }
        }

        public function set($rname = NULL, $pass = NULL, $adm = NULL) {
            if ((isset($pass)) || (isset($adm)) || (isset($rname))) {
                $pstr = ((is_null($pass)) || (strlen($pass) != 32)) ? "" : "password = '" . $pass . "'";
                $astr = ((is_null($adm)) || (($adm != 'yes') && ($adm != 'no'))) ? "" : "admin = '" . $adm . "'";
                $rnstr = ((is_null($rname)) || ($rname == '')) ? "" : "realname = '" . $rname . "'";
                $setstr = $pstr;
                if ($astr > '') {
                    $setstr .= ($setstr != "") ? ", " . $astr : $astr;
                }
                if ($rnstr > '') {
                    $setstr .= ($setstr != "") ? ", " . $rnstr : $rnstr;
                }
                if ($setstr > "") {
                    $res = $this->db->query("UPDATE users SET " . $setstr . " WHERE id = " . $this->data['id']);
                    if (MDB2::isError($res)) {
                        $this->err .= $res->getMessage() . "\n" . $res->getDebugInfo();
                        error_log($this->err);
                        return false;
                    }
                    return $this->loadUserById();
                } else {
                    return true;
                }
            } else {
                return false;
            }
        }
    }

    class masterRecord {

        private $record = array(
            'id'        => NULL,
            'zone'      => NULL,
            'host'      => '',
            'type'      => '',
            'pri'       => 0,
            'destination' => '',
            'ttl'       => 0,
        );
        private $db     = NULL;
        private $err    = '';

        public function __debugInfo() {
            return array(
                'record'    => $this->record,
                'err'       => $this->err,
                'db'        => NULL,
            );
        }

        public function __construct($param = NULL) {
            global $db;
            if (is_object($db)) {
                $this->db = &$db;
            }
            if (!is_null($param)) {
                return $this->setRecord($param);
            }
            return true;
        }

        public function getId() {
            return $this->record['id'];
        }

        private function fill_record($param) {
            foreach ($param as $key => $value) {
                switch ($key) {
                    case 'id':
                    case 'pri':
                    case 'ttl':
                    case 'zone':
                        $this->record[$key] = intval($value);
                        break;
                    case 'host':
                        $this->record[$key] = idnToHost($value);
                        break;
                    case 'type':
                        $this->record[$key] = strtoupper($value);
                        break;
                    case 'destination':
                        switch ($this->record['type']) {
                            case 'MX':
                            case 'CNAME':
                            case 'SRV':
                            case 'PTR':
                            case 'NS':
                                $this->record[$key] = idnToHost($value);
                                break;
                            default:
                                $this->record[$key] = strval($value);
                        }
                        break;
                    default:
                        $this->record[$key] = $value;
                }
            }
        }

        public function setRecord($param) {
            if (is_string($param)) {
                if (!$this->parseRecord($param)) {
                    return false;
                }
            } elseif (is_numeric($param)) {
                $this->record['id'] = $param;
            } elseif (is_array($param)) {
                $this->fill_record($param);
            } else {
                ob_start();
                var_dump($param);
                $this->err .= "Unidentified parameter" . "\n" . ob_get_clean();
                error_log($this->err);
                return false;
            }
            return true;
        }

        private function bind_time_format($value) {
            if (preg_match(BIND_TIME_PATTERN, strtolower($value), $match)) {
                $value = $match[1];
                switch ($match[2]) {
                    case "s":
                        $multiplier = 1;
                        break;
                    case "m":
                        $multiplier = 60;
                        break;
                    case "h":
                        $multiplier = 3600;
                        break;
                    case "d":
                        $multiplier = 86400;
                        break;
                    case "w":
                        $multiplier = 604800;
                        break;
                }
                $value = $value*$multiplier;
            }
            return $value;
        }

        private function parseRecord($buffer) {
            if (preg_match(RECORD_PATTERN, $buffer, $match)) {
                $this->record['host'] = $match[1];
                if ($this->record['host'] == '') {
                    $this->record['host'] = '@';
                }
                $this->record['type'] = strtoupper($match[4]);
                if (isset($match[2])) {
                    $this->record['ttl'] = intval($this->bind_time_format($match[2]));
                } else {
                    $this->record['ttl'] = 0;
                }
                switch ($this->record['type']) {
                    case 'MX':
                        if (preg_match(MX_PATTERN, $match[5], $match)) {
                            $this->record['pri'] = intval($match[1]);
                            $this->record['destination'] = idnToHost($match[2]);
                        } else {
                            ob_start();
                            var_dump($buffer);
                            $this->err .= "MX cannot be parsed" . "\n" . ob_get_clean();
                            error_log($this->err);
                            return NULL;
                        }
                        break;
                    case 'SRV':
                    case 'CNAME':
                    case 'NS':
                    case 'PTR':
                        $this->record['destination'] = idnToHost($match[5]);
                        break;
                    case 'TXT':
                        $this->record['destination'] = idnToHost(preg_replace('/(^"+|"+$|"+\s*"+)/msi', '', trim($match[5])));
                        break;
                    default:
                        $this->record['destination'] = $match[5];
                }
                return true;
            } else {
                ob_start();
                var_dump($buffer);
                $this->err .= "Record cannot be parsed" . "\n" . ob_get_clean();
                error_log($this->err);
                return false;
            }
        }

        public function getRecordRaw() {
            return $this->record;
        }

        public function getRecord() {
            $out = $this->record;
            $out['host'] = hostToIdn($out['host']);
            switch ($out['type']) {
                case 'MX':
                case 'SRV':
                case 'NS':
                case 'CNAME':
                case 'PTR':
                    $out['destination'] = hostToIdn($out['destination']);
            }
            return $out;
        }

        private function is_identified() {
            return (is_numeric($this->record['id']) && ($this->record['id'] > 0));
        }

        public function loadRecord() {
            if ($this->is_identified()) {
                $res = $this->db->query('SELECT * FROM records WHERE id =' . $this->record['id']);
                if (MDB2::isError($res)) {
                    $this->err .= $res->getMessage() . "\n" . $res->getDebugInfo();
                    error_log($this->err);
                    return false;
                } else {
                    $row = $res->fetchRow();
                    if (is_array($row)) {
                        $this->fill_record($row);
                        return true;
                    } else {
                        return NULL;
                    }
                }
            } else {
                ob_start();
                var_dump($this-record);
                $this->err .= "Record is not identified" . "\n" . ob_get_clean();
                error_log($this->err);
                return false;
            }
        }

        public function getErr() {
            return $this->err;
        }

        private function is_complete() {
            return (($this->record['zone'] > 0) &&
                    ($this->record['type'] > '') &&
                    (
                        ($this->record['host'] > '') ||
                        ($this->record['destination'] > '')
                    ));
        }

        private function find_record() {
            $res = $this->db->query("SELECT id FROM records WHERE " .
                "zone = " . $this->record['zone'] . " AND " .
                "host = '" . $this->record['host'] . "' AND " .
                "ttl = " . $this->record['ttl'] . " AND " .
                "type = '" . $this->record['type'] . "' AND " .
                "pri = " . $this->record['pri'] . " AND " .
                "destination = '" . $this->record['destination'] . "'"
            );
            if (MDB2::isError($res)) {
                $this->err .= $res->getMessage() . "\n" . $res->getDebugInfo();
                error_log($this->err);
                return 0;
            } else {
                $value = $res->fetchRow();
                return $value['id'];
            }
        }

        public function saveRecord() {
            if ($this->is_complete()) {
                if ($this->record['host'] == '') {
                    $this->record['host'] = '@';
                } elseif ($this->record['destination'] == '') {
                    $this->record['destination'] = '@';
                }
                if ($this->record['type'] == 'MX') {
                    $this->record['pri'] = ($this->record['pri'] == 0) ? 10 : $this->record['pri'];
                } else {
                    $this->record['pri'] = 0;
                }
                if ((is_numeric($this->record['id'])) && ($this->record['id'] > 0)) {
                    $res = $this->db->query("UPDATE records SET " .
                        "zone = " . $this->record['zone'] . ", " .
                        "host = '" . $this->record['host'] . "', " .
                        "ttl = " . $this->record['ttl'] . ", " .
                        "type = '" . $this->record['type'] . "', " .
                        "pri = " . $this->record['pri'] . ", " .
                        "destination = '" . $this->record['destination'] .
                        "' WHERE id = " . $this->record['id']
                    );
                    if (MDB2::isError($res)) {
                        $this->err .= $res->getMessage() . "\n" . $res->getDebugInfo();
                        error_log($this->err);
                        return false;
                    } else {
                        return $this->loadRecord();
                    }
                } else {
                    $id = $this->find_record();
                    if ($id > 0) {
                        $this->record['id'] = $id;
                        return true;
                    } else {
                        $res = $this->db->query("INSERT INTO records (zone, host, ttl, type, pri, destination) VALUES (" .
                            $this->record['zone'] . ", '" .
                            $this->record['host'] . "', " .
                            $this->record['ttl'] . ", '" .
                            $this->record['type'] . "', " .
                            $this->record['pri'] . ", '" .
                            $this->record['destination'] . "')"
                        );
                        if (MDB2::isError($res)) {
                            $this->err .= $res->getMessage() . "\n" . $res->getDebugInfo();
                            error_log($this->err);
                            return false;
                        } else {
                            $id = $this->find_record();
                            if ($id > 0) {
                                $this->record['id'] = $id;
                                return true;
                            } else {
                                if ($this->err == '') {
                                    ob_start();
                                    var_dump($this->record);
                                    $this->err .= "Unknown write error" . "\n" . ob_get_clean();
                                    error_log($this->err);
                                }
                                return false;
                            }
                        }
                    }
                }
            } else {
                ob_start();
                var_dump($this->record);
                $this->err .= "Record is not complete" . "\n" . ob_get_clean();
                error_log($this->err);
                return false;
            }
        }

        public function eraseRecord() {
            if ($this->is_identified()) {
                $res = $this->db->query("DELETE FROM records WHERE id = " . $this->record['id']);
                if (MDB2::isError($res)) {
                    $this->err .= $res->getMessage() . "\n" . $res->getDebugInfo();
                    error_log($this->err);
                    return false;
                } else {
                    return true;
                }
            } else {
                ob_start();
                var_dump($this->record);
                $this->err .= "Record is not set" . "\n" . ob_get_clean();
                error_log($this->err);
                return false;
            }
        }
    }

    class slaveZone {
        private $head   = array(
            'id'        => NULL,
            'name'      => '',
            'master'    => '',
            'owner'     => 0,
            'updated'   => 'no',
            'valid'     => 'may',
        );
        private $db     = NULL;
        private $err    = '';

        public function __debugInfo() {
            return array(
                'head'  => $this->head,
                'err'   => $this->err,
                'db'    => NULL,
            );
        }

        public function __construct($param = NULL) {
            global $db;
            if (is_object($db)) {
                $this->db = &$db;
            }
            if (!is_null($param)) {
                return $this->setZoneHead($param);
            }
            return true;
        }

        public function setZoneHead($param) {
            if (is_string($param)) {
                $this->head['name'] = idnToHost($param);
            } elseif (is_numeric($param)) {
                $this->head['id'] = $param;
            } elseif (is_array($param)) {
                $this->fill_head($param);
            } else {
                ob_start();
                var_dump($param);
                $this->err .= "Unidentified parameter" . "\n" . ob_get_clean();
                error_log($this->err);
                return false;
            }
            return true;
        }

        private function fill_head($param) {
            foreach ($param as $key => $value) {
                switch ($key) {
                    case 'id':
                    case 'owner':
                        $this->head[$key] = intval($value);
                        break;
                    case 'master':
                    case 'name':
                        $this->head[$key] = idnToHost($value);
                        break;
                    default:
                        $this->head[$key] = $value;
                }
            }
        }

        private function is_identified() {
            return ((isset($this->head['id'])) || ($this->head['name'] > ''));
        }

        private function notIdent($complete = false) {
            ob_start();
            var_dump($this->head);
            $head = ob_get_clean();
            if ($complete) {
                $this->err .= "Zone is not complete" . "\n" . $head;
                error_log($this->err);
            } else {
                $this->err .= "Unidentified zone" . "\n" . $head;
                error_log($this->err);
            }
        }

        public function loadZoneHead() {
            if ($this->is_identified()) {
                $where = ' WHERE ';
                if (isset($this->head['id'])) {
                    $where .= "id = " . $this->head['id'];
                } else {
                    $where .= "name = '" . $this->head['name'] . "'";
                }
                $res = $this->db->query("SELECT * FROM slave_zones" . $where);
                if (MDB2::isError($res)) {
                    $this->err .= $res->getMessage() . "\n" . $res->getDebugInfo();
                    error_log($this->err);
                    return false;
                } else {
                    $row = $res->fetchRow();
                    if (is_array($row)) {
                        $this->fill_head($row);
                        return true;
                    } else {
                        return NULL;
                    }
                }
            } else {
                notIdent();
                return false;
            }
        }

        public function eraseZone() {
            if (!$this->is_identified()) {
                ob_start();
                var_dump($this->head);
                $this->err .= "Zone head is not complete" . "\n" . ob_get_clean();
                error_log($this->err);
                return false;
            } else {
                $where = ' WHERE ';
                if ($this->head['id'] >>= 0) {
                    $where .= "id = " . $this->head['id'];
                } else {
                    $where .= "name = '" . $this->head['name'] . "'";
                }
                $res = $this->db->query("DELETE FROM slave_zones " . $where);
                if (MDB2::isError($res)) {
                    $this->err .= $res->getMessage() . "\n" . $res->getDebugInfo();
                    error_log($this->err);
                    return false;
                }
                $this->clearZone();
                return true;
            }
        }

        public function clearZone() {

            $hd = array(
                'id'        => NULL,
                'name'      => '',
                'master'    => '',
                'owner'     => 0,
                'updated'   => 'no',
                'valid'     => 'may',
            );
        }

        public function dumpZone($dig) {
            if ($this->is_identified()) {
                if (!$this->is_complete()) {
                    $this->loadZoneHead();
                }
                $cmd = $dig . " axfr @" . $this->head['master'] . " " . $this->head['name'] . ". +time=2 +tries=2 +retry=1 2>/dev/null";
                unset($coutput);
                exec($cmd, $coutput, $exit);
                $out = '';
                foreach ($coutput as $line) {
                    $val = preg_replace('/(^;.*$|\r|\n)/', '', $line);
                    $out .= ($val > '') ? $val . "\n" : '';
                }
                return $out;
            } else {
                $this->err .= "Zone identification failed\n";
                error_log($this->err);
                return false;
            }
        }

        public function validateZone($dig) {
            $out = $this->dumpZone($dig);
            if ((isset($out)) && ($out > '')) {
                return true;
            } elseif (isset($out)) {
                $err = "Zone transfer failed\n";
                error_log($err);
                $this->err .= $err;
            }
            return false;
        }

        public function getZoneHeadRaw() {
            return $this->head;
        }

        public function getZoneHead() {
            $out = array();
            foreach ($this->head as $key => $value) {
                switch ($key) {
                    case 'master':
                    case 'name':
                        $out[$key] = hostToIdn($value);
                        break;
                    default:
                        $out[$key] = $value;
                }
            }
            return $out;
        }

        public function getErr() {
            return $this->err;
        }

        private function is_complete() {
            return (($this->head['name'] > '') && ($this->head['master'] > '') && ($this->head['owner'] >0));
        }

        public function doCommit() {
            if (!$this->is_complete()) {
                ob_start();
                var_dump($this->head);
                $this->err .= "Zone head is not complete" . "\n" . ob_get_clean();
                error_log($this->err);
                return false;
            }
            $res = $this->db->query("UPDATE slave_zones SET " .
                "updated = 'no' " .
                "WHERE id = " . $this->head['id']);
            if (MDB2::isError($res)) {
                $this->err .= $res->getMessage() . "\n" . $res->getDebugInfo();
                error_log($this->err);
                return false;
            }
            return $this->loadZoneHead();
        }

        public function saveZoneHead() {
            if (!$this->is_complete()) {
                ob_start();
                var_dump($this->head);
                $this->err .= "Zone head is not complete" . "\n" . ob_get_clean();
                error_log($this->err);
                return false;
            }
            if (!isset($this->head['id'])) {
                $vld = (isset($this->head['valid'])) ? $this->head['valid'] : 'may';
                $upd = 'yes';
                $res = $this->db->query("INSERT INTO slave_zones " .
                    "(name, master, valid, owner, updated) " .
                    "VALUES ('" . $this->head['name'] .
                        "', '" . $this->head['master'] .
                        "', '" . $vld .
                        "', " . $this->head['owner'] .
                        ", '" . $upd .
                        "')");
                if (MDB2::isError($res)) {
                    $this->err .= $res->getMessage() . "\n" . $res->getDebugInfo();
                    error_log($this->err);
                    return false;
                }
                return $this->loadZoneHead();
            } else {
                $vld = (($this->head['valid'] == 'yes') || ($this->head['valid'] == 'no')) ? $this->head['valid'] : 'may';
                $upd = ((isset($this->head['updated'])) && ($this->head['updated'] != 'del')) ? 'yes' : $this->head['updated'];
                $res = $this->db->query("UPDATE slave_zones SET " .
                    "name = '" . $this->head['name'] . "', " .
                    "master = '" . $this->head['master'] . "', " .
                    "valid = '" . $vld . "', " .
                    "owner = " . $this->head['owner'] . ", " .
                    "updated = '" . $upd . "' " .
                    "WHERE id = " . $this->head['id']);
                if (MDB2::isError($res)) {
                    $this->err .= $res->getMessage() . "\n" . $res->getDebugInfo();
                    error_log($this->err);
                    return false;
                }
                return $this->loadZoneHead();
            }
        }

    }

    class masterZone {

        private $head   = array(
            'id'        => NULL,
            'name'      => '',
            'pri_dns'   => '',
            'sec_dns'   => '',
            'serial'    => 0,
            'refresh'   => 0,
            'retry'     => 0,
            'expire'    => 0,
            'ttl'       => 0,
            'valid'     => 'may',
            'owner'     => 0,
            'updated'   => 'no',
            'secured'   => 'no',
        );
        private $db     = NULL;
        private $records = array();
        private $err    = '';
        private $isloaded = FALSE;
        private $msg    = '';

        public function __debugInfo() {
            return array(
                'head'      => $this->head,
                'records'   => $this->records,
                'err'       => $this->err,
                'isloaded'  => $this->isloaded,
                'msg'       => $this->msg,
                'db'        => NULL,
            );
        }

        public function __construct($param = NULL) {
            global $db;
            if (is_object($db)) {
                $this->db = &$db;
            }
            if (!is_null($param)) {
                return $this->setZoneHead($param);
            }
            return true;
        }

        public function getZoneHeadRaw() {
            return $this->head;
        }

        private function tab_to_space($line, $tab = 8, $nbsp = FALSE) {
            while (($t = mb_strpos($line,"\t")) !== FALSE) {
                $preTab = $t ? mb_substr($line, 0, $t) : '';
                $line = $preTab . str_repeat($nbsp?chr(7):' ', $tab-(mb_strlen($preTab)%$tab)) . mb_substr($line, $t+1);
            }
            return  $nbsp ? str_replace($nbsp?chr(7):' ', '&nbsp;', $line) : $line;
        }

        private function split_text_record($line, $length = 76) {
            $line = $this->tab_to_space($line);
            $slices = $line;
            if(strlen($line) > $length) {
                $pos = stripos($line, "\"");
                if ($pos !== false) {
                    if($pos>$length-2) {
                        $slices = substr($line, 0, $pos) . "(\n";
                        $line = $this->tab_to_space("\t\t\t\t\t" . substr($line,$pos) . " )");
                    } else {
                        $allline = substr($line,0,$pos) . "( " . substr($line,$pos) . " )";
                        $slices = substr($allline,0,$length-1) . "\"\n";
                        $line = $this->tab_to_space("\t\t\t\t\t\t    \"" . substr($allline,$length-1));
                    }
                    while (strlen($line) > $length) {
                        $slices .= substr($line, 0, $length-1) . "\"\n";
                        $line = $this->tab_to_space("\t\t\t\t\t\t    \"" . substr($line,$length-1));
                    }
                    if(strlen($line) > 0) { $slices .= $line; }
                }
            }
            return $slices;
        }

        private function prettyer($name,$ttl,$type,$pri,$target) {
            $line = str_pad($name, 31) . " " . $ttl . "\t" . "IN " . "$type";
            if (strlen($type)<5) {
                $line .= "\t";
            }
            $line .= "    " . $pri;
            if (strlen($pri) > 0){
                $line .= " ";
            }
            if ($type == "TXT"){
                $target = "\"" . $target . "\"";
            }
            return $this->split_text_record($line . $target, 116);
        }


        public function getConf($hostmaster) {
            $out = "\$TTL   " . $this->head['ttl'] . "\n";
            $out .= $this->prettyer("@", "", "SOA", "", $this->head['pri_dns'] . ". " . $hostmaster . ".") . " (\n";
            $out .= $this->tab_to_space("\t\t\t\t\t" . $this->head['serial'] . "\t; Serial\n") .
                $this->tab_to_space("\t\t\t\t\t" . $this->head['refresh'] . "\t\t; Refresh\n") .
                $this->tab_to_space("\t\t\t\t\t" . $this->head['retry'] . "\t\t; Retry\n") .
                $this->tab_to_space("\t\t\t\t\t" . $this->head['expire'] . "\t\t; Expire\n") .
                $this->tab_to_space("\t\t\t\t\t" . $this->head['ttl'] . ")\t\t; Negative Cache TTL\n;\n");
            foreach (array('pri_dns', 'sec_dns') as $ns) {
                $out .= ($this->head[$ns] != '') ? $this->prettyer("@",'','NS','',$this->head[$ns] . ".") . "\n" : "";
            }
            foreach ($this->getRecordsRaw() as $record) {
                $row = $record->getRecordRaw();
                $pri = ($row['type'] == 'MX') ? $row['pri'] : '';
                $ttl = ($row['ttl'] > 0) ? $row['ttl'] : '';
                $out .= $this->prettyer($row['host'],$ttl,$row['type'],$pri,$row['destination']) . "\n";
            }
            return $out;
        }

        public function getMsg() {
            return $this->msg;
        }

        public function writeZone($file, $hostmaster) {
            if (!$this->isloaded) {
                if (!$this->loadZone()) {
                    $this->err .= "Unable to load zone\n";
                    error_log ($this->err);
                    return false;
                }
            }
            $zonedata = $this->getConf($hostmaster);
            $fh = fopen($file, "w");
            fwrite($fh, $zonedata . "\n");
            fclose($fh);
            return true;
        }

        public function validateZone($file, $hostmaster, $checkzonecmd) {
            if ($this->writeZone($file, $hostmaster)) {
                $cmd = $checkzonecmd . " -i local " . $this->head['name'] . " " . $file . " 2>/dev/stdout";
                unset($coutput);
                exec($cmd, $coutput, $exit);
                $rows = sizeof($coutput);
                $return[0] = ($coutput[$rows-1] == 'OK');
                if (($return[0]) && ($exit == 0)) {
                    $rows--;
                    $return[1] = '';
                } else {
                    $return[1] = 'Exitcode: ' . $exit . "\n";
                }
                $return[1] .= implode("<br />", $coutput);
                if (!$return[0]) {
                    $this->err = $return[1];
                    error_log("ERROR\nCMD: " . $cmd . "\nExit: " . $exit . "\n" . implode("\n", $coutput));
                    $this->head['valid'] = 'no';
                } else {
                    $this->head['valid'] = 'yes';
                }
                $this->saveZoneHead();
                return $return;
            }
            $log = "Zone cannot be validate\n";
            $this->err .= $log;
            error_log($log);
            return array(false,"Problem in prerequisites\n");
        }

        public function getZoneHead() {
            $out = array();
            foreach ($this->head as $key => $value) {
                switch ($key) {
                    case 'pri_dns':
                    case 'sec_dns':
                    case 'name':
                        $out[$key] = hostToIdn($value);
                        break;
                    default:
                        $out[$key] = $value;
                }
            }
            return $out;
        }

        public function getErr() {
            return $this->err;
        }

        public function getId() {
            return $this->head['id'];
        }

        private function fill_head($param) {
            foreach ($param as $key => $value) {
                switch ($key) {
                    case 'id':
                    case 'serial':
                    case 'retry':
                    case 'refresh':
                    case 'expire':
                    case 'ttl':
                    case 'owner':
                        $this->head[$key] = intval($value);
                        break;
                    case 'pri_dns':
                    case 'sec_dns':
                    case 'name':
                        $this->head[$key] = idnToHost($value);
                        break;
                    default:
                        $this->head[$key] = $value;
                }
            }
        }

        public function setZoneHead($param) {
            if (is_string($param)) {
                $this->head['name'] = idnToHost($param);
            } elseif (is_numeric($param)) {
                $this->head['id'] = $param;
            } elseif (is_array($param)) {
                $this->fill_head($param);
            } else {
                ob_start();
                var_dump($param);
                $this->err .= "Unidentified parameter" . "\n" . ob_get_clean();
                error_log($this->err);
                return false;
            }
            return true;
        }

        private function is_identified() {
            return ((isset($this->head['id'])) || ($this->head['name'] > ''));
        }

        private function notIdent($complete = false) {
            ob_start();
            var_dump($this->head);
            $head = ob_get_clean();
            if ($complete) {
                $this->err .= "Zone is not complete" . "\n" . $head;
                error_log($this->err);
            } else {
                $this->err .= "Unidentified zone" . "\n" . $head;
                error_log($this->err);
            }
        }

        public function loadZoneHead() {
            if ($this->is_identified()) {
                $where = ' WHERE ';
                if (isset($this->head['id'])) {
                    $where .= "id = " . $this->head['id'];
                } else {
                    $where .= "name = '" . $this->head['name'] . "'";
                }
                $res = $this->db->query("SELECT * FROM zones" . $where);
                if (MDB2::isError($res)) {
                    $this->err .= $res->getMessage() . "\n" . $res->getDebugInfo();
                    error_log($this->err);
                    return false;
                } else {
                    $row = $res->fetchRow();
                    if (is_array($row)) {
                        $this->fill_head($row);
                        return true;
                    } else {
                        return NULL;
                    }
                }
            } else {
                $this->notIdent();
                return false;
            }
        }

        public function loadZoneRecords() {
            if ($this->is_complete()) {
                $res = $this->db->query("SELECT id FROM records WHERE zone = " . $this->head['id']);
                if (MDB2::isError($res)) {
                    $this->err .= $res->getMessage() . "\n" . $res->getDebugInfo();
                    error_log($this->err);
                    return false;
                } else {
                    while ($row = $res->fetchRow()) {
                        $id = intval($row['id']);
                        $this->records[$id] = new masterRecord($row);
                        if (!$this->records[$id]->loadRecord()) {
                            $this->err .= $this->records[$id]->getErr();
                            error_log($this->err);
                            return false;
                        }
                    }
                    return true;
                }
            } else {
                notIdent(true);
                return false;
            }
        }

        public function eraseRecord($id) {
            $found = false;
            foreach ($this->records as $key => $entry) {
                if ($key == $id) {
                    $found = true;
                    if (!$entry->eraseRecord()) {
                        $this->err .= $entry->getErr();
                        error_log($this->err);
                        return false;
                    }
                }
            }
            if ($found) {
                $this->head['valid'] = 'may';
                $this->saveZoneHead();
                $this->records = array();
                $this->loadZoneRecords();
                return true;
            }
            $this->err .= "Record id not found" . "\n" . $id;
            error_log($this->err);
            return false;
        }

        public function addRecord($param = NULL) {
            if ((is_numeric($this->head['id'])) && ($this->head['id'] > 0)) {
                $nrec = new masterRecord($param);
                $nrec->setRecord(array( 'zone' => $this->head['id']));
                $urec = $nrec->getRecord();
                if ((
                        ($urec['host'] > '') ||
                        ($urec['destination'] > '')
                    ) &&
                    ($urec['host'] != $urec['destination']) &&
                    ($urec['type'] > '')) {
                    $this->records[] = $nrec;
                    $this->head['valid'] = 'may';
                    return true;
                } else {
                    ob_start();
                    var_dump($param);
                    $this->err .= "Record is empty" . "\n" . ob_get_clean();
                    error_log($this->err);
                    return false;
                }
            } else {
                ob_start();
                var_dump($this->head);
                $this->err .= "Zone has not defined yet" . "\n" . ob_get_clean();
                error_log($this->err);
                return false;
            }
        }

        private function bind_time_format($value) {
            if (preg_match(BIND_TIME_PATTERN, strtolower($value), $match)) {
                $value = $match[1];
                switch ($match[2]) {
                    case "s":
                        $multiplier = 1;
                        break;
                    case "m":
                        $multiplier = 60;
                        break;
                    case "h":
                        $multiplier = 3600;
                        break;
                    case "d":
                        $multiplier = 86400;
                        break;
                    case "w":
                        $multiplier = 604800;
                        break;
                }
                $value = $value*$multiplier;
            }
            return $value;
        }


        public function parseZone($rows, $zonename, $owner = 1) {
            if (!is_array($rows)) {
                ob_start();
                var_dump($rows);
                $this->err .= "Zone can be parsed from an array only" . "\n" . ob_get_clean();
                error_log($this->err);
                return false;
            } else {
                $this->clearZone();
                $this->head['name'] = idnToHost($zonename);
                $soafound = false;
                $soabegins = false;
                $soadata = '';
                $recrow = '';
                foreach ($rows as $row) {
                    $row = preg_replace(COMMENT_PATTERN, ' ', trim($row));
                    $row = ($row == " ") ? '' : $row;
                    if ($soafound === false) {
                        if (preg_match(ORIGIN_PATTERN, $row, $match)) {
                            $zone = strtolower($match[1]);
                            if ($zone != $expectedname) {
                                $this->clearZone();
                                $this->err .= "Given zone not matches with the expected (" . $zone . "<=>" . $expectedzone . ")";
                                error_log($this->err);
                                return false;
                            }
                        }
                        if (preg_match(SOA_BEGINS_PATTERN, $row, $match)) {
                            $soabegins = true;
                        }
                        if ($soabegins) {
                            $soadata .= $row;
                        }
                        if (preg_match(FULL_SOA_PATTERN, $soadata, $match)) {
                            $prins = $match[3];
                            if(preg_match(TIMES_PATTERN, $match[5], $match2)) {
                                $soafound = true;
                                $serial = $match2[1];
                                $refresh = $this->bind_time_format($match2[2]);
                                $retry = $this->bind_time_format($match2[3]);
                                $expire = $this->bind_time_format($match2[4]);
                                $ttl = $this->bind_time_format($match2[5]);
                                if (($this->setZoneHead(
                                    array(
                                        'serial' => intval($serial),
                                        'refresh' => intval($refresh),
                                        'retry' => intval($retry),
                                        'expire' => intval($expire),
                                        'ttl' => intval($ttl),
                                        'owner' => intval($owner),
                                        'pri_dns' => strval($prins),
                                        'sec_dns' => '##EMPTY##',
                                    ))) && ($this->saveZoneHead())) {
                                    $soafound = true;
                                } else {
                                    $this->err .= "Head cannot be set" . "\n" . $soadata;
                                    $this->clearZone();
                                    return false;
                                }
                            } else {
                                $this->err .= "SOA record cannot be parsed" . "\n" . $soadata;
                                error_log($this->err);
                                return false;
                            }
                        }
                    } else {
                        if ($recrow != '') {
                            $rowpart = trim($row);
                            $recrow .= $rowpart;
                            $end = strpos($recrow, ')');
                            if ($end > 0) {
                                $recrow = substr($recrow, 0, $end);
                                $recd = new masterRecord(array('zone' => $this->head['id']));
                                if ($recd->setRecord($recrow)) {
                                    $parsed = $recd->getRecordRaw();
                                    if (
                                        ($this->head['sec_dns'] == '##EMPTY##') &&
                                        ($parsed['type'] == 'NS') &&
                                        ($parsed['destination'] != $self->head['pri_dns']) &&
                                        (
                                            ($parsed['host'] == '@') ||
                                            ($parsed['host'] == '')
                                        )
                                    ) {
                                        $self->head['sec_dns'] == $parsed['destination'];
                                    }
                                    $this->records[] = $recd;
                                    $recrow = '';
                                } else {
                                    $this->err .= $recd->getErr();
                                    return false;
                                }
                            }
                        } elseif ($row > '') {
                            $end = strpos($row, '(');
                            if ($end > 0) {
                                $row = preg_replace('/\(/', '', $row);
                                $recrow = $row;
                            }
                            $end = strpos($recrow, ')');
                            if ($end > 0) {
                                $recrow = substr($recrow, 0, $end);
                                $recd = new masterRecord(array('zone' => $this->head['id']));
                                if ($recd->setRecord($recrow, $this->head['name'])) {
                                    $this->records[] = $recd;
                                    $recrow = '';
                                } else {
                                    $this->err .= $recd->getErr();
                                    return false;
                                }
                            } elseif ($recrow == '') {
                                $recd = new masterRecord(array('zone' => $this->head['id']));
                                if ($recd->setRecord($row, $this->head['name'])) {
                                    $this->records[] = $recd;
                                } else {
                                    $this->err .= $recd->getErr();
                                    return false;
                                }
                            }
                        }
                    }
                }
                $recs = array();
                foreach ($this->records as $each) {
                    $rhd = $each->getRecordRaw();
                    if (($rhd['type'] == 'NS')) {
                        if (($rhd['host'] == '@') && ($rhd['destination'] == $this->head['pri_dns'] . '.')) {
                            continue;
                        } elseif ($rhd['host'] == '@') {
                            $this->head['sec_dns'] = ($this->head['sec_dns'] == '##EMPTY##') ? preg_replace('/\.$/', '', $rhd['destination']) : $this->head['sec_dns'];
                            continue;
                        }
                    }
                    $recs[] = $each;
                }
                $this->records = $recs;
                $this->saveZone();
            }
            return true;
        }

        public function loadZone() {
            $ret = $this->loadZoneHead();
            if (($ret) && (!is_null($this->head['id']))) {
                $this->isloaded = $this->loadZoneRecords();
                return $this->isloaded;
            } elseif (is_null($ret)) {
                return NULL;
            } else {
                return false;
            }
        }

        public function getRecordsRaw() {
            return $this->records;
        }

        public function getRecords($ordered = false) {
            $out = array();
            foreach ($this->records as $key => $each) {
                if ($ordered) {
                    $out[] = $each->getRecord();
                } else {
                    $out[$key] = $each->getRecord();
                }
            }
            return $out;
        }

        public function getZoneRaw() {
            $out = array();
            $out[] = $this->getZoneHeadRaw();
            $out[] = $this->getRecordsRaw();
        }

        public function getZone() {
            $out = array();
            $out[] = $this->getZoneHead();
            $out[] = $this->getRecords();
            return $out;
        }

        public function refresh_secure($zonedir) {

            $files = glob($zonedir . "{K,dsset-,}" . $this->head['name'] . ".{*private,*key,krf,}", GLOB_BRACE);
            $hit = 0;
            $names = array();
            foreach ($files as $key => $file) {
                $name = basename($file);
                switch ($name) {
                    case $this->head['name'] . '.krf':
                    case 'dsset-' . $this->head['name'] . '.':
                        $hit++;
                        break;
                    default:
                        $pattern = '/^K' . $this->head['name'] . '\.\+\d+\+\d+\.(private|key)/';
                        preg_match($pattern, $name, $match);
                        $hit += ($match[0] == $name) ? 1 : 0;
                }
                $names[$key] = $name;
            }
            $filesok = ($hit >= 8);
            $res = $this->db->query("SELECT id, dsset, krf FROM dnssec_zones WHERE zone = " . $this->head['id']);
            if (MDB2::isError($res)) {
                $this->err .= $res->getMessage() . "\n" . $res->getDebugInfo();
                error_log($this->err);
                return false;
            }
            $dbok = ($res->numRows() == 1);
            if ($dbok) {
                $sel = $res->fetchRow();
                $id = $sel['id'];
                $dsset = $sel['dsset'];
                $krf = $sel['krf'];
                if ($filesok) {
                    $dssetf = file_get_contents($zonedir . "dsset-" . $this->head['name'] . '.');
                    $krff = file_get_contents($zonedir . $this->head['name'] . '.krf');
                    if (($dsset != $dssetf) || ($krf != $krff)) {
                        $res = $this->db->query("UPDATE dnssec_zones SET " .
                            "dsset = '" . $dssetf . "', " .
                            "krf = '" . $krff . "' WHERE id = " . $id
                        );
                        if (MDB2::isError($res)) {
                            $this->err .= $res->getMessage() . "\n" . $res->getDebugInfo();
                            error_log($this->err);
                            return false;
                        }
                    }
                    $skeys = array();
                    $keys = array();
                    foreach ($names as $name) {
                        $bn = basename($name, '.key');
                        if ($bn . '.key' == $name) {
                            $keys[] = $bn;
                            $skeys[] = "'" . $bn . "'";
                        }
                    }
                    $res = $this->db->query("UPDATE dnssec_keys SET archive = 'yes' WHERE " .
                        "dszone = " . $id . " AND " .
                        "archive = 'no'");
                    if (MDB2::isError($res)) {
                        $this->err .= $res->getMessage() . "\n" . $res->getDebugInfo();
                        error_log($this->err);
                        return false;
                    }
                    $res = $this->db->query("UPDATE dnssec_keys SET archive = 'no' WHERE " .
                        "dszone = " . $id . " AND " .
                        "filename IN (" . implode(",", $skeys) . ")");
                    if (MDB2::isError($res)) {
                        $this->err .= $res->getMessage() . "\n" . $res->getDebugInfo();
                        error_log($this->err);
                        return false;
                    }
                    foreach ($keys as $keyf) {
                        $res = $this->db->query("SELECT id, fkey, fprivate FROM dnssec_keys WHERE filename = '" . $keyf . "' AND dszone = " . $id . " AND archive = 'no'");
                        if (MDB2::isError($res)) {
                            $this->err .= $res->getMessage() . "\n" . $res->getDebugInfo();
                            error_log($this->err);
                            return false;
                        }
                        $kfile = file_get_contents($zonedir . $keyf . '.key');
                        $pfile = file_get_contents($zonedir . $keyf . '.private');
                        if ($res->numRows() == 0) {
                            $this->db->query("INSERT INTO dnssec_keys (dszone, filename, fkey, fprivate, archive) VALUES ('" . $id . "','" .
                                $keyf . "', '" . $kfile . "', '" .
                                $pfile . "', 'no');"
                            );
                            if (MDB2::isError($res)) {
                                $this->err .= $res->getMessage() . "\n" . $res->getDebugInfo();
                                error_log($this->err);
                                return false;
                            }
                        } else {
                            $row = $res->fetchRow();
                            if (($kfile != $row['fkey']) || ($pfile != $row['fprivate'])) {
                                $res = $this->db->query("UPDATE dnssec_keys SET " .
                                    "fkey = '" . $kfile . "', " .
                                    "fprivate = '" . $pfile .
                                    "' WHERE dszone = " . $id .
                                    " AND filename = '" . $keyf .
                                    "' AND archive = 'no'"
                                );
                                if (MDB2::isError($res)) {
                                    $this->err .= $res->getMessage() . "\n" . $res->getDebugInfo();
                                    error_log($this->err);
                                    return false;
                                }
                            }
                        }
                    }
                } else {
                    $fh = fopen($zonedir . "dsset-" . $this->head['name'] . ".", 'w');
                    fwrite($fh, $sel['dsset']);
                    fclose($fh);
                    $fh = fopen($zonedir . $this->head['name'] . ".krf", 'w');
                    fwrite($fh, $sel['krf'] . "\n\n");
                    fclose($fh);
                    $id = $sel['id'];
                    $res = $this->db->query("SELECT * FROM dnssec_keys WHERE " .
                        " dszone = " . $id . " AND " .
                        " archive = 'no'"
                    );
                    if (MDB2::isError($res)) {
                        $this->err .= $res->getMessage() . "\n" . $res->getDebugInfo();
                        error_log($this->err);
                        return false;
                    }
                    if ($res->numRows() < 3) {
                        $this->err .= "Missing key records\n";
                        error_log($this->err);
                        return false;
                    }
                    while ($rec = $res->fetchRow()) {
                        foreach (array('private', 'key') as $ext) {
                            $fh = fopen($zonedir . $rec['filename'] . "." . $ext, 'w');
                            fwrite($fh, $rec['f' . $ext] . "\n");
                            fclose($fh);
                        }
                    }
                }
            } elseif ($filesok) {
                $dsset = '';
                $krf = '';
                $keyset = array();
                foreach ($names as $name) {
                    switch ($name) {
                        case 'dsset-' . $this->head['name'] . '.':
                            $dsset = file_get_contents($zonedir . $name);
                            break;
                        case $this->head['name'] . '.krf':
                            $krf = file_get_contents($zonedir . $name);
                            break;
                        default:
                            $ext = (basename($name, '.key') == $name) ? 'private' : 'key';
                            $base = basename($name, '.' . $ext);
                            $keyset[$base][$ext] = file_get_contents($zonedir . $name);
                    }
                }
                if (($krf == '') || ($dsset == '')) {
                    $this->err .= "Incomplete DSSET\n";
                    error_log($this->err);
                    return false;
                } else {
                    $res = $this->db->query("INSERT INTO dnssec_zones (zone, krf, dsset) VALUES ('" .
                        $this->head['id'] . "', '" .
                        $krf . "', '" .
                        $dsset . "')"
                    );
                    if (MDB2::isError($res)) {
                        $this->err .= $res->getMessage() . "\n" . $res->getDebugInfo();
                        error_log($this->err);
                        return false;
                    }
                    $res = $this->db->query("SELECT id FROM dnssec_zones WHERE zone = " . $this->head['id']);
                    if (MDB2::isError($res)) {
                        $this->err .= $res->getMessage() . "\n" . $res->getDebugInfo();
                        error_log($this->err);
                        return false;
                    }
                    $rec = $res->fetchRow();
                    $id = $rec['id'];
                    $ok = 0;
                    foreach ($keyset as $name => $arr) {
                        $key = $arr['key'];
                        $private = $arr['private'];
                        if (($key == '') || ($private == '')) {
                            $this->err .= "Incomplete KEYSET\n";
                            error_log($this->err);
                            return false;
                        }
                        $res = $this->db->query("INSERT INTO dnssec_keys (dszone, filename, fkey, fprivate, archive) VALUES (" .
                            $id . ", '" .
                            $name . "', '" .
                            $key . "', '" .
                            $private . "', 'no')"
                        );
                        if (MDB2::isError($res)) {
                            $this->err .= $res->getMessage() . "\n" . $res->getDebugInfo();
                            error_log($this->err);
                            return false;
                        }
                        $ok++;
                    }
                    if ($ok < 3) {
                        $this->err .= "Not enough KEYSET\n";
                        error_log($this->err);
                        return false;
                    }
                }
            } else {
                return false;
            }
            return true;
        }

        public function doSecure($zonedir, $zonesigner, $rollinit, $rollerconf) {
            if (!$this->is_complete()) {
                ob_start();
                var_dump($this->head);
                $this->err .= "Zone head is not complete" . "\n" . ob_get_clean();
                error_log($this->err);
                return false;
            }
            $err = $this->err;
            $param = (($this->refresh_secure($zonedir)) && ($err == $this->err)) ? '' : ' -genkeys -usensec3';
            if ($err != $this->err) return false;
            $cmd = $zonesigner . $param . " -zone " . $this->head['name'] . " " . $zonedir . $this->head['name'] . " 2>/dev/stdout";
            unset($coutput);
            $currpath = getcwd();
            chdir($zonedir);
            exec($cmd, $coutput, $signexit);
            chdir($currpath);
            if ($signexit != 0) {
                $this->err .= "Zonesigner error (" . $signexit . "):\n" . implode("\n",$coutput);
                error_log($this->err);
                return false;
            } else {
                $this->msg .= "Zonesigner output (" . $signexit . "):\n  " . implode("\n  ",$coutput) . "\n";
            }
            if (!$this->refresh_secure($zonedir)) return false;
            $rollf = file($rollerconf,  FILE_IGNORE_NEW_LINES );
            $noroll = false;
            foreach ($rollf as $row) {
                preg_match('/^\s*roll\s+"' . $this->head['name'] . '"\s*/', $row, $match);
                $noroll = (isset($match[0]) && ($row == $match[0]));
                if ($noroll) break;
            }
            if (!$noroll) {
                $cmd = $rollinit . " " . $this->head['name'] .
                    " -zone " . $zonedir . $this->head['name'] . '.signed' .
                    " -keyrec " . $zonedir . $this->head['name'] . ".krf " .
                    " -directory " . $zonedir . " 2>/dev/stdout";
                unset($coutput);
                exec($cmd, $coutput, $exit);
                if ($exit != 0) {
                    $this->err .= "Rollerd error(" . $exit . "):\n" . implode("\n  ",$coutput) . "\n";
                    error_log($this->err);
                    return false;
                } else {
                    $fh = fopen($rollerconf, "a+");
                    fwrite($fh, "\n# rollinit config for zone " . hostToIdn($this->head['name']) . ":\n" . implode("\n", $coutput) . "\n");
                    fclose($fh);
                    $this->msg .= "\n  Rollerd for zone " . hostToIdn($this->head['name']) . " is configured now\n";
                }
            } else {
                $this->msg .= "\n  Rollerd for zone " . hostToIdn($this->head['name']) . " has already set\n";
            }
            return true;
        }

        private function is_complete() {
            return (($this->head['name'] > '') &&
                    ($this->head['pri_dns'] > '') &&
                    ($this->head['sec_dns'] > '') &&
                    ($this->head['refresh'] > 0) &&
                    ($this->head['retry'] > 0) &&
                    ($this->head['expire'] > 0) &&
                    ($this->head['ttl'] > 0) &&
                    ($this->head['owner'] > 0));
        }

        public function doCommit() {
            if (!$this->is_complete()) {
                ob_start();
                var_dump($this->head);
                $this->err .= "Zone head is not complete" . "\n" . ob_get_clean();
                error_log($this->err);
                return false;
            }
            $res = $this->db->query("UPDATE zones SET " .
                "updated = 'no' " .
                "WHERE id = " . $this->head['id']);
            if (MDB2::isError($res)) {
                $this->err .= $res->getMessage() . "\n" . $res->getDebugInfo();
                error_log($this->err);
                return false;
            }
            return $this->loadZoneHead();
        }

        public function saveZoneHead() {
            if (!$this->is_complete()) {
                ob_start();
                var_dump($this->head);
                $this->err .= "Zone head is not complete" . "\n" . ob_get_clean();
                error_log($this->err);
                return false;
            }
            if (!isset($this->head['id'])) {
                $srl =intval(date('Ymd') . '01');
                $vld = (isset($this->head['valid'])) ? $this->head['valid'] : 'may';
                $upd = 'yes';
                $res = $this->db->query("INSERT INTO zones " .
                    "(name, pri_dns, sec_dns, serial, refresh, retry, expire, ttl, valid, owner, updated, secured) " .
                    "VALUES ('" . $this->head['name'] .
                        "', '" . $this->head['pri_dns'] .
                        "', '" . $this->head['sec_dns'] .
                        "', " . $srl .
                        ", " . $this->head['refresh'] .
                        ", " . $this->head['retry'] .
                        ", " . $this->head['expire'] .
                        ", " . $this->head['ttl'] .
                        ", '" . $vld .
                        "', " . $this->head['owner'] .
                        ", '" . $upd .
                        "', '" . $this->head['secured'] . "')");
                if (MDB2::isError($res)) {
                    $this->err .= $res->getMessage() . "\n" . $res->getDebugInfo();
                    error_log($this->err);
                    return false;
                }
                return $this->loadZone();
            } else {
                $srl = ($this->head['updated'] == 'no') ? intval(date('Ymd') . '01') : $this->head['serial'];
                if ($srl <= $this->head['serial']) {
                    $srl = ($this->head['updated'] == 'no') ? $this->head['serial'] + 1 : $this->head['serial'];
                }
                $vld = (($this->head['valid'] == 'yes') || ($this->head['valid'] == 'no')) ? $this->head['valid'] : 'may';
                $upd = ((isset($this->head['updated'])) && ($this->head['updated'] != 'del')) ? 'yes' : $this->head['updated'];
                $res = $this->db->query("UPDATE zones SET " .
                    "name = '" . $this->head['name'] . "', " .
                    "pri_dns = '" . $this->head['pri_dns'] . "', " .
                    "sec_dns = '" . $this->head['sec_dns'] . "', " .
                    "serial = " . $srl . ", " .
                    "refresh = " . $this->head['refresh'] . ", " .
                    "retry = " . $this->head['retry'] . ", " .
                    "expire = " . $this->head['expire'] . ", " .
                    "ttl = " . $this->head['ttl'] . ", " .
                    "valid = '" . $vld . "', " .
                    "owner = " . $this->head['owner'] . ", " .
                    "updated = '" . $upd . "', " .
                    "secured = '" . $this->head['secured'] . "' " .
                    "WHERE id = " . $this->head['id']);
                if (MDB2::isError($res)) {
                    $this->err .= $res->getMessage() . "\n" . $res->getDebugInfo();
                    error_log($this->err);
                    return false;
                }
                return $this->loadZoneHead();
            }
        }

        public function saveZone() {
            if ($this->saveZoneHead()) {
                foreach ($this->records as $each) {
                    if (!$each->saveRecord()) {
                        $this->err = $each->getErr();
                        return false;
                    }
                }
                return true;
            } else {
                return false;
            }
        }

        public function eraseZone() {
            if (!$this->is_identified()) {
                ob_start();
                var_dump($this->head);
                $this->err .= "Zone head is not complete" . "\n" . ob_get_clean();
                error_log($this->err);
                return false;
            } else {
                $where = ' WHERE ';
                if ($this->head['id'] >>= 0) {
                    $where .= "id = " . $this->head['id'];
                } else {
                    $where .= "name = '" . $this->head['name'] . "'";
                }
                $res = $this->db->query("DELETE FROM zones " . $where);
                if (MDB2::isError($res)) {
                    $this->err .= $res->getMessage() . "\n" . $res->getDebugInfo();
                    error_log($this->err);
                    return false;
                }
                $this->clearZone();
                return true;
            }
        }

        public function clearZone() {
            $id = $this->head['id'];
            $head = array(
                'id'      => $id,
                'name'    => '',
                'pri_dns' => '',
                'sec_dns' => '',
                'serial'  => 0,
                'refresh' => 0,
                'retry'   => 0,
                'expire'  => 0,
                'ttl'     => 0,
                'valid'   => 'may',
                'owner'   => 0,
                'updated' => 'no',
                'secured' => 'no',
            );
            $this->head = $head;
            $this->records = array();
            $this->isloaded = FALSE;
        }
    }
