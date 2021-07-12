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

/*
 * 
 * User permission group lists
 * 
 */

user.permissionGroupList = {};

user.permissionGroupList.selector = '.permission-group-list-editable';
user.permissionGroupList.inputSelector = '.permission-group-list-search';
user.permissionGroupList.searchItemSelector = '.permission-group-search-item';
user.permissionGroupList.resultsSelector = '.permission-group-list-search-results';
user.permissionGroupList.itemSelector = '.permission-group-list-item';
user.permissionGroupList.itemDeleteBtnSelector = user.permissionGroupList.itemSelector + ' .permission-group-list-delete-this-btn';

user.permissionGroupList.itemTemplateSelector = '#app-manager-user-part-permission-group-list-item-editable';
user.permissionGroupList.itemEmptyTemplateSelector = '#app-manager-user-part-permission-group-list-item-empty';
user.permissionGroupList.itemEmptySelector = '.permission-group-list-item-empty';
user.permissionGroupList.searchItemTemplateSelector = '#app-manager-user-part-permission-group-search-item';
user.permissionGroupList.searchItemEmptyTemplateSelector = '#app-manager-user-part-permission-group-search-item-empty';


user.permissionGroupList.constructItem = function (dataItem, checkBoxName)
{
    let $clone = $(document
        .querySelector(user.permissionGroupList.itemTemplateSelector)
        .content.firstElementChild.cloneNode(true));

    console.log(dataItem);

    $clone.attr('data-permission-group-id', dataItem.id);
    $clone.find('input[name=\'' + checkBoxName + '[]\']').val(dataItem.id);
    $clone.find('.t-full-name').text(dataItem.fullName);
    $clone.find('.t-description').text(dataItem.description);

    return $clone;
}

user.permissionGroupList.clearAllResultsLists = function ()
{
    $(user.permissionGroupList.resultsSelector).removeClass('d-block').addClass('d-none');
    $(user.permissionGroupList.resultsSelector + ' ul.list-group').empty();
}

user.permissionGroupList.constructSearchItem = function (dataItem)
{
    let $clone = $(document
        .querySelector(user.permissionGroupList.searchItemTemplateSelector)
        .content.firstElementChild.cloneNode(true));

    $clone.attr('data-permission-group-serialized', JSON.stringify(dataItem));
    $clone.attr('data-permission-group-id', dataItem.id);
    $clone.find('.t-full-name').text(dataItem.fullName);
    $clone.find('.t-description').text(dataItem.description);

    return $clone;
}

user.permissionGroupList.validateNotAlreadyAdded = function (dataItem, $targetList)
{
    const listItems = $targetList.find(user.permissionGroupList.itemSelector);

    for (i = 0; i < listItems.length; i++) {
        if ($(listItems[i]).data('permission-group-id') == dataItem.id) return false;
    }
    
    return true;
}

$(() => {
    $(user.permissionGroupList.selector).on('click', user.permissionGroupList.itemDeleteBtnSelector, (event) => {
        $target = $(event.target);
        $list = $target.closest(user.permissionGroupList.selector);
        
        $target.closest(user.permissionGroupList.itemSelector).remove();

        if ($list.find(user.permissionGroupList.itemSelector).length == 0) {
            var template = document.querySelector(user.permissionGroupList.itemEmptyTemplateSelector);
            var clone = template.content.firstElementChild.cloneNode(true);
            $list[0].appendChild(clone);
        }
    });
    
    $(user.permissionGroupList.inputSelector).on('keyup focus', (event) => {
        const $target = $(event.target);
        const requestUrl = $target.data('query-url');
        const query = $target.val();

        $targetList = $('#' + $target.data('target-list'));
        $targetResults = $('#' + $target.data('target-results'));
        $targetResultsList = $('#' + $target.data('target-results') + ' ul.list-group');

        if (requestUrl && query) {
            $.post({
                url: requestUrl,
                contentType: 'application/json; charset=utf-8',
                dataType: 'json',
                data: JSON.stringify({
                    'query': query
                }),
                success: (result) => {
                    $targetResults.css('width', $target.outerWidth());
                    $targetResults.removeClass('d-none').addClass('d-block');
                    $targetResultsList.empty();

                    if (result.data.length == 0) {
                        var template = document.querySelector(user.permissionGroupList.searchItemEmptyTemplateSelector);
                        var clone = template.content.firstElementChild.cloneNode(true);
                        $targetResultsList[0].appendChild(clone);
                    } else {
                        for (let i = 0; i < result.data.length; i++) {
                            $targetResultsList.append(user.permissionGroupList.constructSearchItem(result.data[i]));
                        }
                    }
                }
            });
        }
    });

    $('*').not(
        user.permissionGroupList.resultsSelector + ', ' +
        user.permissionGroupList.resultsSelector + ' *, ' +
        user.permissionGroupList.inputSelector
    ).on('click', (event) => {
        user.permissionGroupList.clearAllResultsLists();
    });

    $(user.permissionGroupList.resultsSelector).on('click', user.permissionGroupList.searchItemSelector, function () {
        const $resultsList = $(this).closest(user.permissionGroupList.resultsSelector);
        var $targetList = $('#' + $resultsList.data('target-list'));
        const dataItem = $(this).data('permission-group-serialized');

        if (user.permissionGroupList.validateNotAlreadyAdded(dataItem, $targetList)) {
            const checkBoxName = $targetList.data('checkbox-name');

            $targetList.find(user.permissionGroupList.itemEmptySelector).remove();

            $targetList.append(user.permissionGroupList.constructItem(dataItem, checkBoxName));

            user.permissionGroupList.clearAllResultsLists();
        } else {
            toast.error('El grupo de permisos seleccionado ya estaba añadido.');
        }
    });
})