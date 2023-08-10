/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./src/advanced-custom-fields-pro/assets/src/js/_browse-fields-modal.js":
/*!******************************************************************************!*\
  !*** ./src/advanced-custom-fields-pro/assets/src/js/_browse-fields-modal.js ***!
  \******************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/defineProperty */ "./node_modules/@babel/runtime/helpers/esm/defineProperty.js");

function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { (0,_babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_0__["default"])(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
/**
 * Extends acf.models.Modal to create the field browser.
 *
 * @package Advanced Custom Fields
 */

(function ($, undefined, acf) {
  const browseFieldsModal = {
    data: {
      openedBy: null,
      currentFieldType: null,
      popularFieldTypes: ['text', 'textarea', 'email', 'url', 'file', 'gallery', 'select', 'true_false', 'link', 'post_object', 'relationship', 'repeater', 'flexible_content', 'clone']
    },
    events: {
      'click .acf-modal-close': 'onClickClose',
      'keydown .acf-browse-fields-modal': 'onPressEscapeClose',
      'click .acf-select-field': 'onClickSelectField',
      'click .acf-field-type': 'onClickFieldType',
      'changed:currentFieldType': 'onChangeFieldType',
      'input .acf-search-field-types': 'onSearchFieldTypes',
      'click .acf-browse-popular-fields': 'onClickBrowsePopular'
    },
    setup: function (props) {
      $.extend(this.data, props);
      this.$el = $(this.tmpl());
      this.render();
    },
    initialize: function () {
      this.open();
      this.lockFocusToModal(true);
      this.$el.find('.acf-modal-title').focus();
      acf.doAction('show', this.$el);
    },
    tmpl: function () {
      return $('#tmpl-acf-browse-fields-modal').html();
    },
    getFieldTypes: function (category, search) {
      let fieldTypes;
      if (!acf.get('is_pro')) {
        // Add in the pro fields.
        fieldTypes = Object.values(_objectSpread(_objectSpread({}, acf.get('fieldTypes')), acf.get('PROFieldTypes')));
      } else {
        fieldTypes = Object.values(acf.get('fieldTypes'));
      }
      if (category) {
        if ('popular' === category) {
          return fieldTypes.filter(fieldType => this.get('popularFieldTypes').includes(fieldType.name));
        }
        if ('pro' === category) {
          return fieldTypes.filter(fieldType => fieldType.pro);
        }
        fieldTypes = fieldTypes.filter(fieldType => fieldType.category === category);
      }
      if (search) {
        fieldTypes = fieldTypes.filter(fieldType => {
          const label = fieldType.label.toLowerCase();
          const labelParts = label.split(' ');
          let match = false;
          if (label.startsWith(search.toLowerCase())) {
            match = true;
          } else if (labelParts.length > 1) {
            labelParts.forEach(part => {
              if (part.startsWith(search.toLowerCase())) {
                match = true;
              }
            });
          }
          return match;
        });
      }
      return fieldTypes;
    },
    render: function () {
      acf.doAction('append', this.$el);
      const $tabs = this.$el.find('.acf-field-types-tab');
      const self = this;
      $tabs.each(function () {
        const category = $(this).data('category');
        const fieldTypes = self.getFieldTypes(category);
        fieldTypes.forEach(fieldType => {
          $(this).append(self.getFieldTypeHTML(fieldType));
        });
      });
      this.initializeFieldLabel();
      this.initializeFieldType();
      this.onChangeFieldType();
    },
    getFieldTypeHTML: function (fieldType) {
      const iconName = fieldType.name.replaceAll('_', '-');
      return `
			<a href="#" class="acf-field-type" data-field-type="${fieldType.name}">
				${fieldType.pro && !acf.get('is_pro') ? '<span class="field-type-requires-pro"><i class="acf-icon acf-icon-lock"></i>PRO</span>' : fieldType.pro ? '<span class="field-type-requires-pro">PRO</span>' : ''}
				<i class="field-type-icon field-type-icon-${iconName}"></i>
				<span class="field-type-label">${fieldType.label}</span>
			</a>
			`;
    },
    decodeFieldTypeURL: function (url) {
      if (typeof url != 'string') return url;
      return url.replaceAll('&#038;', '&');
    },
    renderFieldTypeDesc: function (fieldType) {
      const fieldTypeInfo = this.getFieldTypes().filter(fieldTypeFilter => fieldTypeFilter.name === fieldType)[0] || {};
      const args = acf.parseArgs(fieldTypeInfo, {
        label: '',
        description: '',
        doc_url: false,
        tutorial_url: false,
        preview_image: false,
        pro: false
      });
      this.$el.find('.field-type-name').text(args.label);
      this.$el.find('.field-type-desc').text(args.description);
      if (args.doc_url) {
        this.$el.find('.field-type-doc').attr('href', this.decodeFieldTypeURL(args.doc_url)).show();
      } else {
        this.$el.find('.field-type-doc').hide();
      }
      if (args.tutorial_url) {
        this.$el.find('.field-type-tutorial').attr('href', this.decodeFieldTypeURL(args.tutorial_url)).parent().show();
      } else {
        this.$el.find('.field-type-tutorial').parent().hide();
      }
      if (args.preview_image) {
        this.$el.find('.field-type-image').attr('src', args.preview_image).show();
      } else {
        this.$el.find('.field-type-image').hide();
      }
      const isPro = acf.get('is_pro');
      const $upgateToProButton = this.$el.find('.acf-btn-pro');
      const $upgradeToUnlockButton = this.$el.find('.field-type-upgrade-to-unlock');
      if (args.pro && !isPro) {
        $upgateToProButton.show();
        $upgateToProButton.attr('href', $upgateToProButton.data('urlBase') + fieldType);
        $upgradeToUnlockButton.show();
        $upgradeToUnlockButton.attr('href', $upgradeToUnlockButton.data('urlBase') + fieldType);
        this.$el.find('.acf-insert-field-label').attr('disabled', true);
        this.$el.find('.acf-select-field').hide();
      } else {
        $upgateToProButton.hide();
        $upgradeToUnlockButton.hide();
        this.$el.find('.acf-insert-field-label').attr('disabled', false);
        this.$el.find('.acf-select-field').show();
      }
    },
    initializeFieldType: function () {
      var _fieldObject$data;
      const fieldObject = this.get('openedBy');
      const fieldType = fieldObject === null || fieldObject === void 0 || (_fieldObject$data = fieldObject.data) === null || _fieldObject$data === void 0 ? void 0 : _fieldObject$data.type;

      // Select default field type
      if (fieldType) {
        this.set('currentFieldType', fieldType);
      } else {
        this.set('currentFieldType', 'text');
      }

      // Select first tab with selected field type
      // If type selected is wthin Popular, select Popular Tab
      // Else select first tab the type belongs
      const fieldTypes = this.getFieldTypes();
      const isFieldTypePopular = this.get('popularFieldTypes').includes(fieldType);
      let category = '';
      if (isFieldTypePopular) {
        category = 'popular';
      } else {
        const selectedFieldType = fieldTypes.find(x => {
          return x.name === fieldType;
        });
        category = selectedFieldType.category;
      }
      const uppercaseCategory = category[0].toUpperCase() + category.slice(1);
      const searchTabElement = `.acf-modal-content .acf-tab-wrap a:contains('${uppercaseCategory}')`;
      setTimeout(() => {
        $(searchTabElement).click();
      }, 0);
    },
    initializeFieldLabel: function () {
      const fieldObject = this.get('openedBy');
      const labelText = fieldObject.$fieldLabel().val();
      const $fieldLabel = this.$el.find('.acf-insert-field-label');
      if (labelText) {
        $fieldLabel.val(labelText);
      } else {
        $fieldLabel.val('');
      }
    },
    updateFieldObjectFieldLabel: function () {
      const label = this.$el.find('.acf-insert-field-label').val();
      const fieldObject = this.get('openedBy');
      fieldObject.$fieldLabel().val(label);
      fieldObject.$fieldLabel().trigger('blur');
    },
    onChangeFieldType: function () {
      const fieldType = this.get('currentFieldType');
      this.$el.find('.selected').removeClass('selected');
      this.$el.find('.acf-field-type[data-field-type="' + fieldType + '"]').addClass('selected');
      this.renderFieldTypeDesc(fieldType);
    },
    onSearchFieldTypes: function (e) {
      const $modal = this.$el.find('.acf-browse-fields-modal');
      const inputVal = this.$el.find('.acf-search-field-types').val();
      const self = this;
      let searchString,
        resultsHtml = '';
      let matches = [];
      if ('string' === typeof inputVal) {
        searchString = inputVal.trim();
        matches = this.getFieldTypes(false, searchString);
      }
      if (searchString.length && matches.length) {
        $modal.addClass('is-searching');
      } else {
        $modal.removeClass('is-searching');
      }
      if (!matches.length) {
        $modal.addClass('no-results-found');
        this.$el.find('.acf-invalid-search-term').text(searchString);
        return;
      } else {
        $modal.removeClass('no-results-found');
      }
      matches.forEach(fieldType => {
        resultsHtml = resultsHtml + self.getFieldTypeHTML(fieldType);
      });
      $('.acf-field-type-search-results').html(resultsHtml);
      this.set('currentFieldType', matches[0].name);
      this.onChangeFieldType();
    },
    onClickBrowsePopular: function () {
      this.$el.find('.acf-search-field-types').val('').trigger('input');
      this.$el.find('.acf-tab-wrap a').first().trigger('click');
    },
    onClickSelectField: function (e) {
      const fieldObject = this.get('openedBy');
      fieldObject.$fieldTypeSelect().val(this.get('currentFieldType'));
      fieldObject.$fieldTypeSelect().trigger('change');
      this.updateFieldObjectFieldLabel();
      this.close();
    },
    onClickFieldType: function (e) {
      const $fieldType = $(e.currentTarget);
      this.set('currentFieldType', $fieldType.data('field-type'));
    },
    onClickClose: function () {
      this.close();
    },
    onPressEscapeClose: function (e) {
      if (e.key === 'Escape') {
        this.close();
      }
    },
    close: function () {
      this.lockFocusToModal(false);
      this.returnFocusToOrigin();
      this.remove();
    },
    focus: function () {
      this.$el.find('button').first().trigger('focus');
    }
  };
  acf.models.browseFieldsModal = acf.models.Modal.extend(browseFieldsModal);
  acf.newBrowseFieldsModal = props => new acf.models.browseFieldsModal(props);
})(window.jQuery, undefined, window.acf);

/***/ }),

/***/ "./src/advanced-custom-fields-pro/assets/src/js/_field-group-compatibility.js":
/*!************************************************************************************!*\
  !*** ./src/advanced-custom-fields-pro/assets/src/js/_field-group-compatibility.js ***!
  \************************************************************************************/
