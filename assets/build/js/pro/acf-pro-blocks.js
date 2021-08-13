"use strict";

function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _unsupportedIterableToArray(arr, i) || _nonIterableRest(); }

function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function _iterableToArrayLimit(arr, i) { var _i = arr == null ? null : typeof Symbol !== "undefined" && arr[Symbol.iterator] || arr["@@iterator"]; if (_i == null) return; var _arr = []; var _n = true; var _d = false; var _s, _e; try { for (_i = _i.call(arr); !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }

function _get(target, property, receiver) { if (typeof Reflect !== "undefined" && Reflect.get) { _get = Reflect.get; } else { _get = function _get(target, property, receiver) { var base = _superPropBase(target, property); if (!base) return; var desc = Object.getOwnPropertyDescriptor(base, property); if (desc.get) { return desc.get.call(receiver); } return desc.value; }; } return _get(target, property, receiver || target); }

function _superPropBase(object, property) { while (!Object.prototype.hasOwnProperty.call(object, property)) { object = _getPrototypeOf(object); if (object === null) break; } return object; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function"); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }); if (superClass) _setPrototypeOf(subClass, superClass); }

function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }

function _createSuper(Derived) { var hasNativeReflectConstruct = _isNativeReflectConstruct(); return function _createSuperInternal() { var Super = _getPrototypeOf(Derived), result; if (hasNativeReflectConstruct) { var NewTarget = _getPrototypeOf(this).constructor; result = Reflect.construct(Super, arguments, NewTarget); } else { result = Super.apply(this, arguments); } return _possibleConstructorReturn(this, result); }; }

function _possibleConstructorReturn(self, call) { if (call && (_typeof(call) === "object" || typeof call === "function")) { return call; } return _assertThisInitialized(self); }

function _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return self; }

function _isNativeReflectConstruct() { if (typeof Reflect === "undefined" || !Reflect.construct) return false; if (Reflect.construct.sham) return false; if (typeof Proxy === "function") return true; try { Boolean.prototype.valueOf.call(Reflect.construct(Boolean, [], function () {})); return true; } catch (e) { return false; } }

function _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }

