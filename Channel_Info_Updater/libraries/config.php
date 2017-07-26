<?php
/* Require Library */
Require ("TeamSpeak3/TeamSpeak3.php");
/* Require Library End */

/* Config Start with Array */
$config = array();

$config['Username'] = "rwarAS"; //Login name for the Query
$config['Password'] = "m7p6CMKZ"; //Password for the Query
$config['serverIP'] = "localhost"; //Server IP or Domain Name
$config['sPort'] = "9987"; //Server Port for the Query | Default: 10011
$config['qPort'] = "10011"; //Query Port for the Query | Default: 10011
$config['BotName'] = rawurlencode("Annoying Bot"); //url encoded bot name

/* Bot Config End */

/* TeamSpeak Settings */

$config['TimeChannel'] = "233";
$config['UserChannel'] = "234";

/* TeamSpeak Settings */