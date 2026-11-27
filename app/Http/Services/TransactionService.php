<?php

namespace App\Http\Services;

use Illuminate\Support\Facades\DB;

class TransactionService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function getTransactions(string $majorName)
    {
        $data = DB::table('transaction as tx')
            ->join('users as u', 'u.id','=','tx.staff_id')
            ->join('major as m','m.id','=','u.major_id');

        if(!is_null($majorName))
        {
            $data->where('m.name','=',$majorName);
        }        
    }
}