/***/ (() => {

(function ($, undefined) {
  var _acf = acf.getCompatibility(acf);

  /**
   *  fieldGroupCompatibility
   *
   *  Compatibility layer for extinct acf.field_group
   *
   *  @date	15/12/17
   *  @since	5.7.0
   *
   *  @param	void
   *  @return	void
   */

  _acf.field_group = {
    save_field: function ($field, type) {
      type = type !== undefined ? type : 'settings';
      acf.getFieldObject($field).save(type);
    },
    delete_field: function ($field, animate) {
      animate = animate !== undefined ? animate : true;
      acf.getFieldObject($field).delete({
        animate: animate
      });
    },
    update_field_meta: function ($field, name, value) {
      acf.getFieldObject($field).prop(name, value);
    },
    delete_field_meta: function ($field, name) {
      acf.getFieldObject($field).prop(name, null);
    }
  };

  /**
   *  fieldGroupCompatibility.field_object
   *
   *  Compatibility layer for extinct acf.field_group.field_object
   *
   *  @date	15/12/17
   *  @since	5.7.0
   *
   *  @param	void
   *  @return	void
   */

  _acf.field_group.field_object = acf.model.extend({
    // vars
    type: '',
    o: {},
    $field: null,
    $settings: null,
    tag: function (tag) {
      // vars
      var type = this.type;

      // explode, add 'field' and implode
      // - open 			=> open_field
      // - change_type	=> change_field_type
      var tags = tag.split('_');
      tags.splice(1, 0, 'field');
      tag = tags.join('_');

      // add type
      if (type) {
        tag += '/type=' + type;
      }

      // return
      return tag;
    },
    selector: function () {
      // vars
      var selector = '.acf-field-object';
      var type = this.type;

      // add type
      if (type) {
        selector += '-' + type;
        selector = acf.str_replace('_', '-', selector);
      }

      // return
      return selector;
    },
    _add_action: function (name, callback) {
      // vars
      var model = this;

      // add action
      acf.add_action(this.tag(name), function ($field) {
        // focus
        model.set('$field', $field);

        // callback
        model[callback].apply(model, arguments);
      });
    },
    _add_filter: function (name, callback) {
      // vars
      var model = this;

      // add action
      acf.add_filter(this.tag(name), function ($field) {
        // focus
        model.set('$field', $field);

        // callback
        model[callback].apply(model, arguments);
      });
    },
    _add_event: function (name, callback) {
      // vars
      var model = this;
      var event = name.substr(0, name.indexOf(' '));
      var selector = name.substr(name.indexOf(' ') + 1);
      var context = this.selector();

      // add event
      $(document).on(event, context + ' ' + selector, function (e) {
        // append $el to event object
        e.$el = $(this);
        e.$field = e.$el.closest('.acf-field-object');

        // focus
        model.set('$field', e.$field);

        // callback
        model[callback].apply(model, [e]);
      });
    },
    _set_$field: function () {
      // vars
      this.o = this.$field.data();

      // els
      this.$settings = this.$field.find('> .settings > table > tbody');

      // focus
      this.focus();
    },
    focus: function () {
      // do nothing
    },
    setting: function (name) {
      return this.$settings.find('> .acf-field-setting-' + name);
    }
  });

  /*
   *  field
   *
   *  This model fires actions and filters for registered fields
   *
   *  @type	function
   *  @date	21/02/2014
   *  @since	3.5.1
   *
   *  @param	n/a
   *  @return	n/a
   */

  var actionManager = new acf.Model({
    actions: {
      open_field_object: 'onOpenFieldObject',
      close_field_object: 'onCloseFieldObject',
      add_field_object: 'onAddFieldObject',
      duplicate_field_object: 'onDuplicateFieldObject',
      delete_field_object: 'onDeleteFieldObject',
      change_field_object_type: 'onChangeFieldObjectType',
      change_field_object_label: 'onChangeFieldObjectLabel',
      change_field_object_name: 'onChangeFieldObjectName',
      change_field_object_parent: 'onChangeFieldObjectParent',
      sortstop_field_object: 'onChangeFieldObjectParent'
    },
    onOpenFieldObject: function (field) {
      acf.doAction('open_field', field.$el);
      acf.doAction('open_field/type=' + field.get('type'), field.$el);
      acf.doAction('render_field_settings', field.$el);
      acf.doAction('render_field_settings/type=' + field.get('type'), field.$el);
    },
    onCloseFieldObject: function (field) {
      acf.doAction('close_field', field.$el);
      acf.doAction('close_field/type=' + field.get('type'), field.$el);
    },
    onAddFieldObject: function (field) {
      acf.doAction('add_field', field.$el);
      acf.doAction('add_field/type=' + field.get('type'), field.$el);
    },
    onDuplicateFieldObject: function (field) {
      acf.doAction('duplicate_field', field.$el);
      acf.doAction('duplicate_field/type=' + field.get('type'), field.$el);
    },
    onDeleteFieldObject: function (field) {
      acf.doAction('delete_field', field.$el);
      acf.doAction('delete_field/type=' + field.get('type'), field.$el);
    },
    onChangeFieldObjectType: function (field) {
      acf.doAction('change_field_type', field.$el);
      acf.doAction('change_field_type/type=' + field.get('type'), field.$el);
      acf.doAction('render_field_settings', field.$el);
      acf.doAction('render_field_settings/type=' + field.get('type'), field.$el);
    },
    onChangeFieldObjectLabel: function (field) {
      acf.doAction('change_field_label', field.$el);
      acf.doAction('change_field_label/type=' + field.get('type'), field.$el);
    },
    onChangeFieldObjectName: function (field) {
      acf.doAction('change_field_name', field.$el);
      acf.doAction('change_field_name/type=' + field.get('type'), field.$el);
    },
    onChangeFieldObjectParent: function (field) {
      acf.doAction('update_field_parent', field.$el);
    }
  });
})(jQuery);

/***/ }),

/***/ "./src/advanced-custom-fields-pro/assets/src/js/_field-group-conditions.js":
/*!*********************************************************************************!*\
  !*** ./src/advanced-custom-fields-pro/assets/src/js/_field-group-conditions.js ***!
  \*********************************************************************************/
/***/ (() => {

(function ($, undefined) {
  /**
   *  ConditionalLogicFieldSetting
   *
   *  description
   *
   *  @date	3/2/18
   *  @since	5.6.5
   *
   *  @param	type $var Description. Default.
   *  @return	type Description.
   */

  var ConditionalLogicFieldSetting = acf.FieldSetting.extend({
    type: '',
    name: 'conditional_logic',
    events: {
      'change .conditions-toggle': 'onChangeToggle',
      'click .add-conditional-group': 'onClickAddGroup',
      'focus .condition-rule-field': 'onFocusField',
      'change .condition-rule-field': 'onChangeField',
      'change .condition-rule-operator': 'onChangeOperator',
      'click .add-conditional-rule': 'onClickAdd',
      'click .remove-conditional-rule': 'onClickRemove'
    },
    $rule: false,
    scope: function ($rule) {
      this.$rule = $rule;
      return this;
    },
    ruleData: function (name, value) {
      return this.$rule.data.apply(this.$rule, arguments);
    },
    $input: function (name) {
      return this.$rule.find('.condition-rule-' + name);
    },
    $td: function (name) {
      return this.$rule.find('td.' + name);
    },
    $toggle: function () {
      return this.$('.conditions-toggle');
    },
    $control: function () {
      return this.$('.rule-groups');
    },
    $groups: function () {
      return this.$('.rule-group');
    },
    $rules: function () {
      return this.$('.rule');
    },
    $tabLabel: function () {
      return this.fieldObject.$el.find('.conditional-logic-badge');
    },
    open: function () {
      var $div = this.$control();
      $div.show();
      acf.enable($div);
    },
    close: function () {
      var $div = this.$control();
      $div.hide();
      acf.disable($div);
    },
    render: function () {
      // show
      if (this.$toggle().prop('checked')) {
        this.$tabLabel().addClass('is-enabled');
        this.renderRules();
        this.open();

        // hide
      } else {
        this.$tabLabel().removeClass('is-enabled');
        this.close();
      }
    },
    renderRules: function () {
      // vars
      var self = this;

      // loop
      this.$rules().each(function () {
        self.renderRule($(this));
      });
    },
    renderRule: function ($rule) {
      this.scope($rule);
      this.renderField();
      this.renderOperator();
      this.renderValue();
    },
    renderField: function () {
      // vars
      var choices = [];
      var validFieldTypes = [];
      var cid = this.fieldObject.cid;
      var $select = this.$input('field');

      // loop
      acf.getFieldObjects().map(function (fieldObject) {
        // vars
        var choice = {
          id: fieldObject.getKey(),
          text: fieldObject.getLabel()
        };

        // bail early if is self
        if (fieldObject.cid === cid) {
          choice.text += ' ' + acf.__('(this field)');
          choice.disabled = true;
        }

        // get selected field conditions
        var conditionTypes = acf.getConditionTypes({
          fieldType: fieldObject.getType()
        });

        // bail early if no types
        if (!conditionTypes.length) {
          choice.disabled = true;
        }

        // calulate indents
        var indents = fieldObject.getParents().length;
        choice.text = '- '.repeat(indents) + choice.text;

        // append
        choices.push(choice);
      });

      // allow for scenario where only one field exists
      if (!choices.length) {
        choices.push({
          id: '',
          text: acf.__('No toggle fields available')
        });
      }

      // render
      acf.renderSelect($select, choices);

      // set
      this.ruleData('field', $select.val());
    },
    renderOperator: function () {
      // bail early if no field selected
      if (!this.ruleData('field')) {
        return;
      }

      // vars
      var $select = this.$input('operator');
      var val = $select.val();
      var choices = [];

      // set saved value on first render
      // - this allows the 2nd render to correctly select an option
      if ($select.val() === null) {
        acf.renderSelect($select, [{
          id: this.ruleData('operator'),
          text: ''
        }]);
      }

      // get selected field
      var $field = acf.findFieldObject(this.ruleData('field'));
      var field = acf.getFieldObject($field);

      // get selected field conditions
      var conditionTypes = acf.getConditionTypes({
        fieldType: field.getType()
      });

      // html
      conditionTypes.map(function (model) {
        choices.push({
          id: model.prototype.operator,
          text: model.prototype.label
        });
      });

      // render
      acf.renderSelect($select, choices);

      // set
      this.ruleData('operator', $select.val());
    },
    renderValue: function () {
      // bail early if no field selected
      if (!this.ruleData('field') || !this.ruleData('operator')) {
        return;
      }

      // vars
      var $select = this.$input('value');
      var $td = this.$td('value');
      var val = $select.val();

      // get selected field
      var $field = acf.findFieldObject(this.ruleData('field'));
      var field = acf.getFieldObject($field);

      // get selected field conditions
      var conditionTypes = acf.getConditionTypes({
        fieldType: field.getType(),
        operator: this.ruleData('operator')
      });

      // html
      var conditionType = conditionTypes[0].prototype;
      var choices = conditionType.choices(field);

      // create html: array
      if (choices instanceof Array) {
        var $newSelect = $('<select></select>');
        acf.renderSelect($newSelect, choices);

        // create html: string (<input />)
      } else {
        var $newSelect = $(choices);
      }

      // append
      $select.detach();
      $td.html($newSelect);

      // copy attrs
      // timeout needed to avoid browser bug where "disabled" attribute is not applied
      setTimeout(function () {
        ['class', 'name', 'id'].map(function (attr) {
          $newSelect.attr(attr, $select.attr(attr));
        });
      }, 0);

      // select existing value (if not a disabled input)
      if (!$newSelect.prop('disabled')) {
        acf.val($newSelect, val, true);
      }

      // set
      this.ruleData('value', $newSelect.val());
    },
    onChangeToggle: function () {
      this.render();
    },
    onClickAddGroup: function (e, $el) {
      this.addGroup();
    },
    addGroup: function () {
      // vars
      var $group = this.$('.rule-group:last');

      // duplicate
      var $group2 = acf.duplicate($group);

      // update h4
      $group2.find('h4').text(acf.__('or'));

      // remove all tr's except the first one
      $group2.find('tr').not(':first').remove();

      // save field
      this.fieldObject.save();
    },
    onFocusField: function (e, $el) {
      this.renderField();
    },
    onChangeField: function (e, $el) {
      // scope
      this.scope($el.closest('.rule'));

      // set data
      this.ruleData('field', $el.val());

      // render
      this.renderOperator();
      this.renderValue();
    },
    onChangeOperator: function (e, $el) {
      // scope
      this.scope($el.closest('.rule'));

      // set data
      this.ruleData('operator', $el.val());

      // render
      this.renderValue();
    },
    onClickAdd: function (e, $el) {
      // duplciate
      var $rule = acf.duplicate($el.closest('.rule'));

      // render
      this.renderRule($rule);
    },
    onClickRemove: function (e, $el) {
      // vars
      var $rule = $el.closest('.rule');

      // save field
      this.fieldObject.save();

      // remove group
      if ($rule.siblings('.rule').length == 0) {
        $rule.closest('.rule-group').remove();
      }

      // remove
      $rule.remove();
    }
  });
  acf.registerFieldSetting(ConditionalLogicFieldSetting);

  /**
   *  conditionalLogicHelper
   *
   *  description
   *
   *  @date	20/4/18
   *  @since	5.6.9
   *
   *  @param	type $var Description. Default.
   *  @return	type Description.
   */

  var conditionalLogicHelper = new acf.Model({
    actions: {
      duplicate_field_objects: 'onDuplicateFieldObjects'
    },
    onDuplicateFieldObjects: function (children, newField, prevField) {
      // vars
      var data = {};
      var $selects = $();

      // reference change in key
      children.map(function (child) {
        // store reference of changed key
        data[child.get('prevKey')] = child.get('key');

        // append condition select
        $selects = $selects.add(child.$('.condition-rule-field'));
      });

      // loop
      $selects.each(function () {
        // vars
        var $select = $(this);
        var val = $select.val();

        // bail early if val is not a ref key
        if (!val || !data[val]) {
          return;
        }

        // modify selected option
        $select.find('option:selected').attr('value', data[val]);

        // set new val
        $select.val(data[val]);
      });
    }
  });
})(jQuery);

/***/ }),

