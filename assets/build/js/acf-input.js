/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./src/advanced-custom-fields-pro/assets/src/js/_acf-compatibility.js":
/*!****************************************************************************!*\
  !*** ./src/advanced-custom-fields-pro/assets/src/js/_acf-compatibility.js ***!
  \****************************************************************************/
/***/ (() => {

(function ($, undefined) {
  /**
   *  acf.newCompatibility
   *
   *  Inserts a new __proto__ object compatibility layer
   *
   *  @date	15/2/18
   *  @since	5.6.9
   *
   *  @param	object instance The object to modify.
   *  @param	object compatibilty Optional. The compatibilty layer.
   *  @return	object compatibilty
   */

  acf.newCompatibility = function (instance, compatibilty) {
    // defaults
    compatibilty = compatibilty || {};

    // inherit __proto_-
    compatibilty.__proto__ = instance.__proto__;

    // inject
    instance.__proto__ = compatibilty;

    // reference
    instance.compatibility = compatibilty;

    // return
    return compatibilty;
  };

  /**
   *  acf.getCompatibility
   *
   *  Returns the compatibility layer for a given instance
   *
   *  @date	13/3/18
   *  @since	5.6.9
   *
   *  @param	object		instance		The object to look in.
   *  @return	object|null	compatibility	The compatibility object or null on failure.
   */

  acf.getCompatibility = function (instance) {
    return instance.compatibility || null;
  };

  /**
   *  acf (compatibility)
   *
   *  Compatibility layer for the acf object
   *
   *  @date	15/2/18
   *  @since	5.6.9
   *
   *  @param	void
   *  @return	void
   */

  var _acf = acf.newCompatibility(acf, {
    // storage
    l10n: {},
    o: {},
    fields: {},
    // changed function names
    update: acf.set,
    add_action: acf.addAction,
    remove_action: acf.removeAction,
    do_action: acf.doAction,
    add_filter: acf.addFilter,
    remove_filter: acf.removeFilter,
    apply_filters: acf.applyFilters,
    parse_args: acf.parseArgs,
    disable_el: acf.disable,
    disable_form: acf.disable,
    enable_el: acf.enable,
    enable_form: acf.enable,
    update_user_setting: acf.updateUserSetting,
    prepare_for_ajax: acf.prepareForAjax,
    is_ajax_success: acf.isAjaxSuccess,
    remove_el: acf.remove,
    remove_tr: acf.remove,
    str_replace: acf.strReplace,
    render_select: acf.renderSelect,
    get_uniqid: acf.uniqid,
    serialize_form: acf.serialize,
    esc_html: acf.strEscape,
    str_sanitize: acf.strSanitize
  });
  _acf._e = function (k1, k2) {
    // defaults
    k1 = k1 || '';
    k2 = k2 || '';

    // compability
    var compatKey = k2 ? k1 + '.' + k2 : k1;
    var compats = {
      'image.select': 'Select Image',
      'image.edit': 'Edit Image',
      'image.update': 'Update Image'
    };
    if (compats[compatKey]) {
      return acf.__(compats[compatKey]);
    }

    // try k1
    var string = this.l10n[k1] || '';

    // try k2
    if (k2) {
      string = string[k2] || '';
    }

    // return
    return string;
  };
  _acf.get_selector = function (s) {
    // vars
    var selector = '.acf-field';

    // bail early if no search
    if (!s) {
      return selector;
    }

    // compatibility with object
    if ($.isPlainObject(s)) {
      if ($.isEmptyObject(s)) {
        return selector;
      } else {
        for (var k in s) {
          s = s[k];
          break;
        }
      }
    }

    // append
    selector += '-' + s;

    // replace underscores (split/join replaces all and is faster than regex!)
    selector = acf.strReplace('_', '-', selector);

    // remove potential double up
    selector = acf.strReplace('field-field-', 'field-', selector);

    // return
    return selector;
  };
  _acf.get_fields = function (s, $el, all) {
    // args
    var args = {
      is: s || '',
      parent: $el || false,
      suppressFilters: all || false
    };

    // change 'field_123' to '.acf-field-123'
    if (args.is) {
      args.is = this.get_selector(args.is);
    }

    // return
    return acf.findFields(args);
  };
  _acf.get_field = function (s, $el) {
    // get fields
    var $fields = this.get_fields.apply(this, arguments);

    // return
    if ($fields.length) {
      return $fields.first();
    } else {
      return false;
    }
  };
  _acf.get_closest_field = function ($el, s) {
    return $el.closest(this.get_selector(s));
  };
  _acf.get_field_wrap = function ($el) {
    return $el.closest(this.get_selector());
  };
  _acf.get_field_key = function ($field) {
    return $field.data('key');
  };
  _acf.get_field_type = function ($field) {
    return $field.data('type');
  };
  _acf.get_data = function ($el, defaults) {
    return acf.parseArgs($el.data(), defaults);
  };
  _acf.maybe_get = function (obj, key, value) {
    // default
    if (value === undefined) {
      value = null;
    }

    // get keys
    keys = String(key).split('.');

    // acf.isget
    for (var i = 0; i < keys.length; i++) {
      if (!obj.hasOwnProperty(keys[i])) {
        return value;
      }
      obj = obj[keys[i]];
    }
    return obj;
  };

  /**
   *  hooks
   *
   *  Modify add_action and add_filter functions to add compatibility with changed $field parameter
   *  Using the acf.add_action() or acf.add_filter() functions will interpret new field parameters as jQuery $field
   *
   *  @date	12/5/18
   *  @since	5.6.9
   *
   *  @param	void
   *  @return	void
   */

  var compatibleArgument = function (arg) {
    return arg instanceof acf.Field ? arg.$el : arg;
  };
  var compatibleArguments = function (args) {
    return acf.arrayArgs(args).map(compatibleArgument);
  };
  var compatibleCallback = function (origCallback) {
    return function () {
      // convert to compatible arguments
      if (arguments.length) {
        var args = compatibleArguments(arguments);

        // add default argument for 'ready', 'append' and 'load' events
      } else {
        var args = [$(document)];
      }

      // return
      return origCallback.apply(this, args);
    };
  };
  _acf.add_action = function (action, callback, priority, context) {
    // handle multiple actions
    var actions = action.split(' ');
    var length = actions.length;
    if (length > 1) {
      for (var i = 0; i < length; i++) {
        action = actions[i];
        _acf.add_action.apply(this, arguments);
      }
      return this;
    }

    // single
    var callback = compatibleCallback(callback);
    return acf.addAction.apply(this, arguments);
  };
  _acf.add_filter = function (action, callback, priority, context) {
    var callback = compatibleCallback(callback);
    return acf.addFilter.apply(this, arguments);
  };

  /*
   *  acf.model
   *
   *  This model acts as a scafold for action.event driven modules
   *
   *  @type	object
   *  @date	8/09/2014
   *  @since	5.0.0
   *
   *  @param	(object)
   *  @return	(object)
   */

  _acf.model = {
    actions: {},
    filters: {},
    events: {},
    extend: function (args) {
      // extend
      var model = $.extend({}, this, args);

      // setup actions
      $.each(model.actions, function (name, callback) {
        model._add_action(name, callback);
      });

      // setup filters
      $.each(model.filters, function (name, callback) {
        model._add_filter(name, callback);
      });

      // setup events
      $.each(model.events, function (name, callback) {
        model._add_event(name, callback);
      });

      // return
      return model;
    },
    _add_action: function (name, callback) {
      // split
      var model = this,
        data = name.split(' ');

      // add missing priority
      var name = data[0] || '',
        priority = data[1] || 10;

      // add action
      acf.add_action(name, model[callback], priority, model);
    },
    _add_filter: function (name, callback) {
      // split
      var model = this,
        data = name.split(' ');

      // add missing priority
      var name = data[0] || '',
        priority = data[1] || 10;

      // add action
      acf.add_filter(name, model[callback], priority, model);
    },
    _add_event: function (name, callback) {
      // vars
      var model = this,
        i = name.indexOf(' '),
        event = i > 0 ? name.substr(0, i) : name,
        selector = i > 0 ? name.substr(i + 1) : '';

      // event
      var fn = function (e) {
        // append $el to event object
        e.$el = $(this);

        // append $field to event object (used in field group)
        if (acf.field_group) {
          e.$field = e.$el.closest('.acf-field-object');
        }

        // event
        if (typeof model.event === 'function') {
          e = model.event(e);
        }

        // callback
        model[callback].apply(model, arguments);
      };

      // add event
      if (selector) {
        $(document).on(event, selector, fn);
      } else {
        $(document).on(event, fn);
      }
    },
    get: function (name, value) {
      // defaults
      value = value || null;

      // get
      if (typeof this[name] !== 'undefined') {
        value = this[name];
      }

      // return
      return value;
    },
    set: function (name, value) {
      // set
      this[name] = value;

      // function for 3rd party
      if (typeof this['_set_' + name] === 'function') {
        this['_set_' + name].apply(this);
      }

      // return for chaining
      return this;
    }
  };

  /*
   *  field
   *
   *  This model sets up many of the field's interactions
   *
   *  @type	function
   *  @date	21/02/2014
   *  @since	3.5.1
   *
   *  @param	n/a
   *  @return	n/a
   */

  _acf.field = acf.model.extend({
    type: '',
    o: {},
    $field: null,
    _add_action: function (name, callback) {
      // vars
      var model = this;

      // update name
      name = name + '_field/type=' + model.type;

      // add action
      acf.add_action(name, function ($field) {
        // focus
        model.set('$field', $field);

        // callback
        model[callback].apply(model, arguments);
      });
    },
    _add_filter: function (name, callback) {
      // vars
      var model = this;

      // update name
      name = name + '_field/type=' + model.type;

      // add action
      acf.add_filter(name, function ($field) {
        // focus
        model.set('$field', $field);

        // callback
        model[callback].apply(model, arguments);
      });
    },
    _add_event: function (name, callback) {
      // vars
      var model = this,
        event = name.substr(0, name.indexOf(' ')),
        selector = name.substr(name.indexOf(' ') + 1),
        context = acf.get_selector(model.type);

      // add event
      $(document).on(event, context + ' ' + selector, function (e) {
        // vars
        var $el = $(this);
        var $field = acf.get_closest_field($el, model.type);

        // bail early if no field
        if (!$field.length) return;

        // focus
        if (!$field.is(model.$field)) {
          model.set('$field', $field);
        }

        // append to event
        e.$el = $el;
        e.$field = $field;

        // callback
        model[callback].apply(model, [e]);
      });
    },
    _set_$field: function () {
      // callback
      if (typeof this.focus === 'function') {
        this.focus();
      }
    },
    // depreciated
    doFocus: function ($field) {
      return this.set('$field', $field);
    }
  });

  /**
   *  validation
   *
   *  description
   *
   *  @date	15/2/18
   *  @since	5.6.9
   *
   *  @param	type $var Description. Default.
   *  @return	type Description.
   */

  var _validation = acf.newCompatibility(acf.validation, {
    remove_error: function ($field) {
      acf.getField($field).removeError();
    },
    add_warning: function ($field, message) {
      acf.getField($field).showNotice({
        text: message,
        type: 'warning',
        timeout: 1000
      });
    },
    fetch: acf.validateForm,
    enableSubmit: acf.enableSubmit,
    disableSubmit: acf.disableSubmit,
    showSpinner: acf.showSpinner,
    hideSpinner: acf.hideSpinner,
    unlockForm: acf.unlockForm,
    lockForm: acf.lockForm
  });

  /**
   *  tooltip
   *
   *  description
   *
   *  @date	15/2/18
   *  @since	5.6.9
   *
   *  @param	type $var Description. Default.
   *  @return	type Description.
   */

  _acf.tooltip = {
    tooltip: function (text, $el) {
      var tooltip = acf.newTooltip({
        text: text,
        target: $el
      });

      // return
      return tooltip.$el;
    },
    temp: function (text, $el) {
      var tooltip = acf.newTooltip({
        text: text,
        target: $el,
        timeout: 250
      });
    },
    confirm: function ($el, callback, text, button_y, button_n) {
      var tooltip = acf.newTooltip({
        confirm: true,
        text: text,
        target: $el,
        confirm: function () {
          callback(true);
        },
        cancel: function () {
          callback(false);
        }
      });
    },
    confirm_remove: function ($el, callback) {
      var tooltip = acf.newTooltip({
        confirmRemove: true,
        target: $el,
        confirm: function () {
          callback(true);
        },
        cancel: function () {
          callback(false);
        }
      });
    }
  };

  /**
   *  tooltip
   *
   *  description
   *
   *  @date	15/2/18
   *  @since	5.6.9
   *
   *  @param	type $var Description. Default.
   *  @return	type Description.
   */

  _acf.media = new acf.Model({
    activeFrame: false,
    actions: {
      new_media_popup: 'onNewMediaPopup'
    },
    frame: function () {
      return this.activeFrame;
    },
    onNewMediaPopup: function (popup) {
      this.activeFrame = popup.frame;
    },
    popup: function (props) {
      // update props
      if (props.mime_types) {
        props.allowedTypes = props.mime_types;
      }
      if (props.id) {
        props.attachment = props.id;
      }

      // new
      var popup = acf.newMediaPopup(props);

      // append
      /*
      if( props.selected ) {
      	popup.selected = props.selected;
      }
      */

      // return
      return popup.frame;
    }
  });

  /**
   *  Select2
   *
   *  description
   *
   *  @date	11/6/18
   *  @since	5.6.9
   *
   *  @param	type $var Description. Default.
   *  @return	type Description.
   */

  _acf.select2 = {
    init: function ($select, args, $field) {
      // compatible args
      if (args.allow_null) {
        args.allowNull = args.allow_null;
      }
      if (args.ajax_action) {
        args.ajaxAction = args.ajax_action;
      }
      if ($field) {
        args.field = acf.getField($field);
      }

      // return
      return acf.newSelect2($select, args);
    },
    destroy: function ($select) {
      return acf.getInstance($select).destroy();
    }
  };

  /**
   *  postbox
   *
   *  description
   *
   *  @date	11/6/18
   *  @since	5.6.9
   *
   *  @param	type $var Description. Default.
   *  @return	type Description.
   */

  _acf.postbox = {
    render: function (args) {
      // compatible args
      if (args.edit_url) {
        args.editLink = args.edit_url;
      }
      if (args.edit_title) {
        args.editTitle = args.edit_title;
      }

      // return
      return acf.newPostbox(args);
    }
  };

  /**
   *  acf.screen
   *
   *  description
   *
   *  @date	11/6/18
   *  @since	5.6.9
   *
   *  @param	type $var Description. Default.
   *  @return	type Description.
   */

  acf.newCompatibility(acf.screen, {
    update: function () {
      return this.set.apply(this, arguments);
    },
    fetch: acf.screen.check
  });
  _acf.ajax = acf.screen;
})(jQuery);

/***/ }),

/***/ "./src/advanced-custom-fields-pro/assets/src/js/_acf-condition-types.js":
/*!******************************************************************************!*\
  !*** ./src/advanced-custom-fields-pro/assets/src/js/_acf-condition-types.js ***!
  \******************************************************************************/
/***/ (() => {

(function ($, undefined) {
  var __ = acf.__;
  var parseString = function (val) {
    return val ? '' + val : '';
  };
  var isEqualTo = function (v1, v2) {
    return parseString(v1).toLowerCase() === parseString(v2).toLowerCase();
  };
  var isEqualToNumber = function (v1, v2) {
    return parseFloat(v1) === parseFloat(v2);
  };
  var isGreaterThan = function (v1, v2) {
    return parseFloat(v1) > parseFloat(v2);
  };
  var isLessThan = function (v1, v2) {
    return parseFloat(v1) < parseFloat(v2);
  };
  var inArray = function (v1, array) {
    // cast all values as string
    array = array.map(function (v2) {
      return parseString(v2);
    });
    return array.indexOf(v1) > -1;
  };
  var containsString = function (haystack, needle) {
    return parseString(haystack).indexOf(parseString(needle)) > -1;
  };
  var matchesPattern = function (v1, pattern) {
    var regexp = new RegExp(parseString(pattern), 'gi');
    return parseString(v1).match(regexp);
  };

  /**
   *  hasValue
   *
   *  description
   *
   *  @date	1/2/18
   *  @since	5.6.5
   *
   *  @param	void
   *  @return	void
   */

  var HasValue = acf.Condition.extend({
    type: 'hasValue',
    operator: '!=empty',
    label: __('Has any value'),
    fieldTypes: ['text', 'textarea', 'number', 'range', 'email', 'url', 'password', 'image', 'file', 'wysiwyg', 'oembed', 'select', 'checkbox', 'radio', 'button_group', 'link', 'post_object', 'page_link', 'relationship', 'taxonomy', 'user', 'google_map', 'date_picker', 'date_time_picker', 'time_picker', 'color_picker'],
    match: function (rule, field) {
      let val = field.val();
      if (val instanceof Array) {
        val = val.length;
      }
      return val ? true : false;
    },
    choices: function (fieldObject) {
      return '<input type="text" disabled="" />';
    }
  });
  acf.registerConditionType(HasValue);

  /**
   *  hasValue
   *
   *  description
   *
   *  @date	1/2/18
   *  @since	5.6.5
   *
   *  @param	void
   *  @return	void
   */

  var HasNoValue = HasValue.extend({
    type: 'hasNoValue',
    operator: '==empty',
    label: __('Has no value'),
    match: function (rule, field) {
      return !HasValue.prototype.match.apply(this, arguments);
    }
  });
  acf.registerConditionType(HasNoValue);

  /**
   *  EqualTo
   *
   *  description
   *
   *  @date	1/2/18
   *  @since	5.6.5
   *
   *  @param	void
   *  @return	void
   */

  var EqualTo = acf.Condition.extend({
    type: 'equalTo',
    operator: '==',
    label: __('Value is equal to'),
    fieldTypes: ['text', 'textarea', 'number', 'range', 'email', 'url', 'password'],
    match: function (rule, field) {
      if (acf.isNumeric(rule.value)) {
        return isEqualToNumber(rule.value, field.val());
      } else {
        return isEqualTo(rule.value, field.val());
      }
    },
    choices: function (fieldObject) {
      return '<input type="text" />';
    }
  });
  acf.registerConditionType(EqualTo);

  /**
   *  NotEqualTo
   *
   *  description
   *
   *  @date	1/2/18
   *  @since	5.6.5
   *
   *  @param	void
   *  @return	void
   */

  var NotEqualTo = EqualTo.extend({
    type: 'notEqualTo',
    operator: '!=',
    label: __('Value is not equal to'),
    match: function (rule, field) {
      return !EqualTo.prototype.match.apply(this, arguments);
    }
  });
  acf.registerConditionType(NotEqualTo);

  /**
   *  PatternMatch
   *
   *  description
   *
   *  @date	1/2/18
   *  @since	5.6.5
   *
   *  @param	void
   *  @return	void
   */

  var PatternMatch = acf.Condition.extend({
    type: 'patternMatch',
    operator: '==pattern',
    label: __('Value matches pattern'),
    fieldTypes: ['text', 'textarea', 'email', 'url', 'password', 'wysiwyg'],
    match: function (rule, field) {
      return matchesPattern(field.val(), rule.value);
    },
    choices: function (fieldObject) {
      return '<input type="text" placeholder="[a-z0-9]" />';
    }
  });
  acf.registerConditionType(PatternMatch);

  /**
   *  Contains
   *
   *  description
   *
   *  @date	1/2/18
   *  @since	5.6.5
   *
   *  @param	void
   *  @return	void
   */

  var Contains = acf.Condition.extend({
    type: 'contains',
    operator: '==contains',
    label: __('Value contains'),
    fieldTypes: ['text', 'textarea', 'number', 'email', 'url', 'password', 'wysiwyg', 'oembed', 'select'],
    match: function (rule, field) {
      return containsString(field.val(), rule.value);
    },
    choices: function (fieldObject) {
      return '<input type="text" />';
    }
  });
  acf.registerConditionType(Contains);

  /**
   *  TrueFalseEqualTo
   *
   *  description
   *
   *  @date	1/2/18
   *  @since	5.6.5
   *
   *  @param	void
   *  @return	void
   */

  var TrueFalseEqualTo = EqualTo.extend({
    type: 'trueFalseEqualTo',
    choiceType: 'select',
    fieldTypes: ['true_false'],
    choices: function (field) {
      return [{
        id: 1,
        text: __('Checked')
      }];
    }
  });
  acf.registerConditionType(TrueFalseEqualTo);

  /**
   *  TrueFalseNotEqualTo
   *
   *  description
   *
   *  @date	1/2/18
   *  @since	5.6.5
   *
   *  @param	void
   *  @return	void
   */

  var TrueFalseNotEqualTo = NotEqualTo.extend({
    type: 'trueFalseNotEqualTo',
    choiceType: 'select',
    fieldTypes: ['true_false'],
    choices: function (field) {
      return [{
        id: 1,
        text: __('Checked')
      }];
    }
  });
  acf.registerConditionType(TrueFalseNotEqualTo);

  /**
   *  SelectEqualTo
   *
   *  description
   *
   *  @date	1/2/18
   *  @since	5.6.5
   *
   *  @param	void
   *  @return	void
   */

  var SelectEqualTo = acf.Condition.extend({
    type: 'selectEqualTo',
    operator: '==',
    label: __('Value is equal to'),
    fieldTypes: ['select', 'checkbox', 'radio', 'button_group'],
    match: function (rule, field) {
      var val = field.val();
      if (val instanceof Array) {
        return inArray(rule.value, val);
      } else {
        return isEqualTo(rule.value, val);
      }
    },
    choices: function (fieldObject) {
      // vars
      var choices = [];
      var lines = fieldObject.$setting('choices textarea').val().split('\n');

      // allow null
      if (fieldObject.$input('allow_null').prop('checked')) {
        choices.push({
          id: '',
          text: __('Null')
        });
      }

      // loop
      lines.map(function (line) {
        // split
        line = line.split(':');

        // default label to value
        line[1] = line[1] || line[0];

        // append
        choices.push({
          id: line[0].trim(),
          text: line[1].trim()
        });
      });

      // return
      return choices;
    }
  });
  acf.registerConditionType(SelectEqualTo);

  /**
   *  SelectNotEqualTo
   *
   *  description
   *
   *  @date	1/2/18
   *  @since	5.6.5
   *
   *  @param	void
   *  @return	void
   */

  var SelectNotEqualTo = SelectEqualTo.extend({
    type: 'selectNotEqualTo',
    operator: '!=',
    label: __('Value is not equal to'),
    match: function (rule, field) {
      return !SelectEqualTo.prototype.match.apply(this, arguments);
    }
  });
  acf.registerConditionType(SelectNotEqualTo);

  /**
   *  GreaterThan
   *
   *  description
   *
   *  @date	1/2/18
   *  @since	5.6.5
   *
   *  @param	void
   *  @return	void
   */

  var GreaterThan = acf.Condition.extend({
    type: 'greaterThan',
    operator: '>',
    label: __('Value is greater than'),
    fieldTypes: ['number', 'range'],
    match: function (rule, field) {
      var val = field.val();
      if (val instanceof Array) {
        val = val.length;
      }
      return isGreaterThan(val, rule.value);
    },
    choices: function (fieldObject) {
      return '<input type="number" />';
    }
  });
  acf.registerConditionType(GreaterThan);

  /**
   *  LessThan
   *
   *  description
   *
   *  @date	1/2/18
   *  @since	5.6.5
   *
   *  @param	void
   *  @return	void
   */

  var LessThan = GreaterThan.extend({
    type: 'lessThan',
    operator: '<',
    label: __('Value is less than'),
    match: function (rule, field) {
      var val = field.val();
      if (val instanceof Array) {
        val = val.length;
      }
      if (val === undefined || val === null || val === false) {
        return true;
      }
      return isLessThan(val, rule.value);
    },
    choices: function (fieldObject) {
      return '<input type="number" />';
    }
  });
  acf.registerConditionType(LessThan);

  /**
   *  SelectedGreaterThan
   *
   *  description
   *
   *  @date	1/2/18
   *  @since	5.6.5
   *
   *  @param	void
   *  @return	void
   */

  var SelectionGreaterThan = GreaterThan.extend({
    type: 'selectionGreaterThan',
    label: __('Selection is greater than'),
    fieldTypes: ['checkbox', 'select', 'post_object', 'page_link', 'relationship', 'taxonomy', 'user']
  });
  acf.registerConditionType(SelectionGreaterThan);

  /**
   *  SelectedGreaterThan
   *
   *  description
   *
   *  @date	1/2/18
   *  @since	5.6.5
   *
   *  @param	void
   *  @return	void
   */

  var SelectionLessThan = LessThan.extend({
    type: 'selectionLessThan',
    label: __('Selection is less than'),
    fieldTypes: ['checkbox', 'select', 'post_object', 'page_link', 'relationship', 'taxonomy', 'user']
  });
  acf.registerConditionType(SelectionLessThan);
})(jQuery);

/***/ }),

/***/ "./src/advanced-custom-fields-pro/assets/src/js/_acf-condition.js":
/*!************************************************************************!*\
  !*** ./src/advanced-custom-fields-pro/assets/src/js/_acf-condition.js ***!
  \************************************************************************/
/***/ (() => {

(function ($, undefined) {
  // vars
  var storage = [];

  /**
   *  acf.Condition
   *
   *  description
   *
   *  @date	23/3/18
   *  @since	5.6.9
   *
   *  @param	type $var Description. Default.
   *  @return	type Description.
   */

  acf.Condition = acf.Model.extend({
    type: '',
    // used for model name
    operator: '==',
    // rule operator
    label: '',
    // label shown when editing fields
    choiceType: 'input',
    // input, select
    fieldTypes: [],
    // auto connect this conditions with these field types

    data: {
      conditions: false,
      // the parent instance
      field: false,
      // the field which we query against
      rule: {} // the rule [field, operator, value]
    },
    events: {
      change: 'change',
      keyup: 'change',
      enableField: 'change',
      disableField: 'change'
    },
    setup: function (props) {
      $.extend(this.data, props);
    },
    getEventTarget: function ($el, event) {
      return $el || this.get('field').$el;
    },
    change: function (e, $el) {
      this.get('conditions').change(e);
    },
    match: function (rule, field) {
      return false;
    },
    calculate: function () {
      return this.match(this.get('rule'), this.get('field'));
    },
    choices: function (field) {
      return '<input type="text" />';
    }
  });

  /**
   *  acf.newCondition
   *
   *  description
   *
   *  @date	1/2/18
   *  @since	5.6.5
   *
   *  @param	type $var Description. Default.
   *  @return	type Description.
   */

  acf.newCondition = function (rule, conditions) {
    // currently setting up conditions for fieldX, this field is the 'target'
    var target = conditions.get('field');

    // use the 'target' to find the 'trigger' field.
    // - this field is used to setup the conditional logic events
    var field = target.getField(rule.field);

    // bail early if no target or no field (possible if field doesn't exist due to HTML error)
    if (!target || !field) {
      return false;
    }

    // vars
    var args = {
      rule: rule,
      target: target,
      conditions: conditions,
      field: field
    };

    // vars
    var fieldType = field.get('type');
    var operator = rule.operator;

    // get avaibale conditions
    var conditionTypes = acf.getConditionTypes({
      fieldType: fieldType,
      operator: operator
    });

    // instantiate
    var model = conditionTypes[0] || acf.Condition;

    // instantiate
    var condition = new model(args);

    // return
    return condition;
  };

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
    return acf.strPascalCase(type || '') + 'Condition';
  };

  /**
   *  acf.registerConditionType
   *
   *  description
   *
   *  @date	1/2/18
   *  @since	5.6.5
   *
   *  @param	type $var Description. Default.
   *  @return	type Description.
   */

  acf.registerConditionType = function (model) {
    // vars
    var proto = model.prototype;
    var type = proto.type;
    var mid = modelId(type);

    // store model
    acf.models[mid] = model;

    // store reference
    storage.push(type);
  };

  /**
   *  acf.getConditionType
   *
   *  description
   *
   *  @date	1/2/18
   *  @since	5.6.5
   *
   *  @param	type $var Description. Default.
   *  @return	type Description.
   */

  acf.getConditionType = function (type) {
    var mid = modelId(type);
    return acf.models[mid] || false;
  };

  /**
   *  acf.registerConditionForFieldType
   *
   *  description
   *
   *  @date	1/2/18
   *  @since	5.6.5
   *
   *  @param	type $var Description. Default.
   *  @return	type Description.
   */

  acf.registerConditionForFieldType = function (conditionType, fieldType) {
    // get model
    var model = acf.getConditionType(conditionType);

    // append
    if (model) {
      model.prototype.fieldTypes.push(fieldType);
    }
  };

  /**
   *  acf.getConditionTypes
   *
   *  description
   *
   *  @date	1/2/18
   *  @since	5.6.5
   *
   *  @param	type $var Description. Default.
   *  @return	type Description.
   */

  acf.getConditionTypes = function (args) {
    // defaults
    args = acf.parseArgs(args, {
      fieldType: '',
      operator: ''
    });

    // clonse available types
    var types = [];

    // loop
    storage.map(function (type) {
      // vars
      var model = acf.getConditionType(type);
      var ProtoFieldTypes = model.prototype.fieldTypes;
      var ProtoOperator = model.prototype.operator;

      // check fieldType
      if (args.fieldType && ProtoFieldTypes.indexOf(args.fieldType) === -1) {
        return;
      }

      // check operator
      if (args.operator && ProtoOperator !== args.operator) {
        return;
      }

      // append
      types.push(model);
    });

    // return
    return types;
  };
})(jQuery);

/***/ }),

/***/ "./src/advanced-custom-fields-pro/assets/src/js/_acf-conditions.js":
/*!*************************************************************************!*\
  !*** ./src/advanced-custom-fields-pro/assets/src/js/_acf-conditions.js ***!
  \*************************************************************************/
