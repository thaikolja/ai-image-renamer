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

    const setButtonState = ($btn, disabled, label) => {
      $btn.prop("disabled", disabled);
      if (typeof label === "string") $btn.text(label);
    };

    const setResultState = (state, text) => {
      const $testResult = $("#air_test_result");
      $testResult.removeClass("success error testing").addClass(state).text(text);
    };

    const getResponseMessage = (response) =>
        response && response.data && typeof response.data.message === "string"
            ? response.data.message
            : "";

    // --- Test Connection Handler ---
    const $testBtn = $("#air_test_connection");

    $testBtn.on("click", (e) => {
      e.preventDefault();

      setButtonState($testBtn, true);
      setResultState("testing", admin.strings.testing);

      // Always send the API key value (even empty)
      const apiKey = String($apiKeyInput.val() ?? "");

      $.ajax({
        url:    admin.ajaxUrl,
        method: "POST",
        data:   {
          action:  "air_test_connection",
          nonce:   admin.nonce,
          api_key: apiKey,
        },
      })
          .done((response) => {
            const msg = getResponseMessage(response);

            if (response && response.success) {
              setResultState("success", msg);
            } else {
              setResultState("error", `${admin.strings.error} ${msg}`.trim());
            }
          })
          .fail((_xhr, _status, errorThrown) => {
            setResultState("error", `${admin.strings.error} ${errorThrown || ""}`.trim());
          })
          .always(() => {
            setButtonState($testBtn, false);
            $("#air_test_result").removeClass("testing");
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

      $.ajax({
        url:    admin.ajaxUrl,
        method: "POST",
        data:   {
          action: "air_delete_api_key",
          nonce:  admin.nonce,
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
  });
})(jQuery);
