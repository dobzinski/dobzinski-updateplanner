$(document).ready(function() {
    var logos = ['-week', '-event', '-range', ''];
    var index = 0;
    if ($('#logo').length) {
        setInterval(function() {
            $("#logo").html('<use xlink:href="#bi-calendar4'+ logos[index] +'"/>');
            index = (index + 1) % logos.length;
        }, 1000);
    }
});