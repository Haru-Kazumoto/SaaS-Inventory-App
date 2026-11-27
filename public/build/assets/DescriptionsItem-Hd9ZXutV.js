import{g as H,h as C,d as g,b as e,j as N,k as W,m as G,l as $,r as L,K,u as q,e as E,o as A,f as J}from"./_plugin-vue_export-helper-CKmY-Myo.js";import{f as Q,u as U}from"./Popover-D8VvJPqa.js";import{g as X}from"./get-slot-Bk_rJcZu.js";import{d as V,s as n,p as D}from"./app-_XiQOCxR.js";function _(t,a="default",c=[]){const{children:s}=t;if(s!==null&&typeof s=="object"&&!Array.isArray(s)){const l=s[a];if(typeof l=="function")return l()}return c}const Y={thPaddingBorderedSmall:"8px 12px",thPaddingBorderedMedium:"12px 16px",thPaddingBorderedLarge:"16px 24px",thPaddingSmall:"0",thPaddingMedium:"0",thPaddingLarge:"0",tdPaddingBorderedSmall:"8px 12px",tdPaddingBorderedMedium:"12px 16px",tdPaddingBorderedLarge:"16px 24px",tdPaddingSmall:"0 0 8px 0",tdPaddingMedium:"0 0 12px 0",tdPaddingLarge:"0 0 16px 0"};function Z(t){const{tableHeaderColor:a,textColor2:c,textColor1:s,cardColor:l,modalColor:i,popoverColor:d,dividerColor:p,borderRadius:h,fontWeightStrong:u,lineHeight:S,fontSizeSmall:r,fontSizeMedium:f,fontSizeLarge:m}=t;return Object.assign(Object.assign({},Y),{lineHeight:S,fontSizeSmall:r,fontSizeMedium:f,fontSizeLarge:m,titleTextColor:s,thColor:C(l,a),thColorModal:C(i,a),thColorPopover:C(d,a),thTextColor:s,thFontWeight:u,tdTextColor:c,tdColor:l,tdColorModal:i,tdColorPopover:d,borderColor:C(l,p),borderColorModal:C(i,p),borderColorPopover:C(d,p),borderRadius:h})}const ee={common:H,self:Z},oe=g([e("descriptions",{fontSize:"var(--n-font-size)"},[e("descriptions-separator",`
 display: inline-block;
 margin: 0 8px 0 2px;
 `),e("descriptions-table-wrapper",[e("descriptions-table",[e("descriptions-table-row",[e("descriptions-table-header",{padding:"var(--n-th-padding)"}),e("descriptions-table-content",{padding:"var(--n-td-padding)"})])])]),G("bordered",[e("descriptions-table-wrapper",[e("descriptions-table",[e("descriptions-table-row",[g("&:last-child",[e("descriptions-table-content",{paddingBottom:0})])])])])]),$("left-label-placement",[e("descriptions-table-content",[g("> *",{verticalAlign:"top"})])]),$("left-label-align",[g("th",{textAlign:"left"})]),$("center-label-align",[g("th",{textAlign:"center"})]),$("right-label-align",[g("th",{textAlign:"right"})]),$("bordered",[e("descriptions-table-wrapper",`
 border-radius: var(--n-border-radius);
 overflow: hidden;
 background: var(--n-merged-td-color);
 border: 1px solid var(--n-merged-border-color);
 `,[e("descriptions-table",[e("descriptions-table-row",[g("&:not(:last-child)",[e("descriptions-table-content",{borderBottom:"1px solid var(--n-merged-border-color)"}),e("descriptions-table-header",{borderBottom:"1px solid var(--n-merged-border-color)"})]),e("descriptions-table-header",`
 font-weight: 400;
 background-clip: padding-box;
 background-color: var(--n-merged-th-color);
 `,[g("&:not(:last-child)",{borderRight:"1px solid var(--n-merged-border-color)"})]),e("descriptions-table-content",[g("&:not(:last-child)",{borderRight:"1px solid var(--n-merged-border-color)"})])])])])]),e("descriptions-header",`
 font-weight: var(--n-th-font-weight);
 font-size: 18px;
 transition: color .3s var(--n-bezier);
 line-height: var(--n-line-height);
 margin-bottom: 16px;
 color: var(--n-title-text-color);
 `),e("descriptions-table-wrapper",`
 transition:
 background-color .3s var(--n-bezier),
 border-color .3s var(--n-bezier);
 `,[e("descriptions-table",`
 width: 100%;
 border-collapse: separate;
 border-spacing: 0;
 box-sizing: border-box;
 `,[e("descriptions-table-row",`
 box-sizing: border-box;
 transition: border-color .3s var(--n-bezier);
 `,[e("descriptions-table-header",`
 font-weight: var(--n-th-font-weight);
 line-height: var(--n-line-height);
 display: table-cell;
 box-sizing: border-box;
 color: var(--n-th-text-color);
 transition:
 color .3s var(--n-bezier),
 background-color .3s var(--n-bezier),
 border-color .3s var(--n-bezier);
 `),e("descriptions-table-content",`
 vertical-align: top;
 line-height: var(--n-line-height);
 display: table-cell;
 box-sizing: border-box;
 color: var(--n-td-text-color);
 transition:
 color .3s var(--n-bezier),
 background-color .3s var(--n-bezier),
 border-color .3s var(--n-bezier);
 `,[L("content",`
 transition: color .3s var(--n-bezier);
 display: inline-block;
 color: var(--n-td-text-color);
 `)]),L("label",`
 font-weight: var(--n-th-font-weight);
 transition: color .3s var(--n-bezier);
 display: inline-block;
 margin-right: 14px;
 color: var(--n-th-text-color);
 `)])])])]),e("descriptions-table-wrapper",`
 --n-merged-th-color: var(--n-th-color);
 --n-merged-td-color: var(--n-td-color);
 --n-merged-border-color: var(--n-border-color);
 `),N(e("descriptions-table-wrapper",`
 --n-merged-th-color: var(--n-th-color-modal);
 --n-merged-td-color: var(--n-td-color-modal);
 --n-merged-border-color: var(--n-border-color-modal);
 `)),W(e("descriptions-table-wrapper",`
 --n-merged-th-color: var(--n-th-color-popover);
 --n-merged-td-color: var(--n-td-color-popover);
 --n-merged-border-color: var(--n-border-color-popover);
 `))]),F="DESCRIPTION_ITEM_FLAG";function re(t){return typeof t=="object"&&t&&!Array.isArray(t)?t.type&&t.type[F]:!1}const te=Object.assign(Object.assign({},E.props),{title:String,column:{type:Number,default:3},columns:Number,labelPlacement:{type:String,default:"top"},labelAlign:{type:String,default:"left"},separator:{type:String,default:":"},size:{type:String,default:"medium"},bordered:Boolean,labelClass:String,labelStyle:[Object,String],contentClass:String,contentStyle:[Object,String]}),ae=V({name:"Descriptions",props:te,slots:Object,setup(t){const{mergedClsPrefixRef:a,inlineThemeDisabled:c}=q(t),s=E("Descriptions","-descriptions",oe,ee,t,a),l=D(()=>{const{size:d,bordered:p}=t,{common:{cubicBezierEaseInOut:h},self:{titleTextColor:u,thColor:S,thColorModal:r,thColorPopover:f,thTextColor:m,thFontWeight:R,tdTextColor:M,tdColor:I,tdColorModal:B,tdColorPopover:o,borderColor:y,borderColorModal:j,borderColorPopover:b,borderRadius:v,lineHeight:w,[A("fontSize",d)]:P,[A(p?"thPaddingBordered":"thPadding",d)]:x,[A(p?"tdPaddingBordered":"tdPadding",d)]:z}}=s.value;return{"--n-title-text-color":u,"--n-th-padding":x,"--n-td-padding":z,"--n-font-size":P,"--n-bezier":h,"--n-th-font-weight":R,"--n-line-height":w,"--n-th-text-color":m,"--n-td-text-color":M,"--n-th-color":S,"--n-th-color-modal":r,"--n-th-color-popover":f,"--n-td-color":I,"--n-td-color-modal":B,"--n-td-color-popover":o,"--n-border-radius":v,"--n-border-color":y,"--n-border-color-modal":j,"--n-border-color-popover":b}}),i=c?J("descriptions",D(()=>{let d="";const{size:p,bordered:h}=t;return h&&(d+="a"),d+=p[0],d}),l,t):void 0;return{mergedClsPrefix:a,cssVars:c?void 0:l,themeClass:i==null?void 0:i.themeClass,onRender:i==null?void 0:i.onRender,compitableColumn:U(t,["columns","column"]),inlineThemeDisabled:c}},render(){const t=this.$slots.default,a=t?Q(t()):[];a.length;const{contentClass:c,labelClass:s,compitableColumn:l,labelPlacement:i,labelAlign:d,size:p,bordered:h,title:u,cssVars:S,mergedClsPrefix:r,separator:f,onRender:m}=this;m==null||m();const R=a.filter(o=>re(o)),M={span:0,row:[],secondRow:[],rows:[]},B=R.reduce((o,y,j)=>{const b=y.props||{},v=R.length-1===j,w=["label"in b?b.label:_(y,"label")],P=[_(y)],x=b.span||1,z=o.span;o.span+=x;const O=b.labelStyle||b["label-style"]||this.labelStyle,T=b.contentStyle||b["content-style"]||this.contentStyle;if(i==="left")h?o.row.push(n("th",{class:[`${r}-descriptions-table-header`,s],colspan:1,style:O},w),n("td",{class:[`${r}-descriptions-table-content`,c],colspan:v?(l-z)*2+1:x*2-1,style:T},P)):o.row.push(n("td",{class:`${r}-descriptions-table-content`,colspan:v?(l-z)*2:x*2},n("span",{class:[`${r}-descriptions-table-content__label`,s],style:O},[...w,f&&n("span",{class:`${r}-descriptions-separator`},f)]),n("span",{class:[`${r}-descriptions-table-content__content`,c],style:T},P)));else{const k=v?(l-z)*2:x*2;o.row.push(n("th",{class:[`${r}-descriptions-table-header`,s],colspan:k,style:O},w)),o.secondRow.push(n("td",{class:[`${r}-descriptions-table-content`,c],colspan:k,style:T},P))}return(o.span>=l||v)&&(o.span=0,o.row.length&&(o.rows.push(o.row),o.row=[]),i!=="left"&&o.secondRow.length&&(o.rows.push(o.secondRow),o.secondRow=[])),o},M).rows.map(o=>n("tr",{class:`${r}-descriptions-table-row`},o));return n("div",{style:S,class:[`${r}-descriptions`,this.themeClass,`${r}-descriptions--${i}-label-placement`,`${r}-descriptions--${d}-label-align`,`${r}-descriptions--${p}-size`,h&&`${r}-descriptions--bordered`]},u||this.$slots.header?n("div",{class:`${r}-descriptions-header`},u||X(this,"header")):null,n("div",{class:`${r}-descriptions-table-wrapper`},n("table",{class:`${r}-descriptions-table`},n("tbody",null,i==="top"&&n("tr",{class:`${r}-descriptions-table-row`,style:{visibility:"collapse"}},K(l*2,n("td",null))),B))))}}),ne={label:String,span:{type:Number,default:1},labelClass:String,labelStyle:[Object,String],contentClass:String,contentStyle:[Object,String]},ce=V({name:"DescriptionsItem",[F]:!0,props:ne,slots:Object,render(){return null}});export{ae as N,ce as a};
