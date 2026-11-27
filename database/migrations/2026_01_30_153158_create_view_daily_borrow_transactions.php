<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
            CREATE VIEW daily_borrowers AS
            SELECT 
                b.fullname, 
                b.phone, 
                tx.created_at,
                m.id AS major_id,
                COUNT(tl.id) AS total_items
            FROM transaction tx
            INNER JOIN borrower b 
                ON b.id = tx.borrower_id
            INNER JOIN major m 
                ON m.id = b.major_id
            LEFT JOIN transaction_lines tl 
                ON tl.transaction_id = tx.id
            WHERE tx.tx_type = 'BORROW'
              AND DATE(tx.created_at) = CURDATE()
            GROUP BY 
                b.fullname, 
                b.phone, 
                tx.created_at,
                m.id
            ORDER BY tx.created_at DESC
            LIMIT 5
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS daily_borrowers");
    }
};
