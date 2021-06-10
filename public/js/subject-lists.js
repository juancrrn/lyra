$(() => {
    function constructSubjectListItem(dataItem, checkBoxName)
    {
        const template = document.querySelector('#bookbank-common-subject-list-editable-item');
        var $clone = $(template.content.firstElementChild.cloneNode(true));

        $clone.attr('data-subject-id', dataItem.id);
        $clone.find('input[name=\'' + checkBoxName + '[]\']').val(dataItem.id);
        $clone.find('.t-book-image').css('background-image', 'url(\'' + dataItem.bookImageUrl + '\'');
        $clone.find('.t-title-human').text(dataItem.compoundName);
        $clone.find('.t-book-isbn').text(dataItem.bookIsbn);
        $clone.find('.t-book-name').text(dataItem.bookName);

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
        $clone.find('.t-book-image').css('background-image', 'url(\'' + dataItem.bookImageUrl + '\'');
        $clone.find('.t-title-human').text(dataItem.compoundName);
        $clone.find('.t-book-isbn').text(dataItem.bookIsbn);
        $clone.find('.t-book-name').text(dataItem.bookName);

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
        const educationLevel = $('.subject-list-search-education-level-filter').first().val();

        $targetList = $('#' + $target.data('target-list'));
        $targetResults = $('#' + $target.data('target-results'));
        $targetResultsList = $('#' + $target.data('target-results') + ' ul.list-group');

        if (requestUrl && query) {
            $.post({
                url: requestUrl,
                contentType: 'application/json; charset=utf-8',
                dataType: 'json',
                data: JSON.stringify({
                    'query': query,
                    'educationLevel': educationLevel
                }),
                success: (result) => {
                    console.log($target.width());
                    $targetResults.css('width', $target.outerWidth());
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

    function validateNotAlreadyAdded(dataItem, $targetList)
    {
        const listItems = $targetList.find('li.subject-list-item');

        for (i = 0; i < listItems.length; i++) {
            if ($(listItems[i]).data('subject-id') == dataItem.id) return false;
        }
        
        return true;
    }

    $('.subject-list-search-results').on('click', '.subject-search-item', function () {
        const $resultsList = $(this).closest('.subject-list-search-results');
        var $targetList = $('#' + $resultsList.data('target-list'));
        const dataItem = $(this).data('subject-serialized');

        if (validateNotAlreadyAdded(dataItem, $targetList)) {
            const checkBoxName = $targetList.data('checkbox-name');

            $targetList.find('.subject-list-empty-item').remove();

            $targetList.append(constructSubjectListItem(dataItem, checkBoxName));

            clearAllResultsLists();
        } else {
            toast.error('La asignatura seleccionada ya estaba añadida.');
        }
    });

    /**
     * Handle education level changes
     */
    $('.subject-list-search-education-level-filter').on('change', (event) => {
        // TODO
    })
});