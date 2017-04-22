(function (factory) {
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
}(function ($) {
    "use strict";

    $.extend(true, $.fn, {
        advancedSearch: function(options) {
            var id = $(this).attr('id');
            var setting = {
                searchId: '',
                historyListId: '',
                historyRemoveId: '',
                isModal: false,
                toggleId: null
            };
            $.extend(true, setting, options);

            var modules = {
                loadData: function() {
                    var localData = {};
                    try {
                        localData = localStorage[setting.searchId] ? JSON.parse(localStorage[setting.searchId]) : [];
                    } catch (e) {
                        localData = {};
                    }

                    return localData;
                },
                renderDropDown: function() {
                    var localData = this.loadData();
                    var historyList = $('#'+ setting.historyListId);
                    var removeList = $('#'+ setting.historyRemoveId);
                    if (historyList.length && removeList.length) {
                        $('.'+ setting.searchId +'-load').remove();
                        $('.'+ setting.searchId +'-remove').remove();

                        $.each(localData, function(name, data) {
                            // load list
                            var a = $('<a/>', {
                                'href': 'javascript:;',
                                'class': setting.searchId +'-load',
                                'data-name': name
                            }).append(''+ name);
                            if (historyList.find('.history').length) {
                                $('#'+ setting.historyListId +' .history').after($('<li/>').append(a))
                            } else {
                               $('<li/>').append(a).prependTo(historyList);
                            }

                            // remove list
                            var a = $('<a/>', {
                                'href': 'javascript:;',
                                'class': setting.searchId +'-remove',
                                'data-name': name
                            }).append(''+ name);
                            $('<li/>').append(a).appendTo(removeList);
                        });
                    }
                },
                remove: function(id) {
                    var localData = this.loadData();
                    if (localData[id] !== undefined) {
                        delete localData[id];
                        localStorage.setItem(setting.searchId, JSON.stringify(localData).toString());
                    }
                },
                save: function(name, data) {
                    var localData = this.loadData();
                    localData[name] = data;
                    localStorage.setItem(setting.searchId, JSON.stringify(localData).toString());
                },
                load: function(name) {
                    var localData = this.loadData(), data = {};
                    if ((data = localData[name]) !== undefined) {
                        if (setting.isModal) {
                            $('#'+ id).modal('show');
                        } else {
                            $('#'+ id).collapse('toggle');
                        }

                        $.each(data, function(index, val) {
                            $('[name="'+ val.name +'"]').each(function(index, el) {
                                if (['checkbox', 'radio'].indexOf($(this).attr('type')) > -1) {
                                    if ($(this).val() == val.value) {
                                        $(this).prop('checked', true);
                                        $(this).trigger('change');
                                    }
                                } else {
                                    $(this).val(val.value);
                                    $(this).trigger('change');
                                }
                            });
                        });
                    }
                }
            }

            // save event
            $(document).on('click', '#'+ id +'-save-button', function() {
                var name;

                $('[name="'+ id +'-save-name"]').each(function(index, el) {
                    var value = $(this).val();
                    if (value.toString().trim != '' && value != null) {
                        name = value;
                    }
                });

                if (name.toString().trim() != '' && name != null) {
                    var form = $(this).closest('form');
                    var formData = form.serializeArray();
                    modules.save(name, formData);
                    modules.renderDropDown();

                    if (setting.isModal) {
                        $('#'+ id).modal('hide');
                    } else {
                        $('#'+ id).collapse('toggle');
                    }
                } else {
                    alert('Name empty');
                }
            });

            // load history event
            $(document).on('click', '.'+ setting.searchId + '-load', function() {
                var data = $(this).data();
                if (data.name) {
                    modules.load(data.name);
                    modules.renderDropDown();
                }
            });

            // remove history event
            $(document).on('click', '.'+ setting.searchId + '-remove', function() {
                var data = $(this).data();
                if (data.name) {
                    modules.remove(data.name);
                    modules.renderDropDown();
                }
            });

            // toggle modal event
            if (setting.toggleId) {
                if (setting.isModal) {
                    $(document).on('click', '#'+ setting.toggleId, function() {
                        $('#'+ id).modal('show');
                    });
                }
            }

            // toggle collapse event
            if (!setting.isModal) {
                var toggleId = [id +'-button'];
                if (setting.toggleId) {
                    toggleId.push(setting.toggleId);
                }

                $('#'+ id).collapse({toggle: false});
                $.each(toggleId, function(index, val) {
                    $(document).on('click', '#'+ val, function() {
                        $('#'+ id).collapse('toggle');
                    });
                });
            }

            modules.renderDropDown();
        }
    });    
}));