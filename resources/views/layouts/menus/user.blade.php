@include('layouts.menus.menu_user')

@if( has_feature( 'contacts_limit' ) )
<li>
	<a href="javascript: void(0);"><i class="ti-id-badge"></i><span>{{ _lang('Customers') }}</span><span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span></a>
	<ul class="nav-second-level" aria-expanded="false">
		<li class="nav-item"><a class="nav-link" href="{{ url('contacts') }}">{{ _lang('Contacts List') }}</a></li>
		<li class="nav-item"><a class="nav-link" href="{{ url('contacts/create') }}">{{ _lang('Add New') }}</a></li>
		<li class="nav-item"><a class="nav-link" href="{{ url('contact_groups') }}">{{ _lang('Contact Group') }}</a></li>
	</ul>
</li>
@endif

@if( has_feature('project_management_module') )
<li>
	<a href="{{ route('leads.index') }}"><i class="fas fa-tty"></i><span>{{ _lang('Leads') }}</span></a>
</li>

<li>
	<a href="{{ route('projects.index') }}"><i class="ti-briefcase"></i><span>{{ _lang('Projects') }}</span></a>
</li>

<li>
	<a href="{{ route('tasks.index') }}"><i class="ti-check-box"></i><span>{{ _lang('Tasks') }}</span></a>
</li>
@endif

<li>
	<a href="javascript: void(0);"><i class="ti-id-badge"></i><span>{{ _lang('Vehiculo') }}</span><span
				class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span></a>
	<ul class="nav-second-level" aria-expanded="false">
		<!--<li class="nav-item"> <a class="nav-link" href="{{ route('list_estado_vehiculo') }}"><i class="ti-link"> </i>{{ _lang('Estado y Seguimiento') }}</a></li>-->
		<li class="nav-item"><a class="nav-link" href="{{ route('list_consu_orden') }}"><i class="ti-link"> </i>{{ _lang('Consulta Ordenes de desarme')
		}}</a></li>
		<li class="nav-item"><a href="{{ route('vehiculo.index') }}"><i class="ti-car"></i><span>{{ _lang('Vehiculos')
		}}</span></a></li>
		<li class="nav-item"><a href="{{ route('buscador_de_piezas') }}"><i class="ti-briefcase"></i><span>{{ _lang
		('Buscador de piezas')
		}}</span></a></li>
		
		@canany(['crear-marca', 'editar-marca', 'eliminar-marca','ver-marca'])
		<li class="nav-item"><a class="nav-link" href="{{ route('marcas.index') }}"><i class="nav-icon bi bi-chevron-double-right" style="line-height: 1;"></i>{{ _lang('Marcas')
		}}</a></li>
		@endcanany
		@canany(['crear-modelo', 'editar-modelo', 'eliminar-modelo'])
		<li class="nav-item"><a class="nav-link" href="{{ route('modelos.index') }}"><i class="nav-icon bi bi-chevron-double-right" style="line-height: 1;"></i>{{ _lang('Modelos')
		}}</a></li>
		@endcanany
		{{--<li class="nav-item"><a class="nav-link" href="{{ url('estado') }}">{{ _lang('Estados') }}</a></li>--}}
		<li class="nav-item"><a class="nav-link" href="{{ url('aseguradora') }}">{{ _lang('Aseguradoras') }}</a></li>

		<li class="nav-item"><a class="nav-link" href="{{ url('orden-desarme') }}">{{ _lang('Ordenes de desarme')
		}}</a></li>

		<li class="nav-item"><a class="nav-link" href="{{ url('provincia') }}">{{ _lang('Provincia') }}</a></li>

	</ul>
</li>
<li class="nav-item"><a class="nav-link" href="{{ url('lugarentrega') }}">{{ _lang('Depositos')
		}}</a></li>
