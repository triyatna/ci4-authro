<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Setting extends Migration
{
    public function up()
    {
        // create table settings (config key varchar(255), config longtext);
        $this->forge->addField([
            'config' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'var' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],
        ]);
        // add primary key
        $this->forge->addKey('config', true);
        // create table
        $this->forge->createTable('settings');
    }

    public function down()
    {
        //down
        $this->forge->dropTable('settings');
    }
}
