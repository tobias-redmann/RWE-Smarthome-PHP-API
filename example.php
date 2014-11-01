<?php

include('init.php');

$home = new SmartHome(HOST);

$home->logon(USERNAME, PASSWORD);


#$home->getDevices();

#$home->getConfiguration();

#$home->getPhysicalDevices();


$rooms = $home->getRooms();