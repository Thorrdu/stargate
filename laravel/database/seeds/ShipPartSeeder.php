<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShipPartSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /**
         *
            Ressources
                Naquadah => Hydrogène

            Bâtiments Ressources
                Centrale au Naquadah => Réacteur Thermonucléaire
                Mine de Naquadah => Extracteur d'Hydrogène
                Centre d'extraction de Naquadah=> Extracteur d'Hydrogène Amélioré
                Entrepôt de Naquadah => Silo à Hydrogène
                Vaisseau Terraformeur Gad'Meer => Chantier de Terraformation

            Bâtiments Militaires
                Avant-poste Ancien => Avant-Poste de Défense

            Bâtiments Scientifiques
                Centre de formation => Usine Robotique
                Siège des anciens => Département de Contrôle Psychique

            Technologies Zone 51
                Technologie Maîtrise du Naqhadah => Technologie Maîtrise de l'Hydrogène
                Technologie Hyperspace => Technologie Antimatière
                Technologie Hypenavigation => Technologie Hyperespace
                Savoir des anciens => Savoir des Grands Sages

            Défenses
                Tourelle Jaffa => Tour de Combat
                Lanceur de Missiles MARK III => Lanceur de Missiles
                Mini Satellite à Ions => Satellite à Ions
                Canon au naquadah => Canon Electromagnétique
                Silos à Missiles MARK VIII => Silos à Missiles HEM
                Satellite Lantien => Complexe de Défense Orbital
                Drone de combat => Missile d'Interception Intelligent

            Appareils spé
                Bombe au naquadah => Bombe Electromagnétique
                MALP => I.P.E.R
                Sonde wraith => Sonde Spatiale
                X-303b => Vaisseau Ruines : RC-1
                X-303a => Vaisseau Ruines : RC-2

            Composants
                Infrastructure X-302=> Infrastructure de Chasseur
                Infrastructure cargo teltak => Infrastructure de Chasseur Lourd
                Infrastructure Navette bedrosienne => Infrastructure de Vaisseau Cargo
                Infrastructure X 303 => Infrastructure de Bombardier
                Infrastructure d'Alkesh => Infrastructure de Croiseur
                Infrastructure X304 => Infrastructure de Croiseur Lourd
                Infrastructure de Vaisseau Mère => Infrastructure de Destroyer
                Infrastructure Vaisseau Amiral => Infrastructure de Vaisseau Mère
                Infrastructure de Vaisseau Ruche => Infrastructure Vaisseau Amiral

                Missiles MARK III => Missiles
                Missiles MARK III Enrichie => Missiles Enrichis
                Canon à Ions Goa'ulds => Canon à Ions
                Batterie Electromagnétique => Canon Electromagnétique
                Lanceur de Plasma Lantien => Lanceur de Plasma Avancé
                Missiles MARK VIII => Missiles Nucléaire
                Canon au Naquadah => Rayon Electromagnétique
                Bombes au Naquadria => Bombes à Impulsion


                Réacteur SubLu. à Impulsion => Réacteur SubLuminique Ionique
                Réacteur SubLu. Asgard => Réacteur SubLuminique à Fusion
                Réacteur Hyperspatial Wraith => Réacteur à Antimatière
                Réacteur Hyperspatial Goa'ulds => Réacteur à Antigravité
                Réacteur d'Hypernavig. Asgard => Réacteur Hyperpropulseur
                Réacteur d'Hypernavig. Lantienne => Réacteur de type Stardrive

                Occulteur Goa'ulds => Occulteur des Croisés
                Occulteur Lantien => Occulteur des Grands Sages

                Anneaux de transport => Arche de Sauvetage

            UA
                Jaffa => Unité d'Elite
                Wraith => BioSoldat
                Espion Ashrak => Agent Secret
                Guerrier Skull => Soldat Droïde
                Prieur Ori => Androïde de Combat
                Assuran => NanoSoldat

            Autres
                Super porte => Arche Quantique
                Porte des étoiles => Portail spatial
                eppz => cellule énergétique
        */

        /**
         *
        Needle Threader
        Puddle Jumper
        Death Glider
        Spider ship
        Ori fighter
        F-302 fighter-interceptor
        Tel'tak
        X-301 Interceptor
        X-302 hyperspace fighter
        Replicator cruiser
        Al'kesh
        X-303 / BC-303
        BC-304
        Supergate 1
        Ha'tak
        Destiny
        Asgard science vessel
        Ori warship
        Bilskirnir-class ship
        O'Neill-class ship
        Supergate 2
        Anubis' mothership
        Wraith cruiser (Incorrect by visual comparison)
        Aurora-class battleship
        *
        */

        DB::table('ship_parts')->insert([
            'id' => 1,
            'name' => "Jumper",
            'type' => 'Blueprint', //['Blueprint', 'Armament', 'Shield', 'Hull', 'Reactor']
            'slug' => 'jumper',
            'description' => '',
            'iron' => 300,
            'gold' => 300,
            'quartz' => 20,
            'naqahdah' => 5,
            'capacity' => 1000,
            'crew' => 2,
            'base_time' => 30,
        ]);
        //Chantier Spatial 1
        DB::table('ship_part_buildings')->insert([
            'ship_part_id' => 1,
            'required_building_id' => 9,
            'level' => 1
        ]);
        //Blueprint 1
        DB::table('ship_part_technologies')->insert([
            'ship_part_id' => 1,
            'required_technology_id' => 6,
            'level' => 1
        ]);

        DB::table('ship_parts')->insert([
            'id' => 2,
            'name' => "Death glider",
            'type' => 'Blueprint', //['Blueprint', 'Armament', 'Shield', 'Hull', 'Reactor']
            'slug' => 'deathglider',
            'description' => '',
            'iron' => 600,
            'gold' => 600,
            'quartz' => 60,
            'naqahdah' => 10,
            'capacity' => 9000,
            'crew' => 25,
            'base_time' => 60,
        ]);
        //Chantier Spatial 2
        DB::table('ship_part_buildings')->insert([
            'ship_part_id' => 2,
            'required_building_id' => 9,
            'level' => 2
        ]);
        //Blueprint 3
        DB::table('ship_part_technologies')->insert([
            'ship_part_id' => 2,
            'required_technology_id' => 6,
            'level' => 3
        ]);

        DB::table('ship_parts')->insert([
            'id' => 3,
            'name' => "Tel'tak",
            'type' => 'Blueprint', //['Blueprint', 'Armament', 'Shield', 'Hull', 'Reactor']
            'slug' => 'teltak',
            'description' => '',
            'iron' => 1200,
            'gold' => 1200,
            'quartz' => 100,
            'naqahdah' => 20,
            'capacity' => 17500,
            'crew' => 100,
            'base_time' => 120,
        ]);
        //Chantier Spatial 4
        DB::table('ship_part_buildings')->insert([
            'ship_part_id' => 3,
            'required_building_id' => 9,
            'level' => 4
        ]);
        //Blueprint 5
        DB::table('ship_part_technologies')->insert([
            'ship_part_id' => 3,
            'required_technology_id' => 6,
            'level' => 5
        ]);

        DB::table('ship_parts')->insert([
            'id' => 4,
            'name' => "Al'kesh",
            'type' => 'Blueprint', //['Blueprint', 'Armament', 'Shield', 'Hull', 'Reactor']
            'slug' => 'alkesh',
            'description' => '',
            'iron' => 3000,
            'gold' => 3000,
            'quartz' => 400,
            'naqahdah' => 50,
            'capacity' => 35000,
            'crew' => 500,
            'base_time' => 240,
        ]);
        //Chantier Spatial 6
        DB::table('ship_part_buildings')->insert([
            'ship_part_id' => 4,
            'required_building_id' => 9,
            'level' => 6
        ]);
        //Blueprint 7
        DB::table('ship_part_technologies')->insert([
            'ship_part_id' => 4,
            'required_technology_id' => 6,
            'level' => 7
        ]);


        DB::table('ship_parts')->insert([
            'id' => 5,
            'name' => "Prometheus",
            'type' => 'Blueprint', //['Blueprint', 'Armament', 'Shield', 'Hull', 'Reactor']
            'slug' => 'prometheus',
            'description' => '',
            'iron' => 8000,
            'gold' => 8000,
            'quartz' => 1000,
            'naqahdah' => 100,
            'capacity' => 75000,
            'crew' => 1000,
            'base_time' => 480,
        ]);
        //Chantier Spatial 8
        DB::table('ship_part_buildings')->insert([
            'ship_part_id' => 5,
            'required_building_id' => 9,
            'level' => 8
        ]);
        //Blueprint 9
        DB::table('ship_part_technologies')->insert([
            'ship_part_id' => 5,
            'required_technology_id' => 6,
            'level' => 7
        ]);


        DB::table('ship_parts')->insert([
            'id' => 6,
            'name' => "Ha'tak",
            'type' => 'Blueprint', //['Blueprint', 'Armament', 'Shield', 'Hull', 'Reactor']
            'slug' => 'hatak',
            'description' => '',
            'iron' => 15000,
            'gold' => 15000,
            'quartz' => 2000,
            'naqahdah' => 200,
            'capacity' => 150000,
            'crew' => 2500,
            'base_time' => 960,
        ]);
        //Chantier Spatial 10
        DB::table('ship_part_buildings')->insert([
            'ship_part_id' => 6,
            'required_building_id' => 9,
            'level' => 10
        ]);
        //Blueprint 13
        DB::table('ship_part_technologies')->insert([
            'ship_part_id' => 6,
            'required_technology_id' => 6,
            'level' => 13
        ]);


        DB::table('ship_parts')->insert([
            'id' => 7,
            'name' => "Destiny",
            'type' => 'Blueprint', //['Blueprint', 'Armament', 'Shield', 'Hull', 'Reactor']
            'slug' => 'destiny',
            'description' => '',
            'iron' => 50000,
            'gold' => 50000,
            'quartz' => 5000,
            'naqahdah' => 2000,
            'capacity' => 300000,
            'crew' => 3000,
            'base_time' => 1920,
        ]);
        //Chantier Spatial 12
        DB::table('ship_part_buildings')->insert([
            'ship_part_id' => 7,
            'required_building_id' => 9,
            'level' => 12
        ]);
        //Blueprint 16
        DB::table('ship_part_technologies')->insert([
            'ship_part_id' => 7,
            'required_technology_id' => 6,
            'level' => 16
        ]);



        DB::table('ship_parts')->insert([
            'id' => 8,
            'name' => "Destiny",
            'type' => 'Blueprint', //['Blueprint', 'Armament', 'Shield', 'Hull', 'Reactor']
            'slug' => 'destiny',
            'description' => '',
            'iron' => 90000,
            'gold' => 90000,
            'quartz' => 10000,
            'naqahdah' => 4000,
            'capacity' => 500000,
            'crew' => 5000,
            'base_time' => 5000,
        ]);
        //Chantier Spatial 15
        DB::table('ship_part_buildings')->insert([
            'ship_part_id' => 8,
            'required_building_id' => 9,
            'level' => 15
        ]);
        //Blueprint 20
        DB::table('ship_part_technologies')->insert([
            'ship_part_id' => 8,
            'required_technology_id' => 6,
            'level' => 20
        ]);
        //IA 10
        DB::table('ship_part_technologies')->insert([
            'ship_part_id' => 8,
            'required_technology_id' => 5,
            'level' => 10
        ]);


        /*
        DB::table('ship_parts')->insert([
            'id' => 9,
            'name' => "Ori warship",
            'type' => 'Blueprint', //['Blueprint', 'Armament', 'Shield', 'Hull', 'Reactor']
            'slug' => 'oriwarship',
            'description' => '',
            'iron' => 10000000,
            'gold' => 10000000,
            'quartz' => 5000000,
            'naqahdah' => 1000000,
            'capacity' => 70000000,
            'crew' => 400000,
            'base_time' => 100000,
        ]);
        //Chantier Spatial 20
        DB::table('ship_part_buildings')->insert([
            'ship_part_id' => 9,
            'required_building_id' => 9,
            'level' => 15
        ]);
        //Blueprint 25
        DB::table('ship_part_technologies')->insert([
            'ship_part_id' => 9,
            'required_technology_id' => 6,
            'level' => 25
        ]);
        //IA 15
        DB::table('ship_part_technologies')->insert([
            'ship_part_id' => 9,
            'required_technology_id' => 5,
            'level' => 15
        ]);
            */

        /*
        DB::table('ship_parts')->insert([
            'id' => 9,
            'name' => "O'Neill",
            'type' => 'Blueprint', //['Blueprint', 'Armament', 'Shield', 'Hull', 'Reactor']
            'slug' => 'oneill',
            'description' => '',
            'iron' => 10000000,
            'gold' => 10000000,
            'quartz' => 5000000,
            'naqahdah' => 1000000,
            'capacity' => 70000000,
            'crew' => 400000,
            'base_time' => 100000,
        ]);
        //Chantier Spatial 20
        DB::table('ship_part_buildings')->insert([
            'ship_part_id' => 9,
            'required_building_id' => 9,
            'level' => 15
        ]);
        //Blueprint 25
        DB::table('ship_part_technologies')->insert([
            'ship_part_id' => 9,
            'required_technology_id' => 6,
            'level' => 25
        ]);
        //IA 15
        DB::table('ship_part_technologies')->insert([
            'ship_part_id' => 9,
            'required_technology_id' => 5,
            'level' => 15
        ]);*/




        /**
        Coque Tau'ri
        Coque Goa'ulds
        Coque Wraith
        Coque Asgard
        Coque Lantienne
        Coque Lantienne Renforcée // lantean
        */

        DB::table('ship_parts')->insert([
            'id' => 10,
            'name' => "Coque Tau'ri",
            'type' => 'Hull', //['Blueprint', 'Armament', 'Shield', 'Hull', 'Reactor']
            'slug' => 'taurihull',
            'description' => '',
            'iron' => 500,
            'gold' => 500,
            'quartz' => 0,
            'naqahdah' => 0,
            'hull' => 40,
            'used_capacity' => 6,
            'base_time' => 35,
        ]);
        //Hull 1
        DB::table('ship_part_technologies')->insert([
            'ship_part_id' => 10,
            'required_technology_id' => 8,
            'level' => 1
        ]);


        DB::table('ship_parts')->insert([
            'id' => 11,
            'name' => "Coque Goa'uld",
            'type' => 'Hull', //['Blueprint', 'Armament', 'Shield', 'Hull', 'Reactor']
            'slug' => 'goauldhull',
            'description' => '',
            'iron' => 1000,
            'gold' => 1000,
            'quartz' => 0,
            'naqahdah' => 0,
            'hull' => 220,
            'used_capacity' => 15,
            'base_time' => 70,
        ]);
        //Hull 6
        DB::table('ship_part_technologies')->insert([
            'ship_part_id' => 11,
            'required_technology_id' => 8,
            'level' => 6
        ]);


        DB::table('ship_parts')->insert([
            'id' => 12,
            'name' => "Coque Wraith",
            'type' => 'Hull', //['Blueprint', 'Armament', 'Shield', 'Hull', 'Reactor']
            'slug' => 'wraithhull',
            'description' => '',
            'iron' => 2200,
            'gold' => 2200,
            'quartz' => 300,
            'naqahdah' => 0,
            'hull' => 600,
            'used_capacity' => 20,
            'base_time' => 140,
        ]);
        //Hull 10
        DB::table('ship_part_technologies')->insert([
            'ship_part_id' => 12,
            'required_technology_id' => 8,
            'level' => 10
        ]);


        DB::table('ship_parts')->insert([
            'id' => 13,
            'name' => "Coque Asgard",
            'type' => 'Hull', //['Blueprint', 'Armament', 'Shield', 'Hull', 'Reactor']
            'slug' => 'asgardhull',
            'description' => '',
            'iron' => 2800,
            'gold' => 2800,
            'quartz' => 200,
            'naqahdah' => 500,
            'hull' => 1250,
            'used_capacity' => 350,
            'base_time' => 280,
        ]);
        //Hull 14
        DB::table('ship_part_technologies')->insert([
            'ship_part_id' => 13,
            'required_technology_id' => 8,
            'level' => 14
        ]);
        //IA 8
        DB::table('ship_part_technologies')->insert([
            'ship_part_id' => 13,
            'required_technology_id' => 5,
            'level' => 8
        ]);

        DB::table('ship_parts')->insert([
            'id' => 14,
            'name' => "Coque lantienne",
            'type' => 'Hull', //['Blueprint', 'Armament', 'Shield', 'Hull', 'Reactor']
            'slug' => 'lanteanhull',
            'description' => '',
            'iron' => 1250,
            'gold' => 1250,
            'quartz' => 400,
            'naqahdah' => 1500,
            'hull' => 2000,
            'used_capacity' => 50,
            'base_time' => 560,
        ]);
        //Hull 15
        DB::table('ship_part_technologies')->insert([
            'ship_part_id' => 14,
            'required_technology_id' => 8,
            'level' => 15
        ]);
        //IA 12
        DB::table('ship_part_technologies')->insert([
            'ship_part_id' => 14,
            'required_technology_id' => 5,
            'level' => 12
        ]);


        DB::table('ship_parts')->insert([
            'id' => 15,
            'name' => "Coque Lantienne renforcée",
            'type' => 'Hull', //['Blueprint', 'Armament', 'Shield', 'Hull', 'Reactor']
            'slug' => 'reinforcedlanteanhull',
            'description' => '',
            'iron' => 5000,
            'gold' => 5000,
            'quartz' => 500,
            'naqahdah' => 500,
            'hull' => 3400,
            'used_capacity' => 80,
            'base_time' => 1120,
        ]);
        //Hull 20
        DB::table('ship_part_technologies')->insert([
            'ship_part_id' => 15,
            'required_technology_id' => 8,
            'level' => 20
        ]);
        //IA 16
        DB::table('ship_part_technologies')->insert([
            'ship_part_id' => 15,
            'required_technology_id' => 5,
            'level' => 16
        ]);



        /**
            Bouclier Goa'ulds => Petit Bouclier
            Bouclier Asgard => Champ de Force
            Bouclier Lantien => Bouclier Déflecteur
            Super Bouclier d'Anubis => Bouclier des Croisés
            Bouclier d'Atlantis => Bouclier des Grands Sages
        */


        DB::table('ship_parts')->insert([
            'id' => 16,
            'name' => "Bouclier Goa'uld",
            'type' => 'Shield', //['Blueprint', 'Armament', 'Shield', 'Hull', 'Reactor']
            'slug' => 'goauldshield',
            'description' => '',
            'iron' => 750,
            'gold' => 750,
            'quartz' => 0,
            'naqahdah' => 0,
            'shield' => 60,
            'used_capacity' => 10,
            'base_time' => 60,
        ]);
        //Shield 2
        DB::table('ship_part_technologies')->insert([
            'ship_part_id' => 16,
            'required_technology_id' => 9,
            'level' => 2
        ]);

        DB::table('ship_parts')->insert([
            'id' => 17,
            'name' => "Bouclier Asgard",
            'type' => 'Shield', //['Blueprint', 'Armament', 'Shield', 'Hull', 'Reactor']
            'slug' => 'asgardshield',
            'description' => '',
            'iron' => 1500,
            'gold' => 1500,
            'quartz' => 0,
            'naqahdah' => 0,
            'shield' => 175,
            'used_capacity' => 25,
            'base_time' => 120,
        ]);
        //Shield 8
        DB::table('ship_part_technologies')->insert([
            'ship_part_id' => 17,
            'required_technology_id' => 9,
            'level' => 8
        ]);

        DB::table('ship_parts')->insert([
            'id' => 18,
            'name' => "Bouclier Lantien",
            'type' => 'Shield', //['Blueprint', 'Armament', 'Shield', 'Hull', 'Reactor']
            'slug' => 'lantianshield',
            'description' => '',
            'iron' => 2500,
            'gold' => 2500,
            'quartz' => 500,
            'naqahdah' => 0,
            'shield' => 450,
            'used_capacity' => 35,
            'base_time' => 240,
        ]);
        //Shield 12
        DB::table('ship_part_technologies')->insert([
            'ship_part_id' => 18,
            'required_technology_id' => 9,
            'level' => 12
        ]);

        DB::table('ship_parts')->insert([
            'id' => 19,
            'name' => "Super Bouclier d'Anubis",
            'type' => 'Shield', //['Blueprint', 'Armament', 'Shield', 'Hull', 'Reactor']
            'slug' => 'anubishield',
            'description' => '',
            'iron' => 7500,
            'gold' => 7500,
            'quartz' => 1000,
            'naqahdah' => 0,
            'shield' => 2500,
            'used_capacity' => 50,
            'base_time' => 480,
        ]);
        //Shield 16
        DB::table('ship_part_technologies')->insert([
            'ship_part_id' => 19,
            'required_technology_id' => 9,
            'level' => 16
        ]);
        //IA 10
        DB::table('ship_part_technologies')->insert([
            'ship_part_id' => 19,
            'required_technology_id' => 5,
            'level' => 10
        ]);


        DB::table('ship_parts')->insert([
            'id' => 20,
            'name' => "Bouclier d'Atlantis",
            'type' => 'Shield', //['Blueprint', 'Armament', 'Shield', 'Hull', 'Reactor']
            'slug' => 'atlantisshield',
            'description' => '',
            'iron' => 10000,
            'gold' => 10000,
            'quartz' => 1250,
            'naqahdah' => 0,
            'shield' => 5500,
            'used_capacity' => 110,
            'base_time' => 960,
        ]);
        //Shield 12
        DB::table('ship_part_technologies')->insert([
            'ship_part_id' => 20,
            'required_technology_id' => 9,
            'level' => 12
        ]);
        //IA 16
        DB::table('ship_part_technologies')->insert([
            'ship_part_id' => 20,
            'required_technology_id' => 5,
            'level' => 16
        ]);


        /**
        Reac à combustion
        Reac à fusion
        Réacteur SubLu. à Impulsion => Réacteur SubLuminique Ionique
        Réacteur SubLu. Asgard => Réacteur SubLuminique à Fusion
        Réacteur Hyperspatial Wraith => Réacteur à Antimatière
        Réacteur Hyperspatial Goa'ulds => Réacteur à Antigravité
        Réacteur d'Hypernavig. Asgard => Réacteur Hyperpropulseur
        Réacteur d'Hypernavig. Lantienne => Réacteur de type Stardrive
         *
         */

        DB::table('ship_parts')->insert([
            'id' => 21,
            'name' => "Réacteur à Combusion",
            'type' => 'Reactor', //['Blueprint', 'Armament', 'Shield', 'Hull', 'Reactor']
            'slug' => 'combusionreactor',
            'description' => '',
            'iron' => 250,
            'gold' => 250,
            'quartz' => 0,
            'naqahdah' => 0,
            'speed' => 0.01,
            'used_capacity' => 20,
            'base_time' => 30,
        ]);
        //Combusion 2
        DB::table('ship_part_technologies')->insert([
            'ship_part_id' => 21,
            'required_technology_id' => 10,
            'level' => 2
        ]);

        DB::table('ship_parts')->insert([
            'id' => 22,
            'name' => "Combustion améliorée",
            'type' => 'Reactor', //['Blueprint', 'Armament', 'Shield', 'Hull', 'Reactor']
            'slug' => 'advancedcombusion',
            'description' => '',
            'iron' => 350,
            'gold' => 250,
            'quartz' => 180,
            'naqahdah' => 0,
            'speed' => 0.2,
            'used_capacity' => 38,
            'base_time' => 45,
        ]);
        //Combusion 5
        DB::table('ship_part_technologies')->insert([
            'ship_part_id' => 22,
            'required_technology_id' => 10,
            'level' => 5
        ]);

        DB::table('ship_parts')->insert([
            'id' => 23,
            'name' => "Réacteur à Ion",
            'type' => 'Reactor', //['Blueprint', 'Armament', 'Shield', 'Hull', 'Reactor']
            'slug' => 'ionreactor',
            'description' => '',
            'iron' => 700,
            'gold' => 600,
            'quartz' => 200,
            'naqahdah' => 0,
            'speed' => 0.4,
            'used_capacity' => 75,
            'base_time' => 60,
        ]);
        //Combusion 6
        DB::table('ship_part_technologies')->insert([
            'ship_part_id' => 23,
            'required_technology_id' => 10,
            'level' => 6
        ]);
        //Ion 2
        DB::table('ship_part_technologies')->insert([
            'ship_part_id' => 23,
            'required_technology_id' => 12,
            'level' => 2
        ]);
        //Impulsion/Subluminique 15
        DB::table('ship_part_technologies')->insert([
            'ship_part_id' => 23,
            'required_technology_id' => 15,
            'level' => 2
        ]);

        DB::table('ship_parts')->insert([
            'id' => 24,
            'name' => "Réacteur à fusion",
            'type' => 'Reactor', //['Blueprint', 'Armament', 'Shield', 'Hull', 'Reactor']
            'slug' => 'fusionreactor',
            'description' => '',
            'iron' => 1000,
            'gold' => 800,
            'quartz' => 350,
            'naqahdah' => 0,
            'speed' => 0.6,
            'used_capacity' => 140,
            'base_time' => 90,
        ]);
        //Naqahdah 2
        DB::table('ship_part_technologies')->insert([
            'ship_part_id' => 24,
            'required_technology_id' => 14,
            'level' => 2
        ]);
        //Impulsion/Subluminique 15
        DB::table('ship_part_technologies')->insert([
            'ship_part_id' => 24,
            'required_technology_id' => 15,
            'level' => 8
        ]);

        DB::table('ship_parts')->insert([
            'id' => 25,
            'name' => "Réacteur Wraith",
            'type' => 'Reactor', //['Blueprint', 'Armament', 'Shield', 'Hull', 'Reactor']
            'slug' => 'wraithreactor',
            'description' => '',
            'iron' => 1200,
            'gold' => 1000,
            'quartz' => 400,
            'naqahdah' => 0,
            'speed' => 0.75,
            'used_capacity' => 260,
            'base_time' => 120,
        ]);
        //Antimatière 2
        DB::table('ship_part_technologies')->insert([
            'ship_part_id' => 25,
            'required_technology_id' => 16,
            'level' => 2
        ]);
        //Impulsion/Subluminique 15
        DB::table('ship_part_technologies')->insert([
            'ship_part_id' => 25,
            'required_technology_id' => 15,
            'level' => 10
        ]);

        DB::table('ship_parts')->insert([
            'id' => 26,
            'name' => "Réacteur Goa'uld",
            'type' => 'Reactor', //['Blueprint', 'Armament', 'Shield', 'Hull', 'Reactor']
            'slug' => 'goauldreactor',
            'description' => '',
            'iron' => 1400,
            'gold' => 1100,
            'quartz' => 500,
            'naqahdah' => 0,
            'speed' => 1.00,
            'used_capacity' => 320,
            'base_time' => 150,
        ]);
        //Antimatière 6
        DB::table('ship_part_technologies')->insert([
            'ship_part_id' => 26,
            'required_technology_id' => 16,
            'level' => 6
        ]);
        //Energie 14
        DB::table('ship_part_technologies')->insert([
            'ship_part_id' => 26,
            'required_technology_id' => 4,
            'level' => 14
        ]);

        DB::table('ship_parts')->insert([
            'id' => 27,
            'name' => "Reacteur Asgard",
            'type' => 'Reactor', //['Blueprint', 'Armament', 'Shield', 'Hull', 'Reactor']
            'slug' => 'asgardreactor',
            'description' => '',
            'iron' => 3000,
            'gold' => 2800,
            'quartz' => 1200,
            'naqahdah' => 0,
            'speed' => 2.5,
            'used_capacity' => 600,
            'base_time' => 180,
        ]);
        //Antimatière 8
        DB::table('ship_part_technologies')->insert([
            'ship_part_id' => 27,
            'required_technology_id' => 16,
            'level' => 8
        ]);

        DB::table('ship_parts')->insert([
            'id' => 28,
            'name' => "Reacteur Lantien",
            'type' => 'Reactor', //['Blueprint', 'Armament', 'Shield', 'Hull', 'Reactor']
            'slug' => 'lantianreactor',
            'description' => '',
            'iron' => 5000,
            'gold' => 5400,
            'quartz' => 2000,
            'naqahdah' => 0,
            'speed' => 5,
            'used_capacity' => 900,
            'base_time' => 250,
        ]);
        //Energie 15
        DB::table('ship_part_technologies')->insert([
            'ship_part_id' => 28,
            'required_technology_id' => 4,
            'level' => 15
        ]);
        //Impulsion/Subluminique 12
        DB::table('ship_part_technologies')->insert([
            'ship_part_id' => 27,
            'required_technology_id' => 15,
            'level' => 12
        ]);


        /**
        Missiles MARK III => Missiles
        Missiles MARK III Enrichie => Missiles Enrichis
        Canon à Ions Goa'ulds => Canon à Ions
        Batterie Electromagnétique => Canon Electromagnétique
        Lanceur de Plasma Lantien => Lanceur de Plasma Avancé
        Missiles MARK VIII => Missiles Nucléaire
        Canon au Naquadah => Rayon Electromagnétique
        Bombes au Naquadria => Bombes à Impulsion
        */

        DB::table('ship_parts')->insert([
            'id' => 29,
            'name' => "Tourelle à projectile",
            'type' => 'Reactor', //['Blueprint', 'Armament', 'Shield', 'Hull', 'Reactor']
            'slug' => 'projectileturret',
            'description' => '',
            'iron' => 100,
            'gold' => 100,
            'quartz' => 10,
            'naqahdah' => 0,
            'fire_power' => 1,
            'used_capacity' => 2,
            'base_time' => 10,
        ]);
        //Armement 1
        DB::table('ship_part_technologies')->insert([
            'ship_part_id' => 29,
            'required_technology_id' => 7,
            'level' => 1
        ]);

        DB::table('ship_parts')->insert([
            'id' => 30,
            'name' => "Missiles MARK III",
            'type' => 'Reactor', //['Blueprint', 'Armament', 'Shield', 'Hull', 'Reactor']
            'slug' => 'markiiimissile',
            'description' => '',
            'iron' => 1000,
            'gold' => 1000,
            'quartz' => 50,
            'naqahdah' => 10,
            'fire_power' => 10,
            'used_capacity' => 6,
            'base_time' => 100,
        ]);
        //Armement 5
        DB::table('ship_part_technologies')->insert([
            'ship_part_id' => 30,
            'required_technology_id' => 7,
            'level' => 5
        ]);
        //Laser 3
        DB::table('ship_part_technologies')->insert([
            'ship_part_id' => 30,
            'required_technology_id' => 11,
            'level' => 3
        ]);

        DB::table('ship_parts')->insert([
            'id' => 31,
            'name' => "Canon laser",
            'type' => 'Reactor', //['Blueprint', 'Armament', 'Shield', 'Hull', 'Reactor']
            'slug' => 'lasercannon',
            'description' => '',
            'iron' => 1500,
            'gold' => 1500,
            'quartz' => 60,
            'naqahdah' => 10,
            'fire_power' => 18,
            'used_capacity' => 8,
            'base_time' => 130,
        ]);
        //Armement 8
        DB::table('ship_part_technologies')->insert([
            'ship_part_id' => 31,
            'required_technology_id' => 7,
            'level' => 8
        ]);
        //Laser 8
        DB::table('ship_part_technologies')->insert([
            'ship_part_id' => 31,
            'required_technology_id' => 11,
            'level' => 8
        ]);


        DB::table('ship_parts')->insert([
            'id' => 32,
            'name' => "Canon à ion Goa'uld",
            'type' => 'Reactor', //['Blueprint', 'Armament', 'Shield', 'Hull', 'Reactor']
            'slug' => 'goauldioncannon',
            'description' => '',
            'iron' => 2000,
            'gold' => 2000,
            'quartz' => 100,
            'naqahdah' => 10,
            'fire_power' => 25,
            'used_capacity' => 10,
            'base_time' => 170,
        ]);
        //Armement 8
        DB::table('ship_part_technologies')->insert([
            'ship_part_id' => 32,
            'required_technology_id' => 7,
            'level' => 8
        ]);
        //Ion 5
        DB::table('ship_part_technologies')->insert([
            'ship_part_id' => 32,
            'required_technology_id' => 12,
            'level' => 5
        ]);

        DB::table('ship_parts')->insert([
            'id' => 33,
            'name' => "Canon à plasma",
            'type' => 'Reactor', //['Blueprint', 'Armament', 'Shield', 'Hull', 'Reactor']
            'slug' => 'plasmacannon',
            'description' => '',
            'iron' => 4000,
            'gold' => 4000,
            'quartz' => 200,
            'naqahdah' => 0,
            'fire_power' => 55,
            'used_capacity' => 22,
            'base_time' => 340,
        ]);
        //Plasma 4
        DB::table('ship_part_technologies')->insert([
            'ship_part_id' => 33,
            'required_technology_id' => 13,
            'level' => 4
        ]);

        DB::table('ship_parts')->insert([
            'id' => 34,
            'name' => "Bombe à plasma",
            'type' => 'Reactor', //['Blueprint', 'Armament', 'Shield', 'Hull', 'Reactor']
            'slug' => 'plasmabomb',
            'description' => '',
            'iron' => 5000,
            'gold' => 5000,
            'quartz' => 130,
            'naqahdah' => 0,
            'fire_power' => 80,
            'used_capacity' => 30,
            'base_time' => 425,
        ]);
        //Plasma 8
        DB::table('ship_part_technologies')->insert([
            'ship_part_id' => 34,
            'required_technology_id' => 13,
            'level' => 8
        ]);

        DB::table('ship_parts')->insert([
            'id' => 35,
            'name' => "Bombe au naqahdria",
            'type' => 'Reactor', //['Blueprint', 'Armament', 'Shield', 'Hull', 'Reactor']
            'slug' => 'naqhadriabomb',
            'description' => '',
            'iron' => 50000,
            'gold' => 50000,
            'quartz' => 5000,
            'naqahdah' => 1000,
            'fire_power' => 1200,
            'used_capacity' => 200,
            'base_time' => 1000,
        ]);
        //Naqahdah 8
        DB::table('ship_part_technologies')->insert([
            'ship_part_id' => 35,
            'required_technology_id' => 14,
            'level' => 8
        ]);
        //Plasma 12
        DB::table('ship_part_technologies')->insert([
            'ship_part_id' => 35,
            'required_technology_id' => 13,
            'level' => 12
        ]);
        //Energie 12
        DB::table('ship_part_technologies')->insert([
            'ship_part_id' => 35,
            'required_technology_id' => 4,
            'level' => 12
        ]);

        DB::table('ship_parts')->insert([
            'id' => 36,
            'name' => "Lanceur de drone",
            'type' => 'Reactor', //['Blueprint', 'Armament', 'Shield', 'Hull', 'Reactor']
            'slug' => 'droncelauncher',
            'description' => '',
            'iron' => 75000,
            'gold' => 75000,
            'quartz' => 25000,
            'naqahdah' => 10000,
            'fire_power' => 2800,
            'used_capacity' => 400,
            'base_time' => 2200,
        ]);
        //Armement 15
        DB::table('ship_part_technologies')->insert([
            'ship_part_id' => 36,
            'required_technology_id' => 7,
            'level' => 15
        ]);
        //Plasma 14
        DB::table('ship_part_technologies')->insert([
            'ship_part_id' => 36,
            'required_technology_id' => 13,
            'level' => 14
        ]);
        //IA 12
        DB::table('ship_part_technologies')->insert([
            'ship_part_id' => 36,
            'required_technology_id' => 5,
            'level' => 12
        ]);

        DB::table('ship_parts')->insert([
            'id' => 37,
            'name' => "Rayon à énergie Ori",
            'type' => 'Reactor', //['Blueprint', 'Armament', 'Shield', 'Hull', 'Reactor']
            'slug' => 'orienergybeam',
            'description' => '',
            'iron' => 200000,
            'gold' => 200000,
            'quartz' => 75000,
            'naqahdah' => 15000,
            'fire_power' => 12000,
            'used_capacity' => 1200,
            'base_time' => 10000,
        ]);
        //Armement 18
        DB::table('ship_part_technologies')->insert([
            'ship_part_id' => 37,
            'required_technology_id' => 7,
            'level' => 18
        ]);
        //IA 16
        DB::table('ship_part_technologies')->insert([
            'ship_part_id' => 37,
            'required_technology_id' => 5,
            'level' => 16
        ]);
        //Plasma 16
        DB::table('ship_part_technologies')->insert([
            'ship_part_id' => 37,
            'required_technology_id' => 13,
            'level' => 16
        ]);
        //Energie 16
        DB::table('ship_part_technologies')->insert([
            'ship_part_id' => 37,
            'required_technology_id' => 13,
            'level' => 16
        ]);

    }
}
