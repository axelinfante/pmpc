<?php

use Illuminate\Support\Facades\Route;
use App\Jobs\ActualizarCheckPointsVehiculos;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
 */

Route::group(["middleware" => ["install"]], function () {
    Route::get("/", "WebsiteController@index");
    Route::get("sign_up", "WebsiteController@sign_up");
    Route::get("site/{page}", "WebsiteController@site");
    Route::post("emaiL_subscribed", "WebsiteController@emaiL_subscribed");
    Route::post("contact/send_message", "WebsiteController@send_message");

    Auth::routes(["verify" => true]);

    Route::get("/logout", "\App\Http\Controllers\Auth\LoginController@logout");
    Route::match(
        ["get", "post"],
        "register/client_signup",
        "\App\Http\Controllers\Auth\RegisterController@client_signup",
    );

    Route::group(["middleware" => ["auth", "verified"]], function () {
        Route::get("/dashboard", "DashboardController@index");

        //Profile Controller
        Route::get("profile/edit", "ProfileController@edit");
        Route::post("profile/update", "ProfileController@update")->middleware(
            "demo",
        );
        Route::get(
            "profile/change_password",
            "ProfileController@change_password",
        );
        Route::post(
            "profile/update_password",
            "ProfileController@update_password",
        )->middleware("demo");

        //Membertship Controller
        Route::get(
            "membership/my_subscription",
            "MembershipController@my_subscription",
        ); //View Subscription Details
        Route::get("membership/extend", "MembershipController@extend");

        //Select Payment Gateway
        Route::post("membership/pay", "MembershipController@pay");

        //Payment Gateway PayPal
        Route::get(
            "membership/paypal/{action?}",
            "MembershipController@paypal",
        );

        //Payment Gateway Stripe
        Route::get(
            "membership/stripe_payment/{action}/{payment_id?}",
            "MembershipController@stripe_payment",
        );

        //Payment Gateway RazorPay
        Route::post(
            "membership/razorpay_payment/{payment_id}",
            "MembershipController@razorpay_payment",
        );

        //Paystack Payment Gateway
        Route::get(
            "membership/paystack_payment/{payment_id}/{reference}",
            "MembershipController@paystack_payment",
        );

        /** Admin Only Route **/
        Route::group(["middleware" => ["admin", "demo"]], function () {
            //User Controller
            Route::get("users/type/{user_type}", "UserController@index");
            Route::resource("users", "UserController");

            //Payment Controller
            Route::get(
                "offline_payment/create",
                "PaymentController@create_offline_payment",
            );
            Route::post(
                "offline_payment/store",
                "PaymentController@store_offline_payment",
            );
            Route::get(
                "members/payment_history",
                "PaymentController@payment_history",
            );

            //Email Subscribers
            Route::get(
                "admin/email_subscribers",
                "EmailSubscriberController@index",
            );

            //Feature Controller
            Route::resource("features", "FeatureController");

            //FAQ Controller
            Route::resource("faqs", "FaqController");

            //Package Controller
            Route::resource("packages", "PackageController");

            //Language Controller
            Route::resource("languages", "LanguageController");

            //Utility Controller
            Route::match(
                ["get", "post"],
                "administration/general_settings/{store?}",
                "UtilityController@settings",
            );
            Route::match(
                ["get", "post"],
                "administration/theme_option/{store?}",
                "UtilityController@theme_option",
            );
            Route::post(
                "administration/upload_logo",
                "UtilityController@upload_logo",
            );
            Route::match(
                ["get", "patch"],
                "administration/currency_rates/{id?}",
                "UtilityController@currency_rates",
            );
            Route::get(
                "administration/backup_database",
                "UtilityController@backup_database",
            );
            Route::post(
                "administration/send_test_email",
                "UtilityController@send_test_email",
            )->name("settings.send_test_email");

            //Theme Option
            Route::match(
                ["get", "post"],
                "administration/theme_option/{store?}",
                "UtilityController@theme_option",
            );

            //Email Template
            Route::resource("email_templates", "EmailTemplateController")->only(
                ["index", "show", "edit", "update"],
            );
        });

        Route::group(["middleware" => ["company"]], function () {
            //Contact Group
            Route::resource("contact_groups", "ContactGroupController");
            // die("a");
            //Contact Controller
            Route::match(
                ["get", "post"],
                "contacts/import",
                "ContactController@import",
            )->name("contacts.import");
            Route::get(
                "contacts/get_table_data",
                "ContactController@get_table_data",
            );
            Route::post(
                "contacts/send_email/{id}",
                "ContactController@send_email",
            )->name("contacts.send_email");
            Route::get(
                "contacts/ajustes/{id}",
                "ContactController@ajusteCuenta",
            );
            Route::get(
                "contacts/cotizacionesConSaldo",
                "ContactController@cotizacionesConSaldo",
            );
            Route::resource("contacts", "ContactController");

            //Cuenta Corriente Controller
            Route::get(
                "cuenta_corriente",
                "CuentaCorrienteController@index",
            )->name("cuenta_corriente.index");
            Route::get(
                "cuenta_corriente/contactos",
                "CuentaCorrienteController@getContactos",
            )->name("cuenta_corriente.get_contactos");
            Route::get(
                "cuenta_corriente/{id}",
                "CuentaCorrienteController@show",
            )->name("cuenta_corriente.show");
            Route::get(
                "cuenta_corriente/{id}/movimientos",
                "CuentaCorrienteController@getMovimientos",
            )->name("cuenta_corriente.get_movimientos");
            Route::get(
                "cuenta_corriente/{id}/resumen",
                "CuentaCorrienteController@getResumen",
            )->name("cuenta_corriente.get_resumen");

            // Devolución de saldo a favor
            Route::get(
                "cuenta_corriente/devolucion/create",
                "CuentaCorrienteController@createDevolucion",
            )->name("cuenta_corriente.devolucion.create");
            Route::post(
                "cuenta_corriente/devolucion/store",
                "CuentaCorrienteController@storeDevolucion",
            )->name("cuenta_corriente.devolucion.store");

            // Ingreso manual a cuenta corriente
            Route::get(
                "cuenta_corriente/ingreso/create",
                "CuentaCorrienteController@createIngreso",
            )->name("cuenta_corriente.ingreso.create");
            Route::post(
                "cuenta_corriente/ingreso/store",
                "CuentaCorrienteController@storeIngreso",
            )->name("cuenta_corriente.ingreso.store");

            //Lead Controller
            Route::match(
                ["get", "post"],
                "leads/import",
                "LeadController@import",
            )->name("leads.import");
            Route::match(
                ["get", "post"],
                "leads/convert_to_customer/{id}",
                "LeadController@convert_to_customer",
            )->name("leads.convert_to_customer");
            Route::get(
                "leads/delete_note/{id}",
                "LeadController@delete_note",
            )->name("leads.delete_note");
            Route::post(
                "leads/create_note",
                "LeadController@create_note",
            )->name("leads.create_note");
            Route::get(
                "leads/download_file/{file}",
                "LeadController@download_file",
            )->name("leads.download_file");
            Route::get(
                "leads/delete_file/{id}",
                "LeadController@delete_file",
            )->name("leads.delete_file");
            Route::post(
                "leads/upload_file",
                "LeadController@upload_file",
            )->name("leads.upload_file");
            Route::post(
                "leads/get_table_data",
                "LeadController@get_table_data",
            );
            Route::get(
                "leads/get_logs_data/{lead_id}",
                "LeadController@get_logs_data",
            );
            Route::get(
                "leads/load_more_lead/{lead_status_id}/{last_lead_id}",
                "LeadController@load_more_lead",
            );
            Route::get(
                "leads/update_lead_status/{lead_status_id}/{last_lead_id}",
                "LeadController@update_lead_status",
            );
            Route::get("leads/{view_type?}", "LeadController@index")
                ->where("view_type", "kanban")
                ->name("leads.index");
            Route::resource("leads", "LeadController");

            //Project Controller
            Route::get(
                "projects/delete_project_member/{member_id}",
                "ProjectController@delete_project_member",
            )->name("projects.delete_project_member");
            Route::get(
                "projects/delete_note/{id}",
                "ProjectController@delete_note",
            )->name("projects.delete_note");
            Route::post(
                "projects/create_note",
                "ProjectController@create_note",
            )->name("projects.create_note");
            Route::get(
                "projects/download_file/{file}",
                "ProjectController@download_file",
            )->name("projects.download_file");
            Route::get(
                "projects/delete_file/{id}",
                "ProjectController@delete_file",
            )->name("projects.delete_file");
            Route::post(
                "projects/upload_file",
                "ProjectController@upload_file",
            )->name("projects.upload_file");
            Route::get(
                "projects/get_logs_data/{id}",
                "ProjectController@get_logs_data",
            );
            Route::post(
                "projects/get_table_data",
                "ProjectController@get_table_data",
            );
            Route::resource("projects", "ProjectController");

            //Vehiculo Controller

            Route::get("vehiculo/importxlsx", "VehiculoController@importXls");
            Route::get(
                "vehiculo/importxlsCars",
                "VehiculoController@importxlsCars",
            );
            Route::get(
                "vehiculo/importxlsCarsEstados",
                "VehiculoController@importxlsCarsEstados",
            );
            Route::get(
                "vehiculo/importxlsCarsIfNotExist",
                "VehiculoController@importxlsCarsIfNotExist",
            );
            Route::get(
                "vehiculo/updateTransactionCompanyByCar",
                "VehiculoController@updateTransactionCompanyByCar",
            )->name("updateTransactionCompanyByCar");

            Route::get(
                "vehiculo/change-company/{id?}",
                "VehiculoController@changeCompany",
            )->name("changeCompany");
            Route::get(
                "vehiculo/get-company/{idCar}",
                "VehiculoController@companyByCar",
            )->name("companyByCar");
            Route::get(
                "vehiculo/seguimiento/{id}",
                "VehiculoController@seguimiento",
            )->name("seguimiento");
            Route::get(
                "vehiculo/certificado/{id}",
                "VehiculoController@certificado",
            )->name("certificado");
            Route::post(
                "vehiculo/certificado",
                "VehiculoController@generateCertificatePdf",
            )->name("certificado");

            Route::get(
                "vehiculo/movimiento/{id}",
                "VehiculoController@movimiento",
            )->name("movimiento");
            Route::get(
                "vehiculo/movimientos/{id}",
                "VehiculoController@movimientos",
            )->name("movimientos");
            Route::post(
                "vehiculo/movimiento",
                "VehiculoController@storeMovimiento",
            )->name("storeMovimiento");
            Route::post(
                "vehiculo/movimiento/{id}",
                "VehiculoController@updateMovimiento",
            )->name("updateMovimiento");
            Route::post(
                "vehiculo/storeSeguimiento",
                "VehiculoController@storeSeguimiento",
            )->name("storeSeguimiento");
            //            Route::get('vehiculo/getVideo/{video}', 'VehiculoController@getVideo')->name('carVideo');

            Route::get(
                "vehiculo/expense_get_table_data",
                "VehiculoController@expense_get_table_data",
            )->name("vehiculo.expense_get_table_data");
            Route::get(
                "vehiculo/edit_expense/{id}",
                "VehiculoController@edit_expense",
            )->name("vehiculo.edit_expense");
            Route::post(
                "vehiculo/update_expense/{id}",
                "VehiculoController@updateExpense",
            )->name("vehiculo.update_expense");

            Route::get(
                "historial-estados",
                "VehiculoController@historialEstados",
            )->name("vehiculo.historial_estados");
            Route::get(
                "vehiculo/list-historial",
                "VehiculoController@historial",
            )->name("vehiculo.historial");
            Route::get(
                "vehiculo/historial",
                "VehiculoController@vistaHistorial",
            )->name("vehiculo.vistaHistorial");

            Route::post(
                "get_estados_fecha_table_data",
                "VehiculoController@get_estados_fecha_table_data",
            );

            Route::resource("vehiculo", "VehiculoController");
            Route::post(
                "vehiculo/get_table_data",
                "VehiculoController@get_table_data",
            );

            // orden de desarme

            Route::get(
                "orden-desarme/crear/{id}/{tipo}",
                "OrdenDesarmeController@crear",
            );
            Route::get(
                "orden-desarme/lista-operarios",
                "OrdenDesarmeController@index_operario",
            )->name("list_operarios");
            Route::get(
                "orden-desarme/historial",
                "OrdenDesarmeController@historial",
            )->name("historialOrden");
            Route::resource("orden-desarme", "OrdenDesarmeController");
            Route::post(
                "orden-desarme/get_table_data",
                "OrdenDesarmeController@get_table_data",
            );
            Route::post(
                "orden-desarme/get_table_data_nb",
                "OrdenDesarmeController@get_table_data_nb",
            );
            Route::get(
                "ordendesarme/consultaOrden",
                "OrdenDesarmeController@consultaOrden",
            )->name("list_consu_orden");
            Route::post(
                "ordendesarme/update-puesto",
                "OrdenDesarmeController@updatePuesto",
            )->name("update-puesto");

            //marca modelo
            Route::get(
                "marcamodelo/modelo/{idMarca?}",
                "MarcaModeloController@modelosAjax",
            )->name("modelosByMarca");
            Route::get(
                "marcamodelo/import",
                "MarcaModeloController@importMarcaModelo",
            );
            Route::get(
                "marcamodelo/edit-modelo/{id}",
                "MarcaModeloController@editModelo",
            )->name("editModelo");
            Route::post(
                "marcamodelo/updatedModelo",
                "MarcaModeloController@updatedModelo",
            )->name("updatedModelo");

            Route::resource("marcamodelo", "MarcaModeloController");
            //Aseguradora
            Route::resource("aseguradora", "AseguradoraController");
            //Estado
            Route::resource("estado", "EstadoController");
            //Provincias
            Route::resource("provincia", "ProvinciaController");
            //Lugar de entregas
            Route::resource("lugarentrega", "LugarEntregaController");

            //Project Milestone
            Route::get(
                "project_milestones/get_milestones/{project_id}",
                "ProjectMilestoneController@get_milestones",
            );
            Route::resource(
                "project_milestones",
                "ProjectMilestoneController",
            )->except(["index"]);

            //TimeSheet Controller
            Route::resource("timesheets", "TimeSheetController")->except([
                "index",
            ]);

            //Tasks Controller
            Route::post(
                "tasks/get_table_data",
                "TaskController@get_table_data",
            );
            Route::get(
                "tasks/load_more_task/{status_id}/{task_id}",
                "TaskController@load_more_task",
            );
            Route::get(
                "tasks/update_task_status/{status_id}/{task_id}",
                "TaskController@update_task_status",
            );
            Route::get("tasks/{view_type?}", "TaskController@index")
                ->where("view_type", "kanban")
                ->name("tasks.index");
            Route::resource("tasks", "TaskController");

            //Account Controller
            Route::resource("accounts", "AccountController");

            //Income Controller
            Route::get(
                "income/get_table_data",
                "IncomeController@get_table_data",
            );
            Route::get("income/calendar", "IncomeController@calendar")->name(
                "income.income_calendar",
            );
            Route::get(
                "income/cuenta_corriente",
                "IncomeController@createCuentaCorriente",
            )->name("income.cuenta_corriente");
            Route::post(
                "income/cuenta_corriente_agregar",
                "IncomeController@storeCuentaCorriente",
            )->name("income.storeCuentaCorriente");
            Route::post(
                "income/agregar_ajuste",
                "IncomeController@agregarAjuste",
            )->name("income.agregar_ajuste");

            Route::resource("income", "IncomeController");

            //Expense Controller
            Route::get(
                "expense/get_table_data",
                "ExpenseController@get_table_data",
            );
            Route::get(
                "expense/cuenta_corriente",
                "ExpenseController@createCuentaCorriente",
            )->name("expense.cuenta_corriente");
            Route::post(
                "expense/cuenta_corriente_agregar",
                "ExpenseController@storeCuentaCorriente",
            )->name("expense.storeCuentaCorriente");
            Route::get("caja_diaria", "ExpenseController@caja_diaria")->name(
                "caja_diaria",
            );
            Route::get(
                "expense/get_caja_table_data",
                "ExpenseController@get_caja_table_data",
            );
            Route::get("expense/calendar", "ExpenseController@calendar")->name(
                "expense.expense_calendar",
            );
            Route::resource("expense", "ExpenseController");

            //Transfer Controller
            Route::get("transfer/create", "TransferController@create")->name(
                "transfer.create",
            );
            Route::post("transfer/store", "TransferController@store")->name(
                "transfer.store",
            );

            //Repeating Income
            Route::get(
                "repeating_income/get_table_data",
                "RepeatingIncomeController@get_table_data",
            );
            Route::resource("repeating_income", "RepeatingIncomeController");

            //Repeating Expense
            Route::get(
                "repeating_expense/get_table_data",
                "RepeatingExpenseController@get_table_data",
            );
            Route::resource("repeating_expense", "RepeatingExpenseController");

            //Chart Of Accounts
            Route::resource("chart_of_accounts", "ChartOfAccountController");

            //Payment Method
            Route::resource("payment_methods", "PaymentMethodController");

            //Supplier Controller
            Route::resource("suppliers", "SupplierController");

            //Product Controller
            Route::get("products/historial", "ProductController@historial");
            Route::get(
                "products/history/{id}",
                "ProductController@historyProduct",
            );
            Route::get(
                "products/historial/{id}",
                "ProductController@historialProducto",
            )->name("historialProducto");

            Route::get("products/item/{id}", "ProductController@getItem");
            Route::get(
                "products/get_product/{id}",
                "ProductController@get_product",
            );
            Route::get(
                "products/get-company/{id}",
                "ProductController@companyByProduct",
            )->name("companyByProduct");
            Route::delete(
                "products/eliminar-comunes/{id}",
                "ProductController@destroy_comunes",
            )->name("product.destroy_comunes");
            Route::get(
                "products/comunes",
                "ProductController@productos_comunes",
            )->name("productos_comunes");
            Route::Patch(
                "products/update_item/{id}",
                "ProductController@update_item",
            )->name("update_item");
            Route::get(
                "products/editar/{id}",
                "ProductController@edit_item",
            )->name("edit_item");

            Route::get(
                "products/cambiar-estado/{id}/{estado}",
                "ProductController@cambiarEstado",
            )->name("cambiarEstado");
            Route::get(
                "products/actualizarProductAutos",
                "ProductController@actualizarProductAutos",
            )->name("actualizarProductAutos");
            Route::get("productos/impotarxls", "ProductController@importXls");
            Route::match(
                ["get", "post"],
                "products/import",
                "ProductController@import",
            )->name("products.import");

            Route::get(
                "products/carga-rapida",
                "ProductController@cargaRapida",
            );

            //productos anulados
            Route::get("products/anulados", "ProductController@anulados");
            Route::post(
                "products/toggle-stock",
                "ProductController@toggleStock",
            )->name("toggleStock");

            Route::resource("products", "ProductController");

            //Product Controller
            Route::match(
                ["get", "post"],
                "services/import",
                "ServiceController@import",
            )->name("services.import");
            Route::resource("services", "ServiceController");

            //category controller
            Route::resource("categorias", "CategoryController");

            //Purchase Order
            Route::get(
                "purchase_orders/create_payment/{id}",
                "PurchaseController@create_payment",
            )->name("purchase_orders.create_payment");
            Route::post(
                "purchase_orders/store_payment",
                "PurchaseController@store_payment",
            )->name("purchase_orders.create_payment");
            Route::get(
                "purchase_orders/view_payment/{id}",
                "PurchaseController@view_payment",
            )->name("purchase_orders.view_payment");
            Route::get(
                "purchase_orders/download_pdf/{id}",
                "PurchaseController@download_pdf",
            )->name("purchase_orders.download_pdf");
            Route::post(
                "purchase_orders/get_table_data",
                "PurchaseController@get_table_data",
            );
            Route::resource("purchase_orders", "PurchaseController");

            //Purchase Return
            Route::resource("purchase_returns", "PurchaseReturnController");

            //Sales Return
            Route::resource("sales_returns", "SalesReturnController");

            //Invoice Controller
            Route::get(
                "invoices/create_payment/{id}",
                "InvoiceController@create_payment",
            )->name("invoices.create_payment");
            Route::get(
                "invoices/buscador_de_piezas",
                "InvoiceController@buscador_de_piezas",
            )->name("buscador_de_piezas");
            Route::get(
                "invoices/comisiones",
                "InvoiceController@list_comision",
            )->name("invoices.list_comision");

            Route::post(
                "invoices/comisiones/table",
                "InvoiceController@table_comision",
            )->name("invoices.table_comision");

            Route::get(
                "invoices/create_comision/{id}",
                "InvoiceController@create_comision",
            )->name("invoices.create_comision");
            Route::get(
                "invoices/create_observaciones/{id}",
                "InvoiceController@create_observaciones",
            )->name("invoices.create_observaciones");
            Route::post(
                "invoices/store_comision",
                "InvoiceController@store_comision",
            )->name("invoices.store_comision");
            Route::post(
                "invoices/store_observaciones",
                "InvoiceController@store_observaciones",
            )->name("invoices.store_observaciones");
            Route::post(
                "invoices/store_comisiones_multiples",
                "InvoiceController@store_comisiones_multiples",
            )->name("invoices.store_comisiones_multiples");

            Route::post(
                "invoices/store_payment",
                "InvoiceController@store_payment",
            )->name("invoices.create_payment");
            Route::get(
                "invoices/mark_as_cancelled/{id}",
                "InvoiceController@mark_as_cancelled",
            )->name("invoices.mark_as_cancelled");
            Route::get(
                "invoices/pendiente-facturar",
                "InvoiceController@ventasPorFacturar",
            )->name("invoices.ventasPorFacturar");
            Route::get(
                "invoices/view_payment/{id}",
                "InvoiceController@view_payment",
            )->name("invoices.view_payment");
            Route::get(
                "invoices/create_email/{invoice_id}",
                "InvoiceController@create_email",
            )->name("invoices.send_email");
            Route::post(
                "invoices/send_email",
                "InvoiceController@send_email",
            )->name("invoices.send_email");
            Route::post(
                "invoices/get_table_data",
                "InvoiceController@get_table_data",
            );
            Route::post(
                "invoices/get_table_autos_buscador",
                "InvoiceController@get_table_autos_buscador",
            );

            Route::post(
                "invoices/get_table_piezas_buscador",
                "InvoiceController@get_table_piezas_buscador",
            );

            Route::resource("invoices", "InvoiceController");

            Route::get(
                "invoices/get_items/{id}",
                "InvoiceController@get_list_item",
            );

            //Quotation Controller

            Route::get(
                "reservas/create_payment/{id}",
                "QuotationController@create_payment",
            )->name("quotation.create_payment");
            Route::post(
                "reservas/store_payment",
                "QuotationController@store_payment",
            )->name("quotation.create_payment");
            Route::get(
                "reservas/convert_invoice/{quotation_id}",
                "QuotationController@convert_invoice",
            )->name("quotations.convert_invoice");
            Route::get(
                "reservas/create_email/{quotation_id}",
                "QuotationController@create_email",
            )->name("quotations.send_email");
            Route::post(
                "reservas/send_email",
                "QuotationController@send_email",
            )->name("quotations.send_email");
            Route::get(
                "reservas/get_table_data",
                "QuotationController@get_table_data",
            );
			Route::name('reservas.pdf')->get('reservas/pdf/{id}', 'QuotationController@pdf');

            Route::resource("reservas", "QuotationController");

            //Staff Controller
            Route::resource("staffs", "StaffController");

            //User Roles
            Route::resource("roles", "RoleController");

            //File Manager Controller
            Route::get(
                "file_manager/directory/{parent_id}",
                "FileManagerController@index",
            )->name("file_manager.index");
            Route::get(
                "file_manager/create_folder/{parent_id?}",
                "FileManagerController@create_folder",
            )->name("file_manager.create_folder");
            Route::post(
                "file_manager/store_folder",
                "FileManagerController@store_folder",
            )->name("file_manager.create_folder");
            Route::get(
                "file_manager/edit_folder/{id}",
                "FileManagerController@edit_folder",
            )->name("file_manager.edit_folder");
            Route::patch(
                "file_manager/update_folder/{id}",
                "FileManagerController@update_folder",
            )->name("file_manager.edit_folder");
            Route::get(
                "file_manager/create/{parent_id?}",
                "FileManagerController@create",
            )->name("file_manager.create");
            Route::get(
                "file_manager/create-multiple/{parent_id?}",
                "FileManagerController@createMultiple",
            )->name("file_manager.create-multiple");

            Route::resource("file_manager", "FileManagerController");

            //Company Settings Controller
            Route::post(
                "company/upload_logo",
                "CompanySettingsController@upload_logo",
            )->name("company.change_logo");
            Route::match(
                ["get", "post"],
                "company/general_settings/{store?}",
                "CompanySettingsController@settings",
            )->name("company.change_settings");

            Route::match(
                ["get", "post"],
                "company/crm_settings",
                "CompanySettingsController@crm_settings",
            )->name("company.crm_settings");

            //Lead Status Controller
            Route::get(
                "lead_statuses/update_lead_status_order/{lead_status_id}/{order}",
                "LeadStatusController@update_lead_status_order",
            );
            Route::resource("lead_statuses", "LeadStatusController")->except([
                "index",
            ]);

            //Lead Source Controller
            Route::resource("lead_sources", "LeadSourceController")->except([
                "index",
            ]);

            //Task Status Controller
            Route::get(
                "task_statuses/update_task_status_order/{task_status_id}/{order}",
                "TaskStatusController@update_task_status_order",
            );
            Route::resource("task_statuses", "TaskStatusController")->except([
                "index",
            ]);

            //Company Email Template
            Route::get(
                "company_email_template/get_template/{id}",
                "CompanyEmailTemplateController@get_template",
            );
            Route::resource(
                "company_email_template",
                "CompanyEmailTemplateController",
            );

            //Tax Controller
            Route::resource("taxs", "TaxController");

            //Product Unit Controller
            Route::resource("product_units", "ProductUnitController");

            //Permission Controller
            Route::get(
                "permission/control/{user_id?}",
                "PermissionController@index",
            )->name("permission.manage");
            Route::post("permission/store", "PermissionController@store")->name(
                "permission.manage",
            );

            //Report Controller
            Route::match(
                ["get", "post"],
                "reports/account_statement/{view?}",
                "ReportController@account_statement",
            )->name("reports.account_statement");
            Route::match(
                ["get", "post"],
                "reports/day_wise_income/{view?}",
                "ReportController@day_wise_income",
            )->name("reports.day_wise_income");
            Route::match(
                ["get", "post"],
                "reports/date_wise_income/{view?}",
                "ReportController@date_wise_income",
            )->name("reports.date_wise_income");
            Route::match(
                ["get", "post"],
                "reports/day_wise_expense/{view?}",
                "ReportController@day_wise_expense",
            )->name("reports.day_wise_expense");
            Route::match(
                ["get", "post"],
                "reports/date_wise_expense/{view?}",
                "ReportController@date_wise_expense",
            )->name("reports.date_wise_expense");
            Route::match(
                ["get", "post"],
                "reports/transfer_report/{view?}",
                "ReportController@transfer_report",
            )->name("reports.transfer_report");
            Route::match(
                ["get", "post"],
                "reports/income_vs_expense/{view?}",
                "ReportController@income_vs_expense",
            )->name("reports.income_vs_expense");
            Route::match(
                ["get", "post"],
                "reports/report_by_payer/{view?}",
                "ReportController@report_by_payer",
            )->name("reports.report_by_payer");
            Route::match(
                ["get", "post"],
                "reports/report_by_payee/{view?}",
                "ReportController@report_by_payee",
            )->name("reports.report_by_payee");
            Route::match(
                ["get", "post"],
                "reports/tax_report/{view?}",
                "ReportController@tax_report",
            )->name("reports.tax_report");

            //Route::match(['get', 'post'], 'reports/report_by_day', 'ReportController@report_by_day')->name('report_by_day');
            Route::match(
                ["get", "post"],
                "reports/report_by_day",
                "ReportController@report_by_day_new",
            )->name("report_by_day");
            //Route::match(['get', 'post'], 'reports/report_by_day_new', 'ReportController@report_by_day_new')->name('report_by_day_new');

            //Tipos de comprobante
            Route::get(
                "tipocomprobante/get_table_data",
                "TipoComprobanteController@get_table_data",
            );
            Route::resource("tipocomprobante", "TipoComprobanteController");

            //Devolucion de Produtos
            Route::resource("products_returns", "ProductsReturnController");
            Route::post(
                "products_returns/get_table_data",
                "ProductsReturnController@get_table_data",
            );
            Route::post(
                "products-returns-process",
                "ProductsReturnController@process",
            )->name("products_returns.procesar");
            Route::post(
                "products-returns-cancel",
                "ProductsReturnController@cancel",
            )->name("products_returns.cancel");

            //Tramitadores

            Route::post(
                "tramitador/get_table_data",
                "TramitadorController@get_table_data",
            );
            Route::get(
                "tramitadores/seguimiento/{id}",
                "TramitadorController@seguimiento",
            )->name("tramitadores.seguimiento");
            Route::get(
                "checkpoints/get_table_data/{tramite?}",
                "CheckpointController@get_table_data",
            );

            Route::post(
                "tramitadores/store-checkpoint",
                "TramitadorController@storeCheckPoint",
            )->name("tramitadores.store-checkpoint");

            Route::post(
                "tramitadores/checkpoints_vehiculos/get_table_data",
                "TramitadorController@checkpointVehiculosGetTableData",
            )->name("checkpoints_vehiculos.get_table_data");

            Route::get(
                "tramitadores/checkpoints_vehiculos/get_tramite/{vehiculo_id}/{checkpoint_id}",
                "TramitadorController@checkpointVehiculosGetTramite",
            )->name("checkpoints_vehiculos.get_tramite");

            //Route::resource('tramitadores', 'TramitadorController');

            Route::get(
                "tramitadores/show04D{id}",
                "TramitadorController@show04D",
            )->name("tramitadores.show04D");

            Route::post(
                "tramitadores/titular",
                "TramitadorController@setTitular",
            )->name("tramitadores.set.titular");
            Route::get(
                "tramitadores/titular/{id}",
                "TramitadorController@getTitular",
            )->name("tramitadores.titular");

            Route::get(
                "tramitadores/create",
                "TramitadorController@create",
            )->name("tramitadores.create");
            Route::post(
                "tramitadores/guardar-caso",
                "TramitadorController@guardarCaso",
            )->name("tramitadores.guardar");

            Route::get(
                "tramitadores/edit-caso",
                "TramitadorController@editCaso",
            )->name("tramitadores.edit");
            Route::post(
                "tramitadores/update-caso",
                "TramitadorController@updateCaso",
            )->name("tramitadores.update.caso");

            Route::post(
                "tramitadores/update04D",
                "TramitadorController@update04D",
            )->name("tramitadores.update04D");

            Route::get("tramitadores", "TramitadorController@index")->name(
                "tramitadores.index",
            );
            Route::post("tramitadores", "TramitadorController@update")->name(
                "tramitadores.update",
            );

            Route::resource("checkpoints", "CheckpointController");

            Route::resource("orden-despacho", "OrdenDespachoController");
            Route::post(
                "orden-despacho/get_table_data",
                "OrdenDespachoController@get_table_data",
            );
            Route::patch(
                "orden-despacho/{id}",
                "OrdenDespachoController@update",
            )->name("orden-despacho.update");
        });

        Route::group(["middleware" => ["client"]], function () {
            //Invoice
            Route::get(
                "client/invoices/{status?}",
                "ClientController@invoices",
            );

            //Quotation
            Route::get("client/quotations", "ClientController@quotations");

            //Projects
            Route::get("client/projects", "ClientController@projects");
            Route::get("client/projects/{id}", "ClientController@view_project");
            Route::get(
                "client/projects/delete_note/{id}",
                "ClientController@delete_note",
            );
            Route::post(
                "client/projects/create_note",
                "ClientController@create_note",
            );
            Route::get(
                "client/projects/download_file/{file}",
                "ClientController@download_file",
            );
            Route::get(
                "client/projects/delete_file/{id}",
                "ClientController@delete_file",
            );
            Route::post(
                "client/projects/upload_file",
                "ClientController@upload_file",
            );

            //Transaction
            Route::get("client/transactions", "ClientController@transactions");
            Route::get(
                "client/view_transaction/{id}",
                "ClientController@view_transaction",
            );

            //Select Business
            Route::match(
                ["get", "post"],
                "client/select_business",
                "ClientController@select_business",
            );
        });

        //Chat Controller
        Route::get("live_chat", "ChatController@index");
        Route::post("live_chat/auth", "ChatController@auth");
        Route::post("live_chat/send_message", "ChatController@send_message");
        Route::get(
            "live_chat/get_messages/{user_id}/{limit?}/{offset?}",
            "ChatController@get_messages",
        );
        Route::post(
            "live_chat/mark_as_read/{sender_id}",
            "ChatController@mark_as_read",
        );
        Route::get(
            "live_chat/notification_count",
            "ChatController@notification_count",
        );

        //Group Chat
        Route::get("live_chat/create_group", "ChatController@create_group");
        Route::post("live_chat/store_group", "ChatController@store_group");
        Route::get("live_chat/edit_group/{id}", "ChatController@edit_group");
        Route::post(
            "live_chat/update_group/{group_id}",
            "ChatController@update_group",
        );
        Route::get(
            "live_chat/view_group_members/{id}",
            "ChatController@view_group_members",
        );
        Route::post(
            "live_chat/send_group_message",
            "ChatController@send_group_message",
        );
        Route::get(
            "live_chat/get_group_messages/{group_id}/{limit?}/{offset?}",
            "ChatController@get_group_messages",
        );
        Route::post(
            "live_chat/mark_as_group_read/{group_id}",
            "ChatController@mark_as_group_read",
        );
        Route::get(
            "live_chat/delete_group/{group_id}",
            "ChatController@delete_group",
        );
        Route::get(
            "live_chat/left_group/{group_id}",
            "ChatController@left_group",
        );

        // aqui print QR
        Route::get("product/print-qr/{id}", "ProductController@printQR")->name(
            "print-qr",
        );
        // aqui print sin QR
        Route::get(
            "product/printsin-qr/{id}",
            "ProductController@printsinQR",
        )->name("printsin-qr");
    });

    //Convert Currency
    Route::get(
        "convert_currency/{from}/{to}/{amount}",
        "AccountController@convert_currency",
    );

    //Get Client Info
    Route::get(
        "contacts/get_client_info/{id}",
        "ContactController@get_client_info",
    );

    //Get Client Info
    Route::get("leads/get_lead_info/{id}", "LeadController@get_lead_info");

    //Get Client Info
    Route::get(
        "projects/get_project_info/{id}",
        "ProjectController@get_project_info",
    );

    //View Invoice & Quotation without login
    Route::get("client/view_invoice/{id}", "ClientController@view_invoice");
    Route::get("client/view_quotation/{id}", "ClientController@view_quotation");

    //Online Invoice Payment
    Route::get(
        "client/invoice_payment/{id}/{payment_method}",
        "ClientController@invoice_payment",
    );

    //Stripe Payment Gateway
    Route::get(
        "client/stripe_payment/{action}/{invoice_id}",
        "ClientController@stripe_payment",
    );

    //PayPal Payment Gateway
    Route::get(
        "client/paypal/{action?}/{invoice_id?}",
        "ClientController@paypal",
    );

    //Payment Gateway RazorPay
    Route::post(
        "client/razorpay_payment/{invoice_id}",
        "ClientController@razorpay_payment",
    );

    //Paystack Payment Gateway
    Route::get(
        "client/paystack_payment/{invoice_id}/{reference}",
        "ClientController@paystack_payment",
    );

    //Invoice & Quotation PDF Download
    Route::get(
        "invoices/download_pdf/{id}",
        "ClientController@download_invoice_pdf",
    );
    Route::get(
        "quotations/download_pdf/{id}",
        "ClientController@download_quotation_pdf",
    );

    Route::post("invoices/export/excel", "InvoiceController@exportExcel")->name(
        "invoices.export.excel",
    );
    Route::post("invoices/export/pdf", "InvoiceController@exportPDF")->name(
        "invoices.export.pdf",
    );

    Route::post(
        "vehiculos/export/excel",
        "VehiculoController@exportExcel",
    )->name("vehiculos.export.excel");
    Route::post("vehiculos/export/pdf", "VehiculoController@exportPDF")->name(
        "vehiculos.export.pdf",
    );

    Route::post(
        "tramitadores/export/excel",
        "VehiculoController@exportExcel",
    )->name("tramitadores.export.excel");
    Route::post(
        "tramitadores/export/pdf",
        "VehiculoController@exportPDF",
    )->name("tramitadores.export.pdf");

    Route::post(
        "vehiculo-expense/export/excel",
        "VehiculoController@expenseExportExcel",
    )->name("vehiculo-expense.export.excel");
    Route::post(
        "vehiculo-expense/export/pdf",
        "VehiculoController@expenseExportPdf",
    )->name("vehiculo-expense.export.pdf");

    Route::post(
        "piezas/export/excel",
        "InvoiceController@piezasExportExcel",
    )->name("piezas.export.excel");

    Route::get(
        "orden-desarme/generar-pdf/{ids}",
        "OrdenDesarmeController@generateOrdenPdfLote",
    )->name("orden-desarme.generar-pdf");
	
	Route::get(
        "orden-desarme/generar-pdf-one/{ids}",
        "OrdenDesarmeController@generateOrdenPdf",
    )->name("orden-desarme-one.generar-pdf");
});

