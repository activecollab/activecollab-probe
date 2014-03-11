<?php

  /**
   * activeCollab Probe
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
  
  define('DB_HOST', 'localhost'); // Address of your MySQL server (usually localhost)
  define('DB_USER', ''); // Username that is used to connect to the server
  define('DB_PASS', ''); // User's password
  define('DB_NAME', ''); // Name of the database you are connecting to
  
  // -- No need to change anything below this line --------------------------------------
  
  define('PROBE_VERSION', '4.2');
  define('PROBE_FOR', 'activeCollab 4.2 and Newer');

  define('STATUS_OK', 'ok');
  define('STATUS_WARNING', 'warning');
  define('STATUS_ERROR', 'error');

  class TestResult {
    
    var $message;
    var $status;
    
    function TestResult($message, $status = STATUS_OK) {
      $this->message = $message;
      $this->status = $status;
    }
    
  } // TestResult

?>
<html>
  <head>
    <title>activeCollab environment test</title>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <style type="text/css">
      * {
        margin: 0; padding: 0;
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
   * @param array $result
   */
  function validate_php(&$results) {
    if(version_compare(PHP_VERSION, '5.3.3') == -1) {
      $results[] = new TestResult('Minimum PHP version required in order to run activeCollab is PHP 5.3.3. Your PHP version: ' . PHP_VERSION, STATUS_ERROR);
      return false;
    } else {
      $results[] = new TestResult('Your PHP version is ' . PHP_VERSION, STATUS_OK);
      return true;
    } // if
  } // validate_php

  /**
   * Validate memory limit
   *
   * @param array $result
   */
  function validate_memory_limit(&$results) {
    $memory_limit = php_config_value_to_bytes(ini_get('memory_limit'));

    $formatted_memory_limit = $memory_limit === -1 ? 'unlimited' : format_file_size($memory_limit);

    if($memory_limit === -1 || $memory_limit >= 67108864) {
      $results[] = new TestResult('Your memory limit is: ' . $formatted_memory_limit, STATUS_OK);
      return true;
    } else {
      $results[] = new TestResult('Your memory is too low to complete the installation. Minimal value is 64MB, and you have it set to ' . $formatted_memory_limit, STATUS_ERROR);
      return false;
    } // if
  } // validate_memory_limit
  
  /**
   * Validate PHP extensions
   *
   * @param array $results
   */
  function validate_extensions(&$results) {
    $ok = true;
    
    $required_extensions = array('mysql', 'pcre', 'tokenizer', 'ctype', 'session', 'json', 'xml', 'dom', 'phar');
    
    foreach($required_extensions as $required_extension) {
      if(extension_loaded($required_extension)) {
        $results[] = new TestResult("Required extension '$required_extension' found", STATUS_OK);
      } else {
        $results[] = new TestResult("Extension '$required_extension' is required in order to run activeCollab", STATUS_ERROR);
        $ok = false;
      } // if
    } // foreach

    // Check for eAccelerator
    if(extension_loaded('eAccelerator') && ini_get('eaccelerator.enable')) {
      $results[] = new TestResult("eAccelerator opcode cache enabled. <span class=\"details\">eAccelerator opcode cache causes activeCollab to crash. <a href=\"https://eaccelerator.net/wiki/Settings\">Disable it</a> for folder where activeCollab is installed, or use APC instead: <a href=\"http://www.php.net/apc\">http://www.php.net/apc</a>.</span>", STATUS_ERROR);
      $ok = false;
    } // if

    // Check for XCache
    if(extension_loaded('XCache') && ini_get('xcache.cacher')) {
      $results[] = new TestResult("XCache opcode cache enabled. <span class=\"details\">XCache opcode cache causes activeCollab to crash. <a href=\"http://xcache.lighttpd.net/wiki/XcacheIni\">Disable it</a> for folder where activeCollab is installed, or use APC instead: <a href=\"http://www.php.net/apc\">http://www.php.net/apc</a>.</span>", STATUS_ERROR);
      $ok = false;
    } // if
    
    $recommended_extensions = array(
      'gd' => 'GD is used for image manipulation. Without it, system is not able to create thumbnails for files or manage avatars, logos and project icons. Please refer to <a href="http://www.php.net/manual/en/image.installation.php">this</a> page for installation instructions', 
      'mbstring' => 'MultiByte String is used for work with Unicode. Without it, system may not split words and string properly and you can have weird question mark characters in Recent Activities for example. Please refer to <a href="http://www.php.net/manual/en/mbstring.installation.php">this</a> page for installation instructions', 
      'curl' => 'cURL is used to support various network tasks. Please refer to <a href="http://www.php.net/manual/en/curl.installation.php">this</a> page for installation instructions', 
      'iconv' => 'Iconv is used for character set conversion. Without it, system is a bit slower when converting different character set. Please refer to <a href="http://www.php.net/manual/en/iconv.installation.php">this</a> page for installation instructions', 
      'imap' => 'IMAP is used to connect to POP3 and IMAP servers. Without it, Incoming Mail module will not work. Please refer to <a href="http://www.php.net/manual/en/imap.installation.php">this</a> page for installation instructions', 
      'zlib' => 'ZLIB is used to read and write gzip (.gz) compressed files', 
      // SVN extension ommited, to avoid confusion
    );

    foreach($recommended_extensions as $recommended_extension => $recommended_extension_desc) {
      if(extension_loaded($recommended_extension)) {
        $results[] = new TestResult("Recommended extension '$recommended_extension' found", STATUS_OK);
      } else {
        $results[] = new TestResult("Extension '$recommended_extension' was not found. <span class=\"details\">$recommended_extension_desc</span>", STATUS_WARNING);
      } // if
    } // foreach
    
    return $ok;
  } // validate_extensions
  
  /**
   * Validate Zend Engine compatibility mode
   *
   * @param array $results
   */
  function validate_zend_compatibility_mode(&$results) {
    $ok = true;
    
    if(version_compare(PHP_VERSION, '5.0') >= 0) {
      if(ini_get('zend.ze1_compatibility_mode')) {
        $results[] = new TestResult('zend.ze1_compatibility_mode is set to On. This can cause some strange problems. It is strongly suggested to turn this value to Off (in your php.ini file)', STATUS_WARNING);
        $ok = false;
      } else {
        $results[] = new TestResult('zend.ze1_compatibility_mode is turned Off', STATUS_OK);
      } // if
    } // if
    
    return $ok;
  } // validate_zend_compatibility_mode

  /**
   * Convert filesize value from php.ini to bytes
   *
   * Convert PHP config value (2M, 8M, 200K...) to bytes. This function was taken from PHP documentation. $val is string
   * value that need to be converted
   *
   * @param string $val
   * @return integer
   */
  function php_config_value_to_bytes($val) {
    $val = trim($val);
    $last = strtolower($val{strlen($val)-1});
    switch($last) {
      // The 'G' modifier is available since PHP 5.1.0
      case 'g':
        $val *= 1024;
      case 'm':
        $val *= 1024;
      case 'k':
        $val *= 1024;
    } // if

    return (integer) $val;
  } // php_config_value_to_bytes

  /**
   * Format filesize
   *
   * @param string $value
   * @return string
   */
  function format_file_size($value) {
    $data = array(
      'TB' => 1099511627776,
      'GB' => 1073741824,
      'MB' => 1048576,
      'kb' => 1024,
    );

    // commented because of integer overflow on 32bit sistems
    // http://php.net/manual/en/language.types.integer.php#language.types.integer.overflow
    // $value = (integer) $value;
    foreach($data as $unit => $bytes) {
      $in_unit = $value / $bytes;
      if($in_unit > 0.9) {
        return trim(trim(number_format($in_unit, 2), '0'), '.') . $unit;
      } // if
    } // foreach

    return $value . 'b';
  } // format_file_size

  /**
   * Return true if MySQL supports InnoDB storage engine
   *
   * @param resource $link
   * @return bool
   */
  function check_have_inno($link) {
    if($result = mysql_query('SHOW ENGINES', $link)) {
      while($engine = mysql_fetch_assoc($result)) {
        if(strtolower($engine['Engine']) == 'innodb' && in_array(strtolower($engine['Support']), array('yes', 'default'))) {
          return true;
        } // if
      } // while
    } // if

    return true;
  } // check_have_inno
  
  // ---------------------------------------------------
  //  Do the magic
  // ---------------------------------------------------

  $results = array();
  
  $php_ok = validate_php($results);
  $memory_ok = validate_memory_limit($results);
  $extensions_ok = validate_extensions($results);
  $compatibility_mode_ok = validate_zend_compatibility_mode($results);
  
  foreach($results as $result) {
    print '<li class="' . $result->status . '"><span>' . $result->status . '</span> &mdash; ' . $result->message . '</li>';
  } // foreach

