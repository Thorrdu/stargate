<?php
//ALLIANCE EN
return [
    'leader' => 'Leader',
    'alliance' => 'Alliance',
    'noAlliance' => "You're not a member of an alliance.",
    'generalDescription' => "__Name__: :allianceName\n"
                            ."__Tag__: :allianceTag\n"
                            ."__Members__: :memberCount\n"
                            ."__Leader__: :leader\n"
                            ."__Founder__: :founder\n"
                            ."__Open for recruitement__: :recruitementStatus\n"
                            ."\n:top\n"
                            ."\n:internalDescription",
    'membersList' => "Members",
    'alreadyMemberOfAnAlliance' => "Already member of an alliance.",
    'tagTooShort' => "Tag too short.",
    'tagTooLong' => "Tag too long.",
    'tagAlreadyTaken' => "Tag already taken.",
    'nameAlreadyTaken' => "Name already taken.",
    'defaultRoles' => [
        'recruit' => "Recruit",
        'recruitOfficer' => "Recruiter",
        'officer' => "Officer",
        'council' => "Council member",
        'leader' => "Leader",
    ],
    'allianceCreated' => "The alliance [**:tag**] **:allianceName** has been created.",
    'allianceDisbanded' => "The alliance **:allianceName** has been disbanded.",
    'leaderCannotLeave' => "The alliance leader cannot leave.\nYou must disband the alliance or give the lead first.",
    'allianceLeft' => "You left **:allianceName**.",
    'rolesList' => "Roles list",
    'rolesHowTo' => "You can edit roles name or rights at any time with this command:\n"
                    ."`!alliance role [roleName] set [Option] [Value/on/off]`\n"
                    ."`!alliance [option] [@mention]`\n"
                    ."Available parameters:\n"
                    ."name\nrecruit\nkick\npromote/demote\n",
    'roleLvl' => "Role level",
    'recruitementRight' => "Recruitement right",
    'kickRight' => "Kick right",
    'promoteRight' => "Promote right",
    'unknownRole' => "Unknown role.",
    'roleNameChanged' => "The role **:oldRole** has been changed to **:newRole**.",
    'recruitementEnabled' => "The alliance is not opened to recruitement.",
    'recruitementDisabled' => "The alliance is now closed to recruitement.",
    'recruitementRightEnabled' => "Recruit right enabled.",
    'recruitementRightDisabled' => "Recruit right disabled.",
    'kickRightEnabled' => "Kick right ensabled.",
    'kickRightDisabled' => "Kick right disabled.",
    'promoteRightEnabled' => "Promote right enabled.",
    'promoteRightDisabled' => "Promote right disabled.",
    'internalDescriptionChanged' => "Internal description changed.",
    'externalDescriptionChanged' => "Public description changed.",
    'leaderChanged' => "The alliance leader of **:allianceName** is now **:newLEader**.",
    'playerNotMemberOfThisAlliance' => "**:name** is not a member of **:allianceName**.",
    'inviteMessage' => ":invited, **:inviter** invites you to join **:allianceName**.",
    'inviteAccepted' => "**:name** has joined **:allianceName**.",
    'inviteRefused' => "**:name** has declined the invitation.",
    'memberPromoted' => "**:name** promoted to **:newRole**.",
    'memberDemoted' => "**:name** demoted to **:newRole**.",
    'memberKicked' => "**:name** has been **kicked** out of **:allianceName**.",
    'allianceList' => "Alliances list",
    'membersCount' => "Members",
    'externalDescription' => "Public description",
    'recruitementStatus' => "Open for recruitement",
    'membersLimitReached' => "Members limit reached. use `!alliance upgrade` to buy more slots.",
    'upgradeSuccess' => "Congratz, the alliance can not welcome **:newLimit** members.",
    'upgradeMessage' => "Would you like to upgrade the member limit for a cost of:\n:cost",
];