function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) { symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); } keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { ownKeys(Object(source), true).forEach(function (key) { _defineProperty(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

(function ($, undefined) {
  acf.jsxNameReplacements = {
    "accent-height": "accentHeight",
    "accentheight": "accentHeight",
    "accept-charset": "acceptCharset",
    "acceptcharset": "acceptCharset",
    "accesskey": "accessKey",
    "alignment-baseline": "alignmentBaseline",
    "alignmentbaseline": "alignmentBaseline",
    "allowedblocks": "allowedBlocks",
    "allowfullscreen": "allowFullScreen",
    "allowreorder": "allowReorder",
    "arabic-form": "arabicForm",
    "arabicform": "arabicForm",
    "attributename": "attributeName",
    "attributetype": "attributeType",
    "autocapitalize": "autoCapitalize",
    "autocomplete": "autoComplete",
    "autocorrect": "autoCorrect",
    "autofocus": "autoFocus",
    "autoplay": "autoPlay",
    "autoreverse": "autoReverse",
    "autosave": "autoSave",
    "basefrequency": "baseFrequency",
    "baseline-shift": "baselineShift",
    "baselineshift": "baselineShift",
    "baseprofile": "baseProfile",
    "calcmode": "calcMode",
    "cap-height": "capHeight",
    "capheight": "capHeight",
    "cellpadding": "cellPadding",
    "cellspacing": "cellSpacing",
    "charset": "charSet",
    "class": "className",
    "classid": "classID",
    "classname": "className",
    "clip-path": "clipPath",
    "clip-rule": "clipRule",
    "clippath": "clipPath",
    "clippathunits": "clipPathUnits",
    "cliprule": "clipRule",
    "color-interpolation": "colorInterpolation",
    "color-interpolation-filters": "colorInterpolationFilters",
    "color-profile": "colorProfile",
    "color-rendering": "colorRendering",
    "colorinterpolation": "colorInterpolation",
    "colorinterpolationfilters": "colorInterpolationFilters",
    "colorprofile": "colorProfile",
    "colorrendering": "colorRendering",
    "colspan": "colSpan",
    "contenteditable": "contentEditable",
    "contentscripttype": "contentScriptType",
    "contentstyletype": "contentStyleType",
    "contextmenu": "contextMenu",
    "controlslist": "controlsList",
    "crossorigin": "crossOrigin",
    "dangerouslysetinnerhtml": "dangerouslySetInnerHTML",
    "datetime": "dateTime",
    "defaultchecked": "defaultChecked",
    "defaultvalue": "defaultValue",
    "diffuseconstant": "diffuseConstant",
    "disablepictureinpicture": "disablePictureInPicture",
    "disableremoteplayback": "disableRemotePlayback",
    "dominant-baseline": "dominantBaseline",
    "dominantbaseline": "dominantBaseline",
    "edgemode": "edgeMode",
    "enable-background": "enableBackground",
    "enablebackground": "enableBackground",
    "enctype": "encType",
    "enterkeyhint": "enterKeyHint",
    "externalresourcesrequired": "externalResourcesRequired",
    "fill-opacity": "fillOpacity",
    "fill-rule": "fillRule",
    "fillopacity": "fillOpacity",
    "fillrule": "fillRule",
    "filterres": "filterRes",
    "filterunits": "filterUnits",
    "flood-color": "floodColor",
    "flood-opacity": "floodOpacity",
    "floodcolor": "floodColor",
    "floodopacity": "floodOpacity",
    "font-family": "fontFamily",
    "font-size": "fontSize",
    "font-size-adjust": "fontSizeAdjust",
    "font-stretch": "fontStretch",
    "font-style": "fontStyle",
    "font-variant": "fontVariant",
    "font-weight": "fontWeight",
    "fontfamily": "fontFamily",
    "fontsize": "fontSize",
    "fontsizeadjust": "fontSizeAdjust",
    "fontstretch": "fontStretch",
    "fontstyle": "fontStyle",
    "fontvariant": "fontVariant",
    "fontweight": "fontWeight",
    "for": "htmlFor",
    "formaction": "formAction",
    "formenctype": "formEncType",
    "formmethod": "formMethod",
    "formnovalidate": "formNoValidate",
    "formtarget": "formTarget",
    "frameborder": "frameBorder",
    "glyph-name": "glyphName",
    "glyph-orientation-horizontal": "glyphOrientationHorizontal",
    "glyph-orientation-vertical": "glyphOrientationVertical",
    "glyphname": "glyphName",
    "glyphorientationhorizontal": "glyphOrientationHorizontal",
    "glyphorientationvertical": "glyphOrientationVertical",
    "glyphref": "glyphRef",
    "gradienttransform": "gradientTransform",
    "gradientunits": "gradientUnits",
    "horiz-adv-x": "horizAdvX",
    "horiz-origin-x": "horizOriginX",
    "horizadvx": "horizAdvX",
    "horizoriginx": "horizOriginX",
    "hreflang": "hrefLang",
    "htmlfor": "htmlFor",
    "http-equiv": "httpEquiv",
    "httpequiv": "httpEquiv",
    "image-rendering": "imageRendering",
    "imagerendering": "imageRendering",
    "innerhtml": "innerHTML",
    "inputmode": "inputMode",
    "itemid": "itemID",
    "itemprop": "itemProp",
    "itemref": "itemRef",
    "itemscope": "itemScope",
    "itemtype": "itemType",
    "kernelmatrix": "kernelMatrix",
    "kernelunitlength": "kernelUnitLength",
    "keyparams": "keyParams",
    "keypoints": "keyPoints",
    "keysplines": "keySplines",
    "keytimes": "keyTimes",
    "keytype": "keyType",
    "lengthadjust": "lengthAdjust",
    "letter-spacing": "letterSpacing",
    "letterspacing": "letterSpacing",
    "lighting-color": "lightingColor",
    "lightingcolor": "lightingColor",
    "limitingconeangle": "limitingConeAngle",
    "marginheight": "marginHeight",
    "marginwidth": "marginWidth",
    "marker-end": "markerEnd",
    "marker-mid": "markerMid",
    "marker-start": "markerStart",
    "markerend": "markerEnd",
    "markerheight": "markerHeight",
    "markermid": "markerMid",
    "markerstart": "markerStart",
    "markerunits": "markerUnits",
    "markerwidth": "markerWidth",
    "maskcontentunits": "maskContentUnits",
    "maskunits": "maskUnits",
    "maxlength": "maxLength",
    "mediagroup": "mediaGroup",
    "minlength": "minLength",
    "nomodule": "noModule",
    "novalidate": "noValidate",
    "numoctaves": "numOctaves",
    "overline-position": "overlinePosition",
    "overline-thickness": "overlineThickness",
    "overlineposition": "overlinePosition",
    "overlinethickness": "overlineThickness",
    "paint-order": "paintOrder",
    "paintorder": "paintOrder",
    "panose-1": "panose1",
    "pathlength": "pathLength",
    "patterncontentunits": "patternContentUnits",
    "patterntransform": "patternTransform",
    "patternunits": "patternUnits",
    "playsinline": "playsInline",
    "pointer-events": "pointerEvents",
    "pointerevents": "pointerEvents",
    "pointsatx": "pointsAtX",
    "pointsaty": "pointsAtY",
    "pointsatz": "pointsAtZ",
    "preservealpha": "preserveAlpha",
    "preserveaspectratio": "preserveAspectRatio",
    "primitiveunits": "primitiveUnits",
    "radiogroup": "radioGroup",
    "readonly": "readOnly",
    "referrerpolicy": "referrerPolicy",
    "refx": "refX",
    "refy": "refY",
    "rendering-intent": "renderingIntent",
    "renderingintent": "renderingIntent",
    "repeatcount": "repeatCount",
    "repeatdur": "repeatDur",
    "requiredextensions": "requiredExtensions",
    "requiredfeatures": "requiredFeatures",
    "rowspan": "rowSpan",
    "shape-rendering": "shapeRendering",
    "shaperendering": "shapeRendering",
    "specularconstant": "specularConstant",
    "specularexponent": "specularExponent",
    "spellcheck": "spellCheck",
    "spreadmethod": "spreadMethod",
    "srcdoc": "srcDoc",
    "srclang": "srcLang",
    "srcset": "srcSet",
    "startoffset": "startOffset",
    "stddeviation": "stdDeviation",
    "stitchtiles": "stitchTiles",
    "stop-color": "stopColor",
    "stop-opacity": "stopOpacity",
    "stopcolor": "stopColor",
    "stopopacity": "stopOpacity",
    "strikethrough-position": "strikethroughPosition",
    "strikethrough-thickness": "strikethroughThickness",
    "strikethroughposition": "strikethroughPosition",
    "strikethroughthickness": "strikethroughThickness",
    "stroke-dasharray": "strokeDasharray",
    "stroke-dashoffset": "strokeDashoffset",
    "stroke-linecap": "strokeLinecap",
    "stroke-linejoin": "strokeLinejoin",
    "stroke-miterlimit": "strokeMiterlimit",
    "stroke-opacity": "strokeOpacity",
    "stroke-width": "strokeWidth",
    "strokedasharray": "strokeDasharray",
    "strokedashoffset": "strokeDashoffset",
    "strokelinecap": "strokeLinecap",
    "strokelinejoin": "strokeLinejoin",
    "strokemiterlimit": "strokeMiterlimit",
    "strokeopacity": "strokeOpacity",
    "strokewidth": "strokeWidth",
    "suppresscontenteditablewarning": "suppressContentEditableWarning",
    "suppresshydrationwarning": "suppressHydrationWarning",
    "surfacescale": "surfaceScale",
    "systemlanguage": "systemLanguage",
    "tabindex": "tabIndex",
    "tablevalues": "tableValues",
    "targetx": "targetX",
    "targety": "targetY",
    "templatelock": "templateLock",
    "text-anchor": "textAnchor",
    "text-decoration": "textDecoration",
    "text-rendering": "textRendering",
    "textanchor": "textAnchor",
    "textdecoration": "textDecoration",
    "textlength": "textLength",
    "textrendering": "textRendering",
    "underline-position": "underlinePosition",
    "underline-thickness": "underlineThickness",
    "underlineposition": "underlinePosition",
    "underlinethickness": "underlineThickness",
    "unicode-bidi": "unicodeBidi",
    "unicode-range": "unicodeRange",
    "unicodebidi": "unicodeBidi",
    "unicoderange": "unicodeRange",
    "units-per-em": "unitsPerEm",
    "unitsperem": "unitsPerEm",
    "usemap": "useMap",
    "v-alphabetic": "vAlphabetic",
    "v-hanging": "vHanging",
    "v-ideographic": "vIdeographic",
    "v-mathematical": "vMathematical",
    "valphabetic": "vAlphabetic",
    "vector-effect": "vectorEffect",
    "vectoreffect": "vectorEffect",
    "vert-adv-y": "vertAdvY",
    "vert-origin-x": "vertOriginX",
    "vert-origin-y": "vertOriginY",
    "vertadvy": "vertAdvY",
    "vertoriginx": "vertOriginX",
    "vertoriginy": "vertOriginY",
    "vhanging": "vHanging",
    "videographic": "vIdeographic",
    "viewbox": "viewBox",
    "viewtarget": "viewTarget",
    "vmathematical": "vMathematical",
    "word-spacing": "wordSpacing",
    "wordspacing": "wordSpacing",
    "writing-mode": "writingMode",
    "writingmode": "writingMode",
    "x-height": "xHeight",
    "xchannelselector": "xChannelSelector",
    "xheight": "xHeight",
    "xlink:actuate": "xlinkActuate",
    "xlink:arcrole": "xlinkArcrole",
    "xlink:href": "xlinkHref",
    "xlink:role": "xlinkRole",
    "xlink:show": "xlinkShow",
    "xlink:title": "xlinkTitle",
    "xlink:type": "xlinkType",
    "xlinkactuate": "xlinkActuate",
    "xlinkarcrole": "xlinkArcrole",
    "xlinkhref": "xlinkHref",
    "xlinkrole": "xlinkRole",
    "xlinkshow": "xlinkShow",
    "xlinktitle": "xlinkTitle",
    "xlinktype": "xlinkType",
    "xml:base": "xmlBase",
    "xml:lang": "xmlLang",
    "xml:space": "xmlSpace",
    "xmlbase": "xmlBase",
    "xmllang": "xmlLang",
    "xmlns:xlink": "xmlnsXlink",
    "xmlnsxlink": "xmlnsXlink",
    "xmlspace": "xmlSpace",
    "ychannelselector": "yChannelSelector",
    "zoomandpan": "zoomAndPan"
  };
})(jQuery);

(function ($, undefined) {
  // Dependencies.
  var _wp$blockEditor = wp.blockEditor,
      BlockControls = _wp$blockEditor.BlockControls,
      InspectorControls = _wp$blockEditor.InspectorControls,
      InnerBlocks = _wp$blockEditor.InnerBlocks;
  var _wp$components = wp.components,
      Toolbar = _wp$components.Toolbar,
      IconButton = _wp$components.IconButton,
      Placeholder = _wp$components.Placeholder,
      Spinner = _wp$components.Spinner;
  var Fragment = wp.element.Fragment;
  var _React = React,
      Component = _React.Component;
  var withSelect = wp.data.withSelect;
  var createHigherOrderComponent = wp.compose.createHigherOrderComponent;
  /**
   * Storage for registered block types.
   *
   * @since 5.8.0
   * @var object
   */

  var blockTypes = {};
  /**
   * Returns a block type for the given name.
   *
   * @date	20/2/19
   * @since	5.8.0
   *
   * @param	string name The block name.
   * @return	(object|false)
   */

  function getBlockType(name) {
    return blockTypes[name] || false;
  }
  /**
   * Returns true if a block exists for the given name.
   *
   * @date	20/2/19
   * @since	5.8.0
   *
   * @param	string name The block name.
   * @return	bool
   */


  function isBlockType(name) {
    return !!blockTypes[name];
  }
  /**
   * Returns true if the provided block is new.
   *
   * @date	31/07/2020
   * @since	5.9.0
   *
   * @param	object props The block props.
   * @return	bool
   */


  function isNewBlock(props) {
    return !props.attributes.id;
  }
  /**
   * Returns true if the provided block is a duplicate:
   * True when there are is another block with the same "id", but a different "clientId".
   * 
   * @date	31/07/2020
   * @since	5.9.0
   *
   * @param	object props The block props.
   * @return	bool
   */


  function isDuplicateBlock(props) {
    return getBlocks().filter(function (block) {
      return block.attributes.id === props.attributes.id;
    }).filter(function (block) {
      return block.clientId !== props.clientId;
    }).length;
  }
  /**
   * Registers a block type.
   *
   * @date	19/2/19
   * @since	5.8.0
   *
   * @param	object blockType The block type settings localized from PHP.
   * @return	object The result from wp.blocks.registerBlockType().
   */


  function registerBlockType(blockType) {
    // Bail ealry if is excluded post_type.
    var allowedTypes = blockType.post_types || [];

    if (allowedTypes.length) {
      // Always allow block to appear on "Edit reusable Block" screen.
      allowedTypes.push('wp_block'); // Check post type.

      var postType = acf.get('postType');

      if (allowedTypes.indexOf(postType) === -1) {
        return false;
      }
    } // Handle svg HTML.


    if (typeof blockType.icon === 'string' && blockType.icon.substr(0, 4) === '<svg') {
      var iconHTML = blockType.icon;
      blockType.icon = /*#__PURE__*/React.createElement(Div, null, iconHTML);
    } // Remove icon if empty to allow for default "block".
    // Avoids JS error preventing block from being registered.


    if (!blockType.icon) {
      delete blockType.icon;
    } // Check category exists and fallback to "common".


    var category = wp.blocks.getCategories().filter(function (cat) {
      return cat.slug === blockType.category;
    }).pop();

    if (!category) {
      //console.warn( `The block "${blockType.name}" is registered with an unknown category "${blockType.category}".` );
      blockType.category = 'common';
    } // Define block type attributes.
    // Leave default undefined to allow WP to serialize attributes in HTML comments.
    // See https://github.com/WordPress/gutenberg/issues/7342


    var attributes = {
      id: {
        type: 'string'
      },
      name: {
        type: 'string'
      },
      data: {
        type: 'object'
      },
      align: {
        type: 'string'
      },
      mode: {
        type: 'string'
      }
    }; // Append edit and save functions.

    var ThisBlockEdit = BlockEdit;
    var ThisBlockSave = BlockSave; // Apply align_text functionality.

    if (blockType.supports.align_text) {
      attributes = withAlignTextAttributes(attributes);
      ThisBlockEdit = withAlignTextComponent(ThisBlockEdit, blockType);
    } // Apply align_content functionality.


    if (blockType.supports.align_content) {
      attributes = withAlignContentAttributes(attributes);
      ThisBlockEdit = withAlignContentComponent(ThisBlockEdit, blockType);
    } // Merge in block settings.


    blockType = acf.parseArgs(blockType, {
      title: '',
      name: '',
      category: '',
      attributes: attributes,
      edit: function edit(props) {
        return /*#__PURE__*/React.createElement(ThisBlockEdit, props);
      },
      save: function save(props) {
        return /*#__PURE__*/React.createElement(ThisBlockSave, props);
      }
    }); // Add to storage.

    blockTypes[blockType.name] = blockType; // Register with WP.

    var result = wp.blocks.registerBlockType(blockType.name, blockType); // Fix bug in 'core/anchor/attribute' filter overwriting attribute.
    // See https://github.com/WordPress/gutenberg/issues/15240

    if (result.attributes.anchor) {
      result.attributes.anchor = {
        type: 'string'
      };
    } // Return result.


    return result;
  }
  /**
   * Returns the wp.data.select() response with backwards compatibility.
   *
   * @date	17/06/2020
   * @since	5.9.0
   *
   * @param	string selector The selector name.
   * @return	mixed
   */


  function select(selector) {
    if (selector === 'core/block-editor') {
      return wp.data.select('core/block-editor') || wp.data.select('core/editor');
    }

    return wp.data.select(selector);
  }
  /**
   * Returns the wp.data.dispatch() response with backwards compatibility.
   *
   * @date	17/06/2020
   * @since	5.9.0
   *
   * @param	string selector The selector name.
   * @return	mixed
   */


  function dispatch(selector) {
    return wp.data.dispatch(selector);
  }
  /**
   * Returns an array of all blocks for the given args.
   *
   * @date	27/2/19
   * @since	5.7.13
   *
   * @param	object args An object of key=>value pairs used to filter results.
   * @return	array.
   */


  function getBlocks(args) {
    // Get all blocks (avoid deprecated warning).
    var blocks = select('core/block-editor').getBlocks(); // Append innerBlocks.

    var i = 0;

    while (i < blocks.length) {
      blocks = blocks.concat(blocks[i].innerBlocks);
      i++;
    } // Loop over args and filter.


    for (var k in args) {
      blocks = blocks.filter(function (block) {
        return block.attributes[k] === args[k];
      });
    } // Return results.


    return blocks;
  } // Data storage for AJAX requests.


  var ajaxQueue = {};
  /**
   * Fetches a JSON result from the AJAX API.
   *
   * @date	28/2/19
   * @since	5.7.13
   *
   * @param	object block The block props.
   * @query	object The query args used in AJAX callback.
   * @return	object The AJAX promise.
   */

  function fetchBlock(args) {
    var _args$attributes = args.attributes,
        attributes = _args$attributes === void 0 ? {} : _args$attributes,
        _args$query = args.query,
        query = _args$query === void 0 ? {} : _args$query,
        _args$delay = args.delay,
        delay = _args$delay === void 0 ? 0 : _args$delay; // Use storage or default data.

    var id = attributes.id;
    var data = ajaxQueue[id] || {
      query: {},
      timeout: false,
      promise: $.Deferred()
    }; // Append query args to storage.

    data.query = _objectSpread(_objectSpread({}, data.query), query); // Set fresh timeout.

    clearTimeout(data.timeout);
    data.timeout = setTimeout(function () {
      $.ajax({
        url: acf.get('ajaxurl'),
        dataType: 'json',
        type: 'post',
        cache: false,
        data: acf.prepareForAjax({
          action: 'acf/ajax/fetch-block',
          block: JSON.stringify(attributes),
          query: data.query
        })
      }).always(function () {
        // Clean up queue after AJAX request is complete.
        ajaxQueue[id] = null;
      }).done(function () {
        data.promise.resolve.apply(this, arguments);
      }).fail(function () {
        data.promise.reject.apply(this, arguments);
      });
    }, delay); // Update storage.

    ajaxQueue[id] = data; // Return promise.

    return data.promise;
  }
  /**
   * Returns true if both object are the same.
   *
   * @date	19/05/2020
   * @since	5.9.0
   *
   * @param	object obj1
   * @param	object obj2
   * @return	bool
   */


  function compareObjects(obj1, obj2) {
    return JSON.stringify(obj1) === JSON.stringify(obj2);
  }
  /**
   * Converts HTML into a React element.
   *
   * @date	19/05/2020
   * @since	5.9.0
   *
   * @param	string html The HTML to convert.
   * @return	object Result of React.createElement().
   */


  acf.parseJSX = function (html) {
    return parseNode($(html)[0]);
  };
  /**
   * Converts a DOM node into a React element.
   *
   * @date	19/05/2020
   * @since	5.9.0
   *
   * @param	DOM node The DOM node.
   * @return	object Result of React.createElement().
   */


  function parseNode(node) {
    // Get node name.
    var nodeName = parseNodeName(node.nodeName.toLowerCase());

    if (!nodeName) {
      return null;
    } // Get node attributes in React friendly format.


    var nodeAttrs = {};
    acf.arrayArgs(node.attributes).map(parseNodeAttr).forEach(function (attr) {
      nodeAttrs[attr.name] = attr.value;
    }); // Define args for React.createElement().

    var args = [nodeName, nodeAttrs];
    acf.arrayArgs(node.childNodes).forEach(function (child) {
      if (child instanceof Text) {
        var text = child.textContent;

        if (text) {
          args.push(text);
        }
      } else {
        args.push(parseNode(child));
      }
    }); // Return element.

    return React.createElement.apply(this, args);
  }

  ;
  /**
   * Converts a node or attribute name into it's JSX compliant name
   *
   * @date     05/07/2021
   * @since    5.9.8
   *
   * @param    string name The node or attribute name.
   * @returns  string
   */

  function getJSXName(name) {
    var replacement = acf.isget(acf, 'jsxNameReplacements', name);
    if (replacement) return replacement;
    return name;
  }
  /**
   * Converts the given name into a React friendly name or component.
   *
   * @date	19/05/2020
   * @since	5.9.0
   *
   * @param	string name The node name in lowercase.
   * @return	mixed
   */


  function parseNodeName(name) {
    switch (name) {
      case 'innerblocks':
        return InnerBlocks;

      case 'script':
        return Script;

      case '#comment':
        return null;

      default:
        // Replace names for JSX counterparts.
        name = getJSXName(name);
    }

    return name;
  }
  /**
   * Converts the given attribute into a React friendly name and value object.
   *
   * @date	19/05/2020
   * @since	5.9.0
   *
   * @param	obj nodeAttr The node attribute.
   * @return	obj
   */


  function parseNodeAttr(nodeAttr) {
    var name = nodeAttr.name;
    var value = nodeAttr.value;

    switch (name) {
      // Class.
      case 'class':
        name = 'className';
        break;
      // Style.

      case 'style':
        var css = {};
        value.split(';').forEach(function (s) {
          var pos = s.indexOf(':');

          if (pos > 0) {
            var ruleName = s.substr(0, pos).trim();
            var ruleValue = s.substr(pos + 1).trim(); // Rename core properties, but not CSS variables.

            if (ruleName.charAt(0) !== '-') {
              ruleName = acf.strCamelCase(ruleName);
            }

            css[ruleName] = ruleValue;
          }
        });
        value = css;
        break;
      // Default.

      default:
        // No formatting needed for "data-x" attributes.
        if (name.indexOf('data-') === 0) {
          break;
        } // Replace names for JSX counterparts.


        name = getJSXName(name); // Convert JSON values.

        var c1 = value.charAt(0);

        if (c1 === '[' || c1 === '{') {
          value = JSON.parse(value);
        } // Convert bool values.


        if (value === 'true' || value === 'false') {
          value = value === 'true';
        }

        break;
    }

    return {
      name: name,
      value: value
    };
  }
  /**
   * Higher Order Component used to set default block attribute values.
   * 
   * By modifying block attributes directly, instead of defining defaults in registerBlockType(), 
   * WordPress will include them always within the saved block serialized JSON.
   *
   * @date	31/07/2020
   * @since	5.9.0
   *
   * @param	Component BlockListBlock The BlockListBlock Component.
   * @return	Component
   */


  var withDefaultAttributes = createHigherOrderComponent(function (BlockListBlock) {
    return /*#__PURE__*/function (_Component) {
      _inherits(WrappedBlockEdit, _Component);

      var _super = _createSuper(WrappedBlockEdit);

      function WrappedBlockEdit(props) {
        var _this;

        _classCallCheck(this, WrappedBlockEdit);

        _this = _super.call(this, props); // Extract vars.

        var _this$props = _this.props,
            name = _this$props.name,
            attributes = _this$props.attributes; // Only run on ACF Blocks.

        var blockType = getBlockType(name);

        if (!blockType) {
          return _possibleConstructorReturn(_this);
        } // Set unique ID and default attributes for newly added blocks.


        if (isNewBlock(props)) {
          attributes.id = acf.uniqid('block_');

          for (var attribute in blockType.attributes) {
            if (attributes[attribute] === undefined && blockType[attribute] !== undefined) {
              attributes[attribute] = blockType[attribute];
            }
          }

          return _possibleConstructorReturn(_this);
        } // Generate new ID for duplicated blocks.


        if (isDuplicateBlock(props)) {
          attributes.id = acf.uniqid('block_');
          return _possibleConstructorReturn(_this);
        }

        return _this;
      }

      _createClass(WrappedBlockEdit, [{
        key: "render",
        value: function render() {
          return /*#__PURE__*/React.createElement(BlockListBlock, this.props);
        }
      }]);

      return WrappedBlockEdit;
    }(Component);
  }, 'withDefaultAttributes');
  wp.hooks.addFilter('editor.BlockListBlock', 'acf/with-default-attributes', withDefaultAttributes);
  /**
   * The BlockSave functional component.
   *
   * @date	08/07/2020
   * @since	5.9.0
   */

  function BlockSave() {
    return /*#__PURE__*/React.createElement(InnerBlocks.Content, null);
  }
  /**
   * The BlockEdit component.
   *
   * @date	19/2/19
   * @since	5.7.12
   */


  var BlockEdit = /*#__PURE__*/function (_Component2) {
    _inherits(BlockEdit, _Component2);

    var _super2 = _createSuper(BlockEdit);

    function BlockEdit(props) {
      var _this2;

      _classCallCheck(this, BlockEdit);

      _this2 = _super2.call(this, props);

      _this2.setup();

      return _this2;
    }

    _createClass(BlockEdit, [{
      key: "setup",
      value: function setup() {
        var _this$props2 = this.props,
            name = _this$props2.name,
            attributes = _this$props2.attributes;
        var blockType = getBlockType(name); // Restrict current mode.

        function restrictMode(modes) {
          if (modes.indexOf(attributes.mode) === -1) {
            attributes.mode = modes[0];
          }
        }

        switch (blockType.mode) {
          case 'edit':
            restrictMode(['edit', 'preview']);
            break;

          case 'preview':
            restrictMode(['preview', 'edit']);
            break;

          default:
            restrictMode(['auto']);
            break;
        }
      }
    }, {
      key: "render",
      value: function render() {
        var _this$props3 = this.props,
            name = _this$props3.name,
            attributes = _this$props3.attributes,
            setAttributes = _this$props3.setAttributes;
        var mode = attributes.mode;
        var blockType = getBlockType(name); // Show toggle only for edit/preview modes.

        var showToggle = blockType.supports.mode;

        if (mode === 'auto') {
          showToggle = false;
        } // Configure toggle variables.


        var toggleText = mode === 'preview' ? acf.__('Switch to Edit') : acf.__('Switch to Preview');
        var toggleIcon = mode === 'preview' ? 'edit' : 'welcome-view-site';

        function toggleMode() {
          setAttributes({
            mode: mode === 'preview' ? 'edit' : 'preview'
          });
        } // Return template.


        return /*#__PURE__*/React.createElement(Fragment, null, /*#__PURE__*/React.createElement(BlockControls, null, showToggle && /*#__PURE__*/React.createElement(Toolbar, null, /*#__PURE__*/React.createElement(IconButton, {
          className: "components-icon-button components-toolbar__control",
          label: toggleText,
          icon: toggleIcon,
          onClick: toggleMode
        }))), /*#__PURE__*/React.createElement(InspectorControls, null, mode === 'preview' && /*#__PURE__*/React.createElement("div", {
          className: "acf-block-component acf-block-panel"
        }, /*#__PURE__*/React.createElement(BlockForm, this.props))), /*#__PURE__*/React.createElement(BlockBody, this.props));
      }
    }]);

    return BlockEdit;
  }(Component);
  /**
   * The BlockBody component.
   *
   * @date	19/2/19
   * @since	5.7.12
   */


  var _BlockBody = /*#__PURE__*/function (_Component3) {
    _inherits(_BlockBody, _Component3);

    var _super3 = _createSuper(_BlockBody);

    function _BlockBody() {
      _classCallCheck(this, _BlockBody);

      return _super3.apply(this, arguments);
    }

    _createClass(_BlockBody, [{
      key: "render",
      value: function render() {
        var _this$props4 = this.props,
            attributes = _this$props4.attributes,
            isSelected = _this$props4.isSelected;
        var mode = attributes.mode;
        return /*#__PURE__*/React.createElement("div", {
          className: "acf-block-component acf-block-body"
        }, mode === 'auto' && isSelected ? /*#__PURE__*/React.createElement(BlockForm, this.props) : mode === 'auto' && !isSelected ? /*#__PURE__*/React.createElement(BlockPreview, this.props) : mode === 'preview' ? /*#__PURE__*/React.createElement(BlockPreview, this.props) : /*#__PURE__*/React.createElement(BlockForm, this.props));
      }
    }]);

    return _BlockBody;
  }(Component); // Append blockIndex to component props.


  var BlockBody = withSelect(function (select, ownProps) {
    var clientId = ownProps.clientId; // Use optional rootClientId to allow discoverability of child blocks.

    var rootClientId = select('core/block-editor').getBlockRootClientId(clientId);
    var index = select('core/block-editor').getBlockIndex(clientId, rootClientId);
    return {
      index: index
    };
  })(_BlockBody);
  /**
   * A react component to append HTMl.
   *
   * @date	19/2/19
   * @since	5.7.12
   *
   * @param	string children The html to insert.
   * @return	void
   */

  var Div = /*#__PURE__*/function (_Component4) {
    _inherits(Div, _Component4);

    var _super4 = _createSuper(Div);

    function Div() {
      _classCallCheck(this, Div);

      return _super4.apply(this, arguments);
    }

    _createClass(Div, [{
      key: "render",
      value: function render() {
        return /*#__PURE__*/React.createElement("div", {
          dangerouslySetInnerHTML: {
            __html: this.props.children
          }
        });
      }
    }]);

    return Div;
  }(Component);
  /**
   * A react Component for inline scripts.
   * 
   * This Component uses a combination of React references and jQuery to append the
   * inline <script> HTML each time the component is rendered.
   *
   * @date	29/05/2020
   * @since	5.9.0
   *
   * @param	type Var Description.
   * @return	type Description.
   */


  var Script = /*#__PURE__*/function (_Component5) {
    _inherits(Script, _Component5);

    var _super5 = _createSuper(Script);

    function Script() {
      _classCallCheck(this, Script);

      return _super5.apply(this, arguments);
    }

    _createClass(Script, [{
      key: "render",
      value: function render() {
        var _this3 = this;

        return /*#__PURE__*/React.createElement("div", {
          ref: function ref(el) {
            return _this3.el = el;
          }
        });
      }
    }, {
      key: "setHTML",
      value: function setHTML(html) {
        $(this.el).html("<script>".concat(html, "</script>"));
      }
    }, {
      key: "componentDidUpdate",
      value: function componentDidUpdate() {
        this.setHTML(this.props.children);
      }
    }, {
      key: "componentDidMount",
      value: function componentDidMount() {
        this.setHTML(this.props.children);
      }
    }]);

    return Script;
  }(Component); // Data storage for DynamicHTML components.


  var store = {};
  /**
   * DynamicHTML Class.
   *
   * A react componenet to load and insert dynamic HTML.
   *
   * @date	19/2/19
   * @since	5.7.12
   *
   * @param	void
   * @return	void
   */

  var DynamicHTML = /*#__PURE__*/function (_Component6) {
    _inherits(DynamicHTML, _Component6);

    var _super6 = _createSuper(DynamicHTML);

    function DynamicHTML(props) {
      var _this4;

      _classCallCheck(this, DynamicHTML);

      _this4 = _super6.call(this, props); // Bind callbacks.

      _this4.setRef = _this4.setRef.bind(_assertThisInitialized(_this4)); // Define default props and call setup().

      _this4.id = '';
      _this4.el = false;
      _this4.subscribed = true;
      _this4.renderMethod = 'jQuery';

      _this4.setup(props); // Load state.


      _this4.loadState();

      return _this4;
    }

    _createClass(DynamicHTML, [{
      key: "setup",
      value: function setup(props) {// Do nothing.
      }
    }, {
      key: "fetch",
      value: function fetch() {// Do nothing.
      }
    }, {
      key: "loadState",
      value: function loadState() {
        this.state = store[this.id] || {};
      }
    }, {
      key: "setState",
      value: function setState(state) {
        store[this.id] = _objectSpread(_objectSpread({}, this.state), state); // Update component state if subscribed.
        // - Allows AJAX callback to update store without modifying state of an unmounted component.

        if (this.subscribed) {
          _get(_getPrototypeOf(DynamicHTML.prototype), "setState", this).call(this, state);
        }
      }
    }, {
      key: "setHtml",
      value: function setHtml(html) {
        html = html ? html.trim() : ''; // Bail early if html has not changed.

        if (html === this.state.html) {
          return;
        } // Update state.


        var state = {
          html: html
        };

        if (this.renderMethod === 'jsx') {
          state.jsx = acf.parseJSX(html);
          state.$el = $(this.el);
        } else {
          state.$el = $(html);
        }

        this.setState(state);
      }
    }, {
      key: "setRef",
      value: function setRef(el) {
        this.el = el;
      }
    }, {
      key: "render",
      value: function render() {
        // Render JSX.
        if (this.state.jsx) {
          return /*#__PURE__*/React.createElement("div", {
            ref: this.setRef
          }, this.state.jsx);
        } // Return HTML.


        return /*#__PURE__*/React.createElement("div", {
          ref: this.setRef
        }, /*#__PURE__*/React.createElement(Placeholder, null, /*#__PURE__*/React.createElement(Spinner, null)));
      }
    }, {
      key: "shouldComponentUpdate",
      value: function shouldComponentUpdate(nextProps, nextState) {
        if (nextProps.index !== this.props.index) {
          this.componentWillMove();
        }

        return nextState.html !== this.state.html;
      }
    }, {
      key: "display",
      value: function display(context) {
        // This method is called after setting new HTML and the Component render.
        // The jQuery render method simply needs to move $el into place.
        if (this.renderMethod === 'jQuery') {
          var $el = this.state.$el;
          var $prevParent = $el.parent();
          var $thisParent = $(this.el); // Move $el into place.

          $thisParent.html($el); // Special case for reusable blocks.
          // Multiple instances of the same reusable block share the same block id.
          // This causes all instances to share the same state (cool), which unfortunately
          // pulls $el back and forth between the last rendered reusable block.
          // This simple fix leaves a "clone" behind :)

          if ($prevParent.length && $prevParent[0] !== $thisParent[0]) {
            $prevParent.html($el.clone());
          }
        } // Call context specific method.


        switch (context) {
          case 'append':
            this.componentDidAppend();
            break;

          case 'remount':
            this.componentDidRemount();
            break;
        }
      }
    }, {
      key: "componentDidMount",
      value: function componentDidMount() {
        // Fetch on first load.
        if (this.state.html === undefined) {
          //console.log('componentDidMount', this.id);
          this.fetch(); // Or remount existing HTML.
        } else {
          this.display('remount');
        }
      }
    }, {
      key: "componentDidUpdate",
      value: function componentDidUpdate(prevProps, prevState) {
        // HTML has changed.
        this.display('append');
      }
    }, {
      key: "componentDidAppend",
      value: function componentDidAppend() {
        acf.doAction('append', this.state.$el);
      }
    }, {
      key: "componentWillUnmount",
      value: function componentWillUnmount() {
        acf.doAction('unmount', this.state.$el); // Unsubscribe this component from state.

        this.subscribed = false;
      }
    }, {
      key: "componentDidRemount",
      value: function componentDidRemount() {
        var _this5 = this;

        this.subscribed = true; // Use setTimeout to avoid incorrect timing of events.
        // React will unmount and mount components in DOM order.
        // This means a new component can be mounted before an old one is unmounted.
        // ACF shares $el across new/old components which is un-React-like.
        // This timout ensures that unmounting occurs before remounting.

        setTimeout(function () {
          acf.doAction('remount', _this5.state.$el);
        });
      }
    }, {
      key: "componentWillMove",
      value: function componentWillMove() {
        var _this6 = this;

        acf.doAction('unmount', this.state.$el);
        setTimeout(function () {
          acf.doAction('remount', _this6.state.$el);
        });
      }
    }]);

    return DynamicHTML;
  }(Component);
  /**
   * BlockForm Class.
   *
   * A react componenet to handle the block form.
   *
   * @date	19/2/19
   * @since	5.7.12
   *
   * @param	string id the block id.
   * @return	void
   */


  var BlockForm = /*#__PURE__*/function (_DynamicHTML) {
    _inherits(BlockForm, _DynamicHTML);

    var _super7 = _createSuper(BlockForm);

    function BlockForm() {
      _classCallCheck(this, BlockForm);

      return _super7.apply(this, arguments);
    }

    _createClass(BlockForm, [{
      key: "setup",
      value: function setup(props) {
        this.id = "BlockForm-".concat(props.attributes.id);
      }
    }, {
      key: "fetch",
      value: function fetch() {
        var _this7 = this;

        // Extract props.
        var attributes = this.props.attributes; // Request AJAX and update HTML on complete.

        fetchBlock({
          attributes: attributes,
          query: {
            form: true
          }
        }).done(function (json) {
          _this7.setHtml(json.data.form);
        });
      }
    }, {
      key: "componentDidAppend",
      value: function componentDidAppend() {
        _get(_getPrototypeOf(BlockForm.prototype), "componentDidAppend", this).call(this); // Extract props.


        var _this$props5 = this.props,
            attributes = _this$props5.attributes,
            setAttributes = _this$props5.setAttributes;
        var $el = this.state.$el; // Callback for updating block data.

        function serializeData() {
          var silent = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : false;
          var data = acf.serialize($el, "acf-".concat(attributes.id));

          if (silent) {
            attributes.data = data;
          } else {
            setAttributes({
              data: data
            });
          }
        } // Add events.


        var timeout = false;
        $el.on('change keyup', function () {
          clearTimeout(timeout);
          timeout = setTimeout(serializeData, 300);
        }); // Ensure newly added block is saved with data.
        // Do it silently to avoid triggering a preview render.

        if (!attributes.data) {
          serializeData(true);
        }
      }
    }]);

    return BlockForm;
  }(DynamicHTML);
  /**
   * BlockPreview Class.
   *
   * A react componenet to handle the block preview.
   *
   * @date	19/2/19
   * @since	5.7.12
   *
   * @param	string id the block id.
   * @return	void
   */


  var BlockPreview = /*#__PURE__*/function (_DynamicHTML2) {
    _inherits(BlockPreview, _DynamicHTML2);

    var _super8 = _createSuper(BlockPreview);

    function BlockPreview() {
      _classCallCheck(this, BlockPreview);

      return _super8.apply(this, arguments);
    }

    _createClass(BlockPreview, [{
      key: "setup",
      value: function setup(props) {
        this.id = "BlockPreview-".concat(props.attributes.id);
        var blockType = getBlockType(props.name);

        if (blockType.supports.jsx) {
          this.renderMethod = 'jsx';
        } //console.log('setup', this.id);

      }
    }, {
      key: "fetch",
      value: function fetch() {
        var _this8 = this;

        var args = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
        var _args$attributes2 = args.attributes,
            attributes = _args$attributes2 === void 0 ? this.props.attributes : _args$attributes2,
            _args$delay2 = args.delay,
            delay = _args$delay2 === void 0 ? 0 : _args$delay2; // Remember attributes used to fetch HTML.

        this.setState({
          prevAttributes: attributes
        }); // Try preloaded data first.

        if (this.state.html === undefined) {
          var preloadedBlocks = acf.get('preloadedBlocks');

          if (preloadedBlocks && preloadedBlocks[attributes.id]) {
            this.setHtml(preloadedBlocks[attributes.id]);
            return;
          }
        } // Request AJAX and update HTML on complete.


        fetchBlock({
          attributes: attributes,
          query: {
            preview: true
          },
          delay: delay
        }).done(function (json) {
          _this8.setHtml(json.data.preview);
        });
      }
    }, {
      key: "componentDidAppend",
      value: function componentDidAppend() {
        _get(_getPrototypeOf(BlockPreview.prototype), "componentDidAppend", this).call(this); // Extract props.


        var attributes = this.props.attributes;
        var $el = this.state.$el; // Generate action friendly type.

        var type = attributes.name.replace('acf/', ''); // Do action.

        acf.doAction('render_block_preview', $el, attributes);
        acf.doAction("render_block_preview/type=".concat(type), $el, attributes);
      }
    }, {
      key: "shouldComponentUpdate",
      value: function shouldComponentUpdate(nextProps, nextState) {
        var nextAttributes = nextProps.attributes;
        var thisAttributes = this.props.attributes; // Update preview if block data has changed.

        if (!compareObjects(nextAttributes, thisAttributes)) {
          var delay = 0; // Delay fetch when editing className or anchor to simulate conscistent logic to custom fields.

          if (nextAttributes.className !== thisAttributes.className) {
            delay = 300;
          }

          if (nextAttributes.anchor !== thisAttributes.anchor) {
            delay = 300;
          }

          this.fetch({
            attributes: nextAttributes,
            delay: delay
          });
        }

        return _get(_getPrototypeOf(BlockPreview.prototype), "shouldComponentUpdate", this).call(this, nextProps, nextState);
      }
    }, {
      key: "componentDidRemount",
      value: function componentDidRemount() {
        _get(_getPrototypeOf(BlockPreview.prototype), "componentDidRemount", this).call(this); // Update preview if data has changed since last render (changing from "edit" to "preview").


        if (!compareObjects(this.state.prevAttributes, this.props.attributes)) {
          //console.log('componentDidRemount', this.id);
          this.fetch();
        }
      }
    }]);

    return BlockPreview;
  }(DynamicHTML);
  /**
   * Initializes ACF Blocks logic and registration.
   *
   * @since 5.9.0
   */


  function initialize() {
    // Add support for WordPress versions before 5.2.
    if (!wp.blockEditor) {
      wp.blockEditor = wp.editor;
    } // Register block types.


    var blockTypes = acf.get('blockTypes');

    if (blockTypes) {
      blockTypes.map(registerBlockType);
    }
  } // Run the initialize callback during the "prepare" action.
  // This ensures that all localized data is available and that blocks are registered before the WP editor has been instantiated.


  acf.addAction('prepare', initialize);
  /**
   * Returns a valid vertical alignment.
   *
   * @date	07/08/2020
   * @since	5.9.0
   *
   * @param	string align A vertical alignment.
   * @return	string
   */

  function validateVerticalAlignment(align) {
    var ALIGNMENTS = ['top', 'center', 'bottom'];
    var DEFAULT = 'top';
    return ALIGNMENTS.includes(align) ? align : DEFAULT;
  }
  /**
   * Returns a valid horizontal alignment.
   *
   * @date	07/08/2020
   * @since	5.9.0
   *
   * @param	string align A horizontal alignment.
   * @return	string
   */


  function validateHorizontalAlignment(align) {
    var ALIGNMENTS = ['left', 'center', 'right'];
    var DEFAULT = acf.get('rtl') ? 'right' : 'left';
    return ALIGNMENTS.includes(align) ? align : DEFAULT;
  }
  /**
   * Returns a valid matrix alignment.
   *
   * Written for "upgrade-path" compatibility from vertical alignment to matrix alignment. 
   * 
   * @date	07/08/2020
   * @since	5.9.0
   *
   * @param	string align A matrix alignment.
   * @return	string
   */


  function validateMatrixAlignment(align) {
    var DEFAULT = 'center center';

    if (align) {
      var _align$split = align.split(' '),
          _align$split2 = _slicedToArray(_align$split, 2),
          y = _align$split2[0],
          x = _align$split2[1];

      return validateVerticalAlignment(y) + ' ' + validateHorizontalAlignment(x);
    }

    return DEFAULT;
  } // Dependencies.


  var _wp$blockEditor2 = wp.blockEditor,
      AlignmentToolbar = _wp$blockEditor2.AlignmentToolbar,
      BlockVerticalAlignmentToolbar = _wp$blockEditor2.BlockVerticalAlignmentToolbar;
  var BlockAlignmentMatrixToolbar = wp.blockEditor.__experimentalBlockAlignmentMatrixToolbar || wp.blockEditor.BlockAlignmentMatrixToolbar; // Gutenberg v10.x begins transition from Toolbar components to Control components.

  var BlockAlignmentMatrixControl = wp.blockEditor.__experimentalBlockAlignmentMatrixControl || wp.blockEditor.BlockAlignmentMatrixControl;
  /**
   * Appends extra attributes for block types that support align_content.
   *
   * @date	08/07/2020
   * @since	5.9.0
   *
   * @param	object attributes The block type attributes.
   * @return	object
   */

  function withAlignContentAttributes(attributes) {
    attributes.align_content = {
      type: 'string'
    };
    return attributes;
  }
  /**
   * A higher order component adding align_content editing functionality.
   *
   * @date	08/07/2020
   * @since	5.9.0
   *
   * @param	component OriginalBlockEdit The original BlockEdit component.
   * @param	object blockType The block type settings.
   * @return	component
   */


  function withAlignContentComponent(OriginalBlockEdit, blockType) {
    // Determine alignment vars
    var type = blockType.supports.align_content;
    var AlignmentComponent, validateAlignment;

    switch (type) {
      case 'matrix':
        AlignmentComponent = BlockAlignmentMatrixControl || BlockAlignmentMatrixToolbar;
        validateAlignment = validateMatrixAlignment;
        break;

      default:
        AlignmentComponent = BlockVerticalAlignmentToolbar;
        validateAlignment = validateVerticalAlignment;
        break;
    } // Ensure alignment component exists.


    if (AlignmentComponent === undefined) {
      console.warn("The \"".concat(type, "\" alignment component was not found."));
      return OriginalBlockEdit;
    } // Ensure correct block attribute data is sent in intial preview AJAX request.


    blockType.align_content = validateAlignment(blockType.align_content); // Return wrapped component.

    return /*#__PURE__*/function (_Component7) {
      _inherits(WrappedBlockEdit, _Component7);

      var _super9 = _createSuper(WrappedBlockEdit);

      function WrappedBlockEdit() {
        _classCallCheck(this, WrappedBlockEdit);

        return _super9.apply(this, arguments);
      }

      _createClass(WrappedBlockEdit, [{
        key: "render",
        value: function render() {
          var _this$props6 = this.props,
              attributes = _this$props6.attributes,
              setAttributes = _this$props6.setAttributes;
          var align_content = attributes.align_content;

          function onChangeAlignContent(align_content) {
            setAttributes({
              align_content: validateAlignment(align_content)
            });
          }

          return /*#__PURE__*/React.createElement(Fragment, null, /*#__PURE__*/React.createElement(BlockControls, {
            group: "block"
          }, /*#__PURE__*/React.createElement(AlignmentComponent, {
            label: acf.__('Change content alignment'),
            value: validateAlignment(align_content),
            onChange: onChangeAlignContent
          })), /*#__PURE__*/React.createElement(OriginalBlockEdit, this.props));
        }
      }]);

      return WrappedBlockEdit;
    }(Component);
  }
  /**
   * Appends extra attributes for block types that support align_text.
   *
   * @date	08/07/2020
   * @since	5.9.0
   *
   * @param	object attributes The block type attributes.
   * @return	object
   */


  function withAlignTextAttributes(attributes) {
    attributes.align_text = {
      type: 'string'
    };
    return attributes;
  }
  /**
   * A higher order component adding align_text editing functionality.
   *
   * @date	08/07/2020
   * @since	5.9.0
   *
   * @param	component OriginalBlockEdit The original BlockEdit component.
   * @param	object blockType The block type settings.
   * @return	component
   */


  function withAlignTextComponent(OriginalBlockEdit, blockType) {
    var validateAlignment = validateHorizontalAlignment; // Ensure correct block attribute data is sent in intial preview AJAX request.

    blockType.align_text = validateAlignment(blockType.align_text); // Return wrapped component.

    return /*#__PURE__*/function (_Component8) {
      _inherits(WrappedBlockEdit, _Component8);

      var _super10 = _createSuper(WrappedBlockEdit);

      function WrappedBlockEdit() {
        _classCallCheck(this, WrappedBlockEdit);

        return _super10.apply(this, arguments);
      }

      _createClass(WrappedBlockEdit, [{
        key: "render",
        value: function render() {
          var _this$props7 = this.props,
              attributes = _this$props7.attributes,
              setAttributes = _this$props7.setAttributes;
          var align_text = attributes.align_text;

          function onChangeAlignText(align_text) {
            setAttributes({
              align_text: validateAlignment(align_text)
            });
          }

          return /*#__PURE__*/React.createElement(Fragment, null, /*#__PURE__*/React.createElement(BlockControls, null, /*#__PURE__*/React.createElement(AlignmentToolbar, {
            value: validateAlignment(align_text),
            onChange: onChangeAlignText
          })), /*#__PURE__*/React.createElement(OriginalBlockEdit, this.props));
        }
      }]);

      return WrappedBlockEdit;
    }(Component);
  }
})(jQuery);