Route::get("/installation", "Install\InstallController@index");
Route::get("install/database", "Install\InstallController@database");
Route::post(
    "install/process_install",
    "Install\InstallController@process_install",
);
Route::get("install/create_user", "Install\InstallController@create_user");
Route::post("install/store_user", "Install\InstallController@store_user");
Route::get(
    "install/system_settings",
    "Install\InstallController@system_settings",
);
Route::post("install/finish", "Install\InstallController@final_touch");

//Ajax Select2 Controller
Route::get("ajax/get_table_data", "Select2Controller@get_table_data");

//Show Notification
Route::get("notification/{id}", "NotificationController@show")->middleware(
    "auth",
);

//JSON data for dashboard chart
Route::get(
    "dashboard/json_month_wise_income_expense",
    "DashboardController@json_month_wise_income_expense",
)->middleware("auth");
Route::get(
    "dashboard/json_income_vs_expense",
    "DashboardController@json_income_vs_expense",
)->middleware("auth");

//Google Login
Route::get("google/redirect", "Auth\SocialAuthGoogleController@redirect");
Route::get("google/callback", "Auth\SocialAuthGoogleController@callback");

//Update System
Route::get("migration/update", "Install\UpdateController@update_migration");

//PayPal IPN for Membership Payment
Route::post("membership/paypal_ipn", "MembershipController@paypal_ipn");

