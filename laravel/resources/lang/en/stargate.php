<?php
//STARGATE EN
return [
    "askBaseParameter" => "Available Actions:\n".
                          "**explore** (`!stargate explore [coordinates]`)\n".
                          "**colonize** (`!stargate colonize [coordinates]`)\n".
                          "**move** (`!stargate move [colonyNumber] [Res1] [Qty1]`)\n".
                          "**trade** (`!stargate trade [coordinates] [Res1] [Qty1]`)\n".
                          "**spy** (`!stargate spy [coordinates]`) \n".
                          "**attack** (`!stargate attack [coordinates] military [Qty] [Unit1] [Qty1]`)\n",
    "unknownCoordinates" => "Unknown Coordinates",
    "stargateShattered" => "The Stargate is shattered and not ready for proper usage.\nMay be giving your scientist more resources could help solve this problem...\n\n".
                            "**Lvl 5 - Research Center** required to use the gate to contact other planets.\n".
                            "However, at Lvl 4 you'll be able to receive gate dialing",
    "failedDialing" => "The dialing to this planet failed. There is no gate to contact on those coordinates.",
    "alreadyExploring" => "An exploration mission is already ongoing.",
    "alreadyExplored" => "You already explored this planet.",
    "explorationSent" => "The exploration team is arrived on the planet [:coordinates].\nA report will be send at the end of the mission.",
    "exploreSucessResources" => "During the exploration of the planet [:coordinates], the team has found a warehouse with interresting resources.\n".
    "They brought back with them: :resources",
    "explorePlayerImpossible" => "It's not possible to explore another player's planet. try spy.",
    "exploreFailed" => "Your scientists have not found anything interresting during the exploration of the planet [:coordinates].",
    "exploreSucessBuildingTip" => "Your scientists have found the following information during the exploration of the planet [:coordinates]:\n".
                                  "The building **:name** requires:\n:requirements",
    "exploreSucessTechnologyTip" => "Your scientists have found the following information during the exploration of the planet [:coordinates]:\n".
                                    "The technology **:name** requires:\n:requirements",
    "exploreSucessCraftTip" => "Your scientists have found the following information during the exploration of the planet [:coordinates]:\n".
                               "The craft **:name** requires:\n:requirements",
    "exploreCriticalFailed" => "The tam sent to the planet [:coordinates] has not gave any life sign...",
    "tradeReceived" => "/!\ Incoming traveler /!\ \n\n".
                       "External activation detected on :planetDest [:coordinateDestination] incoming from :planetSource [:coordinateSource] (:player)\n\n".
                       "The following resources have been delivred:\n:resources",
    "tradeSent" => "You sent the following resources from :planetSource [:coordinateSource] to :planetDest [:coordinateDestination] (:player):\n:resources\nAt a cost of: :consumption",
    "tradeMessage" => "__Sending resources from :planetSource [:coordinateSource]__\n".
                      "Destination: :planet [:coordinateDestination] (:player)\n".
                      "Resources:\n".
                      ":resources\n".
                      "Cost: :consumption\n\n".
                      "Sending status: **Awaiting**",
    "moveMessage" => "__Sending de ressources depuis la planète :planetSource [:coordinateSource]__\n".
                      "Destination: Colony :planet [:coordinateDestination]\n".
                      "Resources:\n".
                      ":resources\n".
                      "Coût: :consumption\n\n".
                      "Sending status: **En attente**",
    'unknownResource' => "Unknown resource: :resource",
    "spyConfirmation" => "Send a spy mission on :planetDest [:coordinateDestination] (:player) ?\nCost: :consumption",
    "spySending" => "You sent a spy mission on :planetDest [:coordinateDestination] (:player) !\nCost: :consumption\n\nA report will be delivred soon.",
    "messageSpied" => "Your colony :planetName [:coordinate] targeted by a spy mission comming from :planetSource [:sourceCoordinates] (:player).",
    "emptyReportTitle" => "Partial report",
    "technologyTooLow" => "No date has been recovered, due to Spy technology too low",
    "spyReportDescription" => "Spy report from :planetDest [:coordinateDestination] (:player)",
    'fleet' => 'Fleet',
    'emptyFleet' => 'No fleet docked',
    'defences' => 'Defences',
    'emptydefences' => 'No defence',
    'buildings' => 'Buildings',
    "colonizeDone" => "You troops joined with some scientists have reached your new colony on [:destination].\nUse `!colony` to discover your new colony and `!colony switch [Number]` switch between your colonies or `!colony remove [Number]` to remove one.\n(The list can be seen in `!profile`).",
    "toManyColonies" => 'You reached the maximum amount of colonies.',
    "neverExploredWorld" => "Empty planet.",
    "AttackConfirmation" => "Send an attack on :planetName [:coordinateDestination] (:player) from :planetNameSource [:coordinateSource]\n".
                            "Troops:\n:militaryUnits".
                            "\nCost: :consumption",
    "attackSent" => "Your troops have been send on :planet [:coordinateDestination] (:player).\nA report will arrive soon.",
    "attackCancelled" => "Attack cancelled",
    "attackerWinReport" => "Attaque par la porte des étoiles [:destination] (:player)\n\n".
                            "Vos troupes sont sorties victorieuses du combat contre **:player** sur la planète **:planetDest [:destination]**\n\n".
                            "Bilan des pertes:\n:loostTroops\n".
                            "Bilan des gains:\n:raidReward",
    "attackerLostReport" => "Attaque par la porte des étoiles [:destination] (:player)\n\n".
                            "Vos troupes ont été décimées lors du combat contre **:player** sur la planète **:planetDest [:destination]**\n\n".
                            "Bilan des pertes:\n:loostTroops\n",
    "defenderWinReport" => "Attaque par la porte des étoiles [:destination] (:player)\n\n".
                            "Une attaque est survenue en provenance de la planète **:sourcePLanet [:sourceDestination] (:sourcePlayer)**.\n".
                            "Fort heureusement, vos troupes ont peu contenir l'attaque et repousser l'envahisseur.\n\n".
                            "Bilan des pertes:\n:loostTroops\n".
                            "Bilan des gains:\n:raidReward",
    "defenderLostReport" => "Attaque par la porte des étoiles [:destination] (:player)\n\n".
                            "Une attaque est survenue en provenance de la planète **:sourcePLanet [:sourceDestination] (:sourcePlayer)**.\n".
                            "Malheureusement, vos troupes n'ont pas réussi à contenir l'attaque.\n\n".
                            "Bilan des pertes:\n:loostTroops\n",
    "noCasuality" => "No casualties",
    "playerOwned" => "Already owned by a player",
    "samePlayerAction" => "You can't make this action to yourself...",
    "weakOrStrong" => "This player is too weak or too strong for you...",
    "AttackLimit" => "You already attacked this planet earlier. you'll be able to attack again in: :time",
];