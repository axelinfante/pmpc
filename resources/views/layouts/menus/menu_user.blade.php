@php 
$usuariosAutorizados = [26,169]; //2 
@endphp
<li>
    <div class=" my-3">
        <div class="col">
            <label class="text-white" for="companySelect">Empresa</label>
            <select {{ !in_array(Auth::user()->id, $usuariosAutorizados ?? []) ? 'disabled' : '' }} id="companySelect" class="form-control">
                {{ list_company() }}
            </select>
        </div>
    </div>
</li>