?>
      </ul>
      
      <h2>2. Database test</h2>
<?php if(DB_HOST && DB_USER && DB_NAME) { ?>
      <ul>
<?php

  $mysql_ok = true;

  $results = array();
  
  if($connection = mysql_connect(DB_HOST, DB_USER, DB_PASS)) {
    $results[] = new TestResult('Connected to database as ' . DB_USER . '@' . DB_HOST, STATUS_OK);
    
    if(mysql_select_db(DB_NAME, $connection)) {
      $results[] = new TestResult('Database "' . DB_NAME . '" selected', STATUS_OK);
      
      $mysql_version = mysql_get_server_info($connection);
      
      if(version_compare($mysql_version, '5.0') >= 0) {
        $results[] = new TestResult('MySQL version is ' . $mysql_version, STATUS_OK);
        
        $have_inno = check_have_inno($connection);
        
        if($have_inno) {
          $results[] = new TestResult('InnoDB support is enabled');
        } else {
          $results[] = new TestResult('No InnoDB support. Although activeCollab can use MyISAM storage engine InnoDB is HIGHLY recommended!', STATUS_WARNING);
        }
      } else {
        $results[] = new TestResult('Your MySQL version is ' . $mysql_version . '. We recommend upgrading to at least MySQL5!', STATUS_ERROR);
        $mysql_ok = false;
      } // if
    } else {
      $results[] = new TestResult('Failed to select database. MySQL said: ' . mysql_error(), STATUS_ERROR);
      $mysql_ok = false;
    } // if
  } else {
    $results[] = new TestResult('Failed to connect to database. MySQL said: ' . mysql_error(), STATUS_ERROR);
    $mysql_ok = false;
  } // if
  
  // ---------------------------------------------------
  //  Validators
  // ---------------------------------------------------
  
  foreach($results as $result) {
    print '<li class="' . $result->status . '"><span>' . $result->status . '</span> &mdash; ' . $result->message . '</li>';
  } // foreach

?>
      </ul>
<?php } else { ?>
      <p>Database test is <strong>turned off</strong>. To turn it On, please open probe.php in your favorite text editor and set DB_XXXX connection parameters in database section at the beginning of the file:</p>
      <ul>
        <li>DB_HOST &mdash; Address of your MySQL server (usually localhost)</li>
        <li>DB_USER &mdash; Username that is used to connect to the server</li>
        <li>DB_PASS &mdash; User's password</li>
        <li>DB_NAME &mdash; Name of the database you are connecting to</li>
      </ul>
      <p>Once these settings are set, probe.php will check if your database meets the system requirements.</p>
<?php $mysql_ok = null; ?>
<?php } // if ?>

<?php if($mysql_ok !== null) { ?>
<?php if($php_ok && $memory_ok && $extensions_ok && $compatibility_mode_ok && $mysql_ok) { ?>
      <p id="verdict" class="all_ok">OK, this system can run activeCollab</p>
<?php } else { ?>
      <p id="verdict" class="not_ok">This system does not meet activeCollab system requirements</p>
      
      <h2>Legend</h2>
      
      <div id="legend">
        <ul>
          <li class="ok"><span>ok</span> &mdash; All OK</li>
          <li class="warning"><span>warning</span> &mdash; Not a deal breaker, but it's recommended to have this installed for some features to work</li>
          <li class="error"><span>error</span> &mdash; activeCollab require this feature and can't work without it</li>
        </ul>
      </div>
<?php } // if ?>
<?php } // if ?>
    </div>
<?php

  if(function_exists('date_default_timezone_set')) {
    date_default_timezone_set('GMT');
  } // if

?>
    <p id="footer">&copy;2007&dash;<?php echo date('Y') ?>. <a href="http://www.a51dev.com">A51 doo</a>, makers of <a href="https://www.activecollab.com/index.html">activeCollab</a>.</p>
  </body>
</html>