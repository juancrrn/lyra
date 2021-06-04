/**
 * Bookbank-related JavaScript functionality
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
    function handleRequestAndLotFormStatusChange(trigger, type)
    {
        const value = $(trigger).val();

        var container;

        if (type == 'create') {
            container = document.querySelector('#bookbank-manager-request-and-lot-create-form-associated-lot-container');
        } else if (type == 'edit') {
            container = document.querySelector('#bookbank-manager-request-and-lot-edit-form-associated-lot-container');
        }

        var button = container.querySelector('.accordion-button');
        var collapse = container.querySelector('.accordion-collapse');

        if (value == 'book_request_status_processed') {
            // Enable and expand
            button.classList.remove('collapsed');
            button.setAttribute('data-bs-target', '#bookbank-manager-associated-lot-accordion-body');
            button.setAttribute('aria-controls', 'bookbank-manager-associated-lot-accordion-body');
            button.setAttribute('aria-expanded', true);
            button.removeAttribute('disabled');
            collapse.classList.add('show');
        } else {
            // Disable and collapse
            button.classList.add('collapsed');
            button.removeAttribute('data-bs-target');
            button.removeAttribute('aria-controls');
            button.removeAttribute('aria-expanded');
            button.setAttribute('disabled', 'disabled');
            collapse.classList.remove('show');
        }
    }

    $('#bookbank-manager-request-and-lot-edit-form-status').on('change', function (event) {
        handleRequestAndLotFormStatusChange(this, 'edit');
    });

    $('#bookbank-manager-request-and-lot-create-form-status').on('change', function (event) {
        handleRequestAndLotFormStatusChange(this, 'create');
    });
});