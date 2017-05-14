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

    $.fn.pfsFileUpload = function(options) {
        var settings = $.extend({}, $.fn.pfsFileUpload.defaults);
        var options = options || {};
        $.extend(true, settings, options);
        var data = $(this).data();
        $.extend(true, settings, data);

        var $fileValue = $(this);
        var $dropZone = $fileValue.closest('.fileupload');
        var $button = $dropZone.find('.fileinput-button');
        var $fileInputWrapper = $dropZone.find('.file-input-wrapper');
        var $fileInput = $dropZone.find('.file-input');
        var $filesContainer = $dropZone.find('.files-container');
        var isMultiple = $fileInput.is('[multiple]');
        var modelName = settings.model;
        var id = $fileValue.attr('id').split('-').join('_');
        var formData = {"fuf": settings.attribute, "fum": modelName, "fui": 1};
        var fileMaxSize = settings.fileMaxSize ? settings.fileMaxSize * 1024 : undefined;
        var fileMinSize = settings.fileMinSize ? settings.fileMinSize * 1024 : undefined;

        $fileInput.attr('name', id);

        var uploadTemplate = function(o) {
            var $rows = $();
            $.each(o.files, function(index, file) {
                var tpl = [
                    '<tr class="template-upload fade">',
                        '<td class="col-preview">',
                            '<span class="preview">',
                                (
                                    file.thumbnailUrl 
                                    ? '<a class="image"><img/></a>'
                                    : ''
                                ),
                            '</span>',
                        '</td>',
                        '<td>',
                            '<p class="name" style="word-break: break-all"></p>',
                            '<p class="size">',
                                $.translate("app", "Uploading"),
                            '</p>',
                            '<strong class="error text-danger"></strong>',
                            '<div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">',
                                '<div class="progress-bar progress-bar-success" style="width: 0%"></div>',
                            '</div>',
                        '</td>',
                        '<td class="col-button">',
                            (
                                !o.options.autoUpload 
                                ? [
                                    '<button type="button" class="btn btn-primary start" disabled>',
                                        '<i class="fa fa-upload"></i>',
                                        '&nbsp;',
                                        '<span>',
                                            $.translate("app", "Start upload"),
                                        '</span>',
                                    '</button>'
                                ].join('')
                                : [
                                    '<button type="button" class="btn btn-warning cancel">',
                                        '<i class="fa fa-ban"></i>',
                                        '&nbsp;',
                                        '<span>',
                                            $.translate("app", "Cancel upload"),
                                        '</span>',
                                    '</button>'
                                ].join('')
                            ),
                        '</td>',
                    '</tr>'
                ].join('');

                var $row = $(tpl);
                $row.find(".name").text(file.name);
                $row.find(".size").text(o.formatFileSize(file.size));
                $row.find(".start").click(function() {
                    // next
                });
                $row.find(".error").text(file.error);
                $rows = $rows.add($row);
            });
            return $rows;
        }

        var downloadTemplate = function(o) {
            var $rows = $();
            $.each(o.files, function(index, file) {
                var tpl = [
                    '<tr class="template-download fade">',
                        '<td class"col-preview">',
                            '<span class="preview">',
                                (
                                    file.thumbnailUrl
                                    ? '<a class="image"><img/></a>'
                                    : ''
                                ),
                            '</span>',
                        '</td>',
                        '<td>',
                            '<p class="name"></p>',
                            '<p class="size"></p>',
                            (
                                file.error
                                ? [
                                    '<div>',
                                        '<span class"label label-danger">Error</span>',
                                        '&nbsp;',
                                        '&nbsp;',
                                        '<span class="error-info"></span>',
                                    '</div>'
                                ].join('')
                                : ''
                            ),
                        '</td>',
                        '<td class="col-button">',
                            (
                                file.deleteUrl
                                ? [
                                    '<button type="button" class="btn btn-danger delete">',
                                        '<i class="fa fa-trash"></i>',
                                        '&nbsp;',
                                        '<span>',
                                            $.translate("app", "Delete upload"),
                                        '</span>',
                                    '</button>'
                                ].join('')
                                : [
                                    '<button type="button" class="btn btn-warning cancel">',
                                        '<i class="fa fa-ban"></i>',
                                        '&nbsp;',
                                        '<span>',
                                            $.translate("app", "Cancel upload"),
                                        '</span>',
                                    '</button>'
                                ].join('')
                            ),
                        '</td>',
                    '</tr>'
                ].join('');

                var $row = $(tpl);

                // $row.find(".image").attr("href", file.url);
                // $row.find(".image").attr("title", file.name);
                // $row.find(".image").attr("data-toggle", "tooltip-inline");
                $row.find(".image").attr("download", file.name);
                $row.find(".image").attr("data-gallery", '');
                $row.find(".image > img").attr("src", file.thumbnailUrl);
                $row.find(".image > img").attr("title", file.name);
                $row.find(".image > img").attr("data-toggle", "tooltip-inline");
                if (file.url) {
                    var $name = $("<a/>");
                    // $name.attr("href", file.url);
                    $name.attr("title", file.name);
                    $name.attr("data-toggle", "tooltip-inline");
                    $name.attr("download", file.name);
                    if (file.thumbnailUrl) {
                        $name.attr("data-gallery", '');
                    }
                    $name.text(file.name);
                    $row.find(".name").append($name);
                } else {
                    $row.find(".name").attr({
                        "title": file.name,
                        "data-toggle": "tooltip-inline"
                    });
                    $row.find(".name").text(file.name);
                }
                $row.find(".error-info").text(file.error);
                $row.find(".size").text(o.formatFileSize(file.size));
                $row.find(".delete").attr("data-type", file.deleteType);
                $row.find(".delete").attr("data-url", file.deleteUrl);
                if (file.deleteWithCredentials) {
                    $row.find(".delete").attr("data-xhr-fields", "{\"withCredentials\": true}");
                }
                $rows = $rows.add($row);
            });
            return $rows;
        }

        var done = function(e, data) {

            if (data.result.files.length  < 1) {
                return;
            }
            if (data.result.files[0].error) {
                return;
            }

            var name = data.result.files[0].name;
            var value;
            var arr = [];
            if (isMultiple && $fileValue.val()) {
                arr = $fileValue.val().split(settings.separator);
                arr.push(name);
                value = arr.join(settings.separator);
            } else {
                value = name;
            }

            $fileValue.val(value).trigger("change");
        };

        var change = function(e, data) {
            var arr = $fileValue.val() ? $fileValue.val().split(settings.separator) : [];
            var curlen = $fileValue.val() ? $fileValue.val().split('').length : 0;
            for(var i = 0; i < data.files.length; i++) {
                arr[arr.length] = data.files[i].name;
            }
            curlen = arr.join(settings.separator).split('').length;

            // if set only
            if (settings.fileMaxLength > 0 && settings.fileMaxLength < curlen) {
                alert($.translate("app", "Total length of file names exceeds field length"));
                return false;
            }

            if (settings.fileMaxCount < arr.length) {
                alert($.translate("app", "Maximum number of files exceeded"));
                return false;
            }
            $fileValue.trigger("change");
        };

        var deleted = function(e, data) {
            var param = {};
            var url = decodeURIComponent(data.url);
            if (url) {
                var rgx = new RegExp("(.*"+ id +"=)(.*?)&(.*)", "gi");
                url = url.replace(rgx, "$2");
                if (isMultiple) {
                    var ar = $fileValue.val().split(settings.separator);
                    var nar = [];
                    $.each(ar, function(index, val) {
                        if (val.trim() != url.trim()) {
                            nar.push(val.trim());
                        }
                    });
                    $fileValue.val(nar.join(settings.separator));
                } else {
                    $fileValue.val('');
                }
            }
            $fileValue.trigger("change");
        };

        var processfail = function(e, data) {
            $filesContainer.css("margin-top", "12px");
        };

        var changed = function(e, data) {
            if ($filesContainer.find("tr").length) {
                $filesContainer.css("margin-top", "12px");
            } else {
                $filesContainer.css("margin-top", "0");
            }
            $fileValue.trigger("change");
        };

        $fileValue.on("change", function() {
            var arr = $fileValue.val() ? $fileValue.val().split(settings.separator) : [];
            var curlen = $fileValue.val() ? $fileValue.val().split('').length : 0;

            var disabled = false;

            // if set only
            if (settings.fileMaxLength > 0 && curlen >= settings.fileMaxLength) {
                disabled = true;
            }

            if (arr.length >= settings.fileMaxCount) {
                disabled = true;
            }

            if (!isMultiple && arr.length > 0) {
                disabled = true;
            }

            if (disabled) {
                $(this).closest(".fileinput-button").attr("disabled", true);
                $(this).closest(".fileinput-button").find("[type=\"file\"]").attr("disabled", true).css("opacity", "0");
            } else {
                $(this).closest(".fileinput-button").removeAttr("disabled");
                $(this).closest(".fileinput-button").find("[type=\"file\"]").removeAttr("disabled");
            }
        });

        // inject csrf
        // $.widget("blueimp.fileupload", $.blueimp.fileupload, {
        //     _isXHRUpload: function (options) {
        //         // options.formData[$.pfs.getCsrfTokenName()] = $.pfs.getCsrfHash();
        //         this._super(options);
        //     }
        // });

        $fileInput.fileupload({
            url: settings.url,
            autoUpload: true,
            acceptFileTypes: (settings.fileAllowedTypes) ? new RegExp("\\.(" + settings.fileAllowedTypes + ")$", "i") : null,
            maxFileSize: fileMaxSize,
            minFileSize: fileMinSize,
            previewMaxWidth: 110,
            previewMaxHeight: 110,
            maxNumberOfFiles: settings.fileMaxCount,
            uploadTemplateId: null,
            downloadTemplateId: null,
            uploadTemplate: uploadTemplate,
            downloadTemplate: downloadTemplate,
            filesContainer: $filesContainer,
            formData: formData,
            dropZone: $dropZone,
            pasteZone: $dropZone,
            messages: {
                acceptFileTypes: $.translate("app", "Filetype not allowed"),
                maxFileSize: $.translate("app", "File is too big"),
                maxNumberOfFiles: $.translate("app", "Maximum number of files exceeded"),
                minFileSize: $.translate("app", "File is too small")
            },
            destroy: function (e, data) {
                if (e.isDefaultPrevented()) {
                    return false;
                }
                var that =  $(this).data("blueimp-fileupload") || $(this).data("fileupload");
                var removeNode = function () {
                    that._transition(data.context).done(
                        function () {
                            $(this).remove();
                            that._trigger("destroyed", e, data);
                        }
                    );
                };

                // inject formdata
                $.extend(true, data, {data: formData});

                // inject csrf
                // data.data[$.pfs.getCsrfTokenName()] = $.pfs.getCsrfHash();

                if (data.url) {
                    data.dataType = data.dataType || that.options.dataType;
                    $.ajax(data).done(removeNode).fail(function () {
                        that._trigger("destroyfailed", e, data);
                    });
                } else {
                    removeNode();
                }
            }
        })
        .on("fileuploaddone", done)
        .on("fileuploadchange", change)
        .on("fileuploaddestroy", deleted)
        .on("fileuploadprocessfail", processfail)
        .on("fileuploadadded fileuploadfinished fileuploaddestroyed", changed);

        $("#"+ modelName +"_pfs_form").addClass("fileupload-processing");
        $.ajax({
            url: $fileInput.fileupload("option", "url"),
            dataType: "json",
            data: formData,
            context: $fileInput[0]
        }).always(function () {
            $("#"+ modelName +"_pfs_form").removeClass("fileupload-processing");
        }).done(function (result) {

            if (!result[id].length) {
                $fileValue.val(null);
            }

            // onload
            if (isMultiple) {
                var arr = [];
                if ($fileValue.val()) {
                    arr = $fileValue.val().toString().split(settings.separator);
                }
                $.each(result[id], function(index, val) {
                    if (arr.indexOf(val.name) < 0) {
                        arr.push(val.name);
                    }
                });
                $fileValue.val(arr.join(settings.separator)).trigger("change");
            } else {
                var idx = 0;
                $.each(result[id], function(index, val) {
                    if (idx == 0) {
                        $fileValue.val(val.name).trigger("change");
                    } else {
                        return;
                    }
                    idx++;
                });
            }
            $fileInput.fileupload("option", "done")
                .call($fileInput, $.Event("done"), {result: {files: result[id]}});
        });
    }

    $.fn.pfsFileUpload.defaults = {
        modelName: '',
        fileMaxLength: 100,
        fileAllowedTypes: 'gif|jpg|jpeg|bmp|png',
        fileMaxSize: undefined,
        fileMinSize: undefined,
        fileMaxCount: 1,
        fileMinCount: 0,
        separator: ',',
        url: ''
    };
}));