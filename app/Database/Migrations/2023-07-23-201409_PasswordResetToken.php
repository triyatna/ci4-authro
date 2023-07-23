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
            'expires_at' => [
                'type' => 'VARCHAR',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('email', true);
        $this->forge->createTable('password_reset_tokens', true);
    }

    public function down()
    {
        //
    }
}
