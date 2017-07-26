<?php
/* Require Library */
require("TeamSpeak3/TeamSpeak3.php");
/* Require Library End */

/* Config Start with Array */
$config = array();

$config['Username'] = "test"; // Login name for the Query
$config['Password'] = "XK09HBXj"; // Password for the Query
$config['serverIP'] = "127.0.0.1"; // Server IP or Domain Name
$config['sPort'] = "9987"; // Server Port for the Query | Default: 9987
$config['qPort'] = "10011"; // Query Port for the Query | Default: 10011
$config['BotName'] = rawurlencode("I am normally invisible"); // url encoded bot name
$config['CheckDelay'] = 1; // Amount of Seconds between each Check. Only use Number greater then 1. Faster Checks are useful for bigger TeamSpeaks, in smaller TeamSpeaks, this can be higher

/* Bot Config End */

/* TeamSpeak Settings */

$config['TimeChannel'] = "3";
$config['UserChannel'] = "4";

/* TeamSpeak Settings */