<?php

use Httpful\Request;

class SmartHome {

    private $host;
    private $password;
    private $username;

    private $uuid;

    private $session_id;
    private $currentConfiguration;


    private $rooms = array();

    function __construct($host)
    {
        $this->host = $host;


    }


    function logon($username, $password) {


        $this->username = $username;
        $this->password = $password;

        $this->uuid = getGUID();

        $this->password_hash = base64_encode(hash('sha256',$this->password, true));


        $login_request =
            "<BaseRequest xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:type=\"LoginRequest\" Version=\"1.70\" RequestId=\"".
            $this->uuid.
            "\" UserName=\"".
            $this->username.
             "\" Password=\"".
            $this->password_hash . "\" />";

        error_log($login_request);


        $request = Request::post('https://'. $this->host . '/cmd')->body($login_request);

        $request->addHeader('clientId', getGUID());
        $request->sendsType('text/xml');

        $response = $request->send();

        error_log($response->raw_body);


        $xml = simplexml_load_string($response->raw_body);

        foreach ($xml->attributes() as $key => $value) {

            if ($key == 'SessionId') {
                $this->session_id = $value;
            }

            if ($key == 'CurrentConfigurationVersion') {
                $this->currentConfiguration = $value;
            }

        }

        error_log($this->session_id);


    }

    function getDevices()
    {

        $device_request = "<BaseRequest xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:type=\"GetAllLogicalDeviceStatesRequest\" Version=\"1.70\" RequestId=\"".
            $this->uuid .
            "\" SessionId=\"" .
            $this->session_id .
            "\" BasedOnConfigVersion=\"".
            $this->currentConfiguration.
            "\" />";

        $request = Request::post('https://'. $this->host . '/cmd')->body($device_request);

        $request->addHeader('clientId', getGUID());
        $request->sendsType('text/xml');

        $response = $request->send();

        $xml = new DOMDocument();
        $xml->loadXML($response->raw_body);


        $devices = $xml->getElementsByTagName('LogicalDeviceState');




        for ($i=0; $i<$devices->length; $i++) {

            $device = $devices->item($i);

            error_log(print_r($device->getAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'type'), true));

        }


    }


    function getConfiguration()
    {

        $config_request = "<BaseRequest xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:type=\"GetEntitiesRequest\" Version=\"1.70\" RequestId=\"".
             $this->uuid .
             "\" SessionId=\"" .
             $this->session_id .
             "\">\n".
             "<EntityType>Configuration</EntityType></BaseRequest>";


        $request = Request::post('https://'. $this->host . '/cmd')->body($config_request);

        $request->addHeader('clientId', getGUID());
        $request->sendsType('text/xml');

        $response = $request->send();


        $this->processRooms($response->raw_body);





    }


    private function processRooms($response_xml) {


        $xml = simplexml_load_string($response_xml);

        foreach($xml->LCs->LC as $xml_room) {

            $id = (String) $xml_room->Id;
            $name = (String) $xml_room->Name;
            $position = (int) $xml_room->Position;
            $room_type = (String) $xml_room->RTyp;

            $room = new Room($id, $name, $position, $room_type);

            $this->rooms[$id] = $room;

        }


    }


    function getRooms()
    {

        if (count($this->rooms) == 0) {

            $this->getConfiguration();

        }

        return $this->rooms;

    }

    function getPhysicalDevices()
    {

        $physical_devices_request = "<BaseRequest xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:type=\"GetAllPhysicalDeviceStatesRequest\" Version=\"1.70\" RequestId=\"".
            $this->uuid .
            "\" SessionId=\"" .
            $this->session_id .
            "\" />";


        $request = Request::post('https://'. $this->host . '/cmd')->body($physical_devices_request);

        $request->addHeader('clientId', getGUID());
        $request->sendsType('text/xml');

        $response = $request->send();


        error_log($response->raw_body);

    }

}


class Room {

    private $id, $name, $position, $room_type;

    function __construct($id, $name, $position, $room_type) {

        $this->id = $id;
        $this->name = $name;
        $this->position = $position;
        $this->room_type = $room_type;


    }

    function getId()
    {

        return $this->id;

    }

    function getName()
    {

        return $this->name;

    }

    function getPosition()
    {

        return $this->position;

    }

    function getRoomType()
    {
        $this->room_type;
    }


}



