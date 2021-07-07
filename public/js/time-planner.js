/**
 * Time planner-related JavaScript functionality
 * 
 * Using $ for jQuery object variables
 *
 * @package lyra
 *
 * @author juancrrn
 *
 * @version 0.0.1
 */

/**
 * Namespace object
 */
var timePlanner = {};

timePlanner.landingModalId = 'time-planner-landing-modal';
timePlanner.landingModalSelector = '#' + timePlanner.landingModalId;
timePlanner.landingFormId = 'time-planner-landing-form';
timePlanner.landingFormSelector = '#' + timePlanner.landingFormId;
timePlanner.appointmentDateInputName = 'appointment-date';
timePlanner.appointmentTimeInputName = 'appointment-time';
timePlanner.appointmentDateInputSelector = '#' + timePlanner.landingFormId + ' select[name=' + timePlanner.appointmentDateInputName + ']';
timePlanner.appointmentTimeInputSelector = '#' + timePlanner.landingFormId + ' select[name=' + timePlanner.appointmentTimeInputName + ']';

timePlanner.resetDateSelect = function () {
    const $select = $(timePlanner.appointmentDateInputSelector);
        
    $select.prop('disabled', 'disabled');
    $select.empty();
    $select.append('<option selected disabled>Escoge un día</option>');
}

timePlanner.reloadDateSelect = function () {
    timePlanner.resetDateSelect();

    const $select = $(timePlanner.appointmentDateInputSelector);
        
    $.get(
        autoconf.APP_URL + '/api/timeplanner/slots/available-dates/',
        function(data) {
            if (Array.isArray(data.data)) {
                const dates = data.data;

                for (var i = 0; i < dates.length; i++) {
                    $select.append('<option value="' + dates[i] + '">' + dates[i] + '</option>');
                }

                $select.prop('disabled', false);
            }
        }
    );
}

timePlanner.resetTimeSelect = function () {
    const $select = $(timePlanner.appointmentTimeInputSelector);
        
    $select.prop('disabled', 'disabled');
    $select.empty();
    $select.append('<option selected disabled>Escoge una hora</option>');
}

timePlanner.reloadTimeSelect = function () {
    timePlanner.resetTimeSelect();
    
    const $select = $(timePlanner.appointmentTimeInputSelector);
    
    $.post(
        autoconf.APP_URL + '/api/timeplanner/slots/available-times/',
        JSON.stringify({
            query: $(timePlanner.appointmentDateInputSelector).val()
        }),
        function(data) {
            if (Array.isArray(data.data)) {
                times = data.data;

                for (var i = 0; i < times.length; i++) {
                    $select.append('<option value="' + times[i] + '">' + times[i] + '</option>');
                }
                
                $select.prop('disabled', false);
            }
        }
    );
}

$(() => {
    $(timePlanner.landingModalSelector).on('show.bs.modal', function () {
        timePlanner.reloadDateSelect();
        timePlanner.resetTimeSelect();
    });
    
    $(timePlanner.appointmentDateInputSelector).on('change', function () {
        timePlanner.reloadTimeSelect();
    });
    
    $(timePlanner.landingFormSelector).on('submit', function (event) {
        event.preventDefault();
        
        const formData = auxUtils.jQueryFormToJsonString($(this));
        
        $.post(
            autoconf.APP_URL + '/api/timeplanner/appointments/create/',
            formData
        ).done(
            function(data, status) {
                $(timePlanner.landingModalSelector).modal('hide');
                toast.successes(data.messages);
            }
        ).fail(
            function (data, status) {
                $(timePlanner.landingModalSelector).modal('hide');
                toast.errors(data.responseJSON.messages);
            }
        );
    });
})