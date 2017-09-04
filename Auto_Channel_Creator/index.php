<?php
require("libraries/config.php");

// Example:
// Channel 1, 2 and 3 are occupied, we create a new Channel
//
// Channel 1 | Person 1, Person 2, Person 3
// Channel 2 | Person 1, Person 2
// Channel 3 | Person 1
// Channel 4 | Will be created after Channel 3 is being occupied by at least 1 User
// If a User joins in Channel 4, Channel 5 will be created

try
{
    TeamSpeak3::init();
    $ts3 = TeamSpeak3::factory("serverquery://{$config["Username"]}:{$config["Password"]}@{$config["serverIP"]}:{$config["qPort"]}/?server_port={$config["sPort"]}&nickname={$config["BotName"]}#no_query_clients");
    while (true)
    {
        set_time_limit(30); // Preventing a PHP Timeout
        $occupiedChannels = 0;
        $ts3->channelListReset();
        $ts3Channels = $ts3->channelList();
        $PublicChannelInfo = array();

        foreach($config['PublicChannels'] as $PublicChannel)
        {
            $ChannelInfo = $ts3->channelGetbyID($PublicChannel);
            if($ChannelInfo['total_clients'] != "0")
            {
                $occupiedChannels++;
            }
            $PublicChannelInfo[] = $ChannelInfo;
        }

        if($occupiedChannels != count($config['PublicChannels']))
        {
            DeleteAllTemporaryPublicChannels($ts3Channels, $config['TempChannelName'], $ts3);
        }

        if($occupiedChannels == count($config['PublicChannels']))
        {
            $amountOfExistingTemporaryChannels = CheckForExistingTemporaryPublicChannels($ts3Channels, $config['TempChannelName']);
            $amountOfOccupiedTemporaryChannels = CheckForOccupiedTemporaryChannels($ts3Channels, $config['TempChannelName']);
            $amountOfNeededTemporaryChannels = ($amountOfOccupiedTemporaryChannels + 1);

            CheckForEmptyExistingTemporaryPublicChannel($ts3Channels, $config['TempChannelName'], $amountOfNeededTemporaryChannels, $ts3);

            if($amountOfExistingTemporaryChannels <= $amountOfOccupiedTemporaryChannels)
            {
                $after = GetLastPublicChannel($PublicChannelInfo, $ts3Channels, $config['TempChannelName']);
                CreateNewTemporaryChannel($ts3, $config['TempChannelName'], $amountOfExistingTemporaryChannels, $after, $config['TempMaxClients'], $config['ChannelPermissions'], $config['channel_order'], $config['channel_description']););
            }
        }
        sleep($config['CheckDelay']);
    }
} catch (Exception $ex)
{
    echo "ErrorID: <b>" . $ex->getCode() . "</b>; Error Message: <b>" . $ex->getMessage() . "</b>;";
}

/**
 * @param $ts3
 * @param $tempChannelName
 * @param $amountOfCurrentlyExistingTempChannels
 * @param $after
 */
function CreateNewTemporaryChannel($ts3, $tempChannelName, $amountOfCurrentlyExistingTempChannels, $after, $maxClients, $channelPermissions)
{
    $amountOfCurrentlyExistingTempChannels = intval($amountOfCurrentlyExistingTempChannels);
    $newChannelName = $tempChannelName . ($amountOfCurrentlyExistingTempChannels + 1);
    if(!empty($order){
        $after = $order;
    }
    $channelID = $ts3->channelCreate(array(
        "channel_name" => $newChannelName,
        "channel_order" => $after,
        "channel_maxclients" => $maxClients,
        "channel_flag_maxclients_unlimited" => false,
        "channel_codec" => TeamSpeak3::CODEC_OPUS_VOICE, //  See: https://docs.planetteamspeak.com/ts3/php/framework/class_team_speak3.html#ac6e83b47f7d7d5f832195fa500095dc3
        "channel_flag_permanent" => true
    ));

    $channel = $ts3->channelGetById($channelID);
       
    if(!empty($description)){
        $channel->modify(array("channel_description" => $description));
    }
    
    foreach ($channelPermissions as $permission){
        $permissionArr = explode('=',$permission);
        $channel->permAssign($permissionArr[0],$permissionArr[1]);
    }
}

/**
 * @param $PublicChannelsInfo
 * @param $Channels
 * @param $tempChannelName
 * @return int
 */
function GetLastPublicChannel($PublicChannelsInfo, $Channels, $tempChannelName)
{
    $cid = 0;
    $cid = end($PublicChannelsInfo)['cid'];

    foreach ($Channels as $Channel)
    {
        if (stristr($Channel['channel_name'], $tempChannelName))
        {
            $cid = $Channel['cid'];
        }
    }
    return $cid;
}

/**
 * @param $Channels
 * @param $tempChannelName
 * @return int
 */
function CheckForExistingTemporaryPublicChannels($Channels, $tempChannelName)
{
    $amount = 0;
    foreach ($Channels as $Channel)
    {
        if (stristr($Channel['channel_name'], $tempChannelName))
        {
            $amount++;
        }
    }
    return $amount;
}

/**
 * @param $Channels
 * @param $tempChannelName
 * @return int
 */
function CheckForOccupiedTemporaryChannels($Channels, $tempChannelName)
{
    $amount = 0;
    foreach ($Channels as $Channel)
    {
        if (stristr($Channel['channel_name'], $tempChannelName))
        {
            if ($Channel['total_clients'] != "0")
            {
                $amount++;
            }
        }
    }
    return $amount;
}

/**
 * @param $Channels
 * @param $tempChannelName
 * @param $amountOfNeededChannels
 * @param $ts3
 */
function CheckForEmptyExistingTemporaryPublicChannel($Channels, $tempChannelName, $amountOfNeededChannels, $ts3)
{
    $amount = 0;
    foreach ($Channels as $Channel)
    {
        if (stristr($Channel['channel_name'], $tempChannelName))
        {
            $amount++;
            if($amount > $amountOfNeededChannels)
            {
                $Channel->delete(true);
            }
        }
    }
}

/**
 * @param $Channels
 * @param $tempChannelName
 * @param $ts3
 */
function DeleteAllTemporaryPublicChannels($Channels, $tempChannelName, $ts3)
{
    foreach ($Channels as $Channel)
    {
        if (stristr($Channel['channel_name'], $tempChannelName))
        {
            if($Channel['total_clients'] == "0") {
                $Channel->delete();
            }
        }
    }
}
