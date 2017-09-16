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

try {
    TeamSpeak3::init();
    $ts3 = TeamSpeak3::factory("serverquery://{$config["Username"]}:{$config["Password"]}@{$config["serverIP"]}:{$config["qPort"]}/?server_port={$config["sPort"]}&nickname={$config["BotName"]}#no_query_clients");
    while (true) {
        set_time_limit(30); // Preventing a PHP Timeout
        //loop through
        foreach ($config['TopChannel'] as $top_channel) {
            //the top channels and set top_channel for array of publicchannels
            $occupiedChannels = 0;
            $ts3->channelListReset();
            //Child list from current topchannel
            $ts3Channels = $ts3->channelGetbyId($top_channel)->subChannelList();
            $PublicChannelInfo = array();

            foreach ($config['PublicChannels'][$top_channel] as $PublicChannel) {
                $ChannelInfo = $ts3->channelGetbyID($PublicChannel);
                if ($ChannelInfo['total_clients'] != "0") {
                    $occupiedChannels++;
                }
                $PublicChannelInfo[] = $ChannelInfo;
            }

            if ($occupiedChannels != count($config['PublicChannels'][$top_channel])) {
                DeleteAllTemporaryPublicChannels($ts3Channels, $config['TempChannelName'], $ts3, $config['TempMaxClients']);
            }

            if ($occupiedChannels == count($config['PublicChannels'][$top_channel])) {
                $amountOfExistingTemporaryChannels = CheckForExistingTemporaryPublicChannels($ts3Channels, $config['TempChannelName']);
                $amountOfOccupiedTemporaryChannels = CheckForOccupiedTemporaryChannels($ts3Channels, $config['TempChannelName']);
                $amountOfNeededTemporaryChannels = ($amountOfOccupiedTemporaryChannels + 1);

                CheckForEmptyExistingTemporaryPublicChannel($ts3Channels, $config['TempChannelName'], $amountOfNeededTemporaryChannels, $ts3);
                if ($amountOfExistingTemporaryChannels <= $amountOfOccupiedTemporaryChannels) {
                    CreateNewTemporaryChannel($ts3Channels,$ts3, $config['TempChannelName'], $amountOfExistingTemporaryChannels, $top_channel, $config['TempMaxClients'], $config['ChannelPermissions'], $config['channel_order'], $config['channel_description'], $config['channel_codec'], $config['channel_codec_quality']);
                }
            }
            sleep($config['CheckDelay']);
        }
    }
} catch (Exception $ex) {
    echo "ErrorID: <b>" . $ex->getCode() . "</b>; Error Message: <b>" . $ex->getMessage() . "</b>;";
}

/**
 * @param $ts3
 * @param $tempChannelName
 * @param $amountOfCurrentlyExistingTempChannels
 * @param $TopChannel
 * @param $maxClients
 * @internal param $after
 */
function CreateNewTemporaryChannel($ts3Channels, $ts3, $tempChannelName, $amountOfCurrentlyExistingTempChannels, $TopChannel, $maxClients, $channelPermissions, $order, $description, $codec, $codec_quality) {
    $amountOfCurrentlyExistingTempChannels = intval($amountOfCurrentlyExistingTempChannels);
    $newChannelName = $tempChannelName . ($amountOfCurrentlyExistingTempChannels + 1);
	while(in_array($newChannelName, $ts3Channels)){
		$newChannelName = $tempChannelName . (substr($newChannelName, strlen($tempChannelName)) + 1);
	}
    
    if (empty($order)) {
        $channelID = $ts3->channelCreate(array("channel_name" => $newChannelName, "cpid" => $TopChannel, "channel_maxclients" => $maxClients, "channel_flag_maxclients_unlimited" => false, "channel_codec" => $codec, "channel_codec_quality" => $codec_quality, "channel_flag_permanent" => true));
    }
    else {
        $channelID = $ts3->channelCreate(array("channel_name" => $newChannelName, "cpid" => $TopChannel, "channel_order" => $order, "channel_maxclients" => $maxClients, "channel_flag_maxclients_unlimited" => false, "channel_codec" => $codec, "channel_codec_quality" => $codec_quality, "channel_flag_permanent" => true));
    }
    $channel = $ts3->channelGetById($channelID);
    if (!empty($description)) {
        $channel->modify(array("channel_description" => $description));
    }

    foreach ($channelPermissions as $permission) {
        $permissionArr = explode('=', $permission);
        $channel->permAssign($permissionArr[0], $permissionArr[1]);
    }
}

/**
 * @param $PublicChannelsInfo
 * @param $Channels
 * @param $tempChannelName
 * @return int
 */
function GetLastPublicChannel($PublicChannelsInfo, $Channels, $tempChannelName) {
    $cid = 0;
    $cid = end($PublicChannelsInfo)['cid'];

    foreach ($Channels as $Channel) {
        if (stristr($Channel['channel_name'], $tempChannelName)) {
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
function CheckForExistingTemporaryPublicChannels($Channels, $tempChannelName) {
    $amount = 0;
    foreach ($Channels as $Channel) {
        if (stristr($Channel['channel_name'], $tempChannelName)) {
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
function CheckForOccupiedTemporaryChannels($Channels, $tempChannelName) {
    $amount = 0;
    foreach ($Channels as $Channel) {
        if (stristr($Channel['channel_name'], $tempChannelName)) {
            if ($Channel['total_clients'] != "0") {
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
function CheckForEmptyExistingTemporaryPublicChannel($Channels, $tempChannelName, $amountOfNeededChannels, $ts3) {
    $amount = 0;
    foreach ($Channels as $Channel) {
        if (stristr($Channel['channel_name'], $tempChannelName)) {
            $amount++;
            if ($amount > $amountOfNeededChannels) {
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
function DeleteAllTemporaryPublicChannels($Channels, $tempChannelName, $ts3, $maxClients) {
    foreach ($Channels as $Channel) {
        if (stristr($Channel['channel_name'], $tempChannelName)) {
            //To be used in a later version until issue is fixed
            /*$Channel->modify(array(
                "channel_maxclients" => 0
            ));*/
            if ($Channel['total_clients'] == "0") {
                $Channel->delete();
            }
            /*else{
                $Channel->modify(array(
                    "channel_maxclients" => $maxClients
                ));
            }*/
        }
    }
}
