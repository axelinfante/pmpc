<?php

namespace App\Http\Controllers;

use DateTime;

use Illuminate\Http\Request;
use App\CuentaCorriente;
use App\Contact;
use App\Transaction;
use App\Account;
use App\PaymentMethod;
use App\Company;
use App\ChartOfAccount;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use DataTables;
use Log;

class CuentaCorrienteController extends Controller
{

    public function __construct()
	{
		date_default_timezone_set(get_company_option('timezone', get_option('timezone', 'Asia/Dhaka')));
	}

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view("backend.accounting.cuenta_corriente.index");
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // dd('a');
        $contact = Contact::findOrFail($id);
        $currency = currency();

        return view(
            "backend.accounting.cuenta_corriente.show",
            compact("contact", "currency", "id"),
        );
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * Obtener movimientos de cuenta corriente para DataTable
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getMovimientos(Request $request, $id)
    {
        if ($request->ajax()) {
            try {
                $currency ='$'; //currency();

                $movimientos = CuentaCorriente::with(["comprobable"])
                    ->where("payer_payee_id", $id)
                    ->select("cuenta_corriente.*");

                // Aplicar filtros
                if ($request->has('fecha_desde') && !empty($request->fecha_desde)) {
                    $movimientos->whereDate('created_at', '>=', $request->fecha_desde);
                }

                if ($request->has('fecha_hasta') && !empty($request->fecha_hasta)) {
                    $movimientos->whereDate('created_at', '<=', $request->fecha_hasta);
                }

                if ($request->has('tipo_comprobante') && !empty($request->tipo_comprobante)) {
                    $movimientos->where('comprobable_type', $request->tipo_comprobante);
                }

                if ($request->has('moneda') && !empty($request->moneda)) {
                    if ($request->moneda == 'peso') {
                        $movimientos->where(function($query) {
                            $query->where('debe_peso', '>', 0)
                                  ->orWhere('haber_peso', '>', 0);
                        });
                    } elseif ($request->moneda == 'usd') {
                        $movimientos->where(function($query) {
                            $query->where('debe_usd', '>', 0)
                                  ->orWhere('haber_usd', '>', 0);
                        });
                    }
                }

                $movimientos->orderBy("created_at", "asc")
                    ->orderBy("id", "asc");

                // Usar DataTables con ordenamiento del servidor
                return DataTables::eloquent($movimientos)
                ->addIndexColumn()
                ->editColumn("created_at", function ($movimiento) {
                    if ($movimiento->created_at) {
                        try {
                            // Devolver fecha en formato ISO simple para JavaScript
                            $date = new DateTime($movimiento->created_at);
                            return $date->format('Y-m-d H:i:s');
                        } catch (\Exception $e) {
                            // Si hay error, devolver la fecha original
                            return $movimiento->created_at;
                        }
                    }
                    return '';
                })
                ->editColumn("comprobable_type", function ($movimiento) {
                    return $this->getTipoComprobable(
                        $movimiento->comprobable_type
                    );
                })
                ->editColumn("comprobable_id", function ($movimiento) {
                    return $this->getReferenciaComprobable($movimiento);
                })
                ->editColumn("debe_peso", function ($movimiento) use (
                    $currency
                ) {
                    $debe = $movimiento->debe_peso ?? 0;
                    if ($debe > 0) {
                        // return '<span class="text-danger float-right">' .
                        //     decimalPlace($debe) .
                        //     "</span>";
                        return (float) $debe;
                    }
                    return (float) $debe;
                })
                ->editColumn("haber_peso", function ($movimiento) use (
                   $currency
                ) {
                    $haber = $movimiento->haber_peso ?? 0;
                    if ($haber > 0) {
                        return (float) $haber;
                    }
                    return (float) $haber;
                })
                ->editColumn("saldo_peso", function ($movimiento) use (
                   $currency
                ) {
                    $saldo = $movimiento->saldo_peso ?? 0;
                    $clase = $saldo >= 0 ? "text-success" : "text-danger";
                    // Asegurar que se devuelva como número, no como string
                    return (float) $saldo;
                })
                ->editColumn("debe_usd", function ($movimiento) {
                    $debe = $movimiento->debe_usd ?? 0;
                    if ($debe > 0) {
                        return (float) $debe;
                    }
                    return (float) $debe;
                })
                ->editColumn("haber_usd", function ($movimiento) {
                    $haber = $movimiento->haber_usd ?? 0;
                    if ($haber > 0) {
                        return (float) $haber;
                    }
                    return (float) $haber;
                })
                ->editColumn("saldo_usd", function ($movimiento) {
                    $saldo = $movimiento->saldo_usd ?? 0;
                    $clase = $saldo >= 0 ? "text-success" : "text-danger";
                    // Asegurar que se devuelva como número, no como string
                    return (float) $saldo;
                })
                ->editColumn("nota", function ($movimiento) {
                    return $movimiento->nota ?? "";
                })
                ->editColumn("tiene_conversion", function ($movimiento) {
                    return $movimiento->tiene_conversion ?? false;
                })
                ->editColumn("monto_original", function ($movimiento) {
                    return $movimiento->monto_original ?? null;
                })
                ->editColumn("moneda_original", function ($movimiento) {
                    return $movimiento->moneda_original ?? null;
                })
                ->editColumn("monto_convertido", function ($movimiento) {
                    return $movimiento->monto_convertido ?? null;
                })
                ->editColumn("moneda_convertida", function ($movimiento) {
                    return $movimiento->moneda_convertida ?? null;
                })
                ->editColumn("tasa_aplicada", function ($movimiento) {
                    return $movimiento->tasa_aplicada ?? null;
                })
                ->editColumn("detalle_conversion", function ($movimiento) {
                    return $movimiento->detalle_conversion ?? null;
                })
                ->editColumn("monto_aplicado", function ($movimiento) {
                    return $movimiento->monto_aplicado ?? null;
                })
                ->editColumn("moneda_aplicada", function ($movimiento) {
                    return $movimiento->moneda_aplicada ?? null;
                })
                ->editColumn("sobrante", function ($movimiento) {
                    return $movimiento->sobrante ?? null;
                })
                ->editColumn("moneda_sobrante", function ($movimiento) {
                    return $movimiento->moneda_sobrante ?? null;
                })
                ->addColumn("acciones", function ($movimiento) {
                    return $this->getAccionesComprobable($movimiento);
                })
                ->rawColumns([
                    "debe_peso",
                    "haber_peso",
                    "saldo_peso",
                    "debe_usd",
                    "haber_usd",
                    "saldo_usd",
                    "acciones",
                ])
                ->make(true);
            } catch (\Exception $e) {
                \Log::error('Error en getMovimientos', ['id' => $id, 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
                return response()->json(['error' => $e->getMessage()], 500);
            }
        }
    }

    /**
     * Obtener resumen de saldos
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getResumen($id)
    {
        $resumen = CuentaCorriente::where("payer_payee_id", $id)
            ->select(
                DB::raw("SUM(debe_peso) as total_debe_peso"),
                DB::raw("SUM(haber_peso) as total_haber_peso"),
                DB::raw("SUM(debe_usd) as total_debe_usd"),
                DB::raw("SUM(haber_usd) as total_haber_usd"),
                DB::raw("COUNT(*) as total_movimientos"),
            )
            ->first();

            // dd($resumen);

        // Obtener último saldo
        $ultimoMovimiento = CuentaCorriente::where("payer_payee_id", $id)
            ->orderBy("created_at", "desc")
            ->first();

        return response()->json([
            "total_debe_peso" => $resumen->total_debe_peso ?? 0,
            "total_haber_peso" => $resumen->total_haber_peso ?? 0,
            "total_debe_usd" => $resumen->total_debe_usd ?? 0,
            "total_haber_usd" => $resumen->total_haber_usd ?? 0,
            "total_movimientos" => $resumen->total_movimientos ?? 0,
            "saldo_actual_peso" => $ultimoMovimiento->saldo_peso ?? 0,
            "saldo_actual_usd" => $ultimoMovimiento->saldo_usd ?? 0,
        ]);
    }

    /**
     * Obtener tipo de comprobable en formato legible
     *
     * @param  string  $comprobableType
     * @return string
     */
    private function getTipoComprobable($comprobableType)
    {
        $tipos = [
            "App\Invoice" => "Factura",
            "App\Transaction" => "Transacción",
            "App\SalesReturn" => "Devolución",
            // "App\Quotation" => "Cotización",
        ];

        return $tipos[$comprobableType] ?? $comprobableType;
    }

    /**
     * Obtener referencia del comprobable
     *
     * @param  \App\CuentaCorriente  $movimiento
     * @return string
     */
    private function getReferenciaComprobable($movimiento)
    {
        if (!$movimiento->comprobable) {
            return "N/A";
        }

        $comprobable = $movimiento->comprobable;

        switch ($movimiento->comprobable_type) {
            case "App\Invoice":
                return $comprobable->invoice_number ??
                    "Factura #" . $comprobable->id;
            case "App\Transaction":
                return "Transacción #" . $comprobable->id;
            case "App\SalesReturn":
                return "Devolución #" . $comprobable->id;
            case "App\Quotation":
                return $comprobable->quotation_number ??
                    "Cotización #" . $comprobable->id;
            default:
                return "Referencia #" . $comprobable->id;
        }
    }

    /**
     * Obtener acciones para el comprobable
     *
     * @param  \App\CuentaCorriente  $movimiento
     * @return string
     */
    private function getAccionesComprobable($movimiento)
    {
        if (!$movimiento->comprobable) {
            return "";
        }

        $comprobable = $movimiento->comprobable;
        $url = "";
        $texto = "";

        switch ($movimiento->comprobable_type) {
            case "App\Invoice":
                $url = action("InvoiceController@show", $comprobable->id);
                $texto = "Ver Factura";
                break;
            case "App\Transaction":
                $url = "#";
                $texto = "Ver Transacción";
                break;
            case "App\SalesReturn":
                $url = "#";
                $texto = "Ver Devolución";
                break;
            case "App\Quotation":
                $url = action("QuotationController@show", $comprobable->id);
                $texto = "Ver Cotización";
                break;
            default:
                return "";
        }

        return '<a href="' .
            $url .
            '" target="_blank" class="btn btn-xs btn-primary">' .
            $texto .
            "</a>";
    }

    /**
     * Obtener lista de contactos con cuenta corriente para DataTable
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getContactos(Request $request)
    {
        // Debug: Registrar la solicitud
        Log::info("Solicitud getContactos recibida", [
            "ajax" => $request->ajax(),
            "headers" => $request->headers->all(),
            "user" => auth()->user() ? auth()->user()->id : "no autenticado",
        ]);

        if ($request->ajax()) {
            try {
                $contactos = Contact::with(["group"])
                    ->whereHas("cuentasCorrientes")
                    ->select("contacts.*");

                // Aplicar filtros de búsqueda
                if ($request->has('search') && !empty($request->search['value'])) {
                    $searchValue = $request->search['value'];
                    $contactos->where(function($query) use ($searchValue) {
                        $query->where('contact_name', 'like', '%' . $searchValue . '%')
                              ->orWhere('contact_email', 'like', '%' . $searchValue . '%')
                              ->orWhere('contact_phone', 'like', '%' . $searchValue . '%')
                              ->orWhereHas('group', function($q) use ($searchValue) {
                                  $q->where('name', 'like', '%' . $searchValue . '%');
                              });
                    });
                }

                $contactos->orderBy("contact_name", "asc");

                return DataTables::eloquent($contactos)
                    ->addIndexColumn()
                    ->addColumn("contacto", function ($contact) {
                        return $contact->contact_name;
                    })
                    ->addColumn("grupo", function ($contact) {
                        return $contact->group->name ?? "N/A";
                    })
                    ->addColumn("saldo_peso", function ($contact) {
                        $ultimoMovimiento = CuentaCorriente::where(
                            "payer_payee_id",
                            $contact->id,
                        )
                            ->orderBy("created_at", "desc")
                            ->first();
                        return $ultimoMovimiento->saldo_peso ?? 0;
                    })
                    ->addColumn("saldo_usd", function ($contact) {
                        $ultimoMovimiento = CuentaCorriente::where(
                            "payer_payee_id",
                            $contact->id,
                        )
                            ->orderBy("created_at", "desc")
                            ->first();
                        return $ultimoMovimiento->saldo_usd ?? 0;
                    })
                    ->addColumn("ultimo_movimiento", function ($contact) {
                        $ultimoMovimiento = CuentaCorriente::where(
                            "payer_payee_id",
                            $contact->id,
                        )
                            ->orderBy("created_at", "desc")
                            ->first();
                        return $ultimoMovimiento->created_at ?? null;
                    })
                    ->addColumn("total_movimientos", function ($contact) {
                        return CuentaCorriente::where(
                            "payer_payee_id",
                            $contact->id,
                        )->count();
                    })
                    ->addColumn("url_cuenta_corriente", function ($contact) {
                        return route("cuenta_corriente.show", $contact->id);
                    })
                    ->addColumn("acciones", function ($contact) {
                        return '<a href="' .
                            route("cuenta_corriente.show", $contact->id) .
                            '" class="btn btn-primary btn-xs">' .
                            '<i class="fas fa-eye"></i> Ver Cuenta' .
                            "</a>";
                    })
                    ->addColumn("contact_name", function ($contact) {
                        return $contact->contact_name;
                    })
                    ->addColumn("contact_email", function ($contact) {
                        return $contact->contact_email ?? "";
                    })
                    ->addColumn("group_name", function ($contact) {
                        return $contact->group->name ?? "";
                    })
                    ->filterColumn('contacto', function($query, $keyword) {
                        $query->where('contact_name', 'like', "%{$keyword}%")
                              ->orWhere('contact_email', 'like', "%{$keyword}%");
                    })
                    ->filterColumn('grupo', function($query, $keyword) {
                        $query->whereHas('group', function($q) use ($keyword) {
                            $q->where('name', 'like', "%{$keyword}%");
                        });
                    })
                    ->filterColumn('saldo_peso', function($query, $keyword) {
                        // Buscar por saldo en cuenta corriente
                        $query->whereHas('cuentasCorrientes', function($q) use ($keyword) {
                            $q->where('saldo_peso', 'like', "%{$keyword}%");
                        });
                    })
                    ->filterColumn('saldo_usd', function($query, $keyword) {
                        // Buscar por saldo en cuenta corriente
                        $query->whereHas('cuentasCorrientes', function($q) use ($keyword) {
                            $q->where('saldo_usd', 'like', "%{$keyword}%");
                        });
                    })
                    ->filterColumn('ultimo_movimiento', function($query, $keyword) {
                        // Buscar por fecha del último movimiento
                        $query->whereHas('cuentasCorrientes', function($q) use ($keyword) {
                            $q->where('created_at', 'like', "%{$keyword}%");
                        });
                    })
                    ->rawColumns(["acciones"])
                    ->make(true);
            } catch (\Exception $e) {
                Log::error("Error en getContactos: " . $e->getMessage(), [
                    "trace" => $e->getTraceAsString(),
                ]);

                return response()->json(
                    [
                        "error" => "Error interno del servidor",
                        "message" => $e->getMessage(),
                    ],
                    500,
                );
            }
        }

        Log::warning("Solicitud no AJAX a getContactos");
        return response()->json(["error" => "Solicitud no válida"], 400);
    }

    /**
     * Mostrar formulario para realizar devolución de saldo a favor
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function createDevolucion(Request $request)
    {
        $id = $request->get('id');

        // Obtener saldo actual del cliente
        $ultimoMovimiento = CuentaCorriente::where('payer_payee_id', $id)
            ->orderBy('created_at', 'desc')
            ->first();

        $saldoPeso = $ultimoMovimiento ? $ultimoMovimiento->saldo_peso : 0;
        $saldoUsd = $ultimoMovimiento ? $ultimoMovimiento->saldo_usd : 0;

        // Obtener rubros de gasto (chart of accounts)
        $charts = ChartOfAccount::where('type', 'expense')
            ->where('company_id', company_id())
            ->orderBy('name', 'asc')
            ->get();

        // Obtener métodos de pago
        $payment_methods = PaymentMethod::where('company_id', company_id())
            ->orderBy('name', 'asc')
            ->get();

        $data = [
            'id' => $id,
            'saldo_peso' => $saldoPeso,
            'saldo_usd' => $saldoUsd,
            'charts' => $charts,
            'payment_methods' => $payment_methods,
        ];

        if (!$request->ajax()) {
            return view('backend.accounting.cuenta_corriente.devolucion.create', $data);
        } else {
            return view('backend.accounting.cuenta_corriente.devolucion.modal.create', $data);
        }
    }

    /**
     * Procesar devolución de saldo a favor
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeDevolucion(Request $request)
    {
        $id = $request->get('id');

        $validator = Validator::make($request->all(), [
            'trans_date' => 'required',
            'account_id' => 'required',
            'chart_id' => 'nullable',
            'amount' => 'required|numeric|min:0.01',
            'payment_method_id' => 'required',
            'usd' => 'nullable|in:0,1',
            'tasa' => 'required_if:usd,1|numeric|min:0.01',
            'reference' => 'nullable|max:50',
            'attachment' => 'nullable|mimes:jpeg,png,jpg,doc,pdf,docx,zip',
        ], [
            'tasa.required_if' => 'La tasa de cambio es requerida cuando se retira en USD.',
            'chart_id.required' => 'El rubro de gasto es requerido.',
        ]);

        // Buscar automáticamente el rubro "Devolución" si no se proporciona
        $chart_id = $request->input('chart_id');
        if (!$chart_id) {
            $devolucionChart = ChartOfAccount::where('name', 'like', '%Devolución%')
                ->where('type', 'expense')
                ->where('company_id', company_id())
                ->first();

            if ($devolucionChart) {
                $chart_id = $devolucionChart->id;
            } else {
                // Crear el rubro automáticamente si no existe
                $devolucionChart = ChartOfAccount::create([
                    'name' => 'Devolución a Cliente',
                    'type' => 'expense',
                    'company_id' => company_id(),
                ]);
                $chart_id = $devolucionChart->id;
            }

            // Actualizar el request con el chart_id encontrado/creado
            $request->merge(['chart_id' => $chart_id]);
        }

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
            } else {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }
        }

        $attachment = '';
        if ($request->hasfile('attachment')) {
            $file = $request->file('attachment');
            $attachment = time() . $file->getClientOriginalName();
            $file->move(public_path() . "/uploads/transactions/", $attachment);
        }

        // Validación: Verificar que el cliente tenga saldo a favor (saldo negativo)
        $ultimoMovimiento = CuentaCorriente::where('payer_payee_id', $id)
            ->orderBy('created_at', 'desc')
            ->first();

        $saldoPeso = $ultimoMovimiento ? $ultimoMovimiento->saldo_peso : 0;
        $saldoUsd = $ultimoMovimiento ? $ultimoMovimiento->saldo_usd : 0;

        $monto = $request->input('amount');
        $esUsd = $request->input('usd') == 1;
        $tasa = $request->input('tasa', 1);

        if ($esUsd) {
            // Validar saldo en USD
            if ($saldoUsd >= 0) {
                // Si no tiene saldo en USD, verificar si tiene en ARS y convertir
                if ($saldoPeso < 0 && $tasa > 1) {
                    // Tiene saldo en ARS, convertir a USD
                    $montoEquivalenteUsd = abs($saldoPeso) / $tasa;
                    if ($montoEquivalenteUsd < $monto) {
                        return response()->json([
                            'result' => 'error',
                            'message' => ['El monto a retirar ('.$monto.' USD) excede el saldo a favor disponible (equivalente a '.number_format($montoEquivalenteUsd, 2).' USD luego de conversión).']
                        ]);
                    }
                    // Aplicar conversión: el monto en ARS será $monto * $tasa
                    $montoEnPesos = $monto * $tasa;
                    // Actualizar monto para la transacción
                    $monto = $montoEnPesos;
                    $esUsd = false; // Se retira en ARS después de conversión
                } else {
                    return response()->json([
                        'result' => 'error',
                        'message' => ['El cliente no tiene saldo a favor en USD para retirar.']
                    ]);
                }
            } else if (abs($saldoUsd) < $monto) {
                return response()->json([
                    'result' => 'error',
                    'message' => ['El monto a retirar ('.$monto.' USD) excede el saldo a favor disponible ('.abs($saldoUsd).' USD).']
                ]);
            }
        } else {
            // Validar saldo en Pesos
            if ($saldoPeso >= 0) {
                // Si no tiene saldo en ARS, verificar si tiene en USD y convertir
                if ($saldoUsd < 0 && $tasa > 1) {
                    // Tiene saldo en USD, convertir a ARS
                    $montoEquivalentePesos = abs($saldoUsd) * $tasa;
                    if ($montoEquivalentePesos < $monto) {
                        return response()->json([
                            'result' => 'error',
                            'message' => ['El monto a retirar ('.$monto.' $) excede el saldo a favor disponible (equivalente a '.number_format($montoEquivalentePesos, 2).' $ luego de conversión).']
                        ]);
                    }
                    // Aplicar conversión: el monto en USD será $monto / $tasa
                    $montoEnUsd = $monto / $tasa;
                    // Actualizar monto para la transacción
                    $monto = $montoEnUsd;
                    $esUsd = true; // Se retira en USD después de conversión
                } else {
                    return response()->json([
                        'result' => 'error',
                        'message' => ['El cliente no tiene saldo a favor en Pesos para retirar.']
                    ]);
                }
            } else if (abs($saldoPeso) < $monto) {
                return response()->json([
                    'result' => 'error',
                    'message' => ['El monto a retirar ('.$monto.' $) excede el saldo a favor disponible ('.abs($saldoPeso).' $).']
                ]);
            }
        }

        DB::beginTransaction();
        try {
            $methodP = PaymentMethod::where('name', 'like', '%Gasto cc')->first();

            if (!$methodP) {
                DB::rollBack();
                $errorMessage = 'El método de pago "Gasto cc" no está configurado en el sistema.';
                if ($request->ajax()) {
                    return response()->json([
                        'result' => 'error',
                        'message' => [$errorMessage]
                    ]);
                } else {
                    return redirect()->back()
                        ->with('error', $errorMessage)
                        ->withInput();
                }
            }

            // NO crear transacción para devoluciones de saldo a favor
            // El saldo a favor ya está en la cuenta corriente del cliente
            // Solo necesitamos crear un movimiento en cuenta corriente
            // No es un gasto real (cc_expense) porque el dinero ya está en cuenta corriente

            // Crear movimiento manual en cuenta corriente (HABER para aumentar crédito del cliente)
            $movimientoDevolucion = new \App\CuentaCorriente();
            $movimientoDevolucion->payer_payee_id = $id;
            $movimientoDevolucion->comprobable_type = 'App\Transaction';
            $movimientoDevolucion->comprobable_id = 0; // No vinculado a transacción específica
            
            // Asignar montos según moneda (después de posible conversión)
            if ($esUsd) {
                $movimientoDevolucion->debe_usd = 0;
                $movimientoDevolucion->haber_usd = $monto; // HABER aumenta crédito
                $movimientoDevolucion->debe_peso = 0;
                $movimientoDevolucion->haber_peso = 0;
            } else {
                $movimientoDevolucion->debe_usd = 0;
                $movimientoDevolucion->haber_usd = 0;
                $movimientoDevolucion->debe_peso = 0;
                $movimientoDevolucion->haber_peso = $monto; // HABER aumenta crédito
            }

            $movimientoDevolucion->tasa_cambio = $request->input('tasa', 1);
            $movimientoDevolucion->nota = 'Devolución de saldo a favor: ' . ($esUsd ? 'USD ' . $monto : '$ ' . $monto);
            $movimientoDevolucion->fue_revertido = 0;
            $movimientoDevolucion->save();

            // Crear transacción de gasto (egreso real - dinero que sale de la cuenta bancaria)
            $transaction = new Transaction();
            // Crear transacción de gasto (egreso real - dinero que sale de la cuenta bancaria)
            // Desactivamos temporalmente el TransactionObserver para evitar registro duplicado en cuenta_corriente
            Transaction::withoutEvents(function () use ($request, $chart_id, $monto, $esUsd, $id, $attachment, $movimientoDevolucion, &$transaction) {
                $transaction = new Transaction();
                $transaction->trans_date = $request->input('trans_date');
                $transaction->account_id = $request->input('account_id');
                $transaction->chart_id = $chart_id;
                $transaction->type = 'expense';
                $transaction->dr_cr = 'dr';
                $transaction->amount = $monto;

                // Asignar montos según moneda (después de posible conversión)
                if ($esUsd) {
                    $transaction->amount_usd = $monto;
                    $transaction->amount_peso = 0;
                } else {
                    $transaction->amount_peso = $monto;
                    $transaction->amount_usd = 0;
                }

                // Obtener la cuenta primero para poder acceder a account_currency
                $account = \App\Account::find($request->input('account_id'));
                $transaction->base_amount = convert_currency($account ? $account->account_currency : 'ARS', base_currency(), $transaction->amount);
                $transaction->payer_payee_id = $id;
                $transaction->payment_method_id = $request->input('payment_method_id');
                $transaction->reference = $request->input('reference');
                $transaction->razon_social = $request->input('razon_social');
                $transaction->tipo_comprobante_id = $request->input('tipo_comprobante_id');
                $transaction->imputar_a = $request->input('imputar_a');
                $transaction->detalle_rubro = $request->input('detalle_rubro');
                $transaction->banco = $request->input('banco');
                $transaction->cheque_nro = $request->input('cheque_nro');
                $transaction->cheque_vencimiento = $request->input('cheque_vencimiento');
                $transaction->cheque_entregado_a = $request->input('cheque_entregado_a');
                $transaction->attachment = $attachment;
                $transaction->note = 'Egreso por devolución a cliente - Saldo a favor: ' . ($esUsd ? 'USD ' . $monto : '$ ' . $monto);
                $transaction->usd = $request->input('usd', 0);
                $transaction->tasa = $request->input('tasa', 1);
                $transaction->trans_asoc = 0; // No asociado a transacción de cuenta corriente
                
                // Asignar compañía según imputar_a
                $imputarA = $request->input('imputar_a');
                $companyNames = [
                    'distribuir' => 'A dividir',
                    'triunvirato' => 'Triunvirato',
                    'pentacar' => 'Pentacar',
                    'paternal' => 'Paternal',
                    'g.u.t.' => 'Gut',
                ];

                if (isset($companyNames[$imputarA])) {
                    $company = Company::where('business_name', $companyNames[$imputarA])->first();
                    if ($company) {
                        $transaction->company_id = $company->id;
                    }
                }

                $transaction->save();
            });

            DB::commit();

            if (!$request->ajax()) {
                return redirect()->route('cuenta_corriente.show', $id)
                    ->with('success', _lang('Devolución procesada exitosamente'));
            } else {
                return response()->json([
                    'result' => 'success',
                    'action' => 'store',
                    'message' => _lang('Devolución procesada exitosamente. Se devolvió ' . ($esUsd ? 'USD ' . $monto : '$ ' . $monto) . ' al cliente.'),
                    'data' => $transaction
                ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al procesar devolución: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'result' => 'error',
                    'message' => ['Error al procesar la devolución: ' . $e->getMessage()]
                ]);
            } else {
                return redirect()->back()
                    ->with('error', _lang('Error al procesar la devolución: ' . $e->getMessage()))
                    ->withInput();
            }
        }
    }

    /**
     * Pagar una factura automáticamente desde el saldo a favor del cliente en cuenta corriente
     *
     * @param int $invoiceId ID de la factura
     * @param int $clientId ID del cliente
     * @return array Resultado de la operación
     */
    public function pagarFacturaDesdeSaldoAFavor($invoiceId, $clientId)
    {
        try {
            DB::beginTransaction();

            // Obtener la factura
            $invoice = \App\Invoice::find($invoiceId);
            if (!$invoice) {
                return ['success' => false, 'message' => 'Factura no encontrada'];
            }

            // Verificar si la factura ya está pagada
            if ($invoice->status == 'Paid') {
                return ['success' => false, 'message' => 'La factura ya está pagada'];
            }

            // Obtener saldo actual del cliente EXCLUYENDO la factura actual
            $ultimoMovimiento = CuentaCorriente::where('payer_payee_id', $clientId)
                ->where(function($query) use ($invoiceId) {
                    $query->where('comprobable_type', '!=', 'App\Invoice')
                          ->orWhere('comprobable_id', '!=', $invoiceId);
                })
                ->orderBy('id', 'desc')
                ->first();

            $saldoPeso = $ultimoMovimiento ? $ultimoMovimiento->saldo_peso : 0;
            $saldoUsd = $ultimoMovimiento ? $ultimoMovimiento->saldo_usd : 0;

            // Determinar saldo disponible según moneda de la factura
            $saldoDisponible = 0;
            if ($invoice->is_usd) {
                // Factura en USD: usar saldo en USD
                // Saldo negativo = saldo a favor del cliente
                $saldoDisponible = -$saldoUsd;
            } else {
                // Factura en pesos: usar saldo en pesos
                $saldoDisponible = -$saldoPeso;
            }

            // Verificar si hay saldo a favor disponible
            if ($saldoDisponible <= 0) {
                return ['success' => false, 'message' => 'El cliente no tiene saldo a favor disponible'];
            }

            // Calcular monto a pagar (no puede exceder el total de la factura ni el saldo disponible)
            $montoAPagar = min($saldoDisponible, $invoice->grand_total - $invoice->paid);

            if ($montoAPagar <= 0) {
                return ['success' => false, 'message' => 'No hay monto pendiente por pagar'];
            }

            // Buscar método de pago "Gasto cc" para registrar el pago desde cuenta corriente
            $methodP = PaymentMethod::where('name', 'like', '%Gasto cc')->first();
            if (!$methodP) {
                return ['success' => false, 'message' => 'Método de pago "Gasto cc" no configurado'];
            }

            // Buscar rubro de venta o usar uno por defecto
            $rubroVenta = ChartOfAccount::where('type', 'income')
                ->where('company_id', $invoice->company_id)
                ->first();
        
            if (!$rubroVenta) {
                // Si no hay rubro de income, buscar cualquier rubro
                $rubroVenta = ChartOfAccount::where('company_id', $invoice->company_id)
                    ->first();
                    
                if (!$rubroVenta) {
                    return ['success' => false, 'message' => 'No se encontró un rubro contable para la compañía'];
                }
            }

            // Crear transacción para registrar el pago desde cuenta corriente
            // No es de tipo dr_cr porque el dinero ya está en la cuenta corriente del cliente
            // Usamos withoutEvents para evitar que el TransactionObserver cree un movimiento duplicado en cuenta_corriente
            $transaction = Transaction::withoutEvents(function () use ($invoice, $rubroVenta, $methodP, $montoAPagar, $clientId, $invoiceId) {
                $transaction = new Transaction();
                $transaction->trans_date = date('Y-m-d');
                $transaction->chart_id = $rubroVenta->id;
                $transaction->type = 'cc_expense';
                $transaction->dr_cr = 'cc';
                $transaction->amount = $montoAPagar;
                
                // Asignar montos según moneda
                if ($invoice->is_usd) {
                    $transaction->amount_usd = $montoAPagar;
                    $transaction->amount_peso = 0;
                } else {
                    $transaction->amount_peso = $montoAPagar;
                    $transaction->amount_usd = 0;
                }
                
                $transaction->base_amount = $montoAPagar;
                $transaction->payer_payee_id = $clientId;
                $transaction->payment_method_id = $methodP->id;
                $transaction->invoice_id = $invoiceId;
                $transaction->note = 'Factura #' . $invoice->invoice_number . ' pagada automáticamente desde saldo a favor en cuenta corriente';
                $transaction->company_id = $invoice->company_id;
                $transaction->usd = $invoice->is_usd ? 1 : 0;
                $transaction->tasa = $invoice->tasa ?? 1;
                
                $transaction->save();
                return $transaction;
            });

            // Crear movimiento en cuenta_corriente vinculado a la transacción
            // Buscar si ya existe un movimiento para esta factura (creado por el observer)
            $movimientoExistente = \App\CuentaCorriente::where('comprobable_type', 'App\Invoice')
                ->where('comprobable_id', $invoiceId)
                ->where('payer_payee_id', $clientId)
                ->where('nota', 'like', 'Factura #%')
                ->where('fue_revertido', 0)
                ->first();

            // Determinar si es pago completo o parcial
            $esPagoCompleto = ($montoAPagar >= $invoice->grand_total - $invoice->paid);
            
            // Si existe un movimiento DEBE para esta factura Y es pago COMPLETO, ELIMINARLO y crear solo un movimiento neto
            if ($movimientoExistente && $movimientoExistente->debe_peso + $movimientoExistente->debe_usd > 0 && $esPagoCompleto) {
                // Eliminar el movimiento de creación de factura
                $movimientoExistente->delete();
                
                // Crear un solo movimiento neto que represente la factura pagada automáticamente
                $movimientoNeto = new \App\CuentaCorriente();
                $movimientoNeto->payer_payee_id = $clientId;
                $movimientoNeto->comprobable_type = 'App\Transaction';
                $movimientoNeto->comprobable_id = $transaction->id;
                
                // Para una factura pagada automáticamente desde saldo a favor:
                // - DEBE: monto de la factura (disminuye el saldo negativo/saldo a favor)
                // - HABER: 0 (no hay crédito porque se usó saldo existente)
                // El efecto neto es reducir el saldo a favor del cliente (hacerlo menos negativo)
                if ($invoice->is_usd) {
                    $movimientoNeto->debe_usd = $montoAPagar;
                    $movimientoNeto->haber_usd = 0;
                    $movimientoNeto->debe_peso = 0;
                    $movimientoNeto->haber_peso = 0;
                } else {
                    $movimientoNeto->debe_peso = $montoAPagar;
                    $movimientoNeto->haber_peso = 0;
                    $movimientoNeto->debe_usd = 0;
                    $movimientoNeto->haber_usd = 0;
                }
                
                $movimientoNeto->tasa_cambio = $invoice->tasa ?? 1;
                
                // Determinar símbolo de moneda
                $simboloMoneda = $invoice->is_usd ? 'USD ' : '$';
                
                $movimientoNeto->nota = 'Factura #' . $invoice->invoice_number . 
                                       ' pagada automáticamente desde saldo a favor: ' . $simboloMoneda . number_format($montoAPagar, 2);
                $movimientoNeto->fue_revertido = 0;
                
                $movimientoNeto->save();
            } else {
                // PAGO PARCIAL: Crear dos movimientos:
                // 1. DEBE para cancelar el saldo a favor (llevar saldo a 0)
                // 2. HABER para aplicar el pago a la factura
                
                // 1. Movimiento para cancelar saldo a favor (DEBE)
                $movimientoCancelacionSaldo = new \App\CuentaCorriente();
                $movimientoCancelacionSaldo->payer_payee_id = $clientId;
                $movimientoCancelacionSaldo->comprobable_type = 'App\Transaction';
                $movimientoCancelacionSaldo->comprobable_id = $transaction->id;
                
                // Cancelar el saldo a favor: DEBE del monto del saldo a favor
                if ($invoice->is_usd) {
                    $movimientoCancelacionSaldo->debe_usd = $montoAPagar;
                    $movimientoCancelacionSaldo->haber_usd = 0;
                    $movimientoCancelacionSaldo->debe_peso = 0;
                    $movimientoCancelacionSaldo->haber_peso = 0;
                } else {
                    $movimientoCancelacionSaldo->debe_peso = $montoAPagar;
                    $movimientoCancelacionSaldo->haber_peso = 0;
                    $movimientoCancelacionSaldo->debe_usd = 0;
                    $movimientoCancelacionSaldo->haber_usd = 0;
                }
                
                $movimientoCancelacionSaldo->tasa_cambio = $invoice->tasa ?? 1;
                $movimientoCancelacionSaldo->nota = 'Cancelación de saldo a favor: ' . ($invoice->is_usd ? 'USD ' : '$') . number_format($montoAPagar, 2) . 
                                                  ' aplicado a Factura #' . $invoice->invoice_number;
                $movimientoCancelacionSaldo->fue_revertido = 0;
                $movimientoCancelacionSaldo->save();
                
                // 2. Movimiento de pago parcial (HABER para reducir la deuda de la factura)
                $movimientoPagoParcial = new \App\CuentaCorriente();
                $movimientoPagoParcial->payer_payee_id = $clientId;
                $movimientoPagoParcial->comprobable_type = 'App\Transaction';
                $movimientoPagoParcial->comprobable_id = $transaction->id;
                
                // Pago parcial: HABER del monto pagado
                if ($invoice->is_usd) {
                    $movimientoPagoParcial->debe_usd = 0;
                    $movimientoPagoParcial->haber_usd = $montoAPagar;
                    $movimientoPagoParcial->debe_peso = 0;
                    $movimientoPagoParcial->haber_peso = 0;
                } else {
                    $movimientoPagoParcial->debe_peso = 0;
                    $movimientoPagoParcial->haber_peso = $montoAPagar;
                    $movimientoPagoParcial->debe_usd = 0;
                    $movimientoPagoParcial->haber_usd = 0;
                }
                
                $movimientoPagoParcial->tasa_cambio = $invoice->tasa ?? 1;
                
                // Determinar símbolo de moneda
                $simboloMoneda = $invoice->is_usd ? 'USD ' : '$';
                
                $movimientoPagoParcial->nota = 'Pago parcial desde saldo a favor: ' . $simboloMoneda . number_format($montoAPagar, 2) . 
                                             ' aplicado a Factura #' . $invoice->invoice_number;
                $movimientoPagoParcial->fue_revertido = 0;
                
                $movimientoPagoParcial->save();
            }

            // Recalcular saldos después del pago automático
            \App\CuentaCorriente::recalcular($invoice->client_id);

            // Actualizar factura
            $invoice->paid = $invoice->paid + $montoAPagar;

            if (round($invoice->paid, 2) >= $invoice->grand_total) {
                $invoice->status = 'Paid';
            } else if (round($invoice->paid, 2) > 0 && (round($invoice->paid, 2) < $invoice->grand_total)) {
                $invoice->status = 'Partially_Paid';
            }

            $invoice->save();

            DB::commit();

            return [
                'success' => true,
                'message' => 'Pago automático realizado: ' . ($invoice->is_usd ? 'USD ' : '$ ') . number_format($montoAPagar, 2),
                'monto_pagado' => $montoAPagar,
                'saldo_restante' => $invoice->grand_total - $invoice->paid,
                'nuevo_status' => $invoice->status,
                'transaction_id' => $transaction->id
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al pagar factura desde saldo a favor: ' . $e->getMessage(), [
                'invoice_id' => $invoiceId,
                'client_id' => $clientId,
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Error al procesar pago automático: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Mostrar formulario para ingreso manual a cuenta corriente
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function createIngreso(Request $request)
    {
        $id = $request->get('id');

        // Obtener saldo actual del cliente
        $ultimoMovimiento = CuentaCorriente::where('payer_payee_id', $id)
            ->orderBy('created_at', 'desc')
            ->first();

        $saldoPeso = $ultimoMovimiento ? $ultimoMovimiento->saldo_peso : 0;
        $saldoUsd = $ultimoMovimiento ? $ultimoMovimiento->saldo_usd : 0;

        // Obtener rubros de ingreso (chart of accounts)
        $charts = ChartOfAccount::where('type', 'income')
            ->where('company_id', company_id())
            ->orderBy('name', 'asc')
            ->get();

        // Obtener métodos de pago
        $payment_methods = PaymentMethod::where('company_id', company_id())
            ->orderBy('name', 'asc')
            ->get();

        // Obtener cuentas bancarias
        $accounts = \App\Account::where('company_id', company_id())
            ->orderBy('account_title', 'asc')
            ->get();

        $data = [
            'id' => $id,
            'saldo_peso' => $saldoPeso,
            'saldo_usd' => $saldoUsd,
            'charts' => $charts,
            'payment_methods' => $payment_methods,
            'accounts' => $accounts,
        ];

        if (!$request->ajax()) {
            return view('backend.accounting.cuenta_corriente.ingreso.create', $data);
        } else {
            return view('backend.accounting.cuenta_corriente.ingreso.modal.create', $data);
        }
    }

    /**
     * Procesar ingreso manual a cuenta corriente
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeIngreso(Request $request)
    {
        $id = $request->get('id');

        $validator = Validator::make($request->all(), [
            'trans_date' => 'required',
            'account_id' => 'required',
            'chart_id' => 'nullable',
            'amount' => 'required|numeric|min:0.01',
            'payment_method_id' => 'required',
            'usd' => 'nullable|in:0,1',
            'tasa' => 'nullable|required_if:usd,1|numeric|min:0.01',
            'reference' => 'nullable|max:50',
            'attachment' => 'nullable|mimes:jpeg,png,jpg,doc,pdf,docx,zip',
        ], [
            'tasa.required_if' => 'La tasa de cambio es requerida cuando se ingresa en USD.',
            'tasa.numeric' => 'La tasa de cambio debe ser un número.',
            'tasa.min' => 'La tasa de cambio debe ser al menos 0.01.',
            'chart_id.required' => 'El rubro de ingreso es requerido.',
        ]);

        // Buscar automáticamente el rubro "Ingreso" si no se proporciona
        $chart_id = $request->input('chart_id');
        if (!$chart_id) {
            $ingresoChart = ChartOfAccount::where('name', 'like', '%Ingreso%')
                ->where('type', 'income')
                ->where('company_id', company_id())
                ->first();

            if ($ingresoChart) {
                $chart_id = $ingresoChart->id;
            } else {
                // Crear el rubro automáticamente si no existe
                $ingresoChart = ChartOfAccount::create([
                    'name' => 'Ingreso a Cuenta Corriente',
                    'type' => 'income',
                    'company_id' => company_id(),
                ]);
                $chart_id = $ingresoChart->id;
            }

            // Actualizar el request con el chart_id encontrado/creado
            $request->merge(['chart_id' => $chart_id]);
        }

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
            } else {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }
        }

        $attachment = '';
        if ($request->hasfile('attachment')) {
            $file = $request->file('attachment');
            $attachment = time() . $file->getClientOriginalName();
            $file->move(public_path() . "/uploads/transactions/", $attachment);
        }

        DB::beginTransaction();
        try {
            // Buscar método de pago "Abono cc"
            $methodP = PaymentMethod::where('name', 'like', '%Abono cc')->first();

            if (!$methodP) {
                DB::rollBack();
                $errorMessage = 'El método de pago "Abono cc" no está configurado en el sistema.';
                if ($request->ajax()) {
                    return response()->json([
                        'result' => 'error',
                        'message' => [$errorMessage]
                    ]);
                } else {
                    return redirect()->back()
                        ->with('error', $errorMessage)
                        ->withInput();
                }
            }

            $monto = $request->input('amount');
            $esUsd = $request->input('usd') == 1;

            // Crear transacción de ingreso a cuenta corriente
            $transactionIngreso = new Transaction();
            $transactionIngreso->trans_date = $request->input('trans_date');
            $transactionIngreso->chart_id = $chart_id;
            $transactionIngreso->type = 'cc_income';
            $transactionIngreso->dr_cr = 'cc';
            $transactionIngreso->amount = $monto;

            // Asignar montos según moneda
            if ($esUsd) {
                $transactionIngreso->amount_usd = $monto;
                $transactionIngreso->amount_peso = 0;
            } else {
                $transactionIngreso->amount_peso = $monto;
                $transactionIngreso->amount_usd = 0;
            }

            $transactionIngreso->base_amount = $monto;
            $transactionIngreso->payer_payee_id = $id;
            $transactionIngreso->payment_method_id = $methodP->id;
            $transactionIngreso->reference = $request->input('reference');
            $transactionIngreso->razon_social = $request->input('razon_social');
            $transactionIngreso->tipo_comprobante_id = $request->input('tipo_comprobante_id');
            $transactionIngreso->detalle_rubro = $request->input('detalle_rubro');
            $transactionIngreso->banco = $request->input('banco');
            $transactionIngreso->cheque_nro = $request->input('cheque_nro');
            $transactionIngreso->cheque_vencimiento = $request->input('cheque_vencimiento');
            $transactionIngreso->cheque_entregado_a = $request->input('cheque_entregado_a');
            $transactionIngreso->attachment = $attachment;
            $transactionIngreso->note = 'Ingreso manual a cuenta corriente: ' . ($esUsd ? 'USD ' . $monto : '$ ' . $monto);
            $transactionIngreso->usd = $request->input('usd', 0);
            $transactionIngreso->tasa = $esUsd ? $request->input('tasa', 1) : 1;
            $transactionIngreso->status = 1;
            $transactionIngreso->company_id = company_id();

            $transactionIngreso->save();

            // Crear transacción de ingreso real (dinero que entra a la cuenta bancaria)
            Transaction::withoutEvents(function () use ($request, $chart_id, $monto, $esUsd, $id, $attachment, $transactionIngreso, &$transaction) {
                $transaction = new Transaction();
                $transaction->trans_date = $request->input('trans_date');
                $transaction->account_id = $request->input('account_id');
                $transaction->chart_id = $chart_id;
                $transaction->type = 'income';
                $transaction->dr_cr = 'cr';
                $transaction->amount = $monto;

                // Asignar montos según moneda
                if ($esUsd) {
                    $transaction->amount_usd = $monto;
                    $transaction->amount_peso = 0;
                } else {
                    $transaction->amount_peso = $monto;
                    $transaction->amount_usd = 0;
                }

                // Obtener la cuenta primero para poder acceder a account_currency
                $account = \App\Account::find($request->input('account_id'));
                $transaction->base_amount = convert_currency($account ? $account->account_currency : 'ARS', base_currency(), $transaction->amount);
                $transaction->payer_payee_id = $id;
                $transaction->payment_method_id = $request->input('payment_method_id');
                $transaction->reference = $request->input('reference');
                $transaction->razon_social = $request->input('razon_social');
                $transaction->tipo_comprobante_id = $request->input('tipo_comprobante_id');
                $transaction->detalle_rubro = $request->input('detalle_rubro');
                $transaction->banco = $request->input('banco');
                $transaction->cheque_nro = $request->input('cheque_nro');
                $transaction->cheque_vencimiento = $request->input('cheque_vencimiento');
                $transaction->cheque_entregado_a = $request->input('cheque_entregado_a');
                $transaction->attachment = $attachment;
                $transaction->note = 'Ingreso real a cuenta bancaria desde cuenta corriente: ' . ($esUsd ? 'USD ' . $monto : '$ ' . $monto);
                $transaction->usd = $request->input('usd', 0);
                $transaction->tasa = $esUsd ? $request->input('tasa', 1) : 1;
                $transaction->trans_asoc = $transactionIngreso->id;
                $transaction->company_id = company_id();

                $transaction->save();
            });

            DB::commit();

            if (!$request->ajax()) {
                return redirect()->route('cuenta_corriente.show', $id)
                    ->with('success', _lang('Ingreso manual procesado exitosamente'));
            } else {
                return response()->json([
                    'result' => 'success',
                    'action' => 'store',
                    'message' => _lang('Ingreso manual procesado exitosamente. Se ingresó ' . ($esUsd ? 'USD ' . $monto : '$ ' . $monto) . ' a la cuenta corriente.'),
                    'data' => $transaction
                ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al procesar ingreso manual: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'result' => 'error',
                    'message' => ['Error al procesar el ingreso manual: ' . $e->getMessage()]
                ]);
            } else {
                return redirect()->back()
                    ->with('error', _lang('Error al procesar el ingreso manual: ' . $e->getMessage()))
                    ->withInput();
            }
        }
    }
}
