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
 */(function(e){e(function(){const a=window.airAdmin;if(!a||!a.ajaxUrl)return;const o=e(document),r=e("#air_api_key");let n=!1,c=r.val();const f=(t,s,i)=>{if(t.prop("disabled",s).attr("aria-disabled",s?"true":"false"),typeof i=="string"){const l=t.find(".air-button-label, span[aria-hidden='true']").last();l.length?l.text(i):t.text(i)}},d=(t,s)=>{const i=e("#air_test_result"),l=i.find(".dashicons"),u=i.find(".air-status-badge-text");i.removeClass("air-status-badge--idle air-status-badge--testing air-status-badge--success air-status-badge--error").addClass("air-status-badge--"+t),u.text(s),t==="testing"?l.removeClass("dashicons-lightbulb").addClass("dashicons-update"):l.removeClass("dashicons-update").addClass("dashicons-lightbulb")},p=t=>t&&t.data&&typeof t.data.message=="string"?t.data.message:"";r.on("focus",function(){c=e(this).val(),e(this).select()});const m=()=>{e("#air-api-key-error-msg").hide().text("")},b=t=>{e("#air-api-key-error-msg").text(t).show()},k=t=>{e("#air_api_key_desc").text(t)};r.on("input",function(){m();const t=e(this).val();c&&c.includes("•")&&t!==c&&!t.includes("•")?(e(this).val(t),n=!0):t.includes("•")?n=!1:n=!0});const w=e("#air-settings-form"),v=!!a.usingApiKeyConstant;w.on("submit",function(t){if(m(),!v&&n){const s=r.val().trim();if(s!==""){if(!s.startsWith("gsk_")){t.preventDefault(),b(a.strings.error_prefix||"Invalid API key format. Groq API keys start with gsk_"),r.focus();return}if(s.length!==56){t.preventDefault(),b(a.strings.error_length||"The API key has an invalid length. It must be exactly 56 characters long."),r.focus();return}}}});const g=e("#air_test_connection");g.on("click",t=>{t.preventDefault(),g.prop("disabled",!0).addClass("air-btn--loading"),d("testing",a.strings.testing||"Testing…");const s={action:"air_test_connection",nonce:a.nonces.test_connection};v||(s.api_key=String(r.val()??""),s.is_new_key=n?1:0);const i=Date.now(),l=1e3;e.ajax({url:a.ajaxUrl,method:"POST",data:s}).done(u=>{const h=p(u),_=Math.max(0,l-(Date.now()-i));setTimeout(()=>{u&&u.success?d("success",h||a.strings.success):d("error",`${a.strings.error} ${h}`.trim()),g.prop("disabled",!1).removeClass("air-btn--loading")},_)}).fail((u,h,_)=>{const x=Math.max(0,l-(Date.now()-i));setTimeout(()=>{d("error",`${a.strings.error} ${_||""}`.trim()),g.prop("disabled",!1).removeClass("air-btn--loading")},x)})}),o.on("click","#air_delete_api_key",function(t){if(t.preventDefault(),!window.confirm(a.strings.delete_confirm))return;const s=e(this);f(s,!0,a.strings.deleting),r.val(""),n=!1,c="",e.ajax({url:a.ajaxUrl,method:"POST",data:{action:"air_delete_api_key",nonce:a.nonces.delete_api_key}}).done(i=>{i&&i.success?(m(),k(a.strings.enter_key),d("idle",a.strings.no_key||"Not tested")):window.alert(p(i))}).fail((i,l,u)=>{window.alert(`${a.strings.request_failed} ${u||""}`.trim())}).always(()=>{f(s,!1,a.strings.delete_key_button)})}),o.on("click",".air-encryption-notice .notice-dismiss",function(){const t=e(this).closest(".air-encryption-notice"),s=String(t.data("air-dismiss-nonce")??"");s&&e.ajax({url:a.ajaxUrl,method:"POST",data:{action:"air_dismiss_encryption_notice",nonce:s}})});const y=()=>{const t=e(".air-model-card");t.removeClass("selected"),t.find("input:checked").closest(".air-model-card").addClass("selected")};y(),o.on("change",".air-model-card input",y)})})(jQuery);/*
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
 */(function(e){const a={init:function(){this.bindEvents(),this.handleHashNavigation()},bindEvents:function(){e(document).on("click",".air-tab",this.switchTab.bind(this))},switchTab:function(o){const r=e(o.currentTarget),n=r.data("tab");if(!n)return;o.preventDefault(),e(".air-tab").removeClass("active").attr("aria-selected","false"),r.addClass("active").attr("aria-selected","true"),e(".air-panel").removeClass("active").attr("hidden",!0),e("#air-panel-"+n).addClass("active").removeAttr("hidden"),history.pushState?history.pushState(null,null,"#"+n):window.location.hash=n;const c=e('input[name="_wp_http_referer"]');if(c.length){let f=c.val();const d=f.indexOf("#");d!==-1&&(f=f.substring(0,d)),c.val(f+"#"+n)}},handleHashNavigation:function(){const o=window.location.hash.substring(1);o&&e(`.air-tab[data-tab="${o}"]`).length&&e('.air-tab[data-tab="'+o+'"]').trigger("click")}};e(document).ready(function(){a.init()})})(jQuery);
//# sourceMappingURL=index.js.map
