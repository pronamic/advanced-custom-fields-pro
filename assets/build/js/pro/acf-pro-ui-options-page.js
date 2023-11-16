/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./src/advanced-custom-fields-pro/assets/src/js/pro/_acf-ui-options-page.js":
/*!**********************************************************************************!*\
  !*** ./src/advanced-custom-fields-pro/assets/src/js/pro/_acf-ui-options-page.js ***!
  \**********************************************************************************/
/***/ (() => {

(function ($, undefined) {
  const parentPageSelectTemplate = function (selection) {
    if ('undefined' === typeof selection.element) {
      return selection;
    }

    // Hides the optgroup for the "No Parent" option.
    if (selection.children && 'None' === selection.text) {
      return;
    }
    if ('acfOptionsPages' === selection.text) {
      selection.text = acf.__('Options Pages');
    }
    return $('<span class="acf-selection"></span>').data('element', selection.element).html(acf.escHtml(selection.text));
  };
  const defaultPillTemplate = function (selection) {
    if ('undefined' === typeof selection.element) {
      return selection;
    }
    const $selection = $('<span class="acf-selection"></span>');
    $selection.html(acf.escHtml(selection.element.innerHTML));
    if (selection.id === 'options' || selection.id === 'edit_posts') {
      $selection.append('<span class="acf-select2-default-pill">' + acf.__('Default') + '</span>');
    }
    $selection.data('element', selection.element);
    return $selection;
  };
  const UIOptionsPageManager = new acf.Model({
    id: 'UIOptionsPageManager',
    wait: 'ready',
    events: {
      'change .acf-options-page-parent_slug': 'toggleMenuPositionDesc'
    },
    initialize: function () {
      if ('ui_options_page' !== acf.get('screen')) {
        return;
      }
      acf.newSelect2($('select.acf-options-page-parent_slug'), {
        field: false,
        templateSelection: parentPageSelectTemplate,
        templateResult: parentPageSelectTemplate,
        dropdownCssClass: 'field-type-select-results'
      });
      acf.newSelect2($('select.acf-options-page-capability'), {
        field: false,
        templateSelection: defaultPillTemplate,
        templateResult: defaultPillTemplate
      });
      acf.newSelect2($('select.acf-options-page-data_storage'), {
        field: false,
        templateSelection: defaultPillTemplate,
        templateResult: defaultPillTemplate
      });
      this.toggleMenuPositionDesc();
    },
    toggleMenuPositionDesc: function (e, $el) {
      const parentPage = $('select.acf-options-page-parent_slug').val();
      if ('none' === parentPage) {
        $('.acf-menu-position-desc-child').hide();
        $('.acf-menu-position-desc-parent').show();
      } else {
        $('.acf-menu-position-desc-parent').hide();
        $('.acf-menu-position-desc-child').show();
      }
    }
  });
  const optionsPageModalManager = new acf.Model({
    id: 'optionsPageModalManager',
    events: {
      'change .location-rule-value': 'createOptionsPage'
    },
    createOptionsPage: function (e) {
      const $locationSelect = $(e.target);
      if ('add_new_options_page' !== $locationSelect.val()) {
        return;
      }
      let popup = false;
      const getForm = function () {
        const fieldGroupTitle = $('.acf-headerbar-title-field').val();
        const ajaxData = {
          action: 'acf/create_options_page',
          acf_parent_page_choices: this.acf.data.acfParentPageChoices ? this.acf.data.acfParentPageChoices : []
        };
        if (fieldGroupTitle.length) {
          ajaxData.field_group_title = fieldGroupTitle;
        }
        $.ajax({
          url: acf.get('ajaxurl'),
          data: acf.prepareForAjax(ajaxData),
          type: 'post',
          dataType: 'json',
          success: populateForm
        });
      };
      const populateForm = function (response) {
        popup = acf.newPopup({
          title: response.data.title,
          content: response.data.content,
          width: '600px'
        });
        popup.$el.addClass('acf-create-options-page-popup');

        // Hack to focus with the cursor at the end of the input.
        const $pageTitle = popup.$el.find('#acf_ui_options_page-page_title');
        const pageTitleVal = $pageTitle.val();
        $pageTitle.focus().val('').val(pageTitleVal);
        acf.newSelect2($('#acf_ui_options_page-parent_slug'), {
          field: false,
          templateSelection: parentPageSelectTemplate,
          templateResult: parentPageSelectTemplate,
          dropdownCssClass: 'field-type-select-results'
        });
        popup.on('submit', 'form', validateForm);
      };
      const validateForm = function (e) {
        e.preventDefault();
        acf.validateForm({
          form: $('#acf-create-options-page-form'),
          success: submitForm,
          failure: onFail
        });
      };
      const submitForm = function () {
        const formValues = $('#acf-create-options-page-form').serializeArray();
        const ajaxData = {
          action: 'acf/create_options_page'
        };
        formValues.forEach(setting => {
          ajaxData[setting.name] = setting.value;
        });
        $.ajax({
          url: acf.get('ajaxurl'),
          data: acf.prepareForAjax(ajaxData),
          type: 'post',
          dataType: 'json',
          success: populateLocationSelect
        });
      };
      const onFail = function (e) {
        const $form = $('#acf-create-options-page-form');
        const $fieldNotices = $form.find('.acf-field .acf-error-message');

        // Hide the general validation failed notice.
        $form.find('.acf-notice').first().remove();

        // Update class for inline notices and move into field label.
        $fieldNotices.each(function () {
          const $label = $(this).closest('.acf-field').find('.acf-label:first');
          $(this).attr('class', 'acf-options-page-modal-error').appendTo($label);
        });
      };
      const populateLocationSelect = function (response) {
        if (response.success && response.data.menu_slug) {
          $locationSelect.prepend('<option value="' + response.data.menu_slug + '">' + response.data.page_title + '</option>');
          $locationSelect.val(response.data.menu_slug);
          popup.close();
        } else if (!response.success && response.data.error) {
          alert(response.data.error);
        }
      };
      getForm();
    }
  });
})(jQuery);

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry need to be wrapped in an IIFE because it need to be in strict mode.
(() => {
"use strict";
/*!*************************************************************************************!*\
  !*** ./src/advanced-custom-fields-pro/assets/src/js/pro/acf-pro-ui-options-page.js ***!
  \*************************************************************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _acf_ui_options_page__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./_acf-ui-options-page */ "./src/advanced-custom-fields-pro/assets/src/js/pro/_acf-ui-options-page.js");
/* harmony import */ var _acf_ui_options_page__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_acf_ui_options_page__WEBPACK_IMPORTED_MODULE_0__);

})();

/******/ })()
;
//# sourceMappingURL=acf-pro-ui-options-page.js.map