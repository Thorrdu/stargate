<?php

return [

    'commands' => [
        'start' => [
            'name' => 'start',
            'description' => 'Démarrer l\'aventure',
            'usage' => '!start'
        ],
    ],

    'resources' => ['iron','gold','quartz','naqahdah'],          
    'base_prod' => ['iron' => 20, 'gold' => 10, 'quartz' => 5, 'naqahdah' => 2],
];