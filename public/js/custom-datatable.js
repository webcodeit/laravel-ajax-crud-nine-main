$(document).ready(function () {

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $(document).on('submit', '#form-filter-user', function (e) {
        e.preventDefault();
        initDataTable();
    });

    $(document).on('click', '#btn-filter-submit', function (e) {
        e.preventDefault();
        initDataTable();
    });

    $(document).on('click', '#btn-filter-reset', function (e) {
        $('#form-filter-user').trigger('reset');
        initDataTable();
    });

    $(document).on('click', '.page-link:not(.page-item.active .page-link)', function (e) {
        e.preventDefault();
        let rootPath = $(this).attr('href');
        initDataTable(rootPath);
    });

    $(document).on('click', '#data-table-list .sorting', function () {
        let obj = $(this);

        $('#data-table-list .sorting').each(function () {
            if ($(this).data('column-name') !== obj.data('column-name')) {
                $(this).removeClass('sorting_asc');
                $(this).removeClass('sorting_desc');
            }
        });

        var sortColumn = obj.data('column-name');
        var sortType = 'both';

        if (obj.hasClass('sorting_asc')) {
            obj.removeClass('sorting_asc');
            obj.addClass('sorting_desc');
            sortType = 'desc';

        } else if (obj.hasClass('sorting_desc')) {
            obj.removeClass('sorting_desc');
            obj.addClass('sorting_asc');
            sortType = 'asc';

        } else {
            sortType = 'asc';
            obj.addClass('sorting_asc');
        }

        $("input[name='sort_column']").val(sortColumn);
        $("input[name='sort_type']").val(sortType);

        initDataTable();
    });

    $('#load-ajax').on('change', '#per-page', function () {
        initDataTable();
    });
});

function initDataTable(rootPath) {
    $('body .evt_btn_delete_all').prop('disabled', true);
    if (rootPath == undefined) {
        rootPath = ajaxDataTableUrl;
    }
    var formData = $('#form-filter-user').serialize();
    $.ajax({
        type: 'POST',
        data: formData,
        url: rootPath,
        success: function (response) {
            $('#load-ajax').html(response);
            $('#data-table-list').DataTable({
                columnDefs: [
                    { orderable: false, targets: 'no-sort' },
                ],
                'searching': false,
                'paging': false,
                'ordering': false,
                // 'pageLength': 20,
                'resposive': true,
                'info': false,
            });
        },
        error: function (xhr) {
            ajaxErrorMsg(xhr);
        }
    });
}


// checkbox all delete
$(document).on('change', '#evt_select_all_chk_box_delete', function (e) {
    e.preventDefault();
    var checked = $(this).is(':checked');
    if (checked) {
        $('body .evt_single_chk_box_delete').prop('checked', true);
    } else {
        $('body .evt_single_chk_box_delete').prop('checked', false);
    }
    var total_chk_box_checked = $(".evt_single_chk_box_delete:checked").length;

    if (total_chk_box_checked == 0) {
        $('body .evt_btn_delete_all').prop('disabled', true);
    } else {
        $('body .evt_btn_delete_all').prop('disabled', false);
    }
});

$(document).on('change', '.evt_single_chk_box_delete', function (e) {
    e.preventDefault();
    var total_chk_box_checked = $(".evt_single_chk_box_delete:checked").length;
    if (total_chk_box_checked == 0) {
        $('body .evt_btn_delete_all').prop('disabled', true);
    } else {
        $('body .evt_btn_delete_all').prop('disabled', false);
    }
});

// call delete all btn delete

$(document).on('click', '.evt_btn_delete_all', function (e) {
    e.preventDefault();
    const self = $(this);
    const delete_ids = [];
    var total_chk_box_checked = $(".evt_single_chk_box_delete:checked").length;
    if (total_chk_box_checked != 0) {
        $("body .evt_single_chk_box_delete").each(function () {
            if ($(this).is(':visible') && $(this).prop('checked')) {
                delete_ids.push($(this).val());
            }
        });

        if (delete_ids.length > 0 && confirm("Are you sure you want to delete the selected items?")) {
            $.ajax({
                type: 'POST',
                url: self.attr('data-href'),
                dataType: 'json',
                data: {
                    'ids': delete_ids
                },
                success: function (res) {
                    if (res.status == true) {
                        $('body .evt_btn_delete_all').prop('disabled', true);
                        alert(res.message);
                        initDataTable();
                    } else {
                        alert(res.message);
                    }
                },
                error: function (xhr) {
                    ajaxErrorMsg(xhr);
                }
            });
        } else {
            $('body .evt_btn_delete_all').prop('disabled', true);
            $('body .evt_single_chk_box_delete').prop('checked', false);
            $('body #evt_select_all_chk_box_delete').prop('checked', false);
        }

    }
});

/* single delete */

function permanentDeleteRecord(_obj) {
    const self = $(_obj)
   var is_delete =  confirm("Are you sure you want to delete the selected items?")
   if(is_delete) {
        $.ajax({
            type: 'GET',
            url: self.attr('data-href'),
            dataType: 'json',
            success: function (res) {
                if (res.status == true) {
                    // toastr.success(res.message);
                    // self.parents('#row-' + self.attr('data-slug')).remove();
                    alert(res.message);
                    initDataTable();
                } else {
                    alert(res.message);
                }
            },
            error: function (xhr) {
                ajaxErrorMsg(xhr);
            }
        });
    }
}

function ajaxErrorMsg(xhr) {
    if (xhr.status === 422) {
        $.each(xhr.responseJSON.errors, function (key, val) {
           alert(val);
        });
    } else {
        toastr.error(xhr.statusText);
    }
}