(()=>{"use strict";const e=window.wp.hooks,t=window.wp.data,n=window.wp.blockEditor,o=window.wp.notices,r=window.wp.i18n,i=window.lodash,l=function(e){let t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{},n=arguments.length>2&&void 0!==arguments[2]&&arguments[2];const o=["allowedBlocks","allowedChildren"];for(const[i,c]of Object.entries(e))if(!o.includes(i))if(i.includes("/"))Object.entries(e).forEach((e=>{let[n,r]=e;o.includes(n)||l(r,t,n)}));else if(!1!==n){var r;const e=s(c,`${i}.`);t[n]={...null!==(r=t[n])&&void 0!==r?r:{},...e}}return t},c=function(e,t,n){let o=arguments.length>3&&void 0!==arguments[3]?arguments[3]:{depth:0,value:void 0},r=arguments.length>4&&void 0!==arguments[4]?arguments[4]:1;const[l,...s]=e,a=n[l];if(0===s.length){const e=(0,i.get)(a,t);return void 0!==e&&r>=o.depth&&(o.depth=r,o.value=e),o}return void 0!==a&&(o=c(s,t,a,o,r+1)),c(s,t,n,o,r)};function s(e){let t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:"";const n={};return Object.entries(e).forEach((e=>{let[o,r]=e;"object"==typeof r&&r&&!Array.isArray(r)?(n[`${t}${o}`]=!0,Object.assign(n,s(r,`${t}${o}.`))):n[`${t}${o}`]=!0})),n}const a=window.wp.element,d=window.wp.components,u=window.wp.compose;function g(e,t,n){if(t.length>0){const o=t[0];if(n.blockSettings&&n.blockSettings[String(o)]&&n.blockSettings[String(o)].allowedChildren)return w(e,n.blockSettings[String(o)].allowedChildren)}return w(e,n.allowedBlocks)}function w(e,t){return t.some((t=>function(e,t){return t.includes("*")?e.match(new RegExp(t.replace("*",".*"))):t===e}(e,t)))}const p={};!function(){if(VIP_GOVERNANCE.error)return void(0,t.dispatch)(o.store).createErrorNotice(VIP_GOVERNANCE.error,{id:"wpcomvip-governance-error",isDismissible:!0,actions:[{label:(0,r.__)("Open governance settings"),url:VIP_GOVERNANCE.urlSettingsPage}]});const i=VIP_GOVERNANCE.governanceRules;(0,e.addFilter)("blockEditor.__unstableCanInsertBlockType","wpcomvip-governance/block-insertion",((o,r,l,c)=>{let{getBlock:s}=c;if(!1===o)return o;let a=[];if(l){const{getBlockParents:e,getBlockName:o}=(0,t.select)(n.store),r=s(l),i=e(l,!0);a=[r.clientId,...i].map((e=>o(e)))}const d=g(r.name,a,i);return(0,e.applyFilters)("vip_governance__is_block_allowed_for_insertion",d,r.name,a,i)}));const s=VIP_GOVERNANCE.nestedSettings,w=l(s);(0,e.addFilter)("blockEditor.useSetting.before","wpcomvip-governance/nested-block-settings",((e,o,r,i)=>{if(void 0===w[i]||!0!==w[i][o])return e;const l=[r,...(0,t.select)(n.store).getBlockParents(r,!0)].map((e=>(0,t.select)(n.store).getBlockName(e))).reverse();return({value:e}=c(l,o,s)),e.theme?e.theme:e})),i?.allowedBlocks&&function(o){const r=(0,u.createHigherOrderComponent)((r=>i=>{const{name:l,clientId:c}=i,{getBlockParents:s,getBlockName:u}=(0,t.select)(n.store),w=s(c,!0),v=w.some((e=>function(e){return e in p}(e)));if(v)return(0,a.createElement)(r,i);const m=w.map((e=>u(e)));let f=g(l,m,o);return f=(0,e.applyFilters)("vip_governance__is_block_allowed_for_editing",f,l,m,o),f?(0,a.createElement)(r,i):(function(e){p[e]=!0}(c),(0,a.createElement)(a.Fragment,null,(0,a.createElement)(d.Disabled,null,(0,a.createElement)("div",{style:{opacity:.6,"background-color":"#eee",border:"2px dashed #999"}},(0,a.createElement)(r,i)))))}),"withDisabledBlocks");(0,e.addFilter)("editor.BlockEdit","wpcomvip-governance/with-disabled-blocks",r)}(i)}()})();