//PayPal IPN for Invoice Payment
Route::post("client/paypal_ipn", "ClientController@paypal_ipn");

Route::get("console/run", "CronJobsController@run");

//videos vehiculos
Route::get("vehiculo/getVideo/{video}", "VehiculoController@getVideo")->name(
    "carVideo",
);
Route::get(
    "vehiculo/getMarcaModeloByCar/{id}",
    "VehiculoController@getMarcaModeloByCar",
)->name("getMarcaModeloByCar");
Route::post(
    "vehiculo/updateEstado/{id}",
    "VehiculoController@updateEstado",
)->name("updateEstado");
Route::post(
    "vehiculo/updateUbicacion/{id}",
    "VehiculoController@updateUbicacion",
)->name("updateUbicacion");
Route::get(
    "vehiculo/verifica-pieza/{itemId}/{carId}",
    "VehiculoController@verificaPiezaByCar",
)->name("verificaPiezaByCar");
Route::get(
    "vehiculo/utilizadas-pieza/{nro_interno}",
    "VehiculoController@seleccionadoPiezaByCar",
)->name("seleccionadoPiezaByCar");

Route::get(
    "orden-desarme/changeProcesar/{id}/{procesar?}",
    "OrdenDesarmeController@changeProcesar",
)->name("changeProcesar");

