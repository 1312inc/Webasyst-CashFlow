(function(t){function e(e){for(var r,o,i=e[0],c=e[1],l=e[2],d=0,m=[];d<i.length;d++)o=i[d],Object.prototype.hasOwnProperty.call(n,o)&&n[o]&&m.push(n[o][0]),n[o]=0;for(r in c)Object.prototype.hasOwnProperty.call(c,r)&&(t[r]=c[r]);u&&u(e);while(m.length)m.shift()();return s.push.apply(s,l||[]),a()}function a(){for(var t,e=0;e<s.length;e++){for(var a=s[e],r=!0,o=1;o<a.length;o++){var c=a[o];0!==n[c]&&(r=!1)}r&&(s.splice(e--,1),t=i(i.s=a[0]))}return t}var r={},n={app:0},s=[];function o(t){return i.p+"js/"+({canvg:"canvg",pdfmake:"pdfmake",xlsx:"xlsx"}[t]||t)+".js"}function i(e){if(r[e])return r[e].exports;var a=r[e]={i:e,l:!1,exports:{}};return t[e].call(a.exports,a,a.exports,i),a.l=!0,a.exports}i.e=function(t){var e=[],a=n[t];if(0!==a)if(a)e.push(a[2]);else{var r=new Promise((function(e,r){a=n[t]=[e,r]}));e.push(a[2]=r);var s,c=document.createElement("script");c.charset="utf-8",c.timeout=120,i.nc&&c.setAttribute("nonce",i.nc),c.src=o(t);var l=new Error;s=function(e){c.onerror=c.onload=null,clearTimeout(d);var a=n[t];if(0!==a){if(a){var r=e&&("load"===e.type?"missing":e.type),s=e&&e.target&&e.target.src;l.message="Loading chunk "+t+" failed.\n("+r+": "+s+")",l.name="ChunkLoadError",l.type=r,l.request=s,a[1](l)}n[t]=void 0}};var d=setTimeout((function(){s({type:"timeout",target:c})}),12e4);c.onerror=c.onload=s,document.head.appendChild(c)}return Promise.all(e)},i.m=t,i.c=r,i.d=function(t,e,a){i.o(t,e)||Object.defineProperty(t,e,{enumerable:!0,get:a})},i.r=function(t){"undefined"!==typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(t,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(t,"__esModule",{value:!0})},i.t=function(t,e){if(1&e&&(t=i(t)),8&e)return t;if(4&e&&"object"===typeof t&&t&&t.__esModule)return t;var a=Object.create(null);if(i.r(a),Object.defineProperty(a,"default",{enumerable:!0,value:t}),2&e&&"string"!=typeof t)for(var r in t)i.d(a,r,function(e){return t[e]}.bind(null,r));return a},i.n=function(t){var e=t&&t.__esModule?function(){return t["default"]}:function(){return t};return i.d(e,"a",e),e},i.o=function(t,e){return Object.prototype.hasOwnProperty.call(t,e)},i.p="/webasyst/cash/",i.oe=function(t){throw console.error(t),t};var c=window["webpackJsonp"]=window["webpackJsonp"]||[],l=c.push.bind(c);c.push=e,c=c.slice();for(var d=0;d<c.length;d++)e(c[d]);var u=l;s.push([0,"chunk-vendors"]),a()})({0:function(t,e,a){t.exports=a("56d7")},"22d7":function(t,e,a){"use strict";var r=a("be87"),n=a.n(r);n.a},4678:function(t,e,a){var r={"./af":"2bfb","./af.js":"2bfb","./ar":"8e73","./ar-dz":"a356","./ar-dz.js":"a356","./ar-kw":"423e","./ar-kw.js":"423e","./ar-ly":"1cfd","./ar-ly.js":"1cfd","./ar-ma":"0a84","./ar-ma.js":"0a84","./ar-sa":"8230","./ar-sa.js":"8230","./ar-tn":"6d83","./ar-tn.js":"6d83","./ar.js":"8e73","./az":"485c","./az.js":"485c","./be":"1fc1","./be.js":"1fc1","./bg":"84aa","./bg.js":"84aa","./bm":"a7fa","./bm.js":"a7fa","./bn":"9043","./bn.js":"9043","./bo":"d26a","./bo.js":"d26a","./br":"6887","./br.js":"6887","./bs":"2554","./bs.js":"2554","./ca":"d716","./ca.js":"d716","./cs":"3c0d","./cs.js":"3c0d","./cv":"03ec","./cv.js":"03ec","./cy":"9797","./cy.js":"9797","./da":"0f14","./da.js":"0f14","./de":"b469","./de-at":"b3eb","./de-at.js":"b3eb","./de-ch":"bb71","./de-ch.js":"bb71","./de.js":"b469","./dv":"598a","./dv.js":"598a","./el":"8d47","./el.js":"8d47","./en-au":"0e6b","./en-au.js":"0e6b","./en-ca":"3886","./en-ca.js":"3886","./en-gb":"39a6","./en-gb.js":"39a6","./en-ie":"e1d3","./en-ie.js":"e1d3","./en-il":"7333","./en-il.js":"7333","./en-in":"ec2e","./en-in.js":"ec2e","./en-nz":"6f50","./en-nz.js":"6f50","./en-sg":"b7e9","./en-sg.js":"b7e9","./eo":"65db","./eo.js":"65db","./es":"898b","./es-do":"0a3c","./es-do.js":"0a3c","./es-us":"55c9","./es-us.js":"55c9","./es.js":"898b","./et":"ec18","./et.js":"ec18","./eu":"0ff2","./eu.js":"0ff2","./fa":"8df4","./fa.js":"8df4","./fi":"81e9","./fi.js":"81e9","./fil":"d69a","./fil.js":"d69a","./fo":"0721","./fo.js":"0721","./fr":"9f26","./fr-ca":"d9f8","./fr-ca.js":"d9f8","./fr-ch":"0e49","./fr-ch.js":"0e49","./fr.js":"9f26","./fy":"7118","./fy.js":"7118","./ga":"5120","./ga.js":"5120","./gd":"f6b4","./gd.js":"f6b4","./gl":"8840","./gl.js":"8840","./gom-deva":"aaf2","./gom-deva.js":"aaf2","./gom-latn":"0caa","./gom-latn.js":"0caa","./gu":"e0c5","./gu.js":"e0c5","./he":"c7aa","./he.js":"c7aa","./hi":"dc4d","./hi.js":"dc4d","./hr":"4ba9","./hr.js":"4ba9","./hu":"5b14","./hu.js":"5b14","./hy-am":"d6b6","./hy-am.js":"d6b6","./id":"5038","./id.js":"5038","./is":"0558","./is.js":"0558","./it":"6e98","./it-ch":"6f12","./it-ch.js":"6f12","./it.js":"6e98","./ja":"079e","./ja.js":"079e","./jv":"b540","./jv.js":"b540","./ka":"201b","./ka.js":"201b","./kk":"6d79","./kk.js":"6d79","./km":"e81d","./km.js":"e81d","./kn":"3e92","./kn.js":"3e92","./ko":"22f8","./ko.js":"22f8","./ku":"2421","./ku.js":"2421","./ky":"9609","./ky.js":"9609","./lb":"440c","./lb.js":"440c","./lo":"b29d","./lo.js":"b29d","./lt":"26f9","./lt.js":"26f9","./lv":"b97c","./lv.js":"b97c","./me":"293c","./me.js":"293c","./mi":"688b","./mi.js":"688b","./mk":"6909","./mk.js":"6909","./ml":"02fb","./ml.js":"02fb","./mn":"958b","./mn.js":"958b","./mr":"39bd","./mr.js":"39bd","./ms":"ebe4","./ms-my":"6403","./ms-my.js":"6403","./ms.js":"ebe4","./mt":"1b45","./mt.js":"1b45","./my":"8689","./my.js":"8689","./nb":"6ce3","./nb.js":"6ce3","./ne":"3a39","./ne.js":"3a39","./nl":"facd","./nl-be":"db29","./nl-be.js":"db29","./nl.js":"facd","./nn":"b84c","./nn.js":"b84c","./oc-lnc":"167b","./oc-lnc.js":"167b","./pa-in":"f3ff","./pa-in.js":"f3ff","./pl":"8d57","./pl.js":"8d57","./pt":"f260","./pt-br":"d2d4","./pt-br.js":"d2d4","./pt.js":"f260","./ro":"972c","./ro.js":"972c","./ru":"957c","./ru.js":"957c","./sd":"6784","./sd.js":"6784","./se":"ffff","./se.js":"ffff","./si":"eda5","./si.js":"eda5","./sk":"7be6","./sk.js":"7be6","./sl":"8155","./sl.js":"8155","./sq":"c8f3","./sq.js":"c8f3","./sr":"cf1e","./sr-cyrl":"13e9","./sr-cyrl.js":"13e9","./sr.js":"cf1e","./ss":"52bd","./ss.js":"52bd","./sv":"5fbd","./sv.js":"5fbd","./sw":"74dc","./sw.js":"74dc","./ta":"3de5","./ta.js":"3de5","./te":"5cbb","./te.js":"5cbb","./tet":"576c","./tet.js":"576c","./tg":"3b1b","./tg.js":"3b1b","./th":"10e8","./th.js":"10e8","./tk":"5aff","./tk.js":"5aff","./tl-ph":"0f38","./tl-ph.js":"0f38","./tlh":"cf75","./tlh.js":"cf75","./tr":"0e81","./tr.js":"0e81","./tzl":"cf51","./tzl.js":"cf51","./tzm":"c109","./tzm-latn":"b53d","./tzm-latn.js":"b53d","./tzm.js":"c109","./ug-cn":"6117","./ug-cn.js":"6117","./uk":"ada2","./uk.js":"ada2","./ur":"5294","./ur.js":"5294","./uz":"2e8c","./uz-latn":"010e","./uz-latn.js":"010e","./uz.js":"2e8c","./vi":"2921","./vi.js":"2921","./x-pseudo":"fd7e","./x-pseudo.js":"fd7e","./yo":"7f33","./yo.js":"7f33","./zh-cn":"5c3a","./zh-cn.js":"5c3a","./zh-hk":"49ab","./zh-hk.js":"49ab","./zh-mo":"3a6c","./zh-mo.js":"3a6c","./zh-tw":"90ea","./zh-tw.js":"90ea"};function n(t){var e=s(t);return a(e)}function s(t){if(!a.o(r,t)){var e=new Error("Cannot find module '"+t+"'");throw e.code="MODULE_NOT_FOUND",e}return r[t]}n.keys=function(){return Object.keys(r)},n.resolve=s,t.exports=n,n.id="4678"},"56d7":function(t,e,a){"use strict";a.r(e);a("e260"),a("e6cfa"),a("cca6"),a("a79d");var r=a("2b0e"),n=function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",{attrs:{id:"app"}},[a("div",{staticClass:"flex"},[a("div",{staticClass:"w-1/6 bg-gray-100"},[a("Sidebar")],1),a("div",{staticClass:"w-5/6 mx-10"},[a("keep-alive",[a("router-view")],1)],1)])])},s=[],o=function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",{staticClass:"p-10 text-left"},[a("div",{staticClass:"mb-10"},[a("h4",{staticClass:"font-bold mb-2"},[t._v("Аккаунты")]),t._l(t.accounts,(function(e){return a("div",{key:e.id,staticClass:"mb-1"},[a("div",{staticClass:"flex items-center"},[a("div",[a("div",{staticClass:"w-2 h-2 rounded-full mr-1",style:"background-color:"+e.color+";"})]),a("div",{staticClass:"text-sm",on:{click:function(a){return t.update("Account",e.id)}}},[t._v(" "+t._s(e.name)+" ")])])])})),a("button",{staticClass:"text-xs bg-transparent hover:bg-blue-500 text-blue-700 font-semibold hover:text-white py-1 px-3 border border-blue-500 hover:border-transparent rounded",on:{click:function(e){return t.update("Account")}}},[t._v(" Добавить аккаунт ")])],2),a("h4",{staticClass:"font-bold mb-4"},[t._v("Категории")]),a("div",{staticClass:"uppercase font-bold text-xs mt-6 mb-2"},[t._v("Income")]),t._l(t.categoriesIncome,(function(e){return a("div",{key:e.id,staticClass:"mb-1"},[a("div",{staticClass:"flex items-center"},[a("div",[a("div",{staticClass:"w-2 h-2 rounded-full mr-1",style:"background-color:"+e.color+";"})]),a("div",{staticClass:"text-sm",on:{click:function(a){return t.update("Category",e.id)}}},[t._v(" "+t._s(e.name)+" ")])])])})),a("button",{staticClass:"text-xs bg-transparent hover:bg-blue-500 text-blue-700 font-semibold hover:text-white py-1 px-3 border border-blue-500 hover:border-transparent rounded",on:{click:function(e){return t.update("Category")}}},[t._v(" Добавить категорию ")]),a("div",{staticClass:"uppercase font-bold text-xs mt-6 mb-2"},[t._v("Expense")]),t._l(t.categoriesExpense,(function(e){return a("div",{key:e.id,staticClass:"mb-1"},[a("div",{staticClass:"flex items-center"},[a("div",[a("div",{staticClass:"w-2 h-2 rounded-full mr-1",style:"background-color:"+e.color+";"})]),a("div",{staticClass:"text-sm",on:{click:function(a){return t.update("Category",e.id)}}},[t._v(" "+t._s(e.name)+" ")])])])})),a("button",{staticClass:"text-xs bg-transparent hover:bg-blue-500 text-blue-700 font-semibold hover:text-white py-1 px-3 border border-blue-500 hover:border-transparent rounded",on:{click:function(e){return t.update("Category")}}},[t._v(" Добавить категорию ")]),t.open?a("Modal",{on:{close:t.close}},[a(t.currentComponentInModal,{tag:"component",attrs:{id:t.dataItemId}})],1):t._e()],2)},i=[],c=(a("4de4"),a("5530")),l=a("2f62"),d=function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("transition",{attrs:{name:"modal"}},[a("div",{staticClass:"modal-mask"},[a("div",{staticClass:"modal-wrapper"},[a("div",{staticClass:"modal-container"},[t._t("default")],2)])])])},u=[],m={},f=m,b=(a("22d7"),a("2877")),p=Object(b["a"])(f,d,u,!1,null,null,null),v=p.exports,g=function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",[a("div",{staticClass:"mb-6 text-xl"},[t._v(" "+t._s(t.isModeUpdate?"Обновить аккаунт":"Добавить аккаунт")+" ")]),a("div",{staticClass:"md:flex md:items-center mb-6"},[t._m(0),a("div",{staticClass:"md:w-2/3"},[a("input",{directives:[{name:"model",rawName:"v-model",value:t.model.name,expression:"model.name"}],staticClass:"appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500",class:{"border-red-500":t.$v.model.name.$error},attrs:{type:"text"},domProps:{value:t.model.name},on:{input:function(e){e.target.composing||t.$set(t.model,"name",e.target.value)}}})])]),a("div",{staticClass:"md:flex md:items-center mb-6"},[t._m(1),a("div",{staticClass:"md:w-2/3"},[a("div",{staticClass:"relative"},[a("select",{directives:[{name:"model",rawName:"v-model",value:t.model.currency,expression:"model.currency"}],staticClass:"block appearance-none w-full bg-gray-200 border border-gray-200 text-gray-700 py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-gray-500",class:{"border-red-500":t.$v.model.currency.$error},on:{change:function(e){var a=Array.prototype.filter.call(e.target.options,(function(t){return t.selected})).map((function(t){var e="_value"in t?t._value:t.value;return e}));t.$set(t.model,"currency",e.target.multiple?a:a[0])}}},[a("option",[t._v("RUB")]),a("option",[t._v("USD")])]),a("div",{staticClass:"pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700"},[a("svg",{staticClass:"fill-current h-4 w-4",attrs:{xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 20 20"}},[a("path",{attrs:{d:"M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"}})])])])])]),a("div",{staticClass:"md:flex md:items-center mb-6"},[t._m(2),a("div",{staticClass:"md:w-2/3"},[a("textarea",{directives:[{name:"model",rawName:"v-model",value:t.model.description,expression:"model.description"}],staticClass:"appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500",domProps:{value:t.model.description},on:{input:function(e){e.target.composing||t.$set(t.model,"description",e.target.value)}}})])]),a("button",{staticClass:"button",on:{click:t.close}},[t._v(" Отменить ")]),a("button",{staticClass:"button",on:{click:t.submit}},[t._v(" "+t._s(t.isModeUpdate?"Обновить":"Добавить")+" ")])])},h=[function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",{staticClass:"md:w-1/3"},[a("label",{staticClass:"block text-gray-500 font-bold md:text-right mb-1 md:mb-0 pr-4",attrs:{for:"inline-full-name"}},[t._v(" Название ")])])},function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",{staticClass:"md:w-1/3"},[a("label",{staticClass:"block text-gray-500 font-bold md:text-right mb-1 md:mb-0 pr-4",attrs:{for:"inline-full-name"}},[t._v(" Валюта ")])])},function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",{staticClass:"md:w-1/3"},[a("label",{staticClass:"block text-gray-500 font-bold md:text-right mb-1 md:mb-0 pr-4",attrs:{for:"inline-full-name"}},[t._v(" Описание ")])])}],y=(a("a4d3"),a("e01a"),a("b0c0"),a("a9e3"),a("b5ae")),j={props:{id:{type:Number,default:function(){return null}}},data:function(){return{model:{id:null,name:"",currency:"",description:""}}},validations:{model:{name:{required:y["required"]},currency:{required:y["required"]}}},computed:Object(c["a"])(Object(c["a"])({},Object(l["c"])("account",["getAccountById"])),{},{accountToEdit:function(){return this.getAccountById(this.id)},isModeUpdate:function(){return this.accountToEdit}}),created:function(){if(this.accountToEdit){var t=this.accountToEdit,e=t.id,a=t.name,r=t.currency,n=t.description;this.model={id:e,name:a,currency:r,description:n}}},methods:Object(c["a"])(Object(c["a"])({},Object(l["b"])("account",["update"])),{},{submit:function(){var t=this;this.$v.$touch(),this.$v.$invalid||this.update(this.model).then((function(){t.$noty.success("Success!"),t.$parent.$emit("close")})).catch((function(){t.$noty.error("Oops, something went wrong!")}))},close:function(){this.$parent.$emit("close")}})},x=j,w=Object(b["a"])(x,g,h,!1,null,null,null),_=w.exports,C=function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",[a("div",{staticClass:"mb-6 text-xl"},[t._v(" "+t._s(t.isModeUpdate?"Изменить категорию":"Добавить категорию")+" ")]),a("div",{staticClass:"md:flex md:items-center mb-6"},[t._m(0),a("div",{staticClass:"md:w-2/3"},[a("input",{directives:[{name:"model",rawName:"v-model",value:t.model.name,expression:"model.name"}],staticClass:"appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500",class:{"border-red-500":t.$v.model.name.$error},attrs:{type:"text"},domProps:{value:t.model.name},on:{input:function(e){e.target.composing||t.$set(t.model,"name",e.target.value)}}})])]),a("div",{staticClass:"md:flex md:items-center mb-6"},[t._m(1),a("div",{staticClass:"md:w-2/3"},[a("div",{staticClass:"relative"},[a("select",{directives:[{name:"model",rawName:"v-model",value:t.model.type,expression:"model.type"}],staticClass:"block appearance-none w-full bg-gray-200 border border-gray-200 text-gray-700 py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-gray-500",class:{"border-red-500":t.$v.model.type.$error},on:{change:function(e){var a=Array.prototype.filter.call(e.target.options,(function(t){return t.selected})).map((function(t){var e="_value"in t?t._value:t.value;return e}));t.$set(t.model,"type",e.target.multiple?a:a[0])}}},[a("option",{attrs:{value:"expense"}},[t._v("Расход")]),a("option",{attrs:{value:"income"}},[t._v("Приход")])]),a("div",{staticClass:"pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700"},[a("svg",{staticClass:"fill-current h-4 w-4",attrs:{xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 20 20"}},[a("path",{attrs:{d:"M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"}})])])])])]),a("div",{staticClass:"md:flex md:items-center mb-6"},[t._m(2),a("div",{staticClass:"md:w-2/3"},[a("textarea",{directives:[{name:"model",rawName:"v-model",value:t.model.description,expression:"model.description"}],staticClass:"appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500",domProps:{value:t.model.description},on:{input:function(e){e.target.composing||t.$set(t.model,"description",e.target.value)}}})])]),a("button",{staticClass:"button",on:{click:t.close}},[t._v(" Отменить ")]),a("button",{staticClass:"button",on:{click:t.submit}},[t._v(" "+t._s(t.isModeUpdate?"Изменить":"Добавить")+" ")])])},k=[function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",{staticClass:"md:w-1/3"},[a("label",{staticClass:"block text-gray-500 font-bold md:text-right mb-1 md:mb-0 pr-4",attrs:{for:"inline-full-name"}},[t._v(" Название ")])])},function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",{staticClass:"md:w-1/3"},[a("label",{staticClass:"block text-gray-500 font-bold md:text-right mb-1 md:mb-0 pr-4",attrs:{for:"inline-full-name"}},[t._v(" Тип ")])])},function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",{staticClass:"md:w-1/3"},[a("label",{staticClass:"block text-gray-500 font-bold md:text-right mb-1 md:mb-0 pr-4",attrs:{for:"inline-full-name"}},[t._v(" Описание ")])])}],O={props:{id:{type:Number,default:function(){return null}}},data:function(){return{model:{id:null,name:"",type:"",color:"",description:""}}},validations:{model:{name:{required:y["required"]},type:{required:y["required"]}}},computed:Object(c["a"])(Object(c["a"])({},Object(l["c"])("category",["getCategoryById"])),{},{categoryToEdit:function(){return this.getCategoryById(this.id)},isModeUpdate:function(){return this.categoryToEdit}}),created:function(){if(this.categoryToEdit){var t=this.categoryToEdit,e=t.id,a=t.name,r=t.type,n=t.color,s=t.description;this.model={id:e,name:a,type:r,color:n,description:s}}},methods:Object(c["a"])(Object(c["a"])({},Object(l["b"])("category",["update"])),{},{submit:function(){var t=this;this.$v.$touch(),this.$v.$invalid||this.update(this.model).then((function(){t.$noty.success("Success!"),t.$parent.$emit("close")})).catch((function(){t.$noty.error("Oops, something went wrong!")}))},close:function(){this.$parent.$emit("close")}})},$=O,D=Object(b["a"])($,C,k,!1,null,null,null),I=D.exports,E={components:{Modal:v,Account:_,Category:I},data:function(){return{open:!1,currentComponentInModal:"",dataItemId:null}},computed:Object(c["a"])(Object(c["a"])(Object(c["a"])({},Object(l["e"])("account",["accounts"])),Object(l["e"])("category",["categories"])),{},{categoriesIncome:function(){return this.categories.filter((function(t){return"income"===t.type}))},categoriesExpense:function(){return this.categories.filter((function(t){return"expense"===t.type}))}}),methods:{update:function(t){var e=arguments.length>1&&void 0!==arguments[1]?arguments[1]:null;this.currentComponentInModal=t,this.dataItemId=e,this.open=!0},close:function(){this.open=!1,this.currentComponentInModal="",this.dataItemId=null}}},A=E,M=Object(b["a"])(A,o,i,!1,null,null,null),z=M.exports,F={components:{Sidebar:z},mounted:function(){this.$store.dispatch("transaction/getList",{from:"2020-07-15",to:"2020-09-15"}),this.$store.dispatch("category/getList"),this.$store.dispatch("account/getList")}},L=F,S=(a("5c0b"),Object(b["a"])(L,n,s,!1,null,null,null)),U=S.exports,P=a("8c4f"),R=function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",[a("AmChart"),t.detailsDate?a("DetailsDashboard"):t._e(),a("table",{staticClass:"table-auto w-full"},[t._m(0),a("tbody",t._l(t.listItems,(function(e,r){return a("tr",{key:e.id,class:{"bg-gray-100":r%2===0}},[a("td",{staticClass:"border px-4 py-2"},[t._v(t._s(t.$moment(e.date).format("ll")))]),a("td",{staticClass:"border px-4 py-2"},[t._v(t._s(t.$numeral(+e.amount).format("0,0 $")))]),a("td",{staticClass:"border px-4 py-2"},[t._v(t._s(t.getCategoryNameById(e.category_id)))]),a("td",{staticClass:"border px-4 py-2"},[t._v(t._s(e.description))])])})),0)])],1)},T=[function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("thead",[a("tr",[a("th",{staticClass:"px-4 py-2"},[t._v("Дата")]),a("th",{staticClass:"px-4 py-2"},[t._v("Сумма")]),a("th",{staticClass:"px-4 py-2"},[t._v("Категория")]),a("th",{staticClass:"px-4 py-2"},[t._v("Описание")])])])}],N=function(){var t=this,e=t.$createElement;t._self._c;return t._m(0)},B=[function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",[a("div",{attrs:{id:"chartdiv"}})])}],X=(a("cb29"),a("71c9")),Y=a("c497"),q=a("5a54"),G=a("3788");X["d"](q["a"]);var V={watch:{listItems:function(){this.renderChart()}},computed:Object(c["a"])({},Object(l["e"])("transaction",{listItems:function(t){return t.fakeData}})),mounted:function(){var t=this,e=X["b"]("chartdiv",Y["h"]);e.language.locale=G["a"],e.leftAxesContainer.layout="vertical",e.seriesContainer.zIndex=-1;var a=e.xAxes.push(new Y["b"]);a.groupData=!0,a.groupCount=180,a.groupIntervals.setAll([{timeUnit:"day",count:1},{timeUnit:"month",count:1}]),a.renderer.minGridDistance=60,a.renderer.grid.template.location=0,a.renderer.grid.template.disabled=!0,a.events.on("globalscalechanged",(function(){console.log("d")}));var r=e.yAxes.push(new Y["g"]);r.cursorTooltipEnabled=!1,r.zIndex=1,r.height=X["c"](35),r.renderer.grid.template.disabled=!0,r.renderer.labels.template.disabled=!0;var n=e.yAxes.push(new Y["g"]);n.cursorTooltipEnabled=!1,n.zIndex=3,n.height=X["c"](65),n.marginTop=30,n.renderer.gridContainer.background.fill=X["a"]("#000000"),n.renderer.gridContainer.background.fillOpacity=.01,e.legend=new Y["c"],e.cursor=new Y["j"],e.cursor.fullWidthLineX=!0,e.cursor.xAxis=a,e.cursor.lineX.strokeOpacity=0,e.cursor.lineX.fill=X["a"]("#000"),e.cursor.lineX.fillOpacity=.1,e.cursor.lineY.strokeOpacity=0;var s=e.series.push(new Y["a"]);s.name="Приход",s.yAxis=n,s.dataFields.valueY="income",s.dataFields.dateX="date",s.groupFields.valueY="sum",s.stroke=X["a"]("#19ffa3"),s.columns.template.stroke=X["a"]("#19ffa3"),s.columns.template.fill=X["a"]("#19ffa3"),s.columns.template.fillOpacity=.5,s.defaultState.transitionDuration=0;var o=e.series.push(new Y["a"]);o.name="Расход",o.yAxis=n,o.dataFields.valueY="expense",o.dataFields.dateX="date",o.groupFields.valueY="sum",o.stroke=X["a"]("#ff604a"),o.columns.template.stroke=X["a"]("#ff604a"),o.columns.template.fill=X["a"]("#ff604a"),o.columns.template.fillOpacity=.5,o.defaultState.transitionDuration=0;var i=e.series.push(new Y["d"]);i.name="Баланс",i.yAxis=r,i.dataFields.valueY="balance",i.dataFields.dateX="date",i.groupFields.valueY="sum",i.stroke=X["a"]("#19ffa3"),i.strokeWidth=3,i.strokeOpacity=.8,i.defaultState.transitionDuration=0,i.adapter.add("tooltipHTML",(function(a){var r,n="<div class=\"mb-2\"><strong>{dateX.formatDate('d MMMM yyyy')}</strong></div>";return e.series.each((function(e,a){n+='<div class="text-sm"><span style="color:'+e.stroke.hex+'">●</span> '+e.name+": "+t.$numeral(e.tooltipDataItem.valueY).format("0,0 $")+"</div>",2===a&&(r=e.tooltipDataItem.groupDataItems?"month":"day")})),n+="<button onclick=\"toggleDateForDetails('{dateX}', '"+r+'\')" class="bg-blue-500 hover:bg-blue-700 text-sm text-white font-bold py-2 px-4 rounded my-2">Подробнее</a>',n})),i.tooltip.getFillFromObject=!1,i.tooltip.background.filters.clear(),i.tooltip.background.fill=X["a"]("#000"),i.tooltip.background.fillOpacity=.8,i.tooltip.background.strokeWidth=0,i.tooltip.background.cornerRadius=1,i.tooltip.label.interactionsEnabled=!0,i.tooltip.pointerOrientation="vertical";var c=r.createSeriesRange(i);c.value=0,c.endValue=-1e7,c.contents.stroke=X["a"]("#ff604a"),c.contents.strokeOpacity=.7;var l=a.axisRanges.create();l.date=new Date(2020,0,3),l.grid.stroke=X["a"]("#000000"),l.grid.strokeWidth=1,l.grid.strokeOpacity=.6;var d=a.axisRanges.create();d.date=new Date(2020,0,3),d.endDate=new Date(2022,0,3),d.grid.disabled=!0,d.axisFill.fillOpacity=.6,d.axisFill.fill="#FFFFFF";var u=new Y["i"];u.series.push(i),u.marginTop=20,u.marginBottom=20,e.scrollbarX=u,e.scrollbarX.scrollbarChart.plotContainer.filters.clear(),e.scrollbarX.parent=e.bottomAxesContainer;var m=e.scrollbarX.scrollbarChart.series.getIndex(0);m.strokeWidth=1,m.strokeOpacity=.4;var f=e.scrollbarX.scrollbarChart.yAxes.getIndex(0),b=f.createSeriesRange(e.scrollbarX.scrollbarChart.series.getIndex(0));b.value=0,b.endValue=-1e7,b.contents.stroke="#ff604a",b.contents.fill="#ff604a",this.chart=e,window.toggleDateForDetails=function(e,a){t.setDetailsDate({date:e,interval:a})}},methods:Object(c["a"])(Object(c["a"])({},Object(l["d"])({setDetailsDate:"transaction/setDetailsDate"})),{},{renderChart:function(){this.chart.data=this.listItems}})},W=V,H=(a("d929"),Object(b["a"])(W,N,B,!1,null,null,null)),J=H.exports,K=function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("transition",{attrs:{name:"fade",mode:"out-in"}},[a("div",{staticClass:"p-6 bg-gray-100 mb-10 rounded border"},[a("div",{staticClass:"flex justify-between mb-6"},[a("div",[a("h3",{staticClass:"text-2xl"},[t._v(" "+t._s(t.$moment(t.detailsDate).format("LL"))+" ")])]),a("div",[a("div",{staticClass:"cursor-pointer border-gray-900 border-b",on:{click:t.close}},[t._v(" Закрыть ")])])]),a("div",{staticClass:"flex"},[a("div",{staticClass:"w-2/5 pr-6"},[a("div",{staticClass:"flex items-center"},[a("div",{staticClass:"mr-6"},[a("div",{staticClass:"text-xl"},[t._v("Приход:")])]),a("div",[a("div",{staticClass:"text-2xl border px-3 rounded bg-green-100 border-green-300 text-green-900"},[t._v(" "+t._s(t.$numeral(t.detailsDataGenerator.income.total).format("0,0 $"))+" ")])])]),a("ChartPie",{attrs:{data:t.detailsDataGenerator.income.items}})],1),a("div",{staticClass:"w-2/5 pr-6"},[a("div",{staticClass:"flex items-center"},[a("div",{staticClass:"mr-6"},[a("div",{staticClass:"text-xl"},[t._v("Расход:")])]),a("div",[a("div",{staticClass:"text-2xl border px-3 rounded bg-red-100 border-red-300 text-red-900"},[t._v(" "+t._s(t.$numeral(-t.detailsDataGenerator.expense.total).format("0,0 $"))+" ")])])]),a("ChartPie",{attrs:{data:t.detailsDataGenerator.expense.items}})],1),a("div",{staticClass:"w-1/5"},[a("div",{staticClass:"text-xl mb-6"},[t._v("Баланс")]),a("div",{staticClass:"text-2xl border px-3 rounded bg-green-100 border-green-300 inline-block"},[t._v(" "+t._s(t.$numeral(t.detailsDataGenerator.balance.total).format("0,0 $"))+" ")])])])])])},Q=[],Z=function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",{ref:"chart",staticClass:"chart"})},tt=[],et={props:{data:{type:Array,default:function(){return[]}}},beforeDestroy:function(){this.chart&&this.chart.dispose()},mounted:function(){var t=X["b"](this.$refs.chart,Y["e"]);t.language.locale=G["a"],t.data=this.data;var e=t.series.push(new Y["f"]);e.dataFields.value="amount",e.dataFields.category="category_name",e.slices.template.propertyFields.fill="category_color",e.innerRadius=X["c"](40),e.labels.template.disabled=!0,e.slices.template.stroke=X["a"]("#4a2abb"),t.legend=new Y["c"],t.legend.position="right",this.chart=t}},at=et,rt=(a("8eca"),Object(b["a"])(at,Z,tt,!1,null,null,null)),nt=rt.exports,st={components:{ChartPie:nt},computed:Object(c["a"])(Object(c["a"])(Object(c["a"])({},Object(l["e"])("transaction",["detailsDate","detailsDateIntervalUnit"])),Object(l["c"])("category",["detailsDataGenerator"])),Object(l["c"])("transaction",["getDetailsDateInterval"])),methods:Object(c["a"])(Object(c["a"])({},Object(l["d"])({setDetailsDate:"transaction/setDetailsDate"})),{},{close:function(){this.setDetailsDate({date:null})}})},ot=st,it=Object(b["a"])(ot,K,Q,!1,null,null,null),ct=it.exports,lt={components:{AmChart:J,DetailsDashboard:ct},computed:Object(c["a"])(Object(c["a"])({},Object(l["e"])("transaction",["detailsDate","listItems"])),Object(l["c"])("category",["getCategoryNameById"]))},dt=lt,ut=Object(b["a"])(dt,R,T,!1,null,null,null),mt=ut.exports,ft=function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div")},bt=[],pt={},vt=Object(b["a"])(pt,ft,bt,!1,null,null,null),gt=vt.exports;r["a"].use(P["a"]);var ht=[{path:"/",name:"Home",component:mt},{path:"/about",name:"About",component:gt}],yt=new P["a"]({mode:"history",base:"/webasyst/cash/",routes:ht}),jt=yt,xt=(a("96cf"),a("1da1")),wt=a("bc3a"),_t=a.n(wt),Ct=_t.a.create({baseURL:Object({NODE_ENV:"production",BASE_URL:"/webasyst/cash/"}).VUE_APP_BASE_URL||(window.appState?"".concat(window.appState.baseUrl,"api.php"):""),params:{access_token:Object({NODE_ENV:"production",BASE_URL:"/webasyst/cash/"}).VUE_APP_API_TOKEN||(window.appState?window.appState.token:"")}}),kt=(a("d81d"),a("c1df")),Ot=a.n(kt);function $t(t,e){var a=arguments.length>2&&void 0!==arguments[2]?arguments[2]:0;a>20&&(t=-t,e=-e),a>80&&(t=-t,e=-e);var r=t-.5+Math.random()*(e-t+1);return Math.round(r)}var Dt=function(t,e){var a=Ot()(t),r=Ot()(e),n=r.diff(a,"days")+1,s=new Array(n).fill(null).map((function(e,a){return{date:Ot()(t).add(a,"d").format("YYYY-MM-DD"),income:$t(1e5,3e5),expense:$t(1e4,1e5),balance:$t(1e4,1e5,a/n*100)}}));return s},It={namespaced:!0,state:function(){return{listItems:[],fakeData:[],detailsDate:null,detailsDateIntervalUnit:null}},mutations:{setItems:function(t,e){t.listItems=e},setFakeItems:function(t,e){t.fakeData=e},setDetailsDate:function(t,e){var a=e.date,r=e.interval,n=void 0===r?null:r;t.detailsDate=a,t.detailsDateIntervalUnit=n}},actions:{getList:function(t,e){return Object(xt["a"])(regeneratorRuntime.mark((function a(){var r,n,s;return regeneratorRuntime.wrap((function(a){while(1)switch(a.prev=a.next){case 0:return r=t.commit,a.prev=1,a.next=4,Ct.get("cash.transaction.getList",{params:{from:e.from,to:e.to}});case 4:n=a.sent,s=n.data,r("setItems",s),a.next=11;break;case 9:a.prev=9,a.t0=a["catch"](1);case 11:r("setFakeItems",Dt("2018-08-15","2021-02-15"));case 12:case"end":return a.stop()}}),a,null,[[1,9]])})))()}}},Et=(a("7db0"),a("fb6a"),a("2909")),At={namespaced:!0,state:function(){return{categories:[]}},getters:{getCategoryById:function(t){return function(e){return t.categories.find((function(t){return t.id===e}))}},getCategoryNameById:function(t){return function(e){var a=t.categories.find((function(t){return t.id===e}));return a?a.name:""}},detailsDataGenerator:function(t){function e(t,e){var a=arguments.length>2&&void 0!==arguments[2]?arguments[2]:0;a>20&&(t=-t,e=-e),a>80&&(t=-t,e=-e);var r=t-.5+Math.random()*(e-t+1);return Math.round(r)}var a=Object(Et["a"])(t.categories.filter((function(t){return"income"===t.type}))).sort((function(){return Math.random()-.5})).slice(0,6).map((function(t){return{category_id:t.id,category_name:t.name,category_color:t.color,amount:e(1e3,4e3)}})),r=Object(Et["a"])(t.categories.filter((function(t){return"expense"===t.type}))).sort((function(){return Math.random()-.5})).slice(0,6).map((function(t){return{category_id:t.id,category_name:t.name,category_color:t.color,amount:e(1e3,4e3)}})),n={date:"",income:{total:2343545,items:a},expense:{total:4349545,items:r},balance:{total:85948367}};return n}},mutations:{setCategories:function(t,e){t.categories=e}},actions:{getList:function(t){return Object(xt["a"])(regeneratorRuntime.mark((function e(){var a,r,n;return regeneratorRuntime.wrap((function(e){while(1)switch(e.prev=e.next){case 0:return a=t.commit,e.next=3,Ct.get("cash.category.getList");case 3:r=e.sent,n=r.data,a("setCategories",n);case 6:case"end":return e.stop()}}),e)})))()},update:function(t,e){return Object(xt["a"])(regeneratorRuntime.mark((function a(){var r,n,s;return regeneratorRuntime.wrap((function(a){while(1)switch(a.prev=a.next){case 0:return t.commit,r=e.id?"update":"create",a.next=4,Ct.post("cash.category.".concat(r),Object(c["a"])({},e));case 4:n=a.sent,s=n.data,console.log(s);case 7:case"end":return a.stop()}}),a)})))()}}},Mt={namespaced:!0,state:function(){return{accounts:[]}},getters:{getAccountById:function(t){return function(e){return t.accounts.find((function(t){return t.id===e}))}}},mutations:{setAccounts:function(t,e){t.accounts=e}},actions:{getList:function(t){return Object(xt["a"])(regeneratorRuntime.mark((function e(){var a,r,n;return regeneratorRuntime.wrap((function(e){while(1)switch(e.prev=e.next){case 0:return a=t.commit,e.next=3,Ct.get("cash.account.getList");case 3:r=e.sent,n=r.data,a("setAccounts",n);case 6:case"end":return e.stop()}}),e)})))()},update:function(t,e){return Object(xt["a"])(regeneratorRuntime.mark((function a(){var r,n,s;return regeneratorRuntime.wrap((function(a){while(1)switch(a.prev=a.next){case 0:return t.commit,r=e.id?"update":"create",a.next=4,Ct.post("cash.account.".concat(r),Object(c["a"])({},e));case 4:n=a.sent,s=n.data,console.log(s);case 7:case"end":return a.stop()}}),a)})))()}}};r["a"].use(l["a"]);var zt=new l["a"].Store({modules:{transaction:It,category:At,account:Mt}}),Ft=a("6612"),Lt=a.n(Ft);Lt.a.register("locale","ru",{delimiters:{thousands:" ",decimal:","},currency:{symbol:"₽"}}),Lt.a.locale("ru"),Ot.a.locale("ru");var St={install:function(t,e){t.prototype.$numeral=Lt.a,t.prototype.$moment=Ot.a}},Ut=a("1dce"),Pt=a.n(Ut),Rt=a("7329"),Tt=a.n(Rt),Nt=(a("d07f"),a("60ba"),{layout:"topCenter",theme:"relax",timeout:5e3,progressBar:!0,closeWith:["click"]}),Bt={options:{},setOptions:function(t){return this.options=Object.assign({},Nt,t),this},create:function(t){return new Tt.a(t)},show:function(t){var e=arguments.length>1&&void 0!==arguments[1]?arguments[1]:"alert",a=arguments.length>2&&void 0!==arguments[2]?arguments[2]:{},r=Object.assign({},this.options,a,{type:e,text:t}),n=this.create(r);return n.show(),n},success:function(t){var e=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{};return this.show(t,"success",e)},error:function(t){var e=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{};return this.show(t,"error",e)},warning:function(t){var e=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{};return this.show(t,"warning",e)},info:function(t){var e=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{};return this.show(t,"info",e)}},Xt={install:function(t,e){var a=Bt.setOptions(e);t.prototype.$noty=a,t.noty=a}};r["a"].config.productionTip=!1,r["a"].use(St),r["a"].use(Xt),r["a"].use(Pt.a),new r["a"]({router:jt,store:zt,render:function(t){return t(U)}}).$mount("#app")},"5c0b":function(t,e,a){"use strict";var r=a("9c0c"),n=a.n(r);n.a},"8eca":function(t,e,a){"use strict";var r=a("9a6d"),n=a.n(r);n.a},"9a6d":function(t,e,a){},"9c0c":function(t,e,a){},be87:function(t,e,a){},d74f:function(t,e,a){},d929:function(t,e,a){"use strict";var r=a("d74f"),n=a.n(r);n.a}});