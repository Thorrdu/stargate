<?php
//BUILDING EN
return [
    'thermalreactor' => [
        'name' => 'Thermal reactor',
        'description' => 'Reactor to exploit the underground thermal energy of your colony'
    ],
    'ironmine' => [
        'name' => 'Iron mine',
        'description' => 'Basic mine to extract Iron ore'
    ],
    'goldmine' => [
        'name' => 'Gold mine',
        'description' => 'Basic mine to extract Gold ore'
    ],
    'quartzmine' => [
        'name' => 'Quartz mine',
        'description' => 'Basic mine to extract Quartz'
    ],
    'naqahdahextractor' => [
        'name' => 'Naqahdah extractor',
        'description' => 'Extractor alowing to recover naqahdah from lower layers of the planet'
    ],
    'robotfactory' => [
        'name' => 'Robot factory',
        'description' => 'Allow your settlers to be helped by robots when working. This will reduce the time of all your buildings.'
    ],
    'research' => [
        'name' => 'Research laboratory',
        'description' => "Give a location  to your settlers to make research, reduce the research time by 10% per level.\n".
                         "Moreover it allows to understand the exploitation of the Stargate"
    ],
    'military' => [
        'name' => 'Military barrack',
        'description' => 'You can recruit natives from your new planet and make them militaries able to help you in fights or exploring new worlds.'
    ],
    'shipyard' => [
        'name' => 'Shipyard',
        'description' => 'Allows to develop the building of probes and space ships'
    ],
    'naqahdahreactor' => [
        'name' => 'Naqahdah reactor',
        'description' => 'Reactor that allows to generate huge amount of energy but consume some Naqahdah'
    ],
    'ironstorage' => [
        'name' => 'Iron storage',
        'description' => 'Multiply your Iron storage capacity by 1.8/lvl'
    ],
    'goldstorage' => [
        'name' => 'Gold storage',
        'description' => 'Multiply your Gold storage capacity by 1.8/lvl'
    ],
    'quartzstorage' => [
        'name' => 'Quartz storage',
        'description' => 'Multiply your Quartz storage capacity by 1.8/lvl'
    ],
    'naqahdahstorage' => [
        'name' => 'naqahdah storage',
        'description' => 'Multiply your Naqahdah storage capacity by 1.8/lvl'
    ],
    'defence' => [
        'name' => 'Defence center',
        'description' => 'Allows to defend your settlement under attacks'
    ],
    'commandcenter' => [
        'name' => 'Command center',
        'description' => 'Command Center equipped with an out of common Artificial Intelligence. It will increase your life quality on this colony.'
    ],
    'ironadvancedmine' => [
        'name' => 'Advanced iron mine',
        'description' => 'Now used to mining iron on this colony, your settlers developed a much better way to extract it on this planet.'
    ],
    'goldadvancedmine' => [
        'name' => 'Advanced gold mine',
        'description' => 'Now used to mining gold on this colony, your settlers developed a much better way to extract it on this planet.'
    ],
    'asuranfactory' => [
        'name' => 'Asuran factory',
        'description' => "After long analysis, you scientists have succed to activate an old Asuran factory of ZPM. It opens you the way to intergalactis travels through the stargate"
    ],
    'terraformer' => [
        'name' => 'Terraformer',
        'description' => "By terraforming process, this factory modifies the appearance of your planet to enlarge the constructible space"
    ],
    'hiddenBuilding' => '-- Hidden building --',
    'unDiscovered' => 'Undiscovered',
    'noActiveBuilding' => 'No building under construction...',
    'buildingCanceled' => 'Building canceled, the majority of resources have been recovered. 25% of the invested resources have been lost.',
    'unknownBuilding' => 'Unknown building...',
    'asuranRestriction' => 'This building is only available on your home planet.',
    'howTo' => "Build with `!build :id confirm` or `!build :slug confirm`\n\n:description",
    'buildingList' => "Building's list",
    'genericHowTo' => "To display some building detail: `!build [ID/Slug]`\nTo start a building: `!build [ID/Slug] confirm`\n",
    'notYetDiscovered' => "Vous n'avez pas encore découvert ce bâtiment.",
    'notEnoughEnergy' => "You miss :missingEnergy energy to power this building.",
    'alreadyBuilding' => 'A building is already under construction. **Lvl :level :name** will be done in **:time**',
    'missingSpace' => 'Insufficient space to start this building.',
    'buildingStarted' => 'Building started, **Lvl :level :name**. It will be done in **:time**',
    'buildingRemovalStarted' => 'Destruction started, **:name **. It will be done in **:time**',
    'dmBuildIsOver' => 'A building has just been completed...',
    'buildingMaxed' => 'This Building is already maxed...',
    'buildingRemoved' => 'The removal of **:name** has been done on :colony',
    'cantCancelRemove' => 'You can\'t cancel a demolition once started...',
    'queueIsFull' => 'Building queue is full on this colony. Manage the queue with `!building queue`.',
    'buildingQueueAdded' => '**:buildingName** has been added to the queue.',
    'emptyQueue' => 'Building queue is empty.',
    'queueList' => 'Building queue',
    'clearedQueue' => 'The queue has been cleared.',
    'howToClearQueue' => "`!building queue clear` to clear the queue.",
    'queueCanceled' => ":colony - **:buildingName** could not be built. Reason: :reason.\nAs a result, the building queue has been cleared."
];
