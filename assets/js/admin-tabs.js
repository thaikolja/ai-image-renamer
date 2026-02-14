/*
 * @name:           AI Image Renamer
 * @wordpress       Uses AI to rename images during upload for SEO-friendly filenames.
 * @author          Kolja Nolte <kolja.nolte@gmail.com>
 * @copyright       2025-2026 (C) Kolja Nolte
 * @see             https://docs.kolja-nolte.com/wp-ai-image-renamer/
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Released under the GNU General Public License v2 or later.
 * See: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package AIR
 * @license GPL-2.0-or-later
 */

/**
 * AI Image Renamer - Tab Navigation
 *
 * Handles tab switching for the modern settings page.
 *
 * @package AIR
 */

(function ($) {
  "use strict";

  const AIRTabs = {
    /**
     * Initialize tab functionality.
     */
    init: function () {
      this.bindEvents();
      this.handleHashNavigation();
    },

    /**
     * Bind click events to tabs.
     */
    bindEvents: function () {
      $(document).on("click", ".air-tab", this.switchTab.bind(this));
    },

    /**
     * Switch to the clicked tab.
     *
     * @param {Event} e Click event.
     */
    switchTab: function (e) {
      const $tab = $(e.currentTarget);
      const tabId = $tab.data("tab");

      if (!tabId) {
        return;
      }

      e.preventDefault();

      // Update active tab
      $(".air-tab").removeClass("active").attr("aria-selected", "false");
      $tab.addClass("active").attr("aria-selected", "true");

      // Update active panel
      $(".air-panel").removeClass("active").attr("hidden", true);
      $("#air-panel-" + tabId)
          .addClass("active")
          .removeAttr("hidden");

      // Update URL hash
      if (history.pushState) {
        history.pushState(null, null, "#" + tabId);
      } else {
        window.location.hash = tabId;
      }

      // Update _wp_http_referer to include the hash so redirects return to this tab
      const $referer = $('input[name="_wp_http_referer"]');
      if ($referer.length) {
        let refererVal = $referer.val();
        // Remove existing hash if present
        const hashIndex = refererVal.indexOf("#");
        if (hashIndex !== -1) {
          refererVal = refererVal.substring(0, hashIndex);
        }
        $referer.val(refererVal + "#" + tabId);
      }
    },

    /**
     * Handle hash navigation for direct links.
     */
    handleHashNavigation: function () {
      const hash = window.location.hash.substring(1);
      const $tab = $(`.air-tab[data-tab="${hash}"]`);

      if (hash && $tab.length) {
        $tab.trigger("click");
      }
    },
  };

  // Initialize on DOM ready
  $(document).ready(
      function () {
        AIRTabs.init();
      }
  );
})(jQuery);
