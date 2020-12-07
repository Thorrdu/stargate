<?php
//RESEARCH EN
return [
    'communication' => [
        'name' => "IT and Communication",
        'description' => "Equipped with a powerful computer and communication system, your colonists are more efficient in their tasks.\nAlso provide another exploration slot every 4 lvl.",
        ],
    'spy' => [
        'name' => "Spying",
        'description' => "Determines your efficiency while spying on an enemy planet",
        ],
    'counterspy' => [
        'name' => "Counter-spy",
        'description' => "Determines your effectiveness against a foreign player's spy attempt",
        ],
    'energy' => [
        'name' => "Energy",
        'description' => "Enables more energy control, granting you a 5% bonus to energy building performance per level",
        ],
    'ia' => [
        'name' => "Artificial intelligence",
        'description' => "Develop an artificial intelligence capable of helping you on a daily basis in your colony".
                        "\nAllows you to alterate you permanent artifact every 4 levels. (`!colony reroll`)".
                        "\nThe more advanced the technology is, the better the artifact will be( 1% every 4 levels. )".
                        "\nBut remember, the new artifact has still 4% chance to be negative.",
        ],
    'blueprint' => [
        'name' => "Ship blueprint",
        'description' => "Allows the design of spaceships",
        ],
    'armament' => [
        'name' => "Armament",
        'description' => "Allows the design of weapons",
        ],
    'hull' => [
        'name' => "Hull",
        'description' => "Allows the design of ship hulls",
        ],
    'shield' => [
        'name' => "Shield",
        'description' => "Allows the design of shields",
        ],
    'afterburner' => [
        'name' => "Combustion",
        'description' => "Allows the design of conventional engines",
        ],
    'laser' => [
        'name' => "Laser",
        'description' => "High concentration laser beam when accumulating large amounts of energy",
        ],
    'ions' => [
        'name' => "Ions",
        'description' => "Enables the development of Ion particle-based weapon and reactor",
        ],
    'plasma' => [
        'name' => "Plasma",
        'description' => "Mastery of plasma technology, creating a laser heated to extreme temperatures enables the development of terribly lethal weapons",
        ],
    'naqahdah' => [
        'name' => "Naqahdah mastery",
        'description' => "Mastery of technology related to Naqahdah. Allows the manufacture of weapons related to this resource and reduce fuel consumption",
        ],
    'subluminal' => [
        'name' => "Subluminal  speed",
        'description' => "Ability to propel a ship at a higher speed than a standard combustion",
        ],
    'antimatter' => [
        'name' => "Antimatter",
        'description' => "Ability to master antimatter",
        ],
    'hyperspace' => [
        'name' => "Hyperspace",
        'description' => "Ability to travel at the speed of light and more",
        ],
    'noActiveTechnology' => 'No technology under research...',
    'technologyCanceled' => 'Research **canceled**, the majority of resources have been recovered. 25% of the invested resources have been lost.',
    'hiddenTechnology' => '-- Hidden technology --',
    'unDiscovered' => 'undiscovered',
    'unknownTechnology' => 'Unknown technology...',
    'howTo' => "Research with `!research :id confirm` or `!research :slug confirm`\n\n:description",
    'technologyList' => "Technologies's list",
    'genericHowTo' => "To display some technology detail: `!research [ID/Slug]`\nTo start a reasearch: `!research [ID/Slug] confirm`\n",
    'notYetDiscovered' => "You haven't discovered this technology yet.",
    'alreadyResearching' => 'A technology is already under research. **Lvl :level :name** will be done in **:time**',
    'researchStarted' => 'Research started, **Lvl :level :name **. It will be done in **:time**',
    'dmTechnologyIsOver' => 'A research has just been completed...',
];
