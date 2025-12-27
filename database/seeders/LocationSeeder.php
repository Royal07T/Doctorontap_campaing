<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\State;
use App\Models\Location;

class LocationSeeder extends Seeder
{
    /**
     * Nigerian states mapping (state_id => state_name)
     */
    private $states = [
        1 => 'Abia',
        2 => 'Adamawa',
        3 => 'Akwa Ibom',
        4 => 'Anambra',
        5 => 'Bauchi',
        6 => 'Benue',
        7 => 'Borno',
        8 => 'Bayelsa',
        9 => 'Cross River',
        10 => 'Delta',
        11 => 'Ebonyi',
        12 => 'Edo',
        13 => 'Ekiti',
        14 => 'Enugu',
        15 => 'FCT',
        16 => 'Gombe',
        17 => 'Imo',
        18 => 'Jigawa',
        19 => 'Kebbi',
        20 => 'Kaduna',
        21 => 'Kano',
        22 => 'Kogi',
        23 => 'Katsina',
        24 => 'Kwara',
        25 => 'Lagos',
        26 => 'Nasarawa',
        27 => 'Niger',
        28 => 'Ogun',
        29 => 'Ondo',
        30 => 'Osun',
        31 => 'Oyo',
        32 => 'Plateau',
        33 => 'Rivers',
        34 => 'Sokoto',
        35 => 'Taraba',
        36 => 'Yobe',
        37 => 'Zamfara',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Importing states and cities from SQL file...');
        
        // Step 1: Create states
        $this->command->info('Creating states...');
        foreach ($this->states as $id => $name) {
            State::updateOrCreate(
                ['id' => $id],
                ['name' => $name]
            );
        }
        $this->command->info('Created ' . count($this->states) . ' states.');
        
        // Step 2: Read and import cities
        $sqlFile = '/home/royal-t/Downloads/cities.sql';
        
        if (!file_exists($sqlFile)) {
            $this->command->error('SQL file not found at: ' . $sqlFile);
            return;
        }
        
        $sqlContent = file_get_contents($sqlFile);
        
        // Extract all city value tuples
        // Pattern: (id, 'name', state_id, 'created_at', 'updated_at')
        preg_match_all("/\((\d+),\s*'([^']*(?:''[^']*)*)',\s*(\d+),\s*'([^']+)',\s*'([^']+)'\)/", $sqlContent, $cityMatches, PREG_SET_ORDER);
        
        $cities = [];
        
        foreach ($cityMatches as $match) {
            $name = str_replace("''", "'", $match[2]); // Handle escaped quotes
            // Also handle &apos; HTML entities
            $name = str_replace('&apos;', "'", $name);
            $cities[] = [
                'id' => (int) $match[1],
                'name' => $name,
                'state_id' => (int) $match[3],
                'created_at' => $match[4],
                'updated_at' => $match[5],
            ];
        }
        
        if (empty($cities)) {
            $this->command->error('No cities found in SQL file.');
            return;
        }
        
        $this->command->info('Found ' . count($cities) . ' cities to import.');
        
        // Clear existing locations
        DB::table('locations')->truncate();
        
        // Insert cities into locations table in chunks
        $chunkSize = 100;
        $chunks = array_chunk($cities, $chunkSize);
        
        foreach ($chunks as $index => $chunk) {
            DB::table('locations')->insert($chunk);
            $this->command->info('Imported chunk ' . ($index + 1) . ' of ' . count($chunks));
        }
        
        $this->command->info('Successfully imported ' . count($cities) . ' cities.');
    }
}
