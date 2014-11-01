<?php

include('init.php');

$home = new SmartHome(HOST);

$home->logon(USERNAME, PASSWORD);


#$home->getDevices();

#$home->getConfiguration();

#$home->getPhysicalDevices();


$rooms = $home->getRooms();


foreach ($rooms as $room) {

    echo 'GUID: '. $room->getId();
    echo 'Name: '. $room->getName();
    echo 'Type: '. $room->getType();

}