/***/ (() => {

(function ($, undefined) {
  // vars
  var CONTEXT = 'conditional_logic';

  /**
   *  conditionsManager
   *
   *  description
   *
   *  @date	1/2/18
   *  @since	5.6.5
   *
   *  @param	type $var Description. Default.
   *  @return	type Description.
   */

  var conditionsManager = new acf.Model({
    id: 'conditionsManager',
    priority: 20,
    // run actions later

    actions: {
      new_field: 'onNewField'
    },
    onNewField: function (field) {
      if (field.has('conditions')) {
        field.getConditions().render();
      }
    }
  });

  /**
   *  acf.Field.prototype.getField
   *
   *  Finds a field that is related to another field
   *
   *  @date	1/2/18
   *  @since	5.6.5
   *
   *  @param	type $var Description. Default.
   *  @return	type Description.
   */

  var getSiblingField = function (field, key) {
    // find sibling (very fast)
    var fields = acf.getFields({
      key: key,
      sibling: field.$el,
      suppressFilters: true
    });

    // find sibling-children (fast)
    // needed for group fields, accordions, etc
    if (!fields.length) {
      fields = acf.getFields({
        key: key,
        parent: field.$el.parent(),
        suppressFilters: true
      });
    }

    // Check for fields on other settings tabs (probably less fast).
    if (!fields.length && $('.acf-field-settings').length) {
      fields = acf.getFields({
        key: key,
        parent: field.$el.parents('.acf-field-settings:first'),
        suppressFilters: true
      });
    }
    if (!fields.length && $('#acf-basic-settings').length) {
      fields = acf.getFields({
        key: key,
        parent: $('#acf-basic-settings'),
        suppressFilters: true
      });
    }

    // return
    if (fields.length) {
      return fields[0];
    }
    return false;
  };
  acf.Field.prototype.getField = function (key) {
    // get sibling field
    var field = getSiblingField(this, key);

    // return early
    if (field) {
      return field;
    }

    // move up through each parent and try again
    var parents = this.parents();
    for (var i = 0; i < parents.length; i++) {
      // get sibling field
      field = getSiblingField(parents[i], key);

      // return early
      if (field) {
        return field;
      }
    }

    // return
    return false;
  };

  /**
   *  acf.Field.prototype.getConditions
   *
   *  Returns the field's conditions instance
   *
   *  @date	1/2/18
   *  @since	5.6.5
   *
   *  @param	type $var Description. Default.
   *  @return	type Description.
   */

  acf.Field.prototype.getConditions = function () {
    // instantiate
    if (!this.conditions) {
      this.conditions = new Conditions(this);
    }

    // return
    return this.conditions;
  };

  /**
   *  Conditions
   *
   *  description
   *
   *  @date	1/2/18
   *  @since	5.6.5
   *
   *  @param	type $var Description. Default.
   *  @return	type Description.
   */
  var timeout = false;
  var Conditions = acf.Model.extend({
    id: 'Conditions',
    data: {
      field: false,
      // The field with "data-conditions" (target).
      timeStamp: false,
      // Reference used during "change" event.
      groups: [] // The groups of condition instances.
    },
    setup: function (field) {
      // data
      this.data.field = field;

      // vars
      var conditions = field.get('conditions');

      // detect groups
      if (conditions instanceof Array) {
        // detect groups
        if (conditions[0] instanceof Array) {
          // loop
          conditions.map(function (rules, i) {
            this.addRules(rules, i);
          }, this);

          // detect rules
        } else {
          this.addRules(conditions);
        }

        // detect rule
      } else {
        this.addRule(conditions);
      }
    },
    change: function (e) {
      // this function may be triggered multiple times per event due to multiple condition classes
      // compare timestamp to allow only 1 trigger per event
      if (this.get('timeStamp') === e.timeStamp) {
        return false;
      } else {
        this.set('timeStamp', e.timeStamp, true);
      }

      // render condition and store result
      var changed = this.render();
    },
    render: function () {
      return this.calculate() ? this.show() : this.hide();
    },
    show: function () {
      return this.get('field').showEnable(this.cid, CONTEXT);
    },
    hide: function () {
      return this.get('field').hideDisable(this.cid, CONTEXT);
    },
    calculate: function () {
      // vars
      var pass = false;

      // loop
      this.getGroups().map(function (group) {
        // igrnore this group if another group passed
        if (pass) return;

        // find passed
        var passed = group.filter(function (condition) {
          return condition.calculate();
        });

        // if all conditions passed, update the global var
        if (passed.length == group.length) {
          pass = true;
        }
      });
      return pass;
    },
    hasGroups: function () {
      return this.data.groups != null;
    },
    getGroups: function () {
      return this.data.groups;
    },
    addGroup: function () {
      var group = [];
      this.data.groups.push(group);
      return group;
    },
    hasGroup: function (i) {
      return this.data.groups[i] != null;
    },
    getGroup: function (i) {
      return this.data.groups[i];
    },
    removeGroup: function (i) {
      this.data.groups[i].delete;
      return this;
    },
    addRules: function (rules, group) {
      rules.map(function (rule) {
        this.addRule(rule, group);
      }, this);
    },
    addRule: function (rule, group) {
      // defaults
      group = group || 0;

      // vars
      var groupArray;

      // get group
      if (this.hasGroup(group)) {
        groupArray = this.getGroup(group);
      } else {
        groupArray = this.addGroup();
      }

      // instantiate
      var condition = acf.newCondition(rule, this);

      // bail early if condition failed (field did not exist)
      if (!condition) {
        return false;
      }

      // add rule
      groupArray.push(condition);
    },
    hasRule: function () {},
    getRule: function (rule, group) {
      // defaults
      rule = rule || 0;
      group = group || 0;
      return this.data.groups[group][rule];
    },
    removeRule: function () {}
  });
})(jQuery);

/***/ }),

/***/ "./src/advanced-custom-fields-pro/assets/src/js/_acf-field-accordion.js":
/*!******************************************************************************!*\
  !*** ./src/advanced-custom-fields-pro/assets/src/js/_acf-field-accordion.js ***!
  \******************************************************************************/
/***/ (() => {

(function ($, undefined) {
  var i = 0;
  var Field = acf.Field.extend({
    type: 'accordion',
    wait: '',
    $control: function () {
      return this.$('.acf-fields:first');
    },
    initialize: function () {
      // Bail early if this is a duplicate of an existing initialized accordion.
      if (this.$el.hasClass('acf-accordion')) {
        return;
      }

      // bail early if is cell
      if (this.$el.is('td')) return;

      // enpoint
      if (this.get('endpoint')) {
        return this.remove();
      }

      // vars
      var $field = this.$el;
      var $label = this.$labelWrap();
      var $input = this.$inputWrap();
      var $wrap = this.$control();
      var $instructions = $input.children('.description');

      // force description into label
      if ($instructions.length) {
        $label.append($instructions);
      }

      // table
      if (this.$el.is('tr')) {
        // vars
        var $table = this.$el.closest('table');
        var $newLabel = $('<div class="acf-accordion-title"/>');
        var $newInput = $('<div class="acf-accordion-content"/>');
        var $newTable = $('<table class="' + $table.attr('class') + '"/>');
        var $newWrap = $('<tbody/>');

        // dom
        $newLabel.append($label.html());
        $newTable.append($newWrap);
        $newInput.append($newTable);
        $input.append($newLabel);
        $input.append($newInput);

        // modify
        $label.remove();
        $wrap.remove();
        $input.attr('colspan', 2);

        // update vars
        $label = $newLabel;
        $input = $newInput;
        $wrap = $newWrap;
      }

      // add classes
      $field.addClass('acf-accordion');
      $label.addClass('acf-accordion-title');
      $input.addClass('acf-accordion-content');

      // index
      i++;

      // multi-expand
      if (this.get('multi_expand')) {
        $field.attr('multi-expand', 1);
      }

      // open
      var order = acf.getPreference('this.accordions') || [];
      if (order[i - 1] !== undefined) {
        this.set('open', order[i - 1]);
      }
      if (this.get('open')) {
        $field.addClass('-open');
        $input.css('display', 'block'); // needed for accordion to close smoothly
      }

      // add icon
      $label.prepend(accordionManager.iconHtml({
        open: this.get('open')
      }));

      // classes
      // - remove 'inside' which is a #poststuff WP class
      var $parent = $field.parent();
      $wrap.addClass($parent.hasClass('-left') ? '-left' : '');
      $wrap.addClass($parent.hasClass('-clear') ? '-clear' : '');

      // append
      $wrap.append($field.nextUntil('.acf-field-accordion', '.acf-field'));

      // clean up
      $wrap.removeAttr('data-open data-multi_expand data-endpoint');
    }
  });
  acf.registerFieldType(Field);

  /**
   *  accordionManager
   *
   *  Events manager for the acf accordion
   *
   *  @date	14/2/18
   *  @since	5.6.9
   *
   *  @param	void
   *  @return	void
   */

  var accordionManager = new acf.Model({
    actions: {
      unload: 'onUnload'
    },
    events: {
      'click .acf-accordion-title': 'onClick',
      'invalidField .acf-accordion': 'onInvalidField'
    },
    isOpen: function ($el) {
      return $el.hasClass('-open');
    },
    toggle: function ($el) {
      if (this.isOpen($el)) {
        this.close($el);
      } else {
        this.open($el);
      }
    },
    iconHtml: function (props) {
      // Use SVG inside Gutenberg editor.
      if (acf.isGutenberg()) {
        if (props.open) {
          return '<svg width="24px" height="24px" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" class="acf-accordion-icon components-panel__arrow" aria-hidden="true" focusable="false"><path d="M6.5 12.4L12 8l5.5 4.4-.9 1.2L12 10l-4.5 3.6-1-1.2z"></path></svg>';
        } else {
          return '<svg width="24px" height="24px" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" class=" acf-accordion-icon components-panel__arrow" aria-hidden="true" focusable="false"><path d="M17.5 11.6L12 16l-5.5-4.4.9-1.2L12 14l4.5-3.6 1 1.2z"></path></svg>';
        }
      } else {
        if (props.open) {
          return '<i class="acf-accordion-icon dashicons dashicons-arrow-down"></i>';
        } else {
          return '<i class="acf-accordion-icon dashicons dashicons-arrow-right"></i>';
        }
      }
    },
    open: function ($el) {
      var duration = acf.isGutenberg() ? 0 : 300;

      // open
      $el.find('.acf-accordion-content:first').slideDown(duration).css('display', 'block');
      $el.find('.acf-accordion-icon:first').replaceWith(this.iconHtml({
        open: true
      }));
      $el.addClass('-open');

      // action
      acf.doAction('show', $el);

      // close siblings
      if (!$el.attr('multi-expand')) {
        $el.siblings('.acf-accordion.-open').each(function () {
          accordionManager.close($(this));
        });
      }
    },
    close: function ($el) {
      var duration = acf.isGutenberg() ? 0 : 300;

      // close
      $el.find('.acf-accordion-content:first').slideUp(duration);
      $el.find('.acf-accordion-icon:first').replaceWith(this.iconHtml({
        open: false
      }));
      $el.removeClass('-open');

      // action
      acf.doAction('hide', $el);
    },
    onClick: function (e, $el) {
      // prevent Defailt
      e.preventDefault();

      // open close
      this.toggle($el.parent());
    },
    onInvalidField: function (e, $el) {
      // bail early if already focused
      if (this.busy) {
        return;
      }

      // disable functionality for 1sec (allow next validation to work)
      this.busy = true;
      this.setTimeout(function () {
        this.busy = false;
      }, 1000);

      // open accordion
      this.open($el);
    },
    onUnload: function (e) {
      // vars
      var order = [];

      // loop
      $('.acf-accordion').each(function () {
        var open = $(this).hasClass('-open') ? 1 : 0;
        order.push(open);
      });

      // set
      if (order.length) {
        acf.setPreference('this.accordions', order);
      }
    }
  });
})(jQuery);

/***/ }),

/***/ "./src/advanced-custom-fields-pro/assets/src/js/_acf-field-button-group.js":
/*!*********************************************************************************!*\
  !*** ./src/advanced-custom-fields-pro/assets/src/js/_acf-field-button-group.js ***!
  \*********************************************************************************/
/***/ (() => {

(function ($, undefined) {
  var Field = acf.Field.extend({
    type: 'button_group',
    events: {
      'click input[type="radio"]': 'onClick'
    },
    $control: function () {
      return this.$('.acf-button-group');
    },
    $input: function () {
      return this.$('input:checked');
    },
    setValue: function (val) {
      this.$('input[value="' + val + '"]').prop('checked', true).trigger('change');
    },
    onClick: function (e, $el) {
      // vars
      var $label = $el.parent('label');
      var selected = $label.hasClass('selected');

      // remove previous selected
      this.$('.selected').removeClass('selected');

      // add active class
      $label.addClass('selected');

      // allow null
      if (this.get('allow_null') && selected) {
        $label.removeClass('selected');
        $el.prop('checked', false).trigger('change');
      }
    }
  });
  acf.registerFieldType(Field);
})(jQuery);

/***/ }),

/***/ "./src/advanced-custom-fields-pro/assets/src/js/_acf-field-checkbox.js":
/*!*****************************************************************************!*\
  !*** ./src/advanced-custom-fields-pro/assets/src/js/_acf-field-checkbox.js ***!
  \*****************************************************************************/
/***/ (() => {

(function ($, undefined) {
  var Field = acf.Field.extend({
    type: 'checkbox',
    events: {
      'change input': 'onChange',
      'click .acf-add-checkbox': 'onClickAdd',
      'click .acf-checkbox-toggle': 'onClickToggle',
      'click .acf-checkbox-custom': 'onClickCustom'
    },
    $control: function () {
      return this.$('.acf-checkbox-list');
    },
    $toggle: function () {
      return this.$('.acf-checkbox-toggle');
    },
    $input: function () {
      return this.$('input[type="hidden"]');
    },
    $inputs: function () {
      return this.$('input[type="checkbox"]').not('.acf-checkbox-toggle');
    },
    getValue: function () {
      var val = [];
      this.$(':checked').each(function () {
        val.push($(this).val());
      });
      return val.length ? val : false;
    },
    onChange: function (e, $el) {
      // Vars.
      var checked = $el.prop('checked');
      var $label = $el.parent('label');
      var $toggle = this.$toggle();

      // Add or remove "selected" class.
      if (checked) {
        $label.addClass('selected');
      } else {
        $label.removeClass('selected');
      }

      // Update toggle state if all inputs are checked.
      if ($toggle.length) {
        var $inputs = this.$inputs();

        // all checked
        if ($inputs.not(':checked').length == 0) {
          $toggle.prop('checked', true);
        } else {
          $toggle.prop('checked', false);
        }
      }
    },
    onClickAdd: function (e, $el) {
      var html = '<li><input class="acf-checkbox-custom" type="checkbox" checked="checked" /><input type="text" name="' + this.getInputName() + '[]" /></li>';
      $el.parent('li').before(html);
      $el.parent('li').parent().find('input[type="text"]').last().focus();
    },
    onClickToggle: function (e, $el) {
      // Vars.
      var checked = $el.prop('checked');
      var $inputs = this.$('input[type="checkbox"]');
      var $labels = this.$('label');

      // Update "checked" state.
      $inputs.prop('checked', checked);

      // Add or remove "selected" class.
      if (checked) {
        $labels.addClass('selected');
      } else {
        $labels.removeClass('selected');
      }
    },
    onClickCustom: function (e, $el) {
      var checked = $el.prop('checked');
      var $text = $el.next('input[type="text"]');

      // checked
      if (checked) {
        $text.prop('disabled', false);

        // not checked
      } else {
        $text.prop('disabled', true);

        // remove
        if ($text.val() == '') {
          $el.parent('li').remove();
        }
      }
    }
  });
  acf.registerFieldType(Field);
})(jQuery);

/***/ }),

/***/ "./src/advanced-custom-fields-pro/assets/src/js/_acf-field-color-picker.js":
/*!*********************************************************************************!*\
  !*** ./src/advanced-custom-fields-pro/assets/src/js/_acf-field-color-picker.js ***!
  \*********************************************************************************/
/***/ (() => {

(function ($, undefined) {
  var Field = acf.Field.extend({
    type: 'color_picker',
    wait: 'load',
    events: {
      duplicateField: 'onDuplicate'
    },
    $control: function () {
      return this.$('.acf-color-picker');
    },
    $input: function () {
      return this.$('input[type="hidden"]');
    },
    $inputText: function () {
      return this.$('input[type="text"]');
    },
    setValue: function (val) {
      // update input (with change)
      acf.val(this.$input(), val);

      // update iris
      this.$inputText().iris('color', val);
    },
    initialize: function () {
      // vars
      var $input = this.$input();
      var $inputText = this.$inputText();

      // event
      var onChange = function (e) {
        // timeout is required to ensure the $input val is correct
        setTimeout(function () {
          acf.val($input, $inputText.val());
        }, 1);
      };

      // args
      var args = {
        defaultColor: false,
        palettes: true,
        hide: true,
        change: onChange,
        clear: onChange
      };

      // filter
      var args = acf.applyFilters('color_picker_args', args, this);

      // initialize
      $inputText.wpColorPicker(args);
    },
    onDuplicate: function (e, $el, $duplicate) {
      // The wpColorPicker library does not provide a destroy method.
      // Manually reset DOM by replacing elements back to their original state.
      $colorPicker = $duplicate.find('.wp-picker-container');
      $inputText = $duplicate.find('input[type="text"]');
      $colorPicker.replaceWith($inputText);
    }
  });
  acf.registerFieldType(Field);
})(jQuery);

/***/ }),

/***/ "./src/advanced-custom-fields-pro/assets/src/js/_acf-field-date-picker.js":
/*!********************************************************************************!*\
  !*** ./src/advanced-custom-fields-pro/assets/src/js/_acf-field-date-picker.js ***!
  \********************************************************************************/
/***/ (() => {

(function ($, undefined) {
  var Field = acf.Field.extend({
    type: 'date_picker',
    events: {
      'blur input[type="text"]': 'onBlur',
      duplicateField: 'onDuplicate'
    },
    $control: function () {
      return this.$('.acf-date-picker');
    },
    $input: function () {
      return this.$('input[type="hidden"]');
    },
    $inputText: function () {
      return this.$('input[type="text"]');
    },
    initialize: function () {
      // save_format: compatibility with ACF < 5.0.0
      if (this.has('save_format')) {
        return this.initializeCompatibility();
      }

      // vars
      var $input = this.$input();
      var $inputText = this.$inputText();

      // args
      var args = {
        dateFormat: this.get('date_format'),
        altField: $input,
        altFormat: 'yymmdd',
        changeYear: true,
        yearRange: '-100:+100',
        changeMonth: true,
        showButtonPanel: true,
        firstDay: this.get('first_day')
      };

      // filter
      args = acf.applyFilters('date_picker_args', args, this);

      // add date picker
      acf.newDatePicker($inputText, args);

      // action
      acf.doAction('date_picker_init', $inputText, args, this);
    },
    initializeCompatibility: function () {
      // vars
      var $input = this.$input();
      var $inputText = this.$inputText();

      // get and set value from alt field
      $inputText.val($input.val());

      // args
      var args = {
        dateFormat: this.get('date_format'),
        altField: $input,
        altFormat: this.get('save_format'),
        changeYear: true,
        yearRange: '-100:+100',
        changeMonth: true,
        showButtonPanel: true,
        firstDay: this.get('first_day')
      };

      // filter for 3rd party customization
      args = acf.applyFilters('date_picker_args', args, this);

      // backup
      var dateFormat = args.dateFormat;

      // change args.dateFormat
      args.dateFormat = this.get('save_format');

      // add date picker
      acf.newDatePicker($inputText, args);

      // now change the format back to how it should be.
      $inputText.datepicker('option', 'dateFormat', dateFormat);

      // action for 3rd party customization
      acf.doAction('date_picker_init', $inputText, args, this);
    },
    onBlur: function () {
      if (!this.$inputText().val()) {
        acf.val(this.$input(), '');
      }
    },
    onDuplicate: function (e, $el, $duplicate) {
      $duplicate.find('input[type="text"]').removeClass('hasDatepicker').removeAttr('id');
    }
  });
  acf.registerFieldType(Field);

  // manager
  var datePickerManager = new acf.Model({
    priority: 5,
    wait: 'ready',
    initialize: function () {
      // vars
      var locale = acf.get('locale');
      var rtl = acf.get('rtl');
      var l10n = acf.get('datePickerL10n');

      // bail early if no l10n
      if (!l10n) {
        return false;
      }

      // bail early if no datepicker library
      if (typeof $.datepicker === 'undefined') {
        return false;
      }

      // rtl
      l10n.isRTL = rtl;

      // append
      $.datepicker.regional[locale] = l10n;
      $.datepicker.setDefaults(l10n);
    }
  });

  // add
  acf.newDatePicker = function ($input, args) {
    // bail early if no datepicker library
    if (typeof $.datepicker === 'undefined') {
      return false;
    }

    // defaults
    args = args || {};

    // initialize
    $input.datepicker(args);

    // wrap the datepicker (only if it hasn't already been wrapped)
    if ($('body > #ui-datepicker-div').exists()) {
      $('body > #ui-datepicker-div').wrap('<div class="acf-ui-datepicker" />');
    }
  };
})(jQuery);

/***/ }),

/***/ "./src/advanced-custom-fields-pro/assets/src/js/_acf-field-date-time-picker.js":
/*!*************************************************************************************!*\
  !*** ./src/advanced-custom-fields-pro/assets/src/js/_acf-field-date-time-picker.js ***!
  \*************************************************************************************/
/***/ (() => {

(function ($, undefined) {
  var Field = acf.models.DatePickerField.extend({
    type: 'date_time_picker',
    $control: function () {
      return this.$('.acf-date-time-picker');
    },
    initialize: function () {
      // vars
      var $input = this.$input();
      var $inputText = this.$inputText();

      // args
      var args = {
        dateFormat: this.get('date_format'),
        timeFormat: this.get('time_format'),
        altField: $input,
        altFieldTimeOnly: false,
        altFormat: 'yy-mm-dd',
        altTimeFormat: 'HH:mm:ss',
        changeYear: true,
        yearRange: '-100:+100',
        changeMonth: true,
        showButtonPanel: true,
        firstDay: this.get('first_day'),
        controlType: 'select',
        oneLine: true
      };

      // filter
      args = acf.applyFilters('date_time_picker_args', args, this);

      // add date time picker
      acf.newDateTimePicker($inputText, args);

      // action
      acf.doAction('date_time_picker_init', $inputText, args, this);
    }
  });
  acf.registerFieldType(Field);

  // manager
  var dateTimePickerManager = new acf.Model({
    priority: 5,
    wait: 'ready',
    initialize: function () {
      // vars
      var locale = acf.get('locale');
      var rtl = acf.get('rtl');
      var l10n = acf.get('dateTimePickerL10n');

      // bail early if no l10n
      if (!l10n) {
        return false;
      }

      // bail early if no datepicker library
      if (typeof $.timepicker === 'undefined') {
        return false;
      }

      // rtl
      l10n.isRTL = rtl;

      // append
      $.timepicker.regional[locale] = l10n;
      $.timepicker.setDefaults(l10n);
    }
  });

  // add
  acf.newDateTimePicker = function ($input, args) {
    // bail early if no datepicker library
    if (typeof $.timepicker === 'undefined') {
      return false;
    }

    // defaults
    args = args || {};

    // initialize
    $input.datetimepicker(args);

    // wrap the datepicker (only if it hasn't already been wrapped)
    if ($('body > #ui-datepicker-div').exists()) {
      $('body > #ui-datepicker-div').wrap('<div class="acf-ui-datepicker" />');
    }
  };
})(jQuery);

/***/ }),

/***/ "./src/advanced-custom-fields-pro/assets/src/js/_acf-field-file.js":
/*!*************************************************************************!*\
  !*** ./src/advanced-custom-fields-pro/assets/src/js/_acf-field-file.js ***!
  \*************************************************************************/
/***/ (() => {

(function ($, undefined) {
  var Field = acf.models.ImageField.extend({
    type: 'file',
    $control: function () {
      return this.$('.acf-file-uploader');
    },
    $input: function () {
      return this.$('input[type="hidden"]:first');
    },
    validateAttachment: function (attachment) {
      // defaults
      attachment = attachment || {};

      // WP attachment
      if (attachment.id !== undefined) {
        attachment = attachment.attributes;
      }

      // args
      attachment = acf.parseArgs(attachment, {
        url: '',
        alt: '',
        title: '',
        filename: '',
        filesizeHumanReadable: '',
        icon: '/wp-includes/images/media/default.png'
      });

      // return
      return attachment;
    },
    render: function (attachment) {
      // vars
      attachment = this.validateAttachment(attachment);

      // update image
      this.$('img').attr({
        src: attachment.icon,
        alt: attachment.alt,
        title: attachment.title
      });

      // update elements
      this.$('[data-name="title"]').text(attachment.title);
      this.$('[data-name="filename"]').text(attachment.filename).attr('href', attachment.url);
      this.$('[data-name="filesize"]').text(attachment.filesizeHumanReadable);

      // vars
      var val = attachment.id || '';

      // update val
      acf.val(this.$input(), val);

      // update class
      if (val) {
        this.$control().addClass('has-value');
      } else {
        this.$control().removeClass('has-value');
      }
    },
    selectAttachment: function () {
      // vars
      var parent = this.parent();
      var multiple = parent && parent.get('type') === 'repeater';

      // new frame
      var frame = acf.newMediaPopup({
        mode: 'select',
        title: acf.__('Select File'),
        field: this.get('key'),
        multiple: multiple,
        library: this.get('library'),
        allowedTypes: this.get('mime_types'),
        select: $.proxy(function (attachment, i) {
          if (i > 0) {
            this.append(attachment, parent);
          } else {
            this.render(attachment);
          }
        }, this)
      });
    },
    editAttachment: function () {
      // vars
      var val = this.val();

      // bail early if no val
      if (!val) {
        return false;
      }

      // popup
      var frame = acf.newMediaPopup({
        mode: 'edit',
        title: acf.__('Edit File'),
        button: acf.__('Update File'),
        attachment: val,
        field: this.get('key'),
        select: $.proxy(function (attachment, i) {
          this.render(attachment);
        }, this)
      });
    }
  });
  acf.registerFieldType(Field);
})(jQuery);

/***/ }),

/***/ "./src/advanced-custom-fields-pro/assets/src/js/_acf-field-google-map.js":
/*!*******************************************************************************!*\
  !*** ./src/advanced-custom-fields-pro/assets/src/js/_acf-field-google-map.js ***!
  \*******************************************************************************/
/***/ (() => {

(function ($, undefined) {
  var Field = acf.Field.extend({
    type: 'google_map',
    map: false,
    wait: 'load',
    events: {
      'click a[data-name="clear"]': 'onClickClear',
      'click a[data-name="locate"]': 'onClickLocate',
      'click a[data-name="search"]': 'onClickSearch',
      'keydown .search': 'onKeydownSearch',
      'keyup .search': 'onKeyupSearch',
      'focus .search': 'onFocusSearch',
      'blur .search': 'onBlurSearch',
      showField: 'onShow'
    },
    $control: function () {
      return this.$('.acf-google-map');
    },
    $search: function () {
      return this.$('.search');
    },
    $canvas: function () {
      return this.$('.canvas');
    },
    setState: function (state) {
      // Remove previous state classes.
      this.$control().removeClass('-value -loading -searching');

      // Determine auto state based of current value.
      if (state === 'default') {
        state = this.val() ? 'value' : '';
      }

      // Update state class.
      if (state) {
        this.$control().addClass('-' + state);
      }
    },
    getValue: function () {
      var val = this.$input().val();
      if (val) {
        return JSON.parse(val);
      } else {
        return false;
      }
    },
    setValue: function (val, silent) {
      // Convert input value.
      var valAttr = '';
      if (val) {
        valAttr = JSON.stringify(val);
      }

      // Update input (with change).
      acf.val(this.$input(), valAttr);

      // Bail early if silent update.
      if (silent) {
        return;
      }

      // Render.
      this.renderVal(val);

      /**
       * Fires immediately after the value has changed.
       *
       * @date	12/02/2014
       * @since	5.0.0
       *
       * @param	object|string val The new value.
       * @param	object map The Google Map isntance.
       * @param	object field The field instance.
       */
      acf.doAction('google_map_change', val, this.map, this);
    },
    renderVal: function (val) {
      // Value.
      if (val) {
        this.setState('value');
        this.$search().val(val.address);
        this.setPosition(val.lat, val.lng);

        // No value.
      } else {
        this.setState('');
        this.$search().val('');
        this.map.marker.setVisible(false);
      }
    },
    newLatLng: function (lat, lng) {
      return new google.maps.LatLng(parseFloat(lat), parseFloat(lng));
    },
    setPosition: function (lat, lng) {
      // Update marker position.
      this.map.marker.setPosition({
        lat: parseFloat(lat),
        lng: parseFloat(lng)
      });

      // Show marker.
      this.map.marker.setVisible(true);

      // Center map.
      this.center();
    },
    center: function () {
      // Find marker position.
      var position = this.map.marker.getPosition();
      if (position) {
        var lat = position.lat();
        var lng = position.lng();

        // Or find default settings.
      } else {
        var lat = this.get('lat');
        var lng = this.get('lng');
      }

      // Center map.
      this.map.setCenter({
        lat: parseFloat(lat),
        lng: parseFloat(lng)
      });
    },
    initialize: function () {
      // Ensure Google API is loaded and then initialize map.
      withAPI(this.initializeMap.bind(this));
    },
    initializeMap: function () {
      // Get value ignoring conditional logic status.
      var val = this.getValue();

      // Construct default args.
      var args = acf.parseArgs(val, {
        zoom: this.get('zoom'),
        lat: this.get('lat'),
        lng: this.get('lng')
      });

      // Create Map.
      var mapArgs = {
        scrollwheel: false,
        zoom: parseInt(args.zoom),
        center: {
          lat: parseFloat(args.lat),
          lng: parseFloat(args.lng)
        },
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        marker: {
          draggable: true,
          raiseOnDrag: true
        },
        autocomplete: {}
      };
      mapArgs = acf.applyFilters('google_map_args', mapArgs, this);
      var map = new google.maps.Map(this.$canvas()[0], mapArgs);

      // Create Marker.
      var markerArgs = acf.parseArgs(mapArgs.marker, {
        draggable: true,
        raiseOnDrag: true,
        map: map
      });
      markerArgs = acf.applyFilters('google_map_marker_args', markerArgs, this);
      var marker = new google.maps.Marker(markerArgs);

      // Maybe Create Autocomplete.
      var autocomplete = false;
      if (acf.isset(google, 'maps', 'places', 'Autocomplete')) {
        var autocompleteArgs = mapArgs.autocomplete || {};
        autocompleteArgs = acf.applyFilters('google_map_autocomplete_args', autocompleteArgs, this);
        autocomplete = new google.maps.places.Autocomplete(this.$search()[0], autocompleteArgs);
        autocomplete.bindTo('bounds', map);
      }

      // Add map events.
      this.addMapEvents(this, map, marker, autocomplete);

      // Append references.
      map.acf = this;
      map.marker = marker;
      map.autocomplete = autocomplete;
      this.map = map;

      // Set position.
      if (val) {
        this.setPosition(val.lat, val.lng);
      }

      /**
       * Fires immediately after the Google Map has been initialized.
       *
       * @date	12/02/2014
       * @since	5.0.0
       *
       * @param	object map The Google Map isntance.
       * @param	object marker The Google Map marker isntance.
       * @param	object field The field instance.
       */
      acf.doAction('google_map_init', map, marker, this);
    },
    addMapEvents: function (field, map, marker, autocomplete) {
      // Click map.
      google.maps.event.addListener(map, 'click', function (e) {
        var lat = e.latLng.lat();
        var lng = e.latLng.lng();
        field.searchPosition(lat, lng);
      });

      // Drag marker.
      google.maps.event.addListener(marker, 'dragend', function () {
        var lat = this.getPosition().lat();
        var lng = this.getPosition().lng();
        field.searchPosition(lat, lng);
      });

      // Autocomplete search.
      if (autocomplete) {
        google.maps.event.addListener(autocomplete, 'place_changed', function () {
          var place = this.getPlace();
          field.searchPlace(place);
        });
      }

      // Detect zoom change.
      google.maps.event.addListener(map, 'zoom_changed', function () {
        var val = field.val();
        if (val) {
          val.zoom = map.getZoom();
          field.setValue(val, true);
        }
      });
    },
    searchPosition: function (lat, lng) {
      //console.log('searchPosition', lat, lng );

      // Start Loading.
      this.setState('loading');

      // Query Geocoder.
      var latLng = {
        lat: lat,
        lng: lng
      };
      geocoder.geocode({
        location: latLng
      }, function (results, status) {
        //console.log('searchPosition', arguments );

        // End Loading.
        this.setState('');

        // Status failure.
        if (status !== 'OK') {
          this.showNotice({
            text: acf.__('Location not found: %s').replace('%s', status),
            type: 'warning'
          });

          // Success.
        } else {
          var val = this.parseResult(results[0]);

          // Override lat/lng to match user defined marker location.
          // Avoids issue where marker "snaps" to nearest result.
          val.lat = lat;
          val.lng = lng;
          this.val(val);
        }
      }.bind(this));
    },
    searchPlace: function (place) {
      //console.log('searchPlace', place );

      // Bail early if no place.
      if (!place) {
        return;
      }

      // Selecting from the autocomplete dropdown will return a rich PlaceResult object.
      // Be sure to over-write the "formatted_address" value with the one displayed to the user for best UX.
      if (place.geometry) {
        place.formatted_address = this.$search().val();
        var val = this.parseResult(place);
        this.val(val);

        // Searching a custom address will return an empty PlaceResult object.
      } else if (place.name) {
        this.searchAddress(place.name);
      }
    },
    searchAddress: function (address) {
      //console.log('searchAddress', address );

      // Bail early if no address.
      if (!address) {
        return;
      }

      // Allow "lat,lng" search.
      var latLng = address.split(',');
      if (latLng.length == 2) {
        var lat = parseFloat(latLng[0]);
        var lng = parseFloat(latLng[1]);
        if (lat && lng) {
          return this.searchPosition(lat, lng);
        }
      }

      // Start Loading.
      this.setState('loading');

      // Query Geocoder.
      geocoder.geocode({
        address: address
      }, function (results, status) {
        //console.log('searchPosition', arguments );

        // End Loading.
        this.setState('');

        // Status failure.
        if (status !== 'OK') {
          this.showNotice({
            text: acf.__('Location not found: %s').replace('%s', status),
            type: 'warning'
          });

          // Success.
        } else {
          var val = this.parseResult(results[0]);

          // Override address data with parameter allowing custom address to be defined in search.
          val.address = address;

          // Update value.
          this.val(val);
        }
      }.bind(this));
    },
    searchLocation: function () {
      //console.log('searchLocation' );

      // Check HTML5 geolocation.
      if (!navigator.geolocation) {
        return alert(acf.__('Sorry, this browser does not support geolocation'));
      }

      // Start Loading.
      this.setState('loading');

      // Query Geolocation.
      navigator.geolocation.getCurrentPosition(
      // Success.
      function (results) {
        // End Loading.
        this.setState('');

        // Search position.
        var lat = results.coords.latitude;
        var lng = results.coords.longitude;
        this.searchPosition(lat, lng);
      }.bind(this),
      // Failure.
      function (error) {
        this.setState('');
      }.bind(this));
    },
    /**
     * parseResult
     *
     * Returns location data for the given GeocoderResult object.
     *
     * @date	15/10/19
     * @since	5.8.6
     *
     * @param	object obj A GeocoderResult object.
     * @return	object
     */
    parseResult: function (obj) {
      // Construct basic data.
      var result = {
        address: obj.formatted_address,
        lat: obj.geometry.location.lat(),
        lng: obj.geometry.location.lng()
      };

      // Add zoom level.
      result.zoom = this.map.getZoom();

      // Add place ID.
      if (obj.place_id) {
        result.place_id = obj.place_id;
      }

      // Add place name.
      if (obj.name) {
        result.name = obj.name;
      }

      // Create search map for address component data.
      var map = {
        street_number: ['street_number'],
        street_name: ['street_address', 'route'],
        city: ['locality', 'postal_town'],
        state: ['administrative_area_level_1', 'administrative_area_level_2', 'administrative_area_level_3', 'administrative_area_level_4', 'administrative_area_level_5'],
        post_code: ['postal_code'],
        country: ['country']
      };

      // Loop over map.
      for (var k in map) {
        var keywords = map[k];

        // Loop over address components.
        for (var i = 0; i < obj.address_components.length; i++) {
          var component = obj.address_components[i];
          var component_type = component.types[0];

          // Look for matching component type.
          if (keywords.indexOf(component_type) !== -1) {
            // Append to result.
            result[k] = component.long_name;

            // Append short version.
            if (component.long_name !== component.short_name) {
              result[k + '_short'] = component.short_name;
            }
          }
        }
      }

      /**
       * Filters the parsed result.
       *
       * @date	18/10/19
       * @since	5.8.6
       *
       * @param	object result The parsed result value.
       * @param	object obj The GeocoderResult object.
       */
      return acf.applyFilters('google_map_result', result, obj, this.map, this);
    },
    onClickClear: function () {
      this.val(false);
    },
    onClickLocate: function () {
      this.searchLocation();
    },
    onClickSearch: function () {
      this.searchAddress(this.$search().val());
    },
    onFocusSearch: function (e, $el) {
      this.setState('searching');
    },
    onBlurSearch: function (e, $el) {
      // Get saved address value.
      var val = this.val();
      var address = val ? val.address : '';

      // Remove 'is-searching' if value has not changed.
      if ($el.val() === address) {
        this.setState('default');
      }
    },
    onKeyupSearch: function (e, $el) {
      // Clear empty value.
      if (!$el.val()) {
        this.val(false);
      }
    },
    // Prevent form from submitting.
    onKeydownSearch: function (e, $el) {
      if (e.which == 13) {
        e.preventDefault();
        $el.blur();
      }
    },
    // Center map once made visible.
    onShow: function () {
      if (this.map) {
        this.setTimeout(this.center);
      }
    }
  });
  acf.registerFieldType(Field);

  // Vars.
  var loading = false;
  var geocoder = false;

  /**
   * withAPI
   *
   * Loads the Google Maps API library and troggers callback.
   *
   * @date	28/3/19
   * @since	5.7.14
   *
   * @param	function callback The callback to excecute.
   * @return	void
   */

  function withAPI(callback) {
    // Check if geocoder exists.
    if (geocoder) {
      return callback();
    }

    // Check if geocoder API exists.
    if (acf.isset(window, 'google', 'maps', 'Geocoder')) {
      geocoder = new google.maps.Geocoder();
      return callback();
    }

    // Geocoder will need to be loaded. Hook callback to action.
    acf.addAction('google_map_api_loaded', callback);

    // Bail early if already loading API.
    if (loading) {
      return;
    }

    // load api
    var url = acf.get('google_map_api');
    if (url) {
      // Set loading status.
      loading = true;

      // Load API
      $.ajax({
        url: url,
        dataType: 'script',
        cache: true,
        success: function () {
          geocoder = new google.maps.Geocoder();
          acf.doAction('google_map_api_loaded');
        }
      });
    }
  }
})(jQuery);

/***/ }),

