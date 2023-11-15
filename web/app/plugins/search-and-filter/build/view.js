/******/ (() => { // webpackBootstrap
var __webpack_exports__ = {};
/*!*********************!*\
  !*** ./src/view.js ***!
  \*********************/
/**
 * Use this file for JavaScript code that you want to run in the front-end
 * on posts/pages that contain this block.
 *
 * When this file is defined as the value of the `viewScript` property
 * in `block.json` it will be enqueued on the front end of the site.
 *
 * Example:
 *
 * ```js
 * {
 *   "viewScript": "file:./view.js"
 * }
 * ```
 *
 * If you're not making any changes to this file because your project doesn't need any
 * JavaScript running in the front-end, then you should delete this file and remove
 * the `viewScript` property from `block.json`.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#view-script
 */

/* eslint-disable no-console */

/* eslint-enable no-console */

document.addEventListener('DOMContentLoaded', function () {
  let categorySelect = document.getElementsByTagName('select')[0];
  categorySelect.addEventListener('change', function () {
    filterPosts();
  });
  function filterPosts() {
    let selectedCategory = categorySelect.value;
    let posts = document.querySelectorAll('li');
    posts.forEach(function (post) {
      let postCategory = post.getAttribute('data-category');
      if (selectedCategory === 'all' || selectedCategory === postCategory) {
        post.style.display = 'block';
      } else {
        post.style.display = 'none';
      }
    });
  }
});
/******/ })()
;
//# sourceMappingURL=view.js.map