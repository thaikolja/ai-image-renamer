/**
 * AI Image Renamer - Admin JavaScript
 *
 * Handles the Test Connection AJAX functionality.
 */

(function ($) {
  "use strict";

  $(document).ready(function () {
    var $button = $("#air_test_connection");
    var $result = $("#air_test_result");

    $button.on("click", function (e) {
      e.preventDefault();

      // Update UI to show testing state.
      $button.prop("disabled", true);
      $result
        .removeClass("success error")
        .addClass("testing")
        .text(airAdmin.strings.testing);

      // Make AJAX request.
      $.ajax({
        url: airAdmin.ajaxUrl,
        type: "POST",
        data: {
          action: "air_test_connection",
          nonce: airAdmin.nonce,
        },
        success: function (response) {
          $button.prop("disabled", false);
          $result.removeClass("testing");

          if (response.success) {
            $result.addClass("success").text(response.data.message);
          } else {
            $result
              .addClass("error")
              .text(airAdmin.strings.error + " " + response.data.message);
          }
        },
        error: function (xhr, status, error) {
          $button.prop("disabled", false);
          $result
            .removeClass("testing")
            .addClass("error")
            .text(airAdmin.strings.error + " " + error);
        },
      });
    });
  });
})(jQuery);