<li>
	<a href="javascript: void(0);"><i class="ti-shopping-cart"></i><span>{{ _lang('Products') }}</span><span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span></a>
	<ul class="nav-second-level" aria-expanded="false">
			<li class="nav-item"><a class="nav-link"
                            href="{{ route('products.create') }}?predefinido=1">{{ _lang('Precarga masiva') }}</a>
                    </li>
		<li class="nav-item"><a class="nav-link" href="{{ url('products/create') }}">{{ _lang('Add New') }}</a></li>
		<li class="nav-item"><a class="nav-link" href="{{ url('products') }}">{{ _lang('Product List') }}</a></li>
		<li class="nav-item"><a class="nav-link" href="{{ url('products/historial') }}">{{ _lang('Historial') }}</a></li>

		<li class="nav-item"><a class="nav-link" href="{{ url('products/comunes') }}">{{ _lang('Productos predefinidos') }}</a>

		<li class="nav-item"><a class="nav-link" href="{{ url('categorias') }}">{{ _lang('Categorias') }}</a></li>
		 <li class="nav-item"><a class="nav-link"
                            href="{{ url('products/anulados') }}">{{ _lang('Anulados') }}</a>
                    </li>
	</ul>
</li>

<li>
	<a href="javascript: void(0);"><i class="ti-agenda"></i><span>{{ _lang('Service') }}</span><span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span></a>
	<ul class="nav-second-level" aria-expanded="false">
		<li class="nav-item"><a class="nav-link" href="{{ url('services/create') }}">{{ _lang('Add New') }}</a></li>
		<li class="nav-item"><a class="nav-link" href="{{ url('services') }}">{{ _lang('Service List') }}</a></li>
	</ul>
</li>

<li>
	<a href="javascript: void(0);"><i class="ti-truck"></i><span>{{ _lang('Supplier') }}</span><span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span></a>
	<ul class="nav-second-level" aria-expanded="false">
		<li class="nav-item"><a class="nav-link" href="{{ url('suppliers/create') }}">{{ _lang('Add New') }}</a></li>
		<li class="nav-item"><a class="nav-link" href="{{ url('suppliers') }}">{{ _lang('Supplier List') }}</a></li>
	</ul>
</li>

@if( has_feature('inventory_module') )
<li>
	<a href="javascript: void(0);"><i class="ti-bag"></i><span>{{ _lang('Purchase') }}</span><span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span></a>
	<ul class="nav-second-level" aria-expanded="false">
		<li class="nav-item"><a class="nav-link" href="{{ url('purchase_orders') }}">{{ _lang('Purchase Orders') }}</a></li>
		<li class="nav-item"><a class="nav-link" href="{{ url('purchase_orders/create') }}">{{ _lang('Create Purchase Order') }}</a></li>
	</ul>
</li>

<li>
	<a href="javascript: void(0);"><i class="ti-back-left"></i><span>{{ _lang('Return') }}</span><span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span></a>
	<ul class="nav-second-level" aria-expanded="false">
		<li class="nav-item"><a class="nav-link" href="{{ url('purchase_returns') }}">{{ _lang('Purchase Return') }}</a></li>
		<li class="nav-item"><a class="nav-link" href="{{ url('sales_returns') }}">{{ _lang('Sales Return') }}</a></li>
	</ul>
</li>
@endif


<li>
	<a href="javascript: void(0);"><i class="ti-shopping-cart-full"></i><span>{{ _lang('Sales') }}</span><span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span></a>
	<ul class="nav-second-level" aria-expanded="false">
		@if( has_feature('invoice_limit') )
			<!--<li class="nav-item"><a class="nav-link" href="{{ url('invoices/create') }}">{{ _lang('Crear venta') }}</a></li>-->
			<li class="nav-item"><a class="nav-link"href="{{ route('invoices.list_comision') }}">{{ _lang('Comisiones') }}</a></li>
			<li class="nav-item"><a href="{{ route('buscador_de_piezas') }}"><span>{{ _lang
				('Buscador de piezas')
				}}</span></a></li>
			<li class="nav-item"><a class="nav-link" href="{{ url('invoices') }}">{{ _lang('Invoice List') }}</a></li>
			<li class="nav-item"><a class="nav-link" href="{{ url('invoices/pendiente-facturar') }}">{{ _lang
			('Pendiente por facturar')
			}}</a></li>
		@endif

		@if( has_feature('quotation_limit') )
			{{-- <li class="nav-item"><a class="nav-link" href="{{ url('reservas/create') }}">{{ _lang('Add Quotation') }}</a></li> --}}
			<li class="nav-item"><a class="nav-link" href="{{ url('reservas') }}">{{ _lang('Quotation List') }}</a></li>
		@endif
	</ul>