/***/ "./src/advanced-custom-fields-pro/assets/src/js/_acf-field-image.js":
/*!**************************************************************************!*\
  !*** ./src/advanced-custom-fields-pro/assets/src/js/_acf-field-image.js ***!
  \**************************************************************************/
/***/ (() => {

(function ($, undefined) {
  var Field = acf.Field.extend({
    type: 'image',
    $control: function () {
      return this.$('.acf-image-uploader');
    },
    $input: function () {
      return this.$('input[type="hidden"]:first');
    },
    events: {
      'click a[data-name="add"]': 'onClickAdd',
      'click a[data-name="edit"]': 'onClickEdit',
      'click a[data-name="remove"]': 'onClickRemove',
      'change input[type="file"]': 'onChange'
    },
    initialize: function () {
      // add attribute to form
      if (this.get('uploader') === 'basic') {
        this.$el.closest('form').attr('enctype', 'multipart/form-data');
      }
    },
    validateAttachment: function (attachment) {
      // Use WP attachment attributes when available.
      if (attachment && attachment.attributes) {
        attachment = attachment.attributes;
      }

      // Apply defaults.
      attachment = acf.parseArgs(attachment, {
        id: 0,
        url: '',
        alt: '',
        title: '',
        caption: '',
        description: '',
        width: 0,
        height: 0
      });

      // Override with "preview size".
      var size = acf.isget(attachment, 'sizes', this.get('preview_size'));
      if (size) {
        attachment.url = size.url;
        attachment.width = size.width;
        attachment.height = size.height;
      }

      // Return.
      return attachment;
    },
    render: function (attachment) {
      attachment = this.validateAttachment(attachment);

      // Update DOM.
      this.$('img').attr({
        src: attachment.url,
        alt: attachment.alt
      });
      if (attachment.id) {
        this.val(attachment.id);
        this.$control().addClass('has-value');
      } else {
        this.val('');
        this.$control().removeClass('has-value');
      }
    },
    // create a new repeater row and render value
    append: function (attachment, parent) {
      // create function to find next available field within parent
      var getNext = function (field, parent) {
        // find existing file fields within parent
        var fields = acf.getFields({
          key: field.get('key'),
          parent: parent.$el
        });

        // find the first field with no value
        for (var i = 0; i < fields.length; i++) {
          if (!fields[i].val()) {
            return fields[i];
          }
        }

        // return
        return false;
      };

      // find existing file fields within parent
      var field = getNext(this, parent);

      // add new row if no available field
      if (!field) {
        parent.$('.acf-button:last').trigger('click');
        field = getNext(this, parent);
      }

      // render
      if (field) {
        field.render(attachment);
      }
    },
    selectAttachment: function () {
      // vars
      var parent = this.parent();
      var multiple = parent && parent.get('type') === 'repeater';

      // new frame
      var frame = acf.newMediaPopup({
        mode: 'select',
        type: 'image',
        title: acf.__('Select Image'),
        field: this.get('key'),
        multiple: multiple,
        library: this.get('library'),
        allowedTypes: this.get('mime_types'),
        select: $.proxy(function (attachment, i) {
          if (i > 0) {
            this.append(attachment, parent);
          } else {
            this.render(attachment);
          }
        }, this)
      });
    },
    editAttachment: function () {
      // vars
      var val = this.val();

      // bail early if no val
      if (!val) return;

      // popup
      var frame = acf.newMediaPopup({
        mode: 'edit',
        title: acf.__('Edit Image'),
        button: acf.__('Update Image'),
        attachment: val,
        field: this.get('key'),
        select: $.proxy(function (attachment, i) {
          this.render(attachment);
        }, this)
      });
    },
    removeAttachment: function () {
      this.render(false);
    },
    onClickAdd: function (e, $el) {
      this.selectAttachment();
    },
    onClickEdit: function (e, $el) {
      this.editAttachment();
    },
    onClickRemove: function (e, $el) {
      this.removeAttachment();
    },
    onChange: function (e, $el) {
      var $hiddenInput = this.$input();
      if (!$el.val()) {
        $hiddenInput.val('');
      }
      acf.getFileInputData($el, function (data) {
        $hiddenInput.val($.param(data));
      });
    }
  });
  acf.registerFieldType(Field);
})(jQuery);

/***/ }),

/***/ "./src/advanced-custom-fields-pro/assets/src/js/_acf-field-link.js":
/*!*************************************************************************!*\
  !*** ./src/advanced-custom-fields-pro/assets/src/js/_acf-field-link.js ***!
  \*************************************************************************/
/***/ (() => {

(function ($, undefined) {
  var Field = acf.Field.extend({
    type: 'link',
    events: {
      'click a[data-name="add"]': 'onClickEdit',
      'click a[data-name="edit"]': 'onClickEdit',
      'click a[data-name="remove"]': 'onClickRemove',
      'change .link-node': 'onChange'
    },
    $control: function () {
      return this.$('.acf-link');
    },
    $node: function () {
      return this.$('.link-node');
    },
    getValue: function () {
      // vars
      var $node = this.$node();

      // return false if empty
      if (!$node.attr('href')) {
        return false;
      }

      // return
      return {
        title: $node.html(),
        url: $node.attr('href'),
        target: $node.attr('target')
      };
    },
    setValue: function (val) {
      // default
      val = acf.parseArgs(val, {
        title: '',
        url: '',
        target: ''
      });

      // vars
      var $div = this.$control();
      var $node = this.$node();

      // remove class
      $div.removeClass('-value -external');

      // add class
      if (val.url) $div.addClass('-value');
      if (val.target === '_blank') $div.addClass('-external');

      // update text
      this.$('.link-title').html(val.title);
      this.$('.link-url').attr('href', val.url).html(val.url);

      // update node
      $node.html(val.title);
      $node.attr('href', val.url);
      $node.attr('target', val.target);

      // update inputs
      this.$('.input-title').val(val.title);
      this.$('.input-target').val(val.target);
      this.$('.input-url').val(val.url).trigger('change');
    },
    onClickEdit: function (e, $el) {
      acf.wpLink.open(this.$node());
    },
    onClickRemove: function (e, $el) {
      this.setValue(false);
    },
    onChange: function (e, $el) {
      // get the changed value
      var val = this.getValue();

      // update inputs
      this.setValue(val);
    }
  });
  acf.registerFieldType(Field);

  // manager
  acf.wpLink = new acf.Model({
    getNodeValue: function () {
      var $node = this.get('node');
      return {
        title: acf.decode($node.html()),
        url: $node.attr('href'),
        target: $node.attr('target')
      };
    },
    setNodeValue: function (val) {
      var $node = this.get('node');
      $node.text(val.title);
      $node.attr('href', val.url);
      $node.attr('target', val.target);
      $node.trigger('change');
    },
    getInputValue: function () {
      return {
        title: $('#wp-link-text').val(),
        url: $('#wp-link-url').val(),
        target: $('#wp-link-target').prop('checked') ? '_blank' : ''
      };
    },
    setInputValue: function (val) {
      $('#wp-link-text').val(val.title);
      $('#wp-link-url').val(val.url);
      $('#wp-link-target').prop('checked', val.target === '_blank');
    },
    open: function ($node) {
      // add events
      this.on('wplink-open', 'onOpen');
      this.on('wplink-close', 'onClose');

      // set node
      this.set('node', $node);

      // create textarea
      var $textarea = $('<textarea id="acf-link-textarea" style="display:none;"></textarea>');
      $('body').append($textarea);

      // vars
      var val = this.getNodeValue();

      // open popup
      wpLink.open('acf-link-textarea', val.url, val.title, null);
    },
    onOpen: function () {
      // always show title (WP will hide title if empty)
      $('#wp-link-wrap').addClass('has-text-field');

      // set inputs
      var val = this.getNodeValue();
      this.setInputValue(val);

      // Update button text.
      if (val.url && wpLinkL10n) {
        $('#wp-link-submit').val(wpLinkL10n.update);
      }
    },
    close: function () {
      wpLink.close();
    },
    onClose: function () {
      // Bail early if no node.
      // Needed due to WP triggering this event twice.
      if (!this.has('node')) {
        return false;
      }

      // Determine context.
      var $submit = $('#wp-link-submit');
      var isSubmit = $submit.is(':hover') || $submit.is(':focus');

      // Set value
      if (isSubmit) {
        var val = this.getInputValue();
        this.setNodeValue(val);
      }

      // Cleanup.
      this.off('wplink-open');
      this.off('wplink-close');
      $('#acf-link-textarea').remove();
      this.set('node', null);
    }
  });
})(jQuery);

/***/ }),

/***/ "./src/advanced-custom-fields-pro/assets/src/js/_acf-field-oembed.js":
/*!***************************************************************************!*\
  !*** ./src/advanced-custom-fields-pro/assets/src/js/_acf-field-oembed.js ***!
  \***************************************************************************/
/***/ (() => {

(function ($, undefined) {
  var Field = acf.Field.extend({
    type: 'oembed',
    events: {
      'click [data-name="clear-button"]': 'onClickClear',
      'keypress .input-search': 'onKeypressSearch',
      'keyup .input-search': 'onKeyupSearch',
      'change .input-search': 'onChangeSearch'
    },
    $control: function () {
      return this.$('.acf-oembed');
    },
    $input: function () {
      return this.$('.input-value');
    },
    $search: function () {
      return this.$('.input-search');
    },
    getValue: function () {
      return this.$input().val();
    },
    getSearchVal: function () {
      return this.$search().val();
    },
    setValue: function (val) {
      // class
      if (val) {
        this.$control().addClass('has-value');
      } else {
        this.$control().removeClass('has-value');
      }
      acf.val(this.$input(), val);
    },
    showLoading: function (show) {
      acf.showLoading(this.$('.canvas'));
    },
    hideLoading: function () {
      acf.hideLoading(this.$('.canvas'));
    },
    maybeSearch: function () {
      // vars
      var prevUrl = this.val();
      var url = this.getSearchVal();

      // no value
      if (!url) {
        return this.clear();
      }

      // fix missing 'http://' - causes the oembed code to error and fail
      if (url.substr(0, 4) != 'http') {
        url = 'http://' + url;
      }

      // bail early if no change
      if (url === prevUrl) return;

      // clear existing timeout
      var timeout = this.get('timeout');
      if (timeout) {
        clearTimeout(timeout);
      }

      // set new timeout
      var callback = $.proxy(this.search, this, url);
      this.set('timeout', setTimeout(callback, 300));
    },
    search: function (url) {
      // ajax
      var ajaxData = {
        action: 'acf/fields/oembed/search',
        s: url,
        field_key: this.get('key')
      };

      // clear existing timeout
      var xhr = this.get('xhr');
      if (xhr) {
        xhr.abort();
      }

      // loading
      this.showLoading();

      // query
      var xhr = $.ajax({
        url: acf.get('ajaxurl'),
        data: acf.prepareForAjax(ajaxData),
        type: 'post',
        dataType: 'json',
        context: this,
        success: function (json) {
          // error
          if (!json || !json.html) {
            json = {
              url: false,
              html: ''
            };
          }

          // update vars
          this.val(json.url);
          this.$('.canvas-media').html(json.html);
        },
        complete: function () {
          this.hideLoading();
        }
      });
      this.set('xhr', xhr);
    },
    clear: function () {
      this.val('');
      this.$search().val('');
      this.$('.canvas-media').html('');
    },
    onClickClear: function (e, $el) {
      this.clear();
    },
    onKeypressSearch: function (e, $el) {
      if (e.which == 13) {
        e.preventDefault();
        this.maybeSearch();
      }
    },
    onKeyupSearch: function (e, $el) {
      if ($el.val()) {
        this.maybeSearch();
      }
    },
    onChangeSearch: function (e, $el) {
      this.maybeSearch();
    }
  });
  acf.registerFieldType(Field);
})(jQuery);

/***/ }),

/***/ "./src/advanced-custom-fields-pro/assets/src/js/_acf-field-page-link.js":
/*!******************************************************************************!*\
  !*** ./src/advanced-custom-fields-pro/assets/src/js/_acf-field-page-link.js ***!
  \******************************************************************************/
/***/ (() => {

(function ($, undefined) {
  var Field = acf.models.SelectField.extend({
    type: 'page_link'
  });
  acf.registerFieldType(Field);
})(jQuery);

/***/ }),

/***/ "./src/advanced-custom-fields-pro/assets/src/js/_acf-field-post-object.js":
/*!********************************************************************************!*\
  !*** ./src/advanced-custom-fields-pro/assets/src/js/_acf-field-post-object.js ***!
  \********************************************************************************/
/***/ (() => {

(function ($, undefined) {
  var Field = acf.models.SelectField.extend({
    type: 'post_object'
  });
  acf.registerFieldType(Field);
})(jQuery);

/***/ }),

/***/ "./src/advanced-custom-fields-pro/assets/src/js/_acf-field-radio.js":
/*!**************************************************************************!*\
  !*** ./src/advanced-custom-fields-pro/assets/src/js/_acf-field-radio.js ***!
  \**************************************************************************/
/***/ (() => {

(function ($, undefined) {
  var Field = acf.Field.extend({
    type: 'radio',
    events: {
      'click input[type="radio"]': 'onClick'
    },
    $control: function () {
      return this.$('.acf-radio-list');
    },
    $input: function () {
      return this.$('input:checked');
    },
    $inputText: function () {
      return this.$('input[type="text"]');
    },
    getValue: function () {
      var val = this.$input().val();
      if (val === 'other' && this.get('other_choice')) {
        val = this.$inputText().val();
      }
      return val;
    },
    onClick: function (e, $el) {
      // vars
      var $label = $el.parent('label');
      var selected = $label.hasClass('selected');
      var val = $el.val();

      // remove previous selected
      this.$('.selected').removeClass('selected');

      // add active class
      $label.addClass('selected');

      // allow null
      if (this.get('allow_null') && selected) {
        $label.removeClass('selected');
        $el.prop('checked', false).trigger('change');
        val = false;
      }

      // other
      if (this.get('other_choice')) {
        // enable
        if (val === 'other') {
          this.$inputText().prop('disabled', false);

          // disable
        } else {
          this.$inputText().prop('disabled', true);
        }
      }
    }
  });
  acf.registerFieldType(Field);
})(jQuery);

/***/ }),

/***/ "./src/advanced-custom-fields-pro/assets/src/js/_acf-field-range.js":
/*!**************************************************************************!*\
  !*** ./src/advanced-custom-fields-pro/assets/src/js/_acf-field-range.js ***!
  \**************************************************************************/
/***/ (() => {

(function ($, undefined) {
  var Field = acf.Field.extend({
    type: 'range',
    events: {
      'input input[type="range"]': 'onChange',
      'change input': 'onChange'
    },
    $input: function () {
      return this.$('input[type="range"]');
    },
    $inputAlt: function () {
      return this.$('input[type="number"]');
    },
    setValue: function (val) {
      this.busy = true;

      // Update range input (with change).
      acf.val(this.$input(), val);

      // Update alt input (without change).
      // Read in input value to inherit min/max validation.
      acf.val(this.$inputAlt(), this.$input().val(), true);
      this.busy = false;
    },
    onChange: function (e, $el) {
      if (!this.busy) {
        this.setValue($el.val());
      }
    }
  });
  acf.registerFieldType(Field);
})(jQuery);

/***/ }),

/***/ "./src/advanced-custom-fields-pro/assets/src/js/_acf-field-relationship.js":
/*!*********************************************************************************!*\
  !*** ./src/advanced-custom-fields-pro/assets/src/js/_acf-field-relationship.js ***!
  \*********************************************************************************/
/***/ (() => {

(function ($, undefined) {
  var Field = acf.Field.extend({
    type: 'relationship',
    events: {
      'keypress [data-filter]': 'onKeypressFilter',
      'change [data-filter]': 'onChangeFilter',
      'keyup [data-filter]': 'onChangeFilter',
      'click .choices-list .acf-rel-item': 'onClickAdd',
      'keypress .choices-list .acf-rel-item': 'onKeypressFilter',
      'keypress .values-list .acf-rel-item': 'onKeypressFilter',
      'click [data-name="remove_item"]': 'onClickRemove',
      'touchstart .values-list .acf-rel-item': 'onTouchStartValues'
    },
    $control: function () {
      return this.$('.acf-relationship');
    },
    $list: function (list) {
      return this.$('.' + list + '-list');
    },
    $listItems: function (list) {
      return this.$list(list).find('.acf-rel-item');
    },
    $listItem: function (list, id) {
      return this.$list(list).find('.acf-rel-item[data-id="' + id + '"]');
    },
    getValue: function () {
      var val = [];
      this.$listItems('values').each(function () {
        val.push($(this).data('id'));
      });
      return val.length ? val : false;
    },
    newChoice: function (props) {
      return ['<li>', '<span tabindex="0" data-id="' + props.id + '" class="acf-rel-item">' + props.text + '</span>', '</li>'].join('');
    },
    newValue: function (props) {
      return ['<li>', '<input type="hidden" name="' + this.getInputName() + '[]" value="' + props.id + '" />', '<span tabindex="0" data-id="' + props.id + '" class="acf-rel-item acf-rel-item-remove">' + props.text, '<a href="#" class="acf-icon -minus small dark" data-name="remove_item"></a>', '</span>', '</li>'].join('');
    },
    initialize: function () {
      // Delay initialization until "interacted with" or "in view".
      var delayed = this.proxy(acf.once(function () {
        // Add sortable.
        this.$list('values').sortable({
          items: 'li',
          forceHelperSize: true,
          forcePlaceholderSize: true,
          scroll: true,
          update: this.proxy(function () {
            this.$input().trigger('change');
          })
        });

        // Avoid browser remembering old scroll position and add event.
        this.$list('choices').scrollTop(0).on('scroll', this.proxy(this.onScrollChoices));

        // Fetch choices.
        this.fetch();
      }));

      // Bind "interacted with".
      this.$el.one('mouseover', delayed);
      this.$el.one('focus', 'input', delayed);

      // Bind "in view".
      acf.onceInView(this.$el, delayed);
    },
    onScrollChoices: function (e) {
      // bail early if no more results
      if (this.get('loading') || !this.get('more')) {
        return;
      }

      // Scrolled to bottom
      var $list = this.$list('choices');
      var scrollTop = Math.ceil($list.scrollTop());
      var scrollHeight = Math.ceil($list[0].scrollHeight);
      var innerHeight = Math.ceil($list.innerHeight());
      var paged = this.get('paged') || 1;
      if (scrollTop + innerHeight >= scrollHeight) {
        // update paged
        this.set('paged', paged + 1);

        // fetch
        this.fetch();
      }
    },
    onKeypressFilter: function (e, $el) {
      // Receive enter key when selecting relationship items.
      if ($el.hasClass('acf-rel-item-add') && e.which == 13) {
        this.onClickAdd(e, $el);
      }
      // Receive enter key when removing relationship items.
      if ($el.hasClass('acf-rel-item-remove') && e.which == 13) {
        this.onClickRemove(e, $el);
      }
      // don't submit form
      if (e.which == 13) {
        e.preventDefault();
      }
    },
    onChangeFilter: function (e, $el) {
      // vars
      var val = $el.val();
      var filter = $el.data('filter');

      // Bail early if filter has not changed
      if (this.get(filter) === val) {
        return;
      }

      // update attr
      this.set(filter, val);

      // reset paged
      this.set('paged', 1);

      // fetch
      if ($el.is('select')) {
        this.fetch();

        // search must go through timeout
      } else {
        this.maybeFetch();
      }
    },
    onClickAdd: function (e, $el) {
      // vars
      var val = this.val();
      var max = parseInt(this.get('max'));

      // can be added?
      if ($el.hasClass('disabled')) {
        return false;
      }

      // validate
      if (max > 0 && val && val.length >= max) {
        // add notice
        this.showNotice({
          text: acf.__('Maximum values reached ( {max} values )').replace('{max}', max),
          type: 'warning'
        });
        return false;
      }

      // disable
      $el.addClass('disabled');

      // add
      var html = this.newValue({
        id: $el.data('id'),
        text: $el.html()
      });
      this.$list('values').append(html);

      // trigger change
      this.$input().trigger('change');
    },
    onClickRemove: function (e, $el) {
      // Prevent default here because generic handler wont be triggered.
      e.preventDefault();
      let $span;
      // Behavior if triggered from tabbed event.
      if ($el.hasClass('acf-rel-item-remove')) {
        $span = $el;
      } else {
        // Behavior if triggered through click event.
        $span = $el.parent();
      }

      // vars
      const $li = $span.parent();
      const id = $span.data('id');

      // remove value
      $li.remove();

      // show choice
      this.$listItem('choices', id).removeClass('disabled');

      // trigger change
      this.$input().trigger('change');
    },
    onTouchStartValues: function (e, $el) {
      $(this.$listItems('values')).removeClass('relationship-hover');
      $el.addClass('relationship-hover');
    },
    maybeFetch: function () {
      // vars
      var timeout = this.get('timeout');

      // abort timeout
      if (timeout) {
        clearTimeout(timeout);
      }

      // fetch
      timeout = this.setTimeout(this.fetch, 300);
      this.set('timeout', timeout);
    },
    getAjaxData: function () {
      // load data based on element attributes
      var ajaxData = this.$control().data();
      for (var name in ajaxData) {
        ajaxData[name] = this.get(name);
      }

      // extra
      ajaxData.action = 'acf/fields/relationship/query';
      ajaxData.field_key = this.get('key');

      // Filter.
      ajaxData = acf.applyFilters('relationship_ajax_data', ajaxData, this);

      // return
      return ajaxData;
    },
    fetch: function () {
      // abort XHR if this field is already loading AJAX data
      var xhr = this.get('xhr');
      if (xhr) {
        xhr.abort();
      }

      // add to this.o
      var ajaxData = this.getAjaxData();

      // clear html if is new query
      var $choiceslist = this.$list('choices');
      if (ajaxData.paged == 1) {
        $choiceslist.html('');
      }

      // loading
      var $loading = $('<li><i class="acf-loading"></i> ' + acf.__('Loading') + '</li>');
      $choiceslist.append($loading);
      this.set('loading', true);

      // callback
      var onComplete = function () {
        this.set('loading', false);
        $loading.remove();
      };
      var onSuccess = function (json) {
        // no results
        if (!json || !json.results || !json.results.length) {
          // prevent pagination
          this.set('more', false);

          // add message
          if (this.get('paged') == 1) {
            this.$list('choices').append('<li>' + acf.__('No matches found') + '</li>');
          }

          // return
          return;
        }

        // set more (allows pagination scroll)
        this.set('more', json.more);

        // get new results
        var html = this.walkChoices(json.results);
        var $html = $(html);

        // apply .disabled to left li's
        var val = this.val();
        if (val && val.length) {
          val.map(function (id) {
            $html.find('.acf-rel-item[data-id="' + id + '"]').addClass('disabled');
          });
        }

        // append
        $choiceslist.append($html);

        // merge together groups
        var $prevLabel = false;
        var $prevList = false;
        $choiceslist.find('.acf-rel-label').each(function () {
          var $label = $(this);
          var $list = $label.siblings('ul');
          if ($prevLabel && $prevLabel.text() == $label.text()) {
            $prevList.append($list.children());
            $(this).parent().remove();
            return;
          }

          // update vars
          $prevLabel = $label;
          $prevList = $list;
        });
      };

      // get results
      var xhr = $.ajax({
        url: acf.get('ajaxurl'),
        dataType: 'json',
        type: 'post',
        data: acf.prepareForAjax(ajaxData),
        context: this,
        success: onSuccess,
        complete: onComplete
      });

      // set
      this.set('xhr', xhr);
    },
    walkChoices: function (data) {
      // walker
      var walk = function (data) {
        // vars
        var html = '';

        // is array
        if ($.isArray(data)) {
          data.map(function (item) {
            html += walk(item);
          });

          // is item
        } else if ($.isPlainObject(data)) {
          // group
          if (data.children !== undefined) {
            html += '<li><span class="acf-rel-label">' + acf.escHtml(data.text) + '</span><ul class="acf-bl">';
            html += walk(data.children);
            html += '</ul></li>';

            // single
          } else {
            html += '<li><span tabindex="0" class="acf-rel-item acf-rel-item-add" data-id="' + acf.escAttr(data.id) + '">' + acf.escHtml(data.text) + '</span></li>';
          }
        }

        // return
        return html;
      };
      return walk(data);
    }
  });
  acf.registerFieldType(Field);
})(jQuery);

/***/ }),

/***/ "./src/advanced-custom-fields-pro/assets/src/js/_acf-field-select.js":
/*!***************************************************************************!*\
  !*** ./src/advanced-custom-fields-pro/assets/src/js/_acf-field-select.js ***!
  \***************************************************************************/
/***/ (() => {

(function ($, undefined) {
  var Field = acf.Field.extend({
    type: 'select',
    select2: false,
    wait: 'load',
    events: {
      removeField: 'onRemove',
      duplicateField: 'onDuplicate'
    },
    $input: function () {
      return this.$('select');
    },
    initialize: function () {
      // vars
      var $select = this.$input();

      // inherit data
      this.inherit($select);

      // select2
      if (this.get('ui')) {
        // populate ajax_data (allowing custom attribute to already exist)
        var ajaxAction = this.get('ajax_action');
        if (!ajaxAction) {
          ajaxAction = 'acf/fields/' + this.get('type') + '/query';
        }

        // select2
        this.select2 = acf.newSelect2($select, {
          field: this,
          ajax: this.get('ajax'),
          multiple: this.get('multiple'),
          placeholder: this.get('placeholder'),
          allowNull: this.get('allow_null'),
          ajaxAction: ajaxAction
        });
      }
    },
    onRemove: function () {
      if (this.select2) {
        this.select2.destroy();
      }
    },
    onDuplicate: function (e, $el, $duplicate) {
      if (this.select2) {
        $duplicate.find('.select2-container').remove();
        $duplicate.find('select').removeClass('select2-hidden-accessible');
      }
    }
  });
  acf.registerFieldType(Field);
})(jQuery);

/***/ }),

/***/ "./src/advanced-custom-fields-pro/assets/src/js/_acf-field-tab.js":
/*!************************************************************************!*\
  !*** ./src/advanced-custom-fields-pro/assets/src/js/_acf-field-tab.js ***!
  \************************************************************************/
