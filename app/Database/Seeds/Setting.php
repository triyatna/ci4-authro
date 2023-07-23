<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class Setting extends Seeder
{
    public function run()
    {
        // insert data to settings table
        $data = [
            [
                'config' => 'title',
                'var' => 'CodeIgniter 4'
            ],
            [
                'config' => 'description',
                'var' => 'CodeIgniter 4 is a 1:1 rewrite of CodeIgniter 3'
            ],
            [
                'config' => 'keywords',
                'var' => 'CodeIgniter, PHP, Framework, MVC'
            ],
            [
                'config' => 'favicon',
                'var' => 'favicon.ico'
            ],
            [
                'config' => 'logo',
                'var' => 'logo.png',
            ],
            [
                'config' => 'gravatar',
                'var' => '0'
            ],
            [
                'config' => 'smtp',
                'var' => '{"host":"","security":"","port":"","user":"","pass":""}'
            ],
            [
                'config' => 'email',
                'var' => 'admin@admin.com'
            ],
            [
                'config' => 'timezone',
                'var' => 'Asia/Jakarta'
            ],
            [
                'config' => 'url',
                'var' => 'http://localhost:8080'
            ],
            [
                'config' => 'maintenance',
                'var' => '0'
            ],
            [
                'config' => 'user_activate',
                'var' => '1'
            ],
            [
                'config' => 'user',
                'var' => '1'
            ],
            [
                'config' => 'version',
                'var' => '1.1'
            ],

        ];
        $this->db->table('settings')->insertBatch($data);
    }
}