/***/ "./src/advanced-custom-fields-pro/assets/src/js/_field-group-field.js":
/*!****************************************************************************!*\
  !*** ./src/advanced-custom-fields-pro/assets/src/js/_field-group-field.js ***!
  \****************************************************************************/
/***/ (() => {

(function ($, undefined) {
  acf.FieldObject = acf.Model.extend({
    // class used to avoid nested event triggers
    eventScope: '.acf-field-object',
    // variable for field type select2
    fieldTypeSelect2: false,
    // events
    events: {
      'click .copyable': 'onClickCopy',
      'click .handle': 'onClickEdit',
      'click .close-field': 'onClickEdit',
      'click a[data-key="acf_field_settings_tabs"]': 'onChangeSettingsTab',
      'click .delete-field': 'onClickDelete',
      'click .duplicate-field': 'duplicate',
      'click .move-field': 'move',
      'click .browse-fields': 'browseFields',
      'focus .edit-field': 'onFocusEdit',
      'blur .edit-field, .row-options a': 'onBlurEdit',
      'change .field-type': 'onChangeType',
      'change .field-required': 'onChangeRequired',
      'blur .field-label': 'onChangeLabel',
      'blur .field-name': 'onChangeName',
      change: 'onChange',
      changed: 'onChanged'
    },
    // data
    data: {
      // Similar to ID, but used for HTML puposes.
      // It is possbile for a new field to have an ID of 0, but an id of 'field_123' */
      id: 0,
      // The field key ('field_123')
      key: '',
      // The field type (text, image, etc)
      type: ''

      // The $post->ID of this field
      //ID: 0,

      // The field's parent
      //parent: 0,

      // The menu order
      //menu_order: 0
    },

    setup: function ($field) {
      // set $el
      this.$el = $field;

      // inherit $field data (id, key, type)
      this.inherit($field);

      // load additional props
      // - this won't trigger 'changed'
      this.prop('ID');
      this.prop('parent');
      this.prop('menu_order');
    },
    $input: function (name) {
      return $('#' + this.getInputId() + '-' + name);
    },
    $meta: function () {
      return this.$('.meta:first');
    },
    $handle: function () {
      return this.$('.handle:first');
    },
    $settings: function () {
      return this.$('.settings:first');
    },
    $setting: function (name) {
      return this.$('.acf-field-settings:first .acf-field-setting-' + name);
    },
    $fieldTypeSelect: function () {
      return this.$('.field-type');
    },
    $fieldLabel: function () {
      return this.$('.field-label');
    },
    getParent: function () {
      return acf.getFieldObjects({
        child: this.$el,
        limit: 1
      }).pop();
    },
    getParents: function () {
      return acf.getFieldObjects({
        child: this.$el
      });
    },
    getFields: function () {
      return acf.getFieldObjects({
        parent: this.$el
      });
    },
    getInputName: function () {
      return 'acf_fields[' + this.get('id') + ']';
    },
    getInputId: function () {
      return 'acf_fields-' + this.get('id');
    },
    newInput: function (name, value) {
      // vars
      var inputId = this.getInputId();
      var inputName = this.getInputName();

      // append name
      if (name) {
        inputId += '-' + name;
        inputName += '[' + name + ']';
      }

      // create input (avoid HTML + JSON value issues)
      var $input = $('<input />').attr({
        id: inputId,
        name: inputName,
        value: value
      });
      this.$('> .meta').append($input);

      // return
      return $input;
    },
    getProp: function (name) {
      // check data
      if (this.has(name)) {
        return this.get(name);
      }

      // get input value
      var $input = this.$input(name);
      var value = $input.length ? $input.val() : null;

      // set data silently (cache)
      this.set(name, value, true);

      // return
      return value;
    },
    setProp: function (name, value) {
      // get input
      var $input = this.$input(name);
      var prevVal = $input.val();

      // create if new
      if (!$input.length) {
        $input = this.newInput(name, value);
      }

      // remove
      if (value === null) {
        $input.remove();

        // update
      } else {
        $input.val(value);
      }

      //console.log('setProp', name, value, this);

      // set data silently (cache)
      if (!this.has(name)) {
        //console.log('setting silently');
        this.set(name, value, true);

        // set data allowing 'change' event to fire
      } else {
        //console.log('setting loudly!');
        this.set(name, value);
      }

      // return
      return this;
    },
    prop: function (name, value) {
      if (value !== undefined) {
        return this.setProp(name, value);
      } else {
        return this.getProp(name);
      }
    },
    props: function (props) {
      Object.keys(props).map(function (key) {
        this.setProp(key, props[key]);
      }, this);
    },
    getLabel: function () {
      // get label with empty default
      var label = this.prop('label');
      if (label === '') {
        label = acf.__('(no label)');
      }

      // return
      return label;
    },
    getName: function () {
      return this.prop('name');
    },
    getType: function () {
      return this.prop('type');
    },
    getTypeLabel: function () {
      var type = this.prop('type');
      var types = acf.get('fieldTypes');
      return types[type] ? types[type].label : type;
    },
    getKey: function () {
      return this.prop('key');
    },
    initialize: function () {
      this.checkCopyable();
    },
    makeCopyable: function (text) {
      if (!navigator.clipboard) return '<span class="copyable copy-unsupported">' + text + '</span>';
      return '<span class="copyable">' + text + '</span>';
    },
    checkCopyable: function () {
      if (!navigator.clipboard) {
        this.$el.find('.copyable').addClass('copy-unsupported');
      }
    },
    initializeFieldTypeSelect2: function () {
      if (this.fieldTypeSelect2) return;

      // Support disabling via filter.
      if (this.$fieldTypeSelect().hasClass('disable-select2')) return;

      // Check for a full modern version of select2, bail loading if not found with a console warning.
      try {
        $.fn.select2.amd.require('select2/compat/dropdownCss');
      } catch (err) {
        console.warn('ACF was not able to load the full version of select2 due to a conflicting version provided by another plugin or theme taking precedence. Select2 fields may not work as expected.');
        return;
      }
      this.fieldTypeSelect2 = acf.newSelect2(this.$fieldTypeSelect(), {
        field: false,
        ajax: false,
        multiple: false,
        allowNull: false,
        suppressFilters: true,
        dropdownCssClass: 'field-type-select-results',
        templateResult: function (selection) {
          if (selection.loading || selection.element && selection.element.nodeName === 'OPTGROUP') {
            var $selection = $('<span class="acf-selection"></span>');
            $selection.html(acf.escHtml(selection.text));
          } else {
            var $selection = $('<i class="field-type-icon field-type-icon-' + selection.id.replaceAll('_', '-') + '"></i><span class="acf-selection has-icon">' + acf.escHtml(selection.text) + '</span>');
          }
          $selection.data('element', selection.element);
          return $selection;
        },
        templateSelection: function (selection) {
          var $selection = $('<i class="field-type-icon field-type-icon-' + selection.id.replaceAll('_', '-') + '"></i><span class="acf-selection has-icon">' + acf.escHtml(selection.text) + '</span>');
          $selection.data('element', selection.element);
          return $selection;
        }
      });
      this.fieldTypeSelect2.on('select2:open', function () {
        $('.field-type-select-results input.select2-search__field').attr('placeholder', acf.__('Type to search...'));
      });
      this.fieldTypeSelect2.on('change', function (e) {
        $(e.target).parents('ul:first').find('button.browse-fields').prop('disabled', true);
      });

      // When typing happens on the li element above the select2.
      this.fieldTypeSelect2.$el.parent().on('keydown', '.select2-selection.select2-selection--single', this.onKeyDownSelect);
    },
    addProFields: function () {
      // Make sure we're only running this on free version.
      if (acf.get('is_pro')) {
        return;
      }

      // Make sure we haven't appended these fields before.
      var $fieldTypeSelect = this.$fieldTypeSelect();
      if ($fieldTypeSelect.hasClass('acf-free-field-type')) return;

      // Loop over each pro field type and append it to the select.
      const PROFieldTypes = acf.get('PROFieldTypes');
      if (typeof PROFieldTypes !== 'object') return;
      const $layoutGroup = $fieldTypeSelect.find('optgroup option[value="group"]').parent();
      const $contentGroup = $fieldTypeSelect.find('optgroup option[value="image"]').parent();
      for (const [name, field] of Object.entries(PROFieldTypes)) {
        const $useGroup = field.category === 'content' ? $contentGroup : $layoutGroup;
        $useGroup.append('<option value="null" disabled="disabled">' + field.label + ' (' + acf.__('PRO Only') + ')</option>');
      }
      $fieldTypeSelect.addClass('acf-free-field-type');
    },
    render: function () {
      // vars
      var $handle = this.$('.handle:first');
      var menu_order = this.prop('menu_order');
      var label = this.getLabel();
      var name = this.prop('name');
      var type = this.getTypeLabel();
      var key = this.prop('key');
      var required = this.$input('required').prop('checked');

      // update menu order
      $handle.find('.acf-icon').html(parseInt(menu_order) + 1);

      // update required
      if (required) {
        label += ' <span class="acf-required">*</span>';
      }

      // update label
      $handle.find('.li-field-label strong a').html(label);

      // update name
      $handle.find('.li-field-name').html(this.makeCopyable(name));

      // update type
      const iconName = acf.strSlugify(this.getType());
      $handle.find('.field-type-label').text(' ' + type);
      $handle.find('.field-type-icon').removeClass().addClass('field-type-icon field-type-icon-' + iconName);

      // update key
      $handle.find('.li-field-key').html(this.makeCopyable(key));

      // action for 3rd party customization
      acf.doAction('render_field_object', this);
    },
    refresh: function () {
      acf.doAction('refresh_field_object', this);
    },
    isOpen: function () {
      return this.$el.hasClass('open');
    },
    onClickCopy: function (e) {
      e.stopPropagation();
      if (!navigator.clipboard) return;
      navigator.clipboard.writeText($(e.target).text()).then(() => {
        $(e.target).addClass('copied');
        setTimeout(function () {
          $(e.target).removeClass('copied');
        }, 2000);
      });
    },
    onClickEdit: function (e) {
      $target = $(e.target);
      if ($target.parent().hasClass('row-options') && !$target.hasClass('edit-field')) return;
      this.isOpen() ? this.close() : this.open();
    },
    onChangeSettingsTab: function () {
      const $settings = this.$el.children('.settings');
      acf.doAction('show', $settings);
    },
    /**
     * Adds 'active' class to row options nearest to the target.
     */
    onFocusEdit: function (e) {
      var $rowOptions = $(e.target).closest('li').find('.row-options');
      $rowOptions.addClass('active');
    },
    /**
     * Removes 'active' class from row options if links in same row options area are no longer in focus.
     */
    onBlurEdit: function (e) {
      var focusDelayMilliseconds = 50;
      var $rowOptionsBlurElement = $(e.target).closest('li').find('.row-options');

      // Timeout so that `activeElement` gives the new element in focus instead of the body.
      setTimeout(function () {
        var $rowOptionsFocusElement = $(document.activeElement).closest('li').find('.row-options');
        if (!$rowOptionsBlurElement.is($rowOptionsFocusElement)) {
          $rowOptionsBlurElement.removeClass('active');
        }
      }, focusDelayMilliseconds);
    },
    open: function () {
      // vars
      var $settings = this.$el.children('.settings');

      // initialise field type select
      this.addProFields();
      this.initializeFieldTypeSelect2();

      // action (open)
      acf.doAction('open_field_object', this);
      this.trigger('openFieldObject');

      // action (show)
      acf.doAction('show', $settings);
      this.hideEmptyTabs();

      // open
      $settings.slideDown();
      this.$el.addClass('open');
    },
    onKeyDownSelect: function (e) {
      // Omit events from special keys.
      if (!(e.which >= 186 && e.which <= 222 ||
      // punctuation and special characters
      [8, 9, 13, 16, 17, 18, 19, 20, 27, 32, 33, 34, 35, 36, 37, 38, 39, 40, 45, 46, 91, 92, 93, 144, 145].includes(e.which) ||
      // Special keys
      e.which >= 112 && e.which <= 123)) {
        // Function keys
        $(this).closest('.select2-container').siblings('select:enabled').select2('open');
        return;
      }
    },
    close: function () {
      // vars
      var $settings = this.$el.children('.settings');

      // close
      $settings.slideUp();
      this.$el.removeClass('open');

      // action (close)
      acf.doAction('close_field_object', this);
      this.trigger('closeFieldObject');

      // action (hide)
      acf.doAction('hide', $settings);
    },
    serialize: function () {
      return acf.serialize(this.$el, this.getInputName());
    },
    save: function (type) {
      // defaults
      type = type || 'settings'; // meta, settings

      // vars
      var save = this.getProp('save');

      // bail if already saving settings
      if (save === 'settings') {
        return;
      }

      // prop
      this.setProp('save', type);

      // debug
      this.$el.attr('data-save', type);

      // action
      acf.doAction('save_field_object', this, type);
    },
    submit: function () {
      // vars
      var inputName = this.getInputName();
      var save = this.get('save');

      // close
      if (this.isOpen()) {
        this.close();
      }

      // allow all inputs to save
      if (save == 'settings') {
        // do nothing
        // allow only meta inputs to save
      } else if (save == 'meta') {
        this.$('> .settings [name^="' + inputName + '"]').remove();

        // prevent all inputs from saving
      } else {
        this.$('[name^="' + inputName + '"]').remove();
      }

      // action
      acf.doAction('submit_field_object', this);
    },
    onChange: function (e, $el) {
      // save settings
      this.save();

      // action for 3rd party customization
      acf.doAction('change_field_object', this);
    },
    onChanged: function (e, $el, name, value) {
      if (this.getType() === $el.attr('data-type')) {
        $('button.acf-btn.browse-fields').prop('disabled', false);
      }

      // ignore 'save'
      if (name == 'save') {
        return;
      }

      // save meta
      if (['menu_order', 'parent'].indexOf(name) > -1) {
        this.save('meta');

        // save field
      } else {
        this.save();
      }

      // render
      if (['menu_order', 'label', 'required', 'name', 'type', 'key'].indexOf(name) > -1) {
        this.render();
      }

      // action for 3rd party customization
      acf.doAction('change_field_object_' + name, this, value);
    },
    onChangeLabel: function (e, $el) {
      // set
      var label = $el.val();
      this.set('label', label);

      // render name
      if (this.prop('name') == '') {
        var name = acf.applyFilters('generate_field_object_name', acf.strSanitize(label), this);
        this.prop('name', name);
      }
    },
    onChangeName: function (e, $el) {
      // set
      var name = $el.val();
      this.set('name', name);

      // error
      if (name.substr(0, 6) === 'field_') {
        alert(acf.__('The string "field_" may not be used at the start of a field name'));
      }
    },
    onChangeRequired: function (e, $el) {
      // set
      var required = $el.prop('checked') ? 1 : 0;
      this.set('required', required);
    },
    delete: function (args) {
      // defaults
      args = acf.parseArgs(args, {
        animate: true
      });

      // add to remove list
      var id = this.prop('ID');
      if (id) {
        var $input = $('#_acf_delete_fields');
        var newVal = $input.val() + '|' + id;
        $input.val(newVal);
      }

      // action
      acf.doAction('delete_field_object', this);

      // animate
      if (args.animate) {
        this.removeAnimate();
      } else {
        this.remove();
      }
    },
    onClickDelete: function (e, $el) {
      // Bypass confirmation when holding down "shift" key.
      if (e.shiftKey) {
        return this.delete();
      }

      // add class
      this.$el.addClass('-hover');

      // add tooltip
      var tooltip = acf.newTooltip({
        confirmRemove: true,
        target: $el,
        context: this,
        confirm: function () {
          this.delete();
        },
        cancel: function () {
          this.$el.removeClass('-hover');
        }
      });
    },
    removeAnimate: function () {
      // vars
      var field = this;
      var $list = this.$el.parent();
      var $fields = acf.findFieldObjects({
        sibling: this.$el
      });

      // remove
      acf.remove({
        target: this.$el,
        endHeight: $fields.length ? 0 : 50,
        complete: function () {
          field.remove();
          acf.doAction('removed_field_object', field, $list);
        }
      });

      // action
      acf.doAction('remove_field_object', field, $list);
    },
    duplicate: function () {
      // vars
      var newKey = acf.uniqid('field_');

      // duplicate
      var $newField = acf.duplicate({
        target: this.$el,
        search: this.get('id'),
        replace: newKey
      });

      // set new key
      $newField.attr('data-key', newKey);

      // get instance
      var newField = acf.getFieldObject($newField);

      // update newField label / name
      var label = newField.prop('label');
      var name = newField.prop('name');
      var end = name.split('_').pop();
      var copy = acf.__('copy');

      // increase suffix "1"
      if (acf.isNumeric(end)) {
        var i = end * 1 + 1;
        label = label.replace(end, i);
        name = name.replace(end, i);

        // increase suffix "(copy1)"
      } else if (end.indexOf(copy) === 0) {
        var i = end.replace(copy, '') * 1;
        i = i ? i + 1 : 2;

        // replace
        label = label.replace(end, copy + i);
        name = name.replace(end, copy + i);

        // add default "(copy)"
      } else {
        label += ' (' + copy + ')';
        name += '_' + copy;
      }
      newField.prop('ID', 0);
      newField.prop('label', label);
      newField.prop('name', name);
      newField.prop('key', newKey);

      // close the current field if it's open.
      if (this.isOpen()) {
        this.close();
      }

      // open the new field and initialise correctly.
      newField.open();

      // focus label
      var $label = newField.$setting('label input');
      setTimeout(function () {
        $label.trigger('focus');
      }, 251);

      // action
      acf.doAction('duplicate_field_object', this, newField);
      acf.doAction('append_field_object', newField);
    },
    wipe: function () {
      // vars
      var prevId = this.get('id');
      var prevKey = this.get('key');
      var newKey = acf.uniqid('field_');

      // rename
      acf.rename({
        target: this.$el,
        search: prevId,
        replace: newKey
      });

      // data
      this.set('id', newKey);
      this.set('prevId', prevId);
      this.set('prevKey', prevKey);

      // props
      this.prop('key', newKey);
      this.prop('ID', 0);

      // attr
      this.$el.attr('data-key', newKey);
      this.$el.attr('data-id', newKey);

      // action
      acf.doAction('wipe_field_object', this);
    },
    move: function () {
      // helper
      var hasChanged = function (field) {
        return field.get('save') == 'settings';
      };

      // vars
      var changed = hasChanged(this);

      // has sub fields changed
      if (!changed) {
        acf.getFieldObjects({
          parent: this.$el
        }).map(function (field) {
          changed = hasChanged(field) || field.changed;
        });
      }

      // bail early if changed
      if (changed) {
        alert(acf.__('This field cannot be moved until its changes have been saved'));
        return;
      }

      // step 1.
      var id = this.prop('ID');
      var field = this;
      var popup = false;
      var step1 = function () {
        // popup
        popup = acf.newPopup({
          title: acf.__('Move Custom Field'),
          loading: true,
          width: '300px',
          openedBy: field.$el.find('.move-field')
        });

        // ajax
        var ajaxData = {
          action: 'acf/field_group/move_field',
          field_id: id
        };

        // get HTML
        $.ajax({
          url: acf.get('ajaxurl'),
          data: acf.prepareForAjax(ajaxData),
          type: 'post',
          dataType: 'html',
          success: step2
        });
      };
      var step2 = function (html) {
        // update popup
        popup.loading(false);
        popup.content(html);

        // submit form
        popup.on('submit', 'form', step3);
      };
      var step3 = function (e, $el) {
        // prevent
        e.preventDefault();

        // disable
        acf.startButtonLoading(popup.$('.button'));

        // ajax
        var ajaxData = {
          action: 'acf/field_group/move_field',
          field_id: id,
          field_group_id: popup.$('select').val()
        };

        // get HTML
        $.ajax({
          url: acf.get('ajaxurl'),
          data: acf.prepareForAjax(ajaxData),
          type: 'post',
          dataType: 'html',
          success: step4
        });
      };
      var step4 = function (html) {
        popup.content(html);
        if (wp.a11y && wp.a11y.speak && acf.__) {
          wp.a11y.speak(acf.__('Field moved to other group'), 'polite');
        }
        popup.$('.acf-close-popup').focus();
        field.removeAnimate();
      };

      // start
      step1();
    },
    browseFields: function (e, $el) {
      e.preventDefault();
      const modal = acf.newBrowseFieldsModal({
        openedBy: this
      });
    },
    onChangeType: function (e, $el) {
      // clea previous timout
      if (this.changeTimeout) {
        clearTimeout(this.changeTimeout);
      }

      // set new timeout
      // - prevents changing type multiple times whilst user types in newType
      this.changeTimeout = this.setTimeout(function () {
        this.changeType($el.val());
      }, 300);
    },
    changeType: function (newType) {
      var prevType = this.prop('type');
      var prevClass = acf.strSlugify('acf-field-object-' + prevType);
      var newClass = acf.strSlugify('acf-field-object-' + newType);

      // Update props.
      this.$el.removeClass(prevClass).addClass(newClass);
      this.$el.attr('data-type', newType);
      this.$el.data('type', newType);

      // Abort XHR if this field is already loading AJAX data.
      if (this.has('xhr')) {
        this.get('xhr').abort();
      }

      // Store old settings so they can be reused later.
      const $oldSettings = {};
      this.$el.find('.acf-field-settings:first > .acf-field-settings-main > .acf-field-type-settings').each(function () {
        let tab = $(this).data('parent-tab');
        let $tabSettings = $(this).children().removeData();
        $oldSettings[tab] = $tabSettings;
        $tabSettings.detach();
      });
      this.set('settings-' + prevType, $oldSettings);

      // Show the settings if we already have them cached.
      if (this.has('settings-' + newType)) {
        let $newSettings = this.get('settings-' + newType);
        this.showFieldTypeSettings($newSettings);
        this.set('type', newType);
        return;
      }

      // Add loading spinner.
      const $loading = $('<div class="acf-field"><div class="acf-input"><div class="acf-loading"></div></div></div>');
      this.$el.find('.acf-field-settings-main-general .acf-field-type-settings').before($loading);
      const ajaxData = {
        action: 'acf/field_group/render_field_settings',
        field: this.serialize(),
        prefix: this.getInputName()
      };

      // Get the settings for this field type over AJAX.
      var xhr = $.ajax({
        url: acf.get('ajaxurl'),
        data: acf.prepareForAjax(ajaxData),
        type: 'post',
        dataType: 'json',
        context: this,
        success: function (response) {
          if (!acf.isAjaxSuccess(response)) {
            return;
          }
          this.showFieldTypeSettings(response.data);
        },
        complete: function () {
          // also triggered by xhr.abort();
          $loading.remove();
          this.set('type', newType);
          //this.refresh();
        }
      });

      // set
      this.set('xhr', xhr);
    },
    showFieldTypeSettings: function (settings) {
      if ('object' !== typeof settings) {
        return;
      }
      const self = this;
      const tabs = Object.keys(settings);
      tabs.forEach(tab => {
        const $tab = self.$el.find('.acf-field-settings-main-' + tab.replace('_', '-') + ' .acf-field-type-settings');
        let tabContent = '';
        if (['object', 'string'].includes(typeof settings[tab])) {
          tabContent = settings[tab];
        }
        $tab.prepend(tabContent);
        acf.doAction('append', $tab);
      });
      this.hideEmptyTabs();
    },
    updateParent: function () {
      // vars
      var ID = acf.get('post_id');

      // check parent
      var parent = this.getParent();
      if (parent) {
        ID = parseInt(parent.prop('ID')) || parent.prop('key');
      }

      // update
      this.prop('parent', ID);
    },
    hideEmptyTabs: function () {
      const $settings = this.$settings();
      const $tabs = $settings.find('.acf-field-settings:first > .acf-field-settings-main');
      $tabs.each(function () {
        const $tabContent = $(this);
        const tabName = $tabContent.find('.acf-field-type-settings:first').data('parentTab');
        const $tabLink = $settings.find('.acf-settings-type-' + tabName).first();
        if ($.trim($tabContent.text()) === '') {
          $tabLink.hide();
        } else if ($tabLink.is(':hidden')) {
          $tabLink.show();
        }
      });
    }
  });
})(jQuery);

/***/ }),

