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

    if (yii && yii.validation) {
        $.extend(yii.validation, {
            alpha: function (value, messages, options) {
                if (options.skipOnEmpty && yii.validation.isEmpty(value)) {
                    return;
                }
                
                var valid = /^[A-z]+$/.test(value);
                if (valid === false) {
                    yii.validation.addMessage(messages, options.message, value);
                }
            },
            alphaDash: function (value, messages, options) {
                if (options.skipOnEmpty && yii.validation.isEmpty(value)) {
                    return;
                }
                
                var valid = /^[a-z0-9_-]+$/i.test(value);
                if (valid === false) {
                    yii.validation.addMessage(messages, options.message, value);
                }
            },
            alphaNumeric: function (value, messages, options) {
                if (options.skipOnEmpty && yii.validation.isEmpty(value)) {
                    return;
                }
                
                var valid = /^\w+$/i.test(value);
                if (valid === false) {
                    yii.validation.addMessage(messages, options.message, value);
                }
            },
            alphaNumericSpaces: function (value, messages, options) {
                if (options.skipOnEmpty && yii.validation.isEmpty(value)) {
                    return;
                }
                
                var valid = /^[A-Z0-9 ]+$/i.test(value);
                if (valid === false) {
                    yii.validation.addMessage(messages, options.message, value);
                }
            },
            contains: function (value, messages, options) {
                if (options.skipOnEmpty && yii.validation.isEmpty(value)) {
                    return;
                }

                var valid = false;
                if (options.mode == 'contain') {
                    var rgx = new RegExp(options.contain, "g");
                    valid = rgx.test(value);
                } else if (options.mode == 'startWith') {
                    var rgx = new RegExp("^"+ options.contain, "g");
                    valid = rgx.test(value);
                } else if (options.mode == 'endWith') {
                    var rgx = new RegExp(options.contain +"$", "g");
                    valid = rgx.test(value);
                }

                if (valid === false) {
                    yii.validation.addMessage(messages, options.message, value);
                }
            },
            creditCards: function (value, messages, options) {
                if (options.skipOnEmpty && yii.validation.isEmpty(value)) {
                    return;
                }
                
                var creditCardMatch = {
                    AMERICAN_EXPRESS: "((34|37)([0-9]{13}))",
                    DANKORT: "((5019)([0-9]{12}))",
                    DINERS_CLUB: "(((300|301|302|303|304|305|309)([0-9]{11}))|((36|38|39)([0-9]{12})))",
                    DINERS_CLUB_US: "((54|55)([0-9]{14}))",
                    DISCOVER: "(((622126|622127|622128|622129|622920|622921|622922|622923|622924|622925)([0-9]{10}))|((62213|62214|62215|62216|62217|62218|62219|62290|62291)([0-9]{11}))|((6222|6223|6224|6225|6226|6227|6228|6011)([0-9]{12}))|((644|645|646|647|648|649)([0-9]{13}))|((65)([0-9]{14})))",
                    ELO: "(((4011|4312|4389|4514|4573|4576|5041|5066|5067|6277|6362|6363|6516|6550)([0-9]{12}))|((509|650)([0-9]{13})))",
                    JCB: "(((3528|3529)([0-9]{12}))|((353|354|355|356|357|358)([0-9]{13})))",
                    LASER: "((6304|6706|6771|6709)([0-9]{12,15}))",
                    MAESTRO: "((5018|5020|5038|5868|6304|6759|6761|6762|6763|6764|6765|6766)([0-9]{8,15}))",
                    MASTERCARD: "((51|52|53|54|55)([0-9]{14}))",
                    SOLO: "((6334|6767)(([0-9]{12})|([0-9]{14,15})))",
                    UNIONPAY: "(((622126|622127|622128|622129|622920|622921|622922|622923|622924|622925)([0-9]{10,13}))|((62213|62214|62215|62216|62217|62218|62219|62290|62291)([0-9]{11,14}))|((6222|6223|6224|6225|6226|6227|6228)([0-9]{12,15})))",
                    VISA_ELECTRON: "(((4026|4405|4508|4844|4913|4917)([0-9]{12}))|((417500)([0-9]{10})))",
                    VISA: "((4)([0-9]{15}))"
                };

                // single format
                if (options.format !== null) {
                    options.formats = [options.format];
                }

                var valid = false;
                if (options.formats.length) {
                    var rgxArr = [];
                    $.each(options.formats, function(index, val) {
                        if (creditCardMatch[val] !== undefined) {
                            rgxArr.push( creditCardMatch[val] );
                        }
                    });
                    var rgx = new RegExp("^("+ rgxArr.join("|") +")$");
                    valid = rgx.test(value);
                }

                if (valid === false) {
                    yii.validation.addMessage(messages, options.message, value);                
                }
            },
            color: function (value, messages, options) {
                if (options.skipOnEmpty && yii.validation.isEmpty(value)) {
                    return;
                }

                var valid = false;
                if (options.format == 'hex') {
                    valid = /^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(value);
                } else if (options.format == 'rgb') {
                    valid = /^(rgb)?\(?([01]?\d\d?|2[0-4]\d|25[0-5])(\W+)([01]?\d\d?|2[0-4]\d|25[0-5])\W+(([01]?\d\d?|2[0-4]\d|25[0-5])\)?)$/.test(value);
                }

                if (valid === false) {
                    yii.validation.addMessage(messages, options.message, value);
                }
            },
            decimal: function (value, messages, options) {
                if (options.skipOnEmpty && yii.validation.isEmpty(value)) {
                    return;
                }
                
                var valid = /^[\-+]?[0-9]+\.[0-9]+$/.test(value);
                if (valid === false) {
                    yii.validation.addMessage(messages, options.message, value);
                }
            },
            equals: function (value, messages, options) {
                if (options.skipOnEmpty && yii.validation.isEmpty(value)) {
                    return;
                }

                var values = [];
                $.each(options.values, function(index, val) {
                    if (options.caseSensitive) {
                        values.push(val.trim());
                    } else {
                        values.push(val.toLowerCase().trim());
                    }
                });

                if (!options.caseSensitive) {
                    value = value.toLowerCase().trim();
                }

                var valid = values.indexOf(value) > -1; // is valid

                // inverse
                if (options.notEquals) {
                    valid = valid ? false : true;
                }

                if (valid === false) {
                    yii.validation.addMessage(messages, options.message, value);
                }
            },
            isbn: function (value, messages, options) {
                if (options.skipOnEmpty && yii.validation.isEmpty(value)) {
                    return;
                }

                var valid = false;
                var isbn = false;

                if ((options.type === null || options.type == 'isbn10') && (
                    /^\d{9}[\dX]$/.test(value) || 
                    /^(\d+)-(\d+)-(\d+)-([\dX])$/.test(value) || 
                    /^(\d+)\s(\d+)\s(\d+)\s([\dX])$/.test(value)
                )) {
                    isbn = 'isbn10';
                } else if ((options.type === null || options.type == 'isbn13') && (
                    /^(978|979)\d{9}[\dX]$/.test(value) ||
                    /^(978|979)-(\d+)-(\d+)-(\d+)-([\dX])$/.test(value) ||
                    /^(978|979)\s(\d+)\s(\d+)\s(\d+)\s([\dX])$/.test(value)
                )) {
                    isbn = 'isbn13';
                } else {

                }

                value = value.replace(/[^0-9x]/gi, "");

                if (isbn == "isbn10") {
                    var chars = value.split("");
                    if(chars[9].toUpperCase() == "X"){
                        chars[9] = 10;
                    }
                    var sum = 0;
                    for (var i = 0; i < chars.length; i++) {
                        sum += ((10-i) * parseInt(chars[i]));
                    }
                    valid = ((sum % 11) == 0);
                } else if (isbn == "isbn13") {
                    var chars = value.split("");
                    var sum = 0, digit, check, i = 0;
                    for(i = 0; i < 12; i++) {
                        digit = parseInt(chars[i]);
                        if (i % 2) {
                            sum += 3 * digit;
                        } else {
                            sum += digit;
                        }
                    }
                    valid = (((10 - (sum % 10)) % 10) == chars[chars.length - 1]);
                }

                if (valid === false) {
                    yii.validation.addMessage(messages, options.message, value);
                }
            },
            uuid: function (value, messages, options) {
                if (options.skipOnEmpty && yii.validation.isEmpty(value)) {
                    return;
                }
                
                var valid = /^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i.test(value);
                if (valid === false) {
                    yii.validation.addMessage(messages, options.message, value);
                }
            }
        });
    }
}));