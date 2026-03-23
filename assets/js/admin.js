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
 * AI Image Renamer - Admin JavaScript
 *
 * Handles the Test Connection and Delete API Key functionalities.
 */

/* global airAdmin */

(function ($) {
  "use strict";

  $(function () {
    const admin = window.airAdmin;
    if (!admin || !admin.ajaxUrl) {
      // Fail fast to avoid runtime errors if localized script data is missing
      return;
    }

    const $doc = $(document);
    const $apiKeyInput = $("#air_api_key");

    // Track if the user has entered a new key (not masked)
    let hasEnteredNewKey = false;
    let originalMaskedValue = $apiKeyInput.val();

    const setButtonState = ($btn, disabled, label) => {
      $btn.prop("disabled", disabled);
      if (typeof label === "string") $btn.text(label);
    };

    const setResultState = (state, text) => {
      const $badge     = $("#air_test_result");
      const $icon      = $badge.find(".dashicons");
      const $text      = $badge.find(".air-status-badge-text");
      const allStates  = "air-status-badge--idle air-status-badge--testing air-status-badge--success air-status-badge--error";

      $badge.removeClass(allStates).addClass("air-status-badge--" + state);
      $text.text(text);

      // Swap icon: spinner while testing, lightbulb otherwise
      if (state === "testing") {
        $icon.removeClass("dashicons-lightbulb").addClass("dashicons-update");
      } else {
        $icon.removeClass("dashicons-update").addClass("dashicons-lightbulb");
      }
    };

    const getResponseMessage = (response) =>
        response && response.data && typeof response.data.message === "string"
            ? response.data.message
            : "";

    // When user focuses on the input field, select all text for easy overwriting
    $apiKeyInput.on("focus", function () {
      originalMaskedValue = $(this).val();
      // Select all text for easy overwriting
      $(this).select();
    });

    // Hide inline error when user types
    const clearErrorInTab = () => {
      $("#air-api-key-error-msg").hide().text("");
    };

    const showErrorInTab = (message) => {
      $("#air-api-key-error-msg").text(message).show();
    };

    // When user starts typing in the input field, clear the masked value
    $apiKeyInput.on("input", function () {
      clearErrorInTab();
      const currentValue = $(this).val();

      // If the value was masked and user started typing, clear it
      if (originalMaskedValue && originalMaskedValue.includes("•") &&
          currentValue !== originalMaskedValue &&
          !currentValue.includes("•")) {
        // User is typing a new key, clear the field completely
        $(this).val(currentValue);
        hasEnteredNewKey = true;
      } else if (currentValue.includes("•")) {
        // Still contains masked characters
        hasEnteredNewKey = false;
      } else {
        // New key being entered
        hasEnteredNewKey = true;
      }
    });

    // --- Pre-save Validation ---
    const $form = $("#air-settings-form");
    const usingConstant = !!admin.usingApiKeyConstant;


    $form.on("submit", function (e) {
      clearErrorInTab();

      if (!usingConstant && hasEnteredNewKey) {
        const val = $apiKeyInput.val().trim();

        // Only validate format/length if user has entered something new
        if (val !== "") {
          if (!val.startsWith("gsk_")) {
            e.preventDefault();
            showErrorInTab(admin.strings.error_prefix || "Invalid API key format. Groq API keys start with gsk_");
            $apiKeyInput.focus();
            return;
          }
          if (val.length !== 56) {
            e.preventDefault();
            showErrorInTab(admin.strings.error_length || "The API key has an invalid length. It must be exactly 56 characters long.");
            $apiKeyInput.focus();
            return;
          }
        }
      }
    });

    // --- Test Connection Handler ---
    const $testBtn = $("#air_test_connection");

    $testBtn.on("click", (e) => {
      e.preventDefault();

      // Disable button and start spinner on its icon
      $testBtn.prop("disabled", true).addClass("air-btn--loading");
      setResultState("testing", admin.strings.testing || "Testing…");

      // Build AJAX data. When using constant, don't send API key from input.
      const data = {
        action: "air_test_connection",
        nonce:  admin.nonces.test_connection,
      };

      if (!usingConstant) {
        data.api_key    = String($apiKeyInput.val() ?? "");
        data.is_new_key = hasEnteredNewKey ? 1 : 0;
      }

      // Enforce a minimum 1-second spinner display before showing result
      const startTime = Date.now();
      const MIN_SPIN_MS = 1000;

      $.ajax({
        url:    admin.ajaxUrl,
        method: "POST",
        data:   data,
      })
          .done((response) => {
            const msg   = getResponseMessage(response);
            const delay = Math.max(0, MIN_SPIN_MS - (Date.now() - startTime));
            setTimeout(() => {
              if (response && response.success) {
                setResultState("success", msg || admin.strings.success);
              } else {
                setResultState("error", `${admin.strings.error} ${msg}`.trim());
              }
              $testBtn.prop("disabled", false).removeClass("air-btn--loading");
            }, delay);
          })
          .fail((_xhr, _status, errorThrown) => {
            const delay = Math.max(0, MIN_SPIN_MS - (Date.now() - startTime));
            setTimeout(() => {
              setResultState("error", `${admin.strings.error} ${errorThrown || ""}`.trim());
              $testBtn.prop("disabled", false).removeClass("air-btn--loading");
            }, delay);
          });
    });

    // --- Delete API Key Handler ---
    $doc.on("click", "#air_delete_api_key", function (e) {
      e.preventDefault();

      if (
          !window.confirm(
              admin.strings.delete_confirm
          )
      ) {
        return;
      }

      const $delBtn = $(this);
      setButtonState($delBtn, true, admin.strings.deleting);

      // Instantly clear the input field as requested
      $apiKeyInput.val("");
      hasEnteredNewKey = false;
      originalMaskedValue = "";

      $.ajax({
        url:    admin.ajaxUrl,
        method: "POST",
        data:   {
          action: "air_delete_api_key",
          nonce:  admin.nonces.delete_api_key,
        },
      })
          .done((response) => {
            if (response && response.success) {
              // Update description to reflect deletion
              $apiKeyInput
                  .closest("div")
                  .next(".description")
                  .text(admin.strings.enter_key);
            } else {
              window.alert(getResponseMessage(response));
            }
          })
          .fail((_xhr, _status, errorThrown) => {
            window.alert(`${admin.strings.request_failed} ${errorThrown || ""}`.trim());
          })
          .always(() => {
            setButtonState($delBtn, false, admin.strings.delete_key_button);
          });
    });

    // --- Model Selector Handler ---
    const updateModelCards = () => {
      const $cards = $(".air-model-card");
      $cards.removeClass("selected");
      $cards.find("input:checked").closest(".air-model-card").addClass("selected");
    };

    updateModelCards();

    $doc.on("change", ".air-model-card input", updateModelCards);

    // --- Donation Banner Close Handler ---
    $doc.on("click", "#air-donation-banner-close", function (e) {
      e.preventDefault();
      e.stopPropagation();
      $(this).closest("#air-donation-banner").addClass("hidden");
    });
  });
})(jQuery);