/***/ "./src/advanced-custom-fields-pro/assets/src/js/_field-group-fields.js":
/*!*****************************************************************************!*\
  !*** ./src/advanced-custom-fields-pro/assets/src/js/_field-group-fields.js ***!
  \*****************************************************************************/
/***/ (() => {

(function ($, undefined) {
  /**
   *  acf.findFieldObject
   *
   *  Returns a single fieldObject $el for a given field key
   *
   *  @date	1/2/18
   *  @since	5.7.0
   *
   *  @param	string key The field key
   *  @return	jQuery
   */

  acf.findFieldObject = function (key) {
    return acf.findFieldObjects({
      key: key,
      limit: 1
    });
  };

  /**
   *  acf.findFieldObjects
   *
   *  Returns an array of fieldObject $el for the given args
   *
   *  @date	1/2/18
   *  @since	5.7.0
   *
   *  @param	object args
   *  @return	jQuery
   */

  acf.findFieldObjects = function (args) {
    // vars
    args = args || {};
    var selector = '.acf-field-object';
    var $fields = false;

    // args
    args = acf.parseArgs(args, {
      id: '',
      key: '',
      type: '',
      limit: false,
      list: null,
      parent: false,
      sibling: false,
      child: false
    });

    // id
    if (args.id) {
      selector += '[data-id="' + args.id + '"]';
    }

    // key
    if (args.key) {
      selector += '[data-key="' + args.key + '"]';
    }

    // type
    if (args.type) {
      selector += '[data-type="' + args.type + '"]';
    }

    // query
    if (args.list) {
      $fields = args.list.children(selector);
    } else if (args.parent) {
      $fields = args.parent.find(selector);
    } else if (args.sibling) {
      $fields = args.sibling.siblings(selector);
    } else if (args.child) {
      $fields = args.child.parents(selector);
    } else {
      $fields = $(selector);
    }

    // limit
    if (args.limit) {
      $fields = $fields.slice(0, args.limit);
    }

    // return
    return $fields;
  };

  /**
   *  acf.getFieldObject
   *
   *  Returns a single fieldObject instance for a given $el|key
   *
   *  @date	1/2/18
   *  @since	5.7.0
   *
   *  @param	string|jQuery $field The field $el or key
   *  @return	jQuery
   */

  acf.getFieldObject = function ($field) {
    // allow key
    if (typeof $field === 'string') {
      $field = acf.findFieldObject($field);
    }

    // instantiate
    var field = $field.data('acf');
    if (!field) {
      field = acf.newFieldObject($field);
    }

    // return
    return field;
  };

  /**
   *  acf.getFieldObjects
   *
   *  Returns an array of fieldObject instances for the given args
   *
   *  @date	1/2/18
   *  @since	5.7.0
   *
   *  @param	object args
   *  @return	array
   */

  acf.getFieldObjects = function (args) {
    // query
    var $fields = acf.findFieldObjects(args);

    // loop
    var fields = [];
    $fields.each(function () {
      var field = acf.getFieldObject($(this));
      fields.push(field);
    });

    // return
    return fields;
  };

  /**
   *  acf.newFieldObject
   *
   *  Initializes and returns a new FieldObject instance
   *
   *  @date	1/2/18
   *  @since	5.7.0
   *
   *  @param	jQuery $field The field $el
   *  @return	object
   */

  acf.newFieldObject = function ($field) {
    // instantiate
    var field = new acf.FieldObject($field);

    // action
    acf.doAction('new_field_object', field);

    // return
    return field;
  };

  /**
   *  actionManager
   *
   *  description
   *
   *  @date	15/12/17
   *  @since	5.6.5
   *
   *  @param	type $var Description. Default.
   *  @return	type Description.
   */

  var eventManager = new acf.Model({
    priority: 5,
    initialize: function () {
      // actions
      var actions = ['prepare', 'ready', 'append', 'remove'];

      // loop
      actions.map(function (action) {
        this.addFieldActions(action);
      }, this);
    },
    addFieldActions: function (action) {
      // vars
      var pluralAction = action + '_field_objects'; // ready_field_objects
      var singleAction = action + '_field_object'; // ready_field_object
      var singleEvent = action + 'FieldObject'; // readyFieldObject

      // global action
      var callback = function ($el /*, arg1, arg2, etc*/) {
        // vars
        var fieldObjects = acf.getFieldObjects({
          parent: $el
        });

        // call plural
        if (fieldObjects.length) {
          /// get args [$el, arg1]
          var args = acf.arrayArgs(arguments);

          // modify args [pluralAction, fields, arg1]
          args.splice(0, 1, pluralAction, fieldObjects);
          acf.doAction.apply(null, args);
        }
      };

      // plural action
      var pluralCallback = function (fieldObjects /*, arg1, arg2, etc*/) {
        /// get args [fields, arg1]
        var args = acf.arrayArgs(arguments);

        // modify args [singleAction, fields, arg1]
        args.unshift(singleAction);

        // loop
        fieldObjects.map(function (fieldObject) {
          // modify args [singleAction, field, arg1]
          args[1] = fieldObject;
          acf.doAction.apply(null, args);
        });
      };

      // single action
      var singleCallback = function (fieldObject /*, arg1, arg2, etc*/) {
        /// get args [$field, arg1]
        var args = acf.arrayArgs(arguments);

        // modify args [singleAction, $field, arg1]
        args.unshift(singleAction);

        // action variations (ready_field/type=image)
        var variations = ['type', 'name', 'key'];
        variations.map(function (variation) {
          args[0] = singleAction + '/' + variation + '=' + fieldObject.get(variation);
          acf.doAction.apply(null, args);
        });

        // modify args [arg1]
        args.splice(0, 2);

        // event
        fieldObject.trigger(singleEvent, args);
      };

      // add actions
      acf.addAction(action, callback, 5);
      acf.addAction(pluralAction, pluralCallback, 5);
      acf.addAction(singleAction, singleCallback, 5);
    }
  });

  /**
   *  fieldManager
   *
   *  description
   *
   *  @date	4/1/18
   *  @since	5.6.5
   *
   *  @param	type $var Description. Default.
   *  @return	type Description.
   */

  var fieldManager = new acf.Model({
    id: 'fieldManager',
    events: {
      'submit #post': 'onSubmit',
      'mouseenter .acf-field-list': 'onHoverSortable',
      'click .add-field': 'onClickAdd'
    },
    actions: {
      removed_field_object: 'onRemovedField',
      sortstop_field_object: 'onReorderField',
      delete_field_object: 'onDeleteField',
      change_field_object_type: 'onChangeFieldType',
      duplicate_field_object: 'onDuplicateField'
    },
    onSubmit: function (e, $el) {
      // vars
      var fields = acf.getFieldObjects();

      // loop
      fields.map(function (field) {
        field.submit();
      });
    },
    setFieldMenuOrder: function (field) {
      this.renderFields(field.$el.parent());
    },
    onHoverSortable: function (e, $el) {
      // bail early if already sortable
      if ($el.hasClass('ui-sortable')) return;

      // sortable
      $el.sortable({
        helper: function (event, element) {
          // https://core.trac.wordpress.org/ticket/16972#comment:22
          return element.clone().find(':input').attr('name', function (i, currentName) {
            return 'sort_' + parseInt(Math.random() * 100000, 10).toString() + '_' + currentName;
          }).end();
        },
        handle: '.acf-sortable-handle',
        connectWith: '.acf-field-list',
        start: function (e, ui) {
          var field = acf.getFieldObject(ui.item);
          ui.placeholder.height(ui.item.height());
          acf.doAction('sortstart_field_object', field, $el);
        },
        update: function (e, ui) {
          var field = acf.getFieldObject(ui.item);
          acf.doAction('sortstop_field_object', field, $el);
        }
      });
    },
    onRemovedField: function (field, $list) {
      this.renderFields($list);
    },
    onReorderField: function (field, $list) {
      field.updateParent();
      this.renderFields($list);
    },
    onDeleteField: function (field) {
      // delete children
      field.getFields().map(function (child) {
        child.delete({
          animate: false
        });
      });
    },
    onChangeFieldType: function (field) {
      // enable browse field modal button
      field.$el.find('button.browse-fields').prop('disabled', false);
    },
    onDuplicateField: function (field, newField) {
      // check for children
      var children = newField.getFields();
      if (children.length) {
        // loop
        children.map(function (child) {
          // wipe field
          child.wipe();

          // if the child is open, re-fire the open method to ensure it's initialised correctly.
          if (child.isOpen()) {
            child.open();
          }

          // update parent
          child.updateParent();
        });

        // action
        acf.doAction('duplicate_field_objects', children, newField, field);
      }

      // set menu order
      this.setFieldMenuOrder(newField);
    },
    renderFields: function ($list) {
      // vars
      var fields = acf.getFieldObjects({
        list: $list
      });

      // no fields
      if (!fields.length) {
        $list.addClass('-empty');
        $list.parents('.acf-field-list-wrap').first().addClass('-empty');
        return;
      }

      // has fields
      $list.removeClass('-empty');
      $list.parents('.acf-field-list-wrap').first().removeClass('-empty');

      // prop
      fields.map(function (field, i) {
        field.prop('menu_order', i);
      });
    },
    onClickAdd: function (e, $el) {
      let $list;
      if ($el.hasClass('add-first-field')) {
        $list = $el.parents('.acf-field-list').eq(0);
      } else if ($el.parent().hasClass('acf-headerbar-actions') || $el.parent().hasClass('no-fields-message-inner')) {
        $list = $('.acf-field-list:first');
      } else if ($el.parent().hasClass('acf-sub-field-list-header')) {
        $list = $el.parents('.acf-input:first').find('.acf-field-list:first');
      } else {
        $list = $el.closest('.acf-tfoot').siblings('.acf-field-list');
      }
      this.addField($list);
    },
    addField: function ($list) {
      // vars
      var html = $('#tmpl-acf-field').html();
      var $el = $(html);
      var prevId = $el.data('id');
      var newKey = acf.uniqid('field_');

      // duplicate
      var $newField = acf.duplicate({
        target: $el,
        search: prevId,
        replace: newKey,
        append: function ($el, $el2) {
          $list.append($el2);
        }
      });

      // get instance
      var newField = acf.getFieldObject($newField);

      // props
      newField.prop('key', newKey);
      newField.prop('ID', 0);
      newField.prop('label', '');
      newField.prop('name', '');

      // attr
      $newField.attr('data-key', newKey);
      $newField.attr('data-id', newKey);

      // update parent prop
      newField.updateParent();

      // focus type
      var $type = newField.$input('type');
      setTimeout(function () {
        if ($list.hasClass('acf-auto-add-field')) {
          $list.removeClass('acf-auto-add-field');
        } else {
          $type.trigger('focus');
        }
      }, 251);

      // open
      newField.open();

      // set menu order
      this.renderFields($list);

      // action
      acf.doAction('add_field_object', newField);
      acf.doAction('append_field_object', newField);
    }
  });
})(jQuery);

/***/ }),

