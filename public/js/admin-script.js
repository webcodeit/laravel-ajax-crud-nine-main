$(document).ready(function () {

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $('.color_mode').click(function (e) {
        e.preventDefault();
        var mipo_theme_bgcolor = $("html").hasClass("dark-mode") ? 'no' : 'yes';
        sessionStorage.setItem("mipo_theme_bgcolor", mipo_theme_bgcolor);
    });

    if (sessionStorage.getItem("mipo_theme_bgcolor") == 'yes') {
        $("html").addClass("dark-mode");
    }

    $('#web-logout').click(function (e) {
        e.preventDefault();
        $('#web-logout-form').submit();
    });

    // if ($(".select2").length) {
    //     $(".select2").select2({
    //         placeholder: "Select"
    //     });
    // }

    if ($(".dataTable").length) {
        $(".dataTable").DataTable({
            "responsive": true,
            "autoWidth": false,
            columnDefs: [
                {
                    orderable: false,
                    targets: "no-sort"
                },
            ],
        });
    }

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
            Swal.fire({
                title: ays_en_msg,
                text: ays_delete_prm_file_en_msg,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#13153B',
                confirmButtonText: yes_delete_en_msg,
                cancelButtonText: cancel_en_msg
            }).then((result) => {
                if (result.isConfirmed) {
                    // second
                    Swal.fire({
                        title: ays_en_msg,
                        text: ays_delete_prm_file_en_msg,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#13153B',
                        confirmButtonText: yes_delete_en_msg,
                        cancelButtonText: cancel_en_msg,
                    }).then((result) => {
                        if (result.isConfirmed) {
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
                                        toastr.success(res.message);
                                        initDataTable();
                                        // self.parents('#row-' + self.attr('data-slug')).remove();
                                        Swal.fire(
                                            'Deleted!',
                                            'Your record has been deleted.',
                                            'success'
                                        )
                                    } else {
                                        toastr.error(res.message);
                                    }
                                },
                                error: function (xhr) {
                                    unsetLoadin();
                                    ajaxErrorMsg(xhr);
                                }
                            });
                        } else {
                            $('body .evt_btn_delete_all').prop('disabled', true);
                            $('body .evt_single_chk_box_delete').prop('checked', false);
                            $('body #evt_select_all_chk_box_delete').prop('checked', false);
                        }
                    });
                    // second
                } else {
                    $('body .evt_btn_delete_all').prop('disabled', true);
                    $('body .evt_single_chk_box_delete').prop('checked', false);
                    $('body #evt_select_all_chk_box_delete').prop('checked', false);
                }
            });
        }
    });

    // if ($(".date-input-group").length) {
    //     $.fn.datepicker.defaults.format = "yyyy-mm-dd";
    //     $.fn.datepicker.defaults.autoClose = true;
    // }

    console.log(route_name);
    var not_allowed_date_range = ['register', 'login', 'verify.otp', 'password.request', 'user.plans', 'details.user', 'dashboard'];
    if (!not_allowed_date_range.includes(route_name)) {
        $('input[name="duration_date_range"]').daterangepicker({
            opens: 'left',
            locale: {
                format: 'DD/MM/YYYY'
            },
        }, function (start, end, label) {
            console.log("A new date selection was made: " + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD'));
        });
    }

    $('input[name="currency_type"]').change(function (e) {
        e.preventDefault();
        var self = $(this);
        var radio_btn_val = self.val()
        if (radio_btn_val == USD) {
            $('input[name="min"]').parents().removeClass('gs-icon');
            $('input[name="max"]').parents().removeClass('gs-icon');
        } else {
            $('input[name="min"]').parents().addClass('gs-icon');
            $('input[name="max"]').parents().addClass('gs-icon');
        }
    });

    /* $('.evt_date_single').daterangepicker({
        singleDatePicker: true,
        showDropdowns: true,
        locale: {
                format: 'DD/MM/YYYY'
        },
    }, function(start, end, label) {
        console.log("A new date selection was made: " + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD'));
    }); */


    /*  $('input[name="issuance_date"]').change(function (e) { 
        e.preventDefault();
        var self = $(this);
        console.log('issuance_date', self.val());
        // var format3 = moment($('input[name="issuance_date"]').val()).format('DD/MM/YYYY');
        // console.log('format3', format3);
        // this.setAttribute("data-date",moment(this.value, "YYYY-MM-DD").format(this.getAttribute("data-date-format")));
    }).trigger("change"); */

    var allowed_table = ['profile.index', 'operations.index', 'offered-operations.index'];
    if (allowed_table.includes(route_name)) {
        $('.data_table').DataTable({
            responsive: true
        });
    }
});

function debounce(func, wait, immediate) {
    var timeout;
    return function () {
        const context = this, args = arguments;
        let later = () => {
            timeout = null;
            if (!immediate)
                func.apply(context, args);
        };
        const callNow = immediate && !timeout;
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
        if (callNow) func.apply(context, args);
    };
}

