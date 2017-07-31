(function($) {

    // Bootstrap tooltip
    $(document).tooltip({
        selector: '.toggle-tooltip, [data-toggle="toggle-tooltip"]',
        trigger: 'hover'
    });

    $(document).tooltip({
        selector: '.toggle-tooltip-body, [data-toggle="toggle-tooltip-body"]',
        container: 'body',
        trigger: 'hover'
    });

    // jQuery Plugin "form entry"
    $.extend($.fn, {
        formEntry: function() {
            var $frmEntry = $(this).closest('div.form-entry, tr.form-entry');
            if ($frmEntry.length) {
                var fields = {};
                $frmEntry.find('[data-attribute]').each(function(index, el) {
                    var data = $(el).data();
                    fields[ data.attribute ] = $(el);
                });
                return fields;
            }
            return;
        }
    });

    // jQuery Plugin
    $.extend($.fn, {
        getInputName: function() {
            var $element = $(this);
            if ($element.length) {
                switch($element[0].localName) {
                    case 'input':
                        return 'input';
                    case 'select':
                        return 'dropdown';
                    case 'textarea':
                        return 'textarea';
                    default:
                        if ($element.find('input[type="checkbox"]').length) {
                            return 'checkbox';
                        } else if ($element.find('input[type="radio"]').length) {
                            return 'radio';
                        }
                    break;
                }
            }
            return null;
        }
    });

    // jQuery Plugin
    $.extend($.fn, {
        getInputValue: function() {
            var self = this;
            var value = null;
            var $element = $(self);
            switch($(self).getInputName()) {
                case 'input':
                case 'select':
                case 'dropdown':
                case 'textarea':
                    value = $element.val();
                break;
                case 'checkbox':
                case 'radio':
                    var values = [];
                    $element.find('input:checked').each(function(index, el) {
                        values.push($(el).val());
                    });
                    value = values;
                break;
                default:

                break;
            }            
            return value;
        }
    });

    // translate
    $.translate = function(category, message, params, language) {
        language = language || 'en';
        try {
            if (window['translate_'+ language] && window['translate_'+ language][message]) {
                return window['translate_'+ language][message];
            }
        } catch (e) {

        }

        return message;
    }
    
    // submenu
    $(document).on("click change keydown", "[data-toggle='dropdown']", function() {
      $(this).submenupicker();
    });

    // autofill
    $(document).on('change', '[data-autofill]', function() {
        $(this).trigger('autofill');
    });

    // autofill
    $(document).on('autofill', '[data-autofill]', function() {
        var data = $(this).data();
        var $this = $(this);
        if (data.autofill && !$.isEmptyObject(data.autofill.parents) && data.autofill.url && data.autofill['target-attribute']) {
            var url = data.autofill.url,
                parents = data.autofill.parents,
                attribute = data.autofill['target-attribute'],
                value = $this.getInputValue();
            
            if (url.indexOf('?') == -1) {
                url += '?';
            } else {
                url += '&';
            }

            if ($.isArray(value)) {
                value = value[0]; // first only
            }

            url += encodeURIComponent(attribute) +'='+ encodeURIComponent(value);

            $.post(url, {}, function(data, textStatus, xhr) {
                if (textStatus == 'success' && $.isPlainObject(data)) {
                    $.each(parents, function(column, target) {
                        if (data[column]) {
                            $('[data-attribute="'+ target +'"]').each(function() {
                                var value = data[column].toString();
                                if (['checkbox', 'radio'].indexOf($(this).attr('type')) > -1) {
                                    if ($(this).val() == value) {
                                        $(this).prop('checked', true);
                                        $(this).trigger('change');
                                    }
                                } else {
                                    $(this).val(data[column]);
                                    $(this).trigger('change');
                                }
                            });
                        }
                    });
                }
            });
        }
    });

    // dialog confirm
    $(document).on('click', '[data-dialog-confirm]', function(event) {
        event.preventDefault();
        var $that = $(this);
        var data = $that.data();
        var title = data.dialogTitle || false;
        var titleClient = data.dialogTitleClient || false;
        var message = data.dialogMessage || false;
        var messageClient = data.dialogMessageClient || false;
        var method = data.dialogMethod || false;

        if (titleClient) {
            title = $.translate('app', titleClient);
        }
        
        if (messageClient) {
            message = $.translate('app', messageClient);
        }

        if (message) {
            $.confirm({
                title: title,
                theme: 'bootstrap',
                content: message,
                buttons: {
                    yes: {
                        text: $.translate('app', 'Yes'),
                        btnClass: 'btn-primary min-width-60',
                        action: function() {
                            if (method) {
                                $that.data('method', 'post');
                            }
                            yii.handleAction($that, event);
                            $that.removeData('method');
                        }
                    },
                    no: {
                        text: $.translate('app', 'No'),
                        btnClass: 'btn-default min-width-60',
                        action: function() {
                            $that.removeData('method');
                        }
                    }
                }
            });
        }
    });

    // dialog alert
    $(document).on('click', '[data-dialog-alert]', function(event) {
        event.preventDefault();
        var $that = $(this);
        var data = $that.data();
        var title = data.dialogTitle || false;
        var titleClient = data.dialogTitleClient || false;
        var message = data.dialogMessage || false;
        var messageClient = data.dialogMessageClient || false;

        if (titleClient) {
            title = $.translate('app', titleClient);
        }
        
        if (messageClient) {
            message = $.translate('app', messageClient);
        }
        
        if (message) {
            $.alert({
                title: title,
                theme: 'bootstrap',
                content: message,
                buttons: {
                    ok: {
                        text: $.translate('app', 'Ok'),
                        btnClass: 'btn-primary min-width-60',
                        action: function() {

                        }
                    }
                }
            });
        }
    });

})(jQuery);