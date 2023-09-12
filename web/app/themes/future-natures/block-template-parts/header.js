jQuery(document).ready(function () {
  let url = window.location.pathname.split("/")[1];

  let menu_links = jQuery(".header-desktop ul li a");

  if (menu_links.length > 0) {
    for (let i = 0; i < menu_links.length; i++) {
      if (menu_links[i].href.split("/").pop() === url) {
        menu_links[i].className = "current-page";
      }
    }
  }
});
