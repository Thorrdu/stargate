<?php
//STARGATE FR
return [
    "askBaseParameter" => "Actions possibles:\n".
                            "**explore** (`!stargate explore [coordonées]`)\n".
                            "**colonize** (`!stargate colonize [coordonées]`)\n".
                            "**move** (`!stargate move [NuméroDeColonie] [Res1] [Qté1]`)\n".
                            "**trade** (`!stargate move [coordonées] [Res1] [Qté1]`)\n".
                            "**spy** (`!stargate spy [coordonées]`)\n".
                            "**attack** (`!stargate attack [coordonées] military [Qté] [Unit1] [Qté1]`)\n",
    "unknownCoordinates" => "Coordonées inconnues",
    "stargateShattered" => "La porte des étoiles est actuellement hors service.\nFournir d'avantage de ressources à vos scientifiques pourrait être la solution...",
    "failedDialing" => "L'appel vers cette planète à échoué. Il n'y a pas de porte à contacter à ces coordonées.",
    "alreadyExploring" => "Une mission d'exploration est déjà en cours.",
    "alreadyExplored" => "Vous avez déjà exploré cette planète.",
    "explorationSent" => "L'équipe d'exploration est bien arrivée sur la planète [:coordinates].\nUn rapport vous sera envoyé à la fin de la mission.",
    "exploreSucessResources" => "En explorant la planète [:coordinates], l'équipe est tombée sur un entrepôt contenant des ressources intéréssantes.\n".
                                "Ils ont ramenés avec eux: :resources",
    "explorePlayerImpossible" => "Il n'est pas possible d'explorer la planète d'un autre joueur. essayez l'espionnage.",
    "exploreFailed" => "Vos scientifiques n'ont rien trouvé d'intéréssant en explorant la planète [:coordinates].",
    "exploreSucessBuildingTip" => "Vos scientifiques ont trouvé l'information suivante en explorant la planète [:coordinates]:\n".
                                  "Le bâtiment :name requiert Lvl :lvlRequirement: :nameRequirement",
    "exploreSucessTechnologyTip" => "Vos scientifiques ont trouvé l'information suivante en explorant la planète [:coordinates]:\n".
                                    "La technologie :name requiert Lvl :lvlRequirement: :nameRequirement",
    "exploreSucessCraftTip" => "Vos scientifiques ont trouvé l'information suivante en explorant la planète [:coordinates]:\n".
                               "Le craft :name requiert Lvl :lvlRequirement: :nameRequirement",
    "exploreCriticalFailed" => "L'équipe envoyée sur [:coordinates] n'a envoyé aucun signe de vie",
    "tradeReceived" => "/!\ Incoming traveler /!\ \n\n".
                       "Une activation extérieure à été détectée sur [:coordinateDestination] en provenance de [:coordinateSource] (:player)\n\n".
                       "Les ressources suivantes vous ont été délivrées:\n:resources",
    "tradeSent" => "Vous avez envoyé les ressources suivantes depuis [:coordinateSource] sur [:coordinateDestination] (:player):\n:resources\nPour un coût de: :consumption",
    "tradeMessage" => "__Envoi de ressources depuis la planète [:coordinateSource]__\n".
                      "Destination: : planet [:coordinateDestination] (:player)\n".
                      "Ressources:\n".
                      ":resources\n".
                      "Coût: :consumption\n\n".
                      "Statut de l'envoi: **En attente**",
    "moveMessage" => "__Envoi de ressources depuis la planète [:coordinateSource]__\n".
                      "Destination: Colonie :planet [:coordinateDestination]\n".
                      "Ressources:\n".
                      ":resources\n".
                      "Coût: :consumption\n\n".
                      "Statut de l'envoi: **En attente**",
    'unknownResource' => "Ressource inconnue: :resource",
    "spyConfirmation" => "Envoyer une sonde espionner [:coordinateDestination] (:player) ?\nCoût: :consumption",
    "spySending" => "Vous avez lancé une mission d'espionnage sur [:coordinateDestination] (:player) !\nCoût: :consumption\n\nUn rapport vous sera envoyé sous peu.",
    "messageSpied" => "Vous avez été visé par une mission d'espionnage en provenance de [:sourceCoordinates] (:player).",
    "emptyReportTitle" => "Rapport incomplet",
    "technologyTooLow" => "Aucune donnée n'a pu être récupérée , votre niveau d'espionnage est trop faible.",
    "spyReportDescription" => "Rapport d'espionnage de la planète [:coordinateDestination] (:player)",
    'fleet' => 'Flotte',
    'emptyFleet' => 'Aucun flotte à quai',
    'defences' => 'Défenses',
    'emptydefences' => 'Aucune défense',
    'buildings' => 'Bâtiments',
    "colonizeDone" => "Vos troupes accompagnées de quelques scientifiques sont arrivé sur votre nouvelle colonie en [:destination].\nAffichez `!colony` pour découvrir votre nouvelle colonie et `!colony switch [Numéro]` pour changer de colonie ou `!colony remove [Number]` pour en abandonner une.\n(Liste présente dans `!profile`).",
    "toManyColonies" => 'Vous avez atteint le nombre maximal de colonies.',
    "neverExploredWorld" => "Monde inhabité.",
    "AttackConfirmation" => "Envoyer une attaque sur :planetName [:coordinateDestination] (:player) depuis :planetNameSource [:coordinateSource]\n".
                            "Troupes:\n:militaryUnits".
                            "\nCoût: :consumption",
    "attackSent" => "Vos troupes ont été envoyées sur :planet [:coordinateDestination] (:player).\nUn rapport arrivera sous peu.",
    "attackCancelled" => "Attaque annulée",
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
    "noCasuality" => "Aucune perte à déplorer",
    "playerOwned" => "Appartient à un joueur",
    "samePlayerAction" => "Vous ne pouvez effectuer cette action sur vous même...",
    "weakOrStrong" => "Ce joueur est trop for ou trop faible pour vous..."
];