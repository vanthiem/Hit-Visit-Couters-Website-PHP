<?php
// HERE sets what you want to use. 'mysql' for MySQL database, or 'file' for text file on server
$savein = 'file';

if(session_id() == '') { session_start(); }    // initiate Session

class MiniTrafic {
  // HERE adds your data for connecting to MySQL
  protected $hostdb = 'localhost';
  protected $namedb = 'database_name';
  protected $userdb = 'user_name';
  protected $passdb = 'password';

  // Or, HERE adds the path (directiory) and file name, to save traffic data in text file on the server
  protected $file = 'minitraff/minitrafic.txt';

         /* From here no need to modify */

 // Script MiniTrafic Site - http://coursesweb.net/php-mysql/
  // Array with 170 bots from internet
  protected $bot_list = array('Teoma', 'Baiduspider', 'Mediapartners', 'alexa', 'AbachoBOT', 'abcdatos_botlink', 'AESOP_com_SpiderMan', 'ia_archiver', 'Mercator', 'AltaVista-Intranet', 'Wget', 'Acoon Robot', 'AxmoRobot', 'Yellopet-Spider', 'Findexa Crawle', 'froogle', 'Gigabot', 'inktomi', 'looksmart', 'URL_Spider_SQL', 'Firefly', 'NationalDirectory', 'Ask Jeeves', 'TECNOSEEK', 'InfoSeek', 'WebFindBot', 'girafabot', 'crawler', 'www.galaxy.com', 'Googlebot', 'Scooter', 'Slurp', 'msnbot', 'appie', 'FAST', 'WebBug', 'Spade', 'ZyBorg', 'rabaz', 'Feedfetcher-Google', 'TechnoratiSnoop', 'Rankivabot', 'Sogou web spider', 'WebAlta Crawler', 'urlck', 'solbot', 'acme-spider', 'searchprocess', 'poppi', 'AdsBot-Google', 'FAST Enterprise Crawler', 'FAST-WebCrawler', 'Google Desktop', 'heise-IT-Markt-Crawler', 'ICCrawler - ICjobs', 'ichiro/2', 'MJ12bot', 'MetagerBot', 'msnbot-NewsBlogs', 'msnbot-media', 'NG-Search', 'NutchCVS', 'OmniExplorer_Bot', 'online link validator', 'psbot/0', 'Seekbot', 'Sensis Web Crawler', 'SEO search Crawler', 'Seoma', 'SEOsearch', 'Snappy/1.1', 'SynooBot', 'TurnitinBot', 'voyager/1.0', 'W3 SiteSearch Crawler', 'W3C-checklink', 'W3C_*Validator', 'yacybot', 'Yahoo-MMCrawler', 'YahooSeeker', 'proximic', 'Yahoo! Slurp', 'Googlebot-Mobile', 'bingbot', 'Genieo', 'facebookexternalhit', 'DoCoMoN905i', 'YandexAntivirus', 'Sosospider', 'HostTracker.com', 'SISTRIX Crawler', 'NetSeer crawler', 'YoudaoBot', 'PaperLiBot', 'YandexBot', 'AskPeterBot', 'Ezooms', 'GrepNetstatBot', 'Butterfly', 'news bot', 'Thumbshots.ru', 'WBSearchBot', 'archive.org_bot', 'SemrushBot', 'spbot', 'TweetmemeBot', 'OpenindexSpider', 'BotSeer', '200PleaseBot', 'AhrefsBot', 'Kraken', 'SEOkicks-Robot', 'ichiro/mobile goo', 'archive.orgbot', 'Exabot', 'ltbot', 'Mail.RUBot', 'NerdByNature.Bot', 'Nmap Scripting Engine', 'OnetSzukaj', 'Plukkie', 'ScribdReader', 'Seznam screenshot-generator 2.0', 'Supybot', 'TweetedTimes Bot', 'YandexDirect', 'YandexFavicons', 'StudioFACA Search', 'BOTW Spider', 'FDSE robot', 'PSpider 1012', 'Zealbot', 'AcoonBot', 'aiHitBot-BP', 'aiHitBot', 'Apercite', 'archive.is bot', 'BecomeJPBot', 'DCPbot', 'discobot', 'discoverybot', 'DomainVader', 'EventGuruBot', 'GrapeshotCrawler', 'Hailoobot', 'heritrix', 'iaarchiver', 'iaskspider', 'Kohana v3.0', 'metager2-verification-bot', 'MillionShortKeywordVerifyBot', 'MobileSurf', 'OpenindexDeepSpider', 'Phonifier', 'ProCogBot', 'Proximic crawler', 'Qualidator.com Bot', 'SearchmetricsBot', 'seexie.combot', 'SEO-Visuals Index Agent', 'SIBot', 'Sleiobot', 'SpiderLing', 'Vagabondo', 'WASALive-Bot ', 'Yahoo! DE Slurp', 'Yahoo! SlurpAsia', 'YandexWebmaster', 'iCjobs Stellenangebote Jobs', 'Gulper Web Bot');
  protected $isbot = 0;         // 1 for Bot
  protected $today;         // current day number in the month
  protected $timp;          // current timestamp
  public $ip = '0.0.0.0';       // visitor IP
  public $visitors = 1;         // nr. visitors in current day
  public $online = 1;          // nr. online validators now
  public $ob_traff;     // will contain an object with traffic data, will be saved in JSON format