/***/ (() => {

(function ($, undefined) {
  // vars
  var CONTEXT = 'tab';
  var Field = acf.Field.extend({
    type: 'tab',
    wait: '',
    tabs: false,
    tab: false,
    events: {
      duplicateField: 'onDuplicate'
    },
    findFields: function () {
      let filter;

      /**
       * Tabs in the admin UI that can be extended by third
       * parties have the child settings wrapped inside an extra div,
       * so we need to look for that instead of an adjacent .acf-field.
       */
      switch (this.get('key')) {
        case 'acf_field_settings_tabs':
          filter = '.acf-field-settings-main';
          break;
        case 'acf_field_group_settings_tabs':
          filter = '.field-group-settings-tab';
          break;
        case 'acf_browse_fields_tabs':
          filter = '.acf-field-types-tab';
          break;
        case 'acf_post_type_tabs':
          filter = '.acf-post-type-advanced-settings';
          break;
        case 'acf_taxonomy_tabs':
          filter = '.acf-taxonomy-advanced-settings';
          break;
        case 'acf_ui_options_page_tabs':
          filter = '.acf-ui-options-page-advanced-settings';
          break;
        default:
          filter = '.acf-field';
      }
      return this.$el.nextUntil('.acf-field-tab', filter);
    },
    getFields: function () {
      return acf.getFields(this.findFields());
    },
    findTabs: function () {
      return this.$el.prevAll('.acf-tab-wrap:first');
    },
    findTab: function () {
      return this.$('.acf-tab-button');
    },
    initialize: function () {
      // bail early if is td
      if (this.$el.is('td')) {
        this.events = {};
        return false;
      }

      // vars
      var $tabs = this.findTabs();
      var $tab = this.findTab();
      var settings = acf.parseArgs($tab.data(), {
        endpoint: false,
        placement: '',
        before: this.$el
      });

      // create wrap
      if (!$tabs.length || settings.endpoint) {
        this.tabs = new Tabs(settings);
      } else {
        this.tabs = $tabs.data('acf');
      }

      // add tab
      this.tab = this.tabs.addTab($tab, this);
    },
    isActive: function () {
      return this.tab.isActive();
    },
    showFields: function () {
      // show fields
      this.getFields().map(function (field) {
        field.show(this.cid, CONTEXT);
        field.hiddenByTab = false;
      }, this);
    },
    hideFields: function () {
      // hide fields
      this.getFields().map(function (field) {
        field.hide(this.cid, CONTEXT);
        field.hiddenByTab = this.tab;
      }, this);
    },
    show: function (lockKey) {
      // show field and store result
      var visible = acf.Field.prototype.show.apply(this, arguments);

      // check if now visible
      if (visible) {
        // show tab
        this.tab.show();

        // check active tabs
        this.tabs.refresh();
      }

      // return
      return visible;
    },
    hide: function (lockKey) {
      // hide field and store result
      var hidden = acf.Field.prototype.hide.apply(this, arguments);

      // check if now hidden
      if (hidden) {
        // hide tab
        this.tab.hide();

        // reset tabs if this was active
        if (this.isActive()) {
          this.tabs.reset();
        }
      }

      // return
      return hidden;
    },
    enable: function (lockKey) {
      // enable fields
      this.getFields().map(function (field) {
        field.enable(CONTEXT);
      });
    },
    disable: function (lockKey) {
      // disable fields
      this.getFields().map(function (field) {
        field.disable(CONTEXT);
      });
    },
    onDuplicate: function (e, $el, $duplicate) {
      if (this.isActive()) {
        $duplicate.prevAll('.acf-tab-wrap:first').remove();
      }
    }
  });
  acf.registerFieldType(Field);

  /**
   *  tabs
   *
   *  description
   *
   *  @date	8/2/18
   *  @since	5.6.5
   *
   *  @param	type $var Description. Default.
   *  @return	type Description.
   */

  var i = 0;
  var Tabs = acf.Model.extend({
    tabs: [],
    active: false,
    actions: {
      refresh: 'onRefresh',
      close_field_object: 'onCloseFieldObject'
    },
    data: {
      before: false,
      placement: 'top',
      index: 0,
      initialized: false
    },
    setup: function (settings) {
      // data
      $.extend(this.data, settings);

      // define this prop to avoid scope issues
      this.tabs = [];
      this.active = false;

      // vars
      var placement = this.get('placement');
      var $before = this.get('before');
      var $parent = $before.parent();

      // add sidebar for left placement
      if (placement == 'left' && $parent.hasClass('acf-fields')) {
        $parent.addClass('-sidebar');
      }

      // create wrap
      if ($before.is('tr')) {
        this.$el = $('<tr class="acf-tab-wrap"><td colspan="2"><ul class="acf-hl acf-tab-group"></ul></td></tr>');
      } else {
        let ulClass = 'acf-hl acf-tab-group';
        if (this.get('key') === 'acf_field_settings_tabs') {
          ulClass = 'acf-field-settings-tab-bar';
        }
        this.$el = $('<div class="acf-tab-wrap -' + placement + '"><ul class="' + ulClass + '"></ul></div>');
      }

      // append
      $before.before(this.$el);

      // set index
      this.set('index', i, true);
      i++;
    },
    initializeTabs: function () {
      // Bail if tabs are disabled.
      if ('acf_field_settings_tabs' === this.get('key') && $('#acf-field-group-fields').hasClass('hide-tabs')) {
        return;
      }

      // find first visible tab
      var tab = this.getVisible().shift();

      // remember previous tab state
      var order = acf.getPreference('this.tabs') || [];
      var groupIndex = this.get('index');
      var tabIndex = order[groupIndex];
      if (this.tabs[tabIndex] && this.tabs[tabIndex].isVisible()) {
        tab = this.tabs[tabIndex];
      }

      // select
      if (tab) {
        this.selectTab(tab);
      } else {
        this.closeTabs();
      }

      // set local variable used by tabsManager
      this.set('initialized', true);
    },
    getVisible: function () {
      return this.tabs.filter(function (tab) {
        return tab.isVisible();
      });
    },
    getActive: function () {
      return this.active;
    },
    setActive: function (tab) {
      return this.active = tab;
    },
    hasActive: function () {
      return this.active !== false;
    },
    isActive: function (tab) {
      var active = this.getActive();
      return active && active.cid === tab.cid;
    },
    closeActive: function () {
      if (this.hasActive()) {
        this.closeTab(this.getActive());
      }
    },
    openTab: function (tab) {
      // close existing tab
      this.closeActive();

      // open
      tab.open();

      // set active
      this.setActive(tab);
    },
    closeTab: function (tab) {
      // close
      tab.close();

      // set active
      this.setActive(false);
    },
    closeTabs: function () {
      this.tabs.map(this.closeTab, this);
    },
    selectTab: function (tab) {
      // close other tabs
      this.tabs.map(function (t) {
        if (tab.cid !== t.cid) {
          this.closeTab(t);
        }
      }, this);

      // open
      this.openTab(tab);
    },
    addTab: function ($a, field) {
      // create <li>
      var $li = $('<li>' + $a.outerHTML() + '</li>');

      // add settings type class.
      var classes = $a.attr('class').replace('acf-tab-button', '');
      $li.addClass(classes);

      // append
      this.$('ul').append($li);

      // initialize
      var tab = new Tab({
        $el: $li,
        field: field,
        group: this
      });

      // store
      this.tabs.push(tab);

      // return
      return tab;
    },
    reset: function () {
      // close existing tab
      this.closeActive();

      // find and active a tab
      return this.refresh();
    },
    refresh: function () {
      // bail early if active already exists
      if (this.hasActive()) {
        return false;
      }
      // find next active tab
      var tab = this.getVisible().shift();
      // open tab
      if (tab) {
        this.openTab(tab);
      }

      // return
      return tab;
    },
    onRefresh: function () {
      // only for left placements
      if (this.get('placement') !== 'left') {
        return;
      }

      // vars
      var $parent = this.$el.parent();
      var $list = this.$el.children('ul');
      var attribute = $parent.is('td') ? 'height' : 'min-height';

      // find height (minus 1 for border-bottom)
      var height = $list.position().top + $list.outerHeight(true) - 1;

      // add css
      $parent.css(attribute, height);
    },
    onCloseFieldObject: function (fieldObject) {
      const tab = this.getVisible().find(item => {
        const id = item.$el.closest('div[data-id]').data('id');
        if (fieldObject.data.id === id) {
          return item;
        }
      });
      if (tab) {
        // Wait for field group drawer to close
        setTimeout(() => {
          this.openTab(tab);
        }, 300);
      }
    }
  });
  var Tab = acf.Model.extend({
    group: false,
    field: false,
    events: {
      'click a': 'onClick'
    },
    index: function () {
      return this.$el.index();
    },
    isVisible: function () {
      return acf.isVisible(this.$el);
    },
    isActive: function () {
      return this.$el.hasClass('active');
    },
    open: function () {
      // add class
      this.$el.addClass('active');

      // show field
      this.field.showFields();
    },
    close: function () {
      // remove class
      this.$el.removeClass('active');

      // hide field
      this.field.hideFields();
    },
    onClick: function (e, $el) {
      // prevent default
      e.preventDefault();

      // toggle
      this.toggle();
    },
    toggle: function () {
      // bail early if already active
      if (this.isActive()) {
        return;
      }

      // toggle this tab
      this.group.openTab(this);
    }
  });
  var tabsManager = new acf.Model({
    priority: 50,
    actions: {
      prepare: 'render',
      append: 'render',
      unload: 'onUnload',
      show: 'render',
      invalid_field: 'onInvalidField'
    },
    findTabs: function () {
      return $('.acf-tab-wrap');
    },
    getTabs: function () {
      return acf.getInstances(this.findTabs());
    },
    render: function ($el) {
      this.getTabs().map(function (tabs) {
        if (!tabs.get('initialized')) {
          tabs.initializeTabs();
        }
      });
    },
    onInvalidField: function (field) {
      // bail early if busy
      if (this.busy) {
        return;
      }

      // ignore if not hidden by tab
      if (!field.hiddenByTab) {
        return;
      }

      // toggle tab
      field.hiddenByTab.toggle();

      // ignore other invalid fields
      this.busy = true;
      this.setTimeout(function () {
        this.busy = false;
      }, 100);
    },
    onUnload: function () {
      // vars
      var order = [];

      // loop
      this.getTabs().map(function (group) {
        // Do not save selected tab on field settings, or an acf-advanced-settings when unloading
        if (group.$el.children('.acf-field-settings-tab-bar').length || group.$el.parents('#acf-advanced-settings.postbox').length) {
          return true;
        }
        var active = group.hasActive() ? group.getActive().index() : 0;
        order.push(active);
      });

      // bail if no tabs
      if (!order.length) {
        return;
      }

      // update
      acf.setPreference('this.tabs', order);
    }
  });
})(jQuery);

/***/ }),

/***/ "./src/advanced-custom-fields-pro/assets/src/js/_acf-field-taxonomy.js":
/*!*****************************************************************************!*\
  !*** ./src/advanced-custom-fields-pro/assets/src/js/_acf-field-taxonomy.js ***!
  \*****************************************************************************/