/***/ "./src/advanced-custom-fields-pro/assets/src/js/_field-group-locations.js":
/*!********************************************************************************!*\
  !*** ./src/advanced-custom-fields-pro/assets/src/js/_field-group-locations.js ***!
  \********************************************************************************/
/***/ (() => {

(function ($, undefined) {
  /**
   *  locationManager
   *
   *  Field group location rules functionality
   *
   *  @date	15/12/17
   *  @since	5.7.0
   *
   *  @param	void
   *  @return	void
   */

  var locationManager = new acf.Model({
    id: 'locationManager',
    wait: 'ready',
    events: {
      'click .add-location-rule': 'onClickAddRule',
      'click .add-location-group': 'onClickAddGroup',
      'click .remove-location-rule': 'onClickRemoveRule',
      'change .refresh-location-rule': 'onChangeRemoveRule'
    },
    initialize: function () {
      this.$el = $('#acf-field-group-options');
      this.addProLocations();
      this.updateGroupsClass();
    },
    addProLocations: function () {
      // Make sure we're only running this on free version.
      if (acf.get('is_pro')) {
        return;
      }

      // Loop over each pro field type and append it to the select.
      const PROLocationTypes = acf.get('PROLocationTypes');
      if (typeof PROLocationTypes !== 'object') return;
      const $formsGroup = this.$el.find('select.refresh-location-rule').find('optgroup[label="Forms"]');
      for (const [key, name] of Object.entries(PROLocationTypes)) {
        $formsGroup.append('<option value="null" disabled="disabled">' + name + ' (' + acf.__('PRO Only') + ')</option>');
      }
    },
    onClickAddRule: function (e, $el) {
      this.addRule($el.closest('tr'));
    },
    onClickRemoveRule: function (e, $el) {
      this.removeRule($el.closest('tr'));
    },
    onChangeRemoveRule: function (e, $el) {
      this.changeRule($el.closest('tr'));
    },
    onClickAddGroup: function (e, $el) {
      this.addGroup();
    },
    addRule: function ($tr) {
      acf.duplicate($tr);
      this.updateGroupsClass();
    },
    removeRule: function ($tr) {
      if ($tr.siblings('tr').length == 0) {
        $tr.closest('.rule-group').remove();
      } else {
        $tr.remove();
      }

      // Update h4
      var $group = this.$('.rule-group:first');
      $group.find('h4').text(acf.__('Show this field group if'));
      this.updateGroupsClass();
    },
    changeRule: function ($rule) {
      // vars
      var $group = $rule.closest('.rule-group');
      var prefix = $rule.find('td.param select').attr('name').replace('[param]', '');

      // ajaxdata
      var ajaxdata = {};
      ajaxdata.action = 'acf/field_group/render_location_rule';
      ajaxdata.rule = acf.serialize($rule, prefix);
      ajaxdata.rule.id = $rule.data('id');
      ajaxdata.rule.group = $group.data('id');

      // temp disable
      acf.disable($rule.find('td.value'));

      // ajax
      $.ajax({
        url: acf.get('ajaxurl'),
        data: acf.prepareForAjax(ajaxdata),
        type: 'post',
        dataType: 'html',
        success: function (html) {
          if (!html) return;
          $rule.replaceWith(html);
        }
      });
    },
    addGroup: function () {
      // vars
      var $group = this.$('.rule-group:last');

      // duplicate
      $group2 = acf.duplicate($group);

      // update h4
      $group2.find('h4').text(acf.__('or'));

      // remove all tr's except the first one
      $group2.find('tr').not(':first').remove();

      // update the groups class
      this.updateGroupsClass();
    },
    updateGroupsClass: function () {
      var $group = this.$('.rule-group:last');
      var $ruleGroups = $group.closest('.rule-groups');
      var rows_count = $ruleGroups.find('.acf-table tr').length;
      if (rows_count > 1) {
        $ruleGroups.addClass('rule-groups-multiple');
      } else {
        $ruleGroups.removeClass('rule-groups-multiple');
      }
    }
  });
})(jQuery);

/***/ }),

