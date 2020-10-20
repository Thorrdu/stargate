<?php
//FleetCommand FR
return [
    "askBaseParameter" => "Actions possibles:\n".
                            "**order** (`!fleet order [id] return`)\n".
                            //"**explore** (`!fleet explore [coordonées]`)\n".
                            //"**colonize** (`!fleet colonize [coordonées]`)\n".
                            "**base** (`!fleet base [NuméroDeColonie] [Vaisseaux] [Nombre] [Ressource] [Qté]`)\n".
                            "**transport** (`!fleet transport [coordonées] [Vaisseaux] [Nombre] [Ressource] [Qté]`)\n".
                            "**spy** (`!fleet spy [coordonées]`)\n".
                            "**attack** (`!fleet attack [coordonées] [Vaisseau] [Qté]`)\n".
                            "**scavenge** (`!fleet scavenge [Recycleurs] [Qté]`)\n".
                            "**history** (`!fleet history`)\n".
                            "Paramètre optionel: speed [10-100]",
    "fleetMessage" => "__Voyage depuis :planetSource [:coordinateSource]__\n".
                        "Destination: :planetDest [:coordinateDestination]\n".
                        "Mission: **:mission**\n\n".
                        "**Flotte**\n".
                        ":fleet\n".
                        "**Transport (:freightCapacity)**\n".
                        ":resources\n".
                        "Equipage: :crew \n".
                        "Vitesse: :speed (:maxSpeed%)\n".
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
                        "Vitesse: :speed (:maxSpeed%)\n".
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
    'incomingFleets' => "__Flottes étrangères en approche__\n:fleets",
    'noActiveFleet' => 'Aucune flotte active',
    'noIncomingFleet' => 'Aucune flotte en approche',
    'returningStatus' => 'Retour',
    'ongoingStatus' => 'Allé',
    'activeFleet' => '[**:mission/:status**] [ID:**:id**] Votre flotte de **:shipCount vaisseaux** en provenance de **:colonySource [:coordinatesSource]** se dirige vers **:colonyDest [:coordinatesDest]**',
    'incomingFleet' => '[**:mission**] Une flotte étrangère de **:shipCount vaisseaux** en provenance de **:colonySource [:coordinatesSource]** se dirige vers **:colonyDest [:coordinatesDest]**',
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
    'battleWin' => "\n__Rapport de bataille__\n\n".
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
    'historyLine' => ':fleetId - :date - :mission - :destination',
    'emptyHistory' => 'Aucun historique actuellement...',
    'ruinFieldGenerated' => 'Champ de ruine généré: :resources',
    'noScavengerSelected' => 'Aucun recycleur sélectionné',
    "scavengeConfirmation" => "__Voyage depuis :planetSource [:coordinateSource]__\n".
                            "Destination: :planetDest [:coordinateDestination]\n".
                            "Mission: **:mission**\n\n".
                            "**Recycleurs**\n".
                            ":fleet\n".
                            "Vitesse: :speed (:maxSpeed%)\n".
                            "Carburant: :fuel\n".
                            "Durée de vol: :duration\n".
                            "Statut de l'envoi: **En attente**",
    'noResourceSeleted' => 'Aucune ressource sélectionnée',
    'scavengeMission' => "Arrivée de vos recycleurs aux coordonées suivantes **[:coordinateDestination]**\n"
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
];
