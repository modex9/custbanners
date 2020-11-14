$(document).on('ready', () =>
    {
        $('li .manage_translations').on('click', () => {
            $(event.target).parents('ul.dropdown-menu').css({"display" : "block"});
        });
    }
);