  protected $conn = false;            // stores the connection to mysql

  function __construct($savein) {
    // sets class properties
    $this->today = intval(date('d'));
    $this->timp = time();

    // gets the visitor IP, from SESSION (if exists), from Server, or Cookie (for unique visitors)
    $this->ip = isset($_SESSION['usr_ip']) ? $_SESSION['usr_ip'] : $this->getUserIP();
    if(isset($_COOKIE['usr_ip'])) $this->ip = $_COOKIE['usr_ip'];

    // sets SESSION (if not seted), and Cookie with $ip, expiring at the end of the day
    if(!isset($_SESSION['usr_ip'])) $_SESSION['usr_ip'] = $this->ip;
    setcookie('usr_ip', $this->ip, (strtotime('23:59:59') + 1));

    // sets $ob_traff, multi-dimensional object with traffic data
    $this->ob_traff = new stdClass;
    // 'visitors' contains an array with IPs of all the visitors in current day
    // 'online' contains an object with 'ip'=>timestamp of the visitors in last 55 seconds
    $this->ob_traff->today = new stdClass;
      $this->ob_traff->today->visits = 0;
      $this->ob_traff->today->visitors = array();
      $this->ob_traff->today->online = new stdClass;
      $this->ob_traff->today->maxonline = array(0, 0);
    $this->ob_traff->yesterday = new stdClass;
      $this->ob_traff->yesterday->visits = 0;
      $this->ob_traff->yesterday->visitors = 0;
      $this->ob_traff->yesterday->maxonline = 0;
    // record with array(nr, timestamp)
    $this->ob_traff->record = new stdClass;
      $this->ob_traff->record->visits = array(0, 0);
      $this->ob_traff->record->visitors = array(0, 0);
      $this->ob_traff->record->online = array(0, 0);
    $this->ob_traff->total_visits = 0;
    $this->ob_traff->day = $this->today;           // current day number in the month
    $this->ob_traff->start = $this->timp;          // the time when started to use MiniTrafic

    // if not access to create the table
    if(!isset($_GET['traffic'])) {
      $this->detectBot();          // sets $isbot
      $this->setTraff($savein);         // calls the methods that sets $ob_traff
    }
  }

