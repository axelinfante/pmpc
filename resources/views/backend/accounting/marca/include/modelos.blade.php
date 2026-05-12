<table>
    @foreach($modelos as $modelo)
        <tr>
            <td><input {{ $modelo->value ? 'checked' : null }} data-id="{{ $modelo->id }}" type="checkbox" class="ingredient-enable"></td>
            <td>{{ $modelo->name }}</td>
            <td><input value="{{ $modelo->value ?? null }}" {{ $modelo->value ? null : 'disabled' }} data-id="{{ $modelo->id }}" name="ingredients[{{ $modelo->id }}]" type="text" class="ingredient-amount form-control" placeholder="Amount"></td>
        </tr>
    @endforeach
</table>

@section('scripts')
    @parent
    <script>
        $('document').ready(function () {
            $('.ingredient-enable').on('click', function () {
                let id = $(this).attr('data-id')
                let enabled = $(this).is(":checked")
                $('.ingredient-amount[data-id="' + id + '"]').attr('disabled', !enabled)
                $('.ingredient-amount[data-id="' + id + '"]').val(null)
            })
        });
    </script>
@endsection



  