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
 */(function(t){t(function(){const a=window.airAdmin;if(!a||!a.ajaxUrl)return;const i=t(document),r=t("#air_api_key");let s=!1,o=r.val();const c=(e,l,n)=>{e.prop("disabled",l),typeof n=="string"&&e.text(n)},d=(e,l)=>{t("#air_test_result").removeClass("success error testing").addClass(e).text(l)},_=e=>e&&e.data&&typeof e.data.message=="string"?e.data.message:"";r.on("focus",function(){o=t(this).val(),t(this).select()}),r.on("input",function(){const e=t(this).val();o&&o.includes("•")&&e!==o&&!e.includes("•")?(t(this).val(e),s=!0):e.includes("•")?s=!1:s=!0});const f=t("#air_test_connection");f.on("click",e=>{e.preventDefault(),c(f,!0),d("testing",a.strings.testing);const l=String(r.val()??"");t.ajax({url:a.ajaxUrl,method:"POST",data:{action:"air_test_connection",nonce:a.nonces.test_connection,api_key:l,is_new_key:s?1:0}}).done(n=>{const u=_(n);n&&n.success?d("success",u):d("error",`${a.strings.error} ${u}`.trim())}).fail((n,u,h)=>{d("error",`${a.strings.error} ${h||""}`.trim())}).always(()=>{c(f,!1),t("#air_test_result").removeClass("testing")})}),i.on("click","#air_delete_api_key",function(e){if(e.preventDefault(),!window.confirm(a.strings.delete_confirm))return;const l=t(this);c(l,!0,a.strings.deleting),r.val(""),s=!1,o="",t.ajax({url:a.ajaxUrl,method:"POST",data:{action:"air_delete_api_key",nonce:a.nonces.delete_api_key}}).done(n=>{n&&n.success?r.closest("div").next(".description").text(a.strings.enter_key):window.alert(_(n))}).fail((n,u,h)=>{window.alert(`${a.strings.request_failed} ${h||""}`.trim())}).always(()=>{c(l,!1,a.strings.delete_key_button)})});const g=()=>{const e=t(".air-model-card");e.removeClass("selected"),e.find("input:checked").closest(".air-model-card").addClass("selected")};g(),i.on("change",".air-model-card input",g),i.on("click","#air-donation-banner-close",function(e){e.preventDefault(),e.stopPropagation(),t(this).closest("#air-donation-banner").addClass("hidden")})})})(jQuery);/*
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
 */(function(t){const a={init:function(){this.bindEvents(),this.handleHashNavigation()},bindEvents:function(){t(document).on("click",".air-tab",this.switchTab.bind(this))},switchTab:function(i){const r=t(i.currentTarget),s=r.data("tab");if(!s)return;i.preventDefault(),t(".air-tab").removeClass("active").attr("aria-selected","false"),r.addClass("active").attr("aria-selected","true"),t(".air-panel").removeClass("active").attr("hidden",!0),t("#air-panel-"+s).addClass("active").removeAttr("hidden"),history.pushState?history.pushState(null,null,"#"+s):window.location.hash=s;const o=t('input[name="_wp_http_referer"]');if(o.length){let c=o.val();const d=c.indexOf("#");d!==-1&&(c=c.substring(0,d)),o.val(c+"#"+s)}},handleHashNavigation:function(){const i=window.location.hash.substring(1);i&&t(`.air-tab[data-tab="${i}"]`).length&&t('.air-tab[data-tab="'+i+'"]').trigger("click")}};t(document).ready(function(){a.init()})})(jQuery);
//# sourceMappingURL=index.js.map
