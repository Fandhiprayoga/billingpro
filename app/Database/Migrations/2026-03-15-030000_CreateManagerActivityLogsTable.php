<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateManagerActivityLogsTable extends Migration
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
            'manager_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'customer_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'action_type' => [
                'type'       => 'ENUM',
                'constraint' => ['create_order', 'upload_payment', 'manage_license', 'view_profile', 'assign_customer', 'unassign_customer'],
            ],
            'reference_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'reference_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'comment'    => 'order, license, payment_confirmation',
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'ip_address' => [
                'type'       => 'VARCHAR',
                'constraint' => 45,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('manager_id', false, false, 'idx_manager_activity_manager');
        $this->forge->addKey('customer_id', false, false, 'idx_manager_activity_customer');
        $this->forge->addForeignKey('manager_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('customer_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('manager_activity_logs');
    }

    public function down()
    {
        $this->forge->dropTable('manager_activity_logs');
    }
}
