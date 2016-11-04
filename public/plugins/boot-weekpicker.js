/*
* @Author: AminBy
* @Date:   2016-11-04 10:51:03
* @Last Modified by:   AminBy
* @Last Modified time: 2016-11-04 17:30:13
*/

'use strict';

$.AWP_DATEFORMAT = 'YY-MM-DD'
$.AWP_SPLITTER = ' to ';
function updateDateRange(ev, el, value) {
    el = el || this;
    value = value || $(el).val();
    var firstDate = moment(value, $.AWP_DATEFORMAT).day(0).format($.AWP_DATEFORMAT);
    var lastDate =  moment(value, $.AWP_DATEFORMAT).day(6).format($.AWP_DATEFORMAT);
    $(el).val(firstDate + $.AWP_SPLITTER + lastDate);
}

$.fn.aweekpicker = function() {
    $(this).on('keydown', function(ev) {
        ev.preventDefault();
        return false;
    });

    var range = $(this).val();

    //Get the value of Start and End of Week
    $(this).on('dp.change', updateDateRange);
    $(this).on('dp.error', updateDateRange);
    $(this).on('dp.hide', updateDateRange);
    $(this).on('dp.show', updateDateRange);
    $(this).on('dp.update', updateDateRange);

    $(this).datetimepicker({
        format: $.AWP_DATEFORMAT
    }).val(range);
};

$.fn.aweekpicker_prev = function() {
    var value = $(this).val() ? $(this).val().split($.AWP_SPLITTER) : moment(new Date()).format($.AWP_DATEFORMAT);
    updateDateRange(null, this, moment(value, $.AWP_DATEFORMAT).add(-7, 'd').format($.AWP_DATEFORMAT));
};
$.fn.aweekpicker_next = function() {
    var value = $(this).val() ? $(this).val().split($.AWP_SPLITTER) : moment(new Date()).format($.AWP_DATEFORMAT);
    updateDateRange(null, this, moment(value, $.AWP_DATEFORMAT).add(7, 'd').format($.AWP_DATEFORMAT));
};