<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLicensesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'order_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'plan_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'license_key' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'comment'    => 'String unik 20 karakter',
            ],
            'device_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'Device ID yang di-lock ke lisensi ini',
            ],
            'activated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'expires_at' => [
                'type' => 'DATETIME',
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['active', 'expired', 'revoked', 'suspended'],
                'default'    => 'active',
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
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('license_key');
        $this->forge->addKey('order_id');
        $this->forge->addKey('user_id');
        $this->forge->addKey('plan_id');
        $this->forge->addKey('device_id');
        $this->forge->addKey('status');
        $this->forge->createTable('licenses');
    }

    public function down()
    {
        $this->forge->dropTable('licenses');
    }
}
