/*!js/librarys/zepto/zepto.js*/
;
var Zepto = function() {
    function t(t) {
        return null == t ? String(t) : W[X.call(t)] || "object"
    }
    function n(n) {
        return "function" == t(n)
    }
    function e(t) {
        return null != t && t == t.window
    }
    function i(t) {
        return null != t && t.nodeType == t.DOCUMENT_NODE
    }
    function r(n) {
        return "object" == t(n)
    }
    function o(t) {
        return r(t) && !e(t) && Object.getPrototypeOf(t) == Object.prototype
    }
    function s(t) {
        return "number" == typeof t.length
    }
    function c(t) {
        return $.call(t,
            function(t) {
                return null != t
            })
    }
    function u(t) {
        return t.length > 0 ? C.fn.concat.apply([], t) : t
    }
    function a(t) {
        return t.replace(/::/g, "/").replace(/([A-Z]+)([A-Z][a-z])/g, "$1_$2").replace(/([a-z\d])([A-Z])/g, "$1_$2").replace(/_/g, "-").toLowerCase()
    }
    function l(t) {
        return t in M ? M[t] : M[t] = new RegExp("(^|\\s)" + t + "(\\s|$)")
    }
    function f(t, n) {
        return "number" != typeof n || B[a(t)] ? n: n + "px"
    }
    function h(t) {
        var n, e;
        return j[t] || (n = P.createElement(t), P.body.appendChild(n), e = getComputedStyle(n, "").getPropertyValue("display"), n.parentNode.removeChild(n), "none" == e && (e = "block"), j[t] = e),
            j[t]
    }
    function p(t) {
        return "children" in t ? L.call(t.children) : C.map(t.childNodes,
            function(t) {
                return 1 == t.nodeType ? t: void 0
            })
    }
    function d(t, n) {
        var e, i = t ? t.length: 0;
        for (e = 0; i > e; e++) this[e] = t[e];
        this.length = i,
            this.selector = n || ""
    }
    function m(t, n, e) {
        for (x in n) e && (o(n[x]) || Q(n[x])) ? (o(n[x]) && !o(t[x]) && (t[x] = {}), Q(n[x]) && !Q(t[x]) && (t[x] = []), m(t[x], n[x], e)) : n[x] !== N && (t[x] = n[x])
    }
    function g(t, n) {
        return null == n ? C(t) : C(t).filter(n)
    }
    function y(t, e, i, r) {
        return n(e) ? e.call(t, i, r) : e
    }
    function v(t, n, e) {
        null == e ? t.removeAttribute(n) : t.setAttribute(n, e)
    }
    function w(t, n) {
        var e = t.className || "",
            i = e && e.baseVal !== N;
        return n === N ? i ? e.baseVal: e: void(i ? e.baseVal = n: t.className = n)
    }
    function b(t) {
        try {
            return t ? "true" == t || ("false" == t ? !1 : "null" == t ? null: +t + "" == t ? +t: /^[\[\{]/.test(t) ? C.parseJSON(t) : t) : t
        } catch(n) {
            return t
        }
    }
    function E(t, n) {
        n(t);
        for (var e = 0,
                 i = t.childNodes.length; i > e; e++) E(t.childNodes[e], n)
    }
    var N, x, C, O, T, S, A = [],
        Z = A.concat,
        $ = A.filter,
        L = A.slice,
        P = window.document,
        j = {},
        M = {},
        B = {
            "column-count": 1,
            columns: 1,
            "font-weight": 1,
            "line-height": 1,
            opacity: 1,
            "z-index": 1,
            zoom: 1
        },
        q = /^\s*<(\w+|!)[^>]*>/,
        z = /^<(\w+)\s*\/?>(?:<\/\1>|)$/,
        k = /<(?!area|br|col|embed|hr|img|input|link|meta|param)(([\w:]+)[^>]*)\/>/gi,
        R = /^(?:body|html)$/i,
        V = /([A-Z])/g,
        F = ["val", "css", "html", "text", "data", "width", "height", "offset"],
        D = ["after", "prepend", "before", "append"],
        H = P.createElement("table"),
        I = P.createElement("tr"),
        J = {
            tr: P.createElement("tbody"),
            tbody: H,
            thead: H,
            tfoot: H,
            td: I,
            th: I,
            "*": P.createElement("div")
        },
        U = /complete|loaded|interactive/,
        _ = /^[\w-]*$/,
        W = {},
        X = W.toString,
        Y = {},
        G = P.createElement("div"),
        K = {
            tabindex: "tabIndex",
            readonly: "readOnly",
            "for": "htmlFor",
            "class": "className",
            maxlength: "maxLength",
            cellspacing: "cellSpacing",
            cellpadding: "cellPadding",
            rowspan: "rowSpan",
            colspan: "colSpan",
            usemap: "useMap",
            frameborder: "frameBorder",
            contenteditable: "contentEditable"
        },
        Q = Array.isArray ||
            function(t) {
                return t instanceof Array
            };
    return Y.matches = function(t, n) {
        if (!n || !t || 1 !== t.nodeType) return ! 1;
        var e = t.webkitMatchesSelector || t.mozMatchesSelector || t.oMatchesSelector || t.matchesSelector;
        if (e) return e.call(t, n);
        var i, r = t.parentNode,
            o = !r;
        return o && (r = G).appendChild(t),
            i = ~Y.qsa(r, n).indexOf(t),
        o && G.removeChild(t),
            i
    },
        T = function(t) {
            return t.replace(/-+(.)?/g,
                function(t, n) {
                    return n ? n.toUpperCase() : ""
                })
        },
        S = function(t) {
            return $.call(t,
                function(n, e) {
                    return t.indexOf(n) == e
                })
        },
        Y.fragment = function(t, n, e) {
            var i, r, s;
            return z.test(t) && (i = C(P.createElement(RegExp.$1))),
            i || (t.replace && (t = t.replace(k, "<$1></$2>")), n === N && (n = q.test(t) && RegExp.$1), n in J || (n = "*"), s = J[n], s.innerHTML = "" + t, i = C.each(L.call(s.childNodes),
                function() {
                    s.removeChild(this)
                })),
            o(e) && (r = C(i), C.each(e,
                function(t, n) {
                    F.indexOf(t) > -1 ? r[t](n) : r.attr(t, n)
                })),
                i
        },
        Y.Z = function(t, n) {
            return new d(t, n)
        },
        Y.isZ = function(t) {
            return t instanceof Y.Z
        },
        Y.init = function(t, e) {
            var i;
            if (!t) return Y.Z();
            if ("string" == typeof t) if (t = t.trim(), "<" == t[0] && q.test(t)) i = Y.fragment(t, RegExp.$1, e),
                t = null;
            else {
                if (e !== N) return C(e).find(t);
                i = Y.qsa(P, t)
            } else {
                if (n(t)) return C(P).ready(t);
                if (Y.isZ(t)) return t;
                if (Q(t)) i = c(t);
                else if (r(t)) i = [t],
                    t = null;
                else if (q.test(t)) i = Y.fragment(t.trim(), RegExp.$1, e),
                    t = null;
                else {
                    if (e !== N) return C(e).find(t);
                    i = Y.qsa(P, t)
                }
            }
            return Y.Z(i, t)
        },
        C = function(t, n) {
            return Y.init(t, n)
        },
        C.extend = function(t) {
            var n, e = L.call(arguments, 1);
            return "boolean" == typeof t && (n = t, t = e.shift()),
                e.forEach(function(e) {
                    m(t, e, n)
                }),
                t
        },
        Y.qsa = function(t, n) {
            var e, i = "#" == n[0],
                r = !i && "." == n[0],
                o = i || r ? n.slice(1) : n,
                s = _.test(o);
            return t.getElementById && s && i ? (e = t.getElementById(o)) ? [e] : [] : 1 !== t.nodeType && 9 !== t.nodeType && 11 !== t.nodeType ? [] : L.call(s && !i && t.getElementsByClassName ? r ? t.getElementsByClassName(o) : t.getElementsByTagName(n) : t.querySelectorAll(n))
        },
        C.contains = P.documentElement.contains ?
            function(t, n) {
                return t !== n && t.contains(n)
            }: function(t, n) {
            for (; n && (n = n.parentNode);) if (n === t) return ! 0;
            return ! 1
        },
        C.type = t,
        C.isFunction = n,
        C.isWindow = e,
        C.isArray = Q,
        C.isPlainObject = o,
        C.isEmptyObject = function(t) {
            var n;
            for (n in t) return ! 1;
            return ! 0
        },
        C.inArray = function(t, n, e) {
            return A.indexOf.call(n, t, e)
        },
        C.camelCase = T,
        C.trim = function(t) {
            return null == t ? "": String.prototype.trim.call(t)
        },
        C.uuid = 0,
        C.support = {},
        C.expr = {},
        C.noop = function() {},
        C.map = function(t, n) {
            var e, i, r, o = [];
            if (s(t)) for (i = 0; i < t.length; i++) e = n(t[i], i),
            null != e && o.push(e);
            else for (r in t) e = n(t[r], r),
            null != e && o.push(e);
            return u(o)
        },
        C.each = function(t, n) {
            var e, i;
            if (s(t)) {
                for (e = 0; e < t.length; e++) if (n.call(t[e], e, t[e]) === !1) return t
            } else for (i in t) if (n.call(t[i], i, t[i]) === !1) return t;
            return t
        },
        C.grep = function(t, n) {
            return $.call(t, n)
        },
    window.JSON && (C.parseJSON = JSON.parse),
        C.each("Boolean Number String Function Array Date RegExp Object Error".split(" "),
            function(t, n) {
                W["[object " + n + "]"] = n.toLowerCase()
            }),
        C.fn = {
            constructor: Y.Z,
            length: 0,
            forEach: A.forEach,
            reduce: A.reduce,
            push: A.push,
            sort: A.sort,
            splice: A.splice,
            indexOf: A.indexOf,
            concat: function() {
                var t, n, e = [];
                for (t = 0; t < arguments.length; t++) n = arguments[t],
                    e[t] = Y.isZ(n) ? n.toArray() : n;
                return Z.apply(Y.isZ(this) ? this.toArray() : this, e)
            },
            map: function(t) {
                return C(C.map(this,
                    function(n, e) {
                        return t.call(n, e, n)
                    }))
            },
            slice: function() {
                return C(L.apply(this, arguments))
            },
            ready: function(t) {
                return U.test(P.readyState) && P.body ? t(C) : P.addEventListener("DOMContentLoaded",
                    function() {
                        t(C)
                    },
                    !1),
                    this
            },
            get: function(t) {
                return t === N ? L.call(this) : this[t >= 0 ? t: t + this.length]
            },
            toArray: function() {
                return this.get()
            },
            size: function() {
                return this.length
            },
            remove: function() {
                return this.each(function() {
                    null != this.parentNode && this.parentNode.removeChild(this)
                })
            },
            each: function(t) {
                return A.every.call(this,
                    function(n, e) {
                        return t.call(n, e, n) !== !1
                    }),
                    this
            },
            filter: function(t) {
                return n(t) ? this.not(this.not(t)) : C($.call(this,
                    function(n) {
                        return Y.matches(n, t)
                    }))
            },
            add: function(t, n) {
                return C(S(this.concat(C(t, n))))
            },
            is: function(t) {
                return this.length > 0 && Y.matches(this[0], t)
            },
            not: function(t) {
                var e = [];
                if (n(t) && t.call !== N) this.each(function(n) {
                    t.call(this, n) || e.push(this)
                });
                else {
                    var i = "string" == typeof t ? this.filter(t) : s(t) && n(t.item) ? L.call(t) : C(t);
                    this.forEach(function(t) {
                        i.indexOf(t) < 0 && e.push(t)
                    })
                }
                return C(e)
            },
            has: function(t) {
                return this.filter(function() {
                    return r(t) ? C.contains(this, t) : C(this).find(t).size()
                })
            },
            eq: function(t) {
                return - 1 === t ? this.slice(t) : this.slice(t, +t + 1)
            },
            first: function() {
                var t = this[0];
                return t && !r(t) ? t: C(t)
            },
            last: function() {
                var t = this[this.length - 1];
                return t && !r(t) ? t: C(t)
            },
            find: function(t) {
                var n, e = this;
                return n = t ? "object" == typeof t ? C(t).filter(function() {
                    var t = this;
                    return A.some.call(e,
                        function(n) {
                            return C.contains(n, t)
                        })
                }) : 1 == this.length ? C(Y.qsa(this[0], t)) : this.map(function() {
                    return Y.qsa(this, t)
                }) : C()
            },
            closest: function(t, n) {
                var e = this[0],
                    r = !1;
                for ("object" == typeof t && (r = C(t)); e && !(r ? r.indexOf(e) >= 0 : Y.matches(e, t));) e = e !== n && !i(e) && e.parentNode;
                return C(e)
            },
            parents: function(t) {
                for (var n = [], e = this; e.length > 0;) e = C.map(e,
                    function(t) {
                        return (t = t.parentNode) && !i(t) && n.indexOf(t) < 0 ? (n.push(t), t) : void 0
                    });
                return g(n, t)
            },
            parent: function(t) {
                return g(S(this.pluck("parentNode")), t)
            },
            children: function(t) {
                return g(this.map(function() {
                    return p(this)
                }), t)
            },
            contents: function() {
                return this.map(function() {
                    return this.contentDocument || L.call(this.childNodes)
                })
            },
            siblings: function(t) {
                return g(this.map(function(t, n) {
                    return $.call(p(n.parentNode),
                        function(t) {
                            return t !== n
                        })
                }), t)
            },
            empty: function() {
                return this.each(function() {
                    this.innerHTML = ""
                })
            },
            pluck: function(t) {
                return C.map(this,
                    function(n) {
                        return n[t]
                    })
            },
            show: function() {
                return this.each(function() {
                    "none" == this.style.display && (this.style.display = ""),
                    "none" == getComputedStyle(this, "").getPropertyValue("display") && (this.style.display = h(this.nodeName))
                })
            },
            replaceWith: function(t) {
                return this.before(t).remove()
            },
            wrap: function(t) {
                var e = n(t);
                if (this[0] && !e) var i = C(t).get(0),
                    r = i.parentNode || this.length > 1;
                return this.each(function(n) {
                    C(this).wrapAll(e ? t.call(this, n) : r ? i.cloneNode(!0) : i)
                })
            },
            wrapAll: function(t) {
                if (this[0]) {
                    C(this[0]).before(t = C(t));
                    for (var n; (n = t.children()).length;) t = n.first();
                    C(t).append(this)
                }
                return this
            },
            wrapInner: function(t) {
                var e = n(t);
                return this.each(function(n) {
                    var i = C(this),
                        r = i.contents(),
                        o = e ? t.call(this, n) : t;
                    r.length ? r.wrapAll(o) : i.append(o)
                })
            },
            unwrap: function() {
                return this.parent().each(function() {
                    C(this).replaceWith(C(this).children())
                }),
                    this
            },
            clone: function() {
                return this.map(function() {
                    return this.cloneNode(!0)
                })
            },
            hide: function() {
                return this.css("display", "none")
            },
            toggle: function(t) {
                return this.each(function() {
                    var n = C(this); (t === N ? "none" == n.css("display") : t) ? n.show() : n.hide()
                })
            },
            prev: function(t) {
                return C(this.pluck("previousElementSibling")).filter(t || "*")
            },
            next: function(t) {
                return C(this.pluck("nextElementSibling")).filter(t || "*")
            },
            html: function(t) {
                return 0 in arguments ? this.each(function(n) {
                    var e = this.innerHTML;
                    C(this).empty().append(y(this, t, n, e))
                }) : 0 in this ? this[0].innerHTML: null
            },
            text: function(t) {
                return 0 in arguments ? this.each(function(n) {
                    var e = y(this, t, n, this.textContent);
                    this.textContent = null == e ? "": "" + e
                }) : 0 in this ? this[0].textContent: null
            },
            attr: function(t, n) {
                var e;
                return "string" != typeof t || 1 in arguments ? this.each(function(e) {
                    if (1 === this.nodeType) if (r(t)) for (x in t) v(this, x, t[x]);
                    else v(this, t, y(this, n, e, this.getAttribute(t)))
                }) : this.length && 1 === this[0].nodeType ? !(e = this[0].getAttribute(t)) && t in this[0] ? this[0][t] : e: N
            },
            removeAttr: function(t) {
                return this.each(function() {
                    1 === this.nodeType && t.split(" ").forEach(function(t) {
                            v(this, t)
                        },
                        this)
                })
            },
            prop: function(t, n) {
                return t = K[t] || t,
                    1 in arguments ? this.each(function(e) {
                        this[t] = y(this, n, e, this[t])
                    }) : this[0] && this[0][t]
            },
            data: function(t, n) {
                var e = "data-" + t.replace(V, "-$1").toLowerCase(),
                    i = 1 in arguments ? this.attr(e, n) : this.attr(e);
                return null !== i ? b(i) : N
            },
            val: function(t) {
                return 0 in arguments ? this.each(function(n) {
                    this.value = y(this, t, n, this.value)
                }) : this[0] && (this[0].multiple ? C(this[0]).find("option").filter(function() {
                    return this.selected
                }).pluck("value") : this[0].value)
            },
            offset: function(t) {
                if (t) return this.each(function(n) {
                    var e = C(this),
                        i = y(this, t, n, e.offset()),
                        r = e.offsetParent().offset(),
                        o = {
                            top: i.top - r.top,
                            left: i.left - r.left
                        };
                    "static" == e.css("position") && (o.position = "relative"),
                        e.css(o)
                });
                if (!this.length) return null;
                if (!C.contains(P.documentElement, this[0])) return {
                    top: 0,
                    left: 0
                };
                var n = this[0].getBoundingClientRect();
                return {
                    left: n.left + window.pageXOffset,
                    top: n.top + window.pageYOffset,
                    width: Math.round(n.width),
                    height: Math.round(n.height)
                }
            },
            css: function(n, e) {
                if (arguments.length < 2) {
                    var i, r = this[0];
                    if (!r) return;
                    if (i = getComputedStyle(r, ""), "string" == typeof n) return r.style[T(n)] || i.getPropertyValue(n);
                    if (Q(n)) {
                        var o = {};
                        return C.each(n,
                            function(t, n) {
                                o[n] = r.style[T(n)] || i.getPropertyValue(n)
                            }),
                            o
                    }
                }
                var s = "";
                if ("string" == t(n)) e || 0 === e ? s = a(n) + ":" + f(n, e) : this.each(function() {
                    this.style.removeProperty(a(n))
                });
                else for (x in n) n[x] || 0 === n[x] ? s += a(x) + ":" + f(x, n[x]) + ";": this.each(function() {
                    this.style.removeProperty(a(x))
                });
                return this.each(function() {
                    this.style.cssText += ";" + s
                })
            },
            index: function(t) {
                return t ? this.indexOf(C(t)[0]) : this.parent().children().indexOf(this[0])
            },
            hasClass: function(t) {
                return t ? A.some.call(this,
                    function(t) {
                        return this.test(w(t))
                    },
                    l(t)) : !1
            },
            addClass: function(t) {
                return t ? this.each(function(n) {
                    if ("className" in this) {
                        O = [];
                        var e = w(this),
                            i = y(this, t, n, e);
                        i.split(/\s+/g).forEach(function(t) {
                                C(this).hasClass(t) || O.push(t)
                            },
                            this),
                        O.length && w(this, e + (e ? " ": "") + O.join(" "))
                    }
                }) : this
            },
            removeClass: function(t) {
                return this.each(function(n) {
                    if ("className" in this) {
                        if (t === N) return w(this, "");
                        O = w(this),
                            y(this, t, n, O).split(/\s+/g).forEach(function(t) {
                                O = O.replace(l(t), " ")
                            }),
                            w(this, O.trim())
                    }
                })
            },
            toggleClass: function(t, n) {
                return t ? this.each(function(e) {
                    var i = C(this),
                        r = y(this, t, e, w(this));
                    r.split(/\s+/g).forEach(function(t) { (n === N ? !i.hasClass(t) : n) ? i.addClass(t) : i.removeClass(t)
                    })
                }) : this
            },
            scrollTop: function(t) {
                if (this.length) {
                    var n = "scrollTop" in this[0];
                    return t === N ? n ? this[0].scrollTop: this[0].pageYOffset: this.each(n ?
                        function() {
                            this.scrollTop = t
                        }: function() {
                        this.scrollTo(this.scrollX, t)
                    })
                }
            },
            scrollLeft: function(t) {
                if (this.length) {
                    var n = "scrollLeft" in this[0];
                    return t === N ? n ? this[0].scrollLeft: this[0].pageXOffset: this.each(n ?
                        function() {
                            this.scrollLeft = t
                        }: function() {
                        this.scrollTo(t, this.scrollY)
                    })
                }
            },
            position: function() {
                if (this.length) {
                    var t = this[0],
                        n = this.offsetParent(),
                        e = this.offset(),
                        i = R.test(n[0].nodeName) ? {
                            top: 0,
                            left: 0
                        }: n.offset();
                    return e.top -= parseFloat(C(t).css("margin-top")) || 0,
                        e.left -= parseFloat(C(t).css("margin-left")) || 0,
                        i.top += parseFloat(C(n[0]).css("border-top-width")) || 0,
                        i.left += parseFloat(C(n[0]).css("border-left-width")) || 0,
                    {
                        top: e.top - i.top,
                        left: e.left - i.left
                    }
                }
            },
            offsetParent: function() {
                return this.map(function() {
                    for (var t = this.offsetParent || P.body; t && !R.test(t.nodeName) && "static" == C(t).css("position");) t = t.offsetParent;
                    return t
                })
            }
        },
        C.fn.detach = C.fn.remove,
        ["width", "height"].forEach(function(t) {
            var n = t.replace(/./,
                function(t) {
                    return t[0].toUpperCase()
                });
            C.fn[t] = function(r) {
                var o, s = this[0];
                return r === N ? e(s) ? s["inner" + n] : i(s) ? s.documentElement["scroll" + n] : (o = this.offset()) && o[t] : this.each(function(n) {
                    s = C(this),
                        s.css(t, y(this, r, n, s[t]()))
                })
            }
        }),
        D.forEach(function(n, e) {
            var i = e % 2;
            C.fn[n] = function() {
                var n, r, o = C.map(arguments,
                    function(e) {
                        return n = t(e),
                            "object" == n || "array" == n || null == e ? e: Y.fragment(e)
                    }),
                    s = this.length > 1;
                return o.length < 1 ? this: this.each(function(t, n) {
                    r = i ? n: n.parentNode,
                        n = 0 == e ? n.nextSibling: 1 == e ? n.firstChild: 2 == e ? n: null;
                    var c = C.contains(P.documentElement, r);
                    o.forEach(function(t) {
                        if (s) t = t.cloneNode(!0);
                        else if (!r) return C(t).remove();
                        r.insertBefore(t, n),
                        c && E(t,
                            function(t) {
                                null == t.nodeName || "SCRIPT" !== t.nodeName.toUpperCase() || t.type && "text/javascript" !== t.type || t.src || window.eval.call(window, t.innerHTML)
                            })
                    })
                })
            },
                C.fn[i ? n + "To": "insert" + (e ? "Before": "After")] = function(t) {
                    return C(t)[n](this),
                        this
                }
        }),
        Y.Z.prototype = d.prototype = C.fn,
        Y.uniq = S,
        Y.deserializeValue = b,
        C.zepto = Y,
        C
} ();
window.Zepto = Zepto,
void 0 === window.$ && (window.$ = Zepto);
/*!js/librarys/zepto/ajax.js*/
; !
    function(t) {
        function e(e, a, n) {
            var r = t.Event(a);
            return t(e).trigger(r, n),
                !r.isDefaultPrevented()
        }
        function a(t, a, n, r) {
            return t.global ? e(a || j, n, r) : void 0
        }
        function n(e) {
            e.global && 0 === t.active++&&a(e, null, "ajaxStart")
        }
        function r(e) {
            e.global && !--t.active && a(e, null, "ajaxStop")
        }
        function o(t, e) {
            var n = e.context;
            return e.beforeSend.call(n, t, e) === !1 || a(e, n, "ajaxBeforeSend", [t, e]) === !1 ? !1 : void a(e, n, "ajaxSend", [t, e])
        }
        function i(t, e, n, r) {
            var o = n.context,
                i = "success";
            n.success.call(o, t, i, e),
            r && r.resolveWith(o, [t, i, e]),
                a(n, o, "ajaxSuccess", [e, n, t]),
                c(i, e, n)
        }
        function s(t, e, n, r, o) {
            var i = r.context;
            r.error.call(i, n, e, t),
            o && o.rejectWith(i, [n, e, t]),
                a(r, i, "ajaxError", [n, r, t || e]),
                c(e, n, r)
        }
        function c(t, e, n) {
            var o = n.context;
            n.complete.call(o, e, t),
                a(n, o, "ajaxComplete", [e, n]),
                r(n)
        }
        function l() {}
        function u(t) {
            return t && (t = t.split(";", 2)[0]),
            t && (t == b ? "html": t == T ? "json": g.test(t) ? "script": w.test(t) && "xml") || "text"
        }
        function p(t, e) {
            return "" == e ? t: (t + "&" + e).replace(/[&?]{1,2}/, "?")
        }
        function d(e) {
            e.processData && e.data && "string" != t.type(e.data) && (e.data = t.param(e.data, e.traditional)),
            !e.data || e.type && "GET" != e.type.toUpperCase() || (e.url = p(e.url, e.data), e.data = void 0)
        }
        function f(e, a, n, r) {
            return t.isFunction(a) && (r = n, n = a, a = void 0),
            t.isFunction(n) || (r = n, n = void 0),
            {
                url: e,
                data: a,
                success: n,
                dataType: r
            }
        }
        function m(e, a, n, r) {
            var o, i = t.isArray(a),
                s = t.isPlainObject(a);
            t.each(a,
                function(a, c) {
                    o = t.type(c),
                    r && (a = n ? r: r + "[" + (s || "object" == o || "array" == o ? a: "") + "]"),
                        !r && i ? e.add(c.name, c.value) : "array" == o || !n && "object" == o ? m(e, c, n, a) : e.add(a, c)
                })
        }
        var x, h, v = 0,
            j = window.document,
            y = /<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi,
            g = /^(?:text|application)\/javascript/i,
            w = /^(?:text|application)\/xml/i,
            T = "application/json",
            b = "text/html",
            S = /^\s*$/,
            D = j.createElement("a");
        D.href = window.location.href,
            t.active = 0,
            t.ajaxJSONP = function(e, a) {
                if (! ("type" in e)) return t.ajax(e);
                var n, r, c = e.jsonpCallback,
                    l = (t.isFunction(c) ? c() : c) || "jsonp" + ++v,
                    u = j.createElement("script"),
                    p = window[l],
                    d = function(e) {
                        t(u).triggerHandler("error", e || "abort")
                    },
                    f = {
                        abort: d
                    };
                return a && a.promise(f),
                    t(u).on("load error",
                        function(o, c) {
                            clearTimeout(r),
                                t(u).off().remove(),
                                "error" != o.type && n ? i(n[0], f, e, a) : s(null, c || "error", f, e, a),
                                window[l] = p,
                            n && t.isFunction(p) && p(n[0]),
                                p = n = void 0
                        }),
                    o(f, e) === !1 ? (d("abort"), f) : (window[l] = function() {
                        n = arguments
                    },
                        u.src = e.url.replace(/\?(.+)=\?/, "?$1=" + l), j.head.appendChild(u), e.timeout > 0 && (r = setTimeout(function() {
                            d("timeout")
                        },
                        e.timeout)), f)
            },
            t.ajaxSettings = {
                type: "GET",
                beforeSend: l,
                success: l,
                error: l,
                complete: l,
                context: null,
                global: !0,
                xhr: function() {
                    return new window.XMLHttpRequest
                },
                accepts: {
                    script: "text/javascript, application/javascript, application/x-javascript",
                    json: T,
                    xml: "application/xml, text/xml",
                    html: b,
                    text: "text/plain"
                },
                crossDomain: !1,
                timeout: 0,
                processData: !0,
                cache: !0
            },
            t.ajax = function(e) {
                var a, r, c = t.extend({},
                        e || {}),
                    f = t.Deferred && t.Deferred();
                for (x in t.ajaxSettings) void 0 === c[x] && (c[x] = t.ajaxSettings[x]);
                n(c),
                c.crossDomain || (a = j.createElement("a"), a.href = c.url, a.href = a.href, c.crossDomain = D.protocol + "//" + D.host != a.protocol + "//" + a.host),
                c.url || (c.url = window.location.toString()),
                (r = c.url.indexOf("#")) > -1 && (c.url = c.url.slice(0, r)),
                    d(c);
                var m = c.dataType,
                    v = /\?.+=\?/.test(c.url);
                if (v && (m = "jsonp"), c.cache !== !1 && (e && e.cache === !0 || "script" != m && "jsonp" != m) || (c.url = p(c.url, "_=" + Date.now())), "jsonp" == m) return v || (c.url = p(c.url, c.jsonp ? c.jsonp + "=?": c.jsonp === !1 ? "": "callback=?")),
                    t.ajaxJSONP(c, f);
                var y, g = c.accepts[m],
                    w = {},
                    T = function(t, e) {
                        w[t.toLowerCase()] = [t, e]
                    },
                    b = /^([\w-]+:)\/\//.test(c.url) ? RegExp.$1: window.location.protocol,
                    E = c.xhr(),
                    C = E.setRequestHeader;
                if (f && f.promise(E), c.crossDomain || T("X-Requested-With", "XMLHttpRequest"), T("Accept", g || "*/*"), (g = c.mimeType || g) && (g.indexOf(",") > -1 && (g = g.split(",", 2)[0]), E.overrideMimeType && E.overrideMimeType(g)), (c.contentType || c.contentType !== !1 && c.data && "GET" != c.type.toUpperCase()) && T("Content-Type", c.contentType || "application/x-www-form-urlencoded"), c.headers) for (h in c.headers) T(h, c.headers[h]);
                if (E.setRequestHeader = T, E.onreadystatechange = function() {
                        if (4 == E.readyState) {
                            E.onreadystatechange = l,
                                clearTimeout(y);
                            var e, a = !1;
                            if (E.status >= 200 && E.status < 300 || 304 == E.status || 0 == E.status && "file:" == b) {
                                m = m || u(c.mimeType || E.getResponseHeader("content-type")),
                                    e = E.responseText;
                                try {
                                    "script" == m ? (1, eval)(e) : "xml" == m ? e = E.responseXML: "json" == m && (e = S.test(e) ? null: t.parseJSON(e))
                                } catch(n) {
                                    a = n
                                }
                                a ? s(a, "parsererror", E, c, f) : i(e, E, c, f)
                            } else s(E.statusText || null, E.status ? "error": "abort", E, c, f)
                        }
                    },
                    o(E, c) === !1) return E.abort(),
                    s(null, "abort", E, c, f),
                    E;
                if (c.xhrFields) for (h in c.xhrFields) E[h] = c.xhrFields[h];
                var F = "async" in c ? c.async: !0;
                E.open(c.type, c.url, F, c.username, c.password);
                for (h in w) C.apply(E, w[h]);
                return c.timeout > 0 && (y = setTimeout(function() {
                        E.onreadystatechange = l,
                            E.abort(),
                            s(null, "timeout", E, c, f)
                    },
                    c.timeout)),
                    E.send(c.data ? c.data: null),
                    E
            },
            t.get = function() {
                return t.ajax(f.apply(null, arguments))
            },
            t.post = function() {
                var e = f.apply(null, arguments);
                return e.type = "POST",
                    t.ajax(e)
            },
            t.getJSON = function() {
                var e = f.apply(null, arguments);
                return e.dataType = "json",
                    t.ajax(e)
            },
            t.fn.load = function(e, a, n) {
                if (!this.length) return this;
                var r, o = this,
                    i = e.split(/\s/),
                    s = f(e, a, n),
                    c = s.success;
                return i.length > 1 && (s.url = i[0], r = i[1]),
                    s.success = function(e) {
                        o.html(r ? t("<div>").html(e.replace(y, "")).find(r) : e),
                        c && c.apply(o, arguments)
                    },
                    t.ajax(s),
                    this
            };
        var E = encodeURIComponent;
        t.param = function(e, a) {
            var n = [];
            return n.add = function(e, a) {
                t.isFunction(a) && (a = a()),
                null == a && (a = ""),
                    this.push(E(e) + "=" + E(a))
            },
                m(n, e, a),
                n.join("&").replace(/%20/g, "+")
        }
    } (Zepto);
/*!js/librarys/zepto/data.js*/
; !
    function(n) {
        function t(t, i) {
            var f = t[u],
                c = f && a[f];
            if (void 0 === i) return c || e(t);
            if (c) {
                if (i in c) return c[i];
                var h = o(i);
                if (h in c) return c[h]
            }
            return r.call(n(t), i)
        }
        function e(t, e, r) {
            var f = t[u] || (t[u] = ++n.uuid),
                c = a[f] || (a[f] = i(t));
            return void 0 !== e && (c[o(e)] = r),
                c
        }
        function i(t) {
            var e = {};
            return n.each(t.attributes || f,
                function(t, i) {
                    0 == i.name.indexOf("data-") && (e[o(i.name.replace("data-", ""))] = n.zepto.deserializeValue(i.value))
                }),
                e
        }
        var a = {},
            r = n.fn.data,
            o = n.camelCase,
            u = n.expando = "Zepto" + +new Date,
            f = [];
        n.fn.data = function(i, a) {
            return void 0 === a ? n.isPlainObject(i) ? this.each(function(t, a) {
                n.each(i,
                    function(n, t) {
                        e(a, n, t)
                    })
            }) : 0 in this ? t(this[0], i) : void 0 : this.each(function() {
                e(this, i, a)
            })
        },
            n.fn.removeData = function(t) {
                return "string" == typeof t && (t = t.split(/\s+/)),
                    this.each(function() {
                        var e = this[u],
                            i = e && a[e];
                        i && n.each(t || i,
                            function(n) {
                                delete i[t ? o(this) : n]
                            })
                    })
            },
            ["remove", "empty"].forEach(function(t) {
                var e = n.fn[t];
                n.fn[t] = function() {
                    var n = this.find("*");
                    return "remove" === t && (n = n.add(this)),
                        n.removeData(),
                        e.call(this)
                }
            })
    } (Zepto);
/*!js/librarys/zepto/callbacks.js*/
; !
    function(n) {
        n.Callbacks = function(t) {
            t = n.extend({},
                t);
            var e, i, r, u, o, f, c = [],
                s = !t.once && [],
                h = function(n) {
                    for (e = t.memory && n, i = !0, f = u || 0, u = 0, o = c.length, r = !0; c && o > f; ++f) if (c[f].apply(n[0], n[1]) === !1 && t.stopOnFalse) {
                        e = !1;
                        break
                    }
                    r = !1,
                    c && (s ? s.length && h(s.shift()) : e ? c.length = 0 : a.disable())
                },
                a = {
                    add: function() {
                        if (c) {
                            var i = c.length,
                                f = function(e) {
                                    n.each(e,
                                        function(n, e) {
                                            "function" == typeof e ? t.unique && a.has(e) || c.push(e) : e && e.length && "string" != typeof e && f(e)
                                        })
                                };
                            f(arguments),
                                r ? o = c.length: e && (u = i, h(e))
                        }
                        return this
                    },
                    remove: function() {
                        return c && n.each(arguments,
                            function(t, e) {
                                for (var i; (i = n.inArray(e, c, i)) > -1;) c.splice(i, 1),
                                r && (o >= i && --o, f >= i && --f)
                            }),
                            this
                    },
                    has: function(t) {
                        return ! (!c || !(t ? n.inArray(t, c) > -1 : c.length))
                    },
                    empty: function() {
                        return o = c.length = 0,
                            this
                    },
                    disable: function() {
                        return c = s = e = void 0,
                            this
                    },
                    disabled: function() {
                        return ! c
                    },
                    lock: function() {
                        return s = void 0,
                        e || a.disable(),
                            this
                    },
                    locked: function() {
                        return ! s
                    },
                    fireWith: function(n, t) {
                        return ! c || i && !s || (t = t || [], t = [n, t.slice ? t.slice() : t], r ? s.push(t) : h(t)),
                            this
                    },
                    fire: function() {
                        return a.fireWith(this, arguments)
                    },
                    fired: function() {
                        return !! i
                    }
                };
            return a
        }
    } (Zepto);
/*!js/librarys/zepto/deferred.js*/
; !
    function(e) {
        function n(r) {
            var t = [["resolve", "done", e.Callbacks({
                    once: 1,
                    memory: 1
                }), "resolved"], ["reject", "fail", e.Callbacks({
                    once: 1,
                    memory: 1
                }), "rejected"], ["notify", "progress", e.Callbacks({
                    memory: 1
                })]],
                i = "pending",
                o = {
                    state: function() {
                        return i
                    },
                    always: function() {
                        return s.done(arguments).fail(arguments),
                            this
                    },
                    then: function() {
                        var r = arguments;
                        return n(function(n) {
                            e.each(t,
                                function(t, i) {
                                    var a = e.isFunction(r[t]) && r[t];
                                    s[i[1]](function() {
                                        var r = a && a.apply(this, arguments);
                                        if (r && e.isFunction(r.promise)) r.promise().done(n.resolve).fail(n.reject).progress(n.notify);
                                        else {
                                            var t = this === o ? n.promise() : this,
                                                s = a ? [r] : arguments;
                                            n[i[0] + "With"](t, s)
                                        }
                                    })
                                }),
                                r = null
                        }).promise()
                    },
                    promise: function(n) {
                        return null != n ? e.extend(n, o) : o
                    }
                },
                s = {};
            return e.each(t,
                function(e, n) {
                    var r = n[2],
                        a = n[3];
                    o[n[1]] = r.add,
                    a && r.add(function() {
                            i = a
                        },
                        t[1 ^ e][2].disable, t[2][2].lock),
                        s[n[0]] = function() {
                            return s[n[0] + "With"](this === s ? o: this, arguments),
                                this
                        },
                        s[n[0] + "With"] = r.fireWith
                }),
                o.promise(s),
            r && r.call(s, s),
                s
        }
        var r = Array.prototype.slice;
        e.when = function(t) {
            var i, o, s, a = r.call(arguments),
                u = a.length,
                c = 0,
                l = 1 !== u || t && e.isFunction(t.promise) ? u: 0,
                f = 1 === l ? t: n(),
                m = function(e, n, t) {
                    return function(o) {
                        n[e] = this,
                            t[e] = arguments.length > 1 ? r.call(arguments) : o,
                            t === i ? f.notifyWith(n, t) : --l || f.resolveWith(n, t)
                    }
                };
            if (u > 1) for (i = new Array(u), o = new Array(u), s = new Array(u); u > c; ++c) a[c] && e.isFunction(a[c].promise) ? a[c].promise().done(m(c, s, a)).fail(f.reject).progress(m(c, o, i)) : --l;
            return l || f.resolveWith(s, a),
                f.promise()
        },
            e.Deferred = n
    } (Zepto);
/*!js/librarys/zepto/event.js*/
; !
    function(n) {
        function e(n) {
            return n._zid || (n._zid = p++)
        }
        function t(n, t, o, u) {
            if (t = r(t), t.ns) var a = i(t.ns);
            return (g[e(n)] || []).filter(function(n) {
                return ! (!n || t.e && n.e != t.e || t.ns && !a.test(n.ns) || o && e(n.fn) !== e(o) || u && n.sel != u)
            })
        }
        function r(n) {
            var e = ("" + n).split(".");
            return {
                e: e[0],
                ns: e.slice(1).sort().join(" ")
            }
        }
        function i(n) {
            return new RegExp("(?:^| )" + n.replace(" ", " .* ?") + "(?: |$)")
        }
        function o(n, e) {
            return n.del && !y && n.e in E || !!e
        }
        function u(n) {
            return P[n] || y && E[n] || n
        }
        function a(t, i, a, s, f, p, d) {
            var v = e(t),
                h = g[v] || (g[v] = []);
            i.split(/\s/).forEach(function(e) {
                if ("ready" == e) return n(document).ready(a);
                var i = r(e);
                i.fn = a,
                    i.sel = f,
                i.e in P && (a = function(e) {
                    var t = e.relatedTarget;
                    return ! t || t !== this && !n.contains(this, t) ? i.fn.apply(this, arguments) : void 0
                }),
                    i.del = p;
                var v = p || a;
                i.proxy = function(n) {
                    if (n = c(n), !n.isImmediatePropagationStopped()) {
                        n.data = s;
                        var e = v.apply(t, n._args == l ? [n] : [n].concat(n._args));
                        return e === !1 && (n.preventDefault(), n.stopPropagation()),
                            e
                    }
                },
                    i.i = h.length,
                    h.push(i),
                "addEventListener" in t && t.addEventListener(u(i.e), i.proxy, o(i, d))
            })
        }
        function s(n, r, i, a, s) {
            var c = e(n); (r || "").split(/\s/).forEach(function(e) {
                t(n, e, i, a).forEach(function(e) {
                    delete g[c][e.i],
                    "removeEventListener" in n && n.removeEventListener(u(e.e), e.proxy, o(e, s))
                })
            })
        }
        function c(e, t) {
            return (t || !e.isDefaultPrevented) && (t || (t = e), n.each(D,
                function(n, r) {
                    var i = t[n];
                    e[n] = function() {
                        return this[r] = b,
                        i && i.apply(t, arguments)
                    },
                        e[r] = x
                }), (t.defaultPrevented !== l ? t.defaultPrevented: "returnValue" in t ? t.returnValue === !1 : t.getPreventDefault && t.getPreventDefault()) && (e.isDefaultPrevented = b)),
                e
        }
        function f(n) {
            var e, t = {
                originalEvent: n
            };
            for (e in n) w.test(e) || n[e] === l || (t[e] = n[e]);
            return c(t, n)
        }
        var l, p = 1,
            d = Array.prototype.slice,
            v = n.isFunction,
            h = function(n) {
                return "string" == typeof n
            },
            g = {},
            m = {},
            y = "onfocusin" in window,
            E = {
                focus: "focusin",
                blur: "focusout"
            },
            P = {
                mouseenter: "mouseover",
                mouseleave: "mouseout"
            };
        m.click = m.mousedown = m.mouseup = m.mousemove = "MouseEvents",
            n.event = {
                add: a,
                remove: s
            },
            n.proxy = function(t, r) {
                var i = 2 in arguments && d.call(arguments, 2);
                if (v(t)) {
                    var o = function() {
                        return t.apply(r, i ? i.concat(d.call(arguments)) : arguments)
                    };
                    return o._zid = e(t),
                        o
                }
                if (h(r)) return i ? (i.unshift(t[r], t), n.proxy.apply(null, i)) : n.proxy(t[r], t);
                throw new TypeError("expected function")
            },
            n.fn.bind = function(n, e, t) {
                return this.on(n, e, t)
            },
            n.fn.unbind = function(n, e) {
                return this.off(n, e)
            },
            n.fn.one = function(n, e, t, r) {
                return this.on(n, e, t, r, 1)
            };
        var b = function() {
                return ! 0
            },
            x = function() {
                return ! 1
            },
            w = /^([A-Z]|returnValue$|layer[XY]$)/,
            D = {
                preventDefault: "isDefaultPrevented",
                stopImmediatePropagation: "isImmediatePropagationStopped",
                stopPropagation: "isPropagationStopped"
            };
        n.fn.delegate = function(n, e, t) {
            return this.on(e, n, t)
        },
            n.fn.undelegate = function(n, e, t) {
                return this.off(e, n, t)
            },
            n.fn.live = function(e, t) {
                return n(document.body).delegate(this.selector, e, t),
                    this
            },
            n.fn.die = function(e, t) {
                return n(document.body).undelegate(this.selector, e, t),
                    this
            },
            n.fn.on = function(e, t, r, i, o) {
                var u, c, p = this;
                return e && !h(e) ? (n.each(e,
                    function(n, e) {
                        p.on(n, t, r, e, o)
                    }), p) : (h(t) || v(i) || i === !1 || (i = r, r = t, t = l), (i === l || r === !1) && (i = r, r = l), i === !1 && (i = x), p.each(function(l, p) {
                    o && (u = function(n) {
                        return s(p, n.type, i),
                            i.apply(this, arguments)
                    }),
                    t && (c = function(e) {
                        var r, o = n(e.target).closest(t, p).get(0);
                        return o && o !== p ? (r = n.extend(f(e), {
                            currentTarget: o,
                            liveFired: p
                        }), (u || i).apply(o, [r].concat(d.call(arguments, 1)))) : void 0
                    }),
                        a(p, e, i, r, t, c || u)
                }))
            },
            n.fn.off = function(e, t, r) {
                var i = this;
                return e && !h(e) ? (n.each(e,
                    function(n, e) {
                        i.off(n, t, e)
                    }), i) : (h(t) || v(r) || r === !1 || (r = t, t = l), r === !1 && (r = x), i.each(function() {
                    s(this, e, r, t)
                }))
            },
            n.fn.trigger = function(e, t) {
                return e = h(e) || n.isPlainObject(e) ? n.Event(e) : c(e),
                    e._args = t,
                    this.each(function() {
                        e.type in E && "function" == typeof this[e.type] ? this[e.type]() : "dispatchEvent" in this ? this.dispatchEvent(e) : n(this).triggerHandler(e, t)
                    })
            },
            n.fn.triggerHandler = function(e, r) {
                var i, o;
                return this.each(function(u, a) {
                    i = f(h(e) ? n.Event(e) : e),
                        i._args = r,
                        i.target = a,
                        n.each(t(a, e.type || e),
                            function(n, e) {
                                return o = e.proxy(i),
                                    i.isImmediatePropagationStopped() ? !1 : void 0
                            })
                }),
                    o
            },
            "focusin focusout focus blur load resize scroll unload click dblclick mousedown mouseup mousemove mouseover mouseout mouseenter mouseleave change select keydown keypress keyup error".split(" ").forEach(function(e) {
                n.fn[e] = function(n) {
                    return 0 in arguments ? this.bind(e, n) : this.trigger(e)
                }
            }),
            n.Event = function(n, e) {
                h(n) || (e = n, n = e.type);
                var t = document.createEvent(m[n] || "Events"),
                    r = !0;
                if (e) for (var i in e)"bubbles" == i ? r = !!e[i] : t[i] = e[i];
                return t.initEvent(n, r, !0),
                    c(t)
            }
    } (Zepto);
/*!js/librarys/zepto/fx.js*/
; !
    function(t, n) {
        function i(t) {
            return t.replace(/([a-z])([A-Z])/, "$1-$2").toLowerCase()
        }
        function e(t) {
            return a ? a + t: t.toLowerCase()
        }
        var a, s, o, r, f, u, c, d, l, m, p = "",
            h = {
                Webkit: "webkit",
                Moz: "",
                O: "o"
            },
            y = document.createElement("div"),
            x = /^((translate|rotate|scale)(X|Y|Z|3d)?|matrix(3d)?|perspective|skew(X|Y)?)$/i,
            b = {};
        t.each(h,
            function(t, i) {
                return y.style[t + "TransitionProperty"] !== n ? (p = "-" + t.toLowerCase() + "-", a = i, !1) : void 0
            }),
            s = p + "transform",
            b[o = p + "transition-property"] = b[r = p + "transition-duration"] = b[u = p + "transition-delay"] = b[f = p + "transition-timing-function"] = b[c = p + "animation-name"] = b[d = p + "animation-duration"] = b[m = p + "animation-delay"] = b[l = p + "animation-timing-function"] = "",
            t.fx = {
                off: a === n && y.style.transitionProperty === n,
                speeds: {
                    _default: 400,
                    fast: 200,
                    slow: 600
                },
                cssPrefix: p,
                transitionEnd: e("TransitionEnd"),
                animationEnd: e("AnimationEnd")
            },
            t.fn.animate = function(i, e, a, s, o) {
                return t.isFunction(e) && (s = e, a = n, e = n),
                t.isFunction(a) && (s = a, a = n),
                t.isPlainObject(e) && (a = e.easing, s = e.complete, o = e.delay, e = e.duration),
                e && (e = ("number" == typeof e ? e: t.fx.speeds[e] || t.fx.speeds._default) / 1e3),
                o && (o = parseFloat(o) / 1e3),
                    this.anim(i, e, a, s, o)
            },
            t.fn.anim = function(e, a, p, h, y) {
                var g, E, w, v = {},
                    T = "",
                    L = this,
                    P = t.fx.transitionEnd,
                    j = !1;
                if (a === n && (a = t.fx.speeds._default / 1e3), y === n && (y = 0), t.fx.off && (a = 0), "string" == typeof e) v[c] = e,
                    v[d] = a + "s",
                    v[m] = y + "s",
                    v[l] = p || "linear",
                    P = t.fx.animationEnd;
                else {
                    E = [];
                    for (g in e) x.test(g) ? T += g + "(" + e[g] + ") ": (v[g] = e[g], E.push(i(g)));
                    T && (v[s] = T, E.push(s)),
                    a > 0 && "object" == typeof e && (v[o] = E.join(", "), v[r] = a + "s", v[u] = y + "s", v[f] = p || "linear")
                }
                return w = function(n) {
                    if ("undefined" != typeof n) {
                        if (n.target !== n.currentTarget) return;
                        t(n.target).unbind(P, w)
                    } else t(this).unbind(P, w);
                    j = !0,
                        t(this).css(b),
                    h && h.call(this)
                },
                a > 0 && (this.bind(P, w), setTimeout(function() {
                        j || w.call(L)
                    },
                    1e3 * (a + y) + 25)),
                this.size() && this.get(0).clientLeft,
                    this.css(v),
                0 >= a && setTimeout(function() {
                        L.each(function() {
                            w.call(this)
                        })
                    },
                    0),
                    this
            },
            y = null
    } (Zepto);
/*!js/librarys/zepto/fx_methods.js*/
; !
    function(n, t) {
        function i(i, s, o, c, e) {
            "function" != typeof s || e || (e = s, s = t);
            var f = {
                opacity: o
            };
            return c && (f.scale = c, i.css(n.fx.cssPrefix + "transform-origin", "0 0")),
                i.animate(f, s, null, e)
        }
        function s(t, s, o, c) {
            return i(t, s, 0, o,
                function() {
                    e.call(n(this)),
                    c && c.call(this)
                })
        }
        var o = window.document,
            c = (o.documentElement, n.fn.show),
            e = n.fn.hide,
            f = n.fn.toggle;
        n.fn.show = function(n, s) {
            return c.call(this),
                n === t ? n = 0 : this.css("opacity", 0),
                i(this, n, 1, "1,1", s)
        },
            n.fn.hide = function(n, i) {
                return n === t ? e.call(this) : s(this, n, "0,0", i)
            },
            n.fn.toggle = function(i, s) {
                return i === t || "boolean" == typeof i ? f.call(this, i) : this.each(function() {
                    var t = n(this);
                    t["none" == t.css("display") ? "show": "hide"](i, s)
                })
            },
            n.fn.fadeTo = function(n, t, s) {
                return i(this, n, t, null, s)
            },
            n.fn.fadeIn = function(n, t) {
                var i = this.css("opacity");
                return i > 0 ? this.css("opacity", 0) : i = 1,
                    c.call(this).fadeTo(n, i, t)
            },
            n.fn.fadeOut = function(n, t) {
                return s(this, n, null, t)
            },
            n.fn.fadeToggle = function(t, i) {
                return this.each(function() {
                    var s = n(this);
                    s[0 == s.css("opacity") || "none" == s.css("display") ? "fadeIn": "fadeOut"](t, i)
                })
            }
    } (Zepto);
/*!js/librarys/zepto/selector.js*/
; !
    function(t) {
        function n(n) {
            return n = t(n),
            !(!n.width() && !n.height()) && "none" !== n.css("display")
        }
        function e(t, n) {
            t = t.replace(/=#\]/g, '="#"]');
            var e, i, r = c.exec(t);
            if (r && r[2] in u && (e = u[r[2]], i = r[3], t = r[1], i)) {
                var s = Number(i);
                i = isNaN(s) ? i.replace(/^["']|["']$/g, "") : s
            }
            return n(t, e, i)
        }
        var i = t.zepto,
            r = i.qsa,
            s = i.matches,
            u = t.expr[":"] = {
                visible: function() {
                    return n(this) ? this: void 0
                },
                hidden: function() {
                    return n(this) ? void 0 : this
                },
                selected: function() {
                    return this.selected ? this: void 0
                },
                checked: function() {
                    return this.checked ? this: void 0
                },
                parent: function() {
                    return this.parentNode
                },
                first: function(t) {
                    return 0 === t ? this: void 0
                },
                last: function(t, n) {
                    return t === n.length - 1 ? this: void 0
                },
                eq: function(t, n, e) {
                    return t === e ? this: void 0
                },
                contains: function(n, e, i) {
                    return t(this).text().indexOf(i) > -1 ? this: void 0
                },
                has: function(t, n, e) {
                    return i.qsa(this, e).length ? this: void 0
                }
            },
            c = new RegExp("(.*):(\\w+)(?:\\(([^)]+)\\))?$\\s*"),
            o = /^\s*>/,
            h = "Zepto" + +new Date;
        i.qsa = function(n, s) {
            return e(s,
                function(e, s, u) {
                    try {
                        var c; ! e && s ? e = "*": o.test(e) && (c = t(n).addClass(h), e = "." + h + " " + e);
                        var a = r(n, e)
                    } catch(f) {
                        throw f
                    } finally {
                        c && c.removeClass(h)
                    }
                    return s ? i.uniq(t.map(a,
                        function(t, n) {
                            return s.call(t, n, a, u)
                        })) : a
                })
        },
            i.matches = function(t, n) {
                return e(n,
                    function(n, e, i) {
                        return ! (n && !s(t, n) || e && e.call(t, null, i) !== t)
                    })
            }
    } (Zepto);
/*!js/librarys/zepto/stack.js*/
; !
    function(n) {
        n.fn.end = function() {
            return this.prevObject || n()
        },
            n.fn.andSelf = function() {
                return this.add(this.prevObject || n())
            },
            "filter,add,not,eq,first,last,find,closest,parents,parent,children,siblings".split(",").forEach(function(t) {
                var e = n.fn[t];
                n.fn[t] = function() {
                    var n = e.apply(this, arguments);
                    return n.prevObject = this,
                        n
                }
            })
    } (Zepto);
/*!js/librarys/zepto/form.js*/
; !
    function(e) {
        e.fn.serializeArray = function() {
            var t, n, i = [],
                r = function(e) {
                    return e.forEach ? e.forEach(r) : void i.push({
                        name: t,
                        value: e
                    })
                };
            return this[0] && e.each(this[0].elements,
                function(i, s) {
                    n = s.type,
                        t = s.name,
                    t && "fieldset" != s.nodeName.toLowerCase() && !s.disabled && "submit" != n && "reset" != n && "button" != n && "file" != n && ("radio" != n && "checkbox" != n || s.checked) && r(e(s).val())
                }),
                i
        },
            e.fn.serialize = function() {
                var e = [];
                return this.serializeArray().forEach(function(t) {
                    e.push(encodeURIComponent(t.name) + "=" + encodeURIComponent(t.value))
                }),
                    e.join("&")
            },
            e.fn.submit = function(t) {
                if (0 in arguments) this.bind("submit", t);
                else if (this.length) {
                    var n = e.Event("submit");
                    this.eq(0).trigger(n),
                    n.isDefaultPrevented() || this.get(0).submit()
                }
                return this
            }
    } (Zepto);
/*!js/librarys/zepto/assets.js*/
; !
    function(t) {
        var e, i = [];
        t.fn.remove = function() {
            return this.each(function() {
                this.parentNode && ("IMG" === this.tagName && (i.push(this), this.src = "data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=", e && clearTimeout(e), e = setTimeout(function() {
                        i = []
                    },
                    6e4)), this.parentNode.removeChild(this))
            })
        }
    } (Zepto);
/*!js/librarys/spa-apis.js*/
;~
    function(a) {
        "use strict";
        function e(a) {
            return a = a || P.href,
                a.replace(/^[^#!]*(#!)?\/?(.*)\/?$/, "$2")
        }
        function t(a) {
            return "[object RegExp]" == Object.prototype.toString.call(a)
        }
        function n(a) {
            return a = a.replace(M, "\\$&").replace(q, "(?:$1)?").replace(H,
                function(a, e) {
                    return e ? a: "([^/]+)"
                }).replace(L, "(.*?)"),
            "^" + a + "$"
        }
        function s(e, t) {
            var n = e.exec(t).slice(1),
                s = [];
            return a.each(n,
                function(a, e) {
                    e && s.push(decodeURIComponent(e))
                }),
                s
        }
        function i(a, e) {
            _[a.data("id")],
                _[e.data("id")];
            a.css({
                zIndex: k
            }),
                e.css({
                    zIndex: z
                })
        }
        function o(e, t, n, s) {
            var i = a(".spa-page-body", e),
                o = (a(".spa-page-body", t), {}); ! W && (n = "defaultInOut"),
            G[n] || (n = "defaultInOut"),
            x && (x = !1, n = "defaultInOut"),
                o.opacity = 1,
            !J(n) && W && (o[W] = "translate(0, 0) scale(1, 1)"),
                i.css(o),
                B[n].apply(e, [e, t, s])
        }
        function r() {
            event.stopPropagation(),
                event.preventDefault()
        }
        if (!a.os) {
            var p = {},
                c = navigator.userAgent,
                l = (navigator.platform, c.match(/(Android);?[\s\/]+([\d.]+)?/)),
                u = c.match(/(iPad).*OS\s([\d_]+)/),
                d = c.match(/(iPod)(.*OS\s([\d_]+))?/),
                g = !u && c.match(/(iPhone\sOS)\s([\d_]+)/);
            l && (p.android = !0, p.version = l[2]),
            g && !d && (p.ios = p.iphone = !0, p.version = g[2].replace(/_/g, ".")),
            u && (p.ios = p.ipad = !0, p.version = u[2].replace(/_/g, ".")),
            d && (p.ios = p.ipod = !0, p.version = d[3] ? d[3].replace(/_/g, ".") : null),
                a.os = p
        }
        var f, v, h, m, b, w = a(window),
            y = a(document),
            P = window.location,
            T = window.history,
            D = !1,
            C = !1,
            x = !0,
            I = {},
            A = {},
            O = [],
            E = 1024,
            k = 1023,
            z = 1025,
            S = 0,
            j = {},
            F = {},
            $ = [],
            _ = {},
            R = '<div class="spa-loader-animate"><div class="bg"></div><span class="ball"></span><span class="ball"></span></div>';
        a.spa = {},
            a.spa.version = "1.0.7",
            y.on("spa:addstyle",
                function(e, t) {
                    a("head").append('<style type="text/css">' + t + "</style>")
                }),
            function() {
                var e, t, n = Math.max(w.height(), window.innerHeight),
                    s = Math.max(w.width(), window.innerWidth),
                    i = function() {
                        h.height() != Math.max(w.height(), window.innerHeight) && (f.css({
                            width: s,
                            height: n
                        }), h.css({
                            width: s,
                            height: n
                        }))
                    };
                w.on("spa:adjustfullscreen" + (a.os.ios ? " orientationchange": ""),
                    function() {
                        void 0 !== e && (cancelAnimationFrame(e), e = void 0),
                            e = requestAnimationFrame(i)
                    }),
                a.os.android && w.on("orientationchange",
                    function() {
                        clearTimeout(t),
                            t = setTimeout(i, 500)
                    }),
                    w.on("resize",
                        function() {
                            clearTimeout(t),
                                t = setTimeout(i, 200)
                        })
            } (),
            y.on("spa:scroll",
                function(e, t) {
                    var n = a(e.target),
                        s = t && t.direction || "";
                    n.addClass("spa-scroll" + (s ? " spa-scroll-" + s: ""))
                }),
            y.on("spa:removescroll",
                function(e) {
                    var t = a(e.target);
                    t.removeClass("spa-scroll spa-scroll-x spa-scroll-y")
                }),
            w.on("popstate",
                function(a) {
                    if (D) {
                        if (m && "block" === m.css("display")) return ! 1;
                        if (b && b.hasClass("spa-panel")) {
                            var t = b.data("id"),
                                n = _[t],
                                s = n.prevPage;
                            return s.trigger("spa:openpage"),
                                !1
                        }
                        var i = e();
                        if (!O.length || O[O.length - 1] !== i) {
                            O.push(i);
                            var o = j[i],
                                r = a.state || {};
                            if (v && (r = v, v = void 0), o) {
                                var p = o.data("id"),
                                    c = _[p];
                                c.oldpushData = c.pushData,
                                    c.pushData = r,
                                    o.trigger("spa:openpage")
                            } else y.trigger("spa:createpage", {
                                hash: i,
                                pushData: r
                            })
                        }
                    }
                });
        var q = /\((.*?)\)/g,
            H = /(\(\?)?:\w+/g,
            L = /\*\w+/g,
            M = /[\-{}\[\]+?.,\\\^$|#\s]/g,
            V = {
                route: "",
                animate: "",
                classname: "",
                view: function() {
                    return {
                        title: "",
                        body: ""
                    }
                },
                init: function() {},
                beforeopen: function() {},
                afteropen: function() {},
                beforeclose: function() {},
                afterclose: function() {}
            };
        y.on("spa:route",
            function(e, s) {
                var i = Array.prototype.slice.call(arguments, 1);
                if (i.length > 1) return a.each(i,
                    function(a, e) {
                        y.trigger("spa:route", e)
                    }),
                    !1;
                var o = s.route || "";
                t(o) || (o = n(o)),
                s.animate && !a.isFunction(s.animate) && J(s.animate) && (s.animate = ""),
                    I[o] = a.extend({},
                        V, s)
            });
        var W, U, Z, B = {},
            G = {}; !
            function() {
                var a, e = {
                        "-webkit-transition": ["-webkit-transform", "webkitTransitionEnd"],
                        "-moz-transition": ["-moz-transform", "transitionend"],
                        "-ms-transition": ["-ms-transform", "msTransitionEnd"],
                        "-o-transition": ["-o-transform", "oTransitionEnd"],
                        transition: ["transform", "transitionend"]
                    },
                    t = document.createElement("div");
                for (a in e) if (void 0 !== t.style[a]) {
                    U = a,
                        W = e[a][0],
                        Z = e[a][1];
                    break
                }
            } (),
            a.fn.emulateTransition = function(e, t) {
                var n = a(this);
                return requestAnimationFrame(function() {
                    n.get(0).offsetWidth,
                        e[U] = "0.4s",
                        n.css(e).emulateTransitionEnd(function() {
                            e = {},
                                e[U] = "",
                                n.css(e),
                            t && t()
                        })
                }),
                    n
            },
            a.fn.emulateTransitionEnd = function(e, t) {
                var n, s = !1,
                    i = a(this);
                t = t || 500,
                    i.one(Z,
                        function() {
                            s = !0,
                                clearTimeout(n),
                                e.call(i)
                        });
                var o = function() {
                    s || i.trigger(Z)
                };
                return n = setTimeout(o, t),
                    i
            },
            B.defaultInOut = function(a, e, t) {
                i(e, a),
                    t()
            },
            y.on("spa:addTransitPageAnimates",
                function(e, t) {
                    var n = [];
                    a.each(t,
                        function(a) {
                            n.push(a)
                        }),
                        a.each(n,
                            function(a, e) {
                                G[e] = a % 2 === 0 ? n[a + 1] : n[a - 1]
                            }),
                        a.extend(B, t)
                }),
            y.trigger("spa:addTransitPageAnimates", {
                pushInLeft: function(e, t, n) {
                    var s = a(".spa-page-body", e),
                        o = a(".spa-page-body", t),
                        r = 0,
                        p = {},
                        c = {},
                        l = {};
                    p[W] = "translate(100%, 0)",
                        c[W] = "translate(0%, 0)",
                        l[W] = "translate(-100%, 0)",
                        s.css(p),
                        i(t, e),
                        s.emulateTransition(c,
                            function() {
                                2 == ++r && n()
                            }),
                        o.emulateTransition(l,
                            function() {
                                2 == ++r && n()
                            })
                },
                pushOutRight: function(e, t, n) {
                    var s = a(".spa-page-body", e),
                        o = a(".spa-page-body", t),
                        r = 0,
                        p = {},
                        c = {},
                        l = {};
                    p[W] = "translate(-100%, 0)",
                        c[W] = "translate(0%, 0)",
                        l[W] = "translate(100%, 0)",
                        s.css(p),
                        i(t, e),
                        s.emulateTransition(c,
                            function() {
                                2 == ++r && n()
                            }),
                        o.emulateTransition(l,
                            function() {
                                2 == ++r && n()
                            })
                }
            });
        var J = function() {
                var a = /^(overlay|reveal|pushPart).*$/;
                return function(e) {
                    return a.test(e)
                }
            } (),
            K = function() {
                var a = 0;
                return function() {
                    return a++
                }
            } ();
        y.on("spa:createpage",
            function(e, n) {
                y.trigger("spa:openloader");
                var i, o, r, p = n.hash,
                    c = n.pushData;
                if (a.each(I,
                        function(a, e) {
                            return o = new RegExp(a),
                                o.test(p) ? (i = a, r = e, !1) : void(o = !1)
                        }), t(o)) {
                    var l, u, d = (r.classname ? " spa-page-" + r.classname: "") + (r.nocache ? " no-cache": ""),
                        g = a('<div class="spa-page' + d + '"><div class="spa-page-body"></div></div>'),
                        f = K();
                    g.data("id", f),
                        l = {
                            id: f,
                            hash: p,
                            pushData: c,
                            requestData: s(o, p),
                            route: i,
                            el: g
                        },
                        _[f] = l,
                    !r.nocache && y.trigger("spa:viewcache", {
                        view: g
                    }),
                        u = r.view.call(g, l),
                    a.isPlainObject(u) && y.trigger("spa:initpage", [g, u])
                }
            }),
            y.on("spa:initpage",
                function(e, t, n) {
                    var s = t.data("id"),
                        i = _[s],
                        o = I[i.route];
                    a.each(["init", "beforeopen", "afteropen", "beforeclose", "afterclose"],
                        function(a, e) {
                            n[e] && (o[e] = n[e])
                        }),
                        i.viewData = n,
                        y.trigger("spa:closeloader"),
                        a(".spa-page-body", t).html(n.body),
                        f.append(t),
                        t.trigger("spa:openpage")
                }),
            y.on("spa:openpage", ".spa-page",
                function(t, n) {
                    var s = a(t.currentTarget),
                        i = s.data("id"),
                        r = _[i],
                        p = I[r.route],
                        c = r.hash,
                        l = r.pushData,
                        u = (r.oldpushData, r.requestData, r.viewData),
                        d = l.title || u.title,
                        g = !1,
                        v = l.animate || p.animate;
                    b || (f.append('<div class="spa-page spa-page-empty"><div class="spa-page-body"></div></div>'), b = a(".spa-page-empty"));
                    var h = b.data("id"),
                        m = _[h] || {};
                    if (!l.animate && m.prevPage && m.prevPage.data("id") === i) {
                        var P = m.prevAnimate;
                        P && !a.isFunction(P) && (v = G[P]),
                            g = !0
                    }
                    var T, D;
                    if (b.hasClass("spa-panel")) {
                        var x = A[b.data("id")];
                        T = x.beforeclose,
                            D = x.afterclose
                    } else if (m.route) {
                        var z = I[m.route];
                        T = z.beforeclose,
                            D = z.afterclose,
                            y.trigger("spa:navigate", {
                                hash: c,
                                title: d,
                                pushData: l,
                                replace: !0
                            })
                    }
                    var S = function() {
                        s.data("spa:init") || (s.data("spa:init", !0), p.init.call(s, r), a.os.ios && parseInt(a.os.version.slice(0, 1)) > 5 && a(".spa-scroll", s).addClass("spa-scroll-touch")),
                        a.os.ios && parseInt(a.os.version.slice(0, 1)) > 5 && a(".spa-scroll", b).removeClass("spa-scroll-touch"),
                        D && D.call(b, m),
                        b.hasClass("no-cache") && (b.remove(), O.splice(a.inArray(m.hash, O), 1)),
                            b.hasClass("spa-panel") ? (b.css({
                                zIndex: E
                            }), r.prevPage && r.prevPage.css({
                                zIndex: k
                            })) : g || (r.prevPage = b),
                            p.afteropen.call(s, r),
                            b = s,
                            r.hash !== e() ? (C = !1, w.trigger("popstate")) : (a.isFunction(n) && n.call(s), C = !1)
                    };
                    T && T.call(b, m),
                        p.beforeopen.call(s, r),
                    !g && (r.prevAnimate = v),
                    a.os.ios && parseInt(a.os.version.slice(0, 1)) > 5 && a(".spa-scroll", s).addClass("spa-scroll-touch"),
                    g || m.prevPage && m.prevPage.css({
                        zIndex: E
                    }),
                        a.isFunction(v) ? v(s, b, S) : o(s, b, v, S),
                        y.trigger("spa:viewcachesort", {
                            view: s
                        })
                });
        var N = {
            id: "",
            animate: "",
            classname: "",
            view: function() {
                return {
                    body: ""
                }
            },
            init: function() {},
            beforeopen: function() {},
            afteropen: function() {},
            beforeclose: function() {},
            afterclose: function() {}
        };
        y.on("spa:panel",
            function(e, t) {
                var n = Array.prototype.slice.call(arguments, 1);
                return n.length > 1 ? (a.each(n,
                    function(a, e) {
                        y.trigger("spa:panel", e)
                    }), !1) : void(t.id && !A[t.id] && (A[t.id] = a.extend({},
                    N, t)))
            }),
            y.on("spa:createpanel",
                function(e, t, n) {
                    var s = A[t];
                    if (s) {
                        y.trigger("spa:openloader");
                        var i, o, r = s.classname ? " spa-panel-" + s.classname: "",
                            p = a('<div id="spa-panel-' + t + '" class="spa-page spa-panel ' + r + '"><div class="spa-page-bg"></div><div class="spa-page-body"></div></div>');
                        p.data("id", t),
                            i = {
                                id: t,
                                pushData: n,
                                el: p
                            },
                            _[t] = i,
                            y.trigger("spa:viewcache", {
                                view: p
                            }),
                            o = s.view.call(p, i),
                        a.isPlainObject(o) && p.trigger("spa:initpanel", [p, o])
                    }
                }),
            y.on("spa:initpanel",
                function(e, t, n) {
                    var s = t.data("id"),
                        i = _[s],
                        o = i.pushData,
                        r = A[s];
                    a.each(["init", "beforeopen", "afteropen", "beforeclose", "afterclose"],
                        function(a, e) {
                            n[e] && (r[e] = n[e])
                        }),
                        i.viewData = n,
                        a(".spa-page-body", t).html(n.body),
                        f.append(t),
                        y.trigger("spa:closeloader"),
                        t.trigger("spa:openpanel", [s, o])
                }),
            y.on("spa:openpanel",
                function(e, t, n) {
                    if (m && "block" === m.css("display")) return ! 1;
                    var s = F[t];
                    if (n || (n = {}), s) {
                        if (C) return ! 1;
                        C = !0;
                        var i = A[t],
                            r = n.animate || i.animate;
                        if (b.hasClass("spa-panel")) {
                            var p = _[b.data("id")].prevPage;
                            return p.trigger("spa:openpage", [function() {
                                y.trigger("spa:openpanel", [t, n])
                            }]),
                                !1
                        }
                        var c = _[t];
                        c.oldpushData = c.pushData,
                            c.pushData = n,
                            i.beforeopen.call(s, c);
                        var l = function() {
                            s.data("spa:init") || (s.data("spa:init", !0), i.init.call(s, c), a.os.ios && parseInt(a.os.version.slice(0, 1)) > 5 && setTimeout(function() {
                                    a(".spa-scroll", s).addClass("spa-scroll-touch")
                                },
                                17)),
                                c.prevPage = b,
                                i.afteropen.call(s, c),
                                b = s,
                                C = !1
                        };
                        c.prevAnimate = r,
                        a.os.ios && parseInt(a.os.version.slice(0, 1)) > 5 && a(".spa-scroll", s).addClass("spa-scroll-touch");
                        var u = b.data("id"),
                            d = _[u] || {};
                        d.prevPage && d.prevPage.css({
                            zIndex: E
                        }),
                            a.isFunction(r) ? r(s, b, l) : o(s, b, r, l),
                            y.trigger("spa:viewcachesort", {
                                view: s
                            })
                    } else y.trigger("spa:createpanel", [t, n])
                }),
            y.on("spa:closepanel",
                function(e, t) {
                    var n = a(e.target),
                        s = n.data("id"),
                        i = _[s];
                    if (t && t.id && (n = a(".spa-panel-" + t.id)), b.hasClass("spa-panel") && b.data("id") === s) {
                        var o = i.prevPage;
                        o.trigger("spa:openpage")
                    }
                }),
            y.on("click touchstart", ".spa-panel",
                function(e) {
                    var t = a(e.currentTarget),
                        n = a(e.target); (n.hasClass("spa-page-bg") || n.hasClass("spa-panel")) && (e.stopPropagation(), e.preventDefault(), t.trigger("spa:closepanel"))
                }),
            y.on("spa:viewcachecount",
                function(a, e) {
                    S = e.count
                }),
            y.on("spa:viewcache",
                function(e, t) {
                    var n, s, i = t.view,
                        o = i.data("id");
                    if (i.hasClass("spa-panel") ? (n = "panle", s = o, F[s] = i) : (n = "page", s = _[o].hash, j[s] = i), $.unshift(n + ":" + s), 0 !== S && $.length > S) {
                        var r, p, c, l, u = $.splice(S);
                        a.each(u,
                            function(e, t) {
                                r = t.split(":", 2),
                                    p = r[0],
                                    c = r[1],
                                    l = "page" == p ? j: F,
                                    a("img", l[c]).remove(),
                                    l[c].html("").remove(),
                                    delete l[c]
                            })
                    }
                }),
            y.on("spa:viewcachesort",
                function(a, e) {
                    var t, n, s, i, o = e.view,
                        r = o.data("id");
                    o.hasClass("spa-panel") ? (t = "panle", n = r) : (t = "page", n = _[r].hash),
                        s = t + ":" + n,
                        i = $.indexOf(s),
                    -1 !== i && ($.splice(i, 1), $.unshift(s))
                }),
            y.on("spa:navigate",
                function(e, t) {
                    var n = t.hash || "",
                        s = t.title || "",
                        i = t.pushData || {},
                        o = t.replace || !1,
                        r = t.url || "";
                    if (s && (document.title = s), n = r + "#!/" + n, o) T.replaceState(i, s, n);
                    else {
                        if (!b.hasClass("spa-panel") && O.length && "#!/" + O[O.length - 1] === n) return;
                        if (C) return ! 1;
                        C = !0,
                            T.pushState(i, s, n),
                        !a.isEmptyObject(i) && (v = i),
                            w.trigger("popstate")
                    }
                }),
            y.on("spa:loader",
                function(a, e) {
                    e.body && (R = e.body)
                });
        var Q;
        y.on("spa:openloader",
            function() {
                Q = setTimeout(function() {
                        Q = void 0,
                            m.show()
                    },
                    300)
            }),
            y.on("spa:closeloader",
                function() {
                    Q ? (clearTimeout(Q), Q = void 0) : m.hide()
                }),
            y.on("spa:boot",
                function(e, t) {
                    f = a("body"),
                        f.append('<div class="spa-fullscreen"></div><div class="spa-loader">' + R + "</div>"),
                        h = a(".spa-fullscreen"),
                        y.trigger("spa:adjustfullscreen"),
                        m = a(".spa-loader"),
                        m.on("click select mousedown mousemove mouseup touchstart touchmove touchend", r),
                        D = !0,
                        w.trigger("popstate"),
                    t && t.callback && t.callback()
                });
        var y = a(document),
            w = a(window);
        a.spa.boot = function(a) {
            y.trigger("spa:boot", a)
        },
            a.spa.addRoute = function(a) {
                y.trigger("spa:route", a)
            },
            a.spa.addPanel = function(a) {
                y.trigger("spa:panel", a)
            },
            a.spa.navigate = function(a) {
                y.trigger("spa:navigate", a)
            },
            a.spa.setViewCacheCount = function(a) {
                y.trigger("spa:viewcachecount", a)
            },
            a.spa.addElScroll = function(a, e) {
                a.trigger("spa:scroll", e)
            },
            a.spa.removeElScroll = function(a, e) {
                a.trigger("spa:removescroll", e)
            },
            a.spa.addTransitPageAnimates = function(a) {
                y.trigger("spa:addTransitPageAnimates", a)
            },
            a.spa.openLoader = function() {
                y.trigger("spa:openloader")
            },
            a.spa.closeLoader = function() {
                y.trigger("spa:closeloader")
            },
            a.spa.fullScreen = function() {
                w.trigger("spa:adjustfullscreen")
            },
            a.spa.getViewData = function(a) {
                return _[a.data("id")]
            },
            a.spa.getCurPage = function() {
                return b
            },
            a.spa.getHash = function(a) {
                return e(a)
            }
    } (window.Zepto);
/*!js/librarys/fastclick.js*/
; !
    function() {
        "use strict";
        function t(e, o) {
            function i(t, e) {
                return function() {
                    return t.apply(e, arguments)
                }
            }
            var r;
            if (o = o || {},
                    this.trackingClick = !1, this.trackingClickStart = 0, this.targetElement = null, this.touchStartX = 0, this.touchStartY = 0, this.lastTouchIdentifier = 0, this.touchBoundary = o.touchBoundary || 10, this.layer = e, this.tapDelay = o.tapDelay || 200, this.tapTimeout = o.tapTimeout || 700, !t.notNeeded(e)) {
                for (var a = ["onMouse", "onClick", "onTouchStart", "onTouchMove", "onTouchEnd", "onTouchCancel"], c = this, s = 0, u = a.length; u > s; s++) c[a[s]] = i(c[a[s]], c);
                n && (e.addEventListener("mouseover", this.onMouse, !0), e.addEventListener("mousedown", this.onMouse, !0), e.addEventListener("mouseup", this.onMouse, !0)),
                    e.addEventListener("click", this.onClick, !0),
                    e.addEventListener("touchstart", this.onTouchStart, !1),
                    e.addEventListener("touchmove", this.onTouchMove, !1),
                    e.addEventListener("touchend", this.onTouchEnd, !1),
                    e.addEventListener("touchcancel", this.onTouchCancel, !1),
                Event.prototype.stopImmediatePropagation || (e.removeEventListener = function(t, n, o) {
                    var i = Node.prototype.removeEventListener;
                    "click" === t ? i.call(e, t, n.hijacked || n, o) : i.call(e, t, n, o)
                },
                    e.addEventListener = function(t, n, o) {
                        var i = Node.prototype.addEventListener;
                        "click" === t ? i.call(e, t, n.hijacked || (n.hijacked = function(t) {
                                t.propagationStopped || n(t)
                            }), o) : i.call(e, t, n, o)
                    }),
                "function" == typeof e.onclick && (r = e.onclick, e.addEventListener("click",
                    function(t) {
                        r(t)
                    },
                    !1), e.onclick = null)
            }
        }
        var e = navigator.userAgent.indexOf("Windows Phone") >= 0,
            n = navigator.userAgent.indexOf("Android") > 0 && !e,
            o = /iP(ad|hone|od)/.test(navigator.userAgent) && !e,
            i = o && /OS 4_\d(_\d)?/.test(navigator.userAgent),
            r = o && /OS [6-7]_\d/.test(navigator.userAgent),
            a = navigator.userAgent.indexOf("BB10") > 0;
        t.prototype.needsClick = function(t) {
            switch (t.nodeName.toLowerCase()) {
                case "button":
                case "select":
                case "textarea":
                    if (t.disabled) return ! 0;
                    break;
                case "input":
                    if (o && "file" === t.type || t.disabled) return ! 0;
                    break;
                case "label":
                case "iframe":
                case "video":
                    return ! 0
            }
            return /\bneedsclick\b/.test(t.className)
        },
            t.prototype.needsFocus = function(t) {
                switch (t.nodeName.toLowerCase()) {
                    case "textarea":
                        return ! 0;
                    case "select":
                        return ! n;
                    case "input":
                        switch (t.type) {
                            case "button":
                            case "checkbox":
                            case "file":
                            case "image":
                            case "radio":
                            case "submit":
                                return ! 1
                        }
                        return ! t.disabled && !t.readOnly;
                    default:
                        return /\bneedsfocus\b/.test(t.className)
                }
            },
            t.prototype.sendClick = function(t, e) {
                var n, o;
                document.activeElement && document.activeElement !== t && document.activeElement.blur(),
                    o = e.changedTouches[0],
                    n = document.createEvent("MouseEvents"),
                    n.initMouseEvent(this.determineEventType(t), !0, !0, window, 1, o.screenX, o.screenY, o.clientX, o.clientY, !1, !1, !1, !1, 0, null),
                    n.forwardedTouchEvent = !0,
                    t.dispatchEvent(n)
            },
            t.prototype.determineEventType = function(t) {
                return n && "select" === t.tagName.toLowerCase() ? "mousedown": "click"
            },
            t.prototype.focus = function(t) {
                var e;
                o && t.setSelectionRange && 0 !== t.type.indexOf("date") && "time" !== t.type && "month" !== t.type ? (e = t.value.length, t.setSelectionRange(e, e)) : t.focus()
            },
            t.prototype.updateScrollParent = function(t) {
                var e, n;
                if (e = t.fastClickScrollParent, !e || !e.contains(t)) {
                    n = t;
                    do {
                        if (n.scrollHeight > n.offsetHeight) {
                            e = n,
                                t.fastClickScrollParent = n;
                            break
                        }
                        n = n.parentElement
                    } while ( n )
                }
                e && (e.fastClickLastScrollTop = e.scrollTop)
            },
            t.prototype.getTargetElementFromEventTarget = function(t) {
                return t.nodeType === Node.TEXT_NODE ? t.parentNode: t
            },
            t.prototype.onTouchStart = function(t) {
                var e, n, r;
                if (t.targetTouches.length > 1) return ! 0;
                if (e = this.getTargetElementFromEventTarget(t.target), n = t.targetTouches[0], o) {
                    if (r = window.getSelection(), r.rangeCount && !r.isCollapsed) return ! 0;
                    if (!i) {
                        if (n.identifier && n.identifier === this.lastTouchIdentifier) return t.preventDefault(),
                            !1;
                        this.lastTouchIdentifier = n.identifier,
                            this.updateScrollParent(e)
                    }
                }
                return this.trackingClick = !0,
                    this.trackingClickStart = t.timeStamp,
                    this.targetElement = e,
                    this.touchStartX = n.pageX,
                    this.touchStartY = n.pageY,
                t.timeStamp - this.lastClickTime < this.tapDelay && t.preventDefault(),
                    !0
            },
            t.prototype.touchHasMoved = function(t) {
                var e = t.changedTouches[0],
                    n = this.touchBoundary;
                return Math.abs(e.pageX - this.touchStartX) > n || Math.abs(e.pageY - this.touchStartY) > n ? !0 : !1
            },
            t.prototype.onTouchMove = function(t) {
                return this.trackingClick ? ((this.targetElement !== this.getTargetElementFromEventTarget(t.target) || this.touchHasMoved(t)) && (this.trackingClick = !1, this.targetElement = null), !0) : !0
            },
            t.prototype.findControl = function(t) {
                return void 0 !== t.control ? t.control: t.htmlFor ? document.getElementById(t.htmlFor) : t.querySelector("button, input:not([type=hidden]), keygen, meter, output, progress, select, textarea")
            },
            t.prototype.onTouchEnd = function(t) {
                var e, a, c, s, u, l = this.targetElement;
                if (!this.trackingClick) return ! 0;
                if (t.timeStamp - this.lastClickTime < this.tapDelay) return this.cancelNextClick = !0,
                    !0;
                if (t.timeStamp - this.trackingClickStart > this.tapTimeout) return ! 0;
                if (this.cancelNextClick = !1, this.lastClickTime = t.timeStamp, a = this.trackingClickStart, this.trackingClick = !1, this.trackingClickStart = 0, r && (u = t.changedTouches[0], l = document.elementFromPoint(u.pageX - window.pageXOffset, u.pageY - window.pageYOffset) || l, l.fastClickScrollParent = this.targetElement.fastClickScrollParent), c = l.tagName.toLowerCase(), "label" === c) {
                    if (e = this.findControl(l)) {
                        if (this.focus(l), n) return ! 1;
                        l = e
                    }
                } else if (this.needsFocus(l)) return t.timeStamp - a > 100 || o && window.top !== window && "input" === c ? (this.targetElement = null, !1) : (this.focus(l), this.sendClick(l, t), o && "select" === c || (this.targetElement = null, t.preventDefault()), !1);
                return o && !i && (s = l.fastClickScrollParent, s && s.fastClickLastScrollTop !== s.scrollTop) ? !0 : (this.needsClick(l) || (t.preventDefault(), this.sendClick(l, t)), !1)
            },
            t.prototype.onTouchCancel = function() {
                this.trackingClick = !1,
                    this.targetElement = null
            },
            t.prototype.onMouse = function(t) {
                return this.targetElement ? t.forwardedTouchEvent ? !0 : t.cancelable && (!this.needsClick(this.targetElement) || this.cancelNextClick) ? (t.stopImmediatePropagation ? t.stopImmediatePropagation() : t.propagationStopped = !0, t.stopPropagation(), t.preventDefault(), !1) : !0 : !0
            },
            t.prototype.onClick = function(t) {
                var e;
                return this.trackingClick ? (this.targetElement = null, this.trackingClick = !1, !0) : "submit" === t.target.type && 0 === t.detail ? !0 : (e = this.onMouse(t), e || (this.targetElement = null), e)
            },
            t.prototype.destroy = function() {
                var t = this.layer;
                n && (t.removeEventListener("mouseover", this.onMouse, !0), t.removeEventListener("mousedown", this.onMouse, !0), t.removeEventListener("mouseup", this.onMouse, !0)),
                    t.removeEventListener("click", this.onClick, !0),
                    t.removeEventListener("touchstart", this.onTouchStart, !1),
                    t.removeEventListener("touchmove", this.onTouchMove, !1),
                    t.removeEventListener("touchend", this.onTouchEnd, !1),
                    t.removeEventListener("touchcancel", this.onTouchCancel, !1)
            },
            t.notNeeded = function(t) {
                var e, o, i, r;
                if ("undefined" == typeof window.ontouchstart) return ! 0;
                if (o = +(/Chrome\/([0-9]+)/.exec(navigator.userAgent) || [, 0])[1]) {
                    if (!n) return ! 0;
                    if (e = document.querySelector("meta[name=viewport]")) {
                        if ( - 1 !== e.content.indexOf("user-scalable=no")) return ! 0;
                        if (o > 31 && document.documentElement.scrollWidth <= window.outerWidth) return ! 0
                    }
                }
                if (a && (i = navigator.userAgent.match(/Version\/([0-9]*)\.([0-9]*)/), i[1] >= 10 && i[2] >= 3 && (e = document.querySelector("meta[name=viewport]")))) {
                    if ( - 1 !== e.content.indexOf("user-scalable=no")) return ! 0;
                    if (document.documentElement.scrollWidth <= window.outerWidth) return ! 0
                }
                return "none" === t.style.msTouchAction || "manipulation" === t.style.touchAction ? !0 : (r = +(/Firefox\/([0-9]+)/.exec(navigator.userAgent) || [, 0])[1], r >= 27 && (e = document.querySelector("meta[name=viewport]"), e && ( - 1 !== e.content.indexOf("user-scalable=no") || document.documentElement.scrollWidth <= window.outerWidth)) ? !0 : "none" === t.style.touchAction || "manipulation" === t.style.touchAction ? !0 : !1)
            },
            t.attach = function(e, n) {
                return new t(e, n)
            },
            "function" == typeof define && "object" == typeof define.amd && define.amd ? define(function() {
                return t
            }) : "undefined" != typeof module && module.exports ? (module.exports = t.attach, module.exports.FastClick = t) : window.FastClick = t
    } ();
/*!js/librarys/preLoader.js*/
;~
    function(e) {
        "use strict";
        var o, n;
        o = function(o, n) {
            var r = this;
            r.options = e.extend(!0, {
                    pipeline: !1,
                    auto: !0,
                    onComplete: function() {}
                },
                n),
                r.addQueue(o),
            r.queue.length && this.options.auto && r.processQueue()
        },
            e.extend(o.prototype, {
                addQueue: function(e) {
                    var o = this;
                    return o.queue = e.slice(),
                        o
                },
                reset: function() {
                    var e = this;
                    return e.completed = [],
                        e.errors = [],
                        e
                },
                load: function(e, o) {
                    var n = this,
                        r = new Image;
                    return r.onerror = r.onabort = function() {
                        r.onerror = r.onabort = r.onload = null,
                            n.errors.push(e),
                        n.options.onError && n.options.onError.call(n, e),
                            n._checkProgress(e),
                        n.options.pipeline && n.loadNext(o)
                    },
                        r.onload = function() {
                            r.onerror = r.onabort = r.onload = null,
                                n.completed.push(e),
                                n._checkProgress(e, this),
                            n.options.pipeline && n.loadNext(o)
                        },
                        r.src = e,
                        n
                },
                loadNext: function(e) {
                    var o = this;
                    return e++,
                    o.queue[e] && o.load(o.queue[e], e),
                        o
                },
                processQueue: function() {
                    var e = this,
                        o = 0,
                        n = e.queue,
                        r = n.length;
                    if (e.reset(), e.options.pipeline) e.load(n[0], 0);
                    else for (; r > o; ++o) e.load(n[o], o);
                    return e
                },
                _checkProgress: function(e, o) {
                    var n = this,
                        r = [];
                    return n.options.onProgress && e && n.options.onProgress.call(n, e, o, n.completed.length),
                    n.completed.length + n.errors.length === n.queue.length && (r.push(n.completed), n.errors.length && r.push(n.errors), n.options.onComplete.apply(n, r)),
                        n
                }
            }),
            n = function(e, n) {
                return new o(e, n)
            },
            "function" == typeof define ? define(function() {
                return n
            }) : e.preLoader = n
    } (Zepto);
/*!js/librarys/template.js*/
; !
    function() {
        function e(e) {
            return e.replace(y, "").replace(b, ",").replace(w, "").replace(x, "").replace(T, "").split(j)
        }
        function n(e) {
            return "'" + e.replace(/('|\\)/g, "\\$1").replace(/\r/g, "\\r").replace(/\n/g, "\\n") + "'"
        }
        function t(t, r) {
            function a(e) {
                return p += e.split(/\n/).length - 1,
                s && (e = e.replace(/\s+/g, " ").replace(/<!--[\w\W]*?-->/g, "")),
                e && (e = m[1] + n(e) + m[2] + "\n"),
                    e
            }
            function i(n) {
                var t = p;
                if (u ? n = u(n, r) : o && (n = n.replace(/\n/g,
                        function() {
                            return p++,
                            "$line=" + p + ";"
                        })), 0 === n.indexOf("=")) {
                    var a = f && !/^=[=#]/.test(n);
                    if (n = n.replace(/^=[=#]?|[\s;]*$/g, ""), a) {
                        var i = n.replace(/\s*\([^\)]+\)/, "");
                        $[i] || /^(include|print)$/.test(i) || (n = "$escape(" + n + ")")
                    } else n = "$string(" + n + ")";
                    n = m[1] + n + m[2]
                }
                return o && (n = "$line=" + t + ";" + n),
                    v(e(n),
                        function(e) {
                            if (e && !g[e]) {
                                var n;
                                n = "print" === e ? b: "include" === e ? w: $[e] ? "$utils." + e: d[e] ? "$helpers." + e: "$data." + e,
                                    x += e + "=" + n + ",",
                                    g[e] = !0
                            }
                        }),
                n + "\n"
            }
            var o = r.debug,
                c = r.openTag,
                l = r.closeTag,
                u = r.parser,
                s = r.compress,
                f = r.escape,
                p = 1,
                g = {
                    $data: 1,
                    $filename: 1,
                    $utils: 1,
                    $helpers: 1,
                    $out: 1,
                    $line: 1
                },
                h = "".trim,
                m = h ? ["$out='';", "$out+=", ";", "$out"] : ["$out=[];", "$out.push(", ");", "$out.join('')"],
                y = h ? "$out+=text;return $out;": "$out.push(text);",
                b = "function(){var text=''.concat.apply('',arguments);" + y + "}",
                w = "function(filename,data){data=data||$data;var text=$utils.$include(filename,data,$filename);" + y + "}",
                x = "'use strict';var $utils=this,$helpers=$utils.$helpers," + (o ? "$line=0,": ""),
                T = m[0],
                j = "return new String(" + m[3] + ");";
            v(t.split(c),
                function(e) {
                    e = e.split(l);
                    var n = e[0],
                        t = e[1];
                    1 === e.length ? T += a(n) : (T += i(n), t && (T += a(t)))
                });
            var k = x + T + j;
            o && (k = "try{" + k + "}catch(e){throw {filename:$filename,name:'Render Error',message:e.message,line:$line,source:" + n(t) + ".split(/\\n/)[$line-1].replace(/^\\s+/,'')};}");
            try {
                var E = new Function("$data", "$filename", k);
                return E.prototype = $,
                    E
            } catch(S) {
                throw S.temp = "function anonymous($data,$filename) {" + k + "}",
                    S
            }
        }
        var r = function(e, n) {
            return "string" == typeof n ? h(n, {
                filename: e
            }) : o(e, n)
        };
        r.version = "3.0.0",
            r.config = function(e, n) {
                a[e] = n
            };
        var a = r.defaults = {
                openTag: "<%",
                closeTag: "%>",
                escape: !0,
                cache: !0,
                compress: !1,
                parser: null
            },
            i = r.cache = {};
        r.render = function(e, n) {
            return h(e, n)
        };
        var o = r.renderFile = function(e, n) {
            var t = r.get(e) || g({
                    filename: e,
                    name: "Render Error",
                    message: "Template not found"
                });
            return n ? t(n) : t
        };
        r.get = function(e) {
            var n;
            if (i[e]) n = i[e];
            else if ("object" == typeof document) {
                var t = document.getElementById(e);
                if (t) {
                    var r = (t.value || t.innerHTML).replace(/^\s*|\s*$/g, "");
                    n = h(r, {
                        filename: e
                    })
                }
            }
            return n
        };
        var c = function(e, n) {
                return "string" != typeof e && (n = typeof e, "number" === n ? e += "": e = "function" === n ? c(e.call(e)) : ""),
                    e
            },
            l = {
                "<": "&#60;",
                ">": "&#62;",
                '"': "&#34;",
                "'": "&#39;",
                "&": "&#38;"
            },
            u = function(e) {
                return l[e]
            },
            s = function(e) {
                return c(e).replace(/&(?![\w#]+;)|[<>"']/g, u)
            },
            f = Array.isArray ||
                function(e) {
                    return "[object Array]" === {}.toString.call(e)
                },
            p = function(e, n) {
                var t, r;
                if (f(e)) for (t = 0, r = e.length; r > t; t++) n.call(e, e[t], t, e);
                else for (t in e) n.call(e, e[t], t)
            },
            $ = r.utils = {
                $helpers: {},
                $include: o,
                $string: c,
                $escape: s,
                $each: p
            };
        r.helper = function(e, n) {
            d[e] = n
        };
        var d = r.helpers = $.$helpers;
        r.onerror = function(e) {
            var n = "Template Error\n\n";
            for (var t in e) n += "<" + t + ">\n" + e[t] + "\n\n"
        };
        var g = function(e) {
                return r.onerror(e),
                    function() {
                        return "{Template Error}"
                    }
            },
            h = r.compile = function(e, n) {
                function r(t) {
                    try {
                        return new l(t, c) + ""
                    } catch(r) {
                        return n.debug ? g(r)() : (n.debug = !0, h(e, n)(t))
                    }
                }
                n = n || {};
                for (var o in a) void 0 === n[o] && (n[o] = a[o]);
                var c = n.filename;
                try {
                    var l = t(e, n)
                } catch(u) {
                    return u.filename = c || "anonymous",
                        u.name = "Syntax Error",
                        g(u)
                }
                return r.prototype = l.prototype,
                    r.toString = function() {
                        return l.toString()
                    },
                c && n.cache && (i[c] = r),
                    r
            },
            v = $.$each,
            m = "break,case,catch,continue,debugger,default,delete,do,else,false,finally,for,function,if,in,instanceof,new,null,return,switch,this,throw,true,try,typeof,var,void,while,with,abstract,boolean,byte,char,class,const,double,enum,export,extends,final,float,goto,implements,import,int,interface,long,native,package,private,protected,public,short,static,super,synchronized,throws,transient,volatile,arguments,let,yield,undefined",
            y = /\/\*[\w\W]*?\*\/|\/\/[^\n]*\n|\/\/[^\n]*$|"(?:[^"\\]|\\[\w\W])*"|'(?:[^'\\]|\\[\w\W])*'|\s*\.\s*[$\w\.]+/g,
            b = /[^\w$]+/g,
            w = new RegExp(["\\b" + m.replace(/,/g, "\\b|\\b") + "\\b"].join("|"), "g"),
            x = /^\d[^,]*|,\d[^,]*/g,
            T = /^,+|,+$/g,
            j = /^$|,+/;
        a.openTag = "{{",
            a.closeTag = "}}";
        var k = function(e, n) {
            var t = n.split(":"),
                r = t.shift(),
                a = t.join(":") || "";
            return a && (a = ", " + a),
            "$helpers." + r + "(" + e + a + ")"
        };
        a.parser = function(e) {
            e = e.replace(/^\s/, "");
            var n = e.split(" "),
                t = n.shift(),
                a = n.join(" ");
            switch (t) {
                case "if":
                    e = "if(" + a + "){";
                    break;
                case "else":
                    n = "if" === n.shift() ? " if(" + n.join(" ") + ")": "",
                        e = "}else" + n + "{";
                    break;
                case "/if":
                    e = "}";
                    break;
                case "each":
                    var i = n[0] || "$data",
                        o = n[1] || "as",
                        c = n[2] || "$value",
                        l = n[3] || "$index",
                        u = c + "," + l;
                    "as" !== o && (i = "[]"),
                        e = "$each(" + i + ",function(" + u + "){";
                    break;
                case "/each":
                    e = "});";
                    break;
                case "echo":
                    e = "print(" + a + ");";
                    break;
                case "print":
                case "include":
                    e = t + "(" + n.join(",") + ");";
                    break;
                default:
                    if (/^\s*\|\s*[\w\$]/.test(a)) {
                        var s = !0;
                        0 === e.indexOf("#") && (e = e.substr(1), s = !1);
                        for (var f = 0,
                                 p = e.split("|"), $ = p.length, d = p[f++]; $ > f; f++) d = k(d, p[f]);
                        e = (s ? "=": "=#") + d
                    } else e = r.helpers[t] ? "=#" + t + "(" + n.join(",") + ");": "=" + e
            }
            return e
        },
            "function" == typeof define ? define(function() {
                return r
            }) : "undefined" != typeof exports ? module.exports = r: this.template = r
    } ();
/*!js/librarys/validator.js*/
;~
    function(t) {
        var e, n, r, a, i, u, o, c, l, s, h, f, d, p, m = [];
        e = {
            email: function(t) {
                return /^(?:[a-z0-9]+[_\-+.]+)*[a-z0-9]+@(?:([a-z0-9]+-?)*[a-z0-9]+.)+([a-z]{2,})+$/i.test(t)
            },
            date: function(t) {
                var e, n, r, a, i, u = /^([1-2]\d{3})([-/.]) ? (1[0 - 2] | 0 ? [1 - 9])([ - /.])?([1-2]\d|3[01]|0?[1-9])$/;
                return u.test(t) ? (e = u.exec(t), r = +e[1], a = +e[3] - 1, i = +e[5], n = new Date(r, a, i), r === n.getFullYear() && a === n.getMonth() && i === n.getDate()) : !1
            },
            mobile: function(t) {
                return /^1[3-9]\d{9}$/.test(t)
            },
            tel: function(t) {
                return /^(?:(?:0\d{2,3}[- ]?[1-9]\d{6,7})|(?:[48]00[- ]?[1-9]\d{6}))$/.test(t)
            },
            number: function(e) {
                var n = t.trim(this.$item.attr("min")),
                    r = t.trim(this.$item.attr("max")),
                    a = /^\-?(?:[1-9]\d*|0)(?:[.]\d+)?$/.test(e),
                    i = +e,
                    u = t.trim(this.$item.attr("step"));
                return n = "" === n || isNaN(n) ? i - 1 : +n,
                    r = "" === r || isNaN(r) ? i + 1 : +r,
                    u = "" === u || isNaN(u) ? 0 : +u,
                a && (0 >= u ? i >= n && r >= i: 0 === (i + n) % u && i >= n && r >= i)
            },
            range: function(t) {
                return this.number(t)
            },
            url: function() {
                var t = "((https?|s?ftp|irc[6s]?|git|afp|telnet|smb):\\/\\/)?",
                    e = "([a-z0-9]\\w*(\\:[\\S]+)?\\@)?",
                    n = "(?:localhost|(?:[a-z0-9]+(?:[-\\w]*[a-z0-9])?(?:\\.[a-z0-9][-\\w]*[a-z0-9])*)*\\.[a-z]{2,})",
                    r = "(:\\d{1,5})?",
                    a = "\\d{1,3}\\.\\d{1,3}\\.\\d{1,3}\\.\\d{1,3}",
                    i = "(\\/\\S*)?",
                    u = [t, e, n, r, i],
                    o = [t, e, a, r, i],
                    c = new RegExp("^" + u.join("") + "$", "i"),
                    l = new RegExp("^" + o.join("") + "$", "i");
                return function(t) {
                    return c.test(t) || l.test(t)
                }
            } (), password: function(t) {
                return this.text(t)
            },
            checkbox: function() {
                return e._checker("checkbox")
            },
            radio: function() {
                return e._checker("radio")
            },
            _checker: function(e) {
                var n = this.$item.parents("form").eq(0),
                    r = "input[type=" + e + '][name="' + this.$item.attr("name") + '"]',
                    a = !1,
                    i = t(r, n);
                return i.each(function(t, e) {
                    return e.checked && !a ? a = !0 : void 0
                }),
                    a
            },
            text: function(e) {
                if ((e = t.trim(e)).length) {
                    var n, r = parseInt(this.$item.attr("maxlength"), 10),
                        a = parseInt(this.$item.attr("minlength"), 10);
                    return (n = function() {
                        var t = !0,
                            n = e.length;
                        return a && (t = n >= a),
                        r && (t = t && r >= n),
                            t
                    })()
                }
            }
        },
            h = function(e, n, r) {
                var a = e.data(),
                    i = a.url,
                    u = a.method || "get",
                    o = a.key || "key",
                    c = f(e),
                    l = {};
                l[o] = c,
                    t[u](i, l).success(function(t) {
                        var a = t ? "IM VALIDED": "unvalid";
                        return p.call(this, e, n, r, a)
                    }).error(function() {})
            },
            d = function(e, n, r) {
                var a = "a" === e.data("aorb") ? "b": "a",
                    u = t("[data-aorb=" + a + "]", e.parents("form").eq(0)),
                    o = [e, n, r],
                    c = [u, n, r],
                    l = 0;
                return l += p.apply(this, o) ? 0 : 1,
                    l += p.apply(this, c) ? 0 : 1,
                    l = l > 0 ? (i.apply(this, o), i.apply(this, c), !1) : p.apply(this, o.concat("unvalid"))
            },
            p = function(n, r, u, o) {
                if (!n) return "DONT VALIDATE UNEXIST ELEMENT";
                var c, l, s, h, d;
                return c = n.attr("pattern"),
                c && c.replace("\\", "\\\\"),
                    l = n.attr("type") || "text",
                    l = e[l] ? l: "text",
                    s = t.trim(f(n)),
                    d = n.data("event"),
                    o = o ? o: c ? new RegExp(c).test(s) || "unvalid": e[l](s) || "unvalid",
                "unvalid" === o && i(n, r, u),
                    /^(?:unvalid|empty)$/.test(o) ? (h = {
                        $el: a.call(this, n, r, u, o),
                        type: l,
                        error: o
                    },
                        n.trigger("after:" + d, n), h) : (i.call(this, n, r, u), n.trigger("after:" + d, n), !1)
            },
            n = function(e, n) {
                return t(e, n)
            },
            f = function(t) {
                return t.val() || (t.is("[contenteditable]") ? t.text() : "")
            },
            c = function(t, n, r) {
                var a, i, u, o, c, l;
                return e.$item = t,
                    u = t.attr("type"),
                    o = f(t),
                    a = t.data("url"),
                    i = t.data("aorb"),
                    l = t.data("event"),
                    c = [t, n, r],
                l && t.trigger("before:" + l, t),
                    /^(?:radio|checkbox)$/.test(u) || i || e.text(o) ? i ? d.apply(this, c) : a ? h.apply(this, c) : p.call(this, t, n, r) : p.call(this, t, n, r, o.length ? "unvalid": "empty")
            },
            l = function(e, n, r, a) {
                var i, u = /^radio|checkbox/;
                t.each(e,
                    function(e, o) {
                        t(o).on(u.test(o.type) || "SELECT" === o.tagName ? "change blur": n,
                            function() {
                                var e = t(this);
                                u.test(this.type) && (e = t("input[type=" + this.type + "][name=" + this.name + "]", e.closest("form"))),
                                    e.each(function() { (i = c.call(this, t(this), r, a)) && m.push(i)
                                    })
                            })
                    })
            },
            o = function(e, n, r, a) {
                return n && !l.length ? !0 : (m = t.map(e,
                    function(e) {
                        var n = c.call(null, t(e), r, a);
                        return n ? n: void 0
                    }), l.length ? m: !1)
            },
            s = function(e) {
                var n, r;
                return (n = t.grep(m,
                    function(t) {
                        return t.$el = e
                    })[0]) ? (r = t.inArray(n, m), m.splice(r, 1), m) : void 0
            },
            r = function(t, e) {
                return t.data("parent") ? t.closest(t.data("parent")) : e ? t.parent() : t
            },
            a = function(t, e, n, a) {
                return r(t, n).addClass(e + " " + a)
            },
            i = function(t, e, n) {
                return s.call(this, t),
                    r(t, n).removeClass(e + " empty unvalid")
            },
            u = function(t) {
                return t.attr("novalidate") || t.attr("novalidate", "true")
            },
            t.fn.validator = function(e) {
                var r = this,
                    a = e || {},
                    c = a.identifie || "[required]",
                    s = a.error || "error",
                    h = a.isErrorOnParent || !1,
                    f = a.method || "blur",
                    d = a.before ||
                        function() {
                            return ! 0
                        },
                    p = a.after ||
                        function() {
                            return ! 0
                        },
                    v = a.errorCallback ||
                        function() {},
                    g = n(c, r);
                u(r),
                f && l.call(this, g, f, s, h),
                    r.on("focusin", c,
                        function() {
                            i.call(this, t(this), "error unvalid empty", h)
                        }),
                    r.on("submit",
                        function(t) {
                            return d.call(this, g),
                                o.call(this, g, f, s, h),
                                m.length ? (t.preventDefault(), v.call(this, m)) : p.call(this, t, g)
                        })
            }
    } (window.jQuery || window.Zepto);
/*!js/librarys/ui/dialog.js*/
; !
    function(i) {
        "use strict";
        var o, e, n = {
            title: "",
            content: "",
            buttons: "",
            animate: !0,
            overlay: !0,
            showClose: !1,
            skin: "",
            zIndex: "",
            scroll: !0,
            onOpen: function() {},
            onClose: function() {},
            template: '<div class="ui-dialog <{skin}>" <{if zIndex}>style="z-index: <{zIndex}>;"<{/if}>><{if overlay}><div class="ui-dialog-mask"></div><{/if}><div class="ui-dialog-wrapper"><div class="ui-dialog-main"><{if showClose}><a href="javascript:;" data-role="close" class="ui-dialog-close" id="dialogClose"></a><{/if}><{if title}><div class="ui-border-b ui-dialog-title"><{title}></div><{/if}><div class="ui-dialog-content"><{#content}></div><{if buttons}><div class="ui-flexbox ui-btn-group ui-dialog-button"><{each buttons}><a href="<{if $value.href}><{$value.href}><{else}>javascript:;<{/if}>" data-role="button" data-index="<{$index}>" class="ui-flexbox-flex ui-border-t <{if $index}>ui-border-l<{/if}> <{if $value.autofocus}>select<{/if}> <{if $value.disabled}>disabled<{/if}>" id="<{if $value.id}><{$value.id}><{else}>dialogButton<{$index}><{/if}>"><{$value.value}></a><{/each}></div><{/if}></div></div></div>'
        };
        o = function(o) {
            var e = this;
            e.options = i.extend({},
                n, o),
                e.element = i(template.compile(e.options.template)(e.options)).appendTo("body"),
                e._bind(),
                e.open()
        },
            i.extend(o.prototype, {
                open: function() {
                    var o = this;
                    o.element.addClass("ui-dialog-show"),
                    o.options.animate && o.element.addClass("ui-dialog-anim ui-dialog-anim-bounce-in"),
                    i.isFunction(o.options.onOpen) && o.options.onOpen.call(o),
                    o.options.scroll && o.element.on("touchmove", o._stopScroll)
                },
                close: function() {
                    var o = this;
                    o.element.addClass("ui-dialog-anim-bounce-out").animationEnd(function() {
                        i(this).remove()
                    }),
                    i.isFunction(o.options.onClose) && o.options.onClose.call(o),
                        o.element.off("touchmove").end().find('[data-role="button"]').off("click").end().find('[data-role="close"]').off("click"),
                        o.element = o.options = null
                },
                _bind: function() {
                    var o = this;
                    o.element.find('[data-role="button"]').on("click",
                        function(e) {
                            var n = i(this).data("index"),
                                t = !0;
                            i.isFunction(o.options.buttons[n].callback) && !o.options.buttons[n].disabled && (t = o.options.buttons[n].callback.call(this)),
                            !o.options.buttons[n].disabled && t && o.close.call(o),
                                e.preventDefault()
                        }).end().find('[data-role="close"]').on("click",
                        function(i) {
                            o.close.call(o),
                                i.preventDefault()
                        })
                },
                _stopScroll: function(o) {
                    i(o.target).closest(".dialog-scroll").length || o.preventDefault()
                }
            }),
            e = function(i) {
                return new o(i)
            },
            "function" == typeof define ? define(function() {
                return e
            }) : i.dialog = e
    } (window.Zepto);
/*!js/librarys/ui/notice.js*/
; !
    function(t) {
        "use strict";
        var o, e, n = {
                container: "body",
                content: "",
                timeout: 3e3,
                anchor: "top",
                zIndex: "",
                offset: "20",
                type: "",
                template: '<div class="ui-notice ui-notice-<{type}>" <{if zIndex}>style="z-index: <{zIndex}>;"<{/if}>><div class="ui-notice-wrapper"><div class="ui-notice-icon"></div><div class="ui-notice-content"><{#content}></div></div></div>'
            },
            i = null;
        o = function(o) {
            var e = this;
            e.options = t.extend({},
                n, o),
                e.options.type = e.options.type && t.inArray(e.options.type, ["alert", "success", "error", "warn", "info"]) ? e.options.type: "alert",
                e.close().open()
        },
            t.extend(o.prototype, {
                open: function() {
                    var o = this,
                        e = {},
                        n = {};
                    return o.element = t(template.compile(o.options.template)(o.options)).appendTo(o.options.container),
                        e.opacity = 1,
                        "top" == o.options.anchor ? (n.top = 0, e.translate3d = "0, " + o.options.offset + "px, 0") : (n.bottom = 0, e.translate3d = "0, -" + o.options.offset + "px, 0"),
                        o.element.css(n).animate(e, 100),
                        o._timeOut.call(o),
                        o
                },
                close: function() {
                    var o = this,
                        e = {};
                    return i && clearTimeout(i),
                        e.opacity = 0,
                        e.translate3d = "top" === o.options.anchor ? "0, -" + o.options.offset + "px, 0": "0, " + o.options.offset + "px, 0",
                        t(".ui-notice").animate(e, {
                            duration: 100,
                            complete: function() {
                                t(this).remove()
                            }
                        }),
                        o
                },
                _timeOut: function() {
                    var o = this;
                    "number" == t.type(o.options.timeout) && (i = setTimeout(function() {
                            o.close()
                        },
                        o.options.timeout))
                }
            }),
            e = function(t) {
                return new o(t)
            },
            "function" == typeof define ? define(function() {
                return e
            }) : t.notice = e
    } (window.Zepto);
/*!js/librarys/basic.js*/
;~
    function() {
        "use strict";
        "undefined" != typeof template && $.isFunction(template) && (template.config("openTag", "<{"), template.config("closeTag", "}>"))
    } (),
    ~
        function() {
            "use strict";
            var n = 0,
                i = ["webkit", "moz"],
                t = 0;
            for (t; t < i.length && !window.requestAnimationFrame; ++t) window.requestAnimationFrame = window[i[t] + "RequestAnimationFrame"],
                window.cancelAnimationFrame = window[i[t] + "CancelAnimationFrame"] || window[i[t] + "CancelRequestAnimationFrame"];
            window.requestAnimationFrame || (window.requestAnimationFrame = function(i) {
                var t = (new Date).getTime(),
                    e = Math.max(0, 16.7 - (t - n)),
                    o = window.setTimeout(function() {
                            i(t + e)
                        },
                        e);
                return n = t + e,
                    o
            }),
            window.cancelAnimationFrame || (window.cancelAnimationFrame = function(n) {
                clearTimeout(n)
            })
        } (),
    ~
        function(n) {
            "use strict";
            n.fn.animationEnd = function(i) {
                return this.each(function() {
                    n(this).one(n.fx.animationEnd + " " + n.fx.transitionEnd,
                        function() {
                            n.isFunction(i) && i.call(this)
                        })
                })
            }
        } (Zepto);
/*!js/app/widget/main.js*/
;
/**
 * Created by hzh on 2015/7/21 0021.
 */
~
    function($) {
        var $doc = $(document);

        // 视图
        var view = function() {

            // 打开前回调
            var beforeopen = function() {
                var that = this;

                // 添加动画
                that.find('.spa-page-body').addClass('spa-page-animation');

                that.find('[data-target="route"]').on('click',
                    function() {
                        var type = $(this).data('type');

                        if (type == 'game') {
                            if (XWX.user.count == 0 && !XWX.isGetPrize) {
                                XWX.dialog.share.call(that)
                            } else {

                                if (XWX.isGetPrize) {
                                    //$.spa.navigate({
                                    //    hash: 'address'
                                    //})
                                    location.href = 'http://www.huogou.com/';

                                    return;
                                }
                                $.spa.navigate({
                                    hash: 'game'
                                })
                            }
                        }
                    }).end().find('[data-target="panel"]').on('click',
                    function() {
                        var $this = $(this),
                            type = $this.data('type');

                        if (type == 'follow') {
                            XWX.dialog.follow.call(that, $this)
                        }
                    });

                /*
                 *
                 * 进入页面重置次数
                 *
                 * */
                that.find('[data-target="include"]').html(template('widget/heart', $.extend(true, {},
                    XWX, {
                        user: {
                            count: function() {
                                var arr = [],
                                    i = 0;

                                for (i; i < XWX.user.count; i++) {
                                    arr.push(i)
                                }
                                return arr
                            } ()
                        }
                    })))
            };
            // 关闭前回调
            var beforeclose = function() {
                this.find('.spa-page-body').removeClass('spa-page-animation').end().find('[data-target="route"]').off('click').end().find('[data-target="panel"]').off('click')
            };

            $doc.trigger('spa:initpage', [this, {
                body: template('widget/main', XWX),
                beforeopen: beforeopen,
                beforeclose: beforeclose
            }])
        };

        // 面板
        $.spa.addRoute({
            route: '',
            classname: 'main',
            animate: 'pushInLeft',
            view: view
        })
    } (Zepto);
/*!js/app/widget/game.js*/
;
/**
 * Created by hzh on 2015/7/21 0021.
 */
~
    function($) {
        var $doc = $(document);

        // 视图
        var view = function() {
            //var pageData = $.spa.getViewData(this);
            XWX.game.data = ['stone', 'scissors', 'paper'];
            XWX.game.level = ['一', '二', '三', '四', '五', '六', '七', '八', '九', '十'];

            // 猜拳
            var roshambo = function(time, count, data) {
                var that = this,
                    canvas = that.find('.canvas'),
                    user = canvas.find('.user .trick .img'),
                    system = canvas.find('.system .trick .img');

                user.removeClass(XWX.game.data.join(' '));
                system.removeClass(XWX.game.data.join(' '));

                if (count == 0) {
                    user.addClass(XWX.game.roshambo);
                    system.addClass(data.roshambo);
                    setTimeout(function() {
                        // 完成通知
                        $doc.trigger('game:roshambo', data);
                    }, 1000);
                    return;
                }

                user.addClass(XWX.game.data[count % 3]);
                system.addClass(XWX.game.data[count % 3]);
                count--;

                time += 15;
                setTimeout(function() {
                        roshambo.call(that, time, count, data)
                    },
                    time)
            };



            // 打开前回调
            var beforeopen = function() {
                var that = this,
                    isRoshambo = false;

                //$(window).on('popstate', function () {
                //    console.log(this)
                //})
                // 添加动画
                that.find('.spa-page-body').addClass('spa-page-animation');


                that.find('[data-target="include"]').html(template('widget/heart', $.extend(true, {},
                    XWX, {
                        user: {
                            count: function() {
                                var arr = [],
                                    i = 0;

                                for (i; i < XWX.user.count; i++) {
                                    arr.push(i)
                                }
                                return arr
                            } ()
                        }
                    })))

                function GetRandomNum(Min,Max) {
                    var Range = Max - Min;
                    var Rand = Math.random();
                    return(Min + Math.round(Rand * Range));
                }
                var loadingFace = true;
                $('.spa-page-game .ui-btn-group .ui-btn img').attr('src','img/btn-pk1.png');
                setTimeout(function() {
                    var faceNum = 10;
                    var randNum = GetRandomNum(1,faceNum);
                    var faceUrl = 'img/face/'+randNum+'.jpg';
                    var faceName = [
                        '','bbb','ccc','ddd','eee',
                        'fff','eee','ttt','ggg','jjj','kkk',
                    ];
                    $('.canvas .system .img img').attr('src',faceUrl);
                    $('.canvas .system .name').text(faceName[randNum]);
                    loadingFace = false;
                    $('.spa-page-game .ui-btn-group .ui-btn img').attr('src','img/btn-pk.png');
                },GetRandomNum(3000,7000));


                that.on('click', '[data-target="roshambo"]',
                    function() {
                        var $elem = $(this);

                        that.find('.trick .ui-flexbox-flex').removeClass('selected');
                        $elem.closest('.ui-flexbox-flex').addClass('selected');
                        // 出拳类型
                        XWX.game.roshambo = $elem.data('type')
                    })
                    // PK
                    .on('click', '[data-target="pk"]',
                        function() {

                            // 上一轮未完成跳出
                            if (isRoshambo) {
                                return;
                            }

                            // 手势选择提醒
                            if (!XWX.game.roshambo) {
                                $.notice({
                                    type: 'warn',
                                    content: '请先选择手势'
                                });

                                return;
                            }

                            if (loadingFace) {
                                $('.spa-page-game .ui-btn-group .ui-btn img').attr('src','img/btn-pk1.png');
                                return;
                            }



                            function gameResult(s) {
                                if (XWX.user.count<=0) {
                                    return 4;
                                }
                                var u = XWX.game.roshambo;
                                if (u==s) {
                                    return 3;
                                } else {
                                    if ((u=='paper' && s=='stone')|| (u=='stone' && s=='scissors') || (u=='scissors' && s=='paper')) {
                                        if (XWX.user.level>=3) {
                                            return 5;
                                        } else {
                                            return 1;
                                        }
                                    } else {
                                        return 2;
                                    }
                                }

                            }

                            function getWinOrFail(s,win) {
                                if (s=='paper') {
                                    return win ?  'stone': 'scissors';
                                } else if (s=='scissors') {
                                    return win ? 'paper': 'stone' ;
                                } else if (s=='stone') {
                                    return win ? 'scissors': 'paper' ;
                                }
                            }

                            function getGameRoshambo() {
                                  if (XWX.game.times==1) {
                                      return getWinOrFail(XWX.game.roshambo,true);
                                  } else if (XWX.game.times==2) {
                                      return getWinOrFail(XWX.game.roshambo,false);
                                  } else if (XWX.game.times==3) {
                                      return getWinOrFail(XWX.game.roshambo,true);
                                  } else if (XWX.game.times==4) {
                                      return getWinOrFail(XWX.game.roshambo,true);
                                  } else {
                                      return getWinOrFail(XWX.game.roshambo,true);
                                  }
                            }

                            if (typeof XWX.game.times != 'undefined') {
                                XWX.game.times += 1;
                            } else {
                                XWX.game.times = 1;
                            }
                            isRoshambo = true;
                            $.spa.openLoader();
                            if (XWX.user.count<=0) {
                                $.spa.closeLoader();
                                var data = {};
                                data.roshambo = getGameRoshambo();
                                data.status = gameResult(data.roshambo);
                                $doc.trigger('game:roshambo', data);
                                return;
                            }
                            setTimeout(function() {
                                $.spa.closeLoader();
                                var data = {};
                                //var i = GetRandomNum(0,2);
                                //data.roshambo = XWX.game.data[i];
                                //data.status = gameResult(data.roshambo);
                                data.roshambo = getGameRoshambo();
                                data.status = gameResult(data.roshambo);
                                that.find('.trick .img i').hide();
                                roshambo.call(that, 100, 10, data);
                                isRoshambo = false;
                                loadingFace = true;
                            },GetRandomNum(2000,3000));

                        });

                $doc.on('game:roshambo',
                    function(e, data) {
                        $.dialog({
                            skin: 'spa-page-result spa-page-result' + data.status + (XWX.user.level == 10 ? ' spa-page-result-level': ''),
                            content: template('widget/result', $.extend({},
                                XWX, {
                                    result: data
                                })),
                            onOpen: function() {
                                var dialog = this,
                                    $element = $(dialog.element);

                                $element.find('[data-target="game"]').on('click',
                                    function() {
                                        var type = $(this).data('type');

                                        if (type == 'goon') {

                                            if (XWX.user.level > 10) {
                                                $.spa.navigate({
                                                    hash: ''
                                                });
                                                return;
                                            }
                                            XWX.user.count -= 1;
                                            setCookie('playNum',XWX.user.count);
                                            // 失败的时候
                                            if (data.status == 2) {
                                                //XWX.user.level = 1;
                                                XWX.game.roshambo = false;
                                            }
                                            // 平局不跳关卡
                                            if (data.status != 3 && data.status !=2) {
                                                XWX.user.level += 1;
                                                setCookie('playLevel',XWX.user.level);
                                            }

                                            if (XWX.isPrize) {
                                                XWX.isPrize = 0;
                                            }
                                            $.spa.navigate({
                                                hash: 'game/' + Date.now()
                                            })
                                        }
                                        if (type=='share') {
                                            $('.m_popUp').show();
                                        }

                                        if (type == 'address') {
                                            //$.spa.navigate({
                                            //    hash: 'address'
                                            //})
                                            location.href = 'http://www.huogou.com/';
                                            return;
                                        }

                                        if (type == 'main') {

                                            //// 失败的时候
                                            //if (data.status == 2) {
                                            //    XWX.user.count -= 1;
                                            //    //XWX.user.level = 1;
                                            //    XWX.game.roshambo = false;
                                            //}

                                            $.spa.navigate({
                                                hash: ''
                                            })
                                        }

                                        // remove dialog
                                        $element.remove()
                                    });
                            },
                            onClose: function() {
                                var dialog = this;

                                $(dialog.element).find('[data-target="game"]').off('click');
                                XWX.game.roshambo = false
                            }
                        })
                    });

            };

            var afteropen = function() {
                // 是否存在奖品关卡
                if (XWX.isPrize) {
                    $doc.trigger('game:roshambo', {
                        status: 5
                    });
                }
            }

            // 关闭前回调
            var beforeclose = function() {
                this.find('.spa-page-body').removeClass('spa-page-animation').end().off('click');

                $doc.off('game:roshambo')
                //.trigger('spa:viewcache')
            };

            $doc.trigger('spa:initpage', [this, {
                body: template('widget/game', XWX),
                beforeopen: beforeopen,
                afteropen: afteropen,
                beforeclose: beforeclose
            }]);

        };

        // 路由
        $.spa.addRoute({
            route: 'game(/:num)',
            nocache: true,
            animate: 'pushInLeft',
            classname: 'game',
            view: view
        })
    } (Zepto);
/*!js/app/widget/description.js*/
;
/**
 * Created by hzh on 2015/7/22 0022.
 */
~
    function($) {
        var $doc = $(document);

        // 视图
        var view = function() {

            // 打开前回调
            var beforeopen = function() {
                var that = this;

                // 添加动画
                that.find('.spa-page-body').addClass('spa-page-animation');

                that.find('[data-target="route"]').on('click',
                    function() {
                        var type = $(this).data('type');

                        if (type == 'game') {
                            if (XWX.user.count == 0 && !XWX.isGetPrize) {
                                XWX.dialog.share.call(that)
                            } else {

                                if (XWX.isGetPrize) {
                                    $.spa.navigate({
                                        hash: 'address'
                                    })

                                    return;
                                }
                                $.spa.navigate({
                                    hash: 'game'
                                })
                            }
                        }
                    }).end().find('[data-target="panel"]').on('click',
                    function() {
                        var $this = $(this),
                            type = $this.data('type');

                        if (type == 'follow') {
                            XWX.dialog.follow.call(that, $this)
                        }
                    })
            };
            // 关闭前回调
            var beforeclose = function() {
                this.find('.spa-page-body').removeClass('spa-page-animation').end().find('[data-target="route"]').off('click').end().find('[data-target="panel"]').off('click')
            };

            $doc.trigger('spa:initpage', [this, {
                body: template('widget/description', XWX),
                beforeopen: beforeopen,
                beforeclose: beforeclose
            }])
        };

        // 面板
        $.spa.addRoute({
            route: 'desc',
            classname: 'description',
            animate: 'pushInLeft',
            view: view
        })
    } (Zepto);
/*!js/app/widget/address.js*/
;
/**
 * Created by hzh on 2015/7/22 0022.
 */
~
    function($) {
        var $doc = $(document);

        // view
        var view = function() {

            // 打开前回调
            var beforeopen = function() {
                var that = this,
                    isSubmit = false;

                // 添加动画
                that.find('.spa-page-body').addClass('spa-page-animation');

                that.find('form').validator({
                    isErrorOnParent: true,
                    errorCallback: function(items) {
                        $.each(items,
                            function(index, item) {
                                $.notice({
                                    type: 'warn',
                                    content: item.$el.prevObject.data(item.error)
                                });
                                return false
                            })
                    },
                    after: function() {

                        if (isSubmit || XWX.isGetPrize) {
                            $.notice({
                                type: 'warn',
                                content: '请勿重复提交'
                            });

                            return;
                        }

                        $.spa.openLoader();
                        isSubmit = true;

                        // 异步提交
                        $.ajax({
                            url: XWX.api.post,
                            type: 'post',
                            data: {
                                name: that.find('form [name="name"]').val(),
                                mobile: that.find('form [name="mobile"]').val(),
                                openid: XWX.user.openid,
                                level: XWX.user.level,
                                activityId: XWX.activityId,
                                Nickname: XWX.user.username
                            },
                            dataType: 'json'
                        }).done(function(data) {
                            $.spa.closeLoader();

                            if (data.status == 0) {
                                $.notice({
                                    type: 'warn',
                                    content: data.msg
                                });

                                return;
                            }

                            // show
                            //that.find('.post-success').addClass('running');
                            $.dialog({
                                skin: 'spa-page-result spa-page-result-level',
                                content: template('widget/result', $.extend({},
                                    XWX, {
                                        result: {
                                            status: 6
                                        }
                                    })),
                                onOpen: function() {
                                    var dialog = this;

                                    $(dialog.element).find('[data-target="game"]').on('click',
                                        function() {
                                            var type = $(this).data('type');

                                            if (type == 'main') {
                                                XWX.isGetPrize = 1;
                                                $.spa.navigate({
                                                    hash: ''
                                                })
                                            }

                                            if (type == 'close') {
                                                wx.closeWindow()
                                            }


                                            if (type=='share') {
                                                $('.m_popUp').show();
                                            }

                                            dialog.close()
                                        });
                                },
                                onClose: function() {
                                    var dialog = this;

                                    $(dialog.element).find('[data-target="game"]').off('click');
                                }
                            });

                            isSubmit = false
                        }).fail(function() {
                            $.spa.closeLoader();
                            $.notice({
                                type: 'error',
                                content: '请检查网络'
                            });

                            isSubmit = false
                        })

                        // 阻止提交
                        return false
                    }
                })
            };

            // 打开后回调
            var afteropen = function() {
                var that = this;

                that.find('.ui-btn').on('click',
                    function() {
                        that.find('form').trigger('submit')
                    }).end().find('.input').on('focus',
                    function() {
                        $.spa.addElScroll(that, {
                            direction: 'y'
                        });
                        $doc.off('touchmove')
                    }).on('blur',
                    function() {
                        $doc.on('touchmove',
                            function(e) {
                                e.preventDefault()
                            })
                    });

                // 未完成返回首页
                if (XWX.isGetPrize == 0 && $.inArray(XWX.user.level, XWX.prizes) == '-1') {
                    setTimeout(function() {
                            $.spa.navigate({
                                hash: ''
                            });
                        },
                        1);
                }
            };

            // 关闭前回调
            var beforeclose = function() {
                this.find('.spa-page-body').removeClass('spa-page-animation').end().find('.ui-btn').off('click').end().find('.input').off('focus blur')
            };
            // spa:initpage
            $doc.trigger('spa:initpage', [this, {
                body: template('widget/address', XWX),
                beforeopen: beforeopen,
                afteropen: afteropen,
                beforeclose: beforeclose
            }])
        };
        // 面板
        $.spa.addRoute({
            route: 'address',
            classname: 'address',
            animate: 'pushInLeft',
            view: view
        })
    } (Zepto);

/*!js/app/boot.js*/
;
/**
 * Created by hzh on 2015/05/25.
 */
~
    function($) {

        // dialog
        XWX.dialog = {
            follow: function() {
                $.dialog({
                    skin: 'spa-page-follow',
                    showClose: true,
                    content: template('widget/follow', XWX)
                })
            },
            share: function() {
                $.dialog({
                    skin: 'spa-page-result spa-page-result4',
                    content: template('widget/result', $.extend({},
                        XWX, {
                            result: {
                                status: 4
                            }
                        })),
                    onOpen: function() {
                        var dialog = this;

                        $(dialog.element).find('[data-target="game"]').on('click',
                            function() {
                                var type = $(this).data('type');

                                if (type == 'main') {
                                    $.spa.navigate({
                                        hash: ''
                                    })
                                }

                                if (type=='share') {
                                    $('.m_popUp').show();
                                }
                                dialog.close()
                            });
                    },
                    onClose: function() {
                        var dialog = this;

                        $(dialog.element).find('[data-target="game"]').off('click')
                    }
                })
            }
        };

        // 方法
        XWX.func = {
            // 分享
            share: function(data) {
                var _temp = $.extend(true, {},
                    XWX.wechat.share, data, {
                        link: location.href.split('#!')[0],
                        // 用户确认分享后执行的回调函数
                        success: function() {
                            //alert(_temp.link)
                            $.ajax({
                                url: XWX.api.share,
                                type: 'post',
                                data: {
                                    openid: XWX.user.openid,
                                    activityId: XWX.activityId
                                },
                                dataType: 'json'
                            }).done(function(data) {
                                XWX.user.count = data.count;
                                $('[data-target="include"]').html(template('widget/heart', $.extend(true, {},
                                    XWX, {
                                        user: {
                                            count: function() {
                                                var arr = [],
                                                    i = 0;

                                                for (i; i < XWX.user.count; i++) {
                                                    arr.push(i)
                                                }
                                                return arr
                                            } ()
                                        }
                                    })))
                            })
                        }
                    });

                wx.ready(function() {
                    wx.onMenuShareTimeline(_temp);
                    wx.onMenuShareAppMessage(_temp);
                    wx.onMenuShareQQ(_temp);
                    wx.onMenuShareWeibo(_temp)
                })
            }
        };
        //
        //template.helper('level', function (prizes, level) {
        //    $.each(prizes, function (item) {
        //        console.log(item)
        //    })
        //});
    } (Zepto)

~
    function($) {
        var
        /*_isInstance = false,*/
            _scrs = [
                // main
                '../img/main-bg.png?t=1438061098850', '../img/main-title.png?t=1438061098850', '../img/heart.png?t=1438061098850', '../img/pnc-xiaoming.png?t=1438061098850', '../img/pnc-jingjing.png?t=1438061098850', '../img/btn-begin.png?t=1438061098850', '../img/btn-description.png?t=1438061098850', '../img/btn-join.png?t=1438061098850', '../img/btn-post.png?t=1438061098850',
                // game
                '../img/game-bg.png?t=1438061098850', '../img/game-paper.png?t=1438061098850', '../img/game-scissors.png?t=1438061098850', '../img/game-stone.png?t=1438061098850',
                //__uri('../img/game-system-avatar.png'),
                '../img/game-vs.png?t=1438061098850', '../img/game-canvas-trick.png?t=1438061098850', '../img/game-roshambo-trick.png?t=1438061098850', '../img/btn-result.png?t=1438061098850', '../img/btn-result2.png?t=1438061098850', '../img/btn-result3.png?t=1438061098850', '../img/game-result.png?t=1438061098850', '../img/game-result2.png?t=1438061098850', '../img/game-result-heart.png?t=1438061098850', '../img/game-result-crown.png?t=1438061098850', '../img/game-result-crown3.png?t=1438061098850',
                // description
                '../img/description-panel.png?t=1438061098850',
                // address
                '../img/address-panel.png?t=1438061098850', '../img/address-prizel.png?t=1438061098850',
                // follow
                '../img/follow-qr.png?t=1438061098850', '../img/dialog-close.png?t=1438061098850',
                // ui
                //__uri('../img/ui/loading-sprite.png'),
                '../img/ui/notice-error.png?t=1438061098850', '../img/ui/notice-success.png?t=1438061098850', '../img/ui/notice-warn.png?t=1438061098850'];

        $.preLoader(_scrs, {
            //pipeline: true,
            onProgress: function() {
                $('.loading-progress-bar').width(Math.floor((100 / this.queue.length) * this.completed.length) + '%');
                //if (!_isInstance && progress >= 90) {
                //    $.spa.setViewCacheCount({count: 2});
                //    $.spa.fullScreen();
                //    $.spa.boot(/*{
                //        callback: function () {
                //
                //        }
                //    }*/);
                //    $.spa.navigate({hash: ''});
                //    _isInstance = true
                //}
                //$('body').append('<img src="'+ this.completed[this.completed.length - 1] +'" width="0" height="0">');
            },
            onComplete: function() {
                $.spa.setViewCacheCount({
                    count: 2
                });
                $.spa.fullScreen();
                $.spa.boot(
                    /*{
                     callback: function () {

                     }
                     }*/
                );

                // 初始化必须返回首页
                if ($.spa.getHash() != '') {
                    $('.ui-dialog').remove();
                    $.spa.navigate({
                        hash: ''
                    });
                }

                // 分享
                XWX.func.share();

                $('.loading').addClass('loading-done').animationEnd(function() {
                    $(this).remove()
                })
            }
        });

        FastClick.attach(document.body);

        $(document).on('touchmove',
            function(e) {
                e.preventDefault()
            });

        //alert(location.href)
        // 调试使用
        //if (window.navigator.userAgent.toLowerCase().match(/MicroMessenger/i) != 'micromessenger') {
        //    $('head').append('<style>.spa-page {top: '+ (128 / 32) +'rem}</style>')
        //}
    } (Zepto);