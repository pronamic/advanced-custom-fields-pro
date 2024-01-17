/******/ (() => { // webpackBootstrap
var __webpack_exports__ = {};
/*!*********************************************************************************!*\
  !*** ./src/advanced-custom-fields-pro/assets/src/js/acf-escaped-html-notice.js ***!
  \*********************************************************************************/
(function ($, undefined) {
  const $notice = $('.acf-escaped-html-notice');
  $notice.on('click', '.notice-dismiss', function (e) {
    const $target = $(e.target).closest('.acf-escaped-html-notice');
    let to_dismiss = 'escaped_html';
    if ($target.hasClass('acf-will-escape')) {
      to_dismiss = 'to_be_escaped';
    }
    $.ajax({
      url: ajaxurl,
      data: {
        'action': 'acf/dismiss_escaped_html_notice',
        'nonce': acf_escaped_html_notice.nonce,
        'notice': to_dismiss
      },
      type: 'post'
    });
  });
  $notice.on('click', '.acf-show-more-details', function (e) {
    e.preventDefault();
    const $link = $(e.target);
    const $details = $link.closest('.acf-escaped-html-notice').find('.acf-error-details');
    if ($details.is(':hidden')) {
      $details.slideDown(100);
      $link.text(acf_escaped_html_notice.hide_details);
    } else {
      $details.slideUp(100);
      $link.text(acf_escaped_html_notice.show_details);
    }
  });
})(jQuery);
/******/ })()
;
//# sourceMappingURL=acf-escaped-html-notice.js.map