</li>

<li>
	<a href="javascript: void(0);"><i class="ti-credit-card"></i><span>{{ _lang('Accounts') }}</span><span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span></a>
	<ul class="nav-second-level" aria-expanded="false">
		<li class="nav-item"><a class="nav-link" href="{{ url('accounts') }}">{{ _lang('List Account') }}</a></li>
		<li class="nav-item"><a class="nav-link" href="{{ url('accounts/create') }}">{{ _lang('Add New Account') }}</a></li>
	</ul>
</li>


<li>
	<a href="javascript: void(0);"><i class="ti-receipt"></i><span>{{ _lang('Transactions') }}</span><span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span></a>
	<ul class="nav-second-level" aria-expanded="false">
		<li class="nav-item"><a class="nav-link" href="{{ url('income') }}">{{ _lang('Income/Deposit') }}</a></li>
		<li class="nav-item"><a class="nav-link" href="{{ url('expense') }}">{{ _lang('Expense') }}</a></li>
		{{--<li class="nav-item"><a class="nav-link" href="{{ url('caja diaria') }}">{{ _lang('Caja_diaria') }}</a></li>--}}
		<li class="nav-item"><a class="nav-link" href="{{ url('transfer/create') }}">{{ _lang('Transfer') }}</a></li>
		<li class="nav-item"><a class="nav-link" href="{{ url('income/calendar') }}">{{ _lang('Income Calendar') }}</a></li>
		<li class="nav-item"><a class="nav-link" href="{{ url('expense/calendar') }}">{{ _lang('Expense Calendar') }}</a></li>
	</ul>
</li>

@if( has_feature('recurring_transaction') )
<li>
	<a href="javascript: void(0);"><i class="ti-wallet"></i><span>{{ _lang('Recurring Transaction') }}</span><span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span></a>
	<ul class="nav-second-level" aria-expanded="false">
		<li class="nav-item"><a class="nav-link" href="{{ url('repeating_income/create') }}">{{ _lang('Add Repeating Income') }}</a></li>
		<li class="nav-item"><a class="nav-link" href="{{ url('repeating_income') }}">{{ _lang('Repeating Income List') }}</a></li>
		<li class="nav-item"><a class="nav-link" href="{{ url('repeating_expense/create') }}">{{ _lang('Add Repeating Expense') }}</a></li>
		<li class="nav-item"><a class="nav-link" href="{{ url('repeating_expense') }}">{{ _lang('Repeating Expense List') }}</a></li>
	</ul>
</li>
@endif

@if(get_option('live_chat') == 'enabled' && has_feature('live_chat') )
	<li>
       <a href="{{ url('live_chat') }}"><i class="far fa-comment"></i><span>{{ _lang('Messenger') }}</span><span class="chat-notification {{ unread_message_count() > 0 ? 'show' : 'hidden' }}">{{ unread_message_count() }}</span></a>
	</li>
@endif

@if( has_feature('file_manager') )
<li>
	<a href="{{ url('file_manager') }}"><i class="ti-folder"></i><span>{{ _lang('File Manager') }}</span></a>
</li>
@endif

@if( has_feature('staff_limit') )
<li>
	<a href="javascript: void(0);"><i class="ti-user"></i><span>{{ _lang('Staffs') }}</span><span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span></a>
	<ul class="nav-second-level" aria-expanded="false">
		<li class="nav-item"><a class="nav-link" href="{{ url('staffs') }}">{{ _lang('All Staff') }}</a></li>
		<li class="nav-item"><a class="nav-link" href="{{ url('staffs/create') }}">{{ _lang('Add New') }}</a></li>
		<li class="nav-item"><a class="nav-link" href="{{ route('roles.index') }}">{{ _lang('Staff Roles') }}</a></li>
	</ul>
</li>
@endif

