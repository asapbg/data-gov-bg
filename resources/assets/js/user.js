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

    if ($('.js-select').length) {
        $('.js-select').select2({minimumResultsForSearch: -1});
    }

    if ($('.js-autocomplete').length) {
        $('.js-autocomplete').select2({
            matcher: function(params, data) {
                if ($.trim(params.term) == '' || typeof params.term == 'undefined') {
                    return data;
                }

                if (data.text.toLowerCase().indexOf(params.term.toLowerCase()) > -1) {
                    return data;
                } else {
                    return false;
                }
            }
        });
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
        $('.js-select').select2({minimumResultsForSearch: -1});
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
