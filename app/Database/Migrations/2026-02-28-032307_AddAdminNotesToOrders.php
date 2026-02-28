<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAdminNotesToOrders extends Migration
{
    public function up()
    {
        // Tambah kolom admin_notes untuk menyimpan catatan admin (approve/reject reason)
        $this->forge->addColumn('orders', [
            'admin_notes' => [
                'type'  => 'TEXT',
                'null'  => true,
                'after' => 'notes',
            ],
            'rejected_at' => [
                'type'  => 'DATETIME',
                'null'  => true,
                'after' => 'paid_at',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('orders', ['admin_notes', 'rejected_at']);
    }
}
