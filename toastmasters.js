jQuery(document).ready(function($) {
    // Inside of this function, $() will work as an alias for jQuery()
    // and other libraries also using $ will not be accessible under this shortcut

  $("div.role-block").mouseover(function(){
   $("div.update_form").slideDown("slow");
  });

});
