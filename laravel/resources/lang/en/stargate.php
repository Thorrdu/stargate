<?php
//STARGATE EN
return [
    "askBaseParameter" => "Available Actions:\n".
                          "**explore** (`!stargate explore [coordinates]`)\n".
                          "**colonize** (`!stargate colonize [coordinates]`)\n".
                          "**move** (`!stargate move [colonyNumber] [Resource] [Qty]`)\n".
                          "**trade** (`!stargate trade [coordinates] [Resource] [Qty]`)\n".
                          "**spy** (`!stargate spy [coordinates]`) \n".
                          "**attack** (`!stargate attack [coordinates] military [Qty] [Unit] [Qty]`)\n".
                          "**bury** (`!stargate bury`)\n",
    "unknownCoordinates" => "Unknown Coordinates",
    "unReacheableCoordinates" => 'Unreachable Coordinates',
    "stargateShattered" => "The Stargate is shattered and not ready for proper usage.\nMay be giving your scientist more resources could help solve this problem...\n\n".
                            "**Lvl 5 - Research Laboratory** required to use the gate to contact other planets.\n".
                            "However, at Lvl 4 you'll be able to receive gate dialing",
    "failedDialing" => "The dialing to this planet failed. There is no gate to contact on those coordinates.",
    "maxExplorationReached" => "Too many exploration missions are already ongoing.",
    "alreadyExplored" => "You already explored this planet.",
    "explorationSent" => "The exploration team is arrived on the planet [:coordinates].\nA report will be send at the end of the mission.",
    "exploreSucessResources" => "During the exploration of the planet [:coordinates], the team has found a warehouse with interesting resources.\n".
                                "They brought back with them:\n :resources",
    "explorePlayerImpossible" => "It's not possible to explore another player's planet. try spy.",
    "exploreFailed" => "Your explorers have not found anything interesting during the exploration of the planet [:coordinates].",
    "exploreFailed2" => "Your exploration team has detected an hostile presence on the planet [:coordinates] it was exploring. Impossible to gather more information.",
    "exploreSucessArtifact" => "Your explorer have brough back an artifact from the planet [:coordinates]:\n".
                                ":artifact",
    "exploreSucessBuildingTip" => "Your explorers have found the following information during the exploration of the planet [:coordinates]:\n".
                                  "The building **:name** requires:\n:requirements",
    "exploreSucessTechnologyTip" => "Your explorers have found the following information during the exploration of the planet [:coordinates]:\n".
                                    "The technology **:name** requires:\n:requirements",
    "exploreSucessCraftTip" => "Your explorers have found the following information during the exploration of the planet [:coordinates]:\n".
                               "The craft **:name** requires:\n:requirements",
    "exploreCriticalFailed" => "The team sent to the planet [:coordinates] has not gave any life sign...",
    "tradeReceived" => "/!\ Incoming traveler /!\ \n\n".
                       "External activation detected on :planetDest [:coordinateDestination] incoming from :planetSource [:coordinateSource] (:player)\n\n".
                       "The following resources have been delivred:\n:resources",
    "tradeSent" => "You sent the following resources from :planetSource [:coordinateSource] to :planetDest [:coordinateDestination] (:player):\n:resources\nAt a cost of: :consumption",
    "tradeMessage" => "__Sending resources from :planetSource [:coordinateSource]__\n".
                      "Destination: :planet [:coordinateDestination]\n\n".
                      "**Freight (:freightCapacity)**\n".
                      ":resources\n".
                      "Cost: :consumption\n\n".
                      "Sending status: **Awaiting**",
    "moveMessage" => "__Sending de ressources depuis la planète :planetSource [:coordinateSource]__\n".
                      "Destination: Colony :planet [:coordinateDestination]\n\n".
                      "**Freight (:freightCapacity)**\n".
                      ":resources\n".
                      "Coût: :consumption\n\n".
                      "Sending status: **Awaiting**",
    'unknownResource' => "Unknown resource: :resource",
    "spyConfirmation" => "Send a spy mission on :planetDest [:coordinateDestination] ?\nCost: :consumption",
    "spySending" => "You sent a spy mission on :planetDest [:coordinateDestination] !\nCost: :consumption\n\nA report will be delivred soon.",
    "messageSpied" => "Your colony :planetName [:coordinate] targeted by a spy mission comming from :planetSource [:sourceCoordinates].",
    "emptyReportTitle" => "Partial report",
    "technologyTooLow" => "No date has been recovered, due to Spy technology too low",
    "spyReportDescription" => "Spy report from :planetDest [:coordinateDestination] (:player)",
    'fleet' => 'Fleet',
    'emptyFleet' => 'No fleet docked',
    'defences' => 'Defences',
    'emptydefences' => 'No defence',
    'buildings' => 'Buildings',
    "colonizeDone" => "You troops joined with some scientists have reached your new colony on [:destination].\nUse `!colony` to discover your new colony and `!colony switch [Number]` switch between your colonies or `!colony remove [Number]` to remove one.\n(The list can be seen with `!colony list`).",
    "toManyColonies" => 'You reached the maximum amount of colonies.',
    "neverExploredWorld" => "Empty planet.",
    "attackConfirmation" => "Send an attack on :planetName [:coordinateDestination] from :planetNameSource [:coordinateSource]\n".
                            "Troops:\n:militaryUnits".
                            "\nCost: :consumption",
    "exploreConfirmation" => "Send an exploration team to [:coordinateDestination] ?\n".
                            "Troops: :militaryUnits".
                            "\nCost: :consumption",
    "colonizeConfirmation" => "Send a colonization team to [:coordinateDestination] ?\n".
                            "Troops: :militaryUnits".
                            "\nCost: :consumption",
    "attackSent" => "Your troops have been send on :planet [:coordinateDestination].\nA report will arrive soon.",
    "attackCancelled" => "Attack cancelled",
    "attackerWinReport" => "Attack through Stargate [:destination] (:player)\n\n".
                            "Your troops emerged victorious from the fight against **:player** on planet **:planetDest [:destination]**\n\n".
                            "Losses result:\n:loostTroops\n".
                            "Winning result:\n:raidReward",
    "attackerLostReport" => "Attack through Stargate [:destination] (:player)\n\n".
                            "Your troops were decimated in the fight against **:player** on planet **:planetDest [:destination]**\n\n".
                            "Losses result:\n:loostTroops\n",
    "defenderWinReport" => "Attack through Stargate [:destination] (:player)\n\n".
                            "An attack has occurred from the planet **:sourcePLanet [:sourceDestination] (:sourcePlayer)**.\n".
                            "Fortunately, your troops have contained the attack and push back the invader.\n\n".
                            "Losses result:\n:loostTroops\n".
                            "Estimated ennemies troops:\n:estimatedAttackTroops\n".
                            "Winning result::\n:raidReward",
    "defenderLostReport" => "Attack through Stargate [:destination] (:player)\n\n".
                            "An attack has occurred from the planet **:sourcePLanet [:sourceDestination] (:sourcePlayer)**.\n".
                            "Unfortunately, your troops have not succed to contain the attack.\n\n".
                            "Losses result:\n:loostTroops\n".
                            "Estimated ennemies troops:\n:estimatedAttackTroops\n",
    "noCasuality" => "No casualties",
    "playerOwned" => "Already owned by a player",
    "samePlayerAction" => "You can't make this action to yourself...",
    "notAColonyOfYour" => "This colony isn't one of your...",
    "weakOrStrong" => "This player is too weak or too strong for you...",
    "AttackLimit" => "You already attacked this planet earlier. you'll be able to attack again in: :time",
    "tradeNpcImpossible" => "Impossible to trade with an NPC",
    'spyCancelled' => "Spy cancelled",
    'colonizeCancel' => 'Colonize Cancelled',
    'tradeStorageTooLow' => 'This planet has not enough storage capacity to receive that much :resource',
    'alreadySpied' => 'You spied this colony too recently. You\'ll be able to spy this colony again in **:time**',
    'digingStarted' => 'You stargate diging up, your stargate will be fully operational again in **48h**.',
    'burialStarted' => "You stargate your stargate burial. Your stargate will be fully inoperant in **12h**.\nHowever, external activation can still occur until the process is complete.",
    'digUpConfirm' => 'Do you want to dig up your Stargate ? This action will take **24h**.',
    'burryConfirm' => "Do you want to bury your Stargate? This action will take **12h**.\nWarning, As soon as the process starts, your Stargate will be out of service but external activation can occur until the process is complete.",
    'digingActive' => 'You are already diging up. Stargate operational in: **:time**',
    'buryingActive' => 'You are already burying your Stargate. Out of service in: **:time**',
    'buriedStargate' => 'Stargate out of service.',
    'playerTradeBan' => 'This played have been banned from the trading system.',
    'sameColony' => 'Destination coordonates are the same as origin...',
    'trade_ban' => 'You are banned from the trading system. If it\'s not already done, you can ask for an extention delay of 12h to regularize the trad. See `!trade [ID]`',
    "probeSpySending" => "You started a spy mission on :planetDest [:coordinateDestination] !\nCost: :consumption\nYour probe will arrive in **:fleetDuration**\n\n",
    'explorationList' => 'Explorations list',
    'emptyExploHistory' => 'Empty exploration history',
    'explorationOngoing' => 'EXPLORING',
];
