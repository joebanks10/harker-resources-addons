(function($){

    $(window).load(function(){ 

        var $table = $('.wpDataTablesWrapper'),
            $label = $('.dataTables_filter label'),
            $input = $('.dataTables_filter input').detach().attr('placeholder', 'Search With Any Keyword'),
            $length = $('.dataTables_length');

        $label.html($input);
        $table.append($length);

    });

})(jQuery);