/***/ "./src/advanced-custom-fields-pro/assets/src/js/_field-group-settings.js":
/*!*******************************************************************************!*\
  !*** ./src/advanced-custom-fields-pro/assets/src/js/_field-group-settings.js ***!
  \*******************************************************************************/
/***/ (() => {

(function ($, undefined) {
  /**
   *  mid
   *
   *  Calculates the model ID for a field type
   *
   *  @date	15/12/17
   *  @since	5.6.5
   *
   *  @param	string type
   *  @return	string
   */

  var modelId = function (type) {
    return acf.strPascalCase(type || '') + 'FieldSetting';
  };

  /**
   *  registerFieldType
   *
   *  description
   *
   *  @date	14/12/17
   *  @since	5.6.5
   *
   *  @param	type $var Description. Default.
   *  @return	type Description.
   */

  acf.registerFieldSetting = function (model) {
    var proto = model.prototype;
    var mid = modelId(proto.type + ' ' + proto.name);
    this.models[mid] = model;
  };

  /**
   *  newField
   *
   *  description
   *
   *  @date	14/12/17
   *  @since	5.6.5
   *
   *  @param	type $var Description. Default.
   *  @return	type Description.
   */

  acf.newFieldSetting = function (field) {
    // vars
    var type = field.get('setting') || '';
    var name = field.get('name') || '';
    var mid = modelId(type + ' ' + name);
    var model = acf.models[mid] || null;

    // bail early if no setting
    if (model === null) return false;

    // instantiate
    var setting = new model(field);

    // return
    return setting;
  };

  /**
   *  acf.getFieldSetting
   *
   *  description
   *
   *  @date	19/4/18
   *  @since	5.6.9
   *
   *  @param	type $var Description. Default.
   *  @return	type Description.
   */

  acf.getFieldSetting = function (field) {
    // allow jQuery
    if (field instanceof jQuery) {
      field = acf.getField(field);
    }

    // return
    return field.setting;
  };

  /**
   * settingsManager
   *
   * @since	5.6.5
   *
   * @param	object The object containing the extended variables and methods.
   * @return	void
   */
  var settingsManager = new acf.Model({
    actions: {
      new_field: 'onNewField'
    },
    onNewField: function (field) {
      field.setting = acf.newFieldSetting(field);
    }
  });

  /**
   * acf.FieldSetting
   *
   * @since	5.6.5
   *
   * @param	object The object containing the extended variables and methods.
   * @return	void
   */
  acf.FieldSetting = acf.Model.extend({
    field: false,
    type: '',
    name: '',
    wait: 'ready',
    eventScope: '.acf-field',
    events: {
      change: 'render'
    },
    setup: function (field) {
      // vars
      var $field = field.$el;

      // set props
      this.$el = $field;
      this.field = field;
      this.$fieldObject = $field.closest('.acf-field-object');
      this.fieldObject = acf.getFieldObject(this.$fieldObject);

      // inherit data
      $.extend(this.data, field.data);
    },
    initialize: function () {
      this.render();
    },
    render: function () {
      // do nothing
    }
  });

  /**
   * Accordion and Tab Endpoint Settings
   *
   * The 'endpoint' setting on accordions and tabs requires an additional class on the
   * field object row when enabled.
   *
   * @since	6.0.0
   *
   * @param	object The object containing the extended variables and methods.
   * @return	void
   */
  var EndpointFieldSetting = acf.FieldSetting.extend({
    type: '',
    name: '',
    render: function () {
      var $endpoint_setting = this.fieldObject.$setting('endpoint');
      var $endpoint_field = $endpoint_setting.find('input[type="checkbox"]:first');
      if ($endpoint_field.is(':checked')) {
        this.fieldObject.$el.addClass('acf-field-is-endpoint');
      } else {
        this.fieldObject.$el.removeClass('acf-field-is-endpoint');
      }
    }
  });
  var AccordionEndpointFieldSetting = EndpointFieldSetting.extend({
    type: 'accordion',
    name: 'endpoint'
  });
  var TabEndpointFieldSetting = EndpointFieldSetting.extend({
    type: 'tab',
    name: 'endpoint'
  });
  acf.registerFieldSetting(AccordionEndpointFieldSetting);
  acf.registerFieldSetting(TabEndpointFieldSetting);

  /**
   * Date Picker
   *
   * This field type requires some extra logic for its settings
   *
   * @since	5.0.0
   *
   * @param	object The object containing the extended variables and methods.
   * @return	void
   */
  var DisplayFormatFieldSetting = acf.FieldSetting.extend({
    type: '',
    name: '',
    render: function () {
      var $input = this.$('input[type="radio"]:checked');
      if ($input.val() != 'other') {
        this.$('input[type="text"]').val($input.val());
      }
    }
  });
  var DatePickerDisplayFormatFieldSetting = DisplayFormatFieldSetting.extend({
    type: 'date_picker',
    name: 'display_format'
  });
  var DatePickerReturnFormatFieldSetting = DisplayFormatFieldSetting.extend({
    type: 'date_picker',
    name: 'return_format'
  });
  acf.registerFieldSetting(DatePickerDisplayFormatFieldSetting);
  acf.registerFieldSetting(DatePickerReturnFormatFieldSetting);

  /**
   * Date Time Picker
   *
   * This field type requires some extra logic for its settings
   *
   * @since	5.0.0
   *
   * @param	object The object containing the extended variables and methods.
   * @return	void
   */
  var DateTimePickerDisplayFormatFieldSetting = DisplayFormatFieldSetting.extend({
    type: 'date_time_picker',
    name: 'display_format'
  });
  var DateTimePickerReturnFormatFieldSetting = DisplayFormatFieldSetting.extend({
    type: 'date_time_picker',
    name: 'return_format'
  });
  acf.registerFieldSetting(DateTimePickerDisplayFormatFieldSetting);
  acf.registerFieldSetting(DateTimePickerReturnFormatFieldSetting);

  /**
   * Time Picker
   *
   * This field type requires some extra logic for its settings
   *
   * @since	5.0.0
   *
   * @param	object The object containing the extended variables and methods.
   * @return	void
   */
  var TimePickerDisplayFormatFieldSetting = DisplayFormatFieldSetting.extend({
    type: 'time_picker',
    name: 'display_format'
  });
  var TimePickerReturnFormatFieldSetting = DisplayFormatFieldSetting.extend({
    type: 'time_picker',
    name: 'return_format'
  });
  acf.registerFieldSetting(TimePickerDisplayFormatFieldSetting);
  acf.registerFieldSetting(TimePickerReturnFormatFieldSetting);

  /**
   * Color Picker Settings.
   *
   * @date	16/12/20
   * @since	5.9.4
   *
   * @param	object The object containing the extended variables and methods.
   * @return	void
   */
  var ColorPickerReturnFormat = acf.FieldSetting.extend({
    type: 'color_picker',
    name: 'enable_opacity',
    render: function () {
      var $return_format_setting = this.fieldObject.$setting('return_format');
      var $default_value_setting = this.fieldObject.$setting('default_value');
      var $labelText = $return_format_setting.find('input[type="radio"][value="string"]').parent('label').contents().last();
      var $defaultPlaceholder = $default_value_setting.find('input[type="text"]');
      var l10n = acf.get('colorPickerL10n');
      if (this.field.val()) {
        $labelText.replaceWith(l10n.rgba_string);
        $defaultPlaceholder.attr('placeholder', 'rgba(255,255,255,0.8)');
      } else {
        $labelText.replaceWith(l10n.hex_string);
        $defaultPlaceholder.attr('placeholder', '#FFFFFF');
      }
    }
  });
  acf.registerFieldSetting(ColorPickerReturnFormat);
})(jQuery);

/***/ }),

