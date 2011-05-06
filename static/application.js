$(document).ready( function() {
 	$('#content a').click( function( e ) {
        var track_url = "click_track.php?url=" + escape( this.href );
        
        window.open(track_url, '_newtab');
        e.preventDefault();
    });
});
