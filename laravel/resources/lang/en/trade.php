<?php
//TRADE EN
return [
    'tradeDetail' => 'Trade ID :tradeID',
    'tradeInfos' => "Trade between **:player1** and **:player2**\n".
                    "End in: **:time**\n".
                    "Status: :status\n".
                    ':warning',
    'status' => [
        'balanced' => '**Fair**',
        'unbalanced' => '**Unfair**'
    ],
    'tradeValue' => 'Total value: :totalValue',
    'notYourTrade' => 'This trade doesn\'t concerns you.',
    'tradeList' => 'Active trades list',
    'howTo' => "Authorized limit in the weak -> strong case: 25% in favor of the strong player.\nGet trade detail with `!trade [ID]`",
    'warning' => '**_Warning, Unfair trade. Ajust this trade before closure or you\'ll suffer automatic sanction._**',
    'emptyList' => 'No active trade.',
    'alreadyExtended' => 'You already claimed a time extention',
    'extentionNotRequired' => 'This trade doesn\'t need time extention',
    'extentionGranted' => 'Delay granted. You now have 48h to ajust the trade.',
    'unknownTrade' => 'Unknown trade.',
    'warn' => '**_Warning, Unfair trade detected. You have +-24h hours left to ajust the trade before getting banned from the trade system._**'."\nMore info: `!trade :tradeID`",
    'ban' => 'Has you haven\'t regulated the unfair trade, you\'re now banned from the trade system...'."\nHowever, you can get an ultimate chance by claiming a time extention with `!trade :tradeID extend`",
];
