/**
 * JavaScript de mensajes toast
 * 
 * Usando el símbolo $ para variables de tipo objeto de jQuery.
 *
 * @package lyra
 *
 * @author juancrrn
 *
 * @version 0.0.1
 */

/**
 * Objeto para namespace.
 */
toast = {};

/**
 * Creates a toast
 */
toast.create = (type, text) =>
{
    const template = document.querySelector('template#toast');
    var $toast = $(template.content.firstElementChild.cloneNode(true));

    $toast.attr('data-bs-autohide', autoconf.APP_PRODUCTION);
    $toast.find('.type-indicator').addClass(type);
    $toast.find('.lyra-toast-app-name').html(autoconf.APP_NAME);
    $toast.find('.toast-body').html(text);

    return $toast;
}

/**
 * Creates and shows a success toast
 */
toast.success = (text) =>
{
    var $toast = toast.create('exito', text);
    $('#toasts-container').prepend($toast);
    $toast.toast('show');
}

/**
 * Creates and shows an error toast
 */
toast.error = (text) =>
{
    var $toast = toast.create('error', text);
    $('#toasts-container').prepend($toast);
    $toast.toast('show');
}

/**
 * jQuery on document ready
 */
$(() => {
    /**
     * Inicializar toasts.
     */
    $('#toasts-container .toast').toast('show');

    /**
     * Ocultar todos los toast.
     */
    $(document).on('click', '.toasts-remove-all', () => {
        $('#toasts-container .toast').fadeOut(300, function() {
            $(this).remove();
        });
    });
});