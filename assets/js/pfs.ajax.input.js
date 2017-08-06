(function(factory) {
    "use strict";
    //noinspection JSUnresolvedVariable
    if (typeof define === 'function' && define.amd) { // jshint ignore:line
        // AMD. Register as an anonymous module.
        define(['jquery'], factory); // jshint ignore:line
    } else { // noinspection JSUnresolvedVariable
        if (typeof module === 'object' && module.exports) { // jshint ignore:line
            // Node/CommonJS
            // noinspection JSUnresolvedVariable
            module.exports = factory(require('jquery')); // jshint ignore:line
        } else {
            // Browser globals
            factory(window.jQuery);
        }
    }
}(function($) {
    "use strict";
    var isEmpty, ajaxInput;
    isEmpty = function(value) {
        return value === null || value === undefined || value.length === 0 || $.trim(value) === '';
    }
    ajaxInput = function(element, options) {
        // copy
        this.options = $.extend(true, {}, $.fn.pfsAjaxInput.defaults);
        // merge js options
        this.options = $.extend(true, this.options, options);
        // merge data options
        this.options = $.extend(true, this.options, $(this).data());
        // element
        this.$element = element;
        this.initialize();
    }
    $.extend(ajaxInput.prototype, {
        /**
         * Initialize plugin
         */
        initialize: function() {
            var self = this;
            // load depends
            if ($.isArray(self.options.depends) && self.options.depends.length > 0) {
                this.preLoadDepends();
                // skip: load data
            } else {
                self.load();
            }
        },
        /**
         * Pre-loading dependencies field.
         * Register all depends for watching 'change' interaction.
         */
        preLoadDepends: function() {
            var self = this;
            self.depLength = self.options.depends.length;
            for (var i = 0; i < self.depLength; i++) {
                var dependency = self.options.depends[i];
                if (dependency.id) {
                    self.watcher(dependency.id, dependency.options);
                }
            }
        },
        /**
         * Watcher use for detecting interaction of field.
         * @param {string} id - ID attribute of dependency element
         * @param {object} options - Configuration of depencency element
         */
        watcher: function(id, options) {
            var self = this,
                $dep;
            $("#" + id).on('change select2:select', function(e) {
                $dep = $(this);
                if (!isEmpty($dep.data('select2')) && e.type === 'change') {
                    return;
                }
                self.depChange();
                self.$element.find('.temporary').remove();
            });
            $("#" + id).trigger('change');
        },
        /**
         * The action if dependencies in interaction
         */
        depChange: function() {
            var self = this,
                type, value = {},
                $el;
            for (var i = 0; i < self.depLength; i++) {
                var dependency = self.options.depends[i];
                var val = null;
                if (dependency) {
                    $el = $("#" + dependency.id);
                    val = self.getValue($el);
                }
                value[i] = val;
            }
            self.preDepProcess(value);
        },
        /**
         * Pre-pocessing for dependencies.
         * Checking rule dependency.
         * @param {array} value - Value of dependencies field.
         */
        preDepProcess: function(value) {
            var self = this,
                forceDisabled = false,
                params = {};
            for (var i = 0; i < self.depLength; i++) {
                var options = self.options.depends[i];
                if (options.options.required && isEmpty(value[i])) {
                    forceDisabled = true;
                }
                params[options.attribute] = value[i];
            }
            self.inputDisabler(forceDisabled);
            if (!forceDisabled) {
                self.load(params);
            }
        },
        /**
         * Disable element if dependencies not complete 
         * @param {boolean} disable - Disabled or endabled the element
         */
        inputDisabler: function(disable) {
            var self = this;
            if (disable) {
                self.$element.addClass('disable');
                self.$element.attr({
                    disable: 'disable',
                    readonly: 'readonly'
                });
            } else {
                self.$element.removeClass('disable');
                self.$element.removeAttr('disable');
                self.$element.removeAttr('readonly');
            }
        },
        /**
         * Request and setting data to server
         * @param {object} dataForm - Param for request data
         */
        load: function(dataForm) {
            var self = this;
            dataForm = dataForm === undefined ? {} : dataForm;
            if (self.options.trigger == 'load') {
                self.process(dataForm, function(data, error) {
                    self.render(data, error);
                });
            } else {
                self.$element.on(self.options.trigger, function() {
                    if (self.$element.find('.temporary').length === 0) {
                        self.process(dataForm, function(data, error) {
                            self.render(data, error);
                        });
                    }
                });
            }
        },
        /**
         * Rendering elements with data from server
         * @param {object} data - Result data from server
         * @param {mixed} error - Error condition 
         */
        render: function(data, error) {
            var self = this;
            if (!error) {
                switch (self.options.type) {
                    case 'dropdown':
                        self.renderDropDown(data);
                        break;
                    case 'checkbox':
                        self.renderCheckbox(data);
                        break;
                    case 'radio':
                        self.renderRadio(data);
                        break;
                    case 'text':
                        self.renderText(data);
                        break;
                    default:
                        break;
                }
            }
        },
        renderDropDown: function(data) {
            var self = this;
            var isOptGroup = false;
            $.each(data, function(index, val) {
                if ($.isPlainObject(val)) {
                    isOptGroup = true;
                }
            });
            // value to array
            var values = self.splitValue(self.options.value, true);
            // remove temporary
            self.$element.find('.temporary').remove();
            var selected = false;
            // render dropdown with optgroup
            if (isOptGroup) {
                $.each(data, function(label, group) {
                    var optGroup = $('<optgroup/>', {
                        'label': label,
                        'class': 'temporary'
                    });
                    $.each(group, function(val, text) {
                        selected = values.indexOf('' + val) > -1;
                        self.createOptionElement(val, text, selected)
                            .appendTo(optGroup);
                    });
                    optGroup.appendTo(self.$element);
                });
                if (self.isParents() && selected) {
                    setTimeout(function() {
                        self.$element.trigger('change');
                    }, 250);
                }
            // render dropdown
            } else {
                $.each(data, function(val, text) {
                    selected = values.indexOf('' + val) > -1;
                    self.createOptionElement(val, text, selected)
                        .appendTo(self.$element);
                });
                if (self.isParents() && selected) {
                    setTimeout(function() {
                        self.$element.trigger('change');
                    }, 250);
                }
            }
        },
        createOptionElement: function(val, text, selected) {
            selected = selected === undefined ? false : selected;
            return $('<option/>', {
                'class': 'temporary ',
                'text': text,
                'value': val,
                'selected': selected,
            });
        },
        renderCheckbox: function(data) {
            var self = this;
            self.renderListInput('checkbox', data);
        },
        renderRadio: function(data) {
            var self = this;
            self.renderListInput('radio', data);
        },
        renderListInput: function(type, data) {
            var self = this,
                $container, $label, $checkbox, formatter,
                labelOptions, itemOptions, values, checked;
            // value to array
            values = self.splitValue(self.options.value, true);
            // remove temporary
            self.$element.find('.temporary').remove();
            // labelOptions
            labelOptions = self.options.options.labelOptions;
            if (labelOptions === undefined) {
                labelOptions = {};
            }
            // itemOptions
            itemOptions = self.options.options.itemOptions;
            if (itemOptions === undefined) {
                itemOptions = {};
            }
            // formatter
            formatter = self.options.options.itemClient;
            var index = 0;
            $.each(data, function(val, text) {
                checked = values.indexOf('' + val) > -1;
                if (formatter !== undefined) {
                    self.$element.append(formatter(index, text,
                        self.options.options.name, checked, val));
                } else {
                    self.$element.append(self.createListElement(type, val, text, false,
                        labelOptions, itemOptions, checked));
                }
                index++;
            });
            if (self.isParents() && checked) {
                setTimeout(function() {
                    self.$element.trigger('change');
                }, 250);
            }
        },
        createListElement: function(type, val, text, selected, labelOptions, itemOptions, checked) {
            var self = this,
                $container, $label;
            $container = $('<div/>', {
                'class': type + ' temporary'
            });
            $label = $('<label/>', labelOptions).appendTo($container);
            $('<input/>', $.extend(true, {
                'type': type,
                'name': self.options.options.name,
                'value': val,
                'checked': checked
            }, itemOptions)).appendTo($label);
            $label.append(' ' + text);
            return $container;
        },
        renderText: function(data) {
            var self = this,
                values, value, sources = [],
                $valueEl;
            $valueEl = self.$element.closest('.pfs__text-autocomplete').find('#' + self.options.options.valueId);
            // clear on render
            self.$element.val(null);
            $valueEl.val(null);
            // value to array
            values = self.splitValue(self.options.value, true);
            value = (values.length ? values[0] : null);
            // create sources
            $.each(data, function(index, val) {
                sources.push({
                    value: index,
                    label: val
                });
                // auto selected
                if (!isEmpty(value) && ('' + index == '' + value)) {
                    // label
                    self.$element.val(val);
                    // value
                    $valueEl.val(index);
                    self.$element.trigger('change');
                    delete self.options.value;
                }
            });
            self.$element.autocomplete({
                source: sources,
                select: function(e, ui) {
                    e.preventDefault();
                    try {
                        self.$element.val(ui.item.label);
                        $valueEl.val(ui.item.value).trigger('change');
                    } catch (e) {}
                },
                change: function(e, ui) {
                    e.preventDefault();
                    try {
                        self.$element.val(ui.item.label);
                        $valueEl.val(ui.item.value);
                    } catch (e) {
                        self.$element.val(null).trigger('change')
                        $valueEl.val(null).trigger('change');
                    }
                },
                focus: function(e, ui) {
                    e.preventDefault();
                    try {
                        self.$element.val(ui.item.label);
                        $valueEl.val(ui.item.value);
                    } catch (e) {
                        // exception
                    }
                }
            });
            self.$element.on('change', function(e) {
                if (isEmpty($(this).val())) {
                    $valueEl.val(null).trigger('change');
                }
            });
        },
        isParents: function() {
            var self = this;
            var $formEntry = self.$element.formEntry();
            var id = self.$element.attr('id');
            var isParents = false;
            $.each($formEntry, function(index, el) {
                var data = el.data();
                if (data.depends && $.isPlainObject(data.depends)) {
                    $.each(data.depends, function(name, active) {
                        if (name == id) {
                            isParents = true;
                        }
                    });
                }
            });
            return isParents;
        },
        showLoader: function() {
            var self = this;
            if (self.options.trigger == 'load') {
                self.$element.addClass('pfs__ajax-loader');
                switch (self.options.type) {
                    case 'dropdown':
                        $('<option/>', {
                            text: self.options.loadingText,
                            class: 'pfs__loader-text',
                            selected: true
                        }).prependTo(self.$element);
                        self.$element.attr('disabled', true);
                        break;
                    case 'text':
                        if (self.$element.prop('placeholder')) {
                            var value = self.$element.attr('placeholder');
                            self.$element.attr('data-placeholder', value);
                        }
                        self.$element.attr('placeholder', self.options.loadingText).attr('disabled', true);
                        break;
                }
            }
        },
        hideLoader: function() {
            var self = this;
            if (self.options.trigger == 'load') {
                switch (self.options.type) {
                    case 'dropdown':
                        self.$element.removeClass('pfs__ajax-loader');
                        self.$element.find('.pfs__loader-text').remove();
                        self.$element.trigger('change');
                        self.$element.removeAttr('disabled');
                        break;
                    case 'text':
                        var value = self.$element.data().placeholder;
                        self.$element.removeAttr('data-placeholder');
                        self.$element.removeAttr('placeholder');
                        if (!isEmpty(value)) {
                            self.$element.attr('placeholder', value);
                        }
                        self.$element.trigger('change');
                        self.$element.removeAttr('disabled');
                        break;
                }
                self.$element.removeClass('pfs__ajax-loader');
            }
        },
        process: function(data, fn) {
            var self = this;
            var data = data || {};
            var ajaxSettings = {
                url: this.options.url,
                data: data,
                dataType: 'json',
                type: 'POST',
                beforeSend: function() {
                    self.showLoader();
                },
                success: function(e) {
                    if (e.status) {
                        fn(null, e);
                    } else {
                        fn(e, null);
                    }
                },
                error: function(e) {
                    fn(null, e);
                },
                complete: function() {
                    setTimeout(function() {
                        self.hideLoader();
                    }, 500);
                }
            };
            $.extend(true, ajaxSettings, self.options.ajaxSettings);
            $.ajax(ajaxSettings);
        },
        splitValue: function(value, trim) {
            if (isEmpty(value)) {
                return [];
            }
            var values = $.trim(value).split(this.options.separator);
            if (trim === true) {
                $.each(values, function(index, val) {
                    values[index] = $.trim(val);
                });
            }
            return values;
        },
        getValue: function(el) {
            var self = this,
                value;
            switch (self.getTagName(el)) {
                case 'input':
                case 'select':
                case 'dropdown':
                case 'textarea':
                    value = el.val();
                    break;
                case 'checkbox':
                case 'radio':
                    var values = [];
                    el.find('input:checked').each(function(index, el) {
                        values.push($(el).val());
                    });
                    value = values;
                    break;
                default:
                    break;
            }
            if (isEmpty(value)) {
                return null;
            }
            return value;
        },
        getTagName: function(el) {
            if (el.length) {
                switch (el[0].localName) {
                    case 'input':
                        return 'input';
                    case 'select':
                        return 'dropdown';
                    case 'textarea':
                        return 'textarea';
                    default:
                        if (el.find('input[type="checkbox"]').length) {
                            return 'checkbox';
                        } else if (el.find('input[type="radio"]').length) {
                            return 'radio';
                        }
                        break;
                }
            }
            return false;
        }
    });
    $.fn.pfsAjaxInput = function(options) {
        new ajaxInput($(this), options);
    }
    $.fn.pfsAjaxInput.defaults = {
        url: '',
        depends: [],
        loadingText: 'Loading...',
        type: 'dropdown', // text, checkbox, radio
        trigger: 'load', // load, click, dblclick, mousedown, etc.
        value: '',
        separator: ',',
        ajaxSettings: {},
        options: {
            isNewRecord: true
        }
    };
}));