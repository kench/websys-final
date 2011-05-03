$(document).ready(function() {
 	$('#content a').click(clickHandler);
});

function clickHandler(e)
{
	var track_url = "click_track.php?url=" + this.href;
	
	window.open(track_url, '_newtab');
	e.preventDefault();
}
