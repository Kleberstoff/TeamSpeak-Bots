<?php
require("libraries/config.php"); //Path to Config
date_default_timezone_set('Europe/Berlin');

$int = 0;

try
{
  TeamSpeak3::init();
  $ts3 = TeamSpeak3::factory("serverquery://{$config["Username"]}:{$config["Password"]}@{$config["serverIP"]}:{$config["qPort"]}/?server_port={$config["sPort"]}&nickname={$config["BotName"]}");

  $TimeChannel = $ts3->channelGetById($config['TimeChannel']);
  $UserChannel = $ts3->channelGetById($config['UserChannel']);

  while(true)
  {
    set_time_limit(30);
    $time = "[cspacer] " . date("[d.m.Y] - [H:i:s]");

    $clientsOnline = onlineClients($ts3);

    if($UserChannel["channel_name"] != $clientsOnline)
    {
        $UserChannel["channel_name"] = $clientsOnline;
    }

    if($TimeChannel['channel_name'] != $time)
    {
      $TimeChannel['channel_name'] = $time;
    }
    $int++;
    echo $int . " ";
    usleep(500000);
  }
}
catch(Exception $ex)
{
        echo "ErrorID: <b>" . $ex->getCode() . "</b>; Error Message: <b>" . $ex->getMessage() . "</b>;";
}

function onlineClients($ts3)
{
    $onlineClients = 0;
    $slotsCount = $ts3['virtualserver_maxclients'];
    foreach($ts3->clientList() as $client)
    {
        if($client['client_type'] == 1) continue;
        $onlineClients++;
    }
    $ts3->clientListReset();
    return("[cspacer] Users Online: " . $onlineClients . "/" . $slotsCount);
}