function dotToArray(str) {
    let output = '';
    let chunks = str.split('.');
    if (chunks.length > 1) {
        for (i = 0; i < chunks.length; i++) {
            if (i == 0) {
                output = chunks[i];
            } else {
                output += '[' + chunks[i] + ']';
            }
        }
    } else {
        output = chunks[0];
    }
    return output;
}

function capitalizeFirstLetter(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}

function permanentDeleteRecord(_obj) {
    const self = $(_obj)
    Swal.fire({
        title: ays_en_msg,
        text: ays_delete_prm_file_en_msg,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#13153B',
        confirmButtonText: yes_delete_en_msg,
        cancelButtonText: cancel_en_msg
    }).then((result) => {
        if (result.isConfirmed) {
            // second
            Swal.fire({
                title: ays_en_msg,
                text: ays_delete_prm_file_en_msg,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#13153B',
                confirmButtonText: yes_delete_en_msg,
                cancelButtonText: cancel_en_msg,
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: 'POST',
                        url: self.attr('data-href'),
                        dataType: 'json',
                        success: function (res) {
                            if (res.status == true) {
                                toastr.success(res.message);
                                self.parents('#row-' + self.attr('data-slug')).remove();
                                Swal.fire(
                                    'Deleted!',
                                    'Your record has been deleted.',
                                    'success'
                                )
                            } else {
                                toastr.error(res.message);
                            }
                        },
                        error: function (xhr) {
                            unsetLoadin();
                            ajaxErrorMsg(xhr);
                        }
                    });

                }
            });
            // second
        }
    });
}

function deleteRecord(_obj, callback = () => { }) {
    const self = $(_obj)
    var action_name = self.data('name');
    Swal.fire({
        title: ays_en_msg,
        text: "Are you sure, you want to " + action_name + " this?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#13153B',
        confirmButtonText: 'Yes, ' + action_name + ' it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                type: 'POST',
                url: self.data('href'),
                dataType: 'json',
                success: function (res) {
                    if (res.status == true) {
                        self.removeClass();
                        callback();
                        toastr.success(res.message);
                        self.data('name', res.data.type);
                        self.text(capitalizeFirstLetter(res.data.type));
                        if (res.data.type == 'delete') {
                            self.addClass('text-white btn btn-sm btn-warning');
                        } else if (res.data.type == 'restore') {
                            self.addClass('text-white btn btn-sm btn-success');
                        }
                        /*   Swal.fire(
                              action_name + '!',
                              'Successfully ' + action_name + 'd.',
                              // 'Your record has been ' + action_name,
                              'success'
                          ) */
                    } else {
                        toastr.error(res.message);
                    }
                },
                error: function (xhr) {
                    unsetLoadin();
                    ajaxErrorMsg(xhr);
                }
            });
        }
    });
}


function setLoadin() {
    const player = document.querySelector("lottie-player");
    player.load(loader_path);
    $('.loader-div').show();
    // $.blockUI({
    // 	html: `<lottie-player autoplay loop mode="normal" style="width: 160px;">
    // 	</lottie-player>`,
    // })
}

function unsetLoadin() {
    // const player = document.querySelector("lottie-player");
    // player.complete(loader_path);
    $('.loader-div').hide();
    // $.unblockUI();
}

function ajaxErrorMsg(xhr) {
    if (xhr.status === 422) {
        $.each(xhr.responseJSON.errors, function (key, val) {
            toastr.error(val);
        });
    } else {
        toastr.error(xhr.statusText);
    }
}

$(document).on('click', '.evt_download_pdf_btn', function (e) {
    var self = $(this);
    var file_name = self.attr('data-file-name');
    var url_link = self.attr('data-href');
    if (url_link != '') {
        window.location.href = self.attr('data-href');
    }
    // ajax_pdf(self.attr('data-href'), 'GET', form_data = null, file_name);
});

function ajax_pdf(route_url, type, form_data, file_name) {
    setLoadin();
    $.ajax({
        type: type,
        url: route_url,
        data: form_data,
        cache: false,
        xhrFields: {
            responseType: 'blob'
        },
        success: function (response) {
            unsetLoadin();
            var blob = new Blob([response]);
            var link = document.createElement('a');
            link.href = window.URL.createObjectURL(blob);
            link.download = file_name + '.pdf';
            link.click();
        },
        error: function (xhr) {
            ajaxErrorMsg(xhr);
        }
    });
}

