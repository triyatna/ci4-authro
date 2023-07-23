<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Users extends Migration
{
    public function up()
    {
        //create table users
        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'constraint' => 25,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
            ],
            'username' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
            ],
            'email' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
            ],
            'email_verified_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'password' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
            ],
            'role' => [
                'type' => 'ENUM',
                'constraint' => ['admin', 'user'],
                'default' => 'user',
            ],
            'profile_photo_path' => [
                'type' => 'VARCHAR',
                'constraint' => '2048',
                'null' => true,
            ],
            'active' => [
                'type' => 'ENUM',
                'constraint' => ['true', 'false'],
                'default' => 'true',
            ],
            'remember_token' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null' => true,
            ],
            'api_token' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        // add primary key
        $this->forge->addKey('id', true);
        // create table
        $this->forge->createTable('users');
    }

    public function down()
    {
        //down
        $this->forge->dropTable('users');
    }
}
