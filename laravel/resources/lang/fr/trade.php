<?php
//TRADE FR
return [
    'tradeDetail' => 'Trade ID :tradeID',
    'tradeInfos' => "Trade entre **:player1** et **:player2**\n".
                    "Clôture dans: **:time**\n".
                    "Statut: :status\n".
                    ':warning',
    'status' => [
        'balanced' => '**Equitable**',
        'unbalanced' => '**Injuste**'
    ],
    'tradeValue' => 'Valeur totale: :totalValue',
    'notYourTrade' => 'Ce trade ne vous concerne pas.',
    'tradeList' => 'Liste des trades en cours',
    'howTo' => "Limite autorisée en cas de trade faible -> fort : 25% en faveur du joueur fort.\nDétails d'un trade avec `!trade [ID]`",
    'warning' => '**_Attention, trade injuste. ajustez le trade avant la clôture automatique sous peine de sanction._**',
    'emptyList' => 'Aucun trade actif.',
    'alreadyExtended' => 'Ce trade à déjà profité d\'une extension',
    'extentionNotRequired' => 'Ce trade n\a pas besoin d\'une extension',
    'extentionGranted' => 'Extention accordée. Vous avez 48h pour ajuster le trade.',
    'unknownTrade' => 'Trade inconnu.',
    'warn' => '**_Attention, Un trade injuste à été détecté. Il vous reste +-24h pour ajuster le trade avant d\'être banni du système de trade._**'."\nPlus d'info: `!trade :tradeID`",
    'ban' => 'N\'ayant pas régulariser le trade endéant les temps. Vous êtes désormais banni du système de trade...'."\nUne ultime chance peut être obtenue en réclamant un délai avec `!trade :tradeID extend`",
];
