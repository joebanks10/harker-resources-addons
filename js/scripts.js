(function($){

    $(window).load(function(){ 

        var $label = $('.dataTables_filter label'),
            $input = $('.dataTables_filter input');

        $input.attr('placeholder', 'Search With Any Keyword');
        $label.html($input);

    });

})(jQuery);
