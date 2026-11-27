<?php

namespace App\Domain\Platform\Exceptions;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;

/**
 * Pelanggaran aturan bisnis penagihan (mis. bayar tagihan yang sudah dibatalkan).
 * Dirender otomatis oleh Laravel: kembali ke halaman sebelumnya dengan pesan error.
 */
class BillingException extends RuntimeException
{
    public function render(Request $request): RedirectResponse
    {
        return back()
            ->withErrors(['billing' => $this->getMessage()])
            ->with('failed', $this->getMessage());
    }
}
