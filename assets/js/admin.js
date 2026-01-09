/**
 * AI Image Renamer - Admin JavaScript
 *
 * Handles the Test Connection and Delete API Key functionalities.
 */

(function ($) {
  "use strict";

  $(document).ready(function () {
    // --- Test Connection Handler ---
    var $testBtn = $("#air_test_connection");
    var $testResult = $("#air_test_result");

    $testBtn.on("click", function (e) {
      e.preventDefault();

      // Update UI to show testing state.
      $testBtn.prop("disabled", true);
      $testResult
        .removeClass("success error")
        .addClass("testing")
        .text(airAdmin.strings.testing);

      // Prepare data.
      var apiKey = $("#air_api_key").val();
      var data = {
        action: "air_test_connection",
        nonce: airAdmin.nonce,
      };

      // Always send the API key value, even if empty, to support live testing of cleared keys.
      data.api_key = apiKey;

      // Make AJAX request.
      $.ajax({
        url: airAdmin.ajaxUrl,
        type: "POST",
        data: data,
        success: function (response) {
          $testBtn.prop("disabled", false);
          $testResult.removeClass("testing");

          if (response.success) {
            $testResult.addClass("success").text(response.data.message);
          } else {
            $testResult
              .addClass("error")
              .text(airAdmin.strings.error + " " + response.data.message);
          }
        },
        error: function (xhr, status, error) {
          $testBtn.prop("disabled", false);
          $testResult
            .removeClass("testing")
            .addClass("error")
            .text(airAdmin.strings.error + " " + error);
        },
      });
    });

    // --- Delete API Key Handler ---
    $(document).on("click", "#air_delete_api_key", function (e) {
      e.preventDefault();

      if (
        !confirm(
          "Are you sure you want to delete the API Key? This action cannot be undone."
        )
      ) {
        return;
      }

      var $delBtn = $(this);
      $delBtn.prop("disabled", true).text("Deleting...");

      // Instantly clear the input field as requested
      var $apiKeyInput = $("#air_api_key");
      $apiKeyInput.val("");

      $.ajax({
        url: airAdmin.ajaxUrl,
        type: "POST",
        data: {
          action: "air_delete_api_key",
          nonce: airAdmin.nonce, // Reuse nonce for simplicity as per PHP
        },
        success: function (response) {
          if (response.success) {
            $delBtn.remove(); // Remove delete button
            // Update description to reflect deletion
            $apiKeyInput
              .closest("div")
              .next(".description")
              .text("Enter your Groq API key."); // Or use localized string if available
          } else {
            alert(response.data.message);
            $delBtn.prop("disabled", false).text("Delete Key");
          }
        },
        error: function (xhr, status, error) {
          alert("Request failed: " + error);
          $delBtn.prop("disabled", false).text("Delete Key");
        },
      });
    });
  });
})(jQuery);
