$(() => {
    function constructSubjectListItem(dataItem)
    {
        const template = document.querySelector('#bookbank-common-subject-list-editable-item');
        var $clone = $(template.content.firstElementChild.cloneNode(true));

        $clone.attr('data-subject-id', dataItem.id);
        $clone.find('input[name=\'bookbank-manager-donation-edit-form-contents[]\']').val(dataItem.id);
        $clone.find('.t-item-book-image').css('background-image', 'url(\'' + dataItem.bookImageUrl + '\'');
        $clone.find('.t-item-title-human').text(dataItem.compoundName);
        $clone.find('.t-item-book-isbn').text(dataItem.bookIsbn);
        $clone.find('.t-item-book-name').text(dataItem.bookName);

        return $clone;
    }

    function clearAllResultsLists()
    {
        $('.subject-list-search-results').removeClass('d-block').addClass('d-none');
        $('.subject-list-search-results ul.list-group').empty();
    }

    function constructSubjectSearchItem(dataItem)
    {
        const template = document.querySelector('#bookbank-common-subject-search-item');
        var $clone = $(template.content.firstElementChild.cloneNode(true));

        $clone.attr('data-subject-serialized', JSON.stringify(dataItem));
        $clone.attr('data-subject-id', dataItem.id);
        $clone.find('.t-item-book-image').css('background-image', 'url(\'' + dataItem.bookImageUrl + '\'');
        $clone.find('.t-item-title-human').text(dataItem.compoundName);
        $clone.find('.t-item-book-isbn').text(dataItem.bookIsbn);
        $clone.find('.t-item-book-name').text(dataItem.bookName);

        return $clone;
    }

    $('.subject-list-editable').on('click', '.subject-list-item .subject-list-delete-this-btn', (event) => {
        $target = $(event.target);
        $list = $target.closest('.subject-list-editable');
        
        $target.closest('.subject-list-item').remove();

        if (! autoconf.APP_PRODUCTION)
            console.log('Item deleted.');

        if ($list.find('li.subject-list-item').length == 0) {
            var template = document.querySelector('#bookbank-common-subject-list-empty-item');
            var clone = template.content.firstElementChild.cloneNode(true);
            $list[0].appendChild(clone);
        }
    });
    
    $('.subject-list-search').on('keyup focus', (event) => {
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
                    $targetResults.removeClass('d-none').addClass('d-block');
                    $targetResultsList.empty();

                    if (result.data.length == 0) {
                        var template = document.querySelector('#bookbank-common-subject-search-empty-item');
                        var clone = template.content.firstElementChild.cloneNode(true);
                        $targetResultsList[0].appendChild(clone);
                    } else {
                        for (let i = 0; i < result.data.length; i++) {
                            $targetResultsList.append(constructSubjectSearchItem(result.data[i]));
                        }
                    }
                }
            });
        }
    });
    
    $('*').not('.subject-list-search-results, .subject-list-search-results *, .subject-list-search').on('click', (event) => {
        clearAllResultsLists();
    });

    $('.subject-list-search-results').on('click', '.subject-search-item', function () {
        const $resultsList = $(this).closest('.subject-list-search-results');
        var $targetList = $('#' + $resultsList.data('target-list'));
        const dataItem = $(this).data('subject-serialized');
        $targetList.append(constructSubjectListItem(dataItem));

        clearAllResultsLists();
    });
});