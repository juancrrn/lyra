$(() => {
    $('.subject-list-editable').on('click', '.subject-list-item .subject-list-delete-this-btn', (event) => {
        $targetList = $(event.target);
        
        $targetList.closest('.subject-list-item').remove();

        if ($targetList.is(':empty')) {
            // TODO Añadir objeto vacío
        }
    });
});