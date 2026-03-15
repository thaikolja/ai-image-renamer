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
var t;(t=jQuery)(function(){const e=window.airAdmin;if(!e||!e.ajaxUrl)return;const n=t(document),a=t("#air_api_key");let i=!1,s=a.val();const r=(t,e,n)=>{t.prop("disabled",e),"string"==typeof n&&t.text(n)},o=(e,n)=>{t("#air_test_result").removeClass("success error testing").addClass(e).text(n)},c=t=>t&&t.data&&"string"==typeof t.data.message?t.data.message:"";a.on("focus",function(){s=t(this).val(),t(this).select()}),a.on("input",function(){const e=t(this).val();s&&s.includes("•")&&e!==s&&!e.includes("•")?(t(this).val(e),i=!0):i=!e.includes("•")});const l=t("#air_test_connection");l.on("click",n=>{n.preventDefault(),r(l,!0),o("testing",e.strings.testing);const s=String(a.val()??"");t.ajax({url:e.ajaxUrl,method:"POST",data:{action:"air_test_connection",nonce:e.nonces.test_connection,api_key:s,is_new_key:i?1:0}}).done(t=>{const n=c(t);t&&t.success?o("success",n):o("error",`${e.strings.error} ${n}`.trim())}).fail((t,n,a)=>{o("error",`${e.strings.error} ${a||""}`.trim())}).always(()=>{r(l,!1),t("#air_test_result").removeClass("testing")})}),n.on("click","#air_delete_api_key",function(n){if(n.preventDefault(),!window.confirm(e.strings.delete_confirm))return;const o=t(this);r(o,!0,e.strings.deleting),a.val(""),i=!1,s="",t.ajax({url:e.ajaxUrl,method:"POST",data:{action:"air_delete_api_key",nonce:e.nonces.delete_api_key}}).done(t=>{t&&t.success?a.closest("div").next(".description").text(e.strings.enter_key):window.alert(c(t))}).fail((t,n,a)=>{window.alert(`${e.strings.request_failed} ${a||""}`.trim())}).always(()=>{r(o,!1,e.strings.delete_key_button)})});const d=()=>{const e=t(".air-model-card");e.removeClass("selected"),e.find("input:checked").closest(".air-model-card").addClass("selected")};d(),n.on("change",".air-model-card input",d),n.on("click","#air-donation-banner-close",function(e){e.preventDefault(),e.stopPropagation(),t(this).closest("#air-donation-banner").addClass("hidden")})}),
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
function(t){const e={init:function(){this.bindEvents(),this.handleHashNavigation()},bindEvents:function(){t(document).on("click",".air-tab",this.switchTab.bind(this))},switchTab:function(e){const n=t(e.currentTarget),a=n.data("tab");if(!a)return;e.preventDefault(),t(".air-tab").removeClass("active").attr("aria-selected","false"),n.addClass("active").attr("aria-selected","true"),t(".air-panel").removeClass("active").attr("hidden",!0),t("#air-panel-"+a).addClass("active").removeAttr("hidden"),history.pushState?history.pushState(null,null,"#"+a):window.location.hash=a;const i=t('input[name="_wp_http_referer"]');if(i.length){let t=i.val();const e=t.indexOf("#");-1!==e&&(t=t.substring(0,e)),i.val(t+"#"+a)}},handleHashNavigation:function(){const e=window.location.hash.substring(1);e&&t(`.air-tab[data-tab="${e}"]`).length&&t('.air-tab[data-tab="'+e+'"]').trigger("click")}};t(document).ready(function(){e.init()})}(jQuery);
