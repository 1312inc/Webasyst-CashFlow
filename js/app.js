(function(e){function t(t){for(var r,c,o=t[0],i=t[1],u=t[2],l=0,f=[];l<o.length;l++)c=o[l],Object.prototype.hasOwnProperty.call(s,c)&&s[c]&&f.push(s[c][0]),s[c]=0;for(r in i)Object.prototype.hasOwnProperty.call(i,r)&&(e[r]=i[r]);d&&d(t);while(f.length)f.shift()();return n.push.apply(n,u||[]),a()}function a(){for(var e,t=0;t<n.length;t++){for(var a=n[t],r=!0,c=1;c<a.length;c++){var i=a[c];0!==s[i]&&(r=!1)}r&&(n.splice(t--,1),e=o(o.s=a[0]))}return e}var r={},s={app:0},n=[];function c(e){return o.p+"js/"+({canvg:"canvg",pdfmake:"pdfmake",xlsx:"xlsx"}[e]||e)+".js"}function o(t){if(r[t])return r[t].exports;var a=r[t]={i:t,l:!1,exports:{}};return e[t].call(a.exports,a,a.exports,o),a.l=!0,a.exports}o.e=function(e){var t=[],a=s[e];if(0!==a)if(a)t.push(a[2]);else{var r=new Promise((function(t,r){a=s[e]=[t,r]}));t.push(a[2]=r);var n,i=document.createElement("script");i.charset="utf-8",i.timeout=120,o.nc&&i.setAttribute("nonce",o.nc),i.src=c(e);var u=new Error;n=function(t){i.onerror=i.onload=null,clearTimeout(l);var a=s[e];if(0!==a){if(a){var r=t&&("load"===t.type?"missing":t.type),n=t&&t.target&&t.target.src;u.message="Loading chunk "+e+" failed.\n("+r+": "+n+")",u.name="ChunkLoadError",u.type=r,u.request=n,a[1](u)}s[e]=void 0}};var l=setTimeout((function(){n({type:"timeout",target:i})}),12e4);i.onerror=i.onload=n,document.head.appendChild(i)}return Promise.all(t)},o.m=e,o.c=r,o.d=function(e,t,a){o.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:a})},o.r=function(e){"undefined"!==typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},o.t=function(e,t){if(1&t&&(e=o(e)),8&t)return e;if(4&t&&"object"===typeof e&&e&&e.__esModule)return e;var a=Object.create(null);if(o.r(a),Object.defineProperty(a,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var r in e)o.d(a,r,function(t){return e[t]}.bind(null,r));return a},o.n=function(e){var t=e&&e.__esModule?function(){return e["default"]}:function(){return e};return o.d(t,"a",t),t},o.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},o.p="/webasyst/cash/",o.oe=function(e){throw console.error(e),e};var i=window["webpackJsonp"]=window["webpackJsonp"]||[],u=i.push.bind(i);i.push=t,i=i.slice();for(var l=0;l<i.length;l++)t(i[l]);var d=u;n.push([0,"chunk-vendors"]),a()})({0:function(e,t,a){e.exports=a("56d7")},"464a":function(e,t,a){},4678:function(e,t,a){var r={"./af":"2bfb","./af.js":"2bfb","./ar":"8e73","./ar-dz":"a356","./ar-dz.js":"a356","./ar-kw":"423e","./ar-kw.js":"423e","./ar-ly":"1cfd","./ar-ly.js":"1cfd","./ar-ma":"0a84","./ar-ma.js":"0a84","./ar-sa":"8230","./ar-sa.js":"8230","./ar-tn":"6d83","./ar-tn.js":"6d83","./ar.js":"8e73","./az":"485c","./az.js":"485c","./be":"1fc1","./be.js":"1fc1","./bg":"84aa","./bg.js":"84aa","./bm":"a7fa","./bm.js":"a7fa","./bn":"9043","./bn.js":"9043","./bo":"d26a","./bo.js":"d26a","./br":"6887","./br.js":"6887","./bs":"2554","./bs.js":"2554","./ca":"d716","./ca.js":"d716","./cs":"3c0d","./cs.js":"3c0d","./cv":"03ec","./cv.js":"03ec","./cy":"9797","./cy.js":"9797","./da":"0f14","./da.js":"0f14","./de":"b469","./de-at":"b3eb","./de-at.js":"b3eb","./de-ch":"bb71","./de-ch.js":"bb71","./de.js":"b469","./dv":"598a","./dv.js":"598a","./el":"8d47","./el.js":"8d47","./en-au":"0e6b","./en-au.js":"0e6b","./en-ca":"3886","./en-ca.js":"3886","./en-gb":"39a6","./en-gb.js":"39a6","./en-ie":"e1d3","./en-ie.js":"e1d3","./en-il":"7333","./en-il.js":"7333","./en-in":"ec2e","./en-in.js":"ec2e","./en-nz":"6f50","./en-nz.js":"6f50","./en-sg":"b7e9","./en-sg.js":"b7e9","./eo":"65db","./eo.js":"65db","./es":"898b","./es-do":"0a3c","./es-do.js":"0a3c","./es-us":"55c9","./es-us.js":"55c9","./es.js":"898b","./et":"ec18","./et.js":"ec18","./eu":"0ff2","./eu.js":"0ff2","./fa":"8df4","./fa.js":"8df4","./fi":"81e9","./fi.js":"81e9","./fil":"d69a","./fil.js":"d69a","./fo":"0721","./fo.js":"0721","./fr":"9f26","./fr-ca":"d9f8","./fr-ca.js":"d9f8","./fr-ch":"0e49","./fr-ch.js":"0e49","./fr.js":"9f26","./fy":"7118","./fy.js":"7118","./ga":"5120","./ga.js":"5120","./gd":"f6b4","./gd.js":"f6b4","./gl":"8840","./gl.js":"8840","./gom-deva":"aaf2","./gom-deva.js":"aaf2","./gom-latn":"0caa","./gom-latn.js":"0caa","./gu":"e0c5","./gu.js":"e0c5","./he":"c7aa","./he.js":"c7aa","./hi":"dc4d","./hi.js":"dc4d","./hr":"4ba9","./hr.js":"4ba9","./hu":"5b14","./hu.js":"5b14","./hy-am":"d6b6","./hy-am.js":"d6b6","./id":"5038","./id.js":"5038","./is":"0558","./is.js":"0558","./it":"6e98","./it-ch":"6f12","./it-ch.js":"6f12","./it.js":"6e98","./ja":"079e","./ja.js":"079e","./jv":"b540","./jv.js":"b540","./ka":"201b","./ka.js":"201b","./kk":"6d79","./kk.js":"6d79","./km":"e81d","./km.js":"e81d","./kn":"3e92","./kn.js":"3e92","./ko":"22f8","./ko.js":"22f8","./ku":"2421","./ku.js":"2421","./ky":"9609","./ky.js":"9609","./lb":"440c","./lb.js":"440c","./lo":"b29d","./lo.js":"b29d","./lt":"26f9","./lt.js":"26f9","./lv":"b97c","./lv.js":"b97c","./me":"293c","./me.js":"293c","./mi":"688b","./mi.js":"688b","./mk":"6909","./mk.js":"6909","./ml":"02fb","./ml.js":"02fb","./mn":"958b","./mn.js":"958b","./mr":"39bd","./mr.js":"39bd","./ms":"ebe4","./ms-my":"6403","./ms-my.js":"6403","./ms.js":"ebe4","./mt":"1b45","./mt.js":"1b45","./my":"8689","./my.js":"8689","./nb":"6ce3","./nb.js":"6ce3","./ne":"3a39","./ne.js":"3a39","./nl":"facd","./nl-be":"db29","./nl-be.js":"db29","./nl.js":"facd","./nn":"b84c","./nn.js":"b84c","./oc-lnc":"167b","./oc-lnc.js":"167b","./pa-in":"f3ff","./pa-in.js":"f3ff","./pl":"8d57","./pl.js":"8d57","./pt":"f260","./pt-br":"d2d4","./pt-br.js":"d2d4","./pt.js":"f260","./ro":"972c","./ro.js":"972c","./ru":"957c","./ru.js":"957c","./sd":"6784","./sd.js":"6784","./se":"ffff","./se.js":"ffff","./si":"eda5","./si.js":"eda5","./sk":"7be6","./sk.js":"7be6","./sl":"8155","./sl.js":"8155","./sq":"c8f3","./sq.js":"c8f3","./sr":"cf1e","./sr-cyrl":"13e9","./sr-cyrl.js":"13e9","./sr.js":"cf1e","./ss":"52bd","./ss.js":"52bd","./sv":"5fbd","./sv.js":"5fbd","./sw":"74dc","./sw.js":"74dc","./ta":"3de5","./ta.js":"3de5","./te":"5cbb","./te.js":"5cbb","./tet":"576c","./tet.js":"576c","./tg":"3b1b","./tg.js":"3b1b","./th":"10e8","./th.js":"10e8","./tk":"5aff","./tk.js":"5aff","./tl-ph":"0f38","./tl-ph.js":"0f38","./tlh":"cf75","./tlh.js":"cf75","./tr":"0e81","./tr.js":"0e81","./tzl":"cf51","./tzl.js":"cf51","./tzm":"c109","./tzm-latn":"b53d","./tzm-latn.js":"b53d","./tzm.js":"c109","./ug-cn":"6117","./ug-cn.js":"6117","./uk":"ada2","./uk.js":"ada2","./ur":"5294","./ur.js":"5294","./uz":"2e8c","./uz-latn":"010e","./uz-latn.js":"010e","./uz.js":"2e8c","./vi":"2921","./vi.js":"2921","./x-pseudo":"fd7e","./x-pseudo.js":"fd7e","./yo":"7f33","./yo.js":"7f33","./zh-cn":"5c3a","./zh-cn.js":"5c3a","./zh-hk":"49ab","./zh-hk.js":"49ab","./zh-mo":"3a6c","./zh-mo.js":"3a6c","./zh-tw":"90ea","./zh-tw.js":"90ea"};function s(e){var t=n(e);return a(t)}function n(e){if(!a.o(r,e)){var t=new Error("Cannot find module '"+e+"'");throw t.code="MODULE_NOT_FOUND",t}return r[e]}s.keys=function(){return Object.keys(r)},s.resolve=n,e.exports=s,s.id="4678"},"56d7":function(e,t,a){"use strict";a.r(t);a("e260"),a("e6cfa"),a("cca6"),a("a79d");var r=a("2b0e"),s=function(){var e=this,t=e.$createElement,a=e._self._c||t;return a("div",{attrs:{id:"app"}},[a("div",{attrs:{id:"nav"}},[a("router-link",{attrs:{to:"/"}},[e._v("Home")]),e._v(" | "),a("router-link",{attrs:{to:"/about"}},[e._v("About")]),e._v(" | "),a("a",{attrs:{href:"/webasyst/cash/?module=static"}},[e._v("Static page")])],1),a("keep-alive",[a("router-view")],1)],1)},n=[],c={mounted:function(){this.$store.dispatch("transaction/getList",{from:"2019-01-01",to:"2020-09-08"})}},o=c,i=(a("99a9"),a("2877")),u=Object(i["a"])(o,s,n,!1,null,"38fc0439",null),l=u.exports,d=a("8c4f"),f=function(){var e=this,t=e.$createElement,a=e._self._c||t;return a("div",[a("AmChart"),a("table",[e._m(0),a("tbody",e._l(e.listItems,(function(t){return a("tr",{key:t.id},[a("td",[e._v(e._s(e.$moment(t.date).format("ll")))]),a("td",[e._v(e._s(e.$numeral(+t.amount).format("0,0 $")))]),a("td",[e._v(e._s(t.category_id))]),a("td",[e._v(e._s(t.description))])])})),0)])],1)},b=[function(){var e=this,t=e.$createElement,a=e._self._c||t;return a("thead",[a("tr",[a("th",[e._v("Дата")]),a("th",[e._v("Сумма")]),a("th",[e._v("Категория")]),a("th",[e._v("Описание")])])])}],m=a("2f62"),j=function(){var e=this,t=e.$createElement;e._self._c;return e._m(0)},h=[function(){var e=this,t=e.$createElement,a=e._self._c||t;return a("div",[a("div",{attrs:{id:"chartdiv"}})])}],p=(a("cb29"),a("caad"),a("13d5"),a("b0c0"),a("2532"),a("2909")),v=a("5530"),g=a("71c9"),y=a("c497"),k=a("5a54");g["d"](k["a"]);var _={watch:{listItems:function(){this.renderChart()}},computed:Object(v["a"])(Object(v["a"])({},Object(m["c"])("transaction",{listItems:function(e){return e.fakeData}})),{},{categoriesInData:function(){return this.listItems.reduce((function(e,t){return e.includes(t.category_id)||e.push(t.category_id),e}),[])}}),mounted:function(){var e=g["c"]("chartdiv",y["e"]),t=e.xAxes.push(new y["b"]);t.groupData=!0,t.groupCount=180,t.groupIntervals.setAll([{timeUnit:"day",count:1},{timeUnit:"month",count:1}]),t.renderer.minGridDistance=60,t.renderer.fullWidthTooltip=!0;var a=e.yAxes.push(new y["d"]);a.cursorTooltipEnabled=!1,e.legend=new y["c"],e.cursor=new y["f"],e.cursor.maxTooltipDistance=0,e.cursor.fullWidthLineX=!0,e.cursor.xAxis=t,e.cursor.lineX.strokeOpacity=0,e.cursor.lineX.fill=g["b"]("#000"),e.cursor.lineX.fillOpacity=.1,e.cursor.lineY.strokeOpacity=0;var r=new g["a"];r.marginBottom=20,e.scrollbarX=r,this.chart=e},methods:Object(v["a"])(Object(v["a"])({},Object(m["b"])(["getList"])),{},{renderChart:function(){this.chart.data=Object(p["a"])(this.listItems).reverse();var e=this.chart.series.push(new y["a"]);e.name="Income",e.dataFields.valueY="income",e.dataFields.dateX="date",e.groupFields.valueY="sum",e.columns.template.column.fill="#19ffa3",e.columns.template.column.strokeWidth=0,e.columns.template.tooltipHTML='<b>{date}</b><br><a href="https://en.wikipedia.org/wiki/{category.urlEncode()}">{valueY}</a>';var t=this.chart.series.push(new y["a"]);t.name="Expense",t.dataFields.valueY="expense",t.dataFields.dateX="date",t.groupFields.valueY="sum",t.columns.template.column.fill="#ff604a",t.columns.template.column.strokeWidth=0,t.columns.template.tooltipHTML='<b>{date}</b><br><a href="https://en.wikipedia.org/wiki/{category.urlEncode()}">{valueY}</a>'}})},w=_,O=(a("7765"),Object(i["a"])(w,j,h,!1,null,null,null)),x=O.exports,E={components:{AmChart:x},computed:Object(m["c"])("transaction",["listItems"])},z=E,I=Object(i["a"])(z,f,b,!1,null,null,null),T=I.exports,$=function(){var e=this,t=e.$createElement,a=e._self._c||t;return a("div",[a("Chart")],1)},A=[],S=function(){var e=this,t=e.$createElement,a=e._self._c||t;return a("canvas",{ref:"chart"})},M=[],P=(a("99af"),a("4de4"),a("4160"),a("b64b"),a("07ac"),a("159b"),a("30ef")),C=a.n(P),L=["rgba(255, 99, 22, 0.5)","rgba(22, 99, 132, 0.5)","rgba(255, 199, 132, 0.5)","rgba(255, 99, 232, 0.5)","rgba(253, 99, 132, 0.5)","rgba(55, 99, 132, 0.5)","rgba(55, 129, 132, 0.5)","rgba(205, 199, 132, 0.5)","rgba(155, 99, 132, 0.5)","rgba(125, 119, 12, 0.5)"],D={name:"Chart",watch:{listItems:function(){this.renderChart()}},computed:Object(v["a"])(Object(v["a"])({},Object(m["c"])(["listItems"])),{},{datasets:function(){var e=this.listItems.reduce((function(e,t){return e.includes(t.date)||e.push(t.date),e}),[]),t=this.listItems.reduce((function(t,a,r,s){if(!t[a.category_id]){var n=[];e.forEach((function(e){var t=s.filter((function(t){return t.category_id===a.category_id&&t.date===e})).reduce((function(e,t){return e+ +t.amount}),0);n.push({x:e,y:Math.abs(t)})})),t[a.category_id]={type:"bar",label:a.category_id,stack:+a.amount>0?"Plus":"Minus",data:n,backgroundColor:L[Object.keys(t).length],borderWidth:1}}return t}),{});return Object.values(t).sort((function(e,t){return e.stack>t.stack?-1:e.stack<t.stack?1:0}))}}),mounted:function(){this.renderChart()},methods:{renderChart:function(){var e=this;this.chart&&this.chart.destroy();var t=this.$refs.chart;t.height=120,this.chart=new C.a(t,{data:{datasets:this.datasets},options:{tooltips:{xPadding:12,yPadding:12,cornerRadius:3,callbacks:{title:function(t){return e.$moment(t[0].label).format("ll")},label:function(t,a){var r=a.datasets[t.datasetIndex].label||"";return"".concat(r,": ").concat(e.$numeral(t.yLabel).format("0,0 $"))}}},scales:{xAxes:[{stacked:!0,distribution:"series",offset:!0,type:"time",time:{unit:"month"},gridLines:{display:!1}}],yAxes:[{stacked:!0,ticks:{beginAtZero:!0,callback:function(t,a,r){return e.$numeral(t).format("0,0 $")}}}]},layout:{padding:60}}})}}},Y=D,F=Object(i["a"])(Y,S,M,!1,null,null,null),U=F.exports,X={components:{Chart:U}},R=X,q=Object(i["a"])(R,$,A,!1,null,null,null),N=q.exports;r["a"].use(d["a"]);var W=[{path:"/",name:"Home",component:T},{path:"/about",name:"About",component:N}],H=new d["a"]({mode:"history",base:"/webasyst/cash/",routes:W}),B=H,K=(a("96cf"),a("1da1")),V=a("bc3a"),J=a.n(V),G=J.a.create({baseURL:"/api.php",params:{access_token:Object({NODE_ENV:"production",VUE_APP_BASE_URL:"/api.php",BASE_URL:"/webasyst/cash/"}).VUE_APP_API_TOKEN||(document.querySelector('meta[name="token"]')?document.querySelector('meta[name="token"]').content:"")}}),Z=a("c1df"),Q=a.n(Z);function ee(e,t){var a=e-.5+Math.random()*(t-e+1);return Math.round(a)}var te=[],ae=0;while(ae<1e3)te.push({date:Q()().add(-ae,"d").format("YYYY-MM-DD"),income:ee(1e5,3e5),expense:ee(1e4,1e5)}),ae++;var re=te,se={namespaced:!0,state:function(){return{listItems:[],fakeData:[]}},mutations:{SET_ITEMS:function(e,t){e.listItems=t},SET_FAKE_ITEMS:function(e,t){e.fakeData=t}},actions:{getList:function(e,t){return Object(K["a"])(regeneratorRuntime.mark((function a(){var r,s,n;return regeneratorRuntime.wrap((function(a){while(1)switch(a.prev=a.next){case 0:return r=e.commit,a.next=3,G.get("cash.transaction.getList",{params:{from:t.from,to:t.to}});case 3:s=a.sent,n=s.data,r("SET_ITEMS",n),r("SET_FAKE_ITEMS",re);case 7:case"end":return a.stop()}}),a)})))()}}};r["a"].use(m["a"]);var ne=new m["a"].Store({modules:{transaction:se}}),ce=a("6612"),oe=a.n(ce);oe.a.register("locale","ru",{delimiters:{thousands:" ",decimal:","},currency:{symbol:"₽"}}),oe.a.locale("ru"),Q.a.locale("ru");var ie={install:function(e,t){e.prototype.$numeral=oe.a,e.prototype.$moment=Q.a}};r["a"].config.productionTip=!1,r["a"].use(ie),new r["a"]({router:B,store:ne,render:function(e){return e(l)}}).$mount("#app")},"5c02":function(e,t,a){},7765:function(e,t,a){"use strict";var r=a("5c02"),s=a.n(r);s.a},"99a9":function(e,t,a){"use strict";var r=a("464a"),s=a.n(r);s.a}});