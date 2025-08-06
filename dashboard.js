$(document).ready(function() {
  $('.menu-item').click(function() {
    $(this).next('.submenu').slideToggle(200);
  });
});
