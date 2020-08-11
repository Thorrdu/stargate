<?php
//STARGATE FR
return [
    "askBaseParameter" => "Actions possibles:\n".
                          "explore\n".
                          "trade\n".
                          "spy\n".
                          "attack\n",
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
                      "Destination: [:coordinateDestination] (:player)\n".
                      "Ressources:\n".
                      ":resources\n".
                      "Coût: :consumption\n\n".
                      "Statut de l'envoi: **En attente**",
    'unknownResource' => "Ressource inconnue: :resource",
    "spyConfirmation" => "Envoyer une sonde espionner [:coordinateDestination] (:player) ?\nCoût: :consumption",
    "spySending" => "Vous avez lancé une mission d'espionnage sur [:coordinateDestination] (:player) !\nCoût: :consumption\n\nUn rapport vous sera envoyé sous peu.",
    "messageSpied" => "Vous avez été visé par une mission d'espionnage en provenance de [:sourceCoordinates] (:player)."
];