<?php
//FleetCommand EN
return [
    "askBaseParameter" => "Available Actions:\n".
                        "**order** (`!fleet order [FleetID] return`)\n".
                        //"**explore** (`!fleet explore [coordinates]`)\n".
                        //"**colonize** (`!fleet colonize [coordinates]`)\n".
                        "**base** (`!fleet base [colonyNumber] [Ship1] [Qty] [Res1] [Qty]`)\n".
                        "**transport** (`!fleet transport [coordinates] [Res] [Qty]`)\n".
                        //"**spy** (`!fleet spy [coordinates]`) \n".
                        //"**attack** (`!fleet attack [coordinates] military [Qty] [Unit] [Qty]`)\n"
                        "Optional parameter: speed [10-100]",
    "fleetMessage" => "__Travel from :planetSource [:coordinateSource]__\n".
                        "Destination: Colony :planet [:coordinateDestination]\n".
                        "Mission: **:mission**\n".
                        "**Fleet**\n".
                        ":fleet\n".
                        "**Freight (:freightCapacity)**\n".
                        ":resources\n".
                        "Crew: :crew \n".
                        "Speed: :speed (:maxSpeed%)\n".
                        "Fuel: :fuel\n".
                        "Flight duration: :duration\n".
                        "Sending status: **Awaiting**",
    'missionReturn' => "Fleet arrival on **:planetDest [:coordinateDestination]**\n"
                        ."Origin: **:planetSource [:coordinateSource]**\n\n"
                        ."__Fleet__\n"
                        .":fleet\n"
                        ."__Freight__\n"
                        .":resources\n",
    'transportMission' => "Your fleet has arrived on **:planetDest [:coordinateDestination] (:playerDest)**\n"
                    ."Origin: **:planetSource [:coordinateSource]**\n\n"
                    ."__Fleet__\n"
                    .":fleet\n"
                    ."__Freight__\n"
                    .":resources\n"
                    ."Your fleet is now heading toward **:planetSource [:coordinateSource]** and will return in :duration",
    'transportReceived' => "Fleet arrival on **:planetDest [:coordinateDestination]**\n"
                    ."Origin: **:planetSource [:coordinateSource]**\n\n"
                    ."In his great kindness, **:playerSource** has sent you the following resources:\n"
                    .":resources\n",
    'fleetReturning' => "Mission Cancelled, your fleet is now heading towards **:planetSource [:coordinateSource]** and will return in :duration",
    'alreadyReturning' => 'This fleet is already returning.',
    'activeFleets' => "__Active fleets__\n:fleets",
    'incomingFleets' => "__Incoming stranger's fleets__\n:fleets",
    'noActiveFleet' => 'No active fleet',
    'noIncomingFleet' => 'No incoming fleet',
    'returningStatus' => 'Returning',
    'ongoingStatus' => 'Ongoing',
    'activeFleet' => '[**:mission/:status**] [ID:**:id**] Your fleet of **:shipCount ships** coming from **:colonySource [:coordinatesSource]** is heading towards **:colonyDest [:coordinatesDest]**',
    'incomingFleet' => '[**:mission**] A stranger fleet of **:shipCount ships** coming from **:colonySource [:coordinatesSource]** is heading towards **:colonyDest [:coordinatesDest]**',
    'noResourceSeleted' => 'No selected resource...',
    'unknownFleet' => 'Unknown fleet',
    'wrongParameter' => 'Wrong parameters. Read `!help fleet` for more help',
    'notEnoughCapacity' => 'The storage capacity of your fleet is too low to transport that much resources. Missing storage: :missingCapacity',
    'noShipSelected' => 'No selected ship',
    'missingComTech' => 'IT and Communication required',
];