/***/ (() => {

(function ($, undefined) {
  var Field = acf.Field.extend({
    type: 'taxonomy',
    data: {
      ftype: 'select'
    },
    select2: false,
    wait: 'load',
    events: {
      'click a[data-name="add"]': 'onClickAdd',
      'click input[type="radio"]': 'onClickRadio',
      removeField: 'onRemove'
    },
    $control: function () {
      return this.$('.acf-taxonomy-field');
    },
    $input: function () {
      return this.getRelatedPrototype().$input.apply(this, arguments);
    },
    getRelatedType: function () {
      // vars
      var fieldType = this.get('ftype');

      // normalize
      if (fieldType == 'multi_select') {
        fieldType = 'select';
      }

      // return
      return fieldType;
    },
    getRelatedPrototype: function () {
      return acf.getFieldType(this.getRelatedType()).prototype;
    },
    getValue: function () {
      return this.getRelatedPrototype().getValue.apply(this, arguments);
    },
    setValue: function () {
      return this.getRelatedPrototype().setValue.apply(this, arguments);
    },
    initialize: function () {
      this.getRelatedPrototype().initialize.apply(this, arguments);
    },
    onRemove: function () {
      var proto = this.getRelatedPrototype();
      if (proto.onRemove) {
        proto.onRemove.apply(this, arguments);
      }
    },
    onClickAdd: function (e, $el) {
      // vars
      var field = this;
      var popup = false;
      var $form = false;
      var $name = false;
      var $parent = false;
      var $button = false;
      var $message = false;
      var notice = false;

      // step 1.
      var step1 = function () {
        // popup
        popup = acf.newPopup({
          title: $el.attr('title'),
          loading: true,
          width: '300px'
        });

        // ajax
        var ajaxData = {
          action: 'acf/fields/taxonomy/add_term',
          field_key: field.get('key')
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

      // step 2.
      var step2 = function (html) {
        // update popup
        popup.loading(false);
        popup.content(html);

        // vars
        $form = popup.$('form');
        $name = popup.$('input[name="term_name"]');
        $parent = popup.$('select[name="term_parent"]');
        $button = popup.$('.acf-submit-button');

        // focus
        $name.trigger('focus');

        // submit form
        popup.on('submit', 'form', step3);
      };

      // step 3.
      var step3 = function (e, $el) {
        // prevent
        e.preventDefault();
        e.stopImmediatePropagation();

        // basic validation
        if ($name.val() === '') {
          $name.trigger('focus');
          return false;
        }

        // disable
        acf.startButtonLoading($button);

        // ajax
        var ajaxData = {
          action: 'acf/fields/taxonomy/add_term',
          field_key: field.get('key'),
          term_name: $name.val(),
          term_parent: $parent.length ? $parent.val() : 0
        };
        $.ajax({
          url: acf.get('ajaxurl'),
          data: acf.prepareForAjax(ajaxData),
          type: 'post',
          dataType: 'json',
          success: step4
        });
      };

      // step 4.
      var step4 = function (json) {
        // enable
        acf.stopButtonLoading($button);

        // remove prev notice
        if (notice) {
          notice.remove();
        }

        // success
        if (acf.isAjaxSuccess(json)) {
          // clear name
          $name.val('');

          // update term lists
          step5(json.data);

          // notice
          notice = acf.newNotice({
            type: 'success',
            text: acf.getAjaxMessage(json),
            target: $form,
            timeout: 2000,
            dismiss: false
          });
        } else {
          // notice
          notice = acf.newNotice({
            type: 'error',
            text: acf.getAjaxError(json),
            target: $form,
            timeout: 2000,
            dismiss: false
          });
        }

        // focus
        $name.trigger('focus');
      };

      // step 5.
      var step5 = function (term) {
        // update parent dropdown
        var $option = $('<option value="' + term.term_id + '">' + term.term_label + '</option>');
        if (term.term_parent) {
          $parent.children('option[value="' + term.term_parent + '"]').after($option);
        } else {
          $parent.append($option);
        }

        // add this new term to all taxonomy field
        var fields = acf.getFields({
          type: 'taxonomy'
        });
        fields.map(function (otherField) {
          if (otherField.get('taxonomy') == field.get('taxonomy')) {
            otherField.appendTerm(term);
          }
        });

        // select
        field.selectTerm(term.term_id);
      };

      // run
      step1();
    },
    appendTerm: function (term) {
      if (this.getRelatedType() == 'select') {
        this.appendTermSelect(term);
      } else {
        this.appendTermCheckbox(term);
      }
    },
    appendTermSelect: function (term) {
      this.select2.addOption({
        id: term.term_id,
        text: term.term_label
      });
    },
    appendTermCheckbox: function (term) {
      // vars
      var name = this.$('[name]:first').attr('name');
      var $ul = this.$('ul:first');

      // allow multiple selection
      if (this.getRelatedType() == 'checkbox') {
        name += '[]';
      }

      // create new li
      var $li = $(['<li data-id="' + term.term_id + '">', '<label>', '<input type="' + this.get('ftype') + '" value="' + term.term_id + '" name="' + name + '" /> ', '<span>' + term.term_name + '</span>', '</label>', '</li>'].join(''));

      // find parent
      if (term.term_parent) {
        // vars
        var $parent = $ul.find('li[data-id="' + term.term_parent + '"]');

        // update vars
        $ul = $parent.children('ul');

        // create ul
        if (!$ul.exists()) {
          $ul = $('<ul class="children acf-bl"></ul>');
          $parent.append($ul);
        }
      }

      // append
      $ul.append($li);
    },
    selectTerm: function (id) {
      if (this.getRelatedType() == 'select') {
        this.select2.selectOption(id);
      } else {
        var $input = this.$('input[value="' + id + '"]');
        $input.prop('checked', true).trigger('change');
      }
    },
    onClickRadio: function (e, $el) {
      // vars
      var $label = $el.parent('label');
      var selected = $label.hasClass('selected');

      // remove previous selected
      this.$('.selected').removeClass('selected');

      // add active class
      $label.addClass('selected');

      // allow null
      if (this.get('allow_null') && selected) {
        $label.removeClass('selected');
        $el.prop('checked', false).trigger('change');
      }
    }
  });
  acf.registerFieldType(Field);
})(jQuery);

/***/ }),

/***/ "./src/advanced-custom-fields-pro/assets/src/js/_acf-field-time-picker.js":
/*!********************************************************************************!*\
  !*** ./src/advanced-custom-fields-pro/assets/src/js/_acf-field-time-picker.js ***!
  \********************************************************************************/
/***/ (() => {

(function ($, undefined) {
  var Field = acf.models.DatePickerField.extend({
    type: 'time_picker',
    $control: function () {
      return this.$('.acf-time-picker');
    },
    initialize: function () {
      // vars
      var $input = this.$input();
      var $inputText = this.$inputText();

      // args
      var args = {
        timeFormat: this.get('time_format'),
        altField: $input,
        altFieldTimeOnly: false,
        altTimeFormat: 'HH:mm:ss',
        showButtonPanel: true,
        controlType: 'select',
        oneLine: true,
        closeText: acf.get('dateTimePickerL10n').selectText,
        timeOnly: true
      };

      // add custom 'Close = Select' functionality
      args.onClose = function (value, dp_instance, t_instance) {
        // vars
        var $close = dp_instance.dpDiv.find('.ui-datepicker-close');

        // if clicking close button
        if (!value && $close.is(':hover')) {
          t_instance._updateDateTime();
        }
      };

      // filter
      args = acf.applyFilters('time_picker_args', args, this);

      // add date time picker
      acf.newTimePicker($inputText, args);

      // action
      acf.doAction('time_picker_init', $inputText, args, this);
    }
  });
  acf.registerFieldType(Field);

  // add
  acf.newTimePicker = function ($input, args) {
    // bail early if no datepicker library
    if (typeof $.timepicker === 'undefined') {
      return false;
    }

    // defaults
    args = args || {};

    // initialize
    $input.timepicker(args);

    // wrap the datepicker (only if it hasn't already been wrapped)
    if ($('body > #ui-datepicker-div').exists()) {
      $('body > #ui-datepicker-div').wrap('<div class="acf-ui-datepicker" />');
    }
  };
})(jQuery);

/***/ }),

/***/ "./src/advanced-custom-fields-pro/assets/src/js/_acf-field-true-false.js":
/*!*******************************************************************************!*\
  !*** ./src/advanced-custom-fields-pro/assets/src/js/_acf-field-true-false.js ***!
  \*******************************************************************************/
/***/ (() => {

(function ($, undefined) {
  var Field = acf.Field.extend({
    type: 'true_false',
    events: {
      'change .acf-switch-input': 'onChange',
      'focus .acf-switch-input': 'onFocus',
      'blur .acf-switch-input': 'onBlur',
      'keypress .acf-switch-input': 'onKeypress'
    },
    $input: function () {
      return this.$('input[type="checkbox"]');
    },
    $switch: function () {
      return this.$('.acf-switch');
    },
    getValue: function () {
      return this.$input().prop('checked') ? 1 : 0;
    },
    initialize: function () {
      this.render();
    },
    render: function () {
      // vars
      var $switch = this.$switch();

      // bail early if no $switch
      if (!$switch.length) return;

      // vars
      var $on = $switch.children('.acf-switch-on');
      var $off = $switch.children('.acf-switch-off');
      var width = Math.max($on.width(), $off.width());

      // bail early if no width
      if (!width) return;

      // set widths
      $on.css('min-width', width);
      $off.css('min-width', width);
    },
    switchOn: function () {
      this.$input().prop('checked', true);
      this.$switch().addClass('-on');
    },
    switchOff: function () {
      this.$input().prop('checked', false);
      this.$switch().removeClass('-on');
    },
    onChange: function (e, $el) {
      if ($el.prop('checked')) {
        this.switchOn();
      } else {
        this.switchOff();
      }
    },
    onFocus: function (e, $el) {
      this.$switch().addClass('-focus');
    },
    onBlur: function (e, $el) {
      this.$switch().removeClass('-focus');
    },
    onKeypress: function (e, $el) {
      // left
      if (e.keyCode === 37) {
        return this.switchOff();
      }

      // right
      if (e.keyCode === 39) {
        return this.switchOn();
      }
    }
  });
  acf.registerFieldType(Field);
})(jQuery);

/***/ }),

/***/ "./src/advanced-custom-fields-pro/assets/src/js/_acf-field-url.js":
/*!************************************************************************!*\
  !*** ./src/advanced-custom-fields-pro/assets/src/js/_acf-field-url.js ***!
  \************************************************************************/
/***/ (() => {

(function ($, undefined) {
  var Field = acf.Field.extend({
    type: 'url',
    events: {
      'keyup input[type="url"]': 'onkeyup'
    },
    $control: function () {
      return this.$('.acf-input-wrap');
    },
    $input: function () {
      return this.$('input[type="url"]');
    },
    initialize: function () {
      this.render();
    },
    isValid: function () {
      // vars
      var val = this.val();

      // bail early if no val
      if (!val) {
        return false;
      }

      // url
      if (val.indexOf('://') !== -1) {
        return true;
      }

      // protocol relative url
      if (val.indexOf('//') === 0) {
        return true;
      }

      // return
      return false;
    },
    render: function () {
      // add class
      if (this.isValid()) {
        this.$control().addClass('-valid');
      } else {
        this.$control().removeClass('-valid');
      }
    },
    onkeyup: function (e, $el) {
      this.render();
    }
  });
  acf.registerFieldType(Field);
})(jQuery);

/***/ }),

/***/ "./src/advanced-custom-fields-pro/assets/src/js/_acf-field-user.js":
/*!*************************************************************************!*\
  !*** ./src/advanced-custom-fields-pro/assets/src/js/_acf-field-user.js ***!
  \*************************************************************************/
/***/ (() => {

(function ($, undefined) {
  var Field = acf.models.SelectField.extend({
    type: 'user'
  });
  acf.registerFieldType(Field);
  acf.addFilter('select2_ajax_data', function (data, args, $input, field, select2) {
    if (!field) {
      return data;
    }
    const query_nonce = field.get('queryNonce');
    if (query_nonce && query_nonce.length) {
      data.user_query_nonce = query_nonce;
    }
    return data;
  });
})(jQuery);

/***/ }),

/***/ "./src/advanced-custom-fields-pro/assets/src/js/_acf-field-wysiwyg.js":
/*!****************************************************************************!*\
  !*** ./src/advanced-custom-fields-pro/assets/src/js/_acf-field-wysiwyg.js ***!
  \****************************************************************************/
/***/ (() => {

(function ($, undefined) {
  var Field = acf.Field.extend({
    type: 'wysiwyg',
    wait: 'load',
    events: {
      'mousedown .acf-editor-wrap.delay': 'onMousedown',
      unmountField: 'disableEditor',
      remountField: 'enableEditor',
      removeField: 'disableEditor'
    },
    $control: function () {
      return this.$('.acf-editor-wrap');
    },
    $input: function () {
      return this.$('textarea');
    },
    getMode: function () {
      return this.$control().hasClass('tmce-active') ? 'visual' : 'text';
    },
    initialize: function () {
      // initializeEditor if no delay
      if (!this.$control().hasClass('delay')) {
        this.initializeEditor();
      }
    },
    initializeEditor: function () {
      // vars
      var $wrap = this.$control();
      var $textarea = this.$input();
      var args = {
        tinymce: true,
        quicktags: true,
        toolbar: this.get('toolbar'),
        mode: this.getMode(),
        field: this
      };

      // generate new id
      var oldId = $textarea.attr('id');
      var newId = acf.uniqueId('acf-editor-');

      // Backup textarea data.
      var inputData = $textarea.data();
      var inputVal = $textarea.val();

      // rename
      acf.rename({
        target: $wrap,
        search: oldId,
        replace: newId,
        destructive: true
      });

      // update id
      this.set('id', newId, true);

      // apply data to new textarea (acf.rename creates a new textarea element due to destructive mode)
      // fixes bug where conditional logic "disabled" is lost during "screen_check"
      this.$input().data(inputData).val(inputVal);

      // initialize
      acf.tinymce.initialize(newId, args);
    },
    onMousedown: function (e) {
      // prevent default
      e.preventDefault();

      // remove delay class
      var $wrap = this.$control();
      $wrap.removeClass('delay');
      $wrap.find('.acf-editor-toolbar').remove();

      // initialize
      this.initializeEditor();
    },
    enableEditor: function () {
      if (this.getMode() == 'visual') {
        acf.tinymce.enable(this.get('id'));
      }
    },
    disableEditor: function () {
      acf.tinymce.destroy(this.get('id'));
    }
  });
  acf.registerFieldType(Field);
})(jQuery);

/***/ }),

/***/ "./src/advanced-custom-fields-pro/assets/src/js/_acf-field.js":
/*!********************************************************************!*\
  !*** ./src/advanced-custom-fields-pro/assets/src/js/_acf-field.js ***!
  \********************************************************************/
/***/ (() => {

(function ($, undefined) {
  // vars
  var storage = [];

  /**
   *  acf.Field
   *
   *  description
   *
   *  @date	23/3/18
   *  @since	5.6.9
   *
   *  @param	type $var Description. Default.
   *  @return	type Description.
   */

  acf.Field = acf.Model.extend({
    // field type
    type: '',
    // class used to avoid nested event triggers
    eventScope: '.acf-field',
    // initialize events on 'ready'
    wait: 'ready',
    /**
     *  setup
     *
     *  Called during the constructor function to setup this field ready for initialization
     *
     *  @date	8/5/18
     *  @since	5.6.9
     *
     *  @param	jQuery $field The field element.
     *  @return	void
     */

    setup: function ($field) {
      // set $el
      this.$el = $field;

      // inherit $field data
      this.inherit($field);

      // inherit controll data
      this.inherit(this.$control());
    },
    /**
     *  val
     *
     *  Sets or returns the field's value
     *
     *  @date	8/5/18
     *  @since	5.6.9
     *
     *  @param	mixed val Optional. The value to set
     *  @return	mixed
     */

    val: function (val) {
      // Set.
      if (val !== undefined) {
        return this.setValue(val);

        // Get.
      } else {
        return this.prop('disabled') ? null : this.getValue();
      }
    },
    /**
     *  getValue
     *
     *  returns the field's value
     *
     *  @date	8/5/18
     *  @since	5.6.9
     *
     *  @param	void
     *  @return	mixed
     */

    getValue: function () {
      return this.$input().val();
    },
    /**
     *  setValue
     *
     *  sets the field's value and returns true if changed
     *
     *  @date	8/5/18
     *  @since	5.6.9
     *
     *  @param	mixed val
     *  @return	boolean. True if changed.
     */

    setValue: function (val) {
      return acf.val(this.$input(), val);
    },
    /**
     *  __
     *
     *  i18n helper to be removed
     *
     *  @date	8/5/18
     *  @since	5.6.9
     *
     *  @param	type $var Description. Default.
     *  @return	type Description.
     */

    __: function (string) {
      return acf._e(this.type, string);
    },
    /**
     *  $control
     *
     *  returns the control jQuery element used for inheriting data. Uses this.control setting.
     *
     *  @date	8/5/18
     *  @since	5.6.9
     *
     *  @param	void
     *  @return	jQuery
     */

    $control: function () {
      return false;
    },
    /**
     *  $input
     *
     *  returns the input jQuery element used for saving values. Uses this.input setting.
     *
     *  @date	8/5/18
     *  @since	5.6.9
     *
     *  @param	void
     *  @return	jQuery
     */

    $input: function () {
      return this.$('[name]:first');
    },
    /**
     *  $inputWrap
     *
     *  description
     *
     *  @date	12/5/18
     *  @since	5.6.9
     *
     *  @param	type $var Description. Default.
     *  @return	type Description.
     */

    $inputWrap: function () {
      return this.$('.acf-input:first');
    },
    /**
     *  $inputWrap
     *
     *  description
     *
     *  @date	12/5/18
     *  @since	5.6.9
     *
     *  @param	type $var Description. Default.
     *  @return	type Description.
     */

    $labelWrap: function () {
      return this.$('.acf-label:first');
    },
    /**
     *  getInputName
     *
     *  Returns the field's input name
     *
     *  @date	8/5/18
     *  @since	5.6.9
     *
     *  @param	void
     *  @return	string
     */

    getInputName: function () {
      return this.$input().attr('name') || '';
    },
    /**
     *  parent
     *
     *  returns the field's parent field or false on failure.
     *
     *  @date	8/5/18
     *  @since	5.6.9
     *
     *  @param	void
     *  @return	object|false
     */

    parent: function () {
      // vars
      var parents = this.parents();

      // return
      return parents.length ? parents[0] : false;
    },
    /**
     *  parents
     *
     *  description
     *
     *  @date	9/7/18
     *  @since	5.6.9
     *
     *  @param	type $var Description. Default.
     *  @return	type Description.
     */

    parents: function () {
      // vars
      var $parents = this.$el.parents('.acf-field');

      // convert
      var parents = acf.getFields($parents);

      // return
      return parents;
    },
    show: function (lockKey, context) {
      // show field and store result
      var changed = acf.show(this.$el, lockKey);

      // do action if visibility has changed
      if (changed) {
        this.prop('hidden', false);
        acf.doAction('show_field', this, context);
      }

      // return
      return changed;
    },
    hide: function (lockKey, context) {
      // hide field and store result
      var changed = acf.hide(this.$el, lockKey);

      // do action if visibility has changed
      if (changed) {
        this.prop('hidden', true);
        acf.doAction('hide_field', this, context);
      }

      // return
      return changed;
    },
    enable: function (lockKey, context) {
      // enable field and store result
      var changed = acf.enable(this.$el, lockKey);

      // do action if disabled has changed
      if (changed) {
        this.prop('disabled', false);
        acf.doAction('enable_field', this, context);
      }

      // return
      return changed;
    },
    disable: function (lockKey, context) {
      // disabled field and store result
      var changed = acf.disable(this.$el, lockKey);

      // do action if disabled has changed
      if (changed) {
        this.prop('disabled', true);
        acf.doAction('disable_field', this, context);
      }

      // return
      return changed;
    },
    showEnable: function (lockKey, context) {
      // enable
      this.enable.apply(this, arguments);

      // show and return true if changed
      return this.show.apply(this, arguments);
    },
    hideDisable: function (lockKey, context) {
      // disable
      this.disable.apply(this, arguments);

      // hide and return true if changed
      return this.hide.apply(this, arguments);
    },
    showNotice: function (props) {
      // ensure object
      if (typeof props !== 'object') {
        props = {
          text: props
        };
      }

      // remove old notice
      if (this.notice) {
        this.notice.remove();
      }

      // create new notice
      props.target = this.$inputWrap();
      this.notice = acf.newNotice(props);
    },
    removeNotice: function (timeout) {
      if (this.notice) {
        this.notice.away(timeout || 0);
        this.notice = false;
      }
    },
    showError: function (message) {
      // add class
      this.$el.addClass('acf-error');

      // add message
      if (message !== undefined) {
        this.showNotice({
          text: message,
          type: 'error',
          dismiss: false
        });
      }

      // action
      acf.doAction('invalid_field', this);

      // add event
      this.$el.one('focus change', 'input, select, textarea', $.proxy(this.removeError, this));
    },
    removeError: function () {
      // remove class
      this.$el.removeClass('acf-error');

      // remove notice
      this.removeNotice(250);

      // action
      acf.doAction('valid_field', this);
    },
    trigger: function (name, args, bubbles) {
      // allow some events to bubble
      if (name == 'invalidField') {
        bubbles = true;
      }

      // return
      return acf.Model.prototype.trigger.apply(this, [name, args, bubbles]);
    }
  });

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

  acf.newField = function ($field) {
    // vars
    var type = $field.data('type');
    var mid = modelId(type);
    var model = acf.models[mid] || acf.Field;

    // instantiate
    var field = new model($field);

    // actions
    acf.doAction('new_field', field);

    // return
    return field;
  };

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
    return acf.strPascalCase(type || '') + 'Field';
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

  acf.registerFieldType = function (model) {
    // vars
    var proto = model.prototype;
    var type = proto.type;
    var mid = modelId(type);

    // store model
    acf.models[mid] = model;

    // store reference
    storage.push(type);
  };

  /**
   *  acf.getFieldType
   *
   *  description
   *
   *  @date	1/2/18
   *  @since	5.6.5
   *
   *  @param	type $var Description. Default.
   *  @return	type Description.
   */

  acf.getFieldType = function (type) {
    var mid = modelId(type);
    return acf.models[mid] || false;
  };

  /**
   *  acf.getFieldTypes
   *
   *  description
   *
   *  @date	1/2/18
   *  @since	5.6.5
   *
   *  @param	type $var Description. Default.
   *  @return	type Description.
   */

  acf.getFieldTypes = function (args) {
    // defaults
    args = acf.parseArgs(args, {
      category: ''
      // hasValue: true
    });

    // clonse available types
    var types = [];

    // loop
    storage.map(function (type) {
      // vars
      var model = acf.getFieldType(type);
      var proto = model.prototype;

      // check operator
      if (args.category && proto.category !== args.category) {
        return;
      }

      // append
      types.push(model);
    });

    // return
    return types;
  };
})(jQuery);

/***/ }),

/***/ "./src/advanced-custom-fields-pro/assets/src/js/_acf-fields.js":
/*!*********************************************************************!*\
  !*** ./src/advanced-custom-fields-pro/assets/src/js/_acf-fields.js ***!
  \*********************************************************************/
/***/ (() => {

(function ($, undefined) {
  /**
   *  findFields
   *
   *  Returns a jQuery selection object of acf fields.
   *
   *  @date	14/12/17
   *  @since	5.6.5
   *
   *  @param	object $args {
   *		Optional. Arguments to find fields.
   *
   *		@type string			key			The field's key (data-attribute).
   *		@type string			name		The field's name (data-attribute).
   *		@type string			type		The field's type (data-attribute).
   *		@type string			is			jQuery selector to compare against.
   *		@type jQuery			parent		jQuery element to search within.
   *		@type jQuery			sibling		jQuery element to search alongside.
   *		@type limit				int			The number of fields to find.
   *		@type suppressFilters	bool		Whether to allow filters to add/remove results. Default behaviour will ignore clone fields.
   *  }
   *  @return	jQuery
   */

  acf.findFields = function (args) {
    // vars
    var selector = '.acf-field';
    var $fields = false;

    // args
    args = acf.parseArgs(args, {
      key: '',
      name: '',
      type: '',
      is: '',
      parent: false,
      sibling: false,
      limit: false,
      visible: false,
      suppressFilters: false,
      excludeSubFields: false
    });

    // filter args
    if (!args.suppressFilters) {
      args = acf.applyFilters('find_fields_args', args);
    }

    // key
    if (args.key) {
      selector += '[data-key="' + args.key + '"]';
    }

    // type
    if (args.type) {
      selector += '[data-type="' + args.type + '"]';
    }

    // name
    if (args.name) {
      selector += '[data-name="' + args.name + '"]';
    }

    // is
    if (args.is) {
      selector += args.is;
    }

    // visibility
    if (args.visible) {
      selector += ':visible';
    }
    if (!args.suppressFilters) {
      selector = acf.applyFilters('find_fields_selector', selector, args);
    }

    // query
    if (args.parent) {
      $fields = args.parent.find(selector);
      // exclude sub fields if required (only if a parent is provided)
      if (args.excludeSubFields) {
        $fields = $fields.not(args.parent.find('.acf-is-subfields .acf-field'));
      }
    } else if (args.sibling) {
      $fields = args.sibling.siblings(selector);
    } else {
      $fields = $(selector);
    }

    // filter
    if (!args.suppressFilters) {
      $fields = $fields.not('.acf-clone .acf-field');
      $fields = acf.applyFilters('find_fields', $fields);
    }

    // limit
    if (args.limit) {
      $fields = $fields.slice(0, args.limit);
    }

    // return
    return $fields;
  };

  /**
   *  findField
   *
   *  Finds a specific field with jQuery
   *
   *  @date	14/12/17
   *  @since	5.6.5
   *
   *  @param	string key 		The field's key.
   *  @param	jQuery $parent	jQuery element to search within.
   *  @return	jQuery
   */

  acf.findField = function (key, $parent) {
    return acf.findFields({
      key: key,
      limit: 1,
      parent: $parent,
      suppressFilters: true
    });
  };

  /**
   *  getField
   *
   *  Returns a field instance
   *
   *  @date	14/12/17
   *  @since	5.6.5
   *
   *  @param	jQuery|string $field	jQuery element or field key.
   *  @return	object
   */

  acf.getField = function ($field) {
    // allow jQuery
    if ($field instanceof jQuery) {
      // find fields
    } else {
      $field = acf.findField($field);
    }

    // instantiate
    var field = $field.data('acf');
    if (!field) {
      field = acf.newField($field);
    }

    // return
    return field;
  };

  /**
   *  getFields
   *
   *  Returns multiple field instances
   *
   *  @date	14/12/17
   *  @since	5.6.5
   *
   *  @param	jQuery|object $fields	jQuery elements or query args.
   *  @return	array
   */

  acf.getFields = function ($fields) {
    // allow jQuery
    if ($fields instanceof jQuery) {
      // find fields
    } else {
      $fields = acf.findFields($fields);
    }

    // loop
    var fields = [];
    $fields.each(function () {
      var field = acf.getField($(this));
      fields.push(field);
    });

    // return
    return fields;
  };

  /**
   *  findClosestField
   *
   *  Returns the closest jQuery field element
   *
   *  @date	9/4/18
   *  @since	5.6.9
   *
   *  @param	jQuery $el
   *  @return	jQuery
   */

  acf.findClosestField = function ($el) {
    return $el.closest('.acf-field');
  };

  /**
   *  getClosestField
   *
   *  Returns the closest field instance
   *
   *  @date	22/1/18
   *  @since	5.6.5
   *
   *  @param	jQuery $el
   *  @return	object
   */

  acf.getClosestField = function ($el) {
    var $field = acf.findClosestField($el);
    return this.getField($field);
  };

  /**
   *  addGlobalFieldAction
   *
   *  Sets up callback logic for global field actions
   *
   *  @date	15/6/18
   *  @since	5.6.9
   *
   *  @param	string action
   *  @return	void
   */

  var addGlobalFieldAction = function (action) {
    // vars
    var globalAction = action;
    var pluralAction = action + '_fields'; // ready_fields
    var singleAction = action + '_field'; // ready_field

    // global action
    var globalCallback = function ($el /*, arg1, arg2, etc*/) {
      //console.log( action, arguments );

      // get args [$el, ...]
      var args = acf.arrayArgs(arguments);
      var extraArgs = args.slice(1);

      // find fields
      var fields = acf.getFields({
        parent: $el
      });

      // check
      if (fields.length) {
        // pluralAction
        var pluralArgs = [pluralAction, fields].concat(extraArgs);
        acf.doAction.apply(null, pluralArgs);
      }
    };

    // plural action
    var pluralCallback = function (fields /*, arg1, arg2, etc*/) {
      //console.log( pluralAction, arguments );

      // get args [fields, ...]
      var args = acf.arrayArgs(arguments);
      var extraArgs = args.slice(1);

      // loop
      fields.map(function (field, i) {
        //setTimeout(function(){
        // singleAction
        var singleArgs = [singleAction, field].concat(extraArgs);
        acf.doAction.apply(null, singleArgs);
        //}, i * 100);
      });
    };

    // add actions
    acf.addAction(globalAction, globalCallback);
    acf.addAction(pluralAction, pluralCallback);

    // also add single action
    addSingleFieldAction(action);
  };

  /**
   *  addSingleFieldAction
   *
   *  Sets up callback logic for single field actions
   *
   *  @date	15/6/18
   *  @since	5.6.9
   *
   *  @param	string action
   *  @return	void
   */

  var addSingleFieldAction = function (action) {
    // vars
    var singleAction = action + '_field'; // ready_field
    var singleEvent = action + 'Field'; // readyField

    // single action
    var singleCallback = function (field /*, arg1, arg2, etc*/) {
      //console.log( singleAction, arguments );

      // get args [field, ...]
      var args = acf.arrayArgs(arguments);
      var extraArgs = args.slice(1);

      // action variations (ready_field/type=image)
      var variations = ['type', 'name', 'key'];
      variations.map(function (variation) {
        // vars
        var prefix = '/' + variation + '=' + field.get(variation);

        // singleAction
        args = [singleAction + prefix, field].concat(extraArgs);
        acf.doAction.apply(null, args);
      });

      // event
      if (singleFieldEvents.indexOf(action) > -1) {
        field.trigger(singleEvent, extraArgs);
      }
    };

    // add actions
    acf.addAction(singleAction, singleCallback);
  };

  // vars
  var globalFieldActions = ['prepare', 'ready', 'load', 'append', 'remove', 'unmount', 'remount', 'sortstart', 'sortstop', 'show', 'hide', 'unload'];
  var singleFieldActions = ['valid', 'invalid', 'enable', 'disable', 'new', 'duplicate'];
  var singleFieldEvents = ['remove', 'unmount', 'remount', 'sortstart', 'sortstop', 'show', 'hide', 'unload', 'valid', 'invalid', 'enable', 'disable', 'duplicate'];

  // add
  globalFieldActions.map(addGlobalFieldAction);
  singleFieldActions.map(addSingleFieldAction);

  /**
   *  fieldsEventManager
   *
   *  Manages field actions and events
   *
   *  @date	15/12/17
   *  @since	5.6.5
   *
   *  @param	void
   *  @param	void
   */

  var fieldsEventManager = new acf.Model({
    id: 'fieldsEventManager',
    events: {
      'click .acf-field a[href="#"]': 'onClick',
      'change .acf-field': 'onChange'
    },
    onClick: function (e) {
      // prevent default of any link with an href of #
      e.preventDefault();
    },
    onChange: function () {
      // preview hack allows post to save with no title or content
      $('#_acf_changed').val(1);
    }
  });
  var duplicateFieldsManager = new acf.Model({
    id: 'duplicateFieldsManager',
    actions: {
      duplicate: 'onDuplicate',
      duplicate_fields: 'onDuplicateFields'
    },
    onDuplicate: function ($el, $el2) {
      var fields = acf.getFields({
        parent: $el
      });
      if (fields.length) {
        var $fields = acf.findFields({
          parent: $el2
        });
        acf.doAction('duplicate_fields', fields, $fields);
      }
    },
    onDuplicateFields: function (fields, duplicates) {
      fields.map(function (field, i) {
        acf.doAction('duplicate_field', field, $(duplicates[i]));
      });
    }
  });
})(jQuery);

/***/ }),

/***/ "./src/advanced-custom-fields-pro/assets/src/js/_acf-helpers.js":
/*!**********************************************************************!*\
  !*** ./src/advanced-custom-fields-pro/assets/src/js/_acf-helpers.js ***!
  \**********************************************************************/
/***/ (() => {

(function ($, undefined) {
  /**
   *  refreshHelper
   *
   *  description
   *
   *  @date	1/7/18
   *  @since	5.6.9
   *
   *  @param	type $var Description. Default.
   *  @return	type Description.
   */

  var refreshHelper = new acf.Model({
    priority: 90,
    actions: {
      new_field: 'refresh',
      show_field: 'refresh',
      hide_field: 'refresh',
      remove_field: 'refresh',
      unmount_field: 'refresh',
      remount_field: 'refresh'
    },
    refresh: function () {
      acf.refresh();
    }
  });

  /**
   * mountHelper
   *
   * Adds compatiblity for the 'unmount' and 'remount' actions added in 5.8.0
   *
   * @date	7/3/19
   * @since	5.7.14
   *
   * @param	void
   * @return	void
   */
  var mountHelper = new acf.Model({
    priority: 1,
    actions: {
      sortstart: 'onSortstart',
      sortstop: 'onSortstop'
    },
    onSortstart: function ($item) {
      acf.doAction('unmount', $item);
    },
    onSortstop: function ($item) {
      acf.doAction('remount', $item);
    }
  });

  /**
   *  sortableHelper
   *
   *  Adds compatibility for sorting a <tr> element
   *
   *  @date	6/3/18
   *  @since	5.6.9
   *
   *  @param	void
   *  @return	void
   */

  var sortableHelper = new acf.Model({
    actions: {
      sortstart: 'onSortstart'
    },
    onSortstart: function ($item, $placeholder) {
      // if $item is a tr, apply some css to the elements
      if ($item.is('tr')) {
        // replace $placeholder children with a single td
        // fixes "width calculation issues" due to conditional logic hiding some children
        $placeholder.html('<td style="padding:0;" colspan="' + $placeholder.children().length + '"></td>');

        // add helper class to remove absolute positioning
        $item.addClass('acf-sortable-tr-helper');

        // set fixed widths for children
        $item.children().each(function () {
          $(this).width($(this).width());
        });

        // mimic height
        $placeholder.height($item.height() + 'px');

        // remove class
        $item.removeClass('acf-sortable-tr-helper');
      }
    }
  });

  /**
   *  duplicateHelper
   *
   *  Fixes browser bugs when duplicating an element
   *
   *  @date	6/3/18
   *  @since	5.6.9
   *
   *  @param	void
   *  @return	void
   */

  var duplicateHelper = new acf.Model({
    actions: {
      after_duplicate: 'onAfterDuplicate'
    },
    onAfterDuplicate: function ($el, $el2) {
      // get original values
      var vals = [];
      $el.find('select').each(function (i) {
        vals.push($(this).val());
      });

      // set duplicate values
      $el2.find('select').each(function (i) {
        $(this).val(vals[i]);
      });
    }
  });

  /**
   *  tableHelper
   *
   *  description
   *
   *  @date	6/3/18
   *  @since	5.6.9
   *
   *  @param	type $var Description. Default.
   *  @return	type Description.
   */

  var tableHelper = new acf.Model({
    id: 'tableHelper',
    priority: 20,
    actions: {
      refresh: 'renderTables'
    },
    renderTables: function ($el) {
      // loop
      var self = this;
      $('.acf-table:visible').each(function () {
        self.renderTable($(this));
      });
    },
    renderTable: function ($table) {
      // vars
      var $ths = $table.find('> thead > tr:visible > th[data-key]');
      var $tds = $table.find('> tbody > tr:visible > td[data-key]');

      // bail early if no thead
      if (!$ths.length || !$tds.length) {
        return false;
      }

      // visiblity
      $ths.each(function (i) {
        // vars
        var $th = $(this);
        var key = $th.data('key');
        var $cells = $tds.filter('[data-key="' + key + '"]');
        var $hidden = $cells.filter('.acf-hidden');

        // always remove empty and allow cells to be hidden
        $cells.removeClass('acf-empty');

        // hide $th if all cells are hidden
        if ($cells.length === $hidden.length) {
          acf.hide($th);

          // force all hidden cells to appear empty
        } else {
          acf.show($th);
          $hidden.addClass('acf-empty');
        }
      });

      // clear width
      $ths.css('width', 'auto');

      // get visible
      $ths = $ths.not('.acf-hidden');

      // vars
      var availableWidth = 100;
      var colspan = $ths.length;

      // set custom widths first
      var $fixedWidths = $ths.filter('[data-width]');
      $fixedWidths.each(function () {
        var width = $(this).data('width');
        $(this).css('width', width + '%');
        availableWidth -= width;
      });

      // set auto widths
      var $auoWidths = $ths.not('[data-width]');
      if ($auoWidths.length) {
        var width = availableWidth / $auoWidths.length;
        $auoWidths.css('width', width + '%');
        availableWidth = 0;
      }

      // avoid stretching issue
      if (availableWidth > 0) {
        $ths.last().css('width', 'auto');
      }

      // update colspan on collapsed
      $tds.filter('.-collapsed-target').each(function () {
        // vars
        var $td = $(this);

        // check if collapsed
        if ($td.parent().hasClass('-collapsed')) {
          $td.attr('colspan', $ths.length);
        } else {
          $td.removeAttr('colspan');
        }
      });
    }
  });

  /**
   *  fieldsHelper
   *
   *  description
   *
   *  @date	6/3/18
   *  @since	5.6.9
   *
   *  @param	type $var Description. Default.
   *  @return	type Description.
   */

  var fieldsHelper = new acf.Model({
    id: 'fieldsHelper',
    priority: 30,
    actions: {
      refresh: 'renderGroups'
    },
    renderGroups: function () {
      // loop
      var self = this;
      $('.acf-fields:visible').each(function () {
        self.renderGroup($(this));
      });
    },
    renderGroup: function ($el) {
      // vars
      var top = 0;
      var height = 0;
      var $row = $();

      // get fields
      var $fields = $el.children('.acf-field[data-width]:visible');

      // bail early if no fields
      if (!$fields.length) {
        return false;
      }

      // bail early if is .-left
      if ($el.hasClass('-left')) {
        $fields.removeAttr('data-width');
        $fields.css('width', 'auto');
        return false;
      }

      // reset fields
      $fields.removeClass('-r0 -c0').css({
        'min-height': 0
      });

      // loop
      $fields.each(function (i) {
        // vars
        var $field = $(this);
        var position = $field.position();
        var thisTop = Math.ceil(position.top);
        var thisLeft = Math.ceil(position.left);

        // detect change in row
        if ($row.length && thisTop > top) {
          // set previous heights
          $row.css({
            'min-height': height + 'px'
          });

          // update position due to change in row above
          position = $field.position();
          thisTop = Math.ceil(position.top);
          thisLeft = Math.ceil(position.left);

          // reset vars
          top = 0;
          height = 0;
          $row = $();
        }

        // rtl
        if (acf.get('rtl')) {
          thisLeft = Math.ceil($field.parent().width() - (position.left + $field.outerWidth()));
        }

        // add classes
        if (thisTop == 0) {
          $field.addClass('-r0');
        } else if (thisLeft == 0) {
          $field.addClass('-c0');
        }

        // get height after class change
        // - add 1 for subpixel rendering
        var thisHeight = Math.ceil($field.outerHeight()) + 1;

        // set height
        height = Math.max(height, thisHeight);

        // set y
        top = Math.max(top, thisTop);

        // append
        $row = $row.add($field);
      });

      // clean up
      if ($row.length) {
        $row.css({
          'min-height': height + 'px'
        });
      }
    }
  });

  /**
   * Adds a body class when holding down the "shift" key.
   *
   * @date	06/05/2020
   * @since	5.9.0
   */
  var bodyClassShiftHelper = new acf.Model({
    id: 'bodyClassShiftHelper',
    events: {
      keydown: 'onKeyDown',
      keyup: 'onKeyUp'
    },
    isShiftKey: function (e) {
      return e.keyCode === 16;
    },
    onKeyDown: function (e) {
      if (this.isShiftKey(e)) {
        $('body').addClass('acf-keydown-shift');
      }
    },
    onKeyUp: function (e) {
      if (this.isShiftKey(e)) {
        $('body').removeClass('acf-keydown-shift');
      }
    }
  });
})(jQuery);

/***/ }),

/***/ "./src/advanced-custom-fields-pro/assets/src/js/_acf-media.js":
/*!********************************************************************!*\
  !*** ./src/advanced-custom-fields-pro/assets/src/js/_acf-media.js ***!
  \********************************************************************/
/***/ (() => {

(function ($, undefined) {
  /**
   *  acf.newMediaPopup
   *
   *  description
   *
   *  @date	10/1/18
   *  @since	5.6.5
   *
   *  @param	type $var Description. Default.
   *  @return	type Description.
   */

  acf.newMediaPopup = function (args) {
    // args
    var popup = null;
    var args = acf.parseArgs(args, {
      mode: 'select',
      // 'select', 'edit'
      title: '',
      // 'Upload Image'
      button: '',
      // 'Select Image'
      type: '',
      // 'image', ''
      field: false,
      // field instance
      allowedTypes: '',
      // '.jpg, .png, etc'
      library: 'all',
      // 'all', 'uploadedTo'
      multiple: false,
      // false, true, 'add'
      attachment: 0,
      // the attachment to edit
      autoOpen: true,
      // open the popup automatically
      open: function () {},
      // callback after close
      select: function () {},
      // callback after select
      close: function () {} // callback after close
    });

    // initialize
    if (args.mode == 'edit') {
      popup = new acf.models.EditMediaPopup(args);
    } else {
      popup = new acf.models.SelectMediaPopup(args);
    }

    // open popup (allow frame customization before opening)
    if (args.autoOpen) {
      setTimeout(function () {
        popup.open();
      }, 1);
    }

    // action
    acf.doAction('new_media_popup', popup);

    // return
    return popup;
  };

  /**
   *  getPostID
   *
   *  description
   *
   *  @date	10/1/18
   *  @since	5.6.5
   *
   *  @param	type $var Description. Default.
   *  @return	type Description.
   */

  var getPostID = function () {
    var postID = acf.get('post_id');
    return acf.isNumeric(postID) ? postID : 0;
  };

  /**
   *  acf.getMimeTypes
   *
   *  description
   *
   *  @date	11/1/18
   *  @since	5.6.5
   *
   *  @param	type $var Description. Default.
   *  @return	type Description.
   */

  acf.getMimeTypes = function () {
    return this.get('mimeTypes');
  };
  acf.getMimeType = function (name) {
    // vars
    var allTypes = acf.getMimeTypes();

    // search
    if (allTypes[name] !== undefined) {
      return allTypes[name];
    }

    // some types contain a mixed key such as "jpg|jpeg|jpe"
    for (var key in allTypes) {
      if (key.indexOf(name) !== -1) {
        return allTypes[key];
      }
    }

    // return
    return false;
  };

  /**
   *  MediaPopup
   *
   *  description
   *
   *  @date	10/1/18
   *  @since	5.6.5
   *
   *  @param	type $var Description. Default.
   *  @return	type Description.
   */

  var MediaPopup = acf.Model.extend({
    id: 'MediaPopup',
    data: {},
    defaults: {},
    frame: false,
    setup: function (props) {
      $.extend(this.data, props);
    },
    initialize: function () {
      // vars
      var options = this.getFrameOptions();

      // add states
      this.addFrameStates(options);

      // create frame
      var frame = wp.media(options);

      // add args reference
      frame.acf = this;

      // add events
      this.addFrameEvents(frame, options);

      // strore frame
      this.frame = frame;
    },
    open: function () {
      this.frame.open();
    },
    close: function () {
      this.frame.close();
    },
    remove: function () {
      this.frame.detach();
      this.frame.remove();
    },
    getFrameOptions: function () {
      // vars
      var options = {
        title: this.get('title'),
        multiple: this.get('multiple'),
        library: {},
        states: []
      };

      // type
      if (this.get('type')) {
        options.library.type = this.get('type');
      }

      // type
      if (this.get('library') === 'uploadedTo') {
        options.library.uploadedTo = getPostID();
      }

      // attachment
      if (this.get('attachment')) {
        options.library.post__in = [this.get('attachment')];
      }

      // button
      if (this.get('button')) {
        options.button = {
          text: this.get('button')
        };
      }

      // return
      return options;
    },
    addFrameStates: function (options) {
      // create query
      var Query = wp.media.query(options.library);

      // add _acfuploader
      // this is super wack!
      // if you add _acfuploader to the options.library args, new uploads will not be added to the library view.
      // this has been traced back to the wp.media.model.Query initialize function (which can't be overriden)
      // Adding any custom args will cause the Attahcments to not observe the uploader queue
      // To bypass this security issue, we add in the args AFTER the Query has been initialized
      // options.library._acfuploader = settings.field;
      if (this.get('field') && acf.isset(Query, 'mirroring', 'args')) {
        Query.mirroring.args._acfuploader = this.get('field');
      }

      // add states
      options.states.push(
      // main state
      new wp.media.controller.Library({
        library: Query,
        multiple: this.get('multiple'),
        title: this.get('title'),
        priority: 20,
        filterable: 'all',
        editable: true,
        allowLocalEdits: true
      }));

      // edit image functionality (added in WP 3.9)
      if (acf.isset(wp, 'media', 'controller', 'EditImage')) {
        options.states.push(new wp.media.controller.EditImage());
      }
    },
    addFrameEvents: function (frame, options) {
      // log all events
      //frame.on('all', function( e ) {
      //	console.log( 'frame all: %o', e );
      //});

      // add class
      frame.on('open', function () {
        this.$el.closest('.media-modal').addClass('acf-media-modal -' + this.acf.get('mode'));
      }, frame);

      // edit image view
      // source: media-views.js:2410 editImageContent()
      frame.on('content:render:edit-image', function () {
        var image = this.state().get('image');
        var view = new wp.media.view.EditImage({
          model: image,
          controller: this
        }).render();
        this.content.set(view);

        // after creating the wrapper view, load the actual editor via an ajax call
        view.loadEditor();
      }, frame);

      // update toolbar button
      //frame.on( 'toolbar:create:select', function( toolbar ) {
      //	toolbar.view = new wp.media.view.Toolbar.Select({
      //		text: frame.options._button,
      //		controller: this
      //	});
      //}, frame );

      // on select
      frame.on('select', function () {
        // vars
        var selection = frame.state().get('selection');

        // if selecting images
        if (selection) {
          // loop
          selection.each(function (attachment, i) {
            frame.acf.get('select').apply(frame.acf, [attachment, i]);
          });
        }
      });

      // on close
      frame.on('close', function () {
        // callback and remove
        setTimeout(function () {
          frame.acf.get('close').apply(frame.acf);
          frame.acf.remove();
        }, 1);
      });
    }
  });

  /**
   *  acf.models.SelectMediaPopup
   *
   *  description
   *
   *  @date	10/1/18
   *  @since	5.6.5
   *
   *  @param	type $var Description. Default.
   *  @return	type Description.
   */

  acf.models.SelectMediaPopup = MediaPopup.extend({
    id: 'SelectMediaPopup',
    setup: function (props) {
      // default button
      if (!props.button) {
        props.button = acf._x('Select', 'verb');
      }

      // parent
      MediaPopup.prototype.setup.apply(this, arguments);
    },
    addFrameEvents: function (frame, options) {
      // plupload
      // adds _acfuploader param to validate uploads
      if (acf.isset(_wpPluploadSettings, 'defaults', 'multipart_params')) {
        // add _acfuploader so that Uploader will inherit
        _wpPluploadSettings.defaults.multipart_params._acfuploader = this.get('field');

        // remove acf_field so future Uploaders won't inherit
        frame.on('open', function () {
          delete _wpPluploadSettings.defaults.multipart_params._acfuploader;
        });
      }

      // browse
      frame.on('content:activate:browse', function () {
        // vars
        var toolbar = false;

        // populate above vars making sure to allow for failure
        // perhaps toolbar does not exist because the frame open is Upload Files
        try {
          toolbar = frame.content.get().toolbar;
        } catch (e) {
          console.log(e);
          return;
        }

        // callback
        frame.acf.customizeFilters.apply(frame.acf, [toolbar]);
      });

      // parent
      MediaPopup.prototype.addFrameEvents.apply(this, arguments);
    },
    customizeFilters: function (toolbar) {
      // vars
      var filters = toolbar.get('filters');

      // image
      if (this.get('type') == 'image') {
        // update all
        filters.filters.all.text = acf.__('All images');

        // remove some filters
        delete filters.filters.audio;
        delete filters.filters.video;
        delete filters.filters.image;

        // update all filters to show images
        $.each(filters.filters, function (i, filter) {
          filter.props.type = filter.props.type || 'image';
        });
      }

      // specific types
      if (this.get('allowedTypes')) {
        // convert ".jpg, .png" into ["jpg", "png"]
        var allowedTypes = this.get('allowedTypes').split(' ').join('').split('.').join('').split(',');

        // loop
        allowedTypes.map(function (name) {
          // get type
          var mimeType = acf.getMimeType(name);

          // bail early if no type
          if (!mimeType) return;

          // create new filter
          var newFilter = {
            text: mimeType,
            props: {
              status: null,
              type: mimeType,
              uploadedTo: null,
              orderby: 'date',
              order: 'DESC'
            },
            priority: 20
          };

          // append
          filters.filters[mimeType] = newFilter;
        });
      }

      // uploaded to post
      if (this.get('library') === 'uploadedTo') {
        // vars
        var uploadedTo = this.frame.options.library.uploadedTo;

        // remove some filters
        delete filters.filters.unattached;
        delete filters.filters.uploaded;

        // add uploadedTo to filters
        $.each(filters.filters, function (i, filter) {
          filter.text += ' (' + acf.__('Uploaded to this post') + ')';
          filter.props.uploadedTo = uploadedTo;
        });
      }

      // add _acfuploader to filters
      var field = this.get('field');
      $.each(filters.filters, function (k, filter) {
        filter.props._acfuploader = field;
      });

      // add _acfuplaoder to search
      var search = toolbar.get('search');
      search.model.attributes._acfuploader = field;

      // render (custom function added to prototype)
      if (filters.renderFilters) {
        filters.renderFilters();
      }
    }
  });

  /**
   *  acf.models.EditMediaPopup
   *
   *  description
   *
   *  @date	10/1/18
   *  @since	5.6.5
   *
   *  @param	type $var Description. Default.
   *  @return	type Description.
   */

  acf.models.EditMediaPopup = MediaPopup.extend({
    id: 'SelectMediaPopup',
    setup: function (props) {
      // default button
      if (!props.button) {
        props.button = acf._x('Update', 'verb');
      }

      // parent
      MediaPopup.prototype.setup.apply(this, arguments);
    },
    addFrameEvents: function (frame, options) {
      // add class
      frame.on('open', function () {
        // add class
        this.$el.closest('.media-modal').addClass('acf-expanded');

        // set to browse
        if (this.content.mode() != 'browse') {
          this.content.mode('browse');
        }

        // set selection
        var state = this.state();
        var selection = state.get('selection');
        var attachment = wp.media.attachment(frame.acf.get('attachment'));
        selection.add(attachment);
      }, frame);

      // parent
      MediaPopup.prototype.addFrameEvents.apply(this, arguments);
    }
  });

  /**
   *  customizePrototypes
   *
   *  description
   *
   *  @date	11/1/18
   *  @since	5.6.5
   *
   *  @param	type $var Description. Default.
   *  @return	type Description.
   */

  var customizePrototypes = new acf.Model({
    id: 'customizePrototypes',
    wait: 'ready',
    initialize: function () {
      // bail early if no media views
      if (!acf.isset(window, 'wp', 'media', 'view')) {
        return;
      }

      // fix bug where CPT without "editor" does not set post.id setting which then prevents uploadedTo from working
      var postID = getPostID();
      if (postID && acf.isset(wp, 'media', 'view', 'settings', 'post')) {
        wp.media.view.settings.post.id = postID;
      }

      // customize
      this.customizeAttachmentsButton();
      this.customizeAttachmentsRouter();
      this.customizeAttachmentFilters();
      this.customizeAttachmentCompat();
      this.customizeAttachmentLibrary();
    },
    customizeAttachmentsButton: function () {
      // validate
      if (!acf.isset(wp, 'media', 'view', 'Button')) {
        return;
      }

      // Extend
      var Button = wp.media.view.Button;
      wp.media.view.Button = Button.extend({
        // Fix bug where "Select" button appears blank after editing an image.
        // Do this by simplifying Button initialize function and avoid deleting this.options.
        initialize: function () {
          var options = _.defaults(this.options, this.defaults);
          this.model = new Backbone.Model(options);
          this.listenTo(this.model, 'change', this.render);
        }
      });
    },
    customizeAttachmentsRouter: function () {
      // validate
      if (!acf.isset(wp, 'media', 'view', 'Router')) {
        return;
      }

      // vars
      var Parent = wp.media.view.Router;

      // extend
      wp.media.view.Router = Parent.extend({
        addExpand: function () {
          // vars
          var $a = $(['<a href="#" class="acf-expand-details">', '<span class="is-closed"><i class="acf-icon -left -small"></i>' + acf.__('Expand Details') + '</span>', '<span class="is-open"><i class="acf-icon -right -small"></i>' + acf.__('Collapse Details') + '</span>', '</a>'].join(''));

          // add events
          $a.on('click', function (e) {
            e.preventDefault();
            var $div = $(this).closest('.media-modal');
            if ($div.hasClass('acf-expanded')) {
              $div.removeClass('acf-expanded');
            } else {
              $div.addClass('acf-expanded');
            }
          });

          // append
          this.$el.append($a);
        },
        initialize: function () {
          // initialize
          Parent.prototype.initialize.apply(this, arguments);

          // add buttons
          this.addExpand();

          // return
          return this;
        }
      });
    },
    customizeAttachmentFilters: function () {
      // validate
      if (!acf.isset(wp, 'media', 'view', 'AttachmentFilters', 'All')) {
        return;
      }

      // vars
      var Parent = wp.media.view.AttachmentFilters.All;

      // renderFilters
      // copied from media-views.js:6939
      Parent.prototype.renderFilters = function () {
        // Build `<option>` elements.
        this.$el.html(_.chain(this.filters).map(function (filter, value) {
          return {
            el: $('<option></option>').val(value).html(filter.text)[0],
            priority: filter.priority || 50
          };
        }, this).sortBy('priority').pluck('el').value());
      };
    },
    customizeAttachmentCompat: function () {
      // validate
      if (!acf.isset(wp, 'media', 'view', 'AttachmentCompat')) {
        return;
      }

      // vars
      var AttachmentCompat = wp.media.view.AttachmentCompat;
      var timeout = false;

      // extend
      wp.media.view.AttachmentCompat = AttachmentCompat.extend({
        render: function () {
          // WP bug
          // When multiple media frames exist on the same page (WP content, WYSIWYG, image, file ),
          // WP creates multiple instances of this AttachmentCompat view.
          // Each instance will attempt to render when a new modal is created.
          // Use a property to avoid this and only render once per instance.
          if (this.rendered) {
            return this;
          }

          // render HTML
          AttachmentCompat.prototype.render.apply(this, arguments);

          // when uploading, render is called twice.
          // ignore first render by checking for #acf-form-data element
          if (!this.$('#acf-form-data').length) {
            return this;
          }

          // clear timeout
          clearTimeout(timeout);

          // setTimeout
          timeout = setTimeout($.proxy(function () {
            this.rendered = true;
            acf.doAction('append', this.$el);
          }, this), 50);

          // return
          return this;
        },
        save: function (event) {
          var data = {};
          if (event) {
            event.preventDefault();
          }

          //_.each( this.$el.serializeArray(), function( pair ) {
          //	data[ pair.name ] = pair.value;
          //});

          // Serialize data more thoroughly to allow chckbox inputs to save.
          data = acf.serializeForAjax(this.$el);
          this.controller.trigger('attachment:compat:waiting', ['waiting']);
          this.model.saveCompat(data).always(_.bind(this.postSave, this));
        }
      });
    },
    customizeAttachmentLibrary: function () {
      // validate
      if (!acf.isset(wp, 'media', 'view', 'Attachment', 'Library')) {
        return;
      }

      // vars
      var AttachmentLibrary = wp.media.view.Attachment.Library;

      // extend
      wp.media.view.Attachment.Library = AttachmentLibrary.extend({
        render: function () {
          // vars
          var popup = acf.isget(this, 'controller', 'acf');
          var attributes = acf.isget(this, 'model', 'attributes');

          // check vars exist to avoid errors
          if (popup && attributes) {
            // show errors
            if (attributes.acf_errors) {
              this.$el.addClass('acf-disabled');
            }

            // disable selected
            var selected = popup.get('selected');
            if (selected && selected.indexOf(attributes.id) > -1) {
              this.$el.addClass('acf-selected');
            }
          }

          // render
          return AttachmentLibrary.prototype.render.apply(this, arguments);
        },
        /*
         *  toggleSelection
         *
         *  This function is called before an attachment is selected
         *  A good place to check for errors and prevent the 'select' function from being fired
         *
         *  @type	function
         *  @date	29/09/2016
         *  @since	5.4.0
         *
         *  @param	options (object)
         *  @return	n/a
         */

        toggleSelection: function (options) {
          // vars
          // source: wp-includes/js/media-views.js:2880
          var collection = this.collection,
            selection = this.options.selection,
            model = this.model,
            single = selection.single();

          // vars
          var frame = this.controller;
          var errors = acf.isget(this, 'model', 'attributes', 'acf_errors');
          var $sidebar = frame.$el.find('.media-frame-content .media-sidebar');

          // remove previous error
          $sidebar.children('.acf-selection-error').remove();

          // show attachment details
          $sidebar.children().removeClass('acf-hidden');

          // add message
          if (frame && errors) {
            // vars
            var filename = acf.isget(this, 'model', 'attributes', 'filename');

            // hide attachment details
            // Gallery field continues to show previously selected attachment...
            $sidebar.children().addClass('acf-hidden');

            // append message
            $sidebar.prepend(['<div class="acf-selection-error">', '<span class="selection-error-label">' + acf.__('Restricted') + '</span>', '<span class="selection-error-filename">' + filename + '</span>', '<span class="selection-error-message">' + errors + '</span>', '</div>'].join(''));

            // reset selection (unselects all attachments)
            selection.reset();

            // set single (attachment displayed in sidebar)
            selection.single(model);

            // return and prevent 'select' form being fired
            return;
          }

          // return
          return AttachmentLibrary.prototype.toggleSelection.apply(this, arguments);
        }
      });
    }
  });
})(jQuery);

/***/ }),

/***/ "./src/advanced-custom-fields-pro/assets/src/js/_acf-postbox.js":
/*!**********************************************************************!*\
  !*** ./src/advanced-custom-fields-pro/assets/src/js/_acf-postbox.js ***!
  \**********************************************************************/
/***/ (() => {

(function ($, undefined) {
  /**
   * postboxManager
   *
   * Manages postboxes on the screen.
   *
   * @date	25/5/19
   * @since	5.8.1
   *
   * @param	void
   * @return	void
   */
  var postboxManager = new acf.Model({
    wait: 'prepare',
    priority: 1,
    initialize: function () {
      (acf.get('postboxes') || []).map(acf.newPostbox);
    }
  });

  /**
   *  acf.getPostbox
   *
   *  Returns a postbox instance.
   *
   *  @date	23/9/18
   *  @since	5.7.7
   *
   *  @param	mixed $el Either a jQuery element or the postbox id.
   *  @return	object
   */
  acf.getPostbox = function ($el) {
    // allow string parameter
    if (typeof arguments[0] == 'string') {
      $el = $('#' + arguments[0]);
    }

    // return instance
    return acf.getInstance($el);
  };

  /**
   *  acf.getPostboxes
   *
   *  Returns an array of postbox instances.
   *
   *  @date	23/9/18
   *  @since	5.7.7
   *
   *  @param	void
   *  @return	array
   */
  acf.getPostboxes = function () {
    return acf.getInstances($('.acf-postbox'));
  };

  /**
   *  acf.newPostbox
   *
   *  Returns a new postbox instance for the given props.
   *
   *  @date	20/9/18
   *  @since	5.7.6
   *
   *  @param	object props The postbox properties.
   *  @return	object
   */
  acf.newPostbox = function (props) {
    return new acf.models.Postbox(props);
  };

  /**
   *  acf.models.Postbox
   *
   *  The postbox model.
   *
   *  @date	20/9/18
   *  @since	5.7.6
   *
   *  @param	void
   *  @return	void
   */
  acf.models.Postbox = acf.Model.extend({
    data: {
      id: '',
      key: '',
      style: 'default',
      label: 'top',
      edit: ''
    },
    setup: function (props) {
      // compatibilty
      if (props.editLink) {
        props.edit = props.editLink;
      }

      // extend data
      $.extend(this.data, props);

      // set $el
      this.$el = this.$postbox();
    },
    $postbox: function () {
      return $('#' + this.get('id'));
    },
    $hide: function () {
      return $('#' + this.get('id') + '-hide');
    },
    $hideLabel: function () {
      return this.$hide().parent();
    },
    $hndle: function () {
      return this.$('> .hndle');
    },
    $handleActions: function () {
      return this.$('> .postbox-header .handle-actions');
    },
    $inside: function () {
      return this.$('> .inside');
    },
    isVisible: function () {
      return this.$el.hasClass('acf-hidden');
    },
    isHiddenByScreenOptions: function () {
      return this.$el.hasClass('hide-if-js') || this.$el.css('display') == 'none';
    },
    initialize: function () {
      // Add default class.
      this.$el.addClass('acf-postbox');

      // Add field group style class (ignore in block editor).
      if (acf.get('editor') !== 'block') {
        var style = this.get('style');
        if (style !== 'default') {
          this.$el.addClass(style);
        }
      }

      // Add .inside class.
      this.$inside().addClass('acf-fields').addClass('-' + this.get('label'));

      // Append edit link.
      var edit = this.get('edit');
      if (edit) {
        var html = '<a href="' + edit + '" class="dashicons dashicons-admin-generic acf-hndle-cog acf-js-tooltip" title="' + acf.__('Edit field group') + '"></a>';
        var $handleActions = this.$handleActions();
        if ($handleActions.length) {
          $handleActions.prepend(html);
        } else {
          this.$hndle().append(html);
        }
      }

      // Show postbox.
      this.show();
    },
    show: function () {
      // If disabled by screen options, set checked to false and return.
      if (this.$el.hasClass('hide-if-js')) {
        this.$hide().prop('checked', false);
        return;
      }

      // Show label.
      this.$hideLabel().show();

      // toggle on checkbox
      this.$hide().prop('checked', true);

      // Show postbox
      this.$el.show().removeClass('acf-hidden');

      // Do action.
      acf.doAction('show_postbox', this);
    },
    enable: function () {
      acf.enable(this.$el, 'postbox');
    },
    showEnable: function () {
      this.enable();
      this.show();
    },
    hide: function () {
      // Hide label.
      this.$hideLabel().hide();

      // Hide postbox
      this.$el.hide().addClass('acf-hidden');

      // Do action.
      acf.doAction('hide_postbox', this);
    },
    disable: function () {
      acf.disable(this.$el, 'postbox');
    },
    hideDisable: function () {
      this.disable();
      this.hide();
    },
    html: function (html) {
      // Update HTML.
      this.$inside().html(html);

      // Do action.
      acf.doAction('append', this.$el);
    }
  });
})(jQuery);

/***/ }),

/***/ "./src/advanced-custom-fields-pro/assets/src/js/_acf-screen.js":
/*!*********************************************************************!*\
  !*** ./src/advanced-custom-fields-pro/assets/src/js/_acf-screen.js ***!
  \*********************************************************************/
/***/ (() => {

(function ($, undefined) {
  acf.screen = new acf.Model({
    active: true,
    xhr: false,
    timeout: false,
    wait: 'load',
    events: {
      'change #page_template': 'onChange',
      'change #parent_id': 'onChange',
      'change #post-formats-select': 'onChange',
      'change .categorychecklist': 'onChange',
      'change .tagsdiv': 'onChange',
      'change .acf-taxonomy-field[data-save="1"]': 'onChange',
      'change #product-type': 'onChange'
    },
    isPost: function () {
      return acf.get('screen') === 'post';
    },
    isUser: function () {
      return acf.get('screen') === 'user';
    },
    isTaxonomy: function () {
      return acf.get('screen') === 'taxonomy';
    },
    isAttachment: function () {
      return acf.get('screen') === 'attachment';
    },
    isNavMenu: function () {
      return acf.get('screen') === 'nav_menu';
    },
    isWidget: function () {
      return acf.get('screen') === 'widget';
    },
    isComment: function () {
      return acf.get('screen') === 'comment';
    },
    getPageTemplate: function () {
      var $el = $('#page_template');
      return $el.length ? $el.val() : null;
    },
    getPageParent: function (e, $el) {
      var $el = $('#parent_id');
      return $el.length ? $el.val() : null;
    },
    getPageType: function (e, $el) {
      return this.getPageParent() ? 'child' : 'parent';
    },
    getPostType: function () {
      return $('#post_type').val();
    },
    getPostFormat: function (e, $el) {
      var $el = $('#post-formats-select input:checked');
      if ($el.length) {
        var val = $el.val();
        return val == '0' ? 'standard' : val;
      }
      return null;
    },
    getPostCoreTerms: function () {
      // vars
      var terms = {};

      // serialize WP taxonomy postboxes
      var data = acf.serialize($('.categorydiv, .tagsdiv'));

      // use tax_input (tag, custom-taxonomy) when possible.
      // this data is already formatted in taxonomy => [terms].
      if (data.tax_input) {
        terms = data.tax_input;
      }

      // append "category" which uses a different name
      if (data.post_category) {
        terms.category = data.post_category;
      }

      // convert any string values (tags) into array format
      for (var tax in terms) {
        if (!acf.isArray(terms[tax])) {
          terms[tax] = terms[tax].split(/,[\s]?/);
        }
      }

      // return
      return terms;
    },
    getPostTerms: function () {
      // Get core terms.
      var terms = this.getPostCoreTerms();

      // loop over taxonomy fields and add their values
      acf.getFields({
        type: 'taxonomy'
      }).map(function (field) {
        // ignore fields that don't save
        if (!field.get('save')) {
          return;
        }

        // vars
        var val = field.val();
        var tax = field.get('taxonomy');

        // check val
        if (val) {
          // ensure terms exists
          terms[tax] = terms[tax] || [];

          // ensure val is an array
          val = acf.isArray(val) ? val : [val];

          // append
          terms[tax] = terms[tax].concat(val);
        }
      });

      // add WC product type
      if ((productType = this.getProductType()) !== null) {
        terms.product_type = [productType];
      }

      // remove duplicate values
      for (var tax in terms) {
        terms[tax] = acf.uniqueArray(terms[tax]);
      }

      // return
      return terms;
    },
    getProductType: function () {
      var $el = $('#product-type');
      return $el.length ? $el.val() : null;
    },
    check: function () {
      // bail early if not for post
      if (acf.get('screen') !== 'post') {
        return;
      }

      // abort XHR if is already loading AJAX data
      if (this.xhr) {
        this.xhr.abort();
      }

      // vars
      var ajaxData = acf.parseArgs(this.data, {
        action: 'acf/ajax/check_screen',
        screen: acf.get('screen'),
        exists: []
      });

      // post id
      if (this.isPost()) {
        ajaxData.post_id = acf.get('post_id');
      }

      // post type
      if ((postType = this.getPostType()) !== null) {
        ajaxData.post_type = postType;
      }

      // page template
      if ((pageTemplate = this.getPageTemplate()) !== null) {
        ajaxData.page_template = pageTemplate;
      }

      // page parent
      if ((pageParent = this.getPageParent()) !== null) {
        ajaxData.page_parent = pageParent;
      }

      // page type
      if ((pageType = this.getPageType()) !== null) {
        ajaxData.page_type = pageType;
      }

      // post format
      if ((postFormat = this.getPostFormat()) !== null) {
        ajaxData.post_format = postFormat;
      }

      // post terms
      if ((postTerms = this.getPostTerms()) !== null) {
        ajaxData.post_terms = postTerms;
      }

      // add array of existing postboxes to increase performance and reduce JSON HTML
      acf.getPostboxes().map(function (postbox) {
        ajaxData.exists.push(postbox.get('key'));
      });

      // filter
      ajaxData = acf.applyFilters('check_screen_args', ajaxData);

      // success
      var onSuccess = function (json) {
        // Render post screen.
        if (acf.get('screen') == 'post') {
          this.renderPostScreen(json);

          // Render user screen.
        } else if (acf.get('screen') == 'user') {
          this.renderUserScreen(json);
        }

        // action
        acf.doAction('check_screen_complete', json, ajaxData);
      };

      // ajax
      this.xhr = $.ajax({
        url: acf.get('ajaxurl'),
        data: acf.prepareForAjax(ajaxData),
        type: 'post',
        dataType: 'json',
        context: this,
        success: onSuccess
      });
    },
    onChange: function (e, $el) {
      this.setTimeout(this.check, 1);
    },
    renderPostScreen: function (data) {
      // Helper function to copy events
      var copyEvents = function ($from, $to) {
        var events = $._data($from[0]).events;
        for (var type in events) {
          for (var i = 0; i < events[type].length; i++) {
            $to.on(type, events[type][i].handler);
          }
        }
      };

      // Helper function to sort metabox.
      var sortMetabox = function (id, ids) {
        // Find position of id within ids.
        var index = ids.indexOf(id);

        // Bail early if index not found.
        if (index == -1) {
          return false;
        }

        // Loop over metaboxes behind (in reverse order).
        for (var i = index - 1; i >= 0; i--) {
          if ($('#' + ids[i]).length) {
            return $('#' + ids[i]).after($('#' + id));
          }
        }

        // Loop over metaboxes infront.
        for (var i = index + 1; i < ids.length; i++) {
          if ($('#' + ids[i]).length) {
            return $('#' + ids[i]).before($('#' + id));
          }
        }

        // Return false if not sorted.
        return false;
      };

      // Keep track of visible and hidden postboxes.
      data.visible = [];
      data.hidden = [];

      // Show these postboxes.
      data.results = data.results.map(function (result, i) {
        // vars
        var postbox = acf.getPostbox(result.id);

        // Prevent "acf_after_title" position in Block Editor.
        if (acf.isGutenberg() && result.position == 'acf_after_title') {
          result.position = 'normal';
        }

        // Create postbox if doesn't exist.
        if (!postbox) {
          var wpMinorVersion = parseFloat(acf.get('wp_version'));
          if (wpMinorVersion >= 5.5) {
            var postboxHeader = ['<div class="postbox-header">', '<h2 class="hndle ui-sortable-handle">', '<span>' + acf.escHtml(result.title) + '</span>', '</h2>', '<div class="handle-actions hide-if-no-js">', '<button type="button" class="handlediv" aria-expanded="true">', '<span class="screen-reader-text">Toggle panel: ' + acf.escHtml(result.title) + '</span>', '<span class="toggle-indicator" aria-hidden="true"></span>', '</button>', '</div>', '</div>'].join('');
          } else {
            var postboxHeader = ['<button type="button" class="handlediv" aria-expanded="true">', '<span class="screen-reader-text">Toggle panel: ' + acf.escHtml(result.title) + '</span>', '<span class="toggle-indicator" aria-hidden="true"></span>', '</button>', '<h2 class="hndle ui-sortable-handle">', '<span>' + acf.escHtml(result.title) + '</span>', '</h2>'].join('');
          }

          // Ensure result.classes is set.
          if (!result.classes) result.classes = '';

          // Create it.
          var $postbox = $(['<div id="' + result.id + '" class="postbox ' + result.classes + '">', postboxHeader, '<div class="inside">', result.html, '</div>', '</div>'].join(''));

          // Create new hide toggle.
          if ($('#adv-settings').length) {
            var $prefs = $('#adv-settings .metabox-prefs');
            var $label = $(['<label for="' + result.id + '-hide">', '<input class="hide-postbox-tog" name="' + result.id + '-hide" type="checkbox" id="' + result.id + '-hide" value="' + result.id + '" checked="checked">', ' ' + result.title, '</label>'].join(''));

            // Copy default WP events onto checkbox.
            copyEvents($prefs.find('input').first(), $label.find('input'));

            // Append hide label
            $prefs.append($label);
          }

          // Copy default WP events onto metabox.
          if ($('.postbox').length) {
            copyEvents($('.postbox .handlediv').first(), $postbox.children('.handlediv'));
            copyEvents($('.postbox .hndle').first(), $postbox.children('.hndle'));
          }

          // Append metabox to the bottom of "side-sortables".
          if (result.position === 'side') {
            $('#' + result.position + '-sortables').append($postbox);

            // Prepend metabox to the top of "normal-sortbables".
          } else {
            $('#' + result.position + '-sortables').prepend($postbox);
          }

          // Position metabox amongst existing ACF metaboxes within the same location.
          var order = [];
          data.results.map(function (_result) {
            if (result.position === _result.position && $('#' + result.position + '-sortables #' + _result.id).length) {
              order.push(_result.id);
            }
          });
          sortMetabox(result.id, order);

          // Check 'sorted' for user preference.
          if (data.sorted) {
            // Loop over each position (acf_after_title, side, normal).
            for (var position in data.sorted) {
              let order = data.sorted[position];
              if (typeof order !== 'string') {
                continue;
              }

              // Explode string into array of ids.
              order = order.split(',');

              // Position metabox relative to order.
              if (sortMetabox(result.id, order)) {
                break;
              }
            }
          }

          // Initalize it (modifies HTML).
          postbox = acf.newPostbox(result);

          // Trigger action.
          acf.doAction('append', $postbox);
          acf.doAction('append_postbox', postbox);
        }

        // show postbox
        postbox.showEnable();

        // append
        data.visible.push(result.id);

        // Return result (may have changed).
        return result;
      });

      // Hide these postboxes.
      acf.getPostboxes().map(function (postbox) {
        if (data.visible.indexOf(postbox.get('id')) === -1) {
          // Hide postbox.
          postbox.hideDisable();

          // Append to data.
          data.hidden.push(postbox.get('id'));
        }
      });

      // Update style.
      $('#acf-style').html(data.style);

      // Do action.
      acf.doAction('refresh_post_screen', data);
    },
    renderUserScreen: function (json) {}
  });

  /**
   *  gutenScreen
   *
   *  Adds compatibility with the Gutenberg edit screen.
   *
   *  @date	11/12/18
   *  @since	5.8.0
   *
   *  @param	void
   *  @return	void
   */
  var gutenScreen = new acf.Model({
    // Keep a reference to the most recent post attributes.
    postEdits: {},
    // Wait until assets have been loaded.
    wait: 'prepare',
    initialize: function () {
      // Bail early if not Gutenberg.
      if (!acf.isGutenbergPostEditor()) {
        return;
      }

      // Listen for changes (use debounced version as this can fires often).
      wp.data.subscribe(acf.debounce(this.onChange).bind(this));

      // Customize "acf.screen.get" functions.
      acf.screen.getPageTemplate = this.getPageTemplate;
      acf.screen.getPageParent = this.getPageParent;
      acf.screen.getPostType = this.getPostType;
      acf.screen.getPostFormat = this.getPostFormat;
      acf.screen.getPostCoreTerms = this.getPostCoreTerms;

      // Disable unload
      acf.unload.disable();

      // Refresh metaboxes since WP 5.3.
      var wpMinorVersion = parseFloat(acf.get('wp_version'));
      if (wpMinorVersion >= 5.3) {
        this.addAction('refresh_post_screen', this.onRefreshPostScreen);
      }

      // Trigger "refresh" after WP has moved metaboxes into place.
      wp.domReady(acf.refresh);
    },
    onChange: function () {
      // Determine attributes that can trigger a refresh.
      var attributes = ['template', 'parent', 'format'];

      // Append taxonomy attribute names to this list.
      (wp.data.select('core').getTaxonomies() || []).map(function (taxonomy) {
        attributes.push(taxonomy.rest_base);
      });

      // Get relevant current post edits.
      var _postEdits = wp.data.select('core/editor').getPostEdits();
      var postEdits = {};
      attributes.map(function (k) {
        if (_postEdits[k] !== undefined) {
          postEdits[k] = _postEdits[k];
        }
      });

      // Detect change.
      if (JSON.stringify(postEdits) !== JSON.stringify(this.postEdits)) {
        this.postEdits = postEdits;

        // Check screen.
        acf.screen.check();
      }
    },
    getPageTemplate: function () {
      return wp.data.select('core/editor').getEditedPostAttribute('template');
    },
    getPageParent: function (e, $el) {
      return wp.data.select('core/editor').getEditedPostAttribute('parent');
    },
    getPostType: function () {
      return wp.data.select('core/editor').getEditedPostAttribute('type');
    },
    getPostFormat: function (e, $el) {
      return wp.data.select('core/editor').getEditedPostAttribute('format');
    },
    getPostCoreTerms: function () {
      // vars
      var terms = {};

      // Loop over taxonomies.
      var taxonomies = wp.data.select('core').getTaxonomies() || [];
      taxonomies.map(function (taxonomy) {
        // Append selected taxonomies to terms object.
        var postTerms = wp.data.select('core/editor').getEditedPostAttribute(taxonomy.rest_base);
        if (postTerms) {
          terms[taxonomy.slug] = postTerms;
        }
      });

      // return
      return terms;
    },
    /**
     * onRefreshPostScreen
     *
     * Fires after the Post edit screen metaboxs are refreshed to update the Block Editor API state.
     *
     * @date	11/11/19
     * @since	5.8.7
     *
     * @param	object data The "check_screen" JSON response data.
     * @return	void
     */
    onRefreshPostScreen: function (data) {
      // Extract vars.
      var select = wp.data.select('core/edit-post');
      var dispatch = wp.data.dispatch('core/edit-post');

      // Load current metabox locations and data.
      var locations = {};
      select.getActiveMetaBoxLocations().map(function (location) {
        locations[location] = select.getMetaBoxesPerLocation(location);
      });

      // Generate flat array of existing ids.
      var ids = [];
      for (var k in locations) {
        locations[k].map(function (m) {
          ids.push(m.id);
        });
      }

      // Append new ACF metaboxes (ignore those which already exist).
      data.results.filter(function (r) {
        return ids.indexOf(r.id) === -1;
      }).map(function (result, i) {
        // Ensure location exists.
        var location = result.position;
        locations[location] = locations[location] || [];

        // Append.
        locations[location].push({
          id: result.id,
          title: result.title
        });
      });

      // Remove hidden ACF metaboxes.
      for (var k in locations) {
        locations[k] = locations[k].filter(function (m) {
          return data.hidden.indexOf(m.id) === -1;
        });
      }

      // Update state.
      dispatch.setAvailableMetaBoxesPerLocation(locations);
    }
  });
})(jQuery);

/***/ }),

/***/ "./src/advanced-custom-fields-pro/assets/src/js/_acf-select2.js":
/*!**********************************************************************!*\
  !*** ./src/advanced-custom-fields-pro/assets/src/js/_acf-select2.js ***!
  \**********************************************************************/
/***/ (() => {

(function ($, undefined) {
  /**
   *  acf.newSelect2
   *
   *  description
   *
   *  @date	13/1/18
   *  @since	5.6.5
   *
   *  @param	type $var Description. Default.
   *  @return	type Description.
   */

  acf.newSelect2 = function ($select, props) {
    // defaults
    props = acf.parseArgs(props, {
      allowNull: false,
      placeholder: '',
      multiple: false,
      field: false,
      ajax: false,
      ajaxAction: '',
      ajaxData: function (data) {
        return data;
      },
      ajaxResults: function (json) {
        return json;
      },
      templateSelection: false,
      templateResult: false,
      dropdownCssClass: '',
      suppressFilters: false
    });

    // initialize
    if (getVersion() == 4) {
      var select2 = new Select2_4($select, props);
    } else {
      var select2 = new Select2_3($select, props);
    }

    // actions
    acf.doAction('new_select2', select2);

    // return
    return select2;
  };

  /**
   *  getVersion
   *
   *  description
   *
   *  @date	13/1/18
   *  @since	5.6.5
   *
   *  @param	type $var Description. Default.
   *  @return	type Description.
   */

  function getVersion() {
    // v4
    if (acf.isset(window, 'jQuery', 'fn', 'select2', 'amd')) {
      return 4;
    }

    // v3
    if (acf.isset(window, 'Select2')) {
      return 3;
    }

    // return
    return false;
  }

  /**
   *  Select2
   *
   *  description
   *
   *  @date	13/1/18
   *  @since	5.6.5
   *
   *  @param	type $var Description. Default.
   *  @return	type Description.
   */

  var Select2 = acf.Model.extend({
    setup: function ($select, props) {
      $.extend(this.data, props);
      this.$el = $select;
    },
    initialize: function () {},
    selectOption: function (value) {
      var $option = this.getOption(value);
      if (!$option.prop('selected')) {
        $option.prop('selected', true).trigger('change');
      }
    },
    unselectOption: function (value) {
      var $option = this.getOption(value);
      if ($option.prop('selected')) {
        $option.prop('selected', false).trigger('change');
      }
    },
    getOption: function (value) {
      return this.$('option[value="' + value + '"]');
    },
    addOption: function (option) {
      // defaults
      option = acf.parseArgs(option, {
        id: '',
        text: '',
        selected: false
      });

      // vars
      var $option = this.getOption(option.id);

      // append
      if (!$option.length) {
        $option = $('<option></option>');
        $option.html(option.text);
        $option.attr('value', option.id);
        $option.prop('selected', option.selected);
        this.$el.append($option);
      }

      // chain
      return $option;
    },
    getValue: function () {
      // vars
      var val = [];
      var $options = this.$el.find('option:selected');

      // bail early if no selected
      if (!$options.exists()) {
        return val;
      }

      // sort by attribute
      $options = $options.sort(function (a, b) {
        return +a.getAttribute('data-i') - +b.getAttribute('data-i');
      });

      // loop
      $options.each(function () {
        var $el = $(this);
        val.push({
          $el: $el,
          id: $el.attr('value'),
          text: $el.text()
        });
      });

      // return
      return val;
    },
    mergeOptions: function () {},
    getChoices: function () {
      // callback
      var crawl = function ($parent) {
        // vars
        var choices = [];

        // loop
        $parent.children().each(function () {
          // vars
          var $child = $(this);

          // optgroup
          if ($child.is('optgroup')) {
            choices.push({
              text: $child.attr('label'),
              children: crawl($child)
            });

            // option
          } else {
            choices.push({
              id: $child.attr('value'),
              text: $child.text()
            });
          }
        });

        // return
        return choices;
      };

      // crawl
      return crawl(this.$el);
    },
    getAjaxData: function (params) {
      // vars
      var ajaxData = {
        action: this.get('ajaxAction'),
        s: params.term || '',
        paged: params.page || 1
      };

      // field helper
      var field = this.get('field');
      if (field) {
        ajaxData.field_key = field.get('key');
      }

      // callback
      var callback = this.get('ajaxData');
      if (callback) {
        ajaxData = callback.apply(this, [ajaxData, params]);
      }

      // filter
      ajaxData = acf.applyFilters('select2_ajax_data', ajaxData, this.data, this.$el, field || false, this);

      // return
      return acf.prepareForAjax(ajaxData);
    },
    getAjaxResults: function (json, params) {
      // defaults
      json = acf.parseArgs(json, {
        results: false,
        more: false
      });

      // callback
      var callback = this.get('ajaxResults');
      if (callback) {
        json = callback.apply(this, [json, params]);
      }

      // filter
      json = acf.applyFilters('select2_ajax_results', json, params, this);

      // return
      return json;
    },
    processAjaxResults: function (json, params) {
      // vars
      var json = this.getAjaxResults(json, params);

      // change more to pagination
      if (json.more) {
        json.pagination = {
          more: true
        };
      }

      // merge together groups
      setTimeout($.proxy(this.mergeOptions, this), 1);

      // return
      return json;
    },
    destroy: function () {
      // destroy via api
      if (this.$el.data('select2')) {
        this.$el.select2('destroy');
      }

      // destory via HTML (duplicating HTML does not contain data)
      this.$el.siblings('.select2-container').remove();
    }
  });

  /**
   *  Select2_4
   *
   *  description
   *
   *  @date	13/1/18
   *  @since	5.6.5
   *
   *  @param	type $var Description. Default.
   *  @return	type Description.
   */

  var Select2_4 = Select2.extend({
    initialize: function () {
      // vars
      var $select = this.$el;
      var options = {
        width: '100%',
        allowClear: this.get('allowNull'),
        placeholder: this.get('placeholder'),
        multiple: this.get('multiple'),
        templateSelection: this.get('templateSelection'),
        templateResult: this.get('templateResult'),
        dropdownCssClass: this.get('dropdownCssClass'),
        suppressFilters: this.get('suppressFilters'),
        data: [],
        escapeMarkup: function (markup) {
          if (typeof markup !== 'string') {
            return markup;
          }
          return acf.escHtml(markup);
        }
      };

      // Clear empty templateSelections, templateResults, or dropdownCssClass.
      if (!options.templateSelection) {
        delete options.templateSelection;
      }
      if (!options.templateResult) {
        delete options.templateResult;
      }
      if (!options.dropdownCssClass) {
        delete options.dropdownCssClass;
      }

      // Only use the template if SelectWoo is not loaded to work around https://github.com/woocommerce/woocommerce/pull/30473
      if (!acf.isset(window, 'jQuery', 'fn', 'selectWoo')) {
        if (!options.templateSelection) {
          options.templateSelection = function (selection) {
            var $selection = $('<span class="acf-selection"></span>');
            $selection.html(acf.escHtml(selection.text));
            $selection.data('element', selection.element);
            return $selection;
          };
        }
      } else {
        delete options.templateSelection;
        delete options.templateResult;
      }

      // multiple
      if (options.multiple) {
        // reorder options
        this.getValue().map(function (item) {
          item.$el.detach().appendTo($select);
        });
      }

      // Temporarily remove conflicting attribute.
      var attrAjax = $select.attr('data-ajax');
      if (attrAjax !== undefined) {
        $select.removeData('ajax');
        $select.removeAttr('data-ajax');
      }

      // ajax
      if (this.get('ajax')) {
        options.ajax = {
          url: acf.get('ajaxurl'),
          delay: 250,
          dataType: 'json',
          type: 'post',
          cache: false,
          data: $.proxy(this.getAjaxData, this),
          processResults: $.proxy(this.processAjaxResults, this)
        };
      }

      // filter for 3rd party customization
      if (!options.suppressFilters) {
        var field = this.get('field');
        options = acf.applyFilters('select2_args', options, $select, this.data, field || false, this);
      }

      // add select2
      $select.select2(options);

      // get container (Select2 v4 does not return this from constructor)
      var $container = $select.next('.select2-container');

      // multiple
      if (options.multiple) {
        // vars
        var $ul = $container.find('ul');

        // sortable
        $ul.sortable({
          stop: function (e) {
            // loop
            $ul.find('.select2-selection__choice').each(function () {
              // Attempt to use .data if it exists (select2 version < 4.0.6) or use our template data instead.
              if ($(this).data('data')) {
                var $option = $($(this).data('data').element);
              } else {
                var $option = $($(this).find('span.acf-selection').data('element'));
              }

              // detach and re-append to end
              $option.detach().appendTo($select);
            });

            // trigger change on input (JS error if trigger on select)
            $select.trigger('change');
          }
        });

        // on select, move to end
        $select.on('select2:select', this.proxy(function (e) {
          this.getOption(e.params.data.id).detach().appendTo(this.$el);
        }));
      }

      // add handler to auto-focus searchbox (for jQuery 3.6)
      $select.on('select2:open', () => {
        $('.select2-container--open .select2-search__field').get(-1).focus();
      });

      // add class
      $container.addClass('-acf');

      // Add back temporarily removed attr.
      if (attrAjax !== undefined) {
        $select.attr('data-ajax', attrAjax);
      }

      // action for 3rd party customization
      if (!options.suppressFilters) {
        acf.doAction('select2_init', $select, options, this.data, field || false, this);
      }
    },
    mergeOptions: function () {
      // vars
      var $prevOptions = false;
      var $prevGroup = false;

      // loop
      $('.select2-results__option[role="group"]').each(function () {
        // vars
        var $options = $(this).children('ul');
        var $group = $(this).children('strong');

        // compare to previous
        if ($prevGroup && $prevGroup.text() === $group.text()) {
          $prevOptions.append($options.children());
          $(this).remove();
          return;
        }

        // update vars
        $prevOptions = $options;
        $prevGroup = $group;
      });
    }
  });

  /**
   *  Select2_3
   *
   *  description
   *
   *  @date	13/1/18
   *  @since	5.6.5
   *
   *  @param	type $var Description. Default.
   *  @return	type Description.
   */

  var Select2_3 = Select2.extend({
    initialize: function () {
      // vars
      var $select = this.$el;
      var value = this.getValue();
      var multiple = this.get('multiple');
      var options = {
        width: '100%',
        allowClear: this.get('allowNull'),
        placeholder: this.get('placeholder'),
        separator: '||',
        multiple: this.get('multiple'),
        data: this.getChoices(),
        escapeMarkup: function (string) {
          return acf.escHtml(string);
        },
        dropdownCss: {
          'z-index': '999999999'
        },
        initSelection: function (element, callback) {
          if (multiple) {
            callback(value);
          } else {
            callback(value.shift());
          }
        }
      };

      // get hidden input
      var $input = $select.siblings('input');
      if (!$input.length) {
        $input = $('<input type="hidden" />');
        $select.before($input);
      }

      // set input value
      inputValue = value.map(function (item) {
        return item.id;
      }).join('||');
      $input.val(inputValue);

      // multiple
      if (options.multiple) {
        // reorder options
        value.map(function (item) {
          item.$el.detach().appendTo($select);
        });
      }

      // remove blank option as we have a clear all button
      if (options.allowClear) {
        options.data = options.data.filter(function (item) {
          return item.id !== '';
        });
      }

      // remove conflicting atts
      $select.removeData('ajax');
      $select.removeAttr('data-ajax');

      // ajax
      if (this.get('ajax')) {
        options.ajax = {
          url: acf.get('ajaxurl'),
          quietMillis: 250,
          dataType: 'json',
          type: 'post',
          cache: false,
          data: $.proxy(this.getAjaxData, this),
          results: $.proxy(this.processAjaxResults, this)
        };
      }

      // filter for 3rd party customization
      var field = this.get('field');
      options = acf.applyFilters('select2_args', options, $select, this.data, field || false, this);

      // add select2
      $input.select2(options);

      // get container
      var $container = $input.select2('container');

      // helper to find this select's option
      var getOption = $.proxy(this.getOption, this);

      // multiple
      if (options.multiple) {
        // vars
        var $ul = $container.find('ul');

        // sortable
        $ul.sortable({
          stop: function () {
            // loop
            $ul.find('.select2-search-choice').each(function () {
              // vars
              var data = $(this).data('select2Data');
              var $option = getOption(data.id);

              // detach and re-append to end
              $option.detach().appendTo($select);
            });

            // trigger change on input (JS error if trigger on select)
            $select.trigger('change');
          }
        });
      }

      // on select, create option and move to end
      $input.on('select2-selecting', function (e) {
        // vars
        var item = e.choice;
        var $option = getOption(item.id);

        // create if doesn't exist
        if (!$option.length) {
          $option = $('<option value="' + item.id + '">' + item.text + '</option>');
        }

        // detach and re-append to end
        $option.detach().appendTo($select);
      });

      // add class
      $container.addClass('-acf');

      // action for 3rd party customization
      acf.doAction('select2_init', $select, options, this.data, field || false, this);

      // change
      $input.on('change', function () {
        var val = $input.val();
        if (val.indexOf('||')) {
          val = val.split('||');
        }
        $select.val(val).trigger('change');
      });

      // hide select
      $select.hide();
    },
    mergeOptions: function () {
      // vars
      var $prevOptions = false;
      var $prevGroup = false;

      // loop
      $('#select2-drop .select2-result-with-children').each(function () {
        // vars
        var $options = $(this).children('ul');
        var $group = $(this).children('.select2-result-label');

        // compare to previous
        if ($prevGroup && $prevGroup.text() === $group.text()) {
          $prevGroup.append($options.children());
          $(this).remove();
          return;
        }

        // update vars
        $prevOptions = $options;
        $prevGroup = $group;
      });
    },
    getAjaxData: function (term, page) {
      // create Select2 v4 params
      var params = {
        term: term,
        page: page
      };

      // filter
      var field = this.get('field');
      params = acf.applyFilters('select2_ajax_data', params, this.data, this.$el, field || false, this);

      // return
      return Select2.prototype.getAjaxData.apply(this, [params]);
    }
  });

  // manager
  var select2Manager = new acf.Model({
    priority: 5,
    wait: 'prepare',
    actions: {
      duplicate: 'onDuplicate'
    },
    initialize: function () {
      // vars
      var locale = acf.get('locale');
      var rtl = acf.get('rtl');
      var l10n = acf.get('select2L10n');
      var version = getVersion();

      // bail early if no l10n
      if (!l10n) {
        return false;
      }

      // bail early if 'en'
      if (locale.indexOf('en') === 0) {
        return false;
      }

      // initialize
      if (version == 4) {
        this.addTranslations4();
      } else if (version == 3) {
        this.addTranslations3();
      }
    },
    addTranslations4: function () {
      // vars
      var l10n = acf.get('select2L10n');
      var locale = acf.get('locale');

      // modify local to match html[lang] attribute (used by Select2)
      locale = locale.replace('_', '-');

      // select2L10n
      var select2L10n = {
        errorLoading: function () {
          return l10n.load_fail;
        },
        inputTooLong: function (args) {
          var overChars = args.input.length - args.maximum;
          if (overChars > 1) {
            return l10n.input_too_long_n.replace('%d', overChars);
          }
          return l10n.input_too_long_1;
        },
        inputTooShort: function (args) {
          var remainingChars = args.minimum - args.input.length;
          if (remainingChars > 1) {
            return l10n.input_too_short_n.replace('%d', remainingChars);
          }
          return l10n.input_too_short_1;
        },
        loadingMore: function () {
          return l10n.load_more;
        },
        maximumSelected: function (args) {
          var maximum = args.maximum;
          if (maximum > 1) {
            return l10n.selection_too_long_n.replace('%d', maximum);
          }
          return l10n.selection_too_long_1;
        },
        noResults: function () {
          return l10n.matches_0;
        },
        searching: function () {
          return l10n.searching;
        }
      };

      // append
      jQuery.fn.select2.amd.define('select2/i18n/' + locale, [], function () {
        return select2L10n;
      });
    },
    addTranslations3: function () {
      // vars
      var l10n = acf.get('select2L10n');
      var locale = acf.get('locale');

      // modify local to match html[lang] attribute (used by Select2)
      locale = locale.replace('_', '-');

      // select2L10n
      var select2L10n = {
        formatMatches: function (matches) {
          if (matches > 1) {
            return l10n.matches_n.replace('%d', matches);
          }
          return l10n.matches_1;
        },
        formatNoMatches: function () {
          return l10n.matches_0;
        },
        formatAjaxError: function () {
          return l10n.load_fail;
        },
        formatInputTooShort: function (input, min) {
          var remainingChars = min - input.length;
          if (remainingChars > 1) {
            return l10n.input_too_short_n.replace('%d', remainingChars);
          }
          return l10n.input_too_short_1;
        },
        formatInputTooLong: function (input, max) {
          var overChars = input.length - max;
          if (overChars > 1) {
            return l10n.input_too_long_n.replace('%d', overChars);
          }
          return l10n.input_too_long_1;
        },
        formatSelectionTooBig: function (maximum) {
          if (maximum > 1) {
            return l10n.selection_too_long_n.replace('%d', maximum);
          }
          return l10n.selection_too_long_1;
        },
        formatLoadMore: function () {
          return l10n.load_more;
        },
        formatSearching: function () {
          return l10n.searching;
        }
      };

      // ensure locales exists
      $.fn.select2.locales = $.fn.select2.locales || {};

      // append
      $.fn.select2.locales[locale] = select2L10n;
      $.extend($.fn.select2.defaults, select2L10n);
    },
    onDuplicate: function ($el, $el2) {
      $el2.find('.select2-container').remove();
    }
  });
})(jQuery);

/***/ }),

/***/ "./src/advanced-custom-fields-pro/assets/src/js/_acf-tinymce.js":
/*!**********************************************************************!*\
  !*** ./src/advanced-custom-fields-pro/assets/src/js/_acf-tinymce.js ***!
  \**********************************************************************/
/***/ (() => {

(function ($, undefined) {
  acf.tinymce = {
    /*
     *  defaults
     *
     *  This function will return default mce and qt settings
     *
     *  @type	function
     *  @date	18/8/17
     *  @since	5.6.0
     *
     *  @param	$post_id (int)
     *  @return	$post_id (int)
     */

    defaults: function () {
      // bail early if no tinyMCEPreInit
      if (typeof tinyMCEPreInit === 'undefined') return false;

      // vars
      var defaults = {
        tinymce: tinyMCEPreInit.mceInit.acf_content,
        quicktags: tinyMCEPreInit.qtInit.acf_content
      };

      // return
      return defaults;
    },
    /*
     *  initialize
     *
     *  This function will initialize the tinymce and quicktags instances
     *
     *  @type	function
     *  @date	18/8/17
     *  @since	5.6.0
     *
     *  @param	$post_id (int)
     *  @return	$post_id (int)
     */

    initialize: function (id, args) {
      // defaults
      args = acf.parseArgs(args, {
        tinymce: true,
        quicktags: true,
        toolbar: 'full',
        mode: 'visual',
        // visual,text
        field: false
      });

      // tinymce
      if (args.tinymce) {
        this.initializeTinymce(id, args);
      }

      // quicktags
      if (args.quicktags) {
        this.initializeQuicktags(id, args);
      }
    },
    /*
     *  initializeTinymce
     *
     *  This function will initialize the tinymce instance
     *
     *  @type	function
     *  @date	18/8/17
     *  @since	5.6.0
     *
     *  @param	$post_id (int)
     *  @return	$post_id (int)
     */

    initializeTinymce: function (id, args) {
      // vars
      var $textarea = $('#' + id);
      var defaults = this.defaults();
      var toolbars = acf.get('toolbars');
      var field = args.field || false;
      var $field = field.$el || false;

      // bail early
      if (typeof tinymce === 'undefined') return false;
      if (!defaults) return false;

      // check if exists
      if (tinymce.get(id)) {
        return this.enable(id);
      }

      // settings
      var init = $.extend({}, defaults.tinymce, args.tinymce);
      init.id = id;
      init.selector = '#' + id;

      // toolbar
      var toolbar = args.toolbar;
      if (toolbar && toolbars && toolbars[toolbar]) {
        for (var i = 1; i <= 4; i++) {
          init['toolbar' + i] = toolbars[toolbar][i] || '';
        }
      }

      // event
      init.setup = function (ed) {
        ed.on('change', function (e) {
          ed.save(); // save to textarea
          $textarea.trigger('change');
        });

        // Fix bug where Gutenberg does not hear "mouseup" event and tries to select multiple blocks.
        ed.on('mouseup', function (e) {
          var event = new MouseEvent('mouseup');
          window.dispatchEvent(event);
        });

        // Temporarily comment out. May not be necessary due to wysiwyg field actions.
        //ed.on('unload', function(e) {
        //	acf.tinymce.remove( id );
        //});
      };

      // disable wp_autoresize_on (no solution yet for fixed toolbar)
      init.wp_autoresize_on = false;

      // Enable wpautop allowing value to save without <p> tags.
      // Only if the "TinyMCE Advanced" plugin hasn't already set this functionality.
      if (!init.tadv_noautop) {
        init.wpautop = true;
      }

      // hook for 3rd party customization
      init = acf.applyFilters('wysiwyg_tinymce_settings', init, id, field);

      // z-index fix (caused too many conflicts)
      //if( acf.isset(tinymce,'ui','FloatPanel') ) {
      //	tinymce.ui.FloatPanel.zIndex = 900000;
      //}

      // store settings
      tinyMCEPreInit.mceInit[id] = init;

      // visual tab is active
      if (args.mode == 'visual') {
        // init
        var result = tinymce.init(init);

        // get editor
        var ed = tinymce.get(id);

        // validate
        if (!ed) {
          return false;
        }

        // add reference
        ed.acf = args.field;

        // action
        acf.doAction('wysiwyg_tinymce_init', ed, ed.id, init, field);
      }
    },
    /*
     *  initializeQuicktags
     *
     *  This function will initialize the quicktags instance
     *
     *  @type	function
     *  @date	18/8/17
     *  @since	5.6.0
     *
     *  @param	$post_id (int)
     *  @return	$post_id (int)
     */

    initializeQuicktags: function (id, args) {
      // vars
      var defaults = this.defaults();

      // bail early
      if (typeof quicktags === 'undefined') return false;
      if (!defaults) return false;

      // settings
      var init = $.extend({}, defaults.quicktags, args.quicktags);
      init.id = id;

      // filter
      var field = args.field || false;
      var $field = field.$el || false;
      init = acf.applyFilters('wysiwyg_quicktags_settings', init, init.id, field);

      // store settings
      tinyMCEPreInit.qtInit[id] = init;

      // init
      var ed = quicktags(init);

      // validate
      if (!ed) {
        return false;
      }

      // generate HTML
      this.buildQuicktags(ed);

      // action for 3rd party customization
      acf.doAction('wysiwyg_quicktags_init', ed, ed.id, init, field);
    },
    /*
     *  buildQuicktags
     *
     *  This function will build the quicktags HTML
     *
     *  @type	function
     *  @date	18/8/17
     *  @since	5.6.0
     *
     *  @param	$post_id (int)
     *  @return	$post_id (int)
     */

    buildQuicktags: function (ed) {
      var canvas,
        name,
        settings,
        theButtons,
        html,
        ed,
        id,
        i,
        use,
        instanceId,
        defaults = ',strong,em,link,block,del,ins,img,ul,ol,li,code,more,close,';
      canvas = ed.canvas;
      name = ed.name;
      settings = ed.settings;
      html = '';
      theButtons = {};
      use = '';
      instanceId = ed.id;

      // set buttons
      if (settings.buttons) {
        use = ',' + settings.buttons + ',';
      }
      for (i in edButtons) {
        if (!edButtons[i]) {
          continue;
        }
        id = edButtons[i].id;
        if (use && defaults.indexOf(',' + id + ',') !== -1 && use.indexOf(',' + id + ',') === -1) {
          continue;
        }
        if (!edButtons[i].instance || edButtons[i].instance === instanceId) {
          theButtons[id] = edButtons[i];
          if (edButtons[i].html) {
            html += edButtons[i].html(name + '_');
          }
        }
      }
      if (use && use.indexOf(',dfw,') !== -1) {
        theButtons.dfw = new QTags.DFWButton();
        html += theButtons.dfw.html(name + '_');
      }
      if ('rtl' === document.getElementsByTagName('html')[0].dir) {
        theButtons.textdirection = new QTags.TextDirectionButton();
        html += theButtons.textdirection.html(name + '_');
      }
      ed.toolbar.innerHTML = html;
      ed.theButtons = theButtons;
      if (typeof jQuery !== 'undefined') {
        jQuery(document).triggerHandler('quicktags-init', [ed]);
      }
    },
    disable: function (id) {
      this.destroyTinymce(id);
    },
    remove: function (id) {
      this.destroyTinymce(id);
    },
    destroy: function (id) {
      this.destroyTinymce(id);
    },
    destroyTinymce: function (id) {
      // bail early
      if (typeof tinymce === 'undefined') return false;

      // get editor
      var ed = tinymce.get(id);

      // bail early if no editor
      if (!ed) return false;

      // save
      ed.save();

      // destroy editor
      ed.destroy();

      // return
      return true;
    },
    enable: function (id) {
      this.enableTinymce(id);
    },
    enableTinymce: function (id) {
      // bail early
      if (typeof switchEditors === 'undefined') return false;

      // bail early if not initialized
      if (typeof tinyMCEPreInit.mceInit[id] === 'undefined') return false;

      // Ensure textarea element is visible
      // - Fixes bug in block editor when switching between "Block" and "Document" tabs.
      $('#' + id).show();

      // toggle
      switchEditors.go(id, 'tmce');

      // return
      return true;
    }
  };
  var editorManager = new acf.Model({
    // hook in before fieldsEventManager, conditions, etc
    priority: 5,
    actions: {
      prepare: 'onPrepare',
      ready: 'onReady'
    },
    onPrepare: function () {
      // find hidden editor which may exist within a field
      var $div = $('#acf-hidden-wp-editor');

      // move to footer
      if ($div.exists()) {
        $div.appendTo('body');
      }
    },
    onReady: function () {
      // Restore wp.editor functions used by tinymce removed in WP5.
      if (acf.isset(window, 'wp', 'oldEditor')) {
        wp.editor.autop = wp.oldEditor.autop;
        wp.editor.removep = wp.oldEditor.removep;
      }

      // bail early if no tinymce
      if (!acf.isset(window, 'tinymce', 'on')) return;

      // restore default activeEditor
      tinymce.on('AddEditor', function (data) {
        // vars
        var editor = data.editor;

        // bail early if not 'acf'
        if (editor.id.substr(0, 3) !== 'acf') return;

        // override if 'content' exists
        editor = tinymce.editors.content || editor;

        // update vars
        tinymce.activeEditor = editor;
        wpActiveEditor = editor.id;
      });
    }
  });
})(jQuery);

/***/ }),

/***/ "./src/advanced-custom-fields-pro/assets/src/js/_acf-unload.js":
/*!*********************************************************************!*\
  !*** ./src/advanced-custom-fields-pro/assets/src/js/_acf-unload.js ***!
  \*********************************************************************/
/***/ (() => {

(function ($, undefined) {
  acf.unload = new acf.Model({
    wait: 'load',
    active: true,
    changed: false,
    actions: {
      validation_failure: 'startListening',
      validation_success: 'stopListening'
    },
    events: {
      'change form .acf-field': 'startListening',
      'submit form': 'stopListening'
    },
    enable: function () {
      this.active = true;
    },
    disable: function () {
      this.active = false;
    },
    reset: function () {
      this.stopListening();
    },
    startListening: function () {
      // bail early if already changed, not active
      if (this.changed || !this.active) {
        return;
      }

      // update
      this.changed = true;

      // add event
      $(window).on('beforeunload', this.onUnload);
    },
    stopListening: function () {
      // update
      this.changed = false;

      // remove event
      $(window).off('beforeunload', this.onUnload);
    },
    onUnload: function () {
      return acf.__('The changes you made will be lost if you navigate away from this page');
    }
  });
})(jQuery);

/***/ }),

/***/ "./src/advanced-custom-fields-pro/assets/src/js/_acf-validation.js":
/*!*************************************************************************!*\
  !*** ./src/advanced-custom-fields-pro/assets/src/js/_acf-validation.js ***!
  \*************************************************************************/
/***/ (() => {

(function ($, undefined) {
  /**
   *  Validator
   *
   *  The model for validating forms
   *
   *  @date	4/9/18
   *  @since	5.7.5
   *
   *  @param	void
   *  @return	void
   */
  var Validator = acf.Model.extend({
    /** @var string The model identifier. */
    id: 'Validator',
    /** @var object The model data. */
    data: {
      /** @var array The form errors. */
      errors: [],
      /** @var object The form notice. */
      notice: null,
      /** @var string The form status. loading, invalid, valid */
      status: ''
    },
    /** @var object The model events. */
    events: {
      'changed:status': 'onChangeStatus'
    },
    /**
     *  addErrors
     *
     *  Adds errors to the form.
     *
     *  @date	4/9/18
     *  @since	5.7.5
     *
     *  @param	array errors An array of errors.
     *  @return	void
     */
    addErrors: function (errors) {
      errors.map(this.addError, this);
    },
    /**
     *  addError
     *
     *  Adds and error to the form.
     *
     *  @date	4/9/18
     *  @since	5.7.5
     *
     *  @param	object error An error object containing input and message.
     *  @return	void
     */
    addError: function (error) {
      this.data.errors.push(error);
    },
    /**
     *  hasErrors
     *
     *  Returns true if the form has errors.
     *
     *  @date	4/9/18
     *  @since	5.7.5
     *
     *  @param	void
     *  @return	bool
     */
    hasErrors: function () {
      return this.data.errors.length;
    },
    /**
     *  clearErrors
     *
     *  Removes any errors.
     *
     *  @date	4/9/18
     *  @since	5.7.5
     *
     *  @param	void
     *  @return	void
     */
    clearErrors: function () {
      return this.data.errors = [];
    },
    /**
     *  getErrors
     *
     *  Returns the forms errors.
     *
     *  @date	4/9/18
     *  @since	5.7.5
     *
     *  @param	void
     *  @return	array
     */
    getErrors: function () {
      return this.data.errors;
    },
    /**
     *  getFieldErrors
     *
     *  Returns the forms field errors.
     *
     *  @date	4/9/18
     *  @since	5.7.5
     *
     *  @param	void
     *  @return	array
     */
    getFieldErrors: function () {
      // vars
      var errors = [];
      var inputs = [];

      // loop
      this.getErrors().map(function (error) {
        // bail early if global
        if (!error.input) return;

        // update if exists
        var i = inputs.indexOf(error.input);
        if (i > -1) {
          errors[i] = error;

          // update
        } else {
          errors.push(error);
          inputs.push(error.input);
        }
      });

      // return
      return errors;
    },
    /**
     *  getGlobalErrors
     *
     *  Returns the forms global errors (errors without a specific input).
     *
     *  @date	4/9/18
     *  @since	5.7.5
     *
     *  @param	void
     *  @return	array
     */
    getGlobalErrors: function () {
      // return array of errors that contain no input
      return this.getErrors().filter(function (error) {
        return !error.input;
      });
    },
    /**
     *  showErrors
     *
     *  Displays all errors for this form.
     *
     *  @date	4/9/18
     *  @since	5.7.5
     *
     *  @param	void
     *  @return	void
     */
    showErrors: function () {
      // bail early if no errors
      if (!this.hasErrors()) {
        return;
      }

      // vars
      var fieldErrors = this.getFieldErrors();
      var globalErrors = this.getGlobalErrors();

      // vars
      var errorCount = 0;
      var $scrollTo = false;

      // loop
      fieldErrors.map(function (error) {
        // get input
        var $input = this.$('[name="' + error.input + '"]').first();

        // if $_POST value was an array, this $input may not exist
        if (!$input.length) {
          $input = this.$('[name^="' + error.input + '"]').first();
        }

        // bail early if input doesn't exist
        if (!$input.length) {
          return;
        }

        // increase
        errorCount++;

        // get field
        var field = acf.getClosestField($input);

        // make sure the postbox containing this field is not hidden by screen options
        ensureFieldPostBoxIsVisible(field.$el);

        // show error
        field.showError(error.message);

        // set $scrollTo
        if (!$scrollTo) {
          $scrollTo = field.$el;
        }
      }, this);

      // errorMessage
      var errorMessage = acf.__('Validation failed');
      globalErrors.map(function (error) {
        errorMessage += '. ' + error.message;
      });
      if (errorCount == 1) {
        errorMessage += '. ' + acf.__('1 field requires attention');
      } else if (errorCount > 1) {
        errorMessage += '. ' + acf.__('%d fields require attention').replace('%d', errorCount);
      }

      // notice
      if (this.has('notice')) {
        this.get('notice').update({
          type: 'error',
          text: errorMessage
        });
      } else {
        var notice = acf.newNotice({
          type: 'error',
          text: errorMessage,
          target: this.$el
        });
        this.set('notice', notice);
      }

      // If in a modal, don't try to scroll.
      if (this.$el.parents('.acf-popup-box').length) {
        return;
      }

      // if no $scrollTo, set to message
      if (!$scrollTo) {
        $scrollTo = this.get('notice').$el;
      }

      // timeout
      setTimeout(function () {
        $('html, body').animate({
          scrollTop: $scrollTo.offset().top - $(window).height() / 2
        }, 500);
      }, 10);
    },
    /**
     *  onChangeStatus
     *
     *  Update the form class when changing the 'status' data
     *
     *  @date	4/9/18
     *  @since	5.7.5
     *
     *  @param	object e The event object.
     *  @param	jQuery $el The form element.
     *  @param	string value The new status.
     *  @param	string prevValue The old status.
     *  @return	void
     */
    onChangeStatus: function (e, $el, value, prevValue) {
      this.$el.removeClass('is-' + prevValue).addClass('is-' + value);
    },
    /**
     *  validate
     *
     *  Vaildates the form via AJAX.
     *
     *  @date	4/9/18
     *  @since	5.7.5
     *
     *  @param	object args A list of settings to customize the validation process.
     *  @return	bool True if the form is valid.
     */
    validate: function (args) {
      // default args
      args = acf.parseArgs(args, {
        // trigger event
        event: false,
        // reset the form after submit
        reset: false,
        // loading callback
        loading: function () {},
        // complete callback
        complete: function () {},
        // failure callback
        failure: function () {},
        // success callback
        success: function ($form) {
          $form.submit();
        }
      });

      // return true if is valid - allows form submit
      if (this.get('status') == 'valid') {
        return true;
      }

      // return false if is currently validating - prevents form submit
      if (this.get('status') == 'validating') {
        return false;
      }

      // return true if no ACF fields exist (no need to validate)
      if (!this.$('.acf-field').length) {
        return true;
      }

      // if event is provided, create a new success callback.
      if (args.event) {
        var event = $.Event(null, args.event);
        args.success = function () {
          acf.enableSubmit($(event.target)).trigger(event);
        };
      }

      // action for 3rd party
      acf.doAction('validation_begin', this.$el);

      // lock form
      acf.lockForm(this.$el);

      // loading callback
      args.loading(this.$el, this);

      // update status
      this.set('status', 'validating');

      // success callback
      var onSuccess = function (json) {
        // validate
        if (!acf.isAjaxSuccess(json)) {
          return;
        }

        // filter
        var data = acf.applyFilters('validation_complete', json.data, this.$el, this);

        // add errors
        if (!data.valid) {
          this.addErrors(data.errors);
        }
      };

      // complete
      var onComplete = function () {
        // unlock form
        acf.unlockForm(this.$el);

        // failure
        if (this.hasErrors()) {
          // update status
          this.set('status', 'invalid');

          // action
          acf.doAction('validation_failure', this.$el, this);

          // display errors
          this.showErrors();

          // failure callback
          args.failure(this.$el, this);

          // success
        } else {
          // update status
          this.set('status', 'valid');

          // remove previous error message
          if (this.has('notice')) {
            this.get('notice').update({
              type: 'success',
              text: acf.__('Validation successful'),
              timeout: 1000
            });
          }

          // action
          acf.doAction('validation_success', this.$el, this);
          acf.doAction('submit', this.$el);

          // success callback (submit form)
          args.success(this.$el, this);

          // lock form
          acf.lockForm(this.$el);

          // reset
          if (args.reset) {
            this.reset();
          }
        }

        // complete callback
        args.complete(this.$el, this);

        // clear errors
        this.clearErrors();
      };

      // serialize form data
      var data = acf.serialize(this.$el);
      data.action = 'acf/validate_save_post';

      // ajax
      $.ajax({
        url: acf.get('ajaxurl'),
        data: acf.prepareForAjax(data),
        type: 'post',
        dataType: 'json',
        context: this,
        success: onSuccess,
        complete: onComplete
      });

      // return false to fail validation and allow AJAX
      return false;
    },
    /**
     *  setup
     *
     *  Called during the constructor function to setup this instance
     *
     *  @date	4/9/18
     *  @since	5.7.5
     *
     *  @param	jQuery $form The form element.
     *  @return	void
     */
    setup: function ($form) {
      // set $el
      this.$el = $form;
    },
    /**
     *  reset
     *
     *  Rests the validation to be used again.
     *
     *  @date	6/9/18
     *  @since	5.7.5
     *
     *  @param	void
     *  @return	void
     */
    reset: function () {
      // reset data
      this.set('errors', []);
      this.set('notice', null);
      this.set('status', '');

      // unlock form
      acf.unlockForm(this.$el);
    }
  });

  /**
   *  getValidator
   *
   *  Returns the instance for a given form element.
   *
   *  @date	4/9/18
   *  @since	5.7.5
   *
   *  @param	jQuery $el The form element.
   *  @return	object
   */
  var getValidator = function ($el) {
    // instantiate
    var validator = $el.data('acf');
    if (!validator) {
      validator = new Validator($el);
    }

    // return
    return validator;
  };

  /**
   *  acf.validateForm
   *
   *  A helper function for the Validator.validate() function.
   *  Returns true if form is valid, or fetches a validation request and returns false.
   *
   *  @date	4/4/18
   *  @since	5.6.9
   *
   *  @param	object args A list of settings to customize the validation process.
   *  @return	bool
   */

  acf.validateForm = function (args) {
    return getValidator(args.form).validate(args);
  };

  /**
   *  acf.enableSubmit
   *
   *  Enables a submit button and returns the element.
   *
   *  @date	30/8/18
   *  @since	5.7.4
   *
   *  @param	jQuery $submit The submit button.
   *  @return	jQuery
   */
  acf.enableSubmit = function ($submit) {
    return $submit.removeClass('disabled').removeAttr('disabled');
  };

  /**
   *  acf.disableSubmit
   *
   *  Disables a submit button and returns the element.
   *
   *  @date	30/8/18
   *  @since	5.7.4
   *
   *  @param	jQuery $submit The submit button.
   *  @return	jQuery
   */
  acf.disableSubmit = function ($submit) {
    return $submit.addClass('disabled').attr('disabled', true);
  };

  /**
   *  acf.showSpinner
   *
   *  Shows the spinner element.
   *
   *  @date	4/9/18
   *  @since	5.7.5
   *
   *  @param	jQuery $spinner The spinner element.
   *  @return	jQuery
   */
  acf.showSpinner = function ($spinner) {
    $spinner.addClass('is-active'); // add class (WP > 4.2)
    $spinner.css('display', 'inline-block'); // css (WP < 4.2)
    return $spinner;
  };

  /**
   *  acf.hideSpinner
   *
   *  Hides the spinner element.
   *
   *  @date	4/9/18
   *  @since	5.7.5
   *
   *  @param	jQuery $spinner The spinner element.
   *  @return	jQuery
   */
  acf.hideSpinner = function ($spinner) {
    $spinner.removeClass('is-active'); // add class (WP > 4.2)
    $spinner.css('display', 'none'); // css (WP < 4.2)
    return $spinner;
  };

  /**
   *  acf.lockForm
   *
   *  Locks a form by disabeling its primary inputs and showing a spinner.
   *
   *  @date	4/9/18
   *  @since	5.7.5
   *
   *  @param	jQuery $form The form element.
   *  @return	jQuery
   */
  acf.lockForm = function ($form) {
    // vars
    var $wrap = findSubmitWrap($form);
    var $submit = $wrap.find('.button, [type="submit"]').not('.acf-nav, .acf-repeater-add-row');
    var $spinner = $wrap.find('.spinner, .acf-spinner');

    // hide all spinners (hides the preview spinner)
    acf.hideSpinner($spinner);

    // lock
    acf.disableSubmit($submit);
    acf.showSpinner($spinner.last());
    return $form;
  };

  /**
   *  acf.unlockForm
   *
   *  Unlocks a form by enabeling its primary inputs and hiding all spinners.
   *
   *  @date	4/9/18
   *  @since	5.7.5
   *
   *  @param	jQuery $form The form element.
   *  @return	jQuery
   */
  acf.unlockForm = function ($form) {
    // vars
    var $wrap = findSubmitWrap($form);
    var $submit = $wrap.find('.button, [type="submit"]').not('.acf-nav, .acf-repeater-add-row');
    var $spinner = $wrap.find('.spinner, .acf-spinner');

    // unlock
    acf.enableSubmit($submit);
    acf.hideSpinner($spinner);
    return $form;
  };

  /**
   *  findSubmitWrap
   *
   *  An internal function to find the 'primary' form submit wrapping element.
   *
   *  @date	4/9/18
   *  @since	5.7.5
   *
   *  @param	jQuery $form The form element.
   *  @return	jQuery
   */
  var findSubmitWrap = function ($form) {
    // default post submit div
    var $wrap = $form.find('#submitdiv');
    if ($wrap.length) {
      return $wrap;
    }

    // 3rd party publish box
    var $wrap = $form.find('#submitpost');
    if ($wrap.length) {
      return $wrap;
    }

    // term, user
    var $wrap = $form.find('p.submit').last();
    if ($wrap.length) {
      return $wrap;
    }

    // front end form
    var $wrap = $form.find('.acf-form-submit');
    if ($wrap.length) {
      return $wrap;
    }

    // ACF 6.2 options page modal
    var $wrap = $('#acf-create-options-page-form .acf-actions');
    if ($wrap.length) {
      return $wrap;
    }

    // ACF 6.0+ headerbar submit
    var $wrap = $('.acf-headerbar-actions');
    if ($wrap.length) {
      return $wrap;
    }

    // default
    return $form;
  };

  /**
   * A debounced function to trigger a form submission.
   *
   * @date	15/07/2020
   * @since	5.9.0
   *
   * @param	type Var Description.
   * @return	type Description.
   */
  var submitFormDebounced = acf.debounce(function ($form) {
    $form.submit();
  });

  /**
   * Ensure field is visible for validation errors
   *
   * @date	20/10/2021
   * @since	5.11.0
   */
  var ensureFieldPostBoxIsVisible = function ($el) {
    // Find the postbox element containing this field.
    var $postbox = $el.parents('.acf-postbox');
    if ($postbox.length) {
      var acf_postbox = acf.getPostbox($postbox);
      if (acf_postbox && acf_postbox.isHiddenByScreenOptions()) {
        // Rather than using .show() here, we don't want the field to appear next reload.
        // So just temporarily show the field group so validation can complete.
        acf_postbox.$el.removeClass('hide-if-js');
        acf_postbox.$el.css('display', '');
      }
    }
  };

  /**
   * Ensure metaboxes which contain browser validation failures are visible.
   *
   * @date	20/10/2021
   * @since	5.11.0
   */
  var ensureInvalidFieldVisibility = function () {
    // Load each ACF input field and check it's browser validation state.
    var $inputs = $('.acf-field input');
    $inputs.each(function () {
      if (!this.checkValidity()) {
        // Field is invalid, so we need to make sure it's metabox is visible.
        ensureFieldPostBoxIsVisible($(this));
      }
    });
  };

  /**
   *  acf.validation
   *
   *  Global validation logic
   *
   *  @date	4/4/18
   *  @since	5.6.9
   *
   *  @param	void
   *  @return	void
   */

  acf.validation = new acf.Model({
    /** @var string The model identifier. */
    id: 'validation',
    /** @var bool The active state. Set to false before 'prepare' to prevent validation. */
    active: true,
    /** @var string The model initialize time. */
    wait: 'prepare',
    /** @var object The model actions. */
    actions: {
      ready: 'addInputEvents',
      append: 'addInputEvents'
    },
    /** @var object The model events. */
    events: {
      'click input[type="submit"]': 'onClickSubmit',
      'click button[type="submit"]': 'onClickSubmit',
      //'click #editor .editor-post-publish-button': 'onClickSubmitGutenberg',
      'click #save-post': 'onClickSave',
      'submit form#post': 'onSubmitPost',
      'submit form': 'onSubmit'
    },
    /**
     *  initialize
     *
     *  Called when initializing the model.
     *
     *  @date	4/9/18
     *  @since	5.7.5
     *
     *  @param	void
     *  @return	void
     */
    initialize: function () {
      // check 'validation' setting
      if (!acf.get('validation')) {
        this.active = false;
        this.actions = {};
        this.events = {};
      }
    },
    /**
     *  enable
     *
     *  Enables validation.
     *
     *  @date	4/9/18
     *  @since	5.7.5
     *
     *  @param	void
     *  @return	void
     */
    enable: function () {
      this.active = true;
    },
    /**
     *  disable
     *
     *  Disables validation.
     *
     *  @date	4/9/18
     *  @since	5.7.5
     *
     *  @param	void
     *  @return	void
     */
    disable: function () {
      this.active = false;
    },
    /**
     *  reset
     *
     *  Rests the form validation to be used again
     *
     *  @date	6/9/18
     *  @since	5.7.5
     *
     *  @param	jQuery $form The form element.
     *  @return	void
     */
    reset: function ($form) {
      getValidator($form).reset();
    },
    /**
     *  addInputEvents
     *
     *  Adds 'invalid' event listeners to HTML inputs.
     *
     *  @date	4/9/18
     *  @since	5.7.5
     *
     *  @param	jQuery $el The element being added / readied.
     *  @return	void
     */
    addInputEvents: function ($el) {
      // Bug exists in Safari where custom "invalid" handling prevents draft from saving.
      if (acf.get('browser') === 'safari') return;

      // vars
      var $inputs = $('.acf-field [name]', $el);

      // check
      if ($inputs.length) {
        this.on($inputs, 'invalid', 'onInvalid');
      }
    },
    /**
     *  onInvalid
     *
     *  Callback for the 'invalid' event.
     *
     *  @date	4/9/18
     *  @since	5.7.5
     *
     *  @param	object e The event object.
     *  @param	jQuery $el The input element.
     *  @return	void
     */
    onInvalid: function (e, $el) {
      // prevent default
      // - prevents browser error message
      // - also fixes chrome bug where 'hidden-by-tab' field throws focus error
      e.preventDefault();

      // vars
      var $form = $el.closest('form');

      // check form exists
      if ($form.length) {
        // add error to validator
        getValidator($form).addError({
          input: $el.attr('name'),
          message: acf.strEscape(e.target.validationMessage)
        });

        // trigger submit on $form
        // - allows for "save", "preview" and "publish" to work
        submitFormDebounced($form);
      }
    },
    /**
     *  onClickSubmit
     *
     *  Callback when clicking submit.
     *
     *  @date	4/9/18
     *  @since	5.7.5
     *
     *  @param	object e The event object.
     *  @param	jQuery $el The input element.
     *  @return	void
     */
    onClickSubmit: function (e, $el) {
      // Some browsers (safari) force their browser validation before our AJAX validation,
      // so we need to make sure fields are visible earlier than showErrors()
      ensureInvalidFieldVisibility();

      // store the "click event" for later use in this.onSubmit()
      this.set('originalEvent', e);
    },
    /**
     *  onClickSave
     *
     *  Set ignore to true when saving a draft.
     *
     *  @date	4/9/18
     *  @since	5.7.5
     *
     *  @param	object e The event object.
     *  @param	jQuery $el The input element.
     *  @return	void
     */
    onClickSave: function (e, $el) {
      this.set('ignore', true);
    },
    /**
     *  onClickSubmitGutenberg
     *
     *  Custom validation event for the gutenberg editor.
     *
     *  @date	29/10/18
     *  @since	5.8.0
     *
     *  @param	object e The event object.
     *  @param	jQuery $el The input element.
     *  @return	void
     */
    onClickSubmitGutenberg: function (e, $el) {
      // validate
      var valid = acf.validateForm({
        form: $('#editor'),
        event: e,
        reset: true,
        failure: function ($form, validator) {
          var $notice = validator.get('notice').$el;
          $notice.appendTo('.components-notice-list');
          $notice.find('.acf-notice-dismiss').removeClass('small');
        }
      });

      // if not valid, stop event and allow validation to continue
      if (!valid) {
        e.preventDefault();
        e.stopImmediatePropagation();
      }
    },
    /**
     * onSubmitPost
     *
     * Callback when the 'post' form is submit.
     *
     * @date	5/3/19
     * @since	5.7.13
     *
     * @param	object e The event object.
     * @param	jQuery $el The input element.
     * @return	void
     */
    onSubmitPost: function (e, $el) {
      // Check if is preview.
      if ($('input#wp-preview').val() === 'dopreview') {
        // Ignore validation.
        this.set('ignore', true);

        // Unlock form to fix conflict with core "submit.edit-post" event causing all submit buttons to be disabled.
        acf.unlockForm($el);
      }
    },
    /**
     *  onSubmit
     *
     *  Callback when the form is submit.
     *
     *  @date	4/9/18
     *  @since	5.7.5
     *
     *  @param	object e The event object.
     *  @param	jQuery $el The input element.
     *  @return	void
     */
    onSubmit: function (e, $el) {
      // Allow form to submit if...
      if (
      // Validation has been disabled.
      !this.active ||
      // Or this event is to be ignored.
      this.get('ignore') ||
      // Or this event has already been prevented.
      e.isDefaultPrevented()) {
        // Return early and call reset function.
        return this.allowSubmit();
      }

      // Validate form.
      var valid = acf.validateForm({
        form: $el,
        event: this.get('originalEvent')
      });

      // If not valid, stop event to prevent form submit.
      if (!valid) {
        e.preventDefault();
      }
    },
    /**
     * allowSubmit
     *
     * Resets data during onSubmit when the form is allowed to submit.
     *
     * @date	5/3/19
     * @since	5.7.13
     *
     * @param	void
     * @return	void
     */
    allowSubmit: function () {
      // Reset "ignore" state.
      this.set('ignore', false);

      // Reset "originalEvent" object.
      this.set('originalEvent', false);

      // Return true
      return true;
    }
  });
  var gutenbergValidation = new acf.Model({
    wait: 'prepare',
    initialize: function () {
      // Bail early if not Gutenberg.
      if (!acf.isGutenberg()) {
        return;
      }

      // Custommize the editor.
      this.customizeEditor();
    },
    customizeEditor: function () {
      // Extract vars.
      var editor = wp.data.dispatch('core/editor');
      var editorSelect = wp.data.select('core/editor');
      var notices = wp.data.dispatch('core/notices');

      // Backup original method.
      var savePost = editor.savePost;

      // Listen for changes to post status and perform actions:
      // a) Enable validation for "publish" action.
      // b) Remember last non "publish" status used for restoring after validation fail.
      var useValidation = false;
      var lastPostStatus = '';
      wp.data.subscribe(function () {
        var postStatus = editorSelect.getEditedPostAttribute('status');
        useValidation = postStatus === 'publish' || postStatus === 'future';
        lastPostStatus = postStatus !== 'publish' ? postStatus : lastPostStatus;
      });

      // Create validation version.
      editor.savePost = function (options) {
        options = options || {};

        // Backup vars.
        var _this = this;
        var _args = arguments;

        // Perform validation within a Promise.
        return new Promise(function (resolve, reject) {
          // Bail early if is autosave or preview.
          if (options.isAutosave || options.isPreview) {
            return resolve('Validation ignored (autosave).');
          }

          // Bail early if validation is not needed.
          if (!useValidation) {
            return resolve('Validation ignored (draft).');
          }

          // Validate the editor form.
          var valid = acf.validateForm({
            form: $('#editor'),
            reset: true,
            complete: function ($form, validator) {
              // Always unlock the form after AJAX.
              editor.unlockPostSaving('acf');
            },
            failure: function ($form, validator) {
              // Get validation error and append to Gutenberg notices.
              var notice = validator.get('notice');
              notices.createErrorNotice(notice.get('text'), {
                id: 'acf-validation',
                isDismissible: true
              });
              notice.remove();

              // Restore last non "publish" status.
              if (lastPostStatus) {
                editor.editPost({
                  status: lastPostStatus
                });
              }

              // Rejext promise and prevent savePost().
              reject('Validation failed.');
            },
            success: function () {
              notices.removeNotice('acf-validation');

              // Resolve promise and allow savePost().
              resolve('Validation success.');
            }
          });

          // Resolve promise and allow savePost() if no validation is needed.
          if (valid) {
            resolve('Validation bypassed.');

            // Otherwise, lock the form and wait for AJAX response.
          } else {
            editor.lockPostSaving('acf');
          }
        }).then(function () {
          return savePost.apply(_this, _args);
        }, err => {
          // Nothing to do here, user is alerted of validation issues.
        });
      };
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
/*!*******************************************************************!*\
  !*** ./src/advanced-custom-fields-pro/assets/src/js/acf-input.js ***!
  \*******************************************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _acf_field_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./_acf-field.js */ "./src/advanced-custom-fields-pro/assets/src/js/_acf-field.js");
/* harmony import */ var _acf_field_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_acf_field_js__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _acf_fields_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./_acf-fields.js */ "./src/advanced-custom-fields-pro/assets/src/js/_acf-fields.js");
/* harmony import */ var _acf_fields_js__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_acf_fields_js__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _acf_field_accordion_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./_acf-field-accordion.js */ "./src/advanced-custom-fields-pro/assets/src/js/_acf-field-accordion.js");
/* harmony import */ var _acf_field_accordion_js__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_acf_field_accordion_js__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _acf_field_button_group_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./_acf-field-button-group.js */ "./src/advanced-custom-fields-pro/assets/src/js/_acf-field-button-group.js");
/* harmony import */ var _acf_field_button_group_js__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_acf_field_button_group_js__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _acf_field_checkbox_js__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./_acf-field-checkbox.js */ "./src/advanced-custom-fields-pro/assets/src/js/_acf-field-checkbox.js");
/* harmony import */ var _acf_field_checkbox_js__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_acf_field_checkbox_js__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var _acf_field_color_picker_js__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./_acf-field-color-picker.js */ "./src/advanced-custom-fields-pro/assets/src/js/_acf-field-color-picker.js");
/* harmony import */ var _acf_field_color_picker_js__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(_acf_field_color_picker_js__WEBPACK_IMPORTED_MODULE_5__);
/* harmony import */ var _acf_field_date_picker_js__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./_acf-field-date-picker.js */ "./src/advanced-custom-fields-pro/assets/src/js/_acf-field-date-picker.js");
/* harmony import */ var _acf_field_date_picker_js__WEBPACK_IMPORTED_MODULE_6___default = /*#__PURE__*/__webpack_require__.n(_acf_field_date_picker_js__WEBPACK_IMPORTED_MODULE_6__);
/* harmony import */ var _acf_field_date_time_picker_js__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./_acf-field-date-time-picker.js */ "./src/advanced-custom-fields-pro/assets/src/js/_acf-field-date-time-picker.js");
/* harmony import */ var _acf_field_date_time_picker_js__WEBPACK_IMPORTED_MODULE_7___default = /*#__PURE__*/__webpack_require__.n(_acf_field_date_time_picker_js__WEBPACK_IMPORTED_MODULE_7__);
/* harmony import */ var _acf_field_google_map_js__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! ./_acf-field-google-map.js */ "./src/advanced-custom-fields-pro/assets/src/js/_acf-field-google-map.js");
/* harmony import */ var _acf_field_google_map_js__WEBPACK_IMPORTED_MODULE_8___default = /*#__PURE__*/__webpack_require__.n(_acf_field_google_map_js__WEBPACK_IMPORTED_MODULE_8__);
/* harmony import */ var _acf_field_image_js__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! ./_acf-field-image.js */ "./src/advanced-custom-fields-pro/assets/src/js/_acf-field-image.js");
/* harmony import */ var _acf_field_image_js__WEBPACK_IMPORTED_MODULE_9___default = /*#__PURE__*/__webpack_require__.n(_acf_field_image_js__WEBPACK_IMPORTED_MODULE_9__);
/* harmony import */ var _acf_field_file_js__WEBPACK_IMPORTED_MODULE_10__ = __webpack_require__(/*! ./_acf-field-file.js */ "./src/advanced-custom-fields-pro/assets/src/js/_acf-field-file.js");
/* harmony import */ var _acf_field_file_js__WEBPACK_IMPORTED_MODULE_10___default = /*#__PURE__*/__webpack_require__.n(_acf_field_file_js__WEBPACK_IMPORTED_MODULE_10__);
/* harmony import */ var _acf_field_link_js__WEBPACK_IMPORTED_MODULE_11__ = __webpack_require__(/*! ./_acf-field-link.js */ "./src/advanced-custom-fields-pro/assets/src/js/_acf-field-link.js");
/* harmony import */ var _acf_field_link_js__WEBPACK_IMPORTED_MODULE_11___default = /*#__PURE__*/__webpack_require__.n(_acf_field_link_js__WEBPACK_IMPORTED_MODULE_11__);
/* harmony import */ var _acf_field_oembed_js__WEBPACK_IMPORTED_MODULE_12__ = __webpack_require__(/*! ./_acf-field-oembed.js */ "./src/advanced-custom-fields-pro/assets/src/js/_acf-field-oembed.js");
/* harmony import */ var _acf_field_oembed_js__WEBPACK_IMPORTED_MODULE_12___default = /*#__PURE__*/__webpack_require__.n(_acf_field_oembed_js__WEBPACK_IMPORTED_MODULE_12__);
/* harmony import */ var _acf_field_radio_js__WEBPACK_IMPORTED_MODULE_13__ = __webpack_require__(/*! ./_acf-field-radio.js */ "./src/advanced-custom-fields-pro/assets/src/js/_acf-field-radio.js");
/* harmony import */ var _acf_field_radio_js__WEBPACK_IMPORTED_MODULE_13___default = /*#__PURE__*/__webpack_require__.n(_acf_field_radio_js__WEBPACK_IMPORTED_MODULE_13__);
/* harmony import */ var _acf_field_range_js__WEBPACK_IMPORTED_MODULE_14__ = __webpack_require__(/*! ./_acf-field-range.js */ "./src/advanced-custom-fields-pro/assets/src/js/_acf-field-range.js");
/* harmony import */ var _acf_field_range_js__WEBPACK_IMPORTED_MODULE_14___default = /*#__PURE__*/__webpack_require__.n(_acf_field_range_js__WEBPACK_IMPORTED_MODULE_14__);
/* harmony import */ var _acf_field_relationship_js__WEBPACK_IMPORTED_MODULE_15__ = __webpack_require__(/*! ./_acf-field-relationship.js */ "./src/advanced-custom-fields-pro/assets/src/js/_acf-field-relationship.js");
/* harmony import */ var _acf_field_relationship_js__WEBPACK_IMPORTED_MODULE_15___default = /*#__PURE__*/__webpack_require__.n(_acf_field_relationship_js__WEBPACK_IMPORTED_MODULE_15__);
/* harmony import */ var _acf_field_select_js__WEBPACK_IMPORTED_MODULE_16__ = __webpack_require__(/*! ./_acf-field-select.js */ "./src/advanced-custom-fields-pro/assets/src/js/_acf-field-select.js");
/* harmony import */ var _acf_field_select_js__WEBPACK_IMPORTED_MODULE_16___default = /*#__PURE__*/__webpack_require__.n(_acf_field_select_js__WEBPACK_IMPORTED_MODULE_16__);
/* harmony import */ var _acf_field_tab_js__WEBPACK_IMPORTED_MODULE_17__ = __webpack_require__(/*! ./_acf-field-tab.js */ "./src/advanced-custom-fields-pro/assets/src/js/_acf-field-tab.js");
/* harmony import */ var _acf_field_tab_js__WEBPACK_IMPORTED_MODULE_17___default = /*#__PURE__*/__webpack_require__.n(_acf_field_tab_js__WEBPACK_IMPORTED_MODULE_17__);
/* harmony import */ var _acf_field_post_object_js__WEBPACK_IMPORTED_MODULE_18__ = __webpack_require__(/*! ./_acf-field-post-object.js */ "./src/advanced-custom-fields-pro/assets/src/js/_acf-field-post-object.js");
/* harmony import */ var _acf_field_post_object_js__WEBPACK_IMPORTED_MODULE_18___default = /*#__PURE__*/__webpack_require__.n(_acf_field_post_object_js__WEBPACK_IMPORTED_MODULE_18__);
/* harmony import */ var _acf_field_page_link_js__WEBPACK_IMPORTED_MODULE_19__ = __webpack_require__(/*! ./_acf-field-page-link.js */ "./src/advanced-custom-fields-pro/assets/src/js/_acf-field-page-link.js");
/* harmony import */ var _acf_field_page_link_js__WEBPACK_IMPORTED_MODULE_19___default = /*#__PURE__*/__webpack_require__.n(_acf_field_page_link_js__WEBPACK_IMPORTED_MODULE_19__);
/* harmony import */ var _acf_field_user_js__WEBPACK_IMPORTED_MODULE_20__ = __webpack_require__(/*! ./_acf-field-user.js */ "./src/advanced-custom-fields-pro/assets/src/js/_acf-field-user.js");
/* harmony import */ var _acf_field_user_js__WEBPACK_IMPORTED_MODULE_20___default = /*#__PURE__*/__webpack_require__.n(_acf_field_user_js__WEBPACK_IMPORTED_MODULE_20__);
/* harmony import */ var _acf_field_taxonomy_js__WEBPACK_IMPORTED_MODULE_21__ = __webpack_require__(/*! ./_acf-field-taxonomy.js */ "./src/advanced-custom-fields-pro/assets/src/js/_acf-field-taxonomy.js");
/* harmony import */ var _acf_field_taxonomy_js__WEBPACK_IMPORTED_MODULE_21___default = /*#__PURE__*/__webpack_require__.n(_acf_field_taxonomy_js__WEBPACK_IMPORTED_MODULE_21__);
/* harmony import */ var _acf_field_time_picker_js__WEBPACK_IMPORTED_MODULE_22__ = __webpack_require__(/*! ./_acf-field-time-picker.js */ "./src/advanced-custom-fields-pro/assets/src/js/_acf-field-time-picker.js");
/* harmony import */ var _acf_field_time_picker_js__WEBPACK_IMPORTED_MODULE_22___default = /*#__PURE__*/__webpack_require__.n(_acf_field_time_picker_js__WEBPACK_IMPORTED_MODULE_22__);
/* harmony import */ var _acf_field_true_false_js__WEBPACK_IMPORTED_MODULE_23__ = __webpack_require__(/*! ./_acf-field-true-false.js */ "./src/advanced-custom-fields-pro/assets/src/js/_acf-field-true-false.js");
/* harmony import */ var _acf_field_true_false_js__WEBPACK_IMPORTED_MODULE_23___default = /*#__PURE__*/__webpack_require__.n(_acf_field_true_false_js__WEBPACK_IMPORTED_MODULE_23__);
/* harmony import */ var _acf_field_url_js__WEBPACK_IMPORTED_MODULE_24__ = __webpack_require__(/*! ./_acf-field-url.js */ "./src/advanced-custom-fields-pro/assets/src/js/_acf-field-url.js");
/* harmony import */ var _acf_field_url_js__WEBPACK_IMPORTED_MODULE_24___default = /*#__PURE__*/__webpack_require__.n(_acf_field_url_js__WEBPACK_IMPORTED_MODULE_24__);
/* harmony import */ var _acf_field_wysiwyg_js__WEBPACK_IMPORTED_MODULE_25__ = __webpack_require__(/*! ./_acf-field-wysiwyg.js */ "./src/advanced-custom-fields-pro/assets/src/js/_acf-field-wysiwyg.js");
/* harmony import */ var _acf_field_wysiwyg_js__WEBPACK_IMPORTED_MODULE_25___default = /*#__PURE__*/__webpack_require__.n(_acf_field_wysiwyg_js__WEBPACK_IMPORTED_MODULE_25__);
/* harmony import */ var _acf_condition_js__WEBPACK_IMPORTED_MODULE_26__ = __webpack_require__(/*! ./_acf-condition.js */ "./src/advanced-custom-fields-pro/assets/src/js/_acf-condition.js");
/* harmony import */ var _acf_condition_js__WEBPACK_IMPORTED_MODULE_26___default = /*#__PURE__*/__webpack_require__.n(_acf_condition_js__WEBPACK_IMPORTED_MODULE_26__);
/* harmony import */ var _acf_conditions_js__WEBPACK_IMPORTED_MODULE_27__ = __webpack_require__(/*! ./_acf-conditions.js */ "./src/advanced-custom-fields-pro/assets/src/js/_acf-conditions.js");
/* harmony import */ var _acf_conditions_js__WEBPACK_IMPORTED_MODULE_27___default = /*#__PURE__*/__webpack_require__.n(_acf_conditions_js__WEBPACK_IMPORTED_MODULE_27__);
/* harmony import */ var _acf_condition_types_js__WEBPACK_IMPORTED_MODULE_28__ = __webpack_require__(/*! ./_acf-condition-types.js */ "./src/advanced-custom-fields-pro/assets/src/js/_acf-condition-types.js");
/* harmony import */ var _acf_condition_types_js__WEBPACK_IMPORTED_MODULE_28___default = /*#__PURE__*/__webpack_require__.n(_acf_condition_types_js__WEBPACK_IMPORTED_MODULE_28__);
/* harmony import */ var _acf_unload_js__WEBPACK_IMPORTED_MODULE_29__ = __webpack_require__(/*! ./_acf-unload.js */ "./src/advanced-custom-fields-pro/assets/src/js/_acf-unload.js");
/* harmony import */ var _acf_unload_js__WEBPACK_IMPORTED_MODULE_29___default = /*#__PURE__*/__webpack_require__.n(_acf_unload_js__WEBPACK_IMPORTED_MODULE_29__);
/* harmony import */ var _acf_postbox_js__WEBPACK_IMPORTED_MODULE_30__ = __webpack_require__(/*! ./_acf-postbox.js */ "./src/advanced-custom-fields-pro/assets/src/js/_acf-postbox.js");
/* harmony import */ var _acf_postbox_js__WEBPACK_IMPORTED_MODULE_30___default = /*#__PURE__*/__webpack_require__.n(_acf_postbox_js__WEBPACK_IMPORTED_MODULE_30__);
/* harmony import */ var _acf_media_js__WEBPACK_IMPORTED_MODULE_31__ = __webpack_require__(/*! ./_acf-media.js */ "./src/advanced-custom-fields-pro/assets/src/js/_acf-media.js");
/* harmony import */ var _acf_media_js__WEBPACK_IMPORTED_MODULE_31___default = /*#__PURE__*/__webpack_require__.n(_acf_media_js__WEBPACK_IMPORTED_MODULE_31__);
/* harmony import */ var _acf_screen_js__WEBPACK_IMPORTED_MODULE_32__ = __webpack_require__(/*! ./_acf-screen.js */ "./src/advanced-custom-fields-pro/assets/src/js/_acf-screen.js");
/* harmony import */ var _acf_screen_js__WEBPACK_IMPORTED_MODULE_32___default = /*#__PURE__*/__webpack_require__.n(_acf_screen_js__WEBPACK_IMPORTED_MODULE_32__);
/* harmony import */ var _acf_select2_js__WEBPACK_IMPORTED_MODULE_33__ = __webpack_require__(/*! ./_acf-select2.js */ "./src/advanced-custom-fields-pro/assets/src/js/_acf-select2.js");
/* harmony import */ var _acf_select2_js__WEBPACK_IMPORTED_MODULE_33___default = /*#__PURE__*/__webpack_require__.n(_acf_select2_js__WEBPACK_IMPORTED_MODULE_33__);
/* harmony import */ var _acf_tinymce_js__WEBPACK_IMPORTED_MODULE_34__ = __webpack_require__(/*! ./_acf-tinymce.js */ "./src/advanced-custom-fields-pro/assets/src/js/_acf-tinymce.js");
/* harmony import */ var _acf_tinymce_js__WEBPACK_IMPORTED_MODULE_34___default = /*#__PURE__*/__webpack_require__.n(_acf_tinymce_js__WEBPACK_IMPORTED_MODULE_34__);
/* harmony import */ var _acf_validation_js__WEBPACK_IMPORTED_MODULE_35__ = __webpack_require__(/*! ./_acf-validation.js */ "./src/advanced-custom-fields-pro/assets/src/js/_acf-validation.js");
/* harmony import */ var _acf_validation_js__WEBPACK_IMPORTED_MODULE_35___default = /*#__PURE__*/__webpack_require__.n(_acf_validation_js__WEBPACK_IMPORTED_MODULE_35__);
/* harmony import */ var _acf_helpers_js__WEBPACK_IMPORTED_MODULE_36__ = __webpack_require__(/*! ./_acf-helpers.js */ "./src/advanced-custom-fields-pro/assets/src/js/_acf-helpers.js");
/* harmony import */ var _acf_helpers_js__WEBPACK_IMPORTED_MODULE_36___default = /*#__PURE__*/__webpack_require__.n(_acf_helpers_js__WEBPACK_IMPORTED_MODULE_36__);
/* harmony import */ var _acf_compatibility_js__WEBPACK_IMPORTED_MODULE_37__ = __webpack_require__(/*! ./_acf-compatibility.js */ "./src/advanced-custom-fields-pro/assets/src/js/_acf-compatibility.js");
/* harmony import */ var _acf_compatibility_js__WEBPACK_IMPORTED_MODULE_37___default = /*#__PURE__*/__webpack_require__.n(_acf_compatibility_js__WEBPACK_IMPORTED_MODULE_37__);






































})();

/******/ })()
;
//# sourceMappingURL=acf-input.js.map