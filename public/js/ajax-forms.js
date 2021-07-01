/**
 * AJAX forms-related JavaScript functionality
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
 * jQuery on document ready
 */
 $(() => {
    /**
     * Loading progress bar.
     */
    var $loadingProgressBar = $('#loading-progress-bar');

    /**
     * AJAX form initialization.
     */
    $(document).on('click', '.btn-ajax-modal-fire', (e) => {
        var $btn = $(e.currentTarget);

        // Get formId.
        var formId = $btn.data('ajax-form-id');

        var $modal = $('.ajax-modal[data-ajax-form-id="' + formId + '"]');
        var url = $modal.data('ajax-submit-url');

        // Check that modal and URL exist
        if (url) {
            $loadingProgressBar.fadeIn();

            // Get formId
            var data = {
                "form-id": formId,
            };

            /**
             * uniqueId represents the id of the record to read, update or
             * delete; it is optional (i. e. in creation forms)
             */
            var uniqueId = $btn.data('ajax-unique-id');
            if (uniqueId) {
                data.uniqueId = uniqueId;
            }

            // Load default data and token
            $.ajax({
                url: url,
                type: 'GET',
                contentType: 'application/json; charset=utf-8',
                dataType: 'json',
                data: data,
                success: (result) => {
                    if (! autoconf.APP_PRODUCTION) {
                        console.log('#btn-ajax-modal-fire click AJAX success');
                        console.log(result);
                    }

                    // Pre-empty form
                    auxUtils.doEmptyForm($modal);

                    // Array for late prevention
                    var linkRels = [];

                    // Get data payload (named with the target object's name)
                    var resultData = result[$modal.data('ajax-target-object-name')];

                    // Check if foreign attribute links were provided
                    if (result.links) {
                        result.links.forEach(link => {
                            linkRels.push(link.rel);

                            // First populate the select
                            var $select = $modal.find('select[name="' + link.rel + '"]');

                            link.data.forEach(value => {
                                const option = '<option value="' + value.uniqueId + '">' + value.selectName + '</option>';
                                $select.append(option);
                            });

                            // Then select the option by id (same for single and multi selects) (first check if any object data was sent)
                            if (resultData) {
                                $modal.find('select[name="' + link.rel + '"]').val(resultData[link.rel]);
                            }
                        });
                    }

                    // Fill form id and CSRF token
                    $modal.find('input[name="form-id"]').val(result['form-id']);
                    $modal.find('input[name="csrf-token"]').val(result['csrf-token']);
                    
                    // Fill form placeholder inputs
                    for (const name in resultData) {
                        // Prevent from filling linked inputs (selects)
                        if ($.inArray(name, linkRels) === -1) {
                            $modal.find('input[name="' + name + '"], textarea[name="' + name + '"]').val(resultData[name]);
                        }
                    }
                    
                    // Hide loader and show modal.
                    $loadingProgressBar.fadeOut();

                    // Show the modal.

                    $modal.modal('show');
                },
                error: (result) => {
                    // Hide loader and log error
                    $loadingProgressBar.fadeOut();

                    if (! autoconf.APP_PRODUCTION) {
                        console.log('#btn-ajax-modal-fire click AJAX error');
                        console.error(result);
                    }
                }
            });
        }
    });

    /**
     * AJAX form submit processing
     */
    $('.ajax-modal form').on('submit', (e) => {
        e.preventDefault();
        
        $loadingProgressBar.fadeIn();

        const $form = $(e.currentTarget);
        const formDataJson = auxUtils.jQueryFormToJsonString($form);
        const $modal = $form.closest('.ajax-modal');
        
        const onSuccessEventName = $modal.data('ajax-on-success-event-name');
        const onSuccessEventTarget = $modal.data('ajax-on-success-event-target');
        const submitUrl = $modal.data('ajax-submit-url');
        const submitMethod = $modal.data('ajax-submit-method');

        $.ajax({
            url: submitUrl,
            type: submitMethod,
            contentType: 'application/json; charset=utf-8',
            dataType: 'json',
            data: formDataJson,
            success: (result) => {
                if (! autoconf.APP_PRODUCTION) {
                    console.log('.ajax-modal form submit AJAX success');
                    console.log(result);
                }

                // Copy the modal so data is not emptied on hidden.bs.modal
                const $modalData = $modal;

                // Trigger success event on the target
                if (onSuccessEventName && onSuccessEventTarget) {
                    $(onSuccessEventTarget).trigger(onSuccessEventName, {
                        modalData: $modalData,
                        result: result
                    });
                    
                    if (! autoconf.APP_PRODUCTION) console.log('Triggering event "' + onSuccessEventName + '" on "' + onSuccessEventTarget + '".');
                }

                $modal.modal('hide');

                $loadingProgressBar.fadeOut();

                if (result.messages) {
                    result.messages.forEach(m => toast.success(m));
                }
            },
            error: (result) => {
                $loadingProgressBar.fadeOut();
                toast.error('Hubo un error al procesar el formulario.');
 
                if (result.responseJSON) {
                    // Refill form id and CSRF token.
                    if (result.responseJSON['csrf-token']) {
                        $modal.find('input[name="csrf-token"]').val(result.responseJSON['csrf-token']);
                    }

                    // Show error messages.
                    if (result.responseJSON.messages) {
                        result.responseJSON.messages.forEach(m => toast.error(m));
                    }
                }

                if (! autoconf.APP_PRODUCTION) {
                    console.log('.ajax-modal form submit AJAX error');
                    console.error(result);
                }
            }
        });
    });

    /**
     * AJAX form modal empty on hide
     */
    $('.ajax-modal').on('hidden.bs.modal', (e) => {
        auxUtils.doEmptyForm($(e.currentTarget));
    });
});