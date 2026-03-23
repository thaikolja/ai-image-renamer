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
 */(function(e){e(function(){const a=window.airAdmin;if(!a||!a.ajaxUrl)return;const o=e(document),i=e("#air_api_key");let r=!1,l=i.val();const d=(t,s,n)=>{t.prop("disabled",s),typeof n=="string"&&t.text(n)},u=(t,s)=>{const n=e("#air_test_result"),f=n.find(".dashicons"),c=n.find(".air-status-badge-text");n.removeClass("air-status-badge--idle air-status-badge--testing air-status-badge--success air-status-badge--error").addClass("air-status-badge--"+t),c.text(s),t==="testing"?f.removeClass("dashicons-lightbulb").addClass("dashicons-update"):f.removeClass("dashicons-update").addClass("dashicons-lightbulb")},b=t=>t&&t.data&&typeof t.data.message=="string"?t.data.message:"";i.on("focus",function(){l=e(this).val(),e(this).select()});const _=()=>{e("#air-api-key-error-msg").hide().text("")},p=t=>{e("#air-api-key-error-msg").text(t).show()};i.on("input",function(){_();const t=e(this).val();l&&l.includes("•")&&t!==l&&!t.includes("•")?(e(this).val(t),r=!0):t.includes("•")?r=!1:r=!0});const w=e("#air-settings-form"),v=!!a.usingApiKeyConstant;w.on("submit",function(t){if(_(),!v&&r){const s=i.val().trim();if(s!==""){if(!s.startsWith("gsk_")){t.preventDefault(),p(a.strings.error_prefix||"Invalid API key format. Groq API keys start with gsk_"),i.focus();return}if(s.length!==56){t.preventDefault(),p(a.strings.error_length||"The API key has an invalid length. It must be exactly 56 characters long."),i.focus();return}}}});const h=e("#air_test_connection");h.on("click",t=>{t.preventDefault(),h.prop("disabled",!0).addClass("air-btn--loading"),u("testing",a.strings.testing||"Testing…");const s={action:"air_test_connection",nonce:a.nonces.test_connection};v||(s.api_key=String(i.val()??""),s.is_new_key=r?1:0);const n=Date.now(),f=1e3;e.ajax({url:a.ajaxUrl,method:"POST",data:s}).done(c=>{const g=b(c),m=Math.max(0,f-(Date.now()-n));setTimeout(()=>{c&&c.success?u("success",g||a.strings.success):u("error",`${a.strings.error} ${g}`.trim()),h.prop("disabled",!1).removeClass("air-btn--loading")},m)}).fail((c,g,m)=>{const k=Math.max(0,f-(Date.now()-n));setTimeout(()=>{u("error",`${a.strings.error} ${m||""}`.trim()),h.prop("disabled",!1).removeClass("air-btn--loading")},k)})}),o.on("click","#air_delete_api_key",function(t){if(t.preventDefault(),!window.confirm(a.strings.delete_confirm))return;const s=e(this);d(s,!0,a.strings.deleting),i.val(""),r=!1,l="",e.ajax({url:a.ajaxUrl,method:"POST",data:{action:"air_delete_api_key",nonce:a.nonces.delete_api_key}}).done(n=>{n&&n.success?i.closest("div").next(".description").text(a.strings.enter_key):window.alert(b(n))}).fail((n,f,c)=>{window.alert(`${a.strings.request_failed} ${c||""}`.trim())}).always(()=>{d(s,!1,a.strings.delete_key_button)})});const y=()=>{const t=e(".air-model-card");t.removeClass("selected"),t.find("input:checked").closest(".air-model-card").addClass("selected")};y(),o.on("change",".air-model-card input",y),o.on("click","#air-donation-banner-close",function(t){t.preventDefault(),t.stopPropagation(),e(this).closest("#air-donation-banner").addClass("hidden")})})})(jQuery);/*
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
 */(function(e){const a={init:function(){this.bindEvents(),this.handleHashNavigation()},bindEvents:function(){e(document).on("click",".air-tab",this.switchTab.bind(this))},switchTab:function(o){const i=e(o.currentTarget),r=i.data("tab");if(!r)return;o.preventDefault(),e(".air-tab").removeClass("active").attr("aria-selected","false"),i.addClass("active").attr("aria-selected","true"),e(".air-panel").removeClass("active").attr("hidden",!0),e("#air-panel-"+r).addClass("active").removeAttr("hidden"),history.pushState?history.pushState(null,null,"#"+r):window.location.hash=r;const l=e('input[name="_wp_http_referer"]');if(l.length){let d=l.val();const u=d.indexOf("#");u!==-1&&(d=d.substring(0,u)),l.val(d+"#"+r)}},handleHashNavigation:function(){const o=window.location.hash.substring(1);o&&e(`.air-tab[data-tab="${o}"]`).length&&e('.air-tab[data-tab="'+o+'"]').trigger("click")}};e(document).ready(function(){a.init()})})(jQuery);
//# sourceMappingURL=index.js.map