$(document).on('click', '.download_export_btn', function (e) {
    var self = $(this);
    setLoadin();
    $.ajax({
        type: 'GET',
        url: self.attr('data-href'),
        dataType: 'json',
        cache: false,
        success: function (res) {
            unsetLoadin();
            if (res.status == true) {
                toastr.success(res.message);
                console.log(res.data.documents);
                console.log(res.data.supporting_attachments);
                if (res.data.documents) {
                    for (let index in res.data.documents) {
                        fileDownload(res.data.documents[index], 'document');
                    }
                }
                if (res.data.supporting_attachments) {
                    for (let index in res.data.supporting_attachments) {
                        fileDownload(res.data.supporting_attachments[index], 'supporting_attachment');
                    }
                }
            } else {
                toastr.error(res.message);
            }
        },
        error: function (xhr) {
            ajaxErrorMsg(xhr);
        }
    });
});

function fileDownload(file_url, file_name = 'file') {
    var link = document.createElement("a");
    link.download = file_name + '_' + randomString();
    link.href = file_url;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function randomString(length = '10') {
    var result = '';
    var characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    var charactersLength = characters.length;
    for (var i = 0; i < length; i++) {
        result += characters.charAt(Math.floor(Math.random() * charactersLength));
    }
    return result;
}

$(document).on('keypress', '.evt_validate_decimal', function (event) {
    var char_code = event.which ? event.which : event.keyCode;
    var number = event.target.value.split(".");
    if (char_code != 46 && char_code > 58 && (char_code < 48 || char_code > 57)) {
        return false;
    }
    return true
});

$(document).on('keydown', '.evt_validate_decimal__', function (event) {
    setTimeout(() => {
        const formatter = new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
        });

        var current_val = $(this).val();
        var fomate_val = formatter.format(current_val);
        var new_val = fomate_val.replace("$", "");
        $(this).val(new_val);

        /* $('.evt_validate_decimal').each(function(){
            var current_val = $(this).val();
                if(current_val!='') {
                    console.log('current_val', current_val);

                    var fomate_val = formatter.format(current_val);
                    var new_val = fomate_val.replace("$", "");
                    console.log('fomate_val', new_val);
                    $(this).val(new_val);
                }
            }); */
    }, 3000);
});

$(document).on('click', '.evt_ex_operations_details', function (e) {
    e.preventDefault();
    var details_link = $(this).attr('data-operations-details-link');
    window.location.href = details_link;
});

$(document).on('click', '.evt_ex_seller_details', function (e) {
    e.preventDefault();
    var details_link = $(this).attr('data-seller-details-link');
    window.location.href = details_link;
});

$(document).on('click', '.evt_ex_issuer_details', function (e) {
    e.preventDefault();
    var details_link = $(this).attr('data-issuer-details-link');
    window.location.href = details_link;
});



$(document).ready(function () {
    var load_data = $('#myTabProfile li a.active').html()
    $('.filter_top_wraps .mobile_select span').html(load_data);
});
$('body').addClass('loaded');



$('.filter_top_wraps .mobile_select').click(function (e) {
    e.preventDefault();
    $(this).parent().toggleClass('active_btn');
    // var icon = '<i class="fa fa-chevron-down"></i>';
    // var text = $(this).parent().find('.nav-tabs')
    // $(this).html(text);
    $(this).parents('.filter_top_wraps').find('.nav-tabs').slideToggle(200);
});

jQuery('.filter_top_wraps .nav-tabs li a').click(function (e) {
    e.preventDefault();
    var text = jQuery(this).html();
    jQuery(this).parents('.filter_top_wraps').find('.mobile_show_block .mobile_select span').html(text);
    jQuery(this).parents('.filter_top_wraps').find('.nav-tabs').slideUp(200)
    jQuery('.filter_top_wraps > .mobile_show_block .mobile_select').parent().removeClass('active_btn');
});
$(window).on('load', function () {

});

$(document).on('change', "input[name='preferred_currency']", function () {
    $('.op_amount, .op_amount_req').val('');
    $('#amount_txt, #amount_requested_txt').val('');
});

$(document).on('keyup', '.op_amount, .op_amount_req', function () {
    $('#amount_txt').val($('.op_amount').val());
    $('#amount_requested_txt').val($('.op_amount_req').val());
    changeCurrency();
});

changeCurrency = () => {
    var currency_type = $("input[name='preferred_currency']:checked").val();
    const currency_id_arr = ['amount_txt', 'amount_requested_txt'];
    $.each(currency_id_arr, function (index, txt_cls) {
        $(`#${txt_cls}:visible`).each(function () {
            if (currency_type == USD) {
                $(this).formatCurrency({
                    symbol: '',
                    positiveFormat: '%s %n',
                    negativeFormat: '(%s %n)',
                    decimalSymbol: '.',
                    digitGroupSymbol: ',',
                    groupDigits: true
                });
            } else {
                $(this).formatCurrency({
                    symbol: '',
                    positiveFormat: '%s %n',
                    negativeFormat: '(%s %n)',
                    decimalSymbol: ',',
                    digitGroupSymbol: '.',
                    groupDigits: true,
                    roundToDecimalPlace: -1,
                });
            }
        });
    });
}
