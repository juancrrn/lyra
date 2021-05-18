$(() => {
    $('.search-autocomplete').on('keyup', (event) => {
        const $target = $(event.target);
        const requestUrl = $target.data('search-request-url');
        const query = $target.val();

        if (requestUrl && query) {
            $.post({
                url: requestUrl,
                contentType: 'application/json; charset=utf-8',
                dataType: 'json',
                data: JSON.stringify({
                    'query': query
                }),
                success: (result) => {
                    console.log(result);
                }
            });
        }
    });
});