Route::get("/tramitadores/run-jobs", function () {
    ActualizarCheckPointsVehiculos::dispatch();
    return "Job dispatched!";
});

Route::get(
    "vehiculo/veh_imag_zip/{id}/{tipo?}",
    "VehiculoController@veh_imag_zip",
)->name("veh_imag_zip");
Route::get(
    "product/pro_imag_zip/{id}/{tipo?}",
    "ProductController@pro_imag_zip",
)->name("pro_imag_zip");
Route::get(
    "vehiculos/estado_seguimiento",
    "VehiculoController@estado_seguimiento",
)->name("list_estado_vehiculo");

Route::post(
    "invoices/comisiones_multiples",
    "InvoiceController@comisiones_multiples",
)->name("invoices.comisiones_multiples");
Route::post(
    "invoices/comisiones_anulados",
    "InvoiceController@comisiones_anulados",
)->name("invoices.comisiones_anulados");

Route::get(
    "/orden-despacho/entrega/pdf/{id}",
    "OrdenDespachoController@generarPDF",
)->name("orden-despacho.entrega.pdf");
Route::post(
    "/orden-despacho/confirmar-entrega",
    "OrdenDespachoController@confirmarEntrega",
)->name("orden-despacho.confirmar-entrega");

Route::resource("activityLog", ActivityLogController::class)->only("index");

