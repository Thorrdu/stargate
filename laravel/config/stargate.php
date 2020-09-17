<?php

return [
    'version' => '0.6.1 (Early Access)',
    'resources' => ['iron','gold','quartz','naqahdah'],          
    'base_prod' => ['iron' => 20, 'gold' => 10, 'quartz' => 5, 'naqahdah' => 2, 'e2pz' => 10],
    'emotes' => [
        'fr' => '🇫🇷',
        'en' => '🇬🇧',
        'confirm' => '✅',
        'cancel' => '❌',
        'iron' => '<:iron:737769180190998680>',
        'gold' => '<:gold:737769237011234917>',
        'quartz' => '<:quartz:737769265104551987>',
        'naqahdah' => '<:naqahdah:742894577710661663>',
        'military' => '<:clone:737772878723940455>',
        'e2pz' => '<:e2pz:742847654492373144>',
        'energy' => '<:energy:745058168169824437>',
        'production' => '<:production:745056821101985882>',
        'productionBuilding' => '<:production:745056821101985882>',
        'research' => '<:research:737769578045898775>',
        'researchBuilding' => '<:researchBuilding:743237547416879185>',
        'military' => '<:military:737769413629182094>',
        'defence' => '<:military:737769413629182094>',
        'storage' => '<:storage:745057028455661678>'
    ],
    'galaxy' => ['maxGalaxies' => 5, 'maxSystems' => 100, 'maxPlanets' => 10],
    'travelCost' => ['sameSystem' => 0.2, 'perSystem' => 0.5, 'perGalaxy' => 2],
    'maxProdTime' => 720, //12h
    'maxColonies' => 5,
    'maxHourly' => 24,
    'gateFight' => ['StrongWeak' => 0],
    'alliance' => ['maxRoles' => 5, 'baseMembers' => 5, 'baseUpgradePrice' => 10000],
    'guilds' => []
];