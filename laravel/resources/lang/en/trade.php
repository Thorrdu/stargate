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
    'howTo' => "Authorized limit in the weak -> strong case: 100% in favor of the strong player.\nGet trade detail with `!trade [ID]`",
    'warning' => '**_Warning, Unfair trade. Ajust this trade before closure or you\'ll suffer automatic sanction._**',
    'emptyList' => 'No active trade.',
    'alreadyExtended' => 'You already claimed a time extention',
    'extentionNotRequired' => 'This trade doesn\'t need time extention',
    'extentionGranted' => 'Delay granted. You now have 48h to ajust the trade.',
    'unknownTrade' => 'Unknown trade.',
    'warn' => '**_Warning, Unfair trade detected. You have +-24h hours left to ajust the trade before getting banned from the trade system._**'."\nMore info: `!trade :tradeID`",
    'ban' => 'Has you haven\'t regulated the unfair trade, you\'re now banned from the trade system...'."\nHowever, you can get an ultimate chance by claiming a time extention with `!trade :tradeID extend`",
    'noPactWithThisPlayer' => 'You do not posess a trade pact with this player...',
    'cantCancelWithUnfairFairTrade' => 'You can\'t cancel your pact with this player, A trade is still active.',
    'pactAlreadyExists' => 'You already have an active pact with this player.',
    'pactConfirm' => "<@:player_2_id>, You're invited to create a trade pact with <@:player_1_id>".
                    "\nThis pact will allow you to trade with him.".
                    "\nStatus: **Awaiting**",
    'pactCancelConfirm' => "Do you want to cancel your pact with :player_2_name ?".
                            "\nYou will not be able to trade with him anymore.".
                            "\nStatus: **Awaiting**",
    'emptyPacts' => 'No active pact',
    'pactList' => 'Active pacts list',
    'pactPlayers' => 'Players',
    'awaitBalancing' => 'Await balancing...',
    'youAlreadyHaveActiveTrade' => 'You already have an active trade with another player. See `!trade` to close it',
    'playerHasActiveTrade' => 'This player already have an active trade with another player.',
    'closed' => 'Trade closed.',
];
