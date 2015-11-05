$(document).ready(function() {
	$.ajaxSetup({ cache: false });
	$("#responsecontainer").load(refreshTable());
	
	$('input[id="autoupdate"]').click(toggleRefresh);	
})

var intID = setTimer();
function setTimer() {
	i = setInterval(function() { $("#responsecontainer").load(refreshTable()); }, 5000);
	return i;
}
function stopTimer() {
	clearInterval(intID);
}	
function toggleRefresh(){
	if($(this).is(":checked")){
		alert("Auto-update has been reapplied.");
		setTimer();
	}
	else if($(this).is(":not(:checked)")){
		alert("Auto-update has been stopped.");
		stopTimer();
	}
}
/* function srchPost(srch) {
	var $srchtype = (srch);
	
	var form = $(this).closest("form");
	form.submit();

}; */