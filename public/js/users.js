/**
 * User-related JavaScript functionality
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
var user = {};

/*
 * 
 * User edit
 * 
 */

user.edit = {};

user.edit.resetPasswordBtnId = 'app-manager-user-edit-form-reset-password-btn';
user.edit.resetPasswordBtnSelector = '#' + user.edit.resetPasswordBtnId;

$(() => {
    $(user.edit.resetPasswordBtnSelector).on('click', function (event) {
        event.preventDefault();

        let $button = $(this);

        $button.find('.spinner-border').removeClass('d-none');
        $button.prop('disabled', true);

        const targetUrl = $button.data('target-url');
        const userId = $button.data('user-id');

        $.post(
            targetUrl,
            JSON.stringify({
                id: userId
            })
        ).done(
            function(data, status) {
                if (! Array.isArray(data.messages)) {
                    toast.fail()
                } else {
                    toast.successes(data.messages);
                }
                
                $button.find('.spinner-border').addClass('d-none');
                $button.prop('disabled', false);
            }
        ).fail(
            function (data, status) {
                if (! Array.isArray(data.messages)) {
                    toast.fail()
                } else {
                    toast.successes(data.messages);
                }
                
                $button.find('.spinner-border').addClass('d-none');
                $button.prop('disabled', false);
            }
        );
    })
})

/*
 * 
 * User search
 * 
 */

user.search = {};

user.search.formClass = 'user-search-form';
user.search.formSelector = '.' + user.search.formClass;
user.search.inputSelector = user.search.formSelector + ' input[type="search"]';
user.search.resultsDivClass = 'results';
user.search.resultsDivSelector = '.' + user.search.resultsDivClass;

user.search.constructResultsItem = function (data, templateLink)
{
    let $item = $(document
        .querySelector('#common-user-search-form-results-item')
        .content.firstElementChild.cloneNode(true));

    $item.find('.t-text-full-name').text(data.fullName);

    if (data.govId == null) {
        $item.find('.t-text-gov-id').text('(Vacío)');
    } else {
        $item.find('.t-text-gov-id').text(data.govId.toUpperCase());
    }

    console.log($item.find('.t-href-target-link'));

    $item.attr('href', templateLink.replace('{id}', data.id)); // .find('.t-href-target-link')

    return $item;
}

user.search.constructEmptyResultsItem = function ()
{
    let $item = $(document
        .querySelector('#common-user-search-form-results-item-empty')
        .content.firstElementChild.cloneNode(true));

    return $item;
}

$(() => {

    $(user.search.formSelector).on('submit', function (event) {
        event.preventDefault();
    });

    $(user.search.inputSelector).on('keypress', function () {
        const $input = $(this);
        const $form = $input.closest(user.search.formSelector);
        const $resultsDiv = $form.find(user.search.resultsDivSelector);
        const queryUrl = $form.data('query-url');
        const targetUrl = $form.data('target-url');

        $.post(
            queryUrl,
            JSON.stringify({
                query: $input.val()
            })
        ).done(function (data, status) {
            if (! Array.isArray(data.data)) {
                toast.fail();
            } else {
                const users = data.data;
                
                $resultsDiv.empty();

                if (users.length > 0) {
                    for (let i = 0; i < users.length; i++) {
                        $resultsDiv.append(user.search.constructResultsItem(users[i], targetUrl));
                    }
                } else {
                    $resultsDiv.append(user.search.constructEmptyResultsItem());
                }
            }
        }).fail(function (data, status) {
            toast.fail();
        });
    })
})