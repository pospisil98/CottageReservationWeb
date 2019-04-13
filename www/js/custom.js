/*$(document).ready(function(){
	jQuery(window).load(function() {
		jQuery("#preloader").delay(100).fadeOut("slow");
		jQuery("#load").delay(100).fadeOut("slow");
	});
});*/

$('a.reserve-disabled').click(function(){return false;});

$(".modalWindow").iziModal();

function numberWithSpaces(x) {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, " ");
}

function triggerChangePriceModal(from, to, price) {
	event.preventDefault();
	$('.modalWindow').iziModal('open');

	var fromTimestamp = from;
	var toTimestamp = to;
	var fromDate = new Date(fromTimestamp*1000);
	var toDate = new Date(toTimestamp*1000);

	var fromReadable = fromDate.getDate() + ". "+ (fromDate.getMonth() + 1) + ". " + fromDate.getFullYear();
	var toReadable = toDate.getDate() + ". "+ (toDate.getMonth() + 1) + ". " + toDate.getFullYear();

	$( "#hiddenFrom" ).val(fromTimestamp);
	$( "#hiddenTo" ).val(toTimestamp);
	$( "#fromReadable" ).html(fromReadable);
	$( "#toReadable" ).html(toReadable);
	$( "#price" ).html(numberWithSpaces(price) + " Kč");
}

function triggerReserveModal(from, to) {
	event.preventDefault();
	$('.modalWindow').iziModal('open');

	var fromTimestamp = from;
	var toTimestamp = to;
	var fromDate = new Date(fromTimestamp*1000);
	var toDate = new Date(toTimestamp*1000);

	var fromReadable = fromDate.getDate() + ". "+ (fromDate.getMonth() + 1) + ". " + fromDate.getFullYear();
	var toReadable = toDate.getDate() + ". "+ (toDate.getMonth() + 1) + ". " + toDate.getFullYear();

	$( "#hiddenFrom" ).val(fromTimestamp);
	$( "#hiddenTo" ).val(toTimestamp);
	$( "#fromReadable" ).html(fromReadable);
	$( "#toReadable" ).html(toReadable);
}

function triggerStornoModal(from, to) {
	event.preventDefault();
	$('#stornoModal').iziModal('open');

    var fromTimestamp = from;
	var toTimestamp = to;

    $( "#hiddenFromStorno" ).val(fromTimestamp);
	$( "#hiddenToStorno" ).val(toTimestamp);
}

$(function() {
	$(document).on('click', '.navbar-nav li a', function(event){

		$('html, body').animate({
			scrollTop: $( $.attr(this, 'href') ).offset().top
		}, 1500);
	});

	$(document).on('click', '.page-scroll a', function(event){
		event.preventDefault();

		$('html, body').animate({
			scrollTop: $( $.attr(this, 'href') ).offset().top
		}, 1500);
	});
});

//navbar heading scroll to top
$("h1").click(function(){
	$("html, body").animate({
		scrollTop: 0 }, 1500);
	return false;
});

var pathname = window.location.pathname;
var substring = "reserve";
var substring1 = "succes";
var substring2 = "failure";
var substring3 = "manage";
if(pathname.indexOf(substring) !== -1 | pathname.indexOf(substring1) !== -1 | pathname.indexOf(substring2) !== -1 | pathname.indexOf(substring3) !== -1){
	$(".navbar-fixed-top").addClass("top-nav-collapse");
} else {
	//jQuery to collapse the navbar on scroll
	$(window).scroll(function() {
		if ($(".navbar").offset().top > 50) {
			$("#heading").fadeIn();
			$(".navbar-fixed-top").addClass("top-nav-collapse");
		} else {
			$(".navbar-fixed-top").removeClass("top-nav-collapse");
			$("#heading").fadeOut();
		}
	});
}
