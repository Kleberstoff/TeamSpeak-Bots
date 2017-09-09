<?php
/* Require Library */
require("TeamSpeak3/TeamSpeak3.php");
/* Require Library End */

/* Config Start with Array */
$config = array();

/* Give the Array Information */
$config['Username'] = "test"; // Login name for the Query
$config['Password'] = "XK09HBXj"; // Password for the Query
$config['serverIP'] = "127.0.0.1"; // Server IP or Domain Name
$config['sPort'] = "9987"; // Server Port for the Query | Default: 9987
$config['qPort'] = "10011"; // Query Port for the Query | Default: 10011
$config['BotName'] = rawurlencode("I am normally invisible"); // url encoded bot name
$config['CheckDelay'] = 1; // Amount of Seconds between each Check. Only use Number greater then 1. Faster Checks are useful for bigger TeamSpeaks, in smaller TeamSpeaks, this can be higher
/* Bot Config End */

/* TeamSpeak Settings */
$config['channel_codec'] = TeamSpeak3::CODEC_OPUS_VOICE; //  See: https://docs.planetteamspeak.com/ts3/php/framework/class_team_speak3.html#ac6e83b47f7d7d5f832195fa500095dc3
$config['channel_description'] = ''; //Set the description of the temporary channel. Leave blank if not needed
$config['PublicChannels'] = array(3, 4, 5); // Put the permanent Public Channel ID's here | Note: The Last Channel in this List should always be the last Channel in the TeamSpeak Order as well
$config['TempChannelName'] = "Temp. Public Channel "; // Temporary Public Channel Name
$config['channel_order'] = ''; //Set this to the ID of the channel above the new temporary channels. Result: https://puu.sh/xquCS.png Leave empty if not needed
$config['TopChannel'] = 2; // Parent channel ID. New temp channels will be created inside this channel
$config['TempMaxClients'] = 2; // Set the Max Clients for new Temp Channels
$config['ChannelPermissions'] = array(

); // A list of all permissions can be either found in teamspeak itself by displaying permission names or on this list: https://www.teamspeak3.com/teamspeak-3-server-permission-list.php
  /* Example permissions:
   * "i_channel_needed_subscribe_power=500",
   * "i_channel_needed_join_power=40"
  */

/* TeamSpeak Settings */
