<div class="row" id="content-checkpoint-list">
    <div class="col-12">

        <div class="card mt-2">
            <span class="panel-title d-none">{{ _lang('Checkpoints') }}</span>


            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label">{{ _lang('Checkpoint') }} </label>
                            <input type="text" class="form-control" id="checkpoint" name="checkpoint" value=""
                                required>

                        </div>
                    </div>
                    <div class="col-md-4 pt-4">
                        <a id="add_to_list" href="" class="btn btn-info btn-xs"><i class="ti-plus"></i></a>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <table id="checkpoint_list_table" class="display table-bordered data-table" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Inc.</th>
                                    <th>{{ _lang('Id') }}</th>
                                    <th>{{ _lang('Nombre') }}</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
    (function($) {
        "use strict";

        let vehiculo_id = $('#vehiculo_id').val();

        checkpoint_list_table = $('#checkpoint_list_table').DataTable({
            processing: true,
            serverSide: true,
            searching: false,
            ajax: _url + '/checkpoints/get_table_data' + '/' + vehiculo_id,
            "columns": [{
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        if (row.marca != '') {
                            return '<input type="checkbox" checked class="row-select-checkbox marca_checkpoint" data-id="' +
                                data.id + '" data-vehiculo_id="' + vehiculo_id + '">';
                        } else {
                            return '<input type="checkbox" class="row-select-checkbox marca_checkpoint" data-id="' +
                                data.id + '" data-vehiculo_id="' + vehiculo_id + '">';
                        }

                    }
                },
                {
                    data: "id",
                    name: "id"
                },
                {
                    data: "nombre",
                    name: "nombre"
                },

            ],
            select: {
                style: 'multi'
            },
            rowCallback: function(row, data) {
                $(row).on('click', '.row-select-checkbox', function() {
                    $(this).closest('tr').toggleClass('selected');
                });
            },
            responsive: true,
            select: {
                style: 'multi'
            },
            bStateSave: true,
            bAutoWidth: false,
            ordering: false,
            "language": {
                "decimal": "",
                "emptyTable": $lang_no_data_found,
                "info": $lang_showing + " _START_ " + $lang_to + " _END_ " + $lang_of +
                    " _TOTAL_ " +
                    $lang_entries,
                "infoEmpty": $lang_showing_0_to_0_of_0_entries,
                "infoFiltered": "(filtered from _MAX_ total entries)",
                "infoPostFix": "",
                "thousands": ",",
                "lengthMenu": $lang_show + " _MENU_ " + $lang_entries,
                "loadingRecords": $lang_loading,
                "processing": $lang_processing,
                "search": $lang_search,
                "zeroRecords": $lang_no_matching_records_found,
                "paginate": {
                    "first": $lang_first,
                    "last": $lang_last,
                    "next": $lang_next,
                    "previous": $lang_previous
                }
            }
        });

        $('#checkpoint_list_table tbody').on('click', 'input[type="checkbox"]', function() {
            var $row = $(this).closest('tr');

            if (this.checked) {
                $row.addClass('selected');
            } else {
                $row.removeClass('selected');
            }
        });

      
      
       
    })(jQuery);
</script>
