<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRenewalFieldsToOrders extends Migration
{
    public function up()
    {
        $this->forge->addColumn('orders', [
            'type' => [
                'type'       => 'ENUM',
                'constraint' => ['new', 'renewal'],
                'default'    => 'new',
                'after'      => 'order_number',
            ],
            'license_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'plan_id',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('orders', ['type', 'license_id']);
    }
}
