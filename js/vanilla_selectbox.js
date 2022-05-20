/**
 * @file
 * Attaches behaviors for the Vanila SelectBox module.
 */

(function($, Drupal, drupalSettings) {
  'use strict';
    Drupal.behaviors.vanillaSelectBox = {

    settings: {

      /**
       * Completely ignores elements that match one of these selectors.
       *
       * Disabled on:
       * - Field UI
       * - WYSIWYG elements
       * - Tabledrag weights
       * - Elements that have opted-out of VanillaSelectBox
       * - Elements already processed by VanillaSelectBox.
       *
       * @type {string}
       */
      ignoreSelector: '#field-ui-field-storage-add-form select, #entity-form-display-edit-form select, #entity-view-display-edit-form select, .wysiwyg, .draggable select[name$="[weight]"], .draggable select[name$="[position]"], .locale-translate-filter-form select, .vanilla-selectbox-disable, .vanilla-selectbox-processed',

      /**
       * Explicit "opt-in" selector.
       *
       * @type {string}
       */
      optedInSelector: 'select.vanilla-selectbox-enable',

      /**
       * The default selector, overridden by drupalSettings.
       *
       * @type {string}
       */
      selector: 'select:visible'
    },

    /**
     * Drupal attach behavior.
     */
    attach: function(context, settings) {
      this.settings = this.getSettings(settings);
      this.getElements(context).once('vanilla-selectbox').each(function (i, element) {
        this.createVanillaSelectBox(element);
      }.bind(this));
    },

    /**
     * Creates a VanillaSelectBox instance for a specific element.
     *
     * @param {jQuery|HTMLElement} element
     *   The element.
     */
    createVanillaSelectBox: function(element) {
      var $element = $(element);
      // Each element must have id attribute.
      new vanillaSelectBox(`#${$element.attr('id')}`, this.getElementOptions($element));
    },

    /**
     * Filter out elements that should not be converted into VanillaSelectBox.
     *
     * @param {jQuery|HTMLElement} element
     *   The element.
     *
     * @return {boolean}
     *   TRUE if the element should stay, FALSE otherwise.
     */
    filterElements: function (element) {
      var $element = $(element);

      // Remove elements that should be ignored completely.
      if ($element.is(this.settings.ignoreSelector)) {
        return false;
      }

      // Zero value means no minimum.
      var minOptions = $element.attr('multiple') ? this.settings.minimum_multiple : this.settings.minimum_single;
      return !minOptions || $element.find('option').length >= minOptions;
    },

    /**
     * Retrieves the elements that should be converted into VanillaSelectBox instances.
     *
     * @param {jQuery|Element} context
     *   A DOM Element, Document, or jQuery object to use as context.
     * @param {string} [selector]
     *   A selector to use, defaults to the default selector in the settings.
     */
    getElements: function (context, selector) {
      var $context = $(context || document);
      var $elements = $context.find(selector || this.settings.selector);

      // Remove elements that should not be converted into VanillaSelectBox.
      $elements = $elements.filter(function(i, element) {
        return this.filterElements(element);
      }.bind(this));

      // Add elements that have explicitly opted in to VanillaSelectBox.
      $elements = $elements.add($context.find(this.settings.optedInSelector));

      return $elements;
    },

    /**
     * Retrieves options used to create a VanillaSelectBox instance based on an
     * element.
     *
     * @param {jQuery|HTMLElement} element
     *   The element to process.
     *
     * @return {Object}
     *   The options object used to instantiate a VanillaSelectBox instance with.
     */
    getElementOptions: function (element) {
      var $element = $(element);
      var options = $.extend({}, this.settings.options);

      // Some field widgets have cardinality, so we must respect that.
      // @see \Drupal\vanilla_selectbox\VanillaSelectBoxFormRender::preRenderSelect()
      var cardinality;
      if ($element.attr('multiple') && (cardinality = $element.data('cardinality'))) {
        options.maxSelect = cardinality;
      }

      return options;
    },

    /**
     * Retrieves the settings passed from Drupal.
     *
     * @param {Object} [settings]
     *   Passed Drupal settings object, if any.
     */
    getSettings: function (settings) {
      return $.extend(true, {}, this.settings, settings && settings.vanilla_selectbox || drupalSettings.vanilla_selectbox);
    }

  };
})(jQuery, Drupal, drupalSettings);