/***/ "./src/advanced-custom-fields-pro/assets/src/js/_field-group.js":
/*!**********************************************************************!*\
  !*** ./src/advanced-custom-fields-pro/assets/src/js/_field-group.js ***!
  \**********************************************************************/
/***/ (() => {

(function ($, undefined) {
  /**
   *  fieldGroupManager
   *
   *  Generic field group functionality
   *
   *  @date	15/12/17
   *  @since	5.7.0
   *
   *  @param	void
   *  @return	void
   */

  var fieldGroupManager = new acf.Model({
    id: 'fieldGroupManager',
    events: {
      'submit #post': 'onSubmit',
      'click a[href="#"]': 'onClick',
      'click .acf-delete-field-group': 'onClickDeleteFieldGroup',
      'blur input#title': 'validateTitle',
      'input input#title': 'validateTitle'
    },
    filters: {
      find_fields_args: 'filterFindFieldArgs',
      find_fields_selector: 'filterFindFieldsSelector'
    },
    initialize: function () {
      acf.addAction('prepare', this.maybeInitNewFieldGroup);
      acf.add_filter('select2_args', this.setBidirectionalSelect2Args);
      acf.add_filter('select2_ajax_data', this.setBidirectionalSelect2AjaxDataArgs);
    },
    setBidirectionalSelect2Args: function (args, $select, settings, field, instance) {
      if (field.data('key') !== 'bidirectional_target') return args;
      args.dropdownCssClass = 'field-type-select-results';
      args.templateResult = function (selection) {
        if ('undefined' !== typeof selection.element) {
          return selection;
        }
        if (selection.children) {
          return selection.text;
        }
        if (selection.loading || selection.element && selection.element.nodeName === 'OPTGROUP') {
          var $selection = $('<span class="acf-selection"></span>');
          $selection.html(acf.escHtml(selection.text));
          return $selection;
        }
        if ('undefined' === typeof selection.human_field_type || 'undefined' === typeof selection.field_type || 'undefined' === typeof selection.this_field) {
          return selection.text;
        }
        var $selection = $('<i title="' + acf.escHtml(selection.human_field_type) + '" class="field-type-icon field-type-icon-' + acf.escHtml(selection.field_type.replaceAll('_', '-')) + '"></i><span class="acf-selection has-icon">' + acf.escHtml(selection.text) + '</span>');
        if (selection.this_field) {
          $selection.last().append('<span class="acf-select2-default-pill">' + acf.__('This Field') + '</span>');
        }
        $selection.data('element', selection.element);
        return $selection;
      };
      return args;
    },
    setBidirectionalSelect2AjaxDataArgs: function (data, args, $input, field, instance) {
      if (data.field_key !== 'bidirectional_target') return data;
      const $fieldObject = acf.findFieldObjects({
        child: field
      });
      const fieldObject = acf.getFieldObject($fieldObject);
      data.field_key = '_acf_bidirectional_target';
      data.parent_key = fieldObject.get('key');
      data.field_type = fieldObject.get('type');

      // This might not be needed, but I wanted to figure out how to get a field setting in the JS API when the key isn't unique.
      data.post_type = acf.getField(acf.findFields({
        parent: $fieldObject,
        key: 'post_type'
      })).val();
      return data;
    },
    maybeInitNewFieldGroup: function () {
      let $field_list_wrapper = $('#acf-field-group-fields > .inside > .acf-field-list-wrap.acf-auto-add-field');
      if ($field_list_wrapper.length) {
        $('.acf-headerbar-actions .add-field').trigger('click');
        $('.acf-title-wrap #title').trigger('focus');
      }
    },
    onSubmit: function (e, $el) {
      // vars
      var $title = $('.acf-title-wrap #title');

      // empty
      if (!$title.val()) {
        // prevent default
        e.preventDefault();

        // unlock form
        acf.unlockForm($el);

        // focus
        $title.trigger('focus');
      }
    },
    onClick: function (e) {
      e.preventDefault();
    },
    onClickDeleteFieldGroup: function (e, $el) {
      e.preventDefault();
      $el.addClass('-hover');

      // Add confirmation tooltip.
      acf.newTooltip({
        confirm: true,
        target: $el,
        context: this,
        text: acf.__('Move field group to trash?'),
        confirm: function () {
          window.location.href = $el.attr('href');
        },
        cancel: function () {
          $el.removeClass('-hover');
        }
      });
    },
    validateTitle: function (e, $el) {
      let $submitButton = $('.acf-publish');
      if (!$el.val()) {
        $el.addClass('acf-input-error');
        $submitButton.addClass('disabled');
        $('.acf-publish').addClass('disabled');
      } else {
        $el.removeClass('acf-input-error');
        $submitButton.removeClass('disabled');
        $('.acf-publish').removeClass('disabled');
      }
    },
    filterFindFieldArgs: function (args) {
      args.visible = true;
      if (args.parent && (args.parent.hasClass('acf-field-object') || args.parent.hasClass('acf-browse-fields-modal-wrap') || args.parent.parents('.acf-field-object').length)) {
        args.visible = false;
        args.excludeSubFields = true;
      }

      // If the field has any open subfields, don't exclude subfields as they're already being displayed.
      if (args.parent && args.parent.find('.acf-field-object.open').length) {
        args.excludeSubFields = false;
      }
      return args;
    },
    filterFindFieldsSelector: function (selector) {
      return selector + ', .acf-field-acf-field-group-settings-tabs';
    }
  });

  /**
   *  screenOptionsManager
   *
   *  Screen options functionality
   *
   *  @date	15/12/17
   *  @since	5.7.0
   *
   *  @param	void
   *  @return	void
   */

  var screenOptionsManager = new acf.Model({
    id: 'screenOptionsManager',
    wait: 'prepare',
    events: {
      'change #acf-field-key-hide': 'onFieldKeysChange',
      'change #acf-field-settings-tabs': 'onFieldSettingsTabsChange',
      'change [name="screen_columns"]': 'render'
    },
    initialize: function () {
      // vars
      var $div = $('#adv-settings');
      var $append = $('#acf-append-show-on-screen');

      // append
      $div.find('.metabox-prefs').append($append.html());
      $div.find('.metabox-prefs br').remove();

      // clean up
      $append.remove();

      // initialize
      this.$el = $('#screen-options-wrap');

      // render
      this.render();
    },
    isFieldKeysChecked: function () {
      return this.$el.find('#acf-field-key-hide').prop('checked');
    },
    isFieldSettingsTabsChecked: function () {
      const $input = this.$el.find('#acf-field-settings-tabs');

      // Screen option is hidden by filter.
      if (!$input.length) {
        return false;
      }
      return $input.prop('checked');
    },
    getSelectedColumnCount: function () {
      return this.$el.find('input[name="screen_columns"]:checked').val();
    },
    onFieldKeysChange: function (e, $el) {
      var val = this.isFieldKeysChecked() ? 1 : 0;
      acf.updateUserSetting('show_field_keys', val);
      this.render();
    },
    onFieldSettingsTabsChange: function () {
      const val = this.isFieldSettingsTabsChecked() ? 1 : 0;
      acf.updateUserSetting('show_field_settings_tabs', val);
      this.render();
    },
    render: function () {
      if (this.isFieldKeysChecked()) {
        $('#acf-field-group-fields').addClass('show-field-keys');
      } else {
        $('#acf-field-group-fields').removeClass('show-field-keys');
      }
      if (!this.isFieldSettingsTabsChecked()) {
        $('#acf-field-group-fields').addClass('hide-tabs');
        $('.acf-field-settings-main').removeClass('acf-hidden').prop('hidden', false);
      } else {
        $('#acf-field-group-fields').removeClass('hide-tabs');
        $('.acf-field-object').each(function () {
          const tabFields = acf.getFields({
            type: 'tab',
            parent: $(this),
            excludeSubFields: true,
            limit: 1
          });
          if (tabFields.length) {
            tabFields[0].tabs.set('initialized', false);
          }
          acf.doAction('show', $(this));
        });
      }
      if (this.getSelectedColumnCount() == 1) {
        $('body').removeClass('columns-2');
        $('body').addClass('columns-1');
      } else {
        $('body').removeClass('columns-1');
        $('body').addClass('columns-2');
      }
    }
  });

  /**
   *  appendFieldManager
   *
   *  Appends fields together
   *
   *  @date	15/12/17
   *  @since	5.7.0
   *
   *  @param	void
   *  @return	void
   */

  var appendFieldManager = new acf.Model({
    actions: {
      new_field: 'onNewField'
    },
    onNewField: function (field) {
      // bail early if not append
      if (!field.has('append')) return;

      // vars
      var append = field.get('append');
      var $sibling = field.$el.siblings('[data-name="' + append + '"]').first();

      // bail early if no sibling
      if (!$sibling.length) return;

      // ul
      var $div = $sibling.children('.acf-input');
      var $ul = $div.children('ul');

      // create ul
      if (!$ul.length) {
        $div.wrapInner('<ul class="acf-hl"><li></li></ul>');
        $ul = $div.children('ul');
      }

      // li
      var html = field.$('.acf-input').html();
      var $li = $('<li>' + html + '</li>');
      $ul.append($li);
      $ul.attr('data-cols', $ul.children().length);

      // clean up
      field.remove();
    }
  });
})(jQuery);

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/esm/defineProperty.js":
/*!*******************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/esm/defineProperty.js ***!
  \*******************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ _defineProperty)
/* harmony export */ });
/* harmony import */ var _toPropertyKey_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./toPropertyKey.js */ "./node_modules/@babel/runtime/helpers/esm/toPropertyKey.js");

