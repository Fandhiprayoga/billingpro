<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class AddUuidToLicenses extends Migration
{
    public function up()
    {
        // Tambah kolom uuid
        $this->forge->addColumn('licenses', [
            'uuid' => [
                'type'       => 'CHAR',
                'constraint' => 36,
                'null'       => true,
                'after'      => 'id',
            ],
        ]);

        // Backfill UUID untuk data yang sudah ada
        $db = \Config\Database::connect();
        $existing = $db->table('licenses')->select('id')->get()->getResult();
        foreach ($existing as $row) {
            $uuid = $this->generateUuid();
            $db->table('licenses')->where('id', $row->id)->update(['uuid' => $uuid]);
        }

        // Set NOT NULL dan UNIQUE setelah backfill
        $this->forge->modifyColumn('licenses', [
            'uuid' => [
                'type'       => 'CHAR',
                'constraint' => 36,
                'null'       => false,
            ],
        ]);

        $db->query('ALTER TABLE licenses ADD UNIQUE INDEX idx_licenses_uuid (uuid)');
    }

    public function down()
    {
        $db = \Config\Database::connect();
        $db->query('ALTER TABLE licenses DROP INDEX idx_licenses_uuid');

        $this->forge->dropColumn('licenses', 'uuid');
    }

    /**
     * Generate UUID v4.
     */
    private function generateUuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // version 4
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // variant

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
