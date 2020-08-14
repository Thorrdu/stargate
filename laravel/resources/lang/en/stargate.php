<?php
//STARGATE EN
return [
    "askBaseParameter" => "Available Actions:\n".
                          "explore\n".
                          "trade\n".
                          "spy\n".
                          "attack\n",
    "unknownCoordinates" => "Unknown Coordinates",
    "stargateShattered" => "The Stargate is shattered and not ready for proper usage.\nMay be giving your scientist more resources could help solve this problem...",
    "failedDialing" => "The dialing to this planet failed. There is no gate to contact on those coordinates.",
    "alreadyExploring" => "An exploration mission is already ongoing.",
    "alreadyExplored" => "You already explored this planet.",
    "explorationSent" => "The exploration team is arrived on the planet [:coordinates].\nA report will be send at the end of the mission.",
    "exploreSucessResources" => "During the exploration of the planet [:coordinates], the team has found a warehouse with interresting resources.\n".
    "They brought back with them: :resources",
    "explorePlayerImpossible" => "It's not possible to explore another player's planet. try spy.",
    "exploreFailed" => "Your scientists have not found anything interresting during the exploration of the planet [:coordinates].",
    "exploreSucessBuildingTip" => "Your scientists have found the following information during the exploration of the planet [:coordinates]:\n".
                                  "The building :name requires Lvl :lvlRequirement: :nameRequirement",
    "exploreSucessTechnologyTip" => "Your scientists have found the following information during the exploration of the planet [:coordinates]:\n".
                                    "The technology :name requires Lvl :lvlRequirement: :nameRequirement",
    "exploreSucessCraftTip" => "Your scientists have found the following information during the exploration of the planet [:coordinates]:\n".
                               "The craft :name requires Lvl :lvlRequirement: :nameRequirement",
    "exploreCriticalFailed" => "The tam sent to the planet [:coordinates] has not gave any life sign...",
    "tradeReceived" => "/!\ Incoming traveler /!\ \n\n".
                       "External activation detected on [:coordinateDestination] incoming from [:coordinateSource] (:player)\n\n".
                       "The following resources have been delivred:\n:resources",
    "tradeSent" => "You sent the following resources from [:coordinateSource] to [:coordinateDestination] (:player):\n:resources\nAt a cost of: :consumption",
    "tradeMessage" => "__Sending resources from [:coordinateSource]__\n".
                      "Destination: [:coordinateDestination] (:player)\n".
                      "Resources:\n".
                      ":resources\n".
                      "Cost: :consumption\n\n".
                      "Sending status: **Awaiting**",
    'unknownResource' => "Unknown resource: :resource",
    "spyConfirmation" => "Send a spy mission on [:coordinateDestination] (:player) ?\nCost: :consumption",
    "spySending" => "You sent a spy mission on [:coordinateDestination] (:player) !\nCost: :consumption\n\nA report will be delivred soon.",
    "messageSpied" => "You've been targeted by a spy mission comming from [:sourceCoordinates] (:player).",
    "emptyReportTitle" => "Partial report",
    "technologyTooLow" => "No date has been recovered, due to Spy technology too low",
    "spyReportDescription" => "Spy report from planet [:coordinateDestination] (:player)",
    'fleet' => 'Fleet',
    'emptyFleet' => 'No fleet docked',
    'defenses' => 'Defences',
    'emptyDefenses' => 'No defense',
    'buildings' => 'Buildings',
    "colonizeDone" => "You troops joined with some scientists have reached your new colony on [:destination].\nUse `!colony` to discover your new colony and `!colony switch [Number]` switch between your colonies.\n(The list can be seen in `!p`).",
    "toManyColonies" => 'You reached the maximum amount of colonies.',
    "neverExploredWorld" => "Empty planet.",
    "AttackConfirmation" => "Send an attack on :planetName [:coordinateDestination] (:player) from :planetNameSource [:coordinateSource]\n".
                            "Troops:\n:militaryUnits".
                            "\nCost: :consumption",
    "attackSent" => "Your troops have been send on :planet [:coordinateDestination] (:player).\nA report will arrive soon.",
    "attackCancelled" => "Attack cancelled",
    "attackerWinReport" => "Attaque par la porte des étoiles [:destination] (:player)\n\n".
                            "Vos troupes sont sorties victorieuses du combat contre **:player** sur la planète **:planetName [:destination]**\n\n".
                            "Bilan des pertes:\n:loostTroops\n".
                            "Bilan des gains:\n:raidReward",
    "attackerLostReport" => "Attaque par la porte des étoiles [:destination] (:player)\n\n".
                            "Vos troupes ont été décimées lors du combat contre **:player** sur la planète **:planetName [:destination]**\n\n".
                            "Bilan des pertes:\n:loostTroops",
    "defenderWinReport" => "Attaque par la porte des étoiles [:destination] (:player)\n\n".
                            "Une attaque est survenue en provenance de la planète **:sourcePLanet [:sourceDestination] (:sourcePlayer)**.\n".
                            "Fort heureusement, vos troupes ont peu contenir l'attaque et repousser l'envahisseur.\n\n".
                            "Bilan des pertes:\n:loostTroops".
                            "Bilan des gains:\n:raidReward",
    "defenderLostReport" => "Attaque par la porte des étoiles [:destination] (:player)\n\n".
                            "Une attaque est survenue en provenance de la planète **:sourcePLanet [:sourceDestination] (:sourcePlayer)**.\n".
                            "Malheureusement, vos troupes n'ont pas réussi à contenir l'attaque.\n\n".
                            "Bilan des pertes:\n:loostTroops",
];