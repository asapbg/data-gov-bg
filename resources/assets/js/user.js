import { EEXIST } from 'constants';
import { error } from 'util';

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
    var select2Delay = 1000;
    var select2MinLength = 3;

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
            var options = {
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
            };

            $(this).select2(options);
        });
    }

    if ($('.js-ajax-autocomplete').length) {
        $('.js-ajax-autocomplete').each(function() {
            var options = {
                placeholder: $(this).data('placeholder'),
                minimumInputLength: select2MinLength,
                dropdownParent: typeof $(this).data('parent') != 'undefined' ? $($(this).data('parent')) : false,
                ajax: {
                    url: $(this).data('url'),
                    type: 'POST',
                    delay: select2Delay,
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
                                    text: item.username + ' | ' + item.firstname + ' ' + item.lastname,
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
            };

            options = addSelect2Translations(options);

            $(this).select2(options);
        });
    }

    if ($('.js-ajax-autocomplete-org').length) {
        $('.js-ajax-autocomplete-org').each(function() {
            var $this = $(this);

            var options = {
                placeholder: $this.data('placeholder'),
                minimumInputLength: select2MinLength,
                ajax: {
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: $this.data('url'),
                    type: 'POST',
                    delay: select2Delay,
                    data: function (params) {
                        var queryParams = {
                            criteria: {
                                keywords: params.term,
                                order: {
                                    type: typeof $this.data('order-type') != 'undefined' ? $this.data('order-type') : '',
                                    field: typeof $this.data('order-field') != 'undefined' ? $this.data('order-field') : '',
                                }
                            }
                        };
                        var finalParams = $.extend({}, queryParams, $this.data('post'));

                        return finalParams;
                    },
                    processResults: function (data) {

                        return {
                            results: $.map(data.organisations, function (item) {

                                return {
                                    text: item.name,
                                    id: typeof $this.data('id') != 'undefined' ? item.id : item.uri
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
            };

            options = addSelect2Translations(options);

            $(this).select2(options);
        });
    }

    $('.js-ajax-autocomplete-org, .js-ajax-user').on('select2:open', function (e) {
        var $this = $(this);
        if (!$($this).data('do-not-apply-clear')) {
            var selectedVal = $this.find(':selected').val();

            if (typeof selectedVal != 'undefined') {
                if ($(".js-ajax-autocomplete-org option[value='0']").length == 0) {
                    var newOption = new Option($('.js-translations').data('clear-org-filter'), 0, true, true);
                    $this.append(newOption).trigger('change');
                }
            }
        }
    });
};
initSelect2();

function addSelect2Translations(options) {
    if ($('#app').data('lang') == 'bg') {
        options = $.extend({}, options, {
            language: {
                errorLoading: function() {
                    return 'Резултатите не могат да бъдат заредени';
                },
                inputTooLong: function(a) {
                    var b = a.input.length - a.maximum,
                        c = 'Моля изтрийте ' + b + ' символа';

                    return 1 != b && (c += 's'), c;
                },
                inputTooShort: function(a) {
                    return 'Моля въведете ' + (a.minimum - a.input.length) + ' или повече символа';
                },
                loadingMore: function loadingMore() {
                    return 'Зареждане на резултати…';
                },
                maximumSelected: function(a) {
                    var b = 'Може да изберете само ' + a.maximum + ' елемент';

                    return 1 != a.maximum && (b += 'а'), b;
                },
                noResults: function() {
                    return 'Няма намерени резултати';
                },
                searching: function() {
                    return 'Търсене…';
                }
            }
        })
    }

    return options;
}

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
});

$(function() {
    if ($('.js-ress-type').length) {
        checkRessType();

        $('.js-ress-type').on('change', function() {
            checkRessType();
        });

        function checkRessType() {
            var type = $('.js-ress-type option:selected').val();

            switch (parseInt(type)) {
                case 1:
                    $('.js-ress-url').hide();
                    $('.js-ress-api').hide();
                    $('.js-ress-file').show();
                    break;
                case 2:
                    $('.js-ress-file').hide();
                    $('.js-ress-api').hide();
                    $('.js-ress-url').show();
                    break;
                case 3:
                    $('.js-ress-file').hide();
                    $('.js-ress-url').hide();
                    $('.js-ress-api').show();
                    break;
                default:
                    $('.js-ress-file').hide();
                    $('.js-ress-url').hide();
                    $('.js-ress-api').hide();
            }
        }
    }
});

$(function() {
    if ($('.js-xml-prev').length) {
        var format = require('prettify-xml');
        var xmlData = $(".js-xml-prev").data('xml-data');
        var formattedXml = format(xmlData.trim());
        $('.js-xml-prev').text(formattedXml);
    }
});
