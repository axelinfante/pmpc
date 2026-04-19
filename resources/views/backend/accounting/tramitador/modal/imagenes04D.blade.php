<div class="col-md-12 border rounded">

    <div class="form-group">
        <label for="imagen">Fotos 04D </label>
        <input type="file" class="form-control" id="imagen_recepcion"
            name="imagen_recepcion[]" multiple="multiple">
    </div>

    @forelse($car->img_recepcion as $img)
        <div class="card mx-3" style="width: 18rem;">
            <img class="card-img-top img-fluid" src="{{ asset('public/uploads/vehiculos/' . $img->img) }}"
                alt="Card image cap">
            <div class="card-body">
                {{-- <h5 class="card-title">Card title</h5> --}}
                {{-- <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p> --}}
                {{-- <a href="#" class="btn btn-primary">Go somewhere</a> --}}
            </div>

            <div class="card-footer">
                <div class="form-check">
                    <input {{-- @if (!$receptor) {{ $option }} @endif --}} type="checkbox" class="form-check-input"
                        name="imgDeleteRecepcion[]" value="{{ $img->id }}">
                    <label class="form-check-label">
                        Eliminar
                    </label>
                </div>
            </div>
        </div>
    @empty
        <p>No hay imágenes disponibles.</p>
    @endforelse
</div>

@isset($save)
    @if($save === true)
    <div class="col-md-12 mt-2">
        <div class="form-group">
            {{-- <button type="reset" class="btn btn-danger">{{ _lang('Reset') }}</button> --}}
            <button id='save04d' class="btn btn-primary">{{ _lang('Save') }}</button>
        </div>
    </div>
    @endif
@endisset

