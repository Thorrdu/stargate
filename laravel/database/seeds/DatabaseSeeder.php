<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
/*
        Buildings
        */
        DB::table('buildings')->insert([
            'id' => 1,
            'name' => 'Centrale Thermique',
            'slug' => Str::slug('thermalreactor'),
            'description' => 'Reacteur permettant d\'exploiter l\'énergie thermique souteraine présente sur votre colonie',
            'type' => 'Energy',
            'iron' => 52,
            'gold' => 30,
            'quartz' => 0,
            'naqahdah' => 0,
            'production_type' => 'special',
            'production_base' => 200,
            'production_coefficient' => 1.2,
            'energy_base' => NULL,
            'energy_coefficient' => NULL,
            'display_order' => 0,
            'upgrade_coefficient' => 1.6,
            'level_max' => 20,
            'time_base' => 146
        ]);


        //https://forum.origins-return.fr/index.php?/topic/243312-les-batiments/

        DB::table('buildings')->insert([
            'id' => 2,
            'name' => 'Mine de fer',
            'slug' => Str::slug('ironmine'),
            'description' => 'Mine rudimentaire permettant d\'extraire du minerais de fer',
            'type' => 'Production',
            'iron' => 60,
            'gold' => 15,
            'quartz' => 0,
            'naqahdah' => 0,
            'production_type' => 'iron',
            'production_base' => 100,
            'production_coefficient' => 1.18,
            'energy_base' => 30,
            'energy_coefficient' => 1.45,
            'display_order' => 0,
            'upgrade_coefficient' => 1.45,
            'level_max' => 20,
            'time_base' => 162
        ]);

        DB::table('buildings')->insert([
            'id' => 3,
            'name' => 'Mine d\'or',
            'slug' => Str::slug('goldmine'),
            'description' => 'Mine rudimentaire permettant d\'extraire de l\'or',
            'type' => 'Production',
            'iron' => 50,
            'gold' => 25,
            'quartz' => 0,
            'naqahdah' => 0,
            'production_type' => 'gold',
            'production_base' => 90,
            'production_coefficient' => 1.15,
            'energy_base' => 26,
            'energy_coefficient' => 1.45,
            'display_order' => 1,
            'upgrade_coefficient' => 1.45,
            'level_max' => 20,
            'time_base' => 155
        ]);

        DB::table('buildings')->insert([
            'id' => 4,
            'name' => 'Mine de quartz',
            'slug' => Str::slug('quartzmine'),
            'description' => 'Mine rudimentaire permettant d\'extraire de quartz',
            'type' => 'Production',
            'iron' => 500,
            'gold' => 160,
            'quartz' => 0,
            'naqahdah' => 0,
            'production_type' => 'quartz',
            'production_base' => 149,
            'production_coefficient' => 1.22,
            'energy_base' => 40,
            'energy_coefficient' => 1.45,
            'display_order' => 2,
            'upgrade_coefficient' => 1.45,
            'time_base' => 770
        ]);

        DB::table('buildings')->insert([
            'id' => 5,
            'name' => 'Extracteur de naqahdah',
            'slug' => Str::slug('naqahdahmine'),
            'description' => 'Extracteur de naqahdah',
            'type' => 'Production',
            'iron' => 500,
            'gold' => 300,
            'quartz' => 100,
            'naqahdah' => 0,
            'production_type' => 'naqahdah',
            'production_base' => 122,
            'production_coefficient' => 1.22,
            'energy_base' => 68,
            'energy_coefficient' => 1.4,
            'display_order' => 3,
            'upgrade_coefficient' => 1.4,
            'time_base' => 840
        ]);

        DB::table('buildings')->insert([
            'id' => 6,
            'name' => 'Usine robotisée',
            'slug' => Str::slug('robotfactory'),
            'description' => "Permet à vos colons de travailler avec le support de robots.",
            'type' => 'Science',
            'iron' => 100,
            'gold' => 200,
            'quartz' => 100,
            'naqahdah' => 0,
            'production_type' => 'special',
            'production_base' => null,
            'production_coefficient' => null,
            'energy_base' => null,
            'energy_coefficient' => null,
            'display_order' => 1,
            'upgrade_coefficient' => 1.9,
            'time_base' => 840,
            'time_coefficient' => 1.7,
            'building_bonus' => 0.90
        ]);

        DB::table('buildings')->insert([
            'id' => 7,
            'name' => 'Centre de recherche',
            'slug' => Str::slug('research'),
            'description' => "Donne un lieu à vos colons pour effectuer des recherches\nPermet également de comprendre le fonctionnement de la porte des étoiles",
            'type' => 'Science',
            'iron' => 200,
            'gold' => 400,
            'quartz' => 200,
            'naqahdah' => 0,
            'production_type' => 'special',
            'production_base' => null,
            'production_coefficient' => null,
            'energy_base' => null,
            'energy_coefficient' => null,
            'display_order' => 2,
            'upgrade_coefficient' => 1.75,
            'time_base' => 648,
            'time_coefficient' => 1.7,
            'technology_bonus' => 0.90
        ]);

        DB::table('buildings')->insert([
            'id' => 8,
            'name' => 'Station de clônage',
            'slug' => Str::slug('clone'),
            'description' => "Obtenue de part l'alliance avec les Asgards, la station de clônage permet de recruter de nouvelles unités",
            'type' => 'Military',
            'iron' => 500,
            'gold' => 500,
            'quartz' => 0,
            'naqahdah' => 0,
            'production_type' => 'military',
            'production_base' => 2.5,
            'production_coefficient' => 2,
            'energy_base' => null,
            'energy_coefficient' => null,
            'display_order' => 1,
            'upgrade_coefficient' => 2,
            'time_base' => 1200,
            'time_coefficient' => 1.7
        ]);
        //Extracteur naqadah 5
        DB::table('building_buildings')->insert([
            'building_id' => 8,
            'required_building_id' => 6,
            'level' => 1
        ]);

        DB::table('buildings')->insert([
            'id' => 9,
            'name' => 'Chantier Spacial',
            'slug' => Str::slug('spacial'),
            'description' => "Permet de développer la construction de sondes et vaisseaux spaciaux",
            'type' => 'Military',
            'iron' => 100,
            'gold' => 200,
            'quartz' => 100,
            'naqahdah' => 0,
            'production_type' => 'special',
            'display_order' => 1,
            'upgrade_coefficient' => 1.9,
            'time_base' => 1140,
            'time_coefficient' => 1.7
        ]);
        //Usine robotisée 2
        DB::table('building_buildings')->insert([
            'building_id' => 9,
            'required_building_id' => 6,
            'level' => 2
        ]);


        DB::table('buildings')->insert([
            'id' => 10,
            'name' => 'Reacteur au Naqahdah',
            'slug' => Str::slug('naqadahreactor'),
            'description' => "Centrale permettant de générer d'énormes quantités d'énergie en consommant du Naqahdah.",
            'type' => 'Energy',
            'iron' => 5200,
            'gold' => 3000,
            'quartz' => 750,
            'naqahdah' => 0,
            'production_type' => 'special',
            'production_base' => 300,
            'production_coefficient' => 1.3,
            'energy_base' => NULL,
            'energy_coefficient' => NULL,
            'display_order' => 5,
            'upgrade_coefficient' => 1.6,
            'time_base' => 1800
        ]);

        DB::table('buildings')->insert([
            'id' => 11,
            'name' => 'Entrepôt de fer',
            'slug' => Str::slug('ironstorage'),
            'description' => "Permet de multiplier la capacité de stockage en Fer par 1.8 / LVL",
            'type' => 'Storage',
            'iron' => 2000,
            'gold' => 0,
            'quartz' => 0,
            'naqahdah' => 0,
            'production_type' => 'iron',
            'production_coefficient' => 1.8,
            'energy_base' => NULL,
            'energy_coefficient' => NULL,
            'display_order' => 1,
            'upgrade_coefficient' => 2,
            'time_base' => 1800,
            'time_coefficient' => 1.5
        ]);
        //Mine de fer
        DB::table('building_buildings')->insert([
            'building_id' => 11,
            'required_building_id' => 2,
            'level' => 5
        ]);

        DB::table('buildings')->insert([
            'id' => 12,
            'name' => 'Entrepôt d\'Or',
            'slug' => Str::slug('goldstorage'),
            'description' => "Permet de multiplier la capacité de stockage en Or par 1.8 / LVL",
            'type' => 'Storage',
            'iron' => 2000,
            'gold' => 1000,
            'quartz' => 0,
            'naqahdah' => 0,
            'production_type' => 'gold',
            'production_coefficient' => 1.8,
            'energy_base' => NULL,
            'energy_coefficient' => NULL,
            'display_order' => 2,
            'upgrade_coefficient' => 2,
            'time_base' => 1800,
            'time_coefficient' => 1.5
        ]);
        //Mine d'or
        DB::table('building_buildings')->insert([
            'building_id' => 12,
            'required_building_id' => 3,
            'level' => 5
        ]);

        DB::table('buildings')->insert([
            'id' => 13,
            'name' => 'Entrepôt de quartz',
            'slug' => Str::slug('quartzstorage'),
            'description' => "Permet de multiplier la capacité de stockage en Quartz par 1.8 / LVL",
            'type' => 'Storage',
            'iron' => 2000,
            'gold' => 2000,
            'quartz' => 0,
            'naqahdah' => 0,
            'production_type' => 'quartz',
            'production_coefficient' => 1.8,
            'energy_base' => NULL,
            'energy_coefficient' => NULL,
            'display_order' => 3,
            'upgrade_coefficient' => 2,
            'time_base' => 1800,
            'time_coefficient' => 1.5
        ]);
        //Mine de quartz
        DB::table('building_buildings')->insert([
            'building_id' => 13,
            'required_building_id' => 4,
            'level' => 5
        ]);

        DB::table('buildings')->insert([
            'id' => 14,
            'name' => 'Entrepôt de naqahdah',
            'slug' => Str::slug('naqahdahstorage'),
            'description' => "Permet de multiplier la capacité de stockage en Naqahdah par 1.8 / LVL",
            'type' => 'Storage',
            'iron' => 2000,
            'gold' => 1000,
            'quartz' => 1000,
            'naqahdah' => 0,
            'production_type' => 'naqahdah',
            'production_coefficient' => 1.8,
            'energy_base' => NULL,
            'energy_coefficient' => NULL,
            'display_order' => 4,
            'upgrade_coefficient' => 2,
            'time_base' => 1800,
            'time_coefficient' => 1.5
        ]);       
        //Mine de naqahdah
        DB::table('building_buildings')->insert([
            'building_id' => 14,
            'required_building_id' => 5,
            'level' => 5
        ]);

        DB::table('buildings')->insert([
            'id' => 15,
            'name' => 'Centre de défense',
            'slug' => Str::slug('defense'),
            'description' => "Permet à votre colonie de se défendre en cas d'attaques.",
            'type' => 'Military',
            'iron' => 75,
            'gold' => 150,
            'quartz' => 75,
            'naqahdah' => 0,
            'production_type' => 'special',
            'display_order' => 3,
            'upgrade_coefficient' => 1.9,
            'time_base' => 878,
            'time_coefficient' => 1.7
        ]);
        //Usine robotisée 3
        DB::table('building_buildings')->insert([
            'building_id' => 15,
            'required_building_id' => 6,
            'level' => 3
        ]);

        
        DB::table('technologies')->insert([
            'id' => 1,
            'name' => 'Informatique et Communication',
            'slug' => Str::slug('communication'),
            'description' => "Doté d'un système informatique et de communication performants, vos colons sont plus efficaces dans leurs tâches.",
            'type' => 'Labo',
            'iron' => 0,
            'gold' => 400,
            'quartz' => 600,
            'naqahdah' => 0,
            'display_order' => 1,
            'upgrade_coefficient' => 2,
            'time_base' => 1602,
            'time_coefficient' => 2,
            'building_bonus' => 0.95,
            'technology_bonus' => 0.95
        ]);
        //Centre de recherche 1
        DB::table('technology_buildings')->insert([
            'technology_id' => 1,
            'required_building_id' => 7,
            'level' => 1
        ]);

        DB::table('technologies')->insert([
            'id' => 2,
            'name' => 'Espionnage',
            'slug' => Str::slug('spy'),
            'description' => "Détermine votre efficacité lors de l'espionnage d'une planète ennemie",
            'type' => 'Labo',
            'iron' => 100,
            'gold' => 1000,
            'quartz' => 200,
            'naqahdah' => 0,
            'display_order' => 2,
            'upgrade_coefficient' => 2,
            'time_base' => 1800,
            'time_coefficient' => 2
        ]);
        //Centre de recherche 1
        DB::table('technology_buildings')->insert([
            'technology_id' => 2,
            'required_building_id' => 7,
            'level' => 3
        ]);

        DB::table('technologies')->insert([
            'id' => 3,
            'name' => 'Contre-Espionage',
            'slug' => Str::slug('counterspy'),
            'description' => "Détermine votre efficacité à contre la tentative d'espionage d'un joueur étranger",
            'type' => 'Labo',
            'iron' => 200,
            'gold' => 2000,
            'quartz' => 400,
            'naqahdah' => 0,
            'display_order' => 3,
            'upgrade_coefficient' => 2,
            'time_base' => 2200,
            'time_coefficient' => 2
        ]);
        //Centre de recherche 1
        DB::table('technology_buildings')->insert([
            'technology_id' => 3,
            'required_building_id' => 7,
            'level' => 4
        ]);
        //Espionnage
        DB::table('technology_technologies')->insert([
            'technology_id' => 3,
            'required_technology_id' => 2,
            'level' => 2
        ]);

        DB::table('technologies')->insert([
            'id' => 4,
            'name' => 'Energie',
            'slug' => Str::slug('energy'),
            'description' => "Permet de maîtriser d'avantage l'énergie, vous octroyant un bonus de 5% du rendement des bâtiments d'énergie par niveau",
            'type' => 'Labo',
            'iron' => 0,
            'gold' => 800,
            'quartz' => 400,
            'naqahdah' => 0,
            'display_order' => 4,
            'upgrade_coefficient' => 2,
            'time_base' => 990,
            'time_coefficient' => 2,
            'energy_bonus' => 1.05
        ]);
        //Chantier spacial 1
        DB::table('technology_buildings')->insert([
            'technology_id' => 4,
            'required_building_id' => 9,
            'level' => 1
        ]);
        //Centre de recherche 1
        DB::table('technology_buildings')->insert([
            'technology_id' => 4,
            'required_building_id' => 7,
            'level' => 1
        ]);

        DB::table('technologies')->insert([
            'id' => 5, // -2% building -2% techno
            'name' => 'Intelligence artificielle', //nanotechnologie
            'slug' => Str::slug('ia'),
            'description' => "Permet de développer une intelligence artificielle capable de vous aider au quotidien sur votre colonie",
            'type' => 'Labo',
            'iron' => 4000,
            'gold' => 0,
            'quartz' => 4000,
            'naqahdah' => 0,
            'display_order' => 5,
            'upgrade_coefficient' => 2,
            'time_base' => 1602,
            'time_coefficient' => 2,
            'building_bonus' => 0.98,
            'technology_bonus' => 0.98
        ]);
        //Centre de recherche 8
        DB::table('technology_buildings')->insert([
            'technology_id' => 5,
            'required_building_id' => 7,
            'level' => 8
        ]);
        //Salle contrôle / information et communication
        DB::table('technology_technologies')->insert([
            'technology_id' => 5,
            'required_technology_id' => 1,
            'level' => 5
        ]);


        DB::table('technologies')->insert([
            'id' => 6, 
            'name' => 'Plans de vaisseaux',
            'slug' => Str::slug('blueprint'),
            'description' => "Permet la conception de vaisseaux spaciaux.",
            'type' => 'Center',
            'iron' => 550,
            'gold' => 250,
            'quartz' => 100,
            'naqahdah' => 0,
            'display_order' => 5,
            'upgrade_coefficient' => 2,
            'time_base' => 1020,
            'time_coefficient' => 2
        ]);
        //- Centre de Recherche : Niveau 1
        DB::table('technology_buildings')->insert([
            'technology_id' => 6,
            'required_building_id' => 7,
            'level' => 1
        ]);

        DB::table('technologies')->insert([
            'id' => 7, 
            'name' => 'Armement',
            'slug' => Str::slug('Armament'),
            'description' => "Permet la conception d'armes.",
            'type' => 'Center',
            'iron' => 400,
            'gold' => 200,
            'quartz' => 0,
            'naqahdah' => 0,
            'display_order' => 5,
            'upgrade_coefficient' => 2,
            'time_base' => 450,
            'time_coefficient' => 2
        ]);
        //- Centre de Recherche : Niveau 4
        DB::table('technology_buildings')->insert([
            'technology_id' => 7,
            'required_building_id' => 7,
            'level' => 4
        ]);

        DB::table('technologies')->insert([
            'id' => 8, 
            'name' => 'Coque',
            'slug' => Str::slug('hull'),
            'description' => "Permet la conception de coques de vaisseaux.",
            'type' => 'Center',
            'iron' => 800,
            'gold' => 0,
            'quartz' => 0,
            'naqahdah' => 0,
            'display_order' => 5,
            'upgrade_coefficient' => 2,
            'time_base' => 1500,
            'time_coefficient' => 2
        ]);
        //- Centre de Recherche : Niveau 1
        DB::table('technology_buildings')->insert([
            'technology_id' => 8,
            'required_building_id' => 7,
            'level' => 1
        ]);
        //- Chantier Spatial : Niveau 1
        DB::table('technology_buildings')->insert([
            'technology_id' => 8,
            'required_building_id' => 9,
            'level' => 1
        ]);



        DB::table('technologies')->insert([
            'id' => 9, 
            'name' => 'Bouclier',
            'slug' => Str::slug('shield'),
            'description' => "Permet la conception de boucliers.",
            'type' => 'Center',
            'iron' => 400,
            'gold' => 300,
            'quartz' => 70,
            'naqahdah' => 0,
            'display_order' => 5,
            'upgrade_coefficient' => 2,
            'time_base' => 450,
            'time_coefficient' => 2
        ]);
        //- Centre de Recherche : Niveau 5
        DB::table('technology_buildings')->insert([
            'technology_id' => 9,
            'required_building_id' => 7,
            'level' => 5
        ]);
        //- Energie : Niveau 5
        DB::table('technology_technologies')->insert([
            'technology_id' => 6,
            'required_technology_id' => 4,
            'level' => 5
        ]);

        DB::table('technologies')->insert([
            'id' => 10, 
            'name' => 'Combustion',
            'slug' => Str::slug('afterburner'),
            'description' => "Permet la conception de moteurs conventionels.",
            'type' => 'Center',
            'iron' => 2400,
            'gold' => 0,
            'quartz' => 3600,
            'naqahdah' => 0,
            'display_order' => 5,
            'upgrade_coefficient' => 2,
            'time_base' => 450,
            'time_coefficient' => 2
        ]);
        //- Centre de Recherche : Niveau 1
        DB::table('technology_buildings')->insert([
            'technology_id' => 10,
            'required_building_id' => 7,
            'level' => 1
        ]);
        //- Chantier Spatial : Niveau 1
        DB::table('technology_buildings')->insert([
            'technology_id' => 10,
            'required_building_id' => 9,
            'level' => 1
        ]);
        //- Energie : Niveau 1 
        DB::table('technology_technologies')->insert([
            'technology_id' => 10,
            'required_technology_id' => 4,
            'level' => 1
        ]);

        DB::table('buildings')->insert([
            'id' => 16,
            'name' => 'Centre de commandement', //département de nanorobotique
            'slug' => Str::slug('commandcenter'),
            'description' => "Centre de commandement équipé d'une intelligence articifielle hors du commun. Votre vie sur cette colonie sera désormais bien plus aisée.", 
            //et moins 30% les vaisseaux
            'type' => 'Military',
            'iron' => 900000,
            'gold' => 900000,
            'quartz' => 500000,
            'naqahdah' => 10000,
            'production_type' => 'special',
            'display_order' => 4,
            'upgrade_coefficient' => 1.9,
            'time_base' => 86400,
            'time_coefficient' => 1.7,
            'building_bonus' => 0.5,
            'technology_bonus' => 0.5
        ]);
        //Centre de recherche 8
        DB::table('building_buildings')->insert([
            'building_id' => 16,
            'required_building_id' => 7,
            'level' => 8
        ]);
        //Salle contrôle / information et communication
        DB::table('building_technologies')->insert([
            'building_id' => 16,
            'required_technology_id' => 1,
            'level' => 10
        ]);
        //Nanotechnologie / Intelligence Articifielle
        DB::table('building_technologies')->insert([
            'building_id' => 16,
            'required_technology_id' => 5,
            'level' => 5
        ]);

        DB::table('buildings')->insert([
            'id' => 17,
            'name' => 'Mine de fer avancée',
            'slug' => Str::slug('ironadvancedmine'),
            'description' => "Désormais habitués à miner du fer sur cette colonie, vos volons ont dévellopés une manière bien plus éfficace d'extraire le fer de la planete",
            'type' => 'Production',
            'iron' => 30000,
            'gold' => 20000,
            'quartz' => 1000,
            'naqahdah' => 0,
            'production_type' => 'iron',
            'production_base' => 800,
            'production_coefficient' => 1.25,
            'display_order' => 6,
            'upgrade_coefficient' => 1.2,
            'time_base' => 17000,
            'time_coefficient' => 1.7
        ]);
        //Centre de recherche 1
        DB::table('building_buildings')->insert([
            'building_id' => 17,
            'required_building_id' => 2,
            'level' => 20
        ]);

        DB::table('buildings')->insert([
            'id' => 18,
            'name' => 'Mine d\'or avancée',
            'slug' => Str::slug('goldadvancedmine'),
            'description' => "Désormais habitués à miner de l\'or sur cette colonie, vos volons ont dévellopés une manière bien plus éfficace d'extraire l\'or de la planete",
            'type' => 'Production',
            'iron' => 32000,
            'gold' => 20000,
            'quartz' => 1500,
            'naqahdah' => 0,
            'production_type' => 'gold',
            'production_base' => 600,
            'production_coefficient' => 1.25,
            'display_order' => 7,
            'upgrade_coefficient' => 1.3,
            'time_base' => 20000,
            'time_coefficient' => 1.7
        ]);
        //Centre de recherche 1
        DB::table('building_buildings')->insert([
            'building_id' => 18,
            'required_building_id' => 3,
            'level' => 20
        ]);

        /*
        Reacteur au naqadah
        */
        //Extracteur naqadah 5
        DB::table('building_buildings')->insert([
            'building_id' => 10,
            'required_building_id' => 5,
            'level' => 5
        ]);
        //Energie 4
        DB::table('building_technologies')->insert([
            'building_id' => 10,
            'required_technology_id' => 4,
            'level' => 4
        ]);

        DB::table('technologies')->insert([
            'id' => 11, 
            'name' => 'Laser',
            'slug' => Str::slug('laser'),
            'description' => "Rayon laser à concentration élevée lors de l'accumulation de grandes quantités d'énergie.",
            'type' => 'Labo',
            'iron' => 400,
            'gold' => 100,
            'quartz' => 0,
            'naqahdah' => 0,
            'display_order' => 5,
            'upgrade_coefficient' => 2,
            'time_base' => 600,
            'time_coefficient' => 2
        ]);

        DB::table('technologies')->insert([
            'id' => 11, 
            'name' => 'Vitesse subluminique', // Impulsion
            'slug' => Str::slug('subluminal'),
            'description' => "Capacité à propulser un vaisseau à une vitesse suppérieure à une combusion standard.",
            'type' => 'Center',
            'iron' => 1000,
            'gold' => 2000,
            'quartz' => 300,
            'naqahdah' => 0,
            'display_order' => 5,
            'upgrade_coefficient' => 2,
            'time_base' => 3600,
            'time_coefficient' => 2
        ]);



        DB::table('units')->insert([
            'id' => 1,
            'type' => 'Military',
            'name' => "Soldat",
            'slug' => 'soldier',
            'description' => "Staff essentiel pour la survie de votre colonie.\nVous permet de vous défendre face aux attaques ennemie ainsi que de raid d'autres colonies",
            'health' => 100,
            'armor' => 100,
            'shield' => 0,
            'capacity' => 0,
            'utility_power' => 100,
            'convertible' => true,
            'buyable' => false,
            'iron' => 20,
            'gold' => 5,
            'quartz' => 0,
            'naqahdah' => 0
        ]);
        DB::table('units')->insert([
            'id' => 2,
            'type' => 'Settler',
            'name' => "Colon",
            'slug' => 'settler',
            'description' => "Les colons sont formés pour entreprendre des missions de colonisation.",
            'health' => 100,
            'armor' => 0,
            'shield' => 0,
            'capacity' => 10,
            'utility_power' => 0,
            'convertible' => true,
            'buyable' => false,
            'iron' => 5,
            'gold' => 15,
            'quartz' => 5,
            'naqahdah' => 0
        ]);
        DB::table('units')->insert([
            'id' => 3,
            'type' => 'Spy',
            'name' => "Espion",
            'slug' => 'spy',
            'description' => "Peut être envoyé sur une colonie adverse afin d'y mener divers missions d'espionnage.",
            'health' => 100,
            'armor' => 0,
            'shield' => 0,
            'capacity' => 0,
            'utility_power' => 100,
            'convertible' => true,
            'buyable' => false,
            'iron' => 100,
            'gold' => 250,
            'quartz' => 0,
            'naqahdah' => 150
        ]);
        DB::table('units')->insert([
            'id' => 4,
            'type' => 'Probe',
            'name' => "Sonde M.A.L.P.",
            'slug' => 'malp',
            'description' => "Sonde terrestre vous permetant d'explorer une planète et relever des informations sur une colonie.",
            'health' => 100,
            'armor' => 0,
            'shield' => 0,
            'capacity' => 0,
            'utility_power' => 0,
            'convertible' => false,
            'buyable' => true,
            'iron' => 500,
            'gold' => 300,
            'quartz' => 1200,
            'naqahdah' => 150,
            'base_time' => 240
        ]);
        DB::table('units')->insert([
            'id' => 5,
            'type' => 'Probe',
            'name' => "Sonde U.A.V.",
            'slug' => 'uav',
            'description' => "Sonde Volante vous permetant d'explorer une planète et relever des informations sur une colonie.",
            'health' => 100,
            'armor' => 0,
            'shield' => 0,
            'capacity' => 10,
            'utility_power' => 0,
            'convertible' => false,
            'buyable' => true,
            'iron' => 1000,
            'gold' => 600,
            'quartz' => 2400,
            'naqahdah' => 480,
            'base_time' => 450
        ]);
        DB::table('units')->insert([
            'id' => 6,
            'type' => 'Transport',
            'name' => "Petit transporteur",
            'slug' => 'transport',
            'description' => "Transport mobile vous permettant d'acheminer des ressources entre 2 colonies.",
            'health' => 2000,
            'armor' => 1500,
            'shield' => 1000,
            'capacity' => 2500,
            'utility_power' => 0,
            'convertible' => false,
            'buyable' => true,
            'iron' => 400,
            'gold' => 150,
            'quartz' => 50,
            'naqahdah' => 200,
            'base_time' => 600
        ]);
        DB::table('units')->insert([
            'id' => 7,
            'type' => 'Transport',
            'name' => "Grand transporteur",
            'slug' => 'transport2',
            'description' => "Transport mobile vous permettant d'acheminer des ressources entre 2 colonies.",
            'health' => 100,
            'armor' => 0,
            'shield' => 0,
            'capacity' => 10000,
            'utility_power' => 0,
            'convertible' => false,
            'buyable' => true,
            'iron' => 1500,
            'gold' => 600,
            'quartz' => 250,
            'naqahdah' => 300
        ]);












        DB::table('players')->insert([
            'id' => 1,
            'user_id' => 125641223544373248,
            'user_name' => "Thorrdu"
        ]);

        DB::table('colonies')->insert([
            'id' => 1,
            'player_id' => 1,
            'colony_type' => 1,
            'space_used' => 22,
            'space_max' => 180,
            'name' => 'P'.rand(1, 9).Str::random(1).'-'.rand(1, 9).rand(1, 9).rand(1, 9),
            'last_claim' => '2020-07-14 00:00:00'
        ]);

        DB::table('building_colony')->insert([
            'colony_id' => 1,
            'building_id' => 1,
            'level' => 1
        ]);

        DB::table('building_colony')->insert([
            'colony_id' => 1,
            'building_id' => 2,
            'level' => 1
        ]);

        DB::table('building_colony')->insert([
            'colony_id' => 1,
            'building_id' => 6,
            'level' => 10
        ]);

        DB::table('building_colony')->insert([
            'colony_id' => 1,
            'building_id' => 7,
            'level' => 10
        ]);
    }
}
