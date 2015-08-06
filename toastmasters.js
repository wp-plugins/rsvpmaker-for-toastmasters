jQuery(document).ready(function($) {
$("div.role-block").mouseenter(function(){
var updateid = '#update' + $(this).attr('id');
$(this).css("border","thin solid yellow");
$(updateid).slideDown("slow");
});
$("div.role-block").mouseleave(function(){
$(this).css("border","none");
});
});
