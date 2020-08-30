<?php
//ALLIANCE FR
return [
    'leader' => 'Leader',
    'alliance' => 'Alliance',
    'noAlliance' => "Vous n'êtes pas membre d'une alliance.",
    'generalDescription' => "__Nom__: :allianceName\n"
                            ."__Tag__: :allianceTag\n"
                            ."__Membres__: :memberCount\n"
                            ."__Leader__: :leader\n"
                            ."__Fondateur__: :founder\n"
                            ."__Ouvert au recrutement__: :recruitementStatus\n"
                            ."\n:top\n"
                            ."\n:internalDescription",
    'membersList' => "Membres",
    'alreadyMemberOfAnAlliance' => "Déjà membre d'une alliance.",
    'tagTooShort' => "Tag trop court.",
    'tagTooLong' => "Tag trop long.",
    'tagAlreadyTaken' => "Tag déjà pris.",
    'nameAlreadyTaken' => "Nom déjà pris.",
    'defaultRoles' => [
        'recruit' => "Recrue",
        'recruitOfficer' => "Recruteur",
        'officer' => "Officier",
        'council' => "Membre du conseil",
        'leader' => "Leader",
    ],
    'allianceCreated' => "L'alliance [**:tag**] **:allianceName** à été créée.",
    'allianceDisbanded' => "L'alliance **:allianceName** à été dissoute.",
    'leaderCannotLeave' => "Le leader ne peut pas quitter une alliance.\nVous devez soit dissoudre l'alliance, soit céder le commandement en premier lieu.",
    'allianceLeft' => "Vous avez quitté **:allianceName**.",
    'rolesList' => "Liste des rôles",
    'rolesHowTo' => "Vous pouvez à tout moment modifier les droits ou le nom d'un rôle comme suit:\n"
                    ."`!alliance role [roleName] set [Option] [Valeur/on/off]`\n"
                    ."`!alliance [option] [@mention]`\n"
                    ."Paramètres utilisables:\n"
                    ."name\nrecruit\nkick\npromote/demote\n",
    'roleLvl' => "Niveau de rôle",
    'recruitementRight' => "Droit de recruter",
    'kickRight' => "Droit de kick",
    'promoteRight' => "Droit de promouvoir",
    'unknownRole' => "Rôle inconnu.",
    'roleNameChanged' => "Le rôle **:oldRole** à été modifié en **:newRole**.",
    'recruitementEnabled' => "L'alliance est désormais ouverte au recrutement.",
    'recruitementDisabled' => "L'alliance est désormais fermée au recrutement.",
    'recruitementRightEnabled' => "Droit de recrutement activé.",
    'recruitementRightDisabled' => "Droite de recrutement désactivé.",
    'kickRightEnabled' => "Droit de kick activé.",
    'kickRightDisabled' => "Droit de kick désactivé.",
    'promoteRightEnabled' => "Droit de promotion activé.",
    'promoteRightDisabled' => "Droit de promotion désactivé.",
    'internalDescriptionChanged' => "Description interne modifiée.",
    'externalDescriptionChanged' => "Description publique modifiée.",
    'leaderChanged' => "Le leader de l'alliance **:allianceName** est désormais **:newLEader**.",
    'playerNotMemberOfThisAlliance' => "**:name** n'est pas un membre de **:allianceName**.",
    'inviteMessage' => ":invited, **:inviter** vous invite à rejoindre **:allianceName**.",
    'inviteAccepted' => "**:name** à rejoint **:allianceName**.",
    'inviteRefused' => "**:name** à décliné l'invitation.",
    'memberPromoted' => "**:name** promu à **:newRole**.",
    'memberDemoted' => "**:name** rétrogradé en **:newRole**.",
    'memberKicked' => "**:name** à été **kick** hors de **:allianceName**.",
    'allianceList' => "Liste des alliances",
    'membersCount' => "Membres",
    'externalDescription' => "Description publique",
    'recruitementStatus' => "Ouvert au recrutement",
    'membersLimitReached' => "Limite de membres atteinte.\nUtilisez `!alliance upgrade` pour acheter de nouveaux slots.",
    'upgradeSuccess' => "Félicitation, l'alliance peut désormais acceuillir **:newLimit** membres.",
    'upgradeMessage' => "Souhaitez vous augmenter la limite de membres pour un coût de:\n:cost",
];
