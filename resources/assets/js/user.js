$(function() {
    if ($('.js-logo').length) {
        var $button = $('.js-logo');
        var $input = $('.js-logo-input');
        var $preview = $('.js-preview');

        $button.on('click', function(e) {
            $input.trigger('click');
        });

        $input.change(function() {
            readURL(this);
            $preview.removeClass('hidden');
        });
    }

    function readURL(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();

            reader.onload = function (e) {
                $preview.attr('src', e.target.result);
            }

            reader.readAsDataURL(input.files[0]);
        }
    }

    if ($('#delete-confirm').length) {
        $('#confirm').on('click', function(e) {
            $('#delete-confirm').modal('toggle');
        })
    }
});

$(function() {
    $('#sendTermOfUseReq').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: '/user/sendTermsOfUseReq',
            type: 'POST',
            data: $('#sendTermOfUseReq').serialize(),
            success: function(data) {
                var response = JSON.parse(data);
                if (response.success) {
                    $('#js-alert-success').show();
                    $('.alert-success').fadeTo(3000, 500).slideUp(500, function(){
                        $('.alert-success').slideUp(500);
                    });
                } else {
                    $('#js-alert-danger').show();
                    $('.alert-danger').fadeTo(3000, 500).slideUp(500, function(){
                        $('.alert-danger').slideUp(500);
                    });
                }
            },
            error: function (jqXHR) {
                $('#js-alert-danger').show();
                $('.alert-danger').fadeTo(2000, 500).slideUp(500, function(){
                    $('.alert-danger').alert('close');
                });
            }
        });
    });
});

/**
 * Select 2 functionality
 *
 */
function initSelect2() {
    if ($('.js-select').length) {
        $('.js-select').each(function() {
            $(this).select2({
                placeholder: $(this).data('placeholder'),
                minimumResultsForSearch: -1
            });
        })
    }

    if ($('.js-autocomplete').length) {
        $('.js-autocomplete').each(function() {
            $(this).select2({
                placeholder: $(this).data('placeholder'),
                matcher: function(params, data) {
                    if ($.trim(params.term) == '' || typeof params.term == 'undefined') {
                        return data;
                    }

                    if (data.text.toLowerCase().indexOf(params.term.toLowerCase()) > -1) {
                        return data;
                    }

                    return false;
                }
            });
        });
    }

    if ($('.js-ajax-autocomplete').length) {
        $('.js-ajax-autocomplete').each(function() {
            $(this).select2({
                placeholder: $(this).data('placeholder'),
                minimumInputLength: 3,
                dropdownParent: $($(this).data('parent')),
                ajax: {
                    url: $(this).data('url'),
                    type: "POST",
                    delay: 1000,
                    data: function (params) {
                        var queryParams = {
                            criteria: {
                                keywords: params.term
                            }
                        };
                        var finalParams = $.extend({}, queryParams, $(this).data('post'));

                        return finalParams;
                    },
                    processResults: function (data) {
                        return {
                            results: $.map(data.users, function (item) {
                                return {
                                    text: item.firstname + ' ' + item.lastname,
                                    id: item.id
                                }
                            })
                        };
                    }
                },
                matcher: function(params, data) {
                    if ($.trim(params.term) == '' || typeof params.term == 'undefined') {
                        return data;
                    }

                    if (data.text.toLowerCase().indexOf(params.term.toLowerCase()) > -1) {
                        return data;
                    }

                    return false;
                }
            });
        });
    }
};
initSelect2();

$(function() {
    $('.js-member-edit').on('click', function(e) {
        var $controls = $(this).closest('.js-member-admin-controls');
        $controls.addClass('hidden');
        $controls.siblings('.js-member-edit-controls').removeClass('hidden');
        initSelect2();
    });

    $('.js-member-cancel').on('click', function(e) {
        var $controls = $(this).closest('.js-member-edit-controls');
        $controls.siblings('.js-member-admin-controls').removeClass('hidden');
        $controls.addClass('hidden');
    });
});

$(function() {
    $('#invite-existing').on('show.bs.modal', function (e) {
        setTimeout(function() {initSelect2();}, 200);
    });

    $('#invite').on('show.bs.modal', function (e) {
        setTimeout(function() {initSelect2();}, 200);
    });
})
