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
    'emotes' => [
        'iron' => '<:iron:737769180190998680>',
        'gold' => '<:gold:737769237011234917>',
        'quartz' => '<:quartz:737769265104551987>',
        'naqahdah' => '<:naqahdah:737769280573276160>'

    ]
];