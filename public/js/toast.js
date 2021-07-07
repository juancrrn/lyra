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
    $toast.find('.t-class-type').addClass(type);
    $toast.find('.t-app-name').html(autoconf.APP_NAME);
    $toast.find('.t-content').html(text);

    return $toast;
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

toast.errors = (array) =>
{
    for (var i = 0; i < array.length; i++)
        toast.error(array[i]);
}

/**
 * Creates and shows a success toast
 */
toast.success = (text) =>
{
    var $toast = toast.create('success', text);
    $('#toasts-container').prepend($toast);
    $toast.toast('show');
}

toast.successes = (array) =>
{
    for (var i = 0; i < array.length; i++)
        toast.success(array[i]);
}

/**
 * Creates and shows a success toast
 */
toast.warning = (text) =>
{
    var $toast = toast.create('warning', text);
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