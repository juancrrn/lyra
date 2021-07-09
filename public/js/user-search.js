/**
 * User search-related JavaScript functionality
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
var userSearch = {};

userSearch.formClass = 'user-search-form';
userSearch.formSelector = '.' + userSearch.formClass;
userSearch.inputSelector = userSearch.formSelector + ' input[type="search"]';
userSearch.resultsDivClass = 'results';
userSearch.resultsDivSelector = '.' + userSearch.resultsDivClass;

userSearch.constructResultsItem = function (data, templateLink)
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

userSearch.constructEmptyResultsItem = function ()
{
    let $item = $(document
        .querySelector('#common-user-search-form-results-item-empty')
        .content.firstElementChild.cloneNode(true));

    return $item;
}

$(() => {
    $(userSearch.formSelector).on('submit', function (event) {
        event.preventDefault();
    });

    $(userSearch.inputSelector).on('keypress', function () {
        const $input = $(this);
        const $form = $input.closest(userSearch.formSelector);
        const $resultsDiv = $form.find(userSearch.resultsDivSelector);
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
                        $resultsDiv.append(userSearch.constructResultsItem(users[i], targetUrl));
                    }
                } else {
                    $resultsDiv.append(userSearch.constructEmptyResultsItem());
                }
            }
        }).fail(function (data, status) {
            toast.fail();
        });
    })
})