<?php

/**
 * ActiveCollab Probe
 *
 * Copyright (c) 2012 A51 d.o.o.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this
 * software and associated documentation files (the "Software"), to deal in the Software
 * without restriction, including without limitation the rights to use, copy, modify,
 * merge, publish, distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to the following
 * conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies
 * or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A
 * PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF
 * CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE
 * OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

// -- Please provide valid database connection parameters ------------------------------

define('DB_HOST', ''); // Address of your MySQL server (usually localhost)
define('DB_USER', ''); // Username that is used to connect to the server
define('DB_PASS', ''); // User's password
define('DB_NAME', ''); // Name of the database you are connecting to

// -- No need to change anything below this line --------------------------------------

define('PROBE_VERSION', '5.14');
define('PROBE_FOR', 'ActiveCollab 5.14 and newer');

define('STATUS_OK', 'ok');
define('STATUS_WARNING', 'warning');
define('STATUS_ERROR', 'error');

class TestResult
{
    public $message;
    public $status;

    function __construct($message, $status = STATUS_OK)
    {
        $this->message = $message;
        $this->status = $status;
    }
}

?>
<html>
<head>
    <title>ActiveCollab environment test</title>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
    <style type="text/css">
        * {
            margin: 0;
            padding: 0;
        }

        body {
            font: 1em "Lucida Grande", verdana, arial, helvetica, sans-serif;
            text-align: center;
            background: white;
            color: #333;
        }

        h1, h2 {
            margin: 16px 0;
        }

        h1 {
            text-align: center;
        }

        a {
            color: #333;
            border-bottom: 1px solid #ccc;
            text-decoration: none;
        }

        a:hover {
            color: black;
            border-color: black;
        }

        p {
            margin: 8px 0;
        }

        ul {
            margin: 8px 0;
            padding: 0 0 0 33px;
            list-style: square;
        }

        dl {
            margin: 8px 0;
            color: #999;
            font-size: 80%;
        }

        dt, dd {
            padding: 3px;
        }

        dt {
            float: left;
            width: 100px;
        }

        dd {
            padding-left: 100px;
            border-bottom: 1px solid #e8e8e8;
        }

        #wrapper {
            margin: 100px auto 16px auto;
            padding: 9px 25px 25px 25px;
            border: 10px solid #ccc;
            width: 600px;
            text-align: left;
        }

        .ok span, .warning span, .error span {
            font-weight: bolder;
        }

        .ok span {
            color: green;
        }

        .warning span {
            color: orange;
        }

        .error span {
            color: red;
        }

        span.details {
            font-weight: normal;
            font-size: 12px;
            color: #999;
            display: block;
            padding: 5px;
        }

        #verdict {
            margin: 20px 0;
            padding: 20px;
            text-align: center;
            font-size: 160%;
            color: white;
            border-radius: 15px;
        }

        #verdict.all_ok {
            background: green;
        }

        #verdict.not_ok {
            background: #BC0000;
        }
    </style>
</head>
<body>
<div id="wrapper">
    <h1>probe.php</h1>
    <p style="padding: 16px 0; text-align: center; color: #666;">This simple utility will help you check if your server environment can run ActiveCollab. Grab the latest version on <a href="https://github.com/activecollab/activecollab-probe" target="_blank">GitHab</a>.</p>
    <dl>
        <dt>Probe Version:</dt>
        <dd><?php echo PROBE_VERSION ?></dd>

        <dt>Testing For:</dt>
        <dd><?php echo PROBE_FOR ?></dd>
    </dl>

    <h2>1. Environment test</h2>
    <ul>
        <?php

        // ---------------------------------------------------
        //  Validators
        // ---------------------------------------------------

        /**
         * Validate PHP platform
         *
         * @param array $results
         * @return bool
         */
        function validate_php(&$results)
        {
            if (version_compare(PHP_VERSION, '7.1', '<')) {
                $results[] = new TestResult('Minimum PHP version required in order to run ActiveCollab is PHP 7.1. Your PHP version: ' . PHP_VERSION, STATUS_ERROR);

                return false;
            } elseif (version_compare(PHP_VERSION, '7.2', '>=')) {
                $results[] = new TestResult('ActiveCollab is currently not compatible with PHP 7.2. Your PHP version: ' . PHP_VERSION, STATUS_ERROR);

                return false;
            } else {
                $results[] = new TestResult('Your PHP version is ' . PHP_VERSION, STATUS_OK);

                return true;
            }
        }

        /**
         * Validate memory limit
         *
         * @param array $results
         * @return bool
         */
        function validate_memory_limit(&$results)
        {
            $memory_limit = php_config_value_to_bytes(ini_get('memory_limit'));

            $formatted_memory_limit = $memory_limit === -1 ? 'unlimited' : format_file_size($memory_limit);

            if ($memory_limit === -1 || $memory_limit >= 67108864) {
                $results[] = new TestResult('Your memory limit is: ' . $formatted_memory_limit, STATUS_OK);

                return true;
            } else {
                $results[] = new TestResult('Your memory is too low to complete the installation. Minimal value is 64MB, and you have it set to ' . $formatted_memory_limit, STATUS_ERROR);

                return false;
            }
        }

        /**
         * Validate PHP extensions
         *
         * @param array $results
         * @return bool
         */
        function validate_extensions(&$results)
        {
            $ok = true;

            $required_extensions = [
                'mysqli',
                'pcre',
                'tokenizer',
                'ctype',
                'session',
                'json',
                'xml',
                'dom',
                'phar',
                'openssl',
                'gd',
                'mbstring',
                'curl',
                'zlib',
                'fileinfo',
            ];

            foreach ($required_extensions as $required_extension) {
                if (extension_loaded($required_extension)) {
                    $results[] = new TestResult("Required extension <span style=\"color: orange\">$required_extension</span> found", STATUS_OK);
                } else {
                    $results[] = new TestResult("Extension <span style=\"color: orange\">$required_extension</span> is required in order to run ActiveCollab", STATUS_ERROR);
                    $ok = false;
                }
            }

            // Check for eAccelerator
            if (extension_loaded('eAccelerator') && ini_get('eaccelerator.enable')) {
                $results[] = new TestResult("eAccelerator opcode cache enabled. <span class=\"details\">eAccelerator opcode cache causes ActiveCollab to crash. <a href=\"https://eaccelerator.net/wiki/Settings\">Disable it</a> for folder where ActiveCollab is installed, or use APC instead: <a href=\"http://www.php.net/apc\">http://www.php.net/apc</a>.</span>", STATUS_ERROR);
                $ok = false;
            }

            // Check for XCache
            if (extension_loaded('XCache') && ini_get('xcache.cacher')) {
                $results[] = new TestResult("XCache opcode cache enabled. <span class=\"details\">XCache opcode cache causes ActiveCollab to crash. <a href=\"http://xcache.lighttpd.net/wiki/XcacheIni\">Disable it</a> for folder where ActiveCollab is installed, or use APC instead: <a href=\"http://www.php.net/apc\">http://www.php.net/apc</a>.</span>", STATUS_ERROR);
                $ok = false;
            }

            $recommended_extensions = [
                'iconv' => 'Iconv is used for character set conversion. Without it, system is a bit slower when converting different character set. Please refer to <a href="http://www.php.net/manual/en/iconv.installation.php">this</a> page for installation instructions',
                'imap' => 'IMAP is used to connect to POP3 and IMAP servers. Without it, Incoming Mail module will not work. Please refer to <a href="http://www.php.net/manual/en/imap.installation.php">this</a> page for installation instructions',
            ];

            foreach ($recommended_extensions as $recommended_extension => $recommended_extension_desc) {
                if (extension_loaded($recommended_extension)) {
                    $results[] = new TestResult("Recommended extension <span style=\"color: orange\">$recommended_extension</span> found", STATUS_OK);
                } else {
                    $results[] = new TestResult("Extension <span style=\"color: orange\">$recommended_extension</span> was not found. <span class=\"details\">$recommended_extension_desc</span>", STATUS_WARNING);
                }
            }

            return $ok;
        }

        /**
         * Convert filesize value from php.ini to bytes
         *
         * Convert PHP config value (2M, 8M, 200K...) to bytes. This function was taken from PHP documentation. $val is string
         * value that need to be converted
         *
         * @param string $val
         * @return integer
         */
        function php_config_value_to_bytes($val)
        {
            $val = trim($val);

            if (ctype_digit($val)) {
                $last = '';
            } else {
                $last = strtolower($val{strlen($val) - 1});
                $val = substr($val, 0, strlen($val) - 1);
            }

            switch ($last) {
                // The 'G' modifier is available since PHP 5.1.0
                case 'g':
                    $val *= 1024;
                case 'm':
                    $val *= 1024;
                case 'k':
                    $val *= 1024;
            }

            return (integer) $val;
        }

        /**
         * Format filesize
         *
         * @param string $value
         * @return string
         */
        function format_file_size($value)
        {
            $data = [
                'TB' => 1099511627776,
                'GB' => 1073741824,
                'MB' => 1048576,
                'kb' => 1024,
            ];

            // commented because of integer overflow on 32bit sistems
            // http://php.net/manual/en/language.types.integer.php#language.types.integer.overflow
            // $value = (integer) $value;
            foreach ($data as $unit => $bytes) {
                $in_unit = $value / $bytes;
                if ($in_unit > 0.9) {
                    return trim(trim(number_format($in_unit, 2), '0'), '.') . $unit;
                }
            }

            return $value . 'b';
        }

        /**
         * @param mysqli $link
         * @return string
         */
        function get_mysql_version($link)
        {
            if ($result = $link->query("SELECT VERSION() AS 'version'")) {
                while ($row = $result->fetch_assoc()) {
                    return $row['version'];
                }
            }

            return $link->get_server_info();
        }

        function validate_mysql_version($version, $min_mysql_version, $min_mariadb_version)
        {
            if (strpos(strtolower($version), 'mariadb') !== false) {
                return [
                    'MariaDB',
                    $min_mariadb_version,
                    version_compare($version, $min_mariadb_version) >= 0,
                ];
            } else {
                return [
                    'MySQL',
                    $min_mysql_version,
                    version_compare($version, $min_mysql_version) >= 0,
                ];
            }
        }

        /**
         * @param mysqli $link
         * @return bool
         */
        function check_is_database_empty($link)
        {
            if ($result = $link->query('SHOW TABLES')) {
                return $result->num_rows < 1;
            }

            return true;
        }

        /**
         * Return true if MySQL supports InnoDB storage engine
         *
         * @param mysqli $link
         * @return bool
         */
        function check_have_inno(mysqli $link)
        {
            if ($result = $link->query('SHOW ENGINES')) {
                while ($engine = $result->fetch_assoc()) {
                    if (strtolower($engine['Engine']) == 'innodb' && in_array(strtolower($engine['Support']), ['yes', 'default'])) {
                        return true;
                    }
                }
            }

            return false;
        }

        /**
         * @param mysqli $link
         * @return bool
         */
        function check_have_utf8mb4($link)
        {
            if ($result = $link->query("SHOW CHARACTER SET LIKE 'utf8mb4'")) {
                while ($charset = $result->fetch_assoc()) {
                    if (strtolower($charset['Charset']) == 'utf8mb4') {
                        return true;
                    }
                }
            }

            return false;
        }

        /**
         * @param mysqli $link
         * @return bool
         */
        function check_thread_stack($link)
        {
            if ($result = $link->query("SELECT @@thread_stack AS 'thread_stack'")) {
                while ($row = $result->fetch_assoc()) {
                    return (int) $row['thread_stack'] >= 262144; // 256kb
                }
            }

            return false;
        }

        // ---------------------------------------------------
        //  Do the magic
        // ---------------------------------------------------

        $results = [];

        $php_ok = validate_php($results);
        $memory_ok = validate_memory_limit($results);
        $extensions_ok = validate_extensions($results);

        foreach ($results as $result) {
            print '<li class="' . $result->status . '"><span>' . $result->status . '</span> &mdash; ' . $result->message . '</li>';
        }

        ?>
    </ul>

    <h2>2. Database test</h2>
    <?php if (DB_HOST && DB_USER && DB_NAME) { ?>
        <ul>
            <?php

            $mysql_ok = true;

            $results = [];

            $link = @mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

            if ($link instanceof mysqli) {
                $results[] = new TestResult('Connected to database as ' . DB_USER . '@' . DB_HOST);

                $mysql_version = get_mysql_version($link);

                list ($mysql_server, $min_mysql_server_version, $mysql_version_ok) = validate_mysql_version(
                    $mysql_version,
                    '5.7.8',
                    '10.2.7'
                );

                if ($mysql_version_ok) {
                    $results[] = new TestResult("{$mysql_server} version is {$mysql_version}");

                    if (check_is_database_empty($link)) {
                        $results[] = new TestResult('Database is empty');
                    } else {
                        $results[] = new TestResult('Database is not empty', STATUS_ERROR);
                        $mysql_ok = false;
                    }

                    if (check_have_inno($link)) {
                        $results[] = new TestResult('InnoDB support is enabled');
                    } else {
                        $results[] = new TestResult('No InnoDB support', STATUS_ERROR);
                        $mysql_ok = false;
                    }

                    if (check_have_utf8mb4($link)) {
                        $results[] = new TestResult('UTF8MB4 support available');
                    } else {
                        $results[] = new TestResult('UTF8MB4 support not available', STATUS_ERROR);
                        $mysql_ok = false;
                    }

                    if (check_thread_stack($link)) {
                        $results[] = new TestResult("{$mysql_server} thread stack is 256kb");
                    } else {
                        $results[] = new TestResult("{$mysql_server} thread stack should be 256kb", STATUS_ERROR);
                        $mysql_ok = false;
                    }
                } else {
                    $results[] = new TestResult("{$mysql_server} {$min_mysql_server_version} or later is required. Your {$mysql_server} version is {$mysql_version}", STATUS_ERROR);
                    $mysql_ok = false;
                }
            } else {
                $results[] = new TestResult('Failed to connect to database. MySQL said: ' . mysqli_connect_error(), STATUS_ERROR);
                $mysql_ok = false;
            }

            // ---------------------------------------------------
            //  Validators
            // ---------------------------------------------------

            foreach ($results as $result) {
                print '<li class="' . $result->status . '"><span>' . $result->status . '</span> &mdash; ' . $result->message . '</li>';
            }

            ?>
        </ul>
    <?php } else { ?>
        <p>Database test is <strong style="color: red">turned off</strong>. To turn it On, please open probe.php in your favorite text
            editor and set DB_XXXX connection parameters in database section at the beginning of the file:</p>
        <ul>
            <li><span style="color: orange">DB_HOST</span> &mdash; Address of your MySQL server (usually localhost)</li>
            <li><span style="color: orange">DB_USER</span> &mdash; Username that is used to connect to the server</li>
            <li><span style="color: orange">DB_PASS</span> &mdash; User's password</li>
            <li><span style="color: orange">DB_NAME</span> &mdash; Name of the database you are connecting to</li>
        </ul>
        <p>Once these settings are set, probe.php will check if your database meets the system requirements.</p>
        <?php $mysql_ok = null; ?>
    <?php } ?>

    <?php if ($mysql_ok !== null) { ?>
        <?php if ($php_ok && $memory_ok && $extensions_ok && $mysql_ok) { ?>
            <p id="verdict" class="all_ok">OK, this system can run ActiveCollab</p>
        <?php } else { ?>
            <p id="verdict" class="not_ok">This system does not meet ActiveCollab system requirements</p>

            <h2>Legend</h2>

            <div id="legend">
                <ul>
                    <li class="ok"><span>ok</span> &mdash; All OK</li>
                    <li class="warning"><span>warning</span> &mdash; Not a deal breaker, but it's recommended to have
                        this installed for some features to work
                    </li>
                    <li class="error"><span>error</span> &mdash; ActiveCollab require this feature and can't work
                        without it
                    </li>
                </ul>
            </div>
        <?php } ?>
    <?php } ?>
</div>
<?php

if (function_exists('date_default_timezone_set')) {
    date_default_timezone_set('GMT');
}

?>
<p id="footer">&copy;2007&dash;<?php echo date('Y') ?>. <a href="https://activecollab.com">ActiveCollab, LLC</a>.</p>
</body>
</html>