// reportes procesos internos

Route::get("consulta-ventas", "InvoiceController@ConsultaVentas")->name(
    "invoice.consulta_ventas",
);
Route::get("/consulta-getventas", "InvoiceController@getConsultaVentas")->name(
    "invoice.consulta_getventas",
);

Route::get("products/productos-lote/{ids}", "ProductController@productosLote");
//Route::post('productos-lote', [App\Http\Controllers\ProductController::class, 'productosLote']);

Route::post(
    "products/actualiza-stockitems",
    "ProductController@actualizaStockitems",
)->name("actualizaStockitems");
Route::resource("item", "ItemController");

Route::resource("puestos", "PuestosController");
//Route::resource('products', ProductController::class);

			Route::post('contacts/movimiento_saldo', 'ContactController@movimiento_saldo')->name('contacts.movimiento_saldo');
			Route::post('contacts/mov_devolucion_saldo', 'ContactController@mov_devolucion_saldo')->name('contacts.mov_devolucion_saldo');
			Route::post('contacts/mov_resumen_saldo', 'ContactController@mov_resumen_saldo')->name('contacts.mov_resumen_saldo');
			
			Route::get('contacts/create_payment/{id}', 'ContactController@create_payment')->name('contacts.create_payment');
			Route::post('contacts/store_payment', 'ContactController@store_payment')->name('contacts.create_payment');
			Route::get('contacts/view_payment/{id}', 'ContactController@view_payment')->name('contacts.view_payment');
			
			
			Route::get(
                "products/list-auditoria/{id}",
                "ProductController@auditoriaProducto",
            )->name("products.auditoriaProducto");
			Route::get(
                "products/auditoria/{id}",
                "ProductController@auditoriaHistorial",
            )->name("auditoriaHistorial");

		   Route::post('products/detalle', 'ProductController@table_detalle')->name('products.table_detalle');
		   Route::post('products/detalle-post', 'ProductController@table_detalle_post')->name('table.detalle.post');
   	       Route::post("orden-despacho/confirmaciones","OrdenDespachoController@confirmacionesMAX")->name("orden-despacho.confirmaciones");
		   