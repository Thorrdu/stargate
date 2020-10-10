<?php
//PROFILE FR
return [
    "notification" => [
        "missingParameter" => "Mauvais paramètres. Essayez `!profile notification on/off`",
        "disabled" => "Notifications **Activée**",
        "enabled" => "Notifications **Désactivée**",
    ],
    'nextVacation' => 'Vous êtes revenu de **mode vacance** trop récement. Vous pourrez le réactiver dans **:time**.',
    'vacationUntil' => 'Vous avez activé le **mode vacance** trop récement. Vous pourrez le désactiver dans **:time**.',
    'vacationConfirm' => 'Désirez-vous activer le **mode vacance** ?'.
                        "\nAucun joueur ne pourra vous attaquer durant cette période mais vos mines ne produiront plus.".
                        "\nAttention, vous ne pourrez le désactiver que 3 jours plus tard!",
    'vacationActivated' => 'Mode vacance **Actif**, bonne vacances!',
    'vacationOverConfirm' => 'Souhaitez-vous désactiver le **mode vacance** ?'.
                            "\nAttention, vous ne pourrez l'activer à nouveau que 3 jours plus tard!",
    'vacationOver' => 'Mode vacance **Inactif**, bon retour!',
    'vacationMode' => 'Mode vacance **Actif**. Pour le désactiver, utilisez `!profile vacation`.',
    'playerVacation' => 'Action impossible, joueur en **mode vacance**.',
    'youFightedRecently' => 'You fighted too recently. You\'ll be able to activate vacation mode in **:time**',
    'activeFleets' => 'You have flying ships. You\'ll be able to activate vacation mode when they\'ll be docked.',
    'busyPlayer' => 'Des constructions ou recherche sont en cours sur vos colonies. Vous ne pourez activer le mode vacance qu\'une fois vos colonies au repos.',
];