<li>
	<a href="javascript: void(0);"><i class="ti-bar-chart"></i><span>{{ _lang('Reports') }}</span><span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span></a>
	<ul class="nav-second-level" aria-expanded="false">
		<li class="nav-item"><a class="nav-link" href="{{ url('reports/account_statement') }}">{{ _lang('Account Statement') }}</a></li>
		<li class="nav-item"><a class="nav-link" href="{{ route('report_by_day') }}">{{ _lang('Reporte por dia personalizado') }}</a></li>
		<li class="nav-item"><a class="nav-link" href="{{ url('reports/day_wise_income') }}">{{ _lang('Detail Income Report') }}</a></li>
		<li class="nav-item"><a class="nav-link" href="{{ url('reports/date_wise_income') }}">{{ _lang('Date Wise Income') }}</a></li>
		<li class="nav-item"><a class="nav-link" href="{{ url('reports/day_wise_expense') }}">{{ _lang('Detail Expense Report') }}</a></li>
		<li class="nav-item"><a class="nav-link" href="{{ url('reports/date_wise_expense') }}">{{ _lang('Date Wise Expense') }}</a></li>
		<li class="nav-item"><a class="nav-link" href="{{ url('reports/tax_report') }}">{{ _lang('Tax Reports') }}</a></li>
		<li class="nav-item"><a class="nav-link" href="{{ url('reports/transfer_report') }}">{{ _lang('Transfer Report') }}</a></li>
		<li class="nav-item"><a class="nav-link" href="{{ url('reports/income_vs_expense') }}">{{ _lang('Income VS Expense') }}</a></li>
		<li class="nav-item"><a class="nav-link" href="{{ url('reports/report_by_payer') }}">{{ _lang('Report By Payer') }}</a></li>
		<li class="nav-item"><a class="nav-link" href="{{ url('reports/report_by_payee') }}">{{ _lang('Report By Payee') }}</a></li>
	</ul>
</li>

<li>
	<a href="javascript: void(0);"><i class="ti-settings"></i><span>{{ _lang('Settings') }}</span><span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span></a>
	<ul class="nav-second-level" aria-expanded="false">
		<li class="nav-item"><a class="nav-link" href="{{ url('company/general_settings') }}">{{ _lang('Company Settings') }}</a></li>
		<li class="nav-item"><a class="nav-link" href="{{ url('company_email_template') }}">{{ _lang('Email Template') }}</a></li>
		<li class="nav-item"><a class="nav-link" href="{{ url('permission/control') }}">{{ _lang('Access Control') }}</a></li>
		<li class="nav-item"><a class="nav-link" href="{{ url('chart_of_accounts') }}">{{ _lang('Income & Expense Types') }}</a></li>
		@if( has_feature('project_management_module') )
			<li class="nav-item"><a class="nav-link" href="{{ url('company/crm_settings') }}">{{ _lang('CRM Settings') }}</a></li>
		@endif
		<li class="nav-item"><a class="nav-link" href="{{ url('payment_methods') }}">{{ _lang('Payment Methods') }}</a></li>
		<li class="nav-item"><a class="nav-link" href="{{ url('product_units') }}">{{ _lang('Product Unit') }}</a></li>
		<li class="nav-item"><a class="nav-link" href="{{ url('taxs') }}">{{ _lang('Tax Settings') }}</a></li>
		<li class="nav-item"><a class="nav-link" href="{{ url('tipocomprobante') }}">Tipos de comprobantes</a></li>
	</ul>
</li>
@canany(['pi_consulta_venta','piezas_destruir.index'])
<li> {{-- PROCESOS INTERNOS --}}
    <a href="javascript: void(0);"><i class="ti-id-badge"></i><span>{{ _lang('PROCESOS INTERNOS') }}</span><span
            class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span></a>
    <ul class="nav-second-level" aria-expanded="false">
		<li> {{-- REPORTES --}}
			<a href="javascript: void(0);"><i class="ti-bar-chart"></i><span>{{ _lang('Reportes') }}</span><span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span></a>
			@can('pi_consulta_venta')
				<li class="nav-item"><a class="nav-link" href="{{ route('invoice.consulta_ventas') }}">{{ _lang('Consulta Venta') }}</a></li>
		   @endcan
		   @can('piezas_destruir.index') 
				<li class="nav-item">
                    <a class="nav-link" href="{{ route('piezas_destruir.index') }}">{{ _lang('Piezas a Destruir') }}</a>
				</li>
			@endcan	
			@can('compactacion.index') 
				<li class="nav-item">
					<a class="nav-link" href="{{ route('compactacion.index') }}">{{ _lang('Vehículos Compactados') }}</a>
				</li>
			@endcan	
		</li>
    </ul>
</li>
@endcanany