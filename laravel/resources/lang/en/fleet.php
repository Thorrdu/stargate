<?php
//FleetCommand EN
return [
    "askBaseParameter" => "Available Actions:\n".
                        "**order** (`!fleet order [FleetID] return`)\n".
                        //"**explore** (`!fleet explore [coordinates]`)\n".
                        //"**colonize** (`!fleet colonize [coordinates]`)\n".
                        "**base** (`!fleet base [colonyNumber] [Ship] [Qty] [Res1] [Qty]`)\n".
                        "**transport** (`!fleet transport [coordinates] [Ship] [Qty] [Res] [Qty]`)\n".
                        "**spy** (`!fleet spy [coordinates]`) \n".
                        "**attack** (`!fleet attack [coordinates] [Ship] [Qty]`)\n".
                        "**scavenge** (`!fleet scavenge [Scavengers] [Qty]`)\n".
                        "**history** (`!fleet history`)\n".
                        "Optional parameter: speed [10-100]",
    "fleetMessage" => "__Travel from :planetSource [:coordinateSource]__\n".
                        "Destination: Colony :planetDest [:coordinateDestination]\n".
                        "Mission: **:mission**\n\n".
                        "**Fleet**\n".
                        ":fleet\n".
                        "**Freight (:freightCapacity)**\n".
                        ":resources\n".
                        "Crew: :crew \n".
                        "Speed: :speed (:maxSpeed%)\n".
                        "Fuel: :fuel\n".
                        "Flight duration: :duration\n".
                        "Sending status: **Awaiting**",
    "fleetAttackMessage" => "__Voyage depuis :planetSource [:coordinateSource]__\n".
                        "Destination: :planetDest [:coordinateDestination]\n".
                        "Mission: **:mission**\n\n".
                        "**Fleet**\n".
                        ":fleet\n".
                        "Capacity: :freightCapacity\n".
                        "Crew: :crew \n".
                        "Speed: :speed (:maxSpeed%)\n".
                        "Fuel: :fuel\n".
                        "Flight duration: :duration\n".
                        "Sending status: **Awaiting**",
    'attackArrived' => "Your attack fleet has arrived at **:planetDest [:coordinateDestination] (:playerDest)**\n"
                    ."Origin: **:planetSource [:coordinateSource]**\n"
                    .":battleResult\n"
                    ."\nReport detail: `!fleet history :fleetId`",
    'attacked' => "An attack fleet has arrived on your colony **:planetDest [:coordinateDestination]**\n"
                ."Origin: **:planetSource [:coordinateSource] (:playerSource)**\n"
                .":battleResult\n"
                ."\nReport detail: `!fleet history :fleetId`",
    'missionReturn' => "One of your fleets is arrived on **:planetDest [:coordinateDestination]**\n"
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
    'fleetReturning' => "Mission cancelled, your fleet is now heading towards **:planetSource [:coordinateSource]** and will return in :duration",
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
    'battleSummary' => "**__Battle report between :playerSource et :playerDest__**\n\n".
                        "Origin: :colonySource [:coordinateSource]\n".
                        "Battle location: :colonyDest [:coordinateDest]\n\n".
                        "**Attacker forces (:playerSource)**\n".
                        ":attackForces\n".
                        "**Defender forces (:playerDest)**\n".
                        ":defenceForces\n",
    'passSummary' => "\n__Pass n°:phaseNbr__\n\n".
                    "The attacker has done a total damage of :attackerDamageDone (including :defenderAbsorbedDamage absorbed by shields).\n".
                    "The defender has done a total damage of :defenderDamageDone (including :attackerAbsorbedDamage absorbed by shields).\n\n".
                    "__Attacker losses__\n:lostAttackerUnits\n".
                    "__Defender losses__\n:lostDefenderUnits\n",
    'battleWin' => "\n__Battle summary__\n\n".
                    "The attacker has lost :lostAttackUnit unit(s)\n".
                    "The defender has lost :lostDefenceUnit unit(s):\n\n".
                    "__Stolen resources__\n".
                    ":stolenResources",
    'battleLost' => "\n__Battle summary__\n\n".
                    "The attacker has lost :lostAttackUnit unité(s)\n".
                    "The defender has lost :lostDefenceUnit unité(s)\n\n".
                    "No resource have been stolen",
    'fleetHistory' => 'Fleets history',
    'historyHowTo' => 'Display fleet details with `!fleet history [ID]`',
    'historyLine' => ':fleetId - :date - :mission - :destination',
    'emptyHistory' => 'No fleet history...',
    'ruinFieldGenerated' => 'Ruin field generated: :resources',
    'noScavengerSelected' => 'No scavenger selected',
    "scavengeConfirmation" => "__Travel from :planetSource [:coordinateSource]__\n".
                        "Destination: Colony :planet [:coordinateDestination]\n".
                        "Mission: **:mission**\n\n".
                        "**Scavengers**\n".
                        ":fleet\n".
                        "Speed: :speed (:maxSpeed%)\n".
                        "Fuel: :fuel\n".
                        "Flight duration: :duration\n".
                        "Sending status: **Awaiting**",
    'noResourceSeleted' => 'No resource selected',
    'scavengeMission' => "Arriving of your scavengers a the following coordinates **[:coordinateDestination]**\n"
                    ."Origin: **:planetSource [:coordinateSource]**\n\n"
                    ."__Scavenged resources__\n"
                    .":resources\n",
    'emptyResources' => 'Empty.',
    'scavengerReturn' => "Your scavengers have returned from **:planetDest [:coordinateDestination]**\n"
        ."Origin: **:planetSource [:coordinateSource]**\n\n"
        ."__Scavengers__\n"
        .":fleet\n"
        ."__Freight__\n"
        .":resources\n",
];