  // get user IP (with this function because on some servers the $_SERVER[REMOTE_ADDR] returns server IP)
  protected function getUserIP() {
    $ip = $_SERVER['REMOTE_ADDR'];
    if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)) {
      $ip = array_pop(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']));
    }
    return $ip;
  }

  // checks if the visitor is a bot (using the $bot_list), and sets $isbot to 1 if it is bot
  protected function detectBot() {
   if(isset($_SERVER['HTTP_USER_AGENT'])) {
     foreach($this->bot_list as $bot) {
       if(stripos($_SERVER['HTTP_USER_AGENT'], $bot)!==false) { $this->isbot = 1; break;}
     }
   }
  }

  // for connecting to mysql
  protected function setConn() {
    try {
      // Connect and create the PDO object
      $this->conn = new PDO("mysql:host=".$this->hostdb."; dbname=".$this->namedb, $this->userdb, $this->passdb);

      // Sets to handle the errors in the ERRMODE_EXCEPTION mode
      $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

      $this->conn->exec('SET CHARACTER SET utf8');      // Sets encoding UTF-8
      
    }
    catch(PDOException $e) {
      echo'Unable to connect to MySQL database with PHP PDO:<br/>'. $e->getMessage();
    }
  }

  // create the table in MySQL database, data will be saved in a single column, in JSON format, into a single row
  public function createTable() {
    if($this->conn===false OR $this->conn===NULL) $this->setConn();      // sets the connection to mysql

    // if there is a connection set ($conn property not false)
    if($this->conn !== false) {
      try {
        // create the table
        $sql = "CREATE TABLE `minitrafic` (`id` INT(1) UNSIGNED PRIMARY KEY, `traff` MEDIUMTEXT) CHARACTER SET utf8 COLLATE utf8_general_ci";
        if($this->conn->exec($sql) !== false) echo 'The table is created';

        // create the 1st row, with traffic data as it is set initially (in Constructor)
        $sql = "INSERT INTO `minitrafic` (`id`, `traff`) VALUES (1, '". json_encode($this->ob_traff). "')";
        if($this->conn->exec($sql) !== false) echo '<br/>The row is created in the MySQL table';
      }
      catch(PDOException $e) {
        echo'Unable to create table in MySQL<br/>';
      }
    }
  }

  // set $ob_traff object with traffic data from JSON saved in MySQL
  protected function getTraffMysql() {
    if($this->conn===false OR $this->conn===NULL) $this->setConn();      // sets the connection to mysql

    // if there is a connection set ($conn property not false)
    if($this->conn !== false) {
      try {
        $sql = "SELECT `traff` FROM `minitrafic` WHERE `id`=1 LIMIT 1";
        $result = $this->conn->query($sql);

        // If the SQL query is succesfully performed ($result not false)
        if($result !== false) {
          // Parse the result set, adds data in $ob_traff
          foreach($result as $row) {
            $this->ob_traff = json_decode($row['traff']);
          }
        }
      }
      catch(PDOException $e) {
        echo'Unable to select data from MySQL<br/>';
      }
    }
  }

  // save in MySQL the JSON with $ob_traff object
  protected function saveTraffMysql() {
    if($this->conn===false OR $this->conn===NULL) $this->setConn();      // sets the connection to mysql

    // if there is a connection set ($conn property not false)
    if($this->conn !== false) {
      try {
        $sql = "UPDATE `minitrafic` SET `traff`='". json_encode($this->ob_traff). "'";
        if($this->conn->exec($sql) === false) echo '<br/>Unable to update MySQL table<br/>';
        $this->conn = null;        // Disconnect
      }
      catch(PDOException $e) {
        echo'Unable to update data in MySQL<br/>';
      }
    }
  }

  // set $ob_traff object with traffic data from JSON saved in $file
  protected function getTraffFile() {
    if(is_file($this->file) && filesize($this->file) > 88) $this->ob_traff = json_decode(file_get_contents($this->file));
  }

  // save in the $file the JSON with $ob_traff object
  protected function saveTraffFile() {
    if(!file_put_contents($this->file, json_encode($this->ob_traff), LOCK_EX)) echo 'Unable to save traffic data in: '. $this->file;
  }

  // if no Bot, update $ob_traff: increment 'total_visits', check to actualize 'vizitors', 'online', 'record'
  // calls saveTraffFile() to save in the $file the JSON with $ob_traff object
  protected function setTraff($savein) {
    // calls the methods that sets $ob_traff, according to $savein parameter ('mysql', or 'file')
    if($savein == 'mysql') $this->getTraffMysql();
    else $this->getTraffFile();

    if($this->isbot === 0) {
      // if 'day' in $ob_traff different by $today, sets 'day' valuue to $today,
      // sets $ob_traff['yesterday'] with data from $ob_traff['today'], and resets it
      if($this->ob_traff->day != $this->today) {
        $this->ob_traff->day = $this->today;
        $this->ob_traff->yesterday->visits = $this->ob_traff->today->visits;
        $this->ob_traff->yesterday->visitors = count($this->ob_traff->today->visitors);
        $this->ob_traff->yesterday->maxonline = $this->ob_traff->today->maxonline[0];
        $this->ob_traff->today->visits = 0;
        $this->ob_traff->today->visitors = array();
        $this->ob_traff->today->online = new stdClass;
        $this->ob_traff->today->maxonline = array(0, 0);
      }

      $this->ob_traff->total_visits++;         // increment total_visits by 1
      $this->ob_traff->today->visits++;      // increment today visits by 1

      // sets record site visits $ob_traff->record->visits=>array(nr, timestamp)
      if($this->ob_traff->today->visits > $this->ob_traff->record->visits[0]) {
        $this->ob_traff->record->visits[0] = $this->ob_traff->today->visits;
        $this->ob_traff->record->visits[1] = $this->timp;
      }

      // if visitor IP not in $ob_traff->today['visitors'] array, adds it
      if(!in_array($this->ip, $this->ob_traff->today->visitors)) $this->ob_traff->today->visitors[] = $this->ip;

      // sets $visitors, and record visitors $ob_traff->record['visitors']=>array(nr, timestamp)
      $this->visitors = count($this->ob_traff->today->visitors);
      if($this->visitors > $this->ob_traff->record->visitors[0]) {
        $this->ob_traff->record->visitors[0] = $this->visitors;
        $this->ob_traff->record->visitors[1] = $this->timp;
      }

      // traverse the array with online visitors ob_traff->today->online,
      // delete records older than 60 seconds, or IP of curent visitor, than adds '$this->ip'=>$timp
      if(count((array)$this->ob_traff->today->online) > 0) {
        foreach($this->ob_traff->today->online as $ip=>$timp) {
          if($this->timp > ($timp + 60) || $this->ip == $ip) unset($this->ob_traff->today->online->{$ip});
        }
      }
      $this->ob_traff->today->online->{$this->ip} = $this->timp;

      // sets $online, and today maxonline, and record online; array(nr, timestamp)
      $this->online = count((array)$this->ob_traff->today->online);
      if($this->online > $this->ob_traff->today->maxonline[0]) {
        $this->ob_traff->today->maxonline[0] = $this->online;
        $this->ob_traff->today->maxonline[1] = $this->timp;

        if($this->ob_traff->today->maxonline[0] > $this->ob_traff->record->online[0]) {
          $this->ob_traff->record->online[0] = $this->ob_traff->today->maxonline[0];
          $this->ob_traff->record->online[1] = $this->ob_traff->today->maxonline[1];
        }
      }

    // calls the methods that save traffic data, according to $savein parameter (in 'mysql', or 'file')
    if($savein == 'mysql') $this->saveTraffMysql();
    else $this->saveTraffFile();
    }
  }

  // define and returns the HTML code with traffic data from $ob_traff
  public function getTraff(){
    // sets variables with date/time of traffic data, to be added in html_trafic
    $start = date('j-M-Y', $this->ob_traff->start);
    $now = date('j-M-Y, H:i', $this->timp);
    $record_visits = date('j-M-Y', $this->ob_traff->record->visits[1]);
    $record_visitors = date('j-M-Y', $this->ob_traff->record->visitors[1]);
    $record_online = date('j-M-Y, H:i', $this->ob_traff->record->online[1]);
    $today_maxonline = date('H:i', $this->ob_traff->today->maxonline[1]);

    // Sets HTML code
    $re_html = '<div id="traff"><div class="traf"><span class="su">Traffic data</span> <sup>Start: '.$start.'</sup></div>Now: <sup id="dtnow">'.$now.'</sup><br/>Total site visits: '.$this->ob_traff->total_visits.'<br/> &nbsp; - Most: '.$this->ob_traff->record->visits[0].' <sup>'.$record_visits.'</sup>
  <br/> &nbsp; &nbsp; &bull;&nbsp; Yesterday: '.$this->ob_traff->yesterday->visits.'<br/> &nbsp; &nbsp; &bull;&nbsp; Today: '.$this->ob_traff->today->visits.'<br/>
<div class="traf">- Unique Visitors -</div> &nbsp; - Record: '.$this->ob_traff->record->visitors[0].' <sup>'.$record_visitors.'</sup>
<br/> &nbsp; &nbsp; &bull;&nbsp; Yesterday: '.$this->ob_traff->yesterday->visitors.'<br/> &nbsp; &nbsp; &bull;&nbsp; Today: '.$this->visitors.'<br/>
  <div class="traf">- Online -</div> &nbsp; - Record: '.$this->ob_traff->record->online[0].' <sup>'.$record_online.'</sup><br/> &nbsp; - Most-Today: '.$this->ob_traff->today->maxonline[0].' <sup>'.$today_maxonline.'</sup><br/> &nbsp; - Most-Yesterday: '.$this->ob_traff->yesterday->maxonline.'<br/> &nbsp; &bull;&nbsp; Now: '.$this->online.'<br/><br/> &nbsp; Your IP: &nbsp; '.$this->getUserIP(). '</div>';

    return $re_html;
  }
}

/* Uses the MiniTrafic class */

$minitrafic = new MiniTrafic($savein);     $html_trafic = '';

// if $savein is 'mysql' and URL with traffic='createtable', calls rhe method to create the table in MySQL
// else, get the HTML code with traffic data
if($savein == 'mysql' && (isset($_GET['traffic']) && $_GET['traffic'] == 'createtable')) {
  $minitrafic->createTable();
}
else $html_trafic = $minitrafic->getTraff();

// Outputs traffic data into a JS function, when this file is accessed with "add=minitrafic" in URL address
if(isset($_GET['add']) && $_GET['add'] == 'minitrafic') {
  echo 'document.write(\''. str_replace(array(PHP_EOL, "'"), array('', "\\'"), $html_trafic). '\');';
}
