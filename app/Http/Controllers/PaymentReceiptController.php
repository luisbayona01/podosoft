<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use Illuminate\Http\Request;

class PaymentReceiptController extends Controller
{
    public function show($id)
    {
        $pago = Pago::with(['paciente', 'cita.sede', 'servicio'])->findOrFail($id);
        return view('payments.receipt', compact('pago'));
    }
}
