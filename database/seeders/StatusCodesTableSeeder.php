<?php

namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Seeder;

class StatusCodesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            [
                'slug' => 'new',
                'name' => 'Новая',
                'description' => 'Новая задача',
            ],
            [
                'slug' => 'in_work',
                'name' => 'В работе',
                'description' => 'Задача взята в работу',
            ],
            [
                'slug' => 'closed',
                'name' => 'Закрыта',
                'description' => 'Задача завершена',
            ],
        ];

        foreach ($items as $item) {
            Status::updateOrCreate(
                ['slug' => $item['slug']],
                [
                    'name'        => $item['name'],
                    'description' => $item['description'],
                ]
            );
        }
    }
}
