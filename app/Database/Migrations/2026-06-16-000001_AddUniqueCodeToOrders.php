<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUniqueCodeToOrders extends Migration
{
    public function up()
    {
        $this->forge->addColumn('orders', [
            'unique_code' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'default'    => 0,
                'after'      => 'amount',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('orders', 'unique_code');
    }
}
