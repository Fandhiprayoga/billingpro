<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddManagerFieldsToOrdersAndPayments extends Migration
{
    public function up()
    {
        // Add created_by_manager_id to orders
        $this->forge->addColumn('orders', [
            'created_by_manager_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'admin_notes',
                'comment'    => 'Manager yang membuatkan order ini',
            ],
        ]);

        // Add uploaded_by_manager_id to payment_confirmations
        $this->forge->addColumn('payment_confirmations', [
            'uploaded_by_manager_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'reviewed_at',
                'comment'    => 'Manager yang mengupload bukti bayar',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('orders', 'created_by_manager_id');
        $this->forge->dropColumn('payment_confirmations', 'uploaded_by_manager_id');
    }
}
