<?php
//FleetCommand FR
return [
    "askBaseParameter" => "Actions possibles:\n".
                            "**order** (`!fleet order [id] return`)\n".
                            //"**explore** (`!fleet explore [Coordonnées]`)\n".
                            //"**colonize** (`!fleet colonize [Coordonnées]`)\n".
                            "**base** (`!fleet base [NuméroDeColonie] [Vaisseaux] [Nombre] [Ressource] [Qté]`)\n".
                            "**transport** (`!fleet transport [Coordonnées] [Vaisseaux] [Nombre] [Ressource] [Qté]`)\n".
                            "**spy** (`!fleet spy [Coordonnées]`)\n".
                            "**attack** (`!fleet attack [Coordonnées] [Vaisseau] [Qté]`)\n".
                            "**scavenge** (`!fleet scavenge [Coordonnées] [Recycleurs] [Qté]`)\n".
                            "**history** (`!fleet history`)\n".
                            "Paramètre optionel: speed [10-100]\n".
                            "Paramètre optionel: boost",
    "fleetMessage" => "__Voyage depuis :planetSource [:coordinateSource]__\n".
                        "Destination: :planetDest [:coordinateDestination]\n".
                        "Mission: **:mission**\n\n".
                        "**Flotte**\n".
                        ":fleet\n".
                        "**Transport (:freightCapacity)**\n".
                        ":resources\n".
                        "Equipage: :crew \n".
                        "Vitesse: :choosedSpeed (:maxSpeed%)\n".
                        "Boost: :boosted\n".
                        "Carburant: :fuel\n".
                        "Durée de vol: :duration\n".
                        "Statut de l'envoi: **En attente**",
    "fleetAttackMessage" => "__Voyage depuis :planetSource [:coordinateSource]__\n".
                        "Destination: :planetDest [:coordinateDestination]\n".
                        "Mission: **:mission**\n\n".
                        "**Flotte**\n".
                        ":fleet\n".
                        "Capacité: :freightCapacity\n".
                        "Equipage: :crew \n".
                        "Vitesse: :choosedSpeed (:maxSpeed%)\n".
                        "Boost: :boosted\n".
                        "Carburant: :fuel\n".
                        "Durée de vol: :duration\n".
                        "Statut de l'envoi: **En attente**",
    'attackArrived' => "Votre flotte d'attaque est arrivée sur **:planetDest [:coordinateDestination] (:playerDest)**\n"
    ."Origine: **:planetSource [:coordinateSource]**\n"
    .":battleResult\n"
    ."\nRapport complet: `!fleet history :fleetId`",
    'attacked' => "Une flotte d'attaque est arrivée sur votre colonie **:planetDest [:coordinateDestination]**\n"
    ."Origine: **:planetSource [:coordinateSource] (:playerSource)**\n"
    .":battleResult\n"
    ."\nRapport complet: `!fleet history :fleetId`",
    'missionReturn' => "Arrivée d'une de vos flotte sur **:planetDest [:coordinateDestination]**\n"
                        ."Origine: **:planetSource [:coordinateSource]**\n\n"
                        ."__Flotte__\n"
                        .":fleet\n"
                        ."__Transport__\n"
                        .":resources\n",
    'transportMission' => "Arrivée de votre flotte sur **:planetDest [:coordinateDestination] (:playerDest)**\n"
                    ."Origine: **:planetSource [:coordinateSource]**\n\n"
                    ."__Flotte__\n"
                    .":fleet\n"
                    ."__Transport__\n"
                    .":resources\n"
                    ."Votre flotte se dirige désormais vers **:planetSource [:coordinateSource]** et arrivera dans :duration",
    'transportReceived' => "Arrivée d'une flotte sur **:planetDest [:coordinateDestination]**\n"
                    ."Origine: **:planetSource [:coordinateSource]**\n\n"
                    ."Dans sa grande bonté, **:playerSource** vous a envoyé les ressources suivantes:\n"
                    .":resources\n",
    'fleetReturning' => "Mission annulée, votre flotte se dirige désormais vers **:planetSource [:coordinateSource]** et arrivera dans :duration",
    'alreadyReturning' => 'Cette flotte se dirige déjà vers sa colonie d\'origine.',
    'activeFleets' => "__Flottes actives__\n:fleets",
    'incomingFleets' => "__Flottes en approche__\n:fleets",
    'noActiveFleet' => 'Aucune flotte active',
    'noIncomingFleet' => 'Aucune flotte en approche',
    'returningStatus' => 'Retour',
    'ongoingStatus' => 'Aller',
    'activeFleet' => '[**:mission/:status**] [ID:**:id**] Votre flotte de **:shipCount :shipType** en provenance de **:colonySource [:coordinatesSource]** se dirige vers **:colonyDest [:coordinatesDest]**',
    'incomingFleet' => '[**:mission**] Une flotte de **:shipCount :shipType** en provenance de **:colonySource [:coordinatesSource]** se dirige vers **:colonyDest [:coordinatesDest]**',
    'noResourceSeleted' => 'Aucune ressource sélectionnée...',
    'unknownFleet' => 'Flotte inconnue',
    'wrongParameter' => 'Mauvais paramètres. Consultez `!help fleet` pour plus d\'aide',
    'notEnoughCapacity' => 'L\'espace de stockage de votre flotte ne peut acceuillir autant de ressources. Espace manquant: :missingCapacity',
    'noShipSelected' => 'Aucun vaisseau sélectionné',
    'missingComTech' => 'Informatique & communication requis',
    'battleSummary' => "**__Rapport de bataille entre :playerSource et :playerDest__**\n\n".
                        "Origine: :colonySource [:coordinateSource]\n".
                        "Lieu de la bataille: :colonyDest [:coordinateDest]\n\n".
                        "**Forces de l'attaquant (:playerSource)**\n".
                        ":attackForces\n".
                        "**Forces du défenseur (:playerDest)**\n".
                        ":defenceForces\n",
    'passSummary' => "\n__Passe n°:phaseNbr__\n\n".
                    "L'attaquant fait un dégât total de :attackerDamageDone (dont :defenderAbsorbedDamage absorbé(s) par les boucliers).\n".
                    "Le défenseur fait un dégât total de :defenderDamageDone (dont :attackerAbsorbedDamage absorbé(s) par les boucliers).\n\n".
                    "__Pertes de l'attaquant__\n:lostAttackerUnits\n".
                    "__Pertes du défenseur__\n:lostDefenderUnits\n",
    'battleWin' => "\n__Résultat de la bataille__\n\n".
                    "L'attaquant a perdu :lostAttackUnit unité(s)\n".
                    "Le défenseur a perdu :lostDefenceUnit unité(s)\n\n".
                    "__Ressources pillées__\n".
                    ":stolenResources",
    'battleLost' => "\n__Résultat de la bataille__\n\n".
                    "L'attaquant a perdu :lostAttackUnit unité(s)\n".
                    "Le défenseur a perdu :lostDefenceUnit unité(s)\n\n".
                    "Aucune ressource n'a été pillée",
    'fleetHistory' => 'Historique des fleets',
    'historyHowTo' => 'Consultez le détail d\'une fleet avec `!fleet history [ID]`',
    'historyLine' => ':fleetId - :arrival - :destination - :mission - :status',
    'emptyHistory' => 'Aucun historique actuellement...',
    'ruinFieldGenerated' => 'Champ de ruine généré: :resources',
    'noScavengerSelected' => 'Aucun recycleur sélectionné',
    "scavengeConfirmation" => "__Voyage depuis :planetSource [:coordinateSource]__\n".
                            "Destination: :planetDest [:coordinateDestination]\n".
                            "Mission: **:mission**\n\n".
                            "**Recycleurs**\n".
                            ":fleet\n".
                            "Vitesse: :choosedSpeed (:maxSpeed%)\n".
                            "Carburant: :fuel\n".
                            "Durée de vol: :duration\n".
                            "Statut de l'envoi: **En attente**",
    'scavengeMission' => "Arrivée de vos recycleurs aux coordonnées suivantes **[:coordinateDestination]**\n"
                        ."Origine: **:planetSource [:coordinateSource]**\n\n"
                        ."__Ressources recyclées__\n"
                        .":resources\n",
    'emptyResources' => 'Vide.',
    'scavengerReturn' => "Retour de vos recycleur depuis **:planetDest [:coordinateDestination]**\n"
                    ."Origine: **:planetSource [:coordinateSource]**\n\n"
                    ."__Recycleurs__\n"
                    .":fleet\n"
                    ."__Transport__\n"
                    .":resources\n",
    "fleetDetailOwned" => "__Voyage depuis :source__\n".
                    "Destination: :destination\n".
                    "Mission: **:mission**\n".
                    "Statut: **:status**\n\n".
                    "**Flotte**\n".
                    ":fleet\n".
                    "**Transport (Capacité de soute: :freightCapacity)**\n".
                    ":resources\n".
                    "Durée avant destination: **:duration**\n",
    "fleetDetailArriving" => "__Arrivée d'une flotte depuis :source__\n".
                    "Destination: :destination\n".
                    "Mission: **:mission**\n\n".
                    "**Flotte**\n".
                    ":fleet\n".
                    "Temps avant arrivée: **:duration**\n",
];