function _defineProperty(obj, key, value) {
  key = (0,_toPropertyKey_js__WEBPACK_IMPORTED_MODULE_0__["default"])(key);
  if (key in obj) {
    Object.defineProperty(obj, key, {
      value: value,
      enumerable: true,
      configurable: true,
      writable: true
    });
  } else {
    obj[key] = value;
  }
  return obj;
}

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/esm/toPrimitive.js":
/*!****************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/esm/toPrimitive.js ***!
  \****************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ _toPrimitive)
/* harmony export */ });
/* harmony import */ var _typeof_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./typeof.js */ "./node_modules/@babel/runtime/helpers/esm/typeof.js");

function _toPrimitive(input, hint) {
  if ((0,_typeof_js__WEBPACK_IMPORTED_MODULE_0__["default"])(input) !== "object" || input === null) return input;
  var prim = input[Symbol.toPrimitive];
  if (prim !== undefined) {
    var res = prim.call(input, hint || "default");
    if ((0,_typeof_js__WEBPACK_IMPORTED_MODULE_0__["default"])(res) !== "object") return res;
    throw new TypeError("@@toPrimitive must return a primitive value.");
  }
  return (hint === "string" ? String : Number)(input);
}

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/esm/toPropertyKey.js":
/*!******************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/esm/toPropertyKey.js ***!
  \******************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ _toPropertyKey)
/* harmony export */ });
/* harmony import */ var _typeof_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./typeof.js */ "./node_modules/@babel/runtime/helpers/esm/typeof.js");
/* harmony import */ var _toPrimitive_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./toPrimitive.js */ "./node_modules/@babel/runtime/helpers/esm/toPrimitive.js");


function _toPropertyKey(arg) {
  var key = (0,_toPrimitive_js__WEBPACK_IMPORTED_MODULE_1__["default"])(arg, "string");
  return (0,_typeof_js__WEBPACK_IMPORTED_MODULE_0__["default"])(key) === "symbol" ? key : String(key);
}

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/esm/typeof.js":
/*!***********************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/esm/typeof.js ***!
  \***********************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ _typeof)
/* harmony export */ });
function _typeof(obj) {
  "@babel/helpers - typeof";

  return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (obj) {
    return typeof obj;
  } : function (obj) {
    return obj && "function" == typeof Symbol && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj;
  }, _typeof(obj);
}

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
/*!*************************************************************************!*\
  !*** ./src/advanced-custom-fields-pro/assets/src/js/acf-field-group.js ***!
  \*************************************************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _field_group_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./_field-group.js */ "./src/advanced-custom-fields-pro/assets/src/js/_field-group.js");
/* harmony import */ var _field_group_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_field_group_js__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _field_group_field_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./_field-group-field.js */ "./src/advanced-custom-fields-pro/assets/src/js/_field-group-field.js");
/* harmony import */ var _field_group_field_js__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_field_group_field_js__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _field_group_settings_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./_field-group-settings.js */ "./src/advanced-custom-fields-pro/assets/src/js/_field-group-settings.js");
/* harmony import */ var _field_group_settings_js__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_field_group_settings_js__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _field_group_conditions_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./_field-group-conditions.js */ "./src/advanced-custom-fields-pro/assets/src/js/_field-group-conditions.js");
/* harmony import */ var _field_group_conditions_js__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_field_group_conditions_js__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _field_group_fields_js__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./_field-group-fields.js */ "./src/advanced-custom-fields-pro/assets/src/js/_field-group-fields.js");
/* harmony import */ var _field_group_fields_js__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_field_group_fields_js__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var _field_group_locations_js__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./_field-group-locations.js */ "./src/advanced-custom-fields-pro/assets/src/js/_field-group-locations.js");
/* harmony import */ var _field_group_locations_js__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(_field_group_locations_js__WEBPACK_IMPORTED_MODULE_5__);
/* harmony import */ var _field_group_compatibility_js__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./_field-group-compatibility.js */ "./src/advanced-custom-fields-pro/assets/src/js/_field-group-compatibility.js");
/* harmony import */ var _field_group_compatibility_js__WEBPACK_IMPORTED_MODULE_6___default = /*#__PURE__*/__webpack_require__.n(_field_group_compatibility_js__WEBPACK_IMPORTED_MODULE_6__);
/* harmony import */ var _browse_fields_modal_js__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./_browse-fields-modal.js */ "./src/advanced-custom-fields-pro/assets/src/js/_browse-fields-modal.js");








})();

/******/ })()
;
//# sourceMappingURL=acf-field-group.js.map