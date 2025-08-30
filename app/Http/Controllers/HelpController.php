<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HelpController extends Controller
{
    public function index()
    {
        return view('admin.help.index');
    }

    public function mercadoPagoNovaApi()
    {
        return view('admin.help.mercado-pago-nova-api');
    }

    public function mercadoPago()
    {
        return view('admin.help.mercado-pago');
    }

    public function configuracaoPagamentos()
    {
        return view('admin.help.configuracao-pagamentos');
    }

    public function dashboardPagamentos()
    {
        return view('admin.help.dashboard-pagamentos');
    }



    public function automacaoPagamentos()
    {
        return view('admin.help.automacao-pagamentos');
    }
} 