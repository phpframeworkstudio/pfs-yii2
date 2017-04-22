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
})


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

$(document).on('change', '[data-autofill]', function() {
    $(this).trigger('autofill');
});

$(document).on('autofill', '[data-autofill]', function() {
    var data = $(this).data();
    var $this = $(this);
    if (data.attribute && data.autofill && !$.isEmptyObject(data.autofill.parents) && data.autofill.url) {
        var url = data.autofill.url,
            parents = data.autofill.parents,
            attribute = data.attribute;
        if (url.indexOf('?') == -1) {
            url += '?';
        } else {
            url += '&';
        }
        url += encodeURIComponent(attribute) +'='+ encodeURIComponent($this.val());
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

})(jQuery);