(function($){

    $.asyncsearch = {};
    var resultHandlers = [];

    (function(_) {

        _.addProvider = function( name, resultHandler ) {
            resultHandlers.push( {
                'name': name,
                'handler': resultHandler
            });
        };

        _.executeSearch = function() {
            var searchPlugin = this;
            $.post( DOKU_BASE + '/lib/exe/ajax.php', {
                'call': 'asyncsearch',
                'pluginID': searchPlugin.name,
                'term': $('#asyncsearch').data('term')
            } ).success( function( data ) {
                searchPlugin.handler.call( $('#asyncsearch'), data);
                _.start();
            } );
        };

        _.start = function() {
            if ( resultHandlers.length > 0 ) {
                $('.asyncsearch.wave').addClass('show');
                _.executeSearch.call( resultHandlers.shift() );
            } else {
                window.setTimeout( function() {
                    $('.asyncsearch.wave').removeClass('show');
                }, 1000);
            }
        };

        _.appendSpinner = function( $anchor ) {
            var $spinnerContainer = $('<div></div>').addClass('loader').appendTo($('<div></div>').addClass('asyncsearch wave').appendTo( $anchor ));
            $.each( new Array(10), function( idx ) {
                $('<div></div>').addClass('line').css('animation-delay', ((idx+1)/10) + 's' ).appendTo( $spinnerContainer );
            });
            
            return $spinnerContainer;
        };
        
        _.init = function() {
            _.appendSpinner( $($('.page :header').get(0)) ).css('margin-left', '1em');
            _.appendSpinner( $('<center></center>').appendTo( $('#asyncsearch').parent() ) );
            _.start();
        };

    })($.asyncsearch);

    $($.asyncsearch.init);

})(jQuery);

/** Provider for the QuickPages Search **/
jQuery.asyncsearch.addProvider( 'pagelookup', function( data ){
    jQuery('<div></div>').html(data).appendTo(this);
});

/** Provider for the Page Search **/
jQuery.asyncsearch.addProvider( 'pagesearch', function( data ){
    jQuery('<div></div>').html(data).appendTo(this);
});
