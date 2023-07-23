<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class PasswordResetToken extends Migration
{
    public function up()
    {
        // Create table password_reset_tokens
        $this->forge->addField([
            'email' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'token' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'expired' => [
                'type' => 'VARCHAR',
                'constraint' => 155,
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        // add primary key
        $this->forge->addKey('email', true);
        // create table
        $this->forge->createTable('password_reset_tokens');
    }

    public function down()
    {
        //down
        $this->forge->dropTable('password_reset_tokens');
    }
}
