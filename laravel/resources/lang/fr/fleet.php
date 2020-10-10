<?php
//FleetCommand FR
return [
    "askBaseParameter" => "Actions possibles:\n".
                            "**order** (`!fleet order [id] return`)\n".
                            //"**explore** (`!fleet explore [coordonées]`)\n".
                            //"**colonize** (`!fleet colonize [coordonées]`)\n".
                            "**base** (`!fleet base [NuméroDeColonie] [Ressource] [Qté]`)\n".
                            "**transport** (`!fleet transport [coordonées] [Ressource] [Qté]`)\n".
                            //"**spy** (`!fleet spy [coordonées]`)\n".
                            //"**attack** (`!fleet attack [coordonées] military [Qté] [Unit] [Qté]`)\n"
                            "Paramètre optionel: speed [10-100]",
    "fleetMessage" => "__Voyage depuis :planetSource [:coordinateSource]__\n".
                        "Destination: :planetDest [:coordinateDestination]\n".
                        "Mission: **:mission**\n".
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
                        "Mission: **:mission**\n".
                        "**Flotte**\n".
                        ":fleet\n".
                        "Capacité: :freightCapacity\n".
                        "Equipage: :crew \n".
                        "Vitesse: :speed (:maxSpeed%)\n".
                        "Carburant: :fuel\n".
                        "Durée de vol: :duration\n".
                        "Statut de l'envoi: **En attente**",
    'missionReturn' => "Arrivée d'une flotte sur **:planetDest [:coordinateDestination]**\n"
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
];
