import{b as on,J as me,q as pn,d as he,s as l,p as _,x as gn,V as bn,B as j,r as k,I as mn,A as an,F as et,Y as nt,X as tt,v as ot}from"./app-_XiQOCxR.js";import{w as ln,d as xn,x as it,p as lt,N as rt,B as at,V as st,a as dt,e as nn,c as sn,u as ct}from"./Popover-D8VvJPqa.js";import{aN as Je,g as je,b as T,r as P,d as Q,x as wn,u as We,e as fe,H as Cn,o as se,f as Ke,A as rn,R as ut,a as Ye,l as G,m as tn,t as dn,aJ as ft,S as ht,s as vt,n as yn,aO as pt,w as Ie,q as Oe,aP as gt,aQ as bt,G as cn,L as mt,W as xt,T as wt,J as te}from"./_plugin-vue_export-helper-CKmY-Myo.js";import{r as Fe,k as Ct,h as Be,a as Qe,V as un,c as yt,m as St}from"./context-D6PFFVjF.js";import{V as Ot,F as Ft}from"./FocusDetector-CsjQmCLK.js";function Sn(e,o){o&&(on(()=>{const{value:a}=e;a&&Je.registerHandler(a,o)}),me(e,(a,s)=>{s&&Je.unregisterHandler(s)},{deep:!1}),pn(()=>{const{value:a}=e;a&&Je.unregisterHandler(a)}))}function fn(e){switch(typeof e){case"string":return e||void 0;case"number":return String(e);default:return}}function Xe(e){const o=e.filter(a=>a!==void 0);if(o.length!==0)return o.length===1?o[0]:a=>{e.forEach(s=>{s&&s(a)})}}const zt=he({name:"Checkmark",render(){return l("svg",{xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 16 16"},l("g",{fill:"none"},l("path",{d:"M14.046 3.486a.75.75 0 0 1-.032 1.06l-7.93 7.474a.85.85 0 0 1-1.188-.022l-2.68-2.72a.75.75 0 1 1 1.068-1.053l2.234 2.267l7.468-7.038a.75.75 0 0 1 1.06.032z",fill:"currentColor"})))}}),Rt=he({name:"Empty",render(){return l("svg",{viewBox:"0 0 28 28",fill:"none",xmlns:"http://www.w3.org/2000/svg"},l("path",{d:"M26 7.5C26 11.0899 23.0899 14 19.5 14C15.9101 14 13 11.0899 13 7.5C13 3.91015 15.9101 1 19.5 1C23.0899 1 26 3.91015 26 7.5ZM16.8536 4.14645C16.6583 3.95118 16.3417 3.95118 16.1464 4.14645C15.9512 4.34171 15.9512 4.65829 16.1464 4.85355L18.7929 7.5L16.1464 10.1464C15.9512 10.3417 15.9512 10.6583 16.1464 10.8536C16.3417 11.0488 16.6583 11.0488 16.8536 10.8536L19.5 8.20711L22.1464 10.8536C22.3417 11.0488 22.6583 11.0488 22.8536 10.8536C23.0488 10.6583 23.0488 10.3417 22.8536 10.1464L20.2071 7.5L22.8536 4.85355C23.0488 4.65829 23.0488 4.34171 22.8536 4.14645C22.6583 3.95118 22.3417 3.95118 22.1464 4.14645L19.5 6.79289L16.8536 4.14645Z",fill:"currentColor"}),l("path",{d:"M25 22.75V12.5991C24.5572 13.0765 24.053 13.4961 23.5 13.8454V16H17.5L17.3982 16.0068C17.0322 16.0565 16.75 16.3703 16.75 16.75C16.75 18.2688 15.5188 19.5 14 19.5C12.4812 19.5 11.25 18.2688 11.25 16.75L11.2432 16.6482C11.1935 16.2822 10.8797 16 10.5 16H4.5V7.25C4.5 6.2835 5.2835 5.5 6.25 5.5H12.2696C12.4146 4.97463 12.6153 4.47237 12.865 4H6.25C4.45507 4 3 5.45507 3 7.25V22.75C3 24.5449 4.45507 26 6.25 26H21.75C23.5449 26 25 24.5449 25 22.75ZM4.5 22.75V17.5H9.81597L9.85751 17.7041C10.2905 19.5919 11.9808 21 14 21L14.215 20.9947C16.2095 20.8953 17.842 19.4209 18.184 17.5H23.5V22.75C23.5 23.7165 22.7165 24.5 21.75 24.5H6.25C5.2835 24.5 4.5 23.7165 4.5 22.75Z",fill:"currentColor"}))}}),Tt={iconSizeTiny:"28px",iconSizeSmall:"34px",iconSizeMedium:"40px",iconSizeLarge:"46px",iconSizeHuge:"52px"};function Mt(e){const{textColorDisabled:o,iconColor:a,textColor2:s,fontSizeTiny:c,fontSizeSmall:p,fontSizeMedium:u,fontSizeLarge:i,fontSizeHuge:m}=e;return Object.assign(Object.assign({},Tt),{fontSizeTiny:c,fontSizeSmall:p,fontSizeMedium:u,fontSizeLarge:i,fontSizeHuge:m,textColor:o,iconColor:a,extraTextColor:s})}const On={name:"Empty",common:je,self:Mt},Pt=T("empty",`
 display: flex;
 flex-direction: column;
 align-items: center;
 font-size: var(--n-font-size);
`,[P("icon",`
 width: var(--n-icon-size);
 height: var(--n-icon-size);
 font-size: var(--n-icon-size);
 line-height: var(--n-icon-size);
 color: var(--n-icon-color);
 transition:
 color .3s var(--n-bezier);
 `,[Q("+",[P("description",`
 margin-top: 8px;
 `)])]),P("description",`
 transition: color .3s var(--n-bezier);
 color: var(--n-text-color);
 `),P("extra",`
 text-align: center;
 transition: color .3s var(--n-bezier);
 margin-top: 12px;
 color: var(--n-extra-text-color);
 `)]),kt=Object.assign(Object.assign({},fe.props),{description:String,showDescription:{type:Boolean,default:!0},showIcon:{type:Boolean,default:!0},size:{type:String,default:"medium"},renderIcon:Function}),_t=he({name:"Empty",props:kt,slots:Object,setup(e){const{mergedClsPrefixRef:o,inlineThemeDisabled:a,mergedComponentPropsRef:s}=We(e),c=fe("Empty","-empty",Pt,On,e,o),{localeRef:p}=Cn("Empty"),u=_(()=>{var v,w,x;return(v=e.description)!==null&&v!==void 0?v:(x=(w=s==null?void 0:s.value)===null||w===void 0?void 0:w.Empty)===null||x===void 0?void 0:x.description}),i=_(()=>{var v,w;return((w=(v=s==null?void 0:s.value)===null||v===void 0?void 0:v.Empty)===null||w===void 0?void 0:w.renderIcon)||(()=>l(Rt,null))}),m=_(()=>{const{size:v}=e,{common:{cubicBezierEaseInOut:w},self:{[se("iconSize",v)]:x,[se("fontSize",v)]:C,textColor:b,iconColor:$,extraTextColor:D}}=c.value;return{"--n-icon-size":x,"--n-font-size":C,"--n-bezier":w,"--n-text-color":b,"--n-icon-color":$,"--n-extra-text-color":D}}),z=a?Ke("empty",_(()=>{let v="";const{size:w}=e;return v+=w[0],v}),m,e):void 0;return{mergedClsPrefix:o,mergedRenderIcon:i,localizedDescription:_(()=>u.value||p.value.description),cssVars:a?void 0:m,themeClass:z==null?void 0:z.themeClass,onRender:z==null?void 0:z.onRender}},render(){const{$slots:e,mergedClsPrefix:o,onRender:a}=this;return a==null||a(),l("div",{class:[`${o}-empty`,this.themeClass],style:this.cssVars},this.showIcon?l("div",{class:`${o}-empty__icon`},e.icon?e.icon():l(wn,{clsPrefix:o},{default:this.mergedRenderIcon})):null,this.showDescription?l("div",{class:`${o}-empty__description`},e.default?e.default():this.localizedDescription):null,e.extra?l("div",{class:`${o}-empty__extra`},e.extra()):null)}}),$t={height:"calc(var(--n-option-height) * 7.6)",paddingTiny:"4px 0",paddingSmall:"4px 0",paddingMedium:"4px 0",paddingLarge:"4px 0",paddingHuge:"4px 0",optionPaddingTiny:"0 12px",optionPaddingSmall:"0 12px",optionPaddingMedium:"0 12px",optionPaddingLarge:"0 12px",optionPaddingHuge:"0 12px",loadingSize:"18px"};function It(e){const{borderRadius:o,popoverColor:a,textColor3:s,dividerColor:c,textColor2:p,primaryColorPressed:u,textColorDisabled:i,primaryColor:m,opacityDisabled:z,hoverColor:v,fontSizeTiny:w,fontSizeSmall:x,fontSizeMedium:C,fontSizeLarge:b,fontSizeHuge:$,heightTiny:D,heightSmall:M,heightMedium:F,heightLarge:I,heightHuge:B}=e;return Object.assign(Object.assign({},$t),{optionFontSizeTiny:w,optionFontSizeSmall:x,optionFontSizeMedium:C,optionFontSizeLarge:b,optionFontSizeHuge:$,optionHeightTiny:D,optionHeightSmall:M,optionHeightMedium:F,optionHeightLarge:I,optionHeightHuge:B,borderRadius:o,color:a,groupHeaderTextColor:s,actionDividerColor:c,optionTextColor:p,optionTextColorPressed:u,optionTextColorDisabled:i,optionTextColorActive:m,optionOpacityDisabled:z,optionCheckColor:m,optionColorPending:v,optionColorActive:"rgba(0, 0, 0, 0)",optionColorActivePending:v,actionTextColor:p,loadingColor:m})}const Fn=rn({name:"InternalSelectMenu",common:je,peers:{Scrollbar:ut,Empty:On},self:It}),hn=he({name:"NBaseSelectGroupHeader",props:{clsPrefix:{type:String,required:!0},tmNode:{type:Object,required:!0}},setup(){const{renderLabelRef:e,renderOptionRef:o,labelFieldRef:a,nodePropsRef:s}=gn(ln);return{labelField:a,nodeProps:s,renderLabel:e,renderOption:o}},render(){const{clsPrefix:e,renderLabel:o,renderOption:a,nodeProps:s,tmNode:{rawNode:c}}=this,p=s==null?void 0:s(c),u=o?o(c,!1):Fe(c[this.labelField],c,!1),i=l("div",Object.assign({},p,{class:[`${e}-base-select-group-header`,p==null?void 0:p.class]}),u);return c.render?c.render({node:i,option:c}):a?a({node:i,option:c,selected:!1}):i}});function Bt(e,o){return l(bn,{name:"fade-in-scale-up-transition"},{default:()=>e?l(wn,{clsPrefix:o,class:`${o}-base-select-option__check`},{default:()=>l(zt)}):null})}const vn=he({name:"NBaseSelectOption",props:{clsPrefix:{type:String,required:!0},tmNode:{type:Object,required:!0}},setup(e){const{valueRef:o,pendingTmNodeRef:a,multipleRef:s,valueSetRef:c,renderLabelRef:p,renderOptionRef:u,labelFieldRef:i,valueFieldRef:m,showCheckmarkRef:z,nodePropsRef:v,handleOptionClick:w,handleOptionMouseEnter:x}=gn(ln),C=Ye(()=>{const{value:M}=a;return M?e.tmNode.key===M.key:!1});function b(M){const{tmNode:F}=e;F.disabled||w(M,F)}function $(M){const{tmNode:F}=e;F.disabled||x(M,F)}function D(M){const{tmNode:F}=e,{value:I}=C;F.disabled||I||x(M,F)}return{multiple:s,isGrouped:Ye(()=>{const{tmNode:M}=e,{parent:F}=M;return F&&F.rawNode.type==="group"}),showCheckmark:z,nodeProps:v,isPending:C,isSelected:Ye(()=>{const{value:M}=o,{value:F}=s;if(M===null)return!1;const I=e.tmNode.rawNode[m.value];if(F){const{value:B}=c;return B.has(I)}else return M===I}),labelField:i,renderLabel:p,renderOption:u,handleMouseMove:D,handleMouseEnter:$,handleClick:b}},render(){const{clsPrefix:e,tmNode:{rawNode:o},isSelected:a,isPending:s,isGrouped:c,showCheckmark:p,nodeProps:u,renderOption:i,renderLabel:m,handleClick:z,handleMouseEnter:v,handleMouseMove:w}=this,x=Bt(a,e),C=m?[m(o,a),p&&x]:[Fe(o[this.labelField],o,a),p&&x],b=u==null?void 0:u(o),$=l("div",Object.assign({},b,{class:[`${e}-base-select-option`,o.class,b==null?void 0:b.class,{[`${e}-base-select-option--disabled`]:o.disabled,[`${e}-base-select-option--selected`]:a,[`${e}-base-select-option--grouped`]:c,[`${e}-base-select-option--pending`]:s,[`${e}-base-select-option--show-checkmark`]:p}],style:[(b==null?void 0:b.style)||"",o.style||""],onClick:Xe([z,b==null?void 0:b.onClick]),onMouseenter:Xe([v,b==null?void 0:b.onMouseenter]),onMousemove:Xe([w,b==null?void 0:b.onMousemove])}),l("div",{class:`${e}-base-select-option__content`},C));return o.render?o.render({node:$,option:o,selected:a}):i?i({node:$,option:o,selected:a}):$}}),Et=T("base-select-menu",`
 line-height: 1.5;
 outline: none;
 z-index: 0;
 position: relative;
 border-radius: var(--n-border-radius);
 transition:
 background-color .3s var(--n-bezier),
 box-shadow .3s var(--n-bezier);
 background-color: var(--n-color);
`,[T("scrollbar",`
 max-height: var(--n-height);
 `),T("virtual-list",`
 max-height: var(--n-height);
 `),T("base-select-option",`
 min-height: var(--n-option-height);
 font-size: var(--n-option-font-size);
 display: flex;
 align-items: center;
 `,[P("content",`
 z-index: 1;
 white-space: nowrap;
 text-overflow: ellipsis;
 overflow: hidden;
 `)]),T("base-select-group-header",`
 min-height: var(--n-option-height);
 font-size: .93em;
 display: flex;
 align-items: center;
 `),T("base-select-menu-option-wrapper",`
 position: relative;
 width: 100%;
 `),P("loading, empty",`
 display: flex;
 padding: 12px 32px;
 flex: 1;
 justify-content: center;
 `),P("loading",`
 color: var(--n-loading-color);
 font-size: var(--n-loading-size);
 `),P("header",`
 padding: 8px var(--n-option-padding-left);
 font-size: var(--n-option-font-size);
 transition: 
 color .3s var(--n-bezier),
 border-color .3s var(--n-bezier);
 border-bottom: 1px solid var(--n-action-divider-color);
 color: var(--n-action-text-color);
 `),P("action",`
 padding: 8px var(--n-option-padding-left);
 font-size: var(--n-option-font-size);
 transition: 
 color .3s var(--n-bezier),
 border-color .3s var(--n-bezier);
 border-top: 1px solid var(--n-action-divider-color);
 color: var(--n-action-text-color);
 `),T("base-select-group-header",`
 position: relative;
 cursor: default;
 padding: var(--n-option-padding);
 color: var(--n-group-header-text-color);
 `),T("base-select-option",`
 cursor: pointer;
 position: relative;
 padding: var(--n-option-padding);
 transition:
 color .3s var(--n-bezier),
 opacity .3s var(--n-bezier);
 box-sizing: border-box;
 color: var(--n-option-text-color);
 opacity: 1;
 `,[G("show-checkmark",`
 padding-right: calc(var(--n-option-padding-right) + 20px);
 `),Q("&::before",`
 content: "";
 position: absolute;
 left: 4px;
 right: 4px;
 top: 0;
 bottom: 0;
 border-radius: var(--n-border-radius);
 transition: background-color .3s var(--n-bezier);
 `),Q("&:active",`
 color: var(--n-option-text-color-pressed);
 `),G("grouped",`
 padding-left: calc(var(--n-option-padding-left) * 1.5);
 `),G("pending",[Q("&::before",`
 background-color: var(--n-option-color-pending);
 `)]),G("selected",`
 color: var(--n-option-text-color-active);
 `,[Q("&::before",`
 background-color: var(--n-option-color-active);
 `),G("pending",[Q("&::before",`
 background-color: var(--n-option-color-active-pending);
 `)])]),G("disabled",`
 cursor: not-allowed;
 `,[tn("selected",`
 color: var(--n-option-text-color-disabled);
 `),G("selected",`
 opacity: var(--n-option-opacity-disabled);
 `)]),P("check",`
 font-size: 16px;
 position: absolute;
 right: calc(var(--n-option-padding-right) - 4px);
 top: calc(50% - 7px);
 color: var(--n-option-check-color);
 transition: color .3s var(--n-bezier);
 `,[xn({enterScale:"0.5"})])])]),At=he({name:"InternalSelectMenu",props:Object.assign(Object.assign({},fe.props),{clsPrefix:{type:String,required:!0},scrollable:{type:Boolean,default:!0},treeMate:{type:Object,required:!0},multiple:Boolean,size:{type:String,default:"medium"},value:{type:[String,Number,Array],default:null},autoPending:Boolean,virtualScroll:{type:Boolean,default:!0},show:{type:Boolean,default:!0},labelField:{type:String,default:"label"},valueField:{type:String,default:"value"},loading:Boolean,focusable:Boolean,renderLabel:Function,renderOption:Function,nodeProps:Function,showCheckmark:{type:Boolean,default:!0},onMousedown:Function,onScroll:Function,onFocus:Function,onBlur:Function,onKeyup:Function,onKeydown:Function,onTabOut:Function,onMouseenter:Function,onMouseleave:Function,onResize:Function,resetMenuOnOptionsChange:{type:Boolean,default:!0},inlineThemeDisabled:Boolean,onToggle:Function}),setup(e){const{mergedClsPrefixRef:o,mergedRtlRef:a}=We(e),s=yn("InternalSelectMenu",a,o),c=fe("InternalSelectMenu","-internal-select-menu",Et,Fn,e,j(e,"clsPrefix")),p=k(null),u=k(null),i=k(null),m=_(()=>e.treeMate.getFlattenedNodes()),z=_(()=>Ct(m.value)),v=k(null);function w(){const{treeMate:r}=e;let f=null;const{value:L}=e;L===null?f=r.getFirstAvailableNode():(e.multiple?f=r.getNode((L||[])[(L||[]).length-1]):f=r.getNode(L),(!f||f.disabled)&&(f=r.getFirstAvailableNode())),X(f||null)}function x(){const{value:r}=v;r&&!e.treeMate.getNode(r.key)&&(v.value=null)}let C;me(()=>e.show,r=>{r?C=me(()=>e.treeMate,()=>{e.resetMenuOnOptionsChange?(e.autoPending?w():x(),mn(ve)):x()},{immediate:!0}):C==null||C()},{immediate:!0}),pn(()=>{C==null||C()});const b=_(()=>pt(c.value.self[se("optionHeight",e.size)])),$=_(()=>Ie(c.value.self[se("padding",e.size)])),D=_(()=>e.multiple&&Array.isArray(e.value)?new Set(e.value):new Set),M=_(()=>{const r=m.value;return r&&r.length===0});function F(r){const{onToggle:f}=e;f&&f(r)}function I(r){const{onScroll:f}=e;f&&f(r)}function B(r){var f;(f=i.value)===null||f===void 0||f.sync(),I(r)}function E(){var r;(r=i.value)===null||r===void 0||r.sync()}function U(){const{value:r}=v;return r||null}function W(r,f){f.disabled||X(f,!1)}function oe(r,f){f.disabled||F(f)}function ie(r){var f;Be(r,"action")||(f=e.onKeyup)===null||f===void 0||f.call(e,r)}function Z(r){var f;Be(r,"action")||(f=e.onKeydown)===null||f===void 0||f.call(e,r)}function J(r){var f;(f=e.onMousedown)===null||f===void 0||f.call(e,r),!e.focusable&&r.preventDefault()}function Y(){const{value:r}=v;r&&X(r.getNext({loop:!0}),!0)}function A(){const{value:r}=v;r&&X(r.getPrev({loop:!0}),!0)}function X(r,f=!1){v.value=r,f&&ve()}function ve(){var r,f;const L=v.value;if(!L)return;const re=z.value(L.key);re!==null&&(e.virtualScroll?(r=u.value)===null||r===void 0||r.scrollTo({index:re}):(f=i.value)===null||f===void 0||f.scrollTo({index:re,elSize:b.value}))}function de(r){var f,L;!((f=p.value)===null||f===void 0)&&f.contains(r.target)&&((L=e.onFocus)===null||L===void 0||L.call(e,r))}function ze(r){var f,L;!((f=p.value)===null||f===void 0)&&f.contains(r.relatedTarget)||(L=e.onBlur)===null||L===void 0||L.call(e,r)}an(ln,{handleOptionMouseEnter:W,handleOptionClick:oe,valueSetRef:D,pendingTmNodeRef:v,nodePropsRef:j(e,"nodeProps"),showCheckmarkRef:j(e,"showCheckmark"),multipleRef:j(e,"multiple"),valueRef:j(e,"value"),renderLabelRef:j(e,"renderLabel"),renderOptionRef:j(e,"renderOption"),labelFieldRef:j(e,"labelField"),valueFieldRef:j(e,"valueField")}),an(it,p),on(()=>{const{value:r}=i;r&&r.sync()});const le=_(()=>{const{size:r}=e,{common:{cubicBezierEaseInOut:f},self:{height:L,borderRadius:re,color:we,groupHeaderTextColor:Ce,actionDividerColor:ae,optionTextColorPressed:q,optionTextColor:ye,optionTextColorDisabled:ce,optionTextColorActive:Re,optionOpacityDisabled:Te,optionCheckColor:Me,actionTextColor:Pe,optionColorPending:pe,optionColorActive:ge,loadingColor:ke,loadingSize:_e,optionColorActivePending:$e,[se("optionFontSize",r)]:Se,[se("optionHeight",r)]:be,[se("optionPadding",r)]:H}}=c.value;return{"--n-height":L,"--n-action-divider-color":ae,"--n-action-text-color":Pe,"--n-bezier":f,"--n-border-radius":re,"--n-color":we,"--n-option-font-size":Se,"--n-group-header-text-color":Ce,"--n-option-check-color":Me,"--n-option-color-pending":pe,"--n-option-color-active":ge,"--n-option-color-active-pending":$e,"--n-option-height":be,"--n-option-opacity-disabled":Te,"--n-option-text-color":ye,"--n-option-text-color-active":Re,"--n-option-text-color-disabled":ce,"--n-option-text-color-pressed":q,"--n-option-padding":H,"--n-option-padding-left":Ie(H,"left"),"--n-option-padding-right":Ie(H,"right"),"--n-loading-color":ke,"--n-loading-size":_e}}),{inlineThemeDisabled:xe}=e,K=xe?Ke("internal-select-menu",_(()=>e.size[0]),le,e):void 0,ee={selfRef:p,next:Y,prev:A,getPendingTmNode:U};return Sn(p,e.onResize),Object.assign({mergedTheme:c,mergedClsPrefix:o,rtlEnabled:s,virtualListRef:u,scrollbarRef:i,itemSize:b,padding:$,flattenedNodes:m,empty:M,virtualListContainer(){const{value:r}=u;return r==null?void 0:r.listElRef},virtualListContent(){const{value:r}=u;return r==null?void 0:r.itemsElRef},doScroll:I,handleFocusin:de,handleFocusout:ze,handleKeyUp:ie,handleKeyDown:Z,handleMouseDown:J,handleVirtualListResize:E,handleVirtualListScroll:B,cssVars:xe?void 0:le,themeClass:K==null?void 0:K.themeClass,onRender:K==null?void 0:K.onRender},ee)},render(){const{$slots:e,virtualScroll:o,clsPrefix:a,mergedTheme:s,themeClass:c,onRender:p}=this;return p==null||p(),l("div",{ref:"selfRef",tabindex:this.focusable?0:-1,class:[`${a}-base-select-menu`,this.rtlEnabled&&`${a}-base-select-menu--rtl`,c,this.multiple&&`${a}-base-select-menu--multiple`],style:this.cssVars,onFocusin:this.handleFocusin,onFocusout:this.handleFocusout,onKeyup:this.handleKeyUp,onKeydown:this.handleKeyDown,onMousedown:this.handleMouseDown,onMouseenter:this.onMouseenter,onMouseleave:this.onMouseleave},dn(e.header,u=>u&&l("div",{class:`${a}-base-select-menu__header`,"data-header":!0,key:"header"},u)),this.loading?l("div",{class:`${a}-base-select-menu__loading`},l(ft,{clsPrefix:a,strokeWidth:20})):this.empty?l("div",{class:`${a}-base-select-menu__empty`,"data-empty":!0},vt(e.empty,()=>[l(_t,{theme:s.peers.Empty,themeOverrides:s.peerOverrides.Empty,size:this.size})])):l(ht,{ref:"scrollbarRef",theme:s.peers.Scrollbar,themeOverrides:s.peerOverrides.Scrollbar,scrollable:this.scrollable,container:o?this.virtualListContainer:void 0,content:o?this.virtualListContent:void 0,onScroll:o?void 0:this.doScroll},{default:()=>o?l(Ot,{ref:"virtualListRef",class:`${a}-virtual-list`,items:this.flattenedNodes,itemSize:this.itemSize,showScrollbar:!1,paddingTop:this.padding.top,paddingBottom:this.padding.bottom,onResize:this.handleVirtualListResize,onScroll:this.handleVirtualListScroll,itemResizable:!0},{default:({item:u})=>u.isGroup?l(hn,{key:u.key,clsPrefix:a,tmNode:u}):u.ignored?null:l(vn,{clsPrefix:a,key:u.key,tmNode:u})}):l("div",{class:`${a}-base-select-menu-option-wrapper`,style:{paddingTop:this.padding.top,paddingBottom:this.padding.bottom}},this.flattenedNodes.map(u=>u.isGroup?l(hn,{key:u.key,clsPrefix:a,tmNode:u}):l(vn,{clsPrefix:a,key:u.key,tmNode:u})))}),dn(e.action,u=>u&&[l("div",{class:`${a}-base-select-menu__action`,"data-action":!0,key:"action"},u),l(Ft,{onFocus:this.onTabOut,key:"focus-detector"})]))}}),Lt={paddingSingle:"0 26px 0 12px",paddingMultiple:"3px 26px 0 12px",clearSize:"16px",arrowSize:"16px"};function Dt(e){const{borderRadius:o,textColor2:a,textColorDisabled:s,inputColor:c,inputColorDisabled:p,primaryColor:u,primaryColorHover:i,warningColor:m,warningColorHover:z,errorColor:v,errorColorHover:w,borderColor:x,iconColor:C,iconColorDisabled:b,clearColor:$,clearColorHover:D,clearColorPressed:M,placeholderColor:F,placeholderColorDisabled:I,fontSizeTiny:B,fontSizeSmall:E,fontSizeMedium:U,fontSizeLarge:W,heightTiny:oe,heightSmall:ie,heightMedium:Z,heightLarge:J,fontWeight:Y}=e;return Object.assign(Object.assign({},Lt),{fontSizeTiny:B,fontSizeSmall:E,fontSizeMedium:U,fontSizeLarge:W,heightTiny:oe,heightSmall:ie,heightMedium:Z,heightLarge:J,borderRadius:o,fontWeight:Y,textColor:a,textColorDisabled:s,placeholderColor:F,placeholderColorDisabled:I,color:c,colorDisabled:p,colorActive:c,border:`1px solid ${x}`,borderHover:`1px solid ${i}`,borderActive:`1px solid ${u}`,borderFocus:`1px solid ${i}`,boxShadowHover:"none",boxShadowActive:`0 0 0 2px ${Oe(u,{alpha:.2})}`,boxShadowFocus:`0 0 0 2px ${Oe(u,{alpha:.2})}`,caretColor:u,arrowColor:C,arrowColorDisabled:b,loadingColor:u,borderWarning:`1px solid ${m}`,borderHoverWarning:`1px solid ${z}`,borderActiveWarning:`1px solid ${m}`,borderFocusWarning:`1px solid ${z}`,boxShadowHoverWarning:"none",boxShadowActiveWarning:`0 0 0 2px ${Oe(m,{alpha:.2})}`,boxShadowFocusWarning:`0 0 0 2px ${Oe(m,{alpha:.2})}`,colorActiveWarning:c,caretColorWarning:m,borderError:`1px solid ${v}`,borderHoverError:`1px solid ${w}`,borderActiveError:`1px solid ${v}`,borderFocusError:`1px solid ${w}`,boxShadowHoverError:"none",boxShadowActiveError:`0 0 0 2px ${Oe(v,{alpha:.2})}`,boxShadowFocusError:`0 0 0 2px ${Oe(v,{alpha:.2})}`,colorActiveError:c,caretColorError:v,clearColor:$,clearColorHover:D,clearColorPressed:M})}const zn=rn({name:"InternalSelection",common:je,peers:{Popover:lt},self:Dt}),Nt=Q([T("base-selection",`
 --n-padding-single: var(--n-padding-single-top) var(--n-padding-single-right) var(--n-padding-single-bottom) var(--n-padding-single-left);
 --n-padding-multiple: var(--n-padding-multiple-top) var(--n-padding-multiple-right) var(--n-padding-multiple-bottom) var(--n-padding-multiple-left);
 position: relative;
 z-index: auto;
 box-shadow: none;
 width: 100%;
 max-width: 100%;
 display: inline-block;
 vertical-align: bottom;
 border-radius: var(--n-border-radius);
 min-height: var(--n-height);
 line-height: 1.5;
 font-size: var(--n-font-size);
 `,[T("base-loading",`
 color: var(--n-loading-color);
 `),T("base-selection-tags","min-height: var(--n-height);"),P("border, state-border",`
 position: absolute;
 left: 0;
 right: 0;
 top: 0;
 bottom: 0;
 pointer-events: none;
 border: var(--n-border);
 border-radius: inherit;
 transition:
 box-shadow .3s var(--n-bezier),
 border-color .3s var(--n-bezier);
 `),P("state-border",`
 z-index: 1;
 border-color: #0000;
 `),T("base-suffix",`
 cursor: pointer;
 position: absolute;
 top: 50%;
 transform: translateY(-50%);
 right: 10px;
 `,[P("arrow",`
 font-size: var(--n-arrow-size);
 color: var(--n-arrow-color);
 transition: color .3s var(--n-bezier);
 `)]),T("base-selection-overlay",`
 display: flex;
 align-items: center;
 white-space: nowrap;
 pointer-events: none;
 position: absolute;
 top: 0;
 right: 0;
 bottom: 0;
 left: 0;
 padding: var(--n-padding-single);
 transition: color .3s var(--n-bezier);
 `,[P("wrapper",`
 flex-basis: 0;
 flex-grow: 1;
 overflow: hidden;
 text-overflow: ellipsis;
 `)]),T("base-selection-placeholder",`
 color: var(--n-placeholder-color);
 `,[P("inner",`
 max-width: 100%;
 overflow: hidden;
 `)]),T("base-selection-tags",`
 cursor: pointer;
 outline: none;
 box-sizing: border-box;
 position: relative;
 z-index: auto;
 display: flex;
 padding: var(--n-padding-multiple);
 flex-wrap: wrap;
 align-items: center;
 width: 100%;
 vertical-align: bottom;
 background-color: var(--n-color);
 border-radius: inherit;
 transition:
 color .3s var(--n-bezier),
 box-shadow .3s var(--n-bezier),
 background-color .3s var(--n-bezier);
 `),T("base-selection-label",`
 height: var(--n-height);
 display: inline-flex;
 width: 100%;
 vertical-align: bottom;
 cursor: pointer;
 outline: none;
 z-index: auto;
 box-sizing: border-box;
 position: relative;
 transition:
 color .3s var(--n-bezier),
 box-shadow .3s var(--n-bezier),
 background-color .3s var(--n-bezier);
 border-radius: inherit;
 background-color: var(--n-color);
 align-items: center;
 `,[T("base-selection-input",`
 font-size: inherit;
 line-height: inherit;
 outline: none;
 cursor: pointer;
 box-sizing: border-box;
 border:none;
 width: 100%;
 padding: var(--n-padding-single);
 background-color: #0000;
 color: var(--n-text-color);
 transition: color .3s var(--n-bezier);
 caret-color: var(--n-caret-color);
 `,[P("content",`
 text-overflow: ellipsis;
 overflow: hidden;
 white-space: nowrap; 
 `)]),P("render-label",`
 color: var(--n-text-color);
 `)]),tn("disabled",[Q("&:hover",[P("state-border",`
 box-shadow: var(--n-box-shadow-hover);
 border: var(--n-border-hover);
 `)]),G("focus",[P("state-border",`
 box-shadow: var(--n-box-shadow-focus);
 border: var(--n-border-focus);
 `)]),G("active",[P("state-border",`
 box-shadow: var(--n-box-shadow-active);
 border: var(--n-border-active);
 `),T("base-selection-label","background-color: var(--n-color-active);"),T("base-selection-tags","background-color: var(--n-color-active);")])]),G("disabled","cursor: not-allowed;",[P("arrow",`
 color: var(--n-arrow-color-disabled);
 `),T("base-selection-label",`
 cursor: not-allowed;
 background-color: var(--n-color-disabled);
 `,[T("base-selection-input",`
 cursor: not-allowed;
 color: var(--n-text-color-disabled);
 `),P("render-label",`
 color: var(--n-text-color-disabled);
 `)]),T("base-selection-tags",`
 cursor: not-allowed;
 background-color: var(--n-color-disabled);
 `),T("base-selection-placeholder",`
 cursor: not-allowed;
 color: var(--n-placeholder-color-disabled);
 `)]),T("base-selection-input-tag",`
 height: calc(var(--n-height) - 6px);
 line-height: calc(var(--n-height) - 6px);
 outline: none;
 display: none;
 position: relative;
 margin-bottom: 3px;
 max-width: 100%;
 vertical-align: bottom;
 `,[P("input",`
 font-size: inherit;
 font-family: inherit;
 min-width: 1px;
 padding: 0;
 background-color: #0000;
 outline: none;
 border: none;
 max-width: 100%;
 overflow: hidden;
 width: 1em;
 line-height: inherit;
 cursor: pointer;
 color: var(--n-text-color);
 caret-color: var(--n-caret-color);
 `),P("mirror",`
 position: absolute;
 left: 0;
 top: 0;
 white-space: pre;
 visibility: hidden;
 user-select: none;
 -webkit-user-select: none;
 opacity: 0;
 `)]),["warning","error"].map(e=>G(`${e}-status`,[P("state-border",`border: var(--n-border-${e});`),tn("disabled",[Q("&:hover",[P("state-border",`
 box-shadow: var(--n-box-shadow-hover-${e});
 border: var(--n-border-hover-${e});
 `)]),G("active",[P("state-border",`
 box-shadow: var(--n-box-shadow-active-${e});
 border: var(--n-border-active-${e});
 `),T("base-selection-label",`background-color: var(--n-color-active-${e});`),T("base-selection-tags",`background-color: var(--n-color-active-${e});`)]),G("focus",[P("state-border",`
 box-shadow: var(--n-box-shadow-focus-${e});
 border: var(--n-border-focus-${e});
 `)])])]))]),T("base-selection-popover",`
 margin-bottom: -3px;
 display: flex;
 flex-wrap: wrap;
 margin-right: -8px;
 `),T("base-selection-tag-wrapper",`
 max-width: 100%;
 display: inline-flex;
 padding: 0 7px 3px 0;
 `,[Q("&:last-child","padding-right: 0;"),T("tag",`
 font-size: 14px;
 max-width: 100%;
 `,[P("content",`
 line-height: 1.25;
 text-overflow: ellipsis;
 overflow: hidden;
 `)])])]),Vt=he({name:"InternalSelection",props:Object.assign(Object.assign({},fe.props),{clsPrefix:{type:String,required:!0},bordered:{type:Boolean,default:void 0},active:Boolean,pattern:{type:String,default:""},placeholder:String,selectedOption:{type:Object,default:null},selectedOptions:{type:Array,default:null},labelField:{type:String,default:"label"},valueField:{type:String,default:"value"},multiple:Boolean,filterable:Boolean,clearable:Boolean,disabled:Boolean,size:{type:String,default:"medium"},loading:Boolean,autofocus:Boolean,showArrow:{type:Boolean,default:!0},inputProps:Object,focused:Boolean,renderTag:Function,onKeydown:Function,onClick:Function,onBlur:Function,onFocus:Function,onDeleteOption:Function,maxTagCount:[String,Number],ellipsisTagPopoverProps:Object,onClear:Function,onPatternInput:Function,onPatternFocus:Function,onPatternBlur:Function,renderLabel:Function,status:String,inlineThemeDisabled:Boolean,ignoreComposition:{type:Boolean,default:!0},onResize:Function}),setup(e){const{mergedClsPrefixRef:o,mergedRtlRef:a}=We(e),s=yn("InternalSelection",a,o),c=k(null),p=k(null),u=k(null),i=k(null),m=k(null),z=k(null),v=k(null),w=k(null),x=k(null),C=k(null),b=k(!1),$=k(!1),D=k(!1),M=fe("InternalSelection","-internal-selection",Nt,zn,e,j(e,"clsPrefix")),F=_(()=>e.clearable&&!e.disabled&&(D.value||e.active)),I=_(()=>e.selectedOption?e.renderTag?e.renderTag({option:e.selectedOption,handleClose:()=>{}}):e.renderLabel?e.renderLabel(e.selectedOption,!0):Fe(e.selectedOption[e.labelField],e.selectedOption,!0):e.placeholder),B=_(()=>{const t=e.selectedOption;if(t)return t[e.labelField]}),E=_(()=>e.multiple?!!(Array.isArray(e.selectedOptions)&&e.selectedOptions.length):e.selectedOption!==null);function U(){var t;const{value:h}=c;if(h){const{value:N}=p;N&&(N.style.width=`${h.offsetWidth}px`,e.maxTagCount!=="responsive"&&((t=x.value)===null||t===void 0||t.sync({showAllItemsBeforeCalculate:!1})))}}function W(){const{value:t}=C;t&&(t.style.display="none")}function oe(){const{value:t}=C;t&&(t.style.display="inline-block")}me(j(e,"active"),t=>{t||W()}),me(j(e,"pattern"),()=>{e.multiple&&mn(U)});function ie(t){const{onFocus:h}=e;h&&h(t)}function Z(t){const{onBlur:h}=e;h&&h(t)}function J(t){const{onDeleteOption:h}=e;h&&h(t)}function Y(t){const{onClear:h}=e;h&&h(t)}function A(t){const{onPatternInput:h}=e;h&&h(t)}function X(t){var h;(!t.relatedTarget||!(!((h=u.value)===null||h===void 0)&&h.contains(t.relatedTarget)))&&ie(t)}function ve(t){var h;!((h=u.value)===null||h===void 0)&&h.contains(t.relatedTarget)||Z(t)}function de(t){Y(t)}function ze(){D.value=!0}function le(){D.value=!1}function xe(t){!e.active||!e.filterable||t.target!==p.value&&t.preventDefault()}function K(t){J(t)}const ee=k(!1);function r(t){if(t.key==="Backspace"&&!ee.value&&!e.pattern.length){const{selectedOptions:h}=e;h!=null&&h.length&&K(h[h.length-1])}}let f=null;function L(t){const{value:h}=c;if(h){const N=t.target.value;h.textContent=N,U()}e.ignoreComposition&&ee.value?f=t:A(t)}function re(){ee.value=!0}function we(){ee.value=!1,e.ignoreComposition&&A(f),f=null}function Ce(t){var h;$.value=!0,(h=e.onPatternFocus)===null||h===void 0||h.call(e,t)}function ae(t){var h;$.value=!1,(h=e.onPatternBlur)===null||h===void 0||h.call(e,t)}function q(){var t,h;if(e.filterable)$.value=!1,(t=z.value)===null||t===void 0||t.blur(),(h=p.value)===null||h===void 0||h.blur();else if(e.multiple){const{value:N}=i;N==null||N.blur()}else{const{value:N}=m;N==null||N.blur()}}function ye(){var t,h,N;e.filterable?($.value=!1,(t=z.value)===null||t===void 0||t.focus()):e.multiple?(h=i.value)===null||h===void 0||h.focus():(N=m.value)===null||N===void 0||N.focus()}function ce(){const{value:t}=p;t&&(oe(),t.focus())}function Re(){const{value:t}=p;t&&t.blur()}function Te(t){const{value:h}=v;h&&h.setTextContent(`+${t}`)}function Me(){const{value:t}=w;return t}function Pe(){return p.value}let pe=null;function ge(){pe!==null&&window.clearTimeout(pe)}function ke(){e.active||(ge(),pe=window.setTimeout(()=>{E.value&&(b.value=!0)},100))}function _e(){ge()}function $e(t){t||(ge(),b.value=!1)}me(E,t=>{t||(b.value=!1)}),on(()=>{nt(()=>{const t=z.value;t&&(e.disabled?t.removeAttribute("tabindex"):t.tabIndex=$.value?-1:0)})}),Sn(u,e.onResize);const{inlineThemeDisabled:Se}=e,be=_(()=>{const{size:t}=e,{common:{cubicBezierEaseInOut:h},self:{fontWeight:N,borderRadius:Ue,color:qe,placeholderColor:Ee,textColor:Ae,paddingSingle:Le,paddingMultiple:Ge,caretColor:Ze,colorDisabled:De,textColorDisabled:ue,placeholderColorDisabled:n,colorActive:d,boxShadowFocus:g,boxShadowActive:R,boxShadowHover:S,border:y,borderFocus:O,borderHover:V,borderActive:ne,arrowColor:Tn,arrowColorDisabled:Mn,loadingColor:Pn,colorActiveWarning:kn,boxShadowFocusWarning:_n,boxShadowActiveWarning:$n,boxShadowHoverWarning:In,borderWarning:Bn,borderFocusWarning:En,borderHoverWarning:An,borderActiveWarning:Ln,colorActiveError:Dn,boxShadowFocusError:Nn,boxShadowActiveError:Vn,boxShadowHoverError:Hn,borderError:jn,borderFocusError:Wn,borderHoverError:Kn,borderActiveError:Un,clearColor:qn,clearColorHover:Gn,clearColorPressed:Zn,clearSize:Jn,arrowSize:Yn,[se("height",t)]:Qn,[se("fontSize",t)]:Xn}}=M.value,Ne=Ie(Le),Ve=Ie(Ge);return{"--n-bezier":h,"--n-border":y,"--n-border-active":ne,"--n-border-focus":O,"--n-border-hover":V,"--n-border-radius":Ue,"--n-box-shadow-active":R,"--n-box-shadow-focus":g,"--n-box-shadow-hover":S,"--n-caret-color":Ze,"--n-color":qe,"--n-color-active":d,"--n-color-disabled":De,"--n-font-size":Xn,"--n-height":Qn,"--n-padding-single-top":Ne.top,"--n-padding-multiple-top":Ve.top,"--n-padding-single-right":Ne.right,"--n-padding-multiple-right":Ve.right,"--n-padding-single-left":Ne.left,"--n-padding-multiple-left":Ve.left,"--n-padding-single-bottom":Ne.bottom,"--n-padding-multiple-bottom":Ve.bottom,"--n-placeholder-color":Ee,"--n-placeholder-color-disabled":n,"--n-text-color":Ae,"--n-text-color-disabled":ue,"--n-arrow-color":Tn,"--n-arrow-color-disabled":Mn,"--n-loading-color":Pn,"--n-color-active-warning":kn,"--n-box-shadow-focus-warning":_n,"--n-box-shadow-active-warning":$n,"--n-box-shadow-hover-warning":In,"--n-border-warning":Bn,"--n-border-focus-warning":En,"--n-border-hover-warning":An,"--n-border-active-warning":Ln,"--n-color-active-error":Dn,"--n-box-shadow-focus-error":Nn,"--n-box-shadow-active-error":Vn,"--n-box-shadow-hover-error":Hn,"--n-border-error":jn,"--n-border-focus-error":Wn,"--n-border-hover-error":Kn,"--n-border-active-error":Un,"--n-clear-size":Jn,"--n-clear-color":qn,"--n-clear-color-hover":Gn,"--n-clear-color-pressed":Zn,"--n-arrow-size":Yn,"--n-font-weight":N}}),H=Se?Ke("internal-selection",_(()=>e.size[0]),be,e):void 0;return{mergedTheme:M,mergedClearable:F,mergedClsPrefix:o,rtlEnabled:s,patternInputFocused:$,filterablePlaceholder:I,label:B,selected:E,showTagsPanel:b,isComposing:ee,counterRef:v,counterWrapperRef:w,patternInputMirrorRef:c,patternInputRef:p,selfRef:u,multipleElRef:i,singleElRef:m,patternInputWrapperRef:z,overflowRef:x,inputTagElRef:C,handleMouseDown:xe,handleFocusin:X,handleClear:de,handleMouseEnter:ze,handleMouseLeave:le,handleDeleteOption:K,handlePatternKeyDown:r,handlePatternInputInput:L,handlePatternInputBlur:ae,handlePatternInputFocus:Ce,handleMouseEnterCounter:ke,handleMouseLeaveCounter:_e,handleFocusout:ve,handleCompositionEnd:we,handleCompositionStart:re,onPopoverUpdateShow:$e,focus:ye,focusInput:ce,blur:q,blurInput:Re,updateCounter:Te,getCounter:Me,getTail:Pe,renderLabel:e.renderLabel,cssVars:Se?void 0:be,themeClass:H==null?void 0:H.themeClass,onRender:H==null?void 0:H.onRender}},render(){const{status:e,multiple:o,size:a,disabled:s,filterable:c,maxTagCount:p,bordered:u,clsPrefix:i,ellipsisTagPopoverProps:m,onRender:z,renderTag:v,renderLabel:w}=this;z==null||z();const x=p==="responsive",C=typeof p=="number",b=x||C,$=l(gt,null,{default:()=>l(bt,{clsPrefix:i,loading:this.loading,showArrow:this.showArrow,showClear:this.mergedClearable&&this.selected,onClear:this.handleClear},{default:()=>{var M,F;return(F=(M=this.$slots).arrow)===null||F===void 0?void 0:F.call(M)}})});let D;if(o){const{labelField:M}=this,F=A=>l("div",{class:`${i}-base-selection-tag-wrapper`,key:A.value},v?v({option:A,handleClose:()=>{this.handleDeleteOption(A)}}):l(Qe,{size:a,closable:!A.disabled,disabled:s,onClose:()=>{this.handleDeleteOption(A)},internalCloseIsButtonTag:!1,internalCloseFocusable:!1},{default:()=>w?w(A,!0):Fe(A[M],A,!0)})),I=()=>(C?this.selectedOptions.slice(0,p):this.selectedOptions).map(F),B=c?l("div",{class:`${i}-base-selection-input-tag`,ref:"inputTagElRef",key:"__input-tag__"},l("input",Object.assign({},this.inputProps,{ref:"patternInputRef",tabindex:-1,disabled:s,value:this.pattern,autofocus:this.autofocus,class:`${i}-base-selection-input-tag__input`,onBlur:this.handlePatternInputBlur,onFocus:this.handlePatternInputFocus,onKeydown:this.handlePatternKeyDown,onInput:this.handlePatternInputInput,onCompositionstart:this.handleCompositionStart,onCompositionend:this.handleCompositionEnd})),l("span",{ref:"patternInputMirrorRef",class:`${i}-base-selection-input-tag__mirror`},this.pattern)):null,E=x?()=>l("div",{class:`${i}-base-selection-tag-wrapper`,ref:"counterWrapperRef"},l(Qe,{size:a,ref:"counterRef",onMouseenter:this.handleMouseEnterCounter,onMouseleave:this.handleMouseLeaveCounter,disabled:s})):void 0;let U;if(C){const A=this.selectedOptions.length-p;A>0&&(U=l("div",{class:`${i}-base-selection-tag-wrapper`,key:"__counter__"},l(Qe,{size:a,ref:"counterRef",onMouseenter:this.handleMouseEnterCounter,disabled:s},{default:()=>`+${A}`})))}const W=x?c?l(un,{ref:"overflowRef",updateCounter:this.updateCounter,getCounter:this.getCounter,getTail:this.getTail,style:{width:"100%",display:"flex",overflow:"hidden"}},{default:I,counter:E,tail:()=>B}):l(un,{ref:"overflowRef",updateCounter:this.updateCounter,getCounter:this.getCounter,style:{width:"100%",display:"flex",overflow:"hidden"}},{default:I,counter:E}):C&&U?I().concat(U):I(),oe=b?()=>l("div",{class:`${i}-base-selection-popover`},x?I():this.selectedOptions.map(F)):void 0,ie=b?Object.assign({show:this.showTagsPanel,trigger:"hover",overlap:!0,placement:"top",width:"trigger",onUpdateShow:this.onPopoverUpdateShow,theme:this.mergedTheme.peers.Popover,themeOverrides:this.mergedTheme.peerOverrides.Popover},m):null,J=(this.selected?!1:this.active?!this.pattern&&!this.isComposing:!0)?l("div",{class:`${i}-base-selection-placeholder ${i}-base-selection-overlay`},l("div",{class:`${i}-base-selection-placeholder__inner`},this.placeholder)):null,Y=c?l("div",{ref:"patternInputWrapperRef",class:`${i}-base-selection-tags`},W,x?null:B,$):l("div",{ref:"multipleElRef",class:`${i}-base-selection-tags`,tabindex:s?void 0:0},W,$);D=l(et,null,b?l(rt,Object.assign({},ie,{scrollable:!0,style:"max-height: calc(var(--v-target-height) * 6.6);"}),{trigger:()=>Y,default:oe}):Y,J)}else if(c){const M=this.pattern||this.isComposing,F=this.active?!M:!this.selected,I=this.active?!1:this.selected;D=l("div",{ref:"patternInputWrapperRef",class:`${i}-base-selection-label`,title:this.patternInputFocused?void 0:fn(this.label)},l("input",Object.assign({},this.inputProps,{ref:"patternInputRef",class:`${i}-base-selection-input`,value:this.active?this.pattern:"",placeholder:"",readonly:s,disabled:s,tabindex:-1,autofocus:this.autofocus,onFocus:this.handlePatternInputFocus,onBlur:this.handlePatternInputBlur,onInput:this.handlePatternInputInput,onCompositionstart:this.handleCompositionStart,onCompositionend:this.handleCompositionEnd})),I?l("div",{class:`${i}-base-selection-label__render-label ${i}-base-selection-overlay`,key:"input"},l("div",{class:`${i}-base-selection-overlay__wrapper`},v?v({option:this.selectedOption,handleClose:()=>{}}):w?w(this.selectedOption,!0):Fe(this.label,this.selectedOption,!0))):null,F?l("div",{class:`${i}-base-selection-placeholder ${i}-base-selection-overlay`,key:"placeholder"},l("div",{class:`${i}-base-selection-overlay__wrapper`},this.filterablePlaceholder)):null,$)}else D=l("div",{ref:"singleElRef",class:`${i}-base-selection-label`,tabindex:this.disabled?void 0:0},this.label!==void 0?l("div",{class:`${i}-base-selection-input`,title:fn(this.label),key:"input"},l("div",{class:`${i}-base-selection-input__content`},v?v({option:this.selectedOption,handleClose:()=>{}}):w?w(this.selectedOption,!0):Fe(this.label,this.selectedOption,!0))):l("div",{class:`${i}-base-selection-placeholder ${i}-base-selection-overlay`,key:"placeholder"},l("div",{class:`${i}-base-selection-placeholder__inner`},this.placeholder)),$);return l("div",{ref:"selfRef",class:[`${i}-base-selection`,this.rtlEnabled&&`${i}-base-selection--rtl`,this.themeClass,e&&`${i}-base-selection--${e}-status`,{[`${i}-base-selection--active`]:this.active,[`${i}-base-selection--selected`]:this.selected||this.active&&this.pattern,[`${i}-base-selection--disabled`]:this.disabled,[`${i}-base-selection--multiple`]:this.multiple,[`${i}-base-selection--focus`]:this.focused}],style:this.cssVars,onClick:this.onClick,onMouseenter:this.handleMouseEnter,onMouseleave:this.handleMouseLeave,onKeydown:this.onKeydown,onFocusin:this.handleFocusin,onFocusout:this.handleFocusout,onMousedown:this.handleMouseDown},D,u?l("div",{class:`${i}-base-selection__border`}):null,u?l("div",{class:`${i}-base-selection__state-border`}):null)}});function He(e){return e.type==="group"}function Rn(e){return e.type==="ignored"}function en(e,o){try{return!!(1+o.toString().toLowerCase().indexOf(e.trim().toLowerCase()))}catch{return!1}}function Ht(e,o){return{getIsGroup:He,getIgnored:Rn,getKey(s){return He(s)?s.name||s.key||"key-required":s[e]},getChildren(s){return s[o]}}}function jt(e,o,a,s){if(!o)return e;function c(p){if(!Array.isArray(p))return[];const u=[];for(const i of p)if(He(i)){const m=c(i[s]);m.length&&u.push(Object.assign({},i,{[s]:m}))}else{if(Rn(i))continue;o(a,i)&&u.push(i)}return u}return c(e)}function Wt(e,o,a){const s=new Map;return e.forEach(c=>{He(c)?c[a].forEach(p=>{s.set(p[o],p)}):s.set(c[o],c)}),s}function Kt(e){const{boxShadow2:o}=e;return{menuBoxShadow:o}}const Ut=rn({name:"Select",common:je,peers:{InternalSelection:zn,InternalSelectMenu:Fn},self:Kt}),qt=Q([T("select",`
 z-index: auto;
 outline: none;
 width: 100%;
 position: relative;
 font-weight: var(--n-font-weight);
 `),T("select-menu",`
 margin: 4px 0;
 box-shadow: var(--n-menu-box-shadow);
 `,[xn({originalTransition:"background-color .3s var(--n-bezier), box-shadow .3s var(--n-bezier)"})])]),Gt=Object.assign(Object.assign({},fe.props),{to:nn.propTo,bordered:{type:Boolean,default:void 0},clearable:Boolean,clearFilterAfterSelect:{type:Boolean,default:!0},options:{type:Array,default:()=>[]},defaultValue:{type:[String,Number,Array],default:null},keyboard:{type:Boolean,default:!0},value:[String,Number,Array],placeholder:String,menuProps:Object,multiple:Boolean,size:String,menuSize:{type:String},filterable:Boolean,disabled:{type:Boolean,default:void 0},remote:Boolean,loading:Boolean,filter:Function,placement:{type:String,default:"bottom-start"},widthMode:{type:String,default:"trigger"},tag:Boolean,onCreate:Function,fallbackOption:{type:[Function,Boolean],default:void 0},show:{type:Boolean,default:void 0},showArrow:{type:Boolean,default:!0},maxTagCount:[Number,String],ellipsisTagPopoverProps:Object,consistentMenuWidth:{type:Boolean,default:!0},virtualScroll:{type:Boolean,default:!0},labelField:{type:String,default:"label"},valueField:{type:String,default:"value"},childrenField:{type:String,default:"children"},renderLabel:Function,renderOption:Function,renderTag:Function,"onUpdate:value":[Function,Array],inputProps:Object,nodeProps:Function,ignoreComposition:{type:Boolean,default:!0},showOnFocus:Boolean,onUpdateValue:[Function,Array],onBlur:[Function,Array],onClear:[Function,Array],onFocus:[Function,Array],onScroll:[Function,Array],onSearch:[Function,Array],onUpdateShow:[Function,Array],"onUpdate:show":[Function,Array],displayDirective:{type:String,default:"show"},resetMenuOnOptionsChange:{type:Boolean,default:!0},status:String,showCheckmark:{type:Boolean,default:!0},onChange:[Function,Array],items:Array}),eo=he({name:"Select",props:Gt,slots:Object,setup(e){const{mergedClsPrefixRef:o,mergedBorderedRef:a,namespaceRef:s,inlineThemeDisabled:c}=We(e),p=fe("Select","-select",qt,Ut,e,o),u=k(e.defaultValue),i=j(e,"value"),m=cn(i,u),z=k(!1),v=k(""),w=ct(e,["items","options"]),x=k([]),C=k([]),b=_(()=>C.value.concat(x.value).concat(w.value)),$=_(()=>{const{filter:n}=e;if(n)return n;const{labelField:d,valueField:g}=e;return(R,S)=>{if(!S)return!1;const y=S[d];if(typeof y=="string")return en(R,y);const O=S[g];return typeof O=="string"?en(R,O):typeof O=="number"?en(R,String(O)):!1}}),D=_(()=>{if(e.remote)return w.value;{const{value:n}=b,{value:d}=v;return!d.length||!e.filterable?n:jt(n,$.value,d,e.childrenField)}}),M=_(()=>{const{valueField:n,childrenField:d}=e,g=Ht(n,d);return yt(D.value,g)}),F=_(()=>Wt(b.value,e.valueField,e.childrenField)),I=k(!1),B=cn(j(e,"show"),I),E=k(null),U=k(null),W=k(null),{localeRef:oe}=Cn("Select"),ie=_(()=>{var n;return(n=e.placeholder)!==null&&n!==void 0?n:oe.value.placeholder}),Z=[],J=k(new Map),Y=_(()=>{const{fallbackOption:n}=e;if(n===void 0){const{labelField:d,valueField:g}=e;return R=>({[d]:String(R),[g]:R})}return n===!1?!1:d=>Object.assign(n(d),{value:d})});function A(n){const d=e.remote,{value:g}=J,{value:R}=F,{value:S}=Y,y=[];return n.forEach(O=>{if(R.has(O))y.push(R.get(O));else if(d&&g.has(O))y.push(g.get(O));else if(S){const V=S(O);V&&y.push(V)}}),y}const X=_(()=>{if(e.multiple){const{value:n}=m;return Array.isArray(n)?A(n):[]}return null}),ve=_(()=>{const{value:n}=m;return!e.multiple&&!Array.isArray(n)?n===null?null:A([n])[0]||null:null}),de=mt(e),{mergedSizeRef:ze,mergedDisabledRef:le,mergedStatusRef:xe}=de;function K(n,d){const{onChange:g,"onUpdate:value":R,onUpdateValue:S}=e,{nTriggerFormChange:y,nTriggerFormInput:O}=de;g&&te(g,n,d),S&&te(S,n,d),R&&te(R,n,d),u.value=n,y(),O()}function ee(n){const{onBlur:d}=e,{nTriggerFormBlur:g}=de;d&&te(d,n),g()}function r(){const{onClear:n}=e;n&&te(n)}function f(n){const{onFocus:d,showOnFocus:g}=e,{nTriggerFormFocus:R}=de;d&&te(d,n),R(),g&&ae()}function L(n){const{onSearch:d}=e;d&&te(d,n)}function re(n){const{onScroll:d}=e;d&&te(d,n)}function we(){var n;const{remote:d,multiple:g}=e;if(d){const{value:R}=J;if(g){const{valueField:S}=e;(n=X.value)===null||n===void 0||n.forEach(y=>{R.set(y[S],y)})}else{const S=ve.value;S&&R.set(S[e.valueField],S)}}}function Ce(n){const{onUpdateShow:d,"onUpdate:show":g}=e;d&&te(d,n),g&&te(g,n),I.value=n}function ae(){le.value||(Ce(!0),I.value=!0,e.filterable&&Le())}function q(){Ce(!1)}function ye(){v.value="",C.value=Z}const ce=k(!1);function Re(){e.filterable&&(ce.value=!0)}function Te(){e.filterable&&(ce.value=!1,B.value||ye())}function Me(){le.value||(B.value?e.filterable?Le():q():ae())}function Pe(n){var d,g;!((g=(d=W.value)===null||d===void 0?void 0:d.selfRef)===null||g===void 0)&&g.contains(n.relatedTarget)||(z.value=!1,ee(n),q())}function pe(n){f(n),z.value=!0}function ge(){z.value=!0}function ke(n){var d;!((d=E.value)===null||d===void 0)&&d.$el.contains(n.relatedTarget)||(z.value=!1,ee(n),q())}function _e(){var n;(n=E.value)===null||n===void 0||n.focus(),q()}function $e(n){var d;B.value&&(!((d=E.value)===null||d===void 0)&&d.$el.contains(wt(n))||q())}function Se(n){if(!Array.isArray(n))return[];if(Y.value)return Array.from(n);{const{remote:d}=e,{value:g}=F;if(d){const{value:R}=J;return n.filter(S=>g.has(S)||R.has(S))}else return n.filter(R=>g.has(R))}}function be(n){H(n.rawNode)}function H(n){if(le.value)return;const{tag:d,remote:g,clearFilterAfterSelect:R,valueField:S}=e;if(d&&!g){const{value:y}=C,O=y[0]||null;if(O){const V=x.value;V.length?V.push(O):x.value=[O],C.value=Z}}if(g&&J.value.set(n[S],n),e.multiple){const y=Se(m.value),O=y.findIndex(V=>V===n[S]);if(~O){if(y.splice(O,1),d&&!g){const V=t(n[S]);~V&&(x.value.splice(V,1),R&&(v.value=""))}}else y.push(n[S]),R&&(v.value="");K(y,A(y))}else{if(d&&!g){const y=t(n[S]);~y?x.value=[x.value[y]]:x.value=Z}Ae(),q(),K(n[S],n)}}function t(n){return x.value.findIndex(g=>g[e.valueField]===n)}function h(n){B.value||ae();const{value:d}=n.target;v.value=d;const{tag:g,remote:R}=e;if(L(d),g&&!R){if(!d){C.value=Z;return}const{onCreate:S}=e,y=S?S(d):{[e.labelField]:d,[e.valueField]:d},{valueField:O,labelField:V}=e;w.value.some(ne=>ne[O]===y[O]||ne[V]===y[V])||x.value.some(ne=>ne[O]===y[O]||ne[V]===y[V])?C.value=Z:C.value=[y]}}function N(n){n.stopPropagation();const{multiple:d}=e;!d&&e.filterable&&q(),r(),d?K([],[]):K(null,null)}function Ue(n){!Be(n,"action")&&!Be(n,"empty")&&!Be(n,"header")&&n.preventDefault()}function qe(n){re(n)}function Ee(n){var d,g,R,S,y;if(!e.keyboard){n.preventDefault();return}switch(n.key){case" ":if(e.filterable)break;n.preventDefault();case"Enter":if(!(!((d=E.value)===null||d===void 0)&&d.isComposing)){if(B.value){const O=(g=W.value)===null||g===void 0?void 0:g.getPendingTmNode();O?be(O):e.filterable||(q(),Ae())}else if(ae(),e.tag&&ce.value){const O=C.value[0];if(O){const V=O[e.valueField],{value:ne}=m;e.multiple&&Array.isArray(ne)&&ne.includes(V)||H(O)}}}n.preventDefault();break;case"ArrowUp":if(n.preventDefault(),e.loading)return;B.value&&((R=W.value)===null||R===void 0||R.prev());break;case"ArrowDown":if(n.preventDefault(),e.loading)return;B.value?(S=W.value)===null||S===void 0||S.next():ae();break;case"Escape":B.value&&(St(n),q()),(y=E.value)===null||y===void 0||y.focus();break}}function Ae(){var n;(n=E.value)===null||n===void 0||n.focus()}function Le(){var n;(n=E.value)===null||n===void 0||n.focusInput()}function Ge(){var n;B.value&&((n=U.value)===null||n===void 0||n.syncPosition())}we(),me(j(e,"options"),we);const Ze={focus:()=>{var n;(n=E.value)===null||n===void 0||n.focus()},focusInput:()=>{var n;(n=E.value)===null||n===void 0||n.focusInput()},blur:()=>{var n;(n=E.value)===null||n===void 0||n.blur()},blurInput:()=>{var n;(n=E.value)===null||n===void 0||n.blurInput()}},De=_(()=>{const{self:{menuBoxShadow:n}}=p.value;return{"--n-menu-box-shadow":n}}),ue=c?Ke("select",void 0,De,e):void 0;return Object.assign(Object.assign({},Ze),{mergedStatus:xe,mergedClsPrefix:o,mergedBordered:a,namespace:s,treeMate:M,isMounted:xt(),triggerRef:E,menuRef:W,pattern:v,uncontrolledShow:I,mergedShow:B,adjustedTo:nn(e),uncontrolledValue:u,mergedValue:m,followerRef:U,localizedPlaceholder:ie,selectedOption:ve,selectedOptions:X,mergedSize:ze,mergedDisabled:le,focused:z,activeWithoutMenuOpen:ce,inlineThemeDisabled:c,onTriggerInputFocus:Re,onTriggerInputBlur:Te,handleTriggerOrMenuResize:Ge,handleMenuFocus:ge,handleMenuBlur:ke,handleMenuTabOut:_e,handleTriggerClick:Me,handleToggle:be,handleDeleteOption:H,handlePatternInput:h,handleClear:N,handleTriggerBlur:Pe,handleTriggerFocus:pe,handleKeydown:Ee,handleMenuAfterLeave:ye,handleMenuClickOutside:$e,handleMenuScroll:qe,handleMenuKeydown:Ee,handleMenuMousedown:Ue,mergedTheme:p,cssVars:c?void 0:De,themeClass:ue==null?void 0:ue.themeClass,onRender:ue==null?void 0:ue.onRender})},render(){return l("div",{class:`${this.mergedClsPrefix}-select`},l(at,null,{default:()=>[l(st,null,{default:()=>l(Vt,{ref:"triggerRef",inlineThemeDisabled:this.inlineThemeDisabled,status:this.mergedStatus,inputProps:this.inputProps,clsPrefix:this.mergedClsPrefix,showArrow:this.showArrow,maxTagCount:this.maxTagCount,ellipsisTagPopoverProps:this.ellipsisTagPopoverProps,bordered:this.mergedBordered,active:this.activeWithoutMenuOpen||this.mergedShow,pattern:this.pattern,placeholder:this.localizedPlaceholder,selectedOption:this.selectedOption,selectedOptions:this.selectedOptions,multiple:this.multiple,renderTag:this.renderTag,renderLabel:this.renderLabel,filterable:this.filterable,clearable:this.clearable,disabled:this.mergedDisabled,size:this.mergedSize,theme:this.mergedTheme.peers.InternalSelection,labelField:this.labelField,valueField:this.valueField,themeOverrides:this.mergedTheme.peerOverrides.InternalSelection,loading:this.loading,focused:this.focused,onClick:this.handleTriggerClick,onDeleteOption:this.handleDeleteOption,onPatternInput:this.handlePatternInput,onClear:this.handleClear,onBlur:this.handleTriggerBlur,onFocus:this.handleTriggerFocus,onKeydown:this.handleKeydown,onPatternBlur:this.onTriggerInputBlur,onPatternFocus:this.onTriggerInputFocus,onResize:this.handleTriggerOrMenuResize,ignoreComposition:this.ignoreComposition},{arrow:()=>{var e,o;return[(o=(e=this.$slots).arrow)===null||o===void 0?void 0:o.call(e)]}})}),l(dt,{ref:"followerRef",show:this.mergedShow,to:this.adjustedTo,teleportDisabled:this.adjustedTo===nn.tdkey,containerClass:this.namespace,width:this.consistentMenuWidth?"target":void 0,minWidth:"target",placement:this.placement},{default:()=>l(bn,{name:"fade-in-scale-up-transition",appear:this.isMounted,onAfterLeave:this.handleMenuAfterLeave},{default:()=>{var e,o,a;return this.mergedShow||this.displayDirective==="show"?((e=this.onRender)===null||e===void 0||e.call(this),tt(l(At,Object.assign({},this.menuProps,{ref:"menuRef",onResize:this.handleTriggerOrMenuResize,inlineThemeDisabled:this.inlineThemeDisabled,virtualScroll:this.consistentMenuWidth&&this.virtualScroll,class:[`${this.mergedClsPrefix}-select-menu`,this.themeClass,(o=this.menuProps)===null||o===void 0?void 0:o.class],clsPrefix:this.mergedClsPrefix,focusable:!0,labelField:this.labelField,valueField:this.valueField,autoPending:!0,nodeProps:this.nodeProps,theme:this.mergedTheme.peers.InternalSelectMenu,themeOverrides:this.mergedTheme.peerOverrides.InternalSelectMenu,treeMate:this.treeMate,multiple:this.multiple,size:this.menuSize,renderOption:this.renderOption,renderLabel:this.renderLabel,value:this.mergedValue,style:[(a=this.menuProps)===null||a===void 0?void 0:a.style,this.cssVars],onToggle:this.handleToggle,onScroll:this.handleMenuScroll,onFocus:this.handleMenuFocus,onBlur:this.handleMenuBlur,onKeydown:this.handleMenuKeydown,onTabOut:this.handleMenuTabOut,onMousedown:this.handleMenuMousedown,show:this.mergedShow,showCheckmark:this.showCheckmark,resetMenuOnOptionsChange:this.resetMenuOnOptionsChange}),{empty:()=>{var s,c;return[(c=(s=this.$slots).empty)===null||c===void 0?void 0:c.call(s)]},header:()=>{var s,c;return[(c=(s=this.$slots).header)===null||c===void 0?void 0:c.call(s)]},action:()=>{var s,c;return[(c=(s=this.$slots).action)===null||c===void 0?void 0:c.call(s)]}}),this.displayDirective==="show"?[[ot,this.mergedShow],[sn,this.handleMenuClickOutside,void 0,{capture:!0}]]:[[sn,this.handleMenuClickOutside,void 0,{capture:!0}]])):null}})})]}))}});export{eo as N,At as a,_t as b,Ht as c,On as e,Fn as i,Xe as m,Ut as s};
