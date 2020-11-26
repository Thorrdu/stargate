<?php
//BUILDING FR
return [
    'thermalreactor' => [
        'name' => 'Centrale Thermique',
        'description' => "Reacteur permettant d'exploiter l'énergie thermique souteraine présente sur votre colonie"
    ],
    'ironmine' => [
        'name' => 'Mine de fer',
        'description' => "Mine rudimentaire permettant d'extraire du minerais de fer"
    ],
    'goldmine' => [
        'name' => "Mine d'or",
        'description' => "Mine rudimentaire permettant d'extraire de l'or"
    ],
    'quartzmine' => [
        'name' => 'Mine de quartz',
        'description' => "Mine rudimentaire permettant d'extraire de quartz"
    ],
    'naqahdahextractor' => [
        'name' => 'Extracteur de naqahdah',
        'description' => 'Extracteur permettant de récupérer du Naqahdah dans les couches inférieures de la planète'
    ],
    'robotfactory' => [
        'name' => 'Usine robotisée',
        'description' => 'Permet à vos colons de travailler avec le support de robots. Augmentant ainsi grandement la vitesse de toutes vos constructions.'
    ],
    'research' => [
        'name' => 'Laboratoire de recherche',
        'description' => "Donne un lieu à vos colons pour effectuer des recherches.".
                         "\nPermet également de comprendre le fonctionnement de la porte des étoiles"
    ],
    'military' => [
        'name' => 'Caserne militaire',
        'description' => "Vous permet de recruter les autochtones de votre nouvelle planète et en faire des militaires capables de vous aider au combat ou en exploration."
    ],
    'shipyard' => [
        'name' => 'Chantier Spatial',
        'description' => 'Permet de déveloper la construction de sondes et vaisseaux spaciaux'
    ],
    'naqahdahreactor' => [
        'name' => 'Reacteur au Naqahdah',
        'description' => "Centrale permettant de générer d'énormes quantités d'énergie en consommant du Naqahdah."
    ],
    'ironstorage' => [
        'name' => 'Entrepôt de Fer',
        'description' => 'Permet de multiplier la capacité de stockage en Fer par 1.8 / LVL'
    ],
    'goldstorage' => [
        'name' => "Entrepôt d'Or",
        'description' => 'Permet de multiplier la capacité de stockage en Or par 1.8 / LVL'
    ],
    'quartzstorage' => [
        'name' => 'Entrepôt de Quartz',
        'description' => 'Permet de multiplier la capacité de stockage en Quartz par 1.8 / LVL'
    ],
    'naqahdahstorage' => [
        'name' => 'Entrepôt de naqahdah',
        'description' => 'Permet de multiplier la capacité de stockage en naqahdah par 1.8 / LVL'
    ],
    'defence' => [
        'name' => 'Centre de défense',
        'description' => "Permet à votre colonie de se défendre en cas d'attaques."
    ],
    'commandcenter' => [
        'name' => 'Centre de commandement',
        'description' => "Centre de commandement équipé d'une intelligence articifielle hors du commun. Votre vie sur cette colonie sera désormais bien plus aisée."
    ],
    'ironadvancedmine' => [
        'name' => 'Mine de fer avancée',
        'description' => "Désormais habitués à miner du fer sur cette colonie, vos colons ont dévellopés une manière bien plus éfficace d'extraire le fer de la planete"
    ],
    'goldadvancedmine' => [
        'name' => "Mine d'or avancée",
        'description' => "Désormais habitués à miner de l'or sur cette colonie, vos colons ont dévellopés une manière bien plus éfficace d'extraire l'or de la planete"
    ],
    'asuranfactory' => [
        'name' => 'Usine Asuran',
        'description' => "Après de longues analyses, vos chercheurs réussissent à réactiver cette vieille usine Asuran de production d'E2PZ. vous ouvrant la voie vers le voyage interplanétaire"
    ],
    'terraformer' => [
        'name' => 'Terraformeur',
        'description' => "Par procédé de terraformation, cette usine modifie l'aspect de votre planète pour agrandir l'espace constructible"
    ],

    'hiddenBuilding' => '-- Bâtiment caché --',
    'unDiscovered' => 'Non découvert',
    'unknownBuilding' => 'Bâtiment inconnu...',
    'asuranRestriction' => 'Ce bâtiment n\'est disponbile que sur votre planète mère.',
    'noActiveBuilding' => 'Aucun bâtiment en cours de construction...',
    'buildingCanceled' => 'Construction annulée, la majorité des ressources ont pu être récupérées. 25% des ressources ont été perdues.',
    'howTo' => "Construisez avec `!build :id confirm` ou `!build :slug confirm`\n\n:description",
    'buildingList' => 'Liste des bâtiments',
    'genericHowTo' => "Pour voir le détail d'un bâtiment: `!build [ID/Slug]`\nPour commencer la construction d\'un bâtiment utilisez `!build [ID/Slug] confirm`\n",
    'notYetDiscovered' => "Vous n'avez pas encore découvert ce bâtiment.",
    'notEnoughEnergy' => "Il vous manque :missingEnergy énergie pour allimenter ce bâtiment.",
    'alreadyBuilding' => 'Un bâtiment est déjà en construction. **Lvl :level :name** sera terminé dans **:time**',
    'missingSpace' => 'Espace insufisant pour construire un nouveau bâtiment.',
    'buildingStarted' => 'Construction commencée, **Lvl :level :name ** sera terminé dans **:time**',
    'buildingRemovalStarted' => 'Destruction commencée, **:name ** sera terminé dans **:time**',
    'dmBuildIsOver' => 'Un bâtiment vient de se terminer...',
    'buildingMaxed' => 'Ce bâtiment est déjà au niveau maximum...',
    'buildingRemoved' => 'La destruction de **:name** s\'est terminée sur :colony',
    'cantCancelRemove' => 'Vous ne pouvez annulez la destruction une fois commencée...',
];
