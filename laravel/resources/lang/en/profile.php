<?php
//PROFILE EN
return [
    "notification" => [
        "missingParameter" => "Missing or wrong parameter. try `!profile notification on/off`",
        "enabled" => "Building/Research notification **ON**",
        "disabled" => "Building/Research notification **OFF**",
    ],
    "hide" => [
        "missingParameter" => "Missing or wrong parameter. try `!profile hide on/off`",
        "enabled" => "Your coordinates are now **hidden**",
        "disabled" => "Your coordinates are now **displayed**",
    ],
    'nextVacation' => 'You came back from **vacation mode** too recently. You\'ll be able to activate it again in **:time**.',
    'vacationUntil' => 'You activated **vacation mode** too recently. You\'ll be able to desactivate it in **:time**.',
    'vacationConfirm' => 'Do you want to activate **vacation mode** ?'.
                        "\nNo player will be able to attack you during this period but your mines won't produce.".
                        "\nWarning, You\'ll only be able to deactivate it again in 3 days!",
    'vacationActivated' => 'Vacation mode **Active**, Happy hollidays!',
    'vacationOverConfirm' => 'Do you want to come back from **vacation mode** ?'.
                            "\nWarning, You'll only be able to reactivate it again in 3 days!",
    'vacationOver' => 'Vacation mode **Inactive**, welcome back!',
    'vacationMode' => 'Vacation mode **Active**. To deactivate it, use `!profile vacation`.',
    'playerVacation' => 'Action impossible, player in **vacation mode**.',
    'youFightedRecently' => 'Vous avez combattu trop récement. Vous pourrez activer le mode vacance dans **:time**',
    'activeFleets' => 'Vous avez des vaisseaux en vol. Vous pourrez activer le mode vacance une fois qu\'ils seront à quai.',
    'busyPlayer' => 'Des constructions ou recherche sont en cours sur vos colonies. Vous ne pouvez activer le mode vacance qu\'une fois vos colonies au repos.',
];
