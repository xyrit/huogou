var Zepto = function() {
    function a(a) {
        return null == a ? String(a) : U[V.call(a)] || "object"
    }
    function b(b) {
        return "function" == a(b)
    }
    function c(a) {
        return null != a && a == a.window
    }
    function d(a) {
        return null != a && a.nodeType == a.DOCUMENT_NODE
    }
    function e(b) {
        return "object" == a(b)
    }
    function f(a) {
        return e(a) && !c(a) && Object.getPrototypeOf(a) == Object.prototype
    }
    function g(a) {
        return "number" == typeof a.length
    }
    function h(a) {
        return D.call(a,
            function(a) {
                return null != a
            })
    }
    function i(a) {
        return a.length > 0 ? x.fn.concat.apply([], a) : a
    }
    function j(a) {
        return a.replace(/::/g, "/").replace(/([A-Z]+)([A-Z][a-z])/g, "$1_$2").replace(/([a-z\d])([A-Z])/g, "$1_$2").replace(/_/g, "-").toLowerCase()
    }
    function k(a) {
        return a in G ? G[a] : G[a] = new RegExp("(^|\\s)" + a + "(\\s|$)")
    }
    function l(a, b) {
        return "number" != typeof b || H[j(a)] ? b: b + "px"
    }
    function m(a) {
        var b, c;
        return F[a] || (b = E.createElement(a), E.body.appendChild(b), c = getComputedStyle(b, "").getPropertyValue("display"), b.parentNode.removeChild(b), "none" == c && (c = "block"), F[a] = c),
            F[a]
    }
    function n(a) {
        return "children" in a ? C.call(a.children) : x.map(a.childNodes,
            function(a) {
                return 1 == a.nodeType ? a: void 0
            })
    }
    function o(a, b, c) {
        for (w in b) c && (f(b[w]) || Z(b[w])) ? (f(b[w]) && !f(a[w]) && (a[w] = {}), Z(b[w]) && !Z(a[w]) && (a[w] = []), o(a[w], b[w], c)) : b[w] !== v && (a[w] = b[w])
    }
    function p(a, b) {
        return null == b ? x(a) : x(a).filter(b)
    }
    function q(a, c, d, e) {
        return b(c) ? c.call(a, d, e) : c
    }
    function r(a, b, c) {
        null == c ? a.removeAttribute(b) : a.setAttribute(b, c)
    }
    function s(a, b) {
        var c = a.className || "",
            d = c && c.baseVal !== v;
        return b === v ? d ? c.baseVal: c: void(d ? c.baseVal = b: a.className = b)
    }
    function t(a) {
        try {
            return a ? "true" == a || ("false" == a ? !1 : "null" == a ? null: +a + "" == a ? +a: /^[\[\{]/.test(a) ? x.parseJSON(a) : a) : a
        } catch(b) {
            return a
        }
    }
    function u(a, b) {
        b(a);
        for (var c = 0,
                 d = a.childNodes.length; d > c; c++) u(a.childNodes[c], b)
    }
    var v, w, x, y, z, A, B = [],
        C = B.slice,
        D = B.filter,
        E = window.document,
        F = {},
        G = {},
        H = {
            "column-count": 1,
            columns: 1,
            "font-weight": 1,
            "line-height": 1,
            opacity: 1,
            "z-index": 1,
            zoom: 1
        },
        I = /^\s*<(\w+|!)[^>]*>/,
        J = /^<(\w+)\s*\/?>(?:<\/\1>|)$/,
        K = /<(?!area|br|col|embed|hr|img|input|link|meta|param)(([\w:]+)[^>]*)\/>/gi,
        L = /^(?:body|html)$/i,
        M = /([A-Z])/g,
        N = ["val", "css", "html", "text", "data", "width", "height", "offset"],
        O = ["after", "prepend", "before", "append"],
        P = E.createElement("table"),
        Q = E.createElement("tr"),
        R = {
            tr: E.createElement("tbody"),
            tbody: P,
            thead: P,
            tfoot: P,
            td: Q,
            th: Q,
            "*": E.createElement("div")
        },
        S = /complete|loaded|interactive/,
        T = /^[\w-]*$/,
        U = {},
        V = U.toString,
        W = {},
        X = E.createElement("div"),
        Y = {
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
        Z = Array.isArray ||
            function(a) {
                return a instanceof Array
            };
    return W.matches = function(a, b) {
        var c, d, e, f;
        return b && a && 1 === a.nodeType ? (c = a.webkitMatchesSelector || a.mozMatchesSelector || a.oMatchesSelector || a.matchesSelector) ? c.call(a, b) : (e = a.parentNode, f = !e, f && (e = X).appendChild(a), d = ~W.qsa(e, b).indexOf(a), f && X.removeChild(a), d) : !1
    },
        z = function(a) {
            return a.replace(/-+(.)?/g,
                function(a, b) {
                    return b ? b.toUpperCase() : ""
                })
        },
        A = function(a) {
            return D.call(a,
                function(b, c) {
                    return a.indexOf(b) == c
                })
        },
        W.fragment = function(a, b, c) {
            var d, e, g;
            return J.test(a) && (d = x(E.createElement(RegExp.$1))),
            d || (a.replace && (a = a.replace(K, "<$1></$2>")), b === v && (b = I.test(a) && RegExp.$1), b in R || (b = "*"), g = R[b], g.innerHTML = "" + a, d = x.each(C.call(g.childNodes),
                function() {
                    g.removeChild(this)
                })),
            f(c) && (e = x(d), x.each(c,
                function(a, b) {
                    N.indexOf(a) > -1 ? e[a](b) : e.attr(a, b)
                })),
                d
        },
        W.Z = function(a, b) {
            return a = a || [],
                a.__proto__ = x.fn,
                a.selector = b || "",
                a
        },
        W.isZ = function(a) {
            return a instanceof W.Z
        },
        W.init = function(a, c) {
            var d;
            if (!a) return W.Z();
            if ("string" == typeof a) if (a = a.trim(), "<" == a[0] && I.test(a)) d = W.fragment(a, RegExp.$1, c),
                a = null;
            else {
                if (c !== v) return x(c).find(a);
                d = W.qsa(E, a)
            } else {
                if (b(a)) return x(E).ready(a);
                if (W.isZ(a)) return a;
                if (Z(a)) d = h(a);
                else if (e(a)) d = [a],
                    a = null;
                else if (I.test(a)) d = W.fragment(a.trim(), RegExp.$1, c),
                    a = null;
                else {
                    if (c !== v) return x(c).find(a);
                    d = W.qsa(E, a)
                }
            }
            return W.Z(d, a)
        },
        x = function(a, b) {
            return W.init(a, b)
        },
        x.extend = function(a) {
            var b, c = C.call(arguments, 1);
            return "boolean" == typeof a && (b = a, a = c.shift()),
                c.forEach(function(c) {
                    o(a, c, b)
                }),
                a
        },
        W.qsa = function(a, b) {
            var c, e = "#" == b[0],
                f = !e && "." == b[0],
                g = e || f ? b.slice(1) : b,
                h = T.test(g);
            return d(a) && h && e ? (c = a.getElementById(g)) ? [c] : [] : 1 !== a.nodeType && 9 !== a.nodeType ? [] : C.call(h && !e ? f ? a.getElementsByClassName(g) : a.getElementsByTagName(b) : a.querySelectorAll(b))
        },
        x.contains = E.documentElement.contains ?
            function(a, b) {
                return a !== b && a.contains(b)
            }: function(a, b) {
            for (; b && (b = b.parentNode);) if (b === a) return ! 0;
            return ! 1
        },
        x.type = a,
        x.isFunction = b,
        x.isWindow = c,
        x.isArray = Z,
        x.isPlainObject = f,
        x.isEmptyObject = function(a) {
            var b;
            for (b in a) return ! 1;
            return ! 0
        },
        x.inArray = function(a, b, c) {
            return B.indexOf.call(b, a, c)
        },
        x.camelCase = z,
        x.trim = function(a) {
            return null == a ? "": String.prototype.trim.call(a)
        },
        x.uuid = 0,
        x.support = {},
        x.expr = {},
        x.map = function(a, b) {
            var c, d, e, f = [];
            if (g(a)) for (d = 0; d < a.length; d++) c = b(a[d], d),
            null != c && f.push(c);
            else for (e in a) c = b(a[e], e),
            null != c && f.push(c);
            return i(f)
        },
        x.each = function(a, b) {
            var c, d;
            if (g(a)) {
                for (c = 0; c < a.length; c++) if (b.call(a[c], c, a[c]) === !1) return a
            } else for (d in a) if (b.call(a[d], d, a[d]) === !1) return a;
            return a
        },
        x.grep = function(a, b) {
            return D.call(a, b)
        },
    window.JSON && (x.parseJSON = JSON.parse),
        x.each("Boolean Number String Function Array Date RegExp Object Error".split(" "),
            function(a, b) {
                U["[object " + b + "]"] = b.toLowerCase()
            }),
        x.fn = {
            forEach: B.forEach,
            reduce: B.reduce,
            push: B.push,
            sort: B.sort,
            indexOf: B.indexOf,
            concat: B.concat,
            map: function(a) {
                return x(x.map(this,
                    function(b, c) {
                        return a.call(b, c, b)
                    }))
            },
            slice: function() {
                return x(C.apply(this, arguments))
            },
            ready: function(a) {
                return S.test(E.readyState) && E.body ? a(x) : E.addEventListener("DOMContentLoaded",
                    function() {
                        a(x)
                    },
                    !1),
                    this
            },
            get: function(a) {
                return a === v ? C.call(this) : this[a >= 0 ? a: a + this.length]
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
            each: function(a) {
                return B.every.call(this,
                    function(b, c) {
                        return a.call(b, c, b) !== !1
                    }),
                    this
            },
            filter: function(a) {
                return b(a) ? this.not(this.not(a)) : x(D.call(this,
                    function(b) {
                        return W.matches(b, a)
                    }))
            },
            add: function(a, b) {
                return x(A(this.concat(x(a, b))))
            },
            is: function(a) {
                return this.length > 0 && W.matches(this[0], a)
            },
            not: function(a) {
                var d, c = [];
                return b(a) && a.call !== v ? this.each(function(b) {
                    a.call(this, b) || c.push(this)
                }) : (d = "string" == typeof a ? this.filter(a) : g(a) && b(a.item) ? C.call(a) : x(a), this.forEach(function(a) {
                    d.indexOf(a) < 0 && c.push(a)
                })),
                    x(c)
            },
            has: function(a) {
                return this.filter(function() {
                    return e(a) ? x.contains(this, a) : x(this).find(a).size()
                })
            },
            eq: function(a) {
                return - 1 === a ? this.slice(a) : this.slice(a, +a + 1)
            },
            first: function() {
                var a = this[0];
                return a && !e(a) ? a: x(a)
            },
            last: function() {
                var a = this[this.length - 1];
                return a && !e(a) ? a: x(a)
            },
            find: function(a) {
                var b, c = this;
                return b = a ? "object" == typeof a ? x(a).filter(function() {
                    var a = this;
                    return B.some.call(c,
                        function(b) {
                            return x.contains(b, a)
                        })
                }) : 1 == this.length ? x(W.qsa(this[0], a)) : this.map(function() {
                    return W.qsa(this, a)
                }) : x()
            },
            closest: function(a, b) {
                var c = this[0],
                    e = !1;
                for ("object" == typeof a && (e = x(a)); c && !(e ? e.indexOf(c) >= 0 : W.matches(c, a));) c = c !== b && !d(c) && c.parentNode;
                return x(c)
            },
            parents: function(a) {
                for (var b = [], c = this; c.length > 0;) c = x.map(c,
                    function(a) {
                        return (a = a.parentNode) && !d(a) && b.indexOf(a) < 0 ? (b.push(a), a) : void 0
                    });
                return p(b, a)
            },
            parent: function(a) {
                return p(A(this.pluck("parentNode")), a)
            },
            children: function(a) {
                return p(this.map(function() {
                    return n(this)
                }), a)
            },
            contents: function() {
                return this.map(function() {
                    return C.call(this.childNodes)
                })
            },
            siblings: function(a) {
                return p(this.map(function(a, b) {
                    return D.call(n(b.parentNode),
                        function(a) {
                            return a !== b
                        })
                }), a)
            },
            empty: function() {
                return this.each(function() {
                    this.innerHTML = ""
                })
            },
            pluck: function(a) {
                return x.map(this,
                    function(b) {
                        return b[a]
                    })
            },
            show: function() {
                return this.each(function() {
                    "none" == this.style.display && (this.style.display = ""),
                    "none" == getComputedStyle(this, "").getPropertyValue("display") && (this.style.display = m(this.nodeName))
                })
            },
            replaceWith: function(a) {
                return this.before(a).remove()
            },
            wrap: function(a) {
                var d, e, c = b(a);
                return this[0] && !c && (d = x(a).get(0), e = d.parentNode || this.length > 1),
                    this.each(function(b) {
                        x(this).wrapAll(c ? a.call(this, b) : e ? d.cloneNode(!0) : d)
                    })
            },
            wrapAll: function(a) {
                if (this[0]) {
                    x(this[0]).before(a = x(a));
                    for (var b; (b = a.children()).length;) a = b.first();
                    x(a).append(this)
                }
                return this
            },
            wrapInner: function(a) {
                var c = b(a);
                return this.each(function(b) {
                    var d = x(this),
                        e = d.contents(),
                        f = c ? a.call(this, b) : a;
                    e.length ? e.wrapAll(f) : d.append(f)
                })
            },
            unwrap: function() {
                return this.parent().each(function() {
                    x(this).replaceWith(x(this).children())
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
            toggle: function(a) {
                return this.each(function() {
                    var b = x(this); (a === v ? "none" == b.css("display") : a) ? b.show() : b.hide()
                })
            },
            prev: function(a) {
                return x(this.pluck("previousElementSibling")).filter(a || "*")
            },
            next: function(a) {
                return x(this.pluck("nextElementSibling")).filter(a || "*")
            },
            html: function(a) {
                return 0 in arguments ? this.each(function(b) {
                    var c = this.innerHTML;
                    x(this).empty().append(q(this, a, b, c))
                }) : 0 in this ? this[0].innerHTML: null
            },
            text: function(a) {
                return 0 in arguments ? this.each(function(b) {
                    var c = q(this, a, b, this.textContent);
                    this.textContent = null == c ? "": "" + c
                }) : 0 in this ? this[0].textContent: null
            },
            attr: function(a, b) {
                var c;
                return "string" != typeof a || 1 in arguments ? this.each(function(c) {
                    if (1 === this.nodeType) if (e(a)) for (w in a) r(this, w, a[w]);
                    else r(this, a, q(this, b, c, this.getAttribute(a)))
                }) : this.length && 1 === this[0].nodeType ? !(c = this[0].getAttribute(a)) && a in this[0] ? this[0][a] : c: v
            },
            removeAttr: function(a) {
                return this.each(function() {
                    1 === this.nodeType && a.split(" ").forEach(function(a) {
                            r(this, a)
                        },
                        this)
                })
            },
            prop: function(a, b) {
                return a = Y[a] || a,
                    1 in arguments ? this.each(function(c) {
                        this[a] = q(this, b, c, this[a])
                    }) : this[0] && this[0][a]
            },
            data: function(a, b) {
                var c = "data-" + a.replace(M, "-$1").toLowerCase(),
                    d = 1 in arguments ? this.attr(c, b) : this.attr(c);
                return null !== d ? t(d) : v
            },
            val: function(a) {
                return 0 in arguments ? this.each(function(b) {
                    this.value = q(this, a, b, this.value)
                }) : this[0] && (this[0].multiple ? x(this[0]).find("option").filter(function() {
                    return this.selected
                }).pluck("value") : this[0].value)
            },
            offset: function(a) {
                if (a) return this.each(function(b) {
                    var c = x(this),
                        d = q(this, a, b, c.offset()),
                        e = c.offsetParent().offset(),
                        f = {
                            top: d.top - e.top,
                            left: d.left - e.left
                        };
                    "static" == c.css("position") && (f.position = "relative"),
                        c.css(f)
                });
                if (!this.length) return null;
                var b = this[0].getBoundingClientRect();
                return {
                    left: b.left + window.pageXOffset,
                    top: b.top + window.pageYOffset,
                    width: Math.round(b.width),
                    height: Math.round(b.height)
                }
            },
            css: function(b, c) {
                var d, e, f, g;
                if (arguments.length < 2) {
                    if (e = this[0], !e) return;
                    if (d = getComputedStyle(e, ""), "string" == typeof b) return e.style[z(b)] || d.getPropertyValue(b);
                    if (Z(b)) return f = {},
                        x.each(b,
                            function(a, b) {
                                f[b] = e.style[z(b)] || d.getPropertyValue(b)
                            }),
                        f
                }
                if (g = "", "string" == a(b)) c || 0 === c ? g = j(b) + ":" + l(b, c) : this.each(function() {
                    this.style.removeProperty(j(b))
                });
                else for (w in b) b[w] || 0 === b[w] ? g += j(w) + ":" + l(w, b[w]) + ";": this.each(function() {
                    this.style.removeProperty(j(w))
                });
                return this.each(function() {
                    this.style.cssText += ";" + g
                })
            },
            index: function(a) {
                return a ? this.indexOf(x(a)[0]) : this.parent().children().indexOf(this[0])
            },
            hasClass: function(a) {
                return a ? B.some.call(this,
                    function(a) {
                        return this.test(s(a))
                    },
                    k(a)) : !1
            },
            addClass: function(a) {
                return a ? this.each(function(b) {
                    if ("className" in this) {
                        y = [];
                        var c = s(this),
                            d = q(this, a, b, c);
                        d.split(/\s+/g).forEach(function(a) {
                                x(this).hasClass(a) || y.push(a)
                            },
                            this),
                        y.length && s(this, c + (c ? " ": "") + y.join(" "))
                    }
                }) : this
            },
            removeClass: function(a) {
                return this.each(function(b) {
                    if ("className" in this) {
                        if (a === v) return s(this, "");
                        y = s(this),
                            q(this, a, b, y).split(/\s+/g).forEach(function(a) {
                                y = y.replace(k(a), " ")
                            }),
                            s(this, y.trim())
                    }
                })
            },
            toggleClass: function(a, b) {
                return a ? this.each(function(c) {
                    var d = x(this),
                        e = q(this, a, c, s(this));
                    e.split(/\s+/g).forEach(function(a) { (b === v ? !d.hasClass(a) : b) ? d.addClass(a) : d.removeClass(a)
                    })
                }) : this
            },
            scrollTop: function(a) {
                if (this.length) {
                    var b = "scrollTop" in this[0];
                    return a === v ? b ? this[0].scrollTop: this[0].pageYOffset: this.each(b ?
                        function() {
                            this.scrollTop = a
                        }: function() {
                        this.scrollTo(this.scrollX, a)
                    })
                }
            },
            scrollLeft: function(a) {
                if (this.length) {
                    var b = "scrollLeft" in this[0];
                    return a === v ? b ? this[0].scrollLeft: this[0].pageXOffset: this.each(b ?
                        function() {
                            this.scrollLeft = a
                        }: function() {
                        this.scrollTo(a, this.scrollY)
                    })
                }
            },
            position: function() {
                if (this.length) {
                    var a = this[0],
                        b = this.offsetParent(),
                        c = this.offset(),
                        d = L.test(b[0].nodeName) ? {
                            top: 0,
                            left: 0
                        }: b.offset();
                    return c.top -= parseFloat(x(a).css("margin-top")) || 0,
                        c.left -= parseFloat(x(a).css("margin-left")) || 0,
                        d.top += parseFloat(x(b[0]).css("border-top-width")) || 0,
                        d.left += parseFloat(x(b[0]).css("border-left-width")) || 0,
                    {
                        top: c.top - d.top,
                        left: c.left - d.left
                    }
                }
            },
            offsetParent: function() {
                return this.map(function() {
                    for (var a = this.offsetParent || E.body; a && !L.test(a.nodeName) && "static" == x(a).css("position");) a = a.offsetParent;
                    return a
                })
            }
        },
        x.fn.detach = x.fn.remove,
        ["width", "height"].forEach(function(a) {
            var b = a.replace(/./,
                function(a) {
                    return a[0].toUpperCase()
                });
            x.fn[a] = function(e) {
                var f, g = this[0];
                return e === v ? c(g) ? g["inner" + b] : d(g) ? g.documentElement["scroll" + b] : (f = this.offset()) && f[a] : this.each(function(b) {
                    g = x(this),
                        g.css(a, q(this, e, b, g[a]()))
                })
            }
        }),
        O.forEach(function(b, c) {
            var d = c % 2;
            x.fn[b] = function() {
                var b, e, f = x.map(arguments,
                    function(c) {
                        return b = a(c),
                            "object" == b || "array" == b || null == c ? c: W.fragment(c)
                    }),
                    g = this.length > 1;
                return f.length < 1 ? this: this.each(function(a, b) {
                    e = d ? b: b.parentNode,
                        b = 0 == c ? b.nextSibling: 1 == c ? b.firstChild: 2 == c ? b: null;
                    var h = x.contains(E.documentElement, e);
                    f.forEach(function(a) {
                        if (g) a = a.cloneNode(!0);
                        else if (!e) return x(a).remove();
                        e.insertBefore(a, b),
                        h && u(a,
                            function(a) {
                                null == a.nodeName || "SCRIPT" !== a.nodeName.toUpperCase() || a.type && "text/javascript" !== a.type || a.src || window.eval.call(window, a.innerHTML)
                            })
                    })
                })
            },
                x.fn[d ? b + "To": "insert" + (c ? "Before": "After")] = function(a) {
                    return x(a)[b](this),
                        this
                }
        }),
        W.Z.prototype = x.fn,
        W.uniq = A,
        W.deserializeValue = t,
        x.zepto = W,
        x
} ();
window.Zepto = Zepto,
void 0 === window.$ && (window.$ = Zepto),
    function(a) {
        function b(a) {
            return a._zid || (a._zid = m++)
        }
        function c(a, c, f, g) {
            if (c = d(c), c.ns) var h = e(c.ns);
            return (q[b(a)] || []).filter(function(a) {
                return ! (!a || c.e && a.e != c.e || c.ns && !h.test(a.ns) || f && b(a.fn) !== b(f) || g && a.sel != g)
            })
        }
        function d(a) {
            var b = ("" + a).split(".");
            return {
                e: b[0],
                ns: b.slice(1).sort().join(" ")
            }
        }
        function e(a) {
            return new RegExp("(?:^| )" + a.replace(" ", " .* ?") + "(?: |$)")
        }
        function f(a, b) {
            return a.del && !s && a.e in t || !!b
        }
        function g(a) {
            return u[a] || s && t[a] || a
        }
        function h(c, e, h, i, k, m, n) {
            var o = b(c),
                p = q[o] || (q[o] = []);
            e.split(/\s/).forEach(function(b) {
                var e, o;
                return "ready" == b ? a(document).ready(h) : (e = d(b), e.fn = h, e.sel = k, e.e in u && (h = function(b) {
                    var c = b.relatedTarget;
                    return ! c || c !== this && !a.contains(this, c) ? e.fn.apply(this, arguments) : void 0
                }), e.del = m, o = m || h, e.proxy = function(a) {
                    if (a = j(a), !a.isImmediatePropagationStopped()) {
                        a.data = i;
                        var b = o.apply(c, a._args == l ? [a] : [a].concat(a._args));
                        return b === !1 && (a.preventDefault(), a.stopPropagation()),
                            b
                    }
                },
                    e.i = p.length, p.push(e), "addEventListener" in c && c.addEventListener(g(e.e), e.proxy, f(e, n)), void 0)
            })
        }
        function i(a, d, e, h, i) {
            var j = b(a); (d || "").split(/\s/).forEach(function(b) {
                c(a, b, e, h).forEach(function(b) {
                    delete q[j][b.i],
                    "removeEventListener" in a && a.removeEventListener(g(b.e), b.proxy, f(b, i))
                })
            })
        }
        function j(b, c) {
            return (c || !b.isDefaultPrevented) && (c || (c = b), a.each(y,
                function(a, d) {
                    var e = c[a];
                    b[a] = function() {
                        return this[d] = v,
                        e && e.apply(c, arguments)
                    },
                        b[d] = w
                }), (c.defaultPrevented !== l ? c.defaultPrevented: "returnValue" in c ? c.returnValue === !1 : c.getPreventDefault && c.getPreventDefault()) && (b.isDefaultPrevented = v)),
                b
        }
        function k(a) {
            var b, c = {
                originalEvent: a
            };
            for (b in a) x.test(b) || a[b] === l || (c[b] = a[b]);
            return j(c, a)
        }
        var l, v, w, x, y, m = 1,
            n = Array.prototype.slice,
            o = a.isFunction,
            p = function(a) {
                return "string" == typeof a
            },
            q = {},
            r = {},
            s = "onfocusin" in window,
            t = {
                focus: "focusin",
                blur: "focusout"
            },
            u = {
                mouseenter: "mouseover",
                mouseleave: "mouseout"
            };
        r.click = r.mousedown = r.mouseup = r.mousemove = "MouseEvents",
            a.event = {
                add: h,
                remove: i
            },
            a.proxy = function(c, d) {
                var f, e = 2 in arguments && n.call(arguments, 2);
                if (o(c)) return f = function() {
                    return c.apply(d, e ? e.concat(n.call(arguments)) : arguments)
                },
                    f._zid = b(c),
                    f;
                if (p(d)) return e ? (e.unshift(c[d], c), a.proxy.apply(null, e)) : a.proxy(c[d], c);
                throw new TypeError("expected function")
            },
            a.fn.bind = function(a, b, c) {
                return this.on(a, b, c)
            },
            a.fn.unbind = function(a, b) {
                return this.off(a, b)
            },
            a.fn.one = function(a, b, c, d) {
                return this.on(a, b, c, d, 1)
            },
            v = function() {
                return ! 0
            },
            w = function() {
                return ! 1
            },
            x = /^([A-Z]|returnValue$|layer[XY]$)/,
            y = {
                preventDefault: "isDefaultPrevented",
                stopImmediatePropagation: "isImmediatePropagationStopped",
                stopPropagation: "isPropagationStopped"
            },
            a.fn.delegate = function(a, b, c) {
                return this.on(b, a, c)
            },
            a.fn.undelegate = function(a, b, c) {
                return this.off(b, a, c)
            },
            a.fn.live = function(b, c) {
                return a(document.body).delegate(this.selector, b, c),
                    this
            },
            a.fn.die = function(b, c) {
                return a(document.body).undelegate(this.selector, b, c),
                    this
            },
            a.fn.on = function(b, c, d, e, f) {
                var g, j, m = this;
                return b && !p(b) ? (a.each(b,
                    function(a, b) {
                        m.on(a, c, d, b, f)
                    }), m) : (p(c) || o(e) || e === !1 || (e = d, d = c, c = l), (o(d) || d === !1) && (e = d, d = l), e === !1 && (e = w), m.each(function(l, m) {
                    f && (g = function(a) {
                        return i(m, a.type, e),
                            e.apply(this, arguments)
                    }),
                    c && (j = function(b) {
                        var d, f = a(b.target).closest(c, m).get(0);
                        return f && f !== m ? (d = a.extend(k(b), {
                            currentTarget: f,
                            liveFired: m
                        }), (g || e).apply(f, [d].concat(n.call(arguments, 1)))) : void 0
                    }),
                        h(m, b, e, d, c, j || g)
                }))
            },
            a.fn.off = function(b, c, d) {
                var e = this;
                return b && !p(b) ? (a.each(b,
                    function(a, b) {
                        e.off(a, c, b)
                    }), e) : (p(c) || o(d) || d === !1 || (d = c, c = l), d === !1 && (d = w), e.each(function() {
                    i(this, b, d, c)
                }))
            },
            a.fn.trigger = function(b, c) {
                return b = p(b) || a.isPlainObject(b) ? a.Event(b) : j(b),
                    b._args = c,
                    this.each(function() {
                        b.type in t && "function" == typeof this[b.type] ? this[b.type]() : "dispatchEvent" in this ? this.dispatchEvent(b) : a(this).triggerHandler(b, c)
                    })
            },
            a.fn.triggerHandler = function(b, d) {
                var e, f;
                return this.each(function(g, h) {
                    e = k(p(b) ? a.Event(b) : b),
                        e._args = d,
                        e.target = h,
                        a.each(c(h, b.type || b),
                            function(a, b) {
                                return f = b.proxy(e),
                                    e.isImmediatePropagationStopped() ? !1 : void 0
                            })
                }),
                    f
            },
            "focusin focusout focus blur load resize scroll unload click dblclick mousedown mouseup mousemove mouseover mouseout mouseenter mouseleave change select keydown keypress keyup error".split(" ").forEach(function(b) {
                a.fn[b] = function(a) {
                    return 0 in arguments ? this.bind(b, a) : this.trigger(b)
                }
            }),
            a.Event = function(a, b) {
                var c, d, e;
                if (p(a) || (b = a, a = b.type), c = document.createEvent(r[a] || "Events"), d = !0, b) for (e in b)"bubbles" == e ? d = !!b[e] : c[e] = b[e];
                return c.initEvent(a, d, !0),
                    j(c)
            }
    } (Zepto),
    function(a) {
        function b(b, c, d) {
            var e = a.Event(c);
            return a(b).trigger(e, d),
                !e.isDefaultPrevented()
        }
        function c(a, c, d, e) {
            return a.global ? b(c || s, d, e) : void 0
        }
        function d(b) {
            b.global && 0 === a.active++&&c(b, null, "ajaxStart")
        }
        function e(b) {
            b.global && !--a.active && c(b, null, "ajaxStop")
        }
        function f(a, b) {
            var d = b.context;
            return b.beforeSend.call(d, a, b) === !1 || c(b, d, "ajaxBeforeSend", [a, b]) === !1 ? !1 : void c(b, d, "ajaxSend", [a, b])
        }
        function g(a, b, d, e) {
            var f = d.context,
                g = "success";
            d.success.call(f, a, g, b),
            e && e.resolveWith(f, [a, g, b]),
                c(d, f, "ajaxSuccess", [b, d, a]),
                i(g, b, d)
        }
        function h(a, b, d, e, f) {
            var g = e.context;
            e.error.call(g, d, b, a),
            f && f.rejectWith(g, [d, b, a]),
                c(e, g, "ajaxError", [d, e, a || b]),
                i(b, d, e)
        }
        function i(a, b, d) {
            var f = d.context;
            d.complete.call(f, b, a),
                c(d, f, "ajaxComplete", [b, d]),
                e(d)
        }
        function j() {}
        function k(a) {
            return a && (a = a.split(";", 2)[0]),
            a && (a == x ? "html": a == w ? "json": u.test(a) ? "script": v.test(a) && "xml") || "text"
        }
        function l(a, b) {
            return "" == b ? a: (a + "&" + b).replace(/[&?]{1,2}/, "?")
        }
        function m(b) {
            b.processData && b.data && "string" != a.type(b.data) && (b.data = a.param(b.data, b.traditional)),
            !b.data || b.type && "GET" != b.type.toUpperCase() || (b.url = l(b.url, b.data), b.data = void 0)
        }
        function n(b, c, d, e) {
            return a.isFunction(c) && (e = d, d = c, c = void 0),
            a.isFunction(d) || (e = d, d = void 0),
            {
                url: b,
                data: c,
                success: d,
                dataType: e
            }
        }
        function o(b, c, d, e) {
            var f, g = a.isArray(c),
                h = a.isPlainObject(c);
            a.each(c,
                function(c, i) {
                    f = a.type(i),
                    e && (c = d ? e: e + "[" + (h || "object" == f || "array" == f ? c: "") + "]"),
                        !e && g ? b.add(i.name, i.value) : "array" == f || !d && "object" == f ? o(b, i, d, c) : b.add(c, i)
                })
        }
        var p, q, A, r = 0,
            s = window.document,
            t = /<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi,
            u = /^(?:text|application)\/javascript/i,
            v = /^(?:text|application)\/xml/i,
            w = "application/json",
            x = "text/html",
            y = /^\s*$/,
            z = s.createElement("a");
        z.href = window.location.href,
            a.active = 0,
            a.ajaxJSONP = function(b, c) {
                if (! ("type" in b)) return a.ajax(b);
                var d, e, i = b.jsonpCallback,
                    j = (a.isFunction(i) ? i() : i) || "jsonp" + ++r,
                    k = s.createElement("script"),
                    l = window[j],
                    m = function(b) {
                        a(k).triggerHandler("error", b || "abort")
                    },
                    n = {
                        abort: m
                    };
                return c && c.promise(n),
                    a(k).on("load error",
                        function(f, i) {
                            clearTimeout(e),
                                a(k).off().remove(),
                                "error" != f.type && d ? g(d[0], n, b, c) : h(null, i || "error", n, b, c),
                                window[j] = l,
                            d && a.isFunction(l) && l(d[0]),
                                l = d = void 0
                        }),
                    f(n, b) === !1 ? (m("abort"), n) : (window[j] = function() {
                        d = arguments
                    },
                        k.src = b.url.replace(/\?(.+)=\?/, "?$1=" + j), s.head.appendChild(k), b.timeout > 0 && (e = setTimeout(function() {
                            m("timeout")
                        },
                        b.timeout)), n)
            },
            a.ajaxSettings = {
                type: "GET",
                beforeSend: j,
                success: j,
                error: j,
                complete: j,
                context: null,
                global: !0,
                xhr: function() {
                    return new window.XMLHttpRequest
                },
                accepts: {
                    script: "text/javascript, application/javascript, application/x-javascript",
                    json: w,
                    xml: "application/xml, text/xml",
                    html: x,
                    text: "text/plain"
                },
                crossDomain: !1,
                timeout: 0,
                processData: !0,
                cache: !0
            },
            a.ajax = function(b) {
                var c, n, o, r, t, u, v, w, x, A, B, e = a.extend({},
                        b || {}),
                    i = a.Deferred && a.Deferred();
                for (p in a.ajaxSettings) void 0 === e[p] && (e[p] = a.ajaxSettings[p]);
                if (d(e), e.crossDomain || (c = s.createElement("a"), c.href = e.url, c.href = c.href, e.crossDomain = z.protocol + "//" + z.host != c.protocol + "//" + c.host), e.url || (e.url = window.location.toString()), m(e), n = e.dataType, o = /\?.+=\?/.test(e.url), o && (n = "jsonp"), e.cache !== !1 && (b && b.cache === !0 || "script" != n && "jsonp" != n) || (e.url = l(e.url, "_=" + Date.now())), "jsonp" == n) return o || (e.url = l(e.url, e.jsonp ? e.jsonp + "=?": e.jsonp === !1 ? "": "callback=?")),
                    a.ajaxJSONP(e, i);
                if (t = e.accepts[n], u = {},
                        v = function(a, b) {
                            u[a.toLowerCase()] = [a, b]
                        },
                        w = /^([\w-]+:)\/\//.test(e.url) ? RegExp.$1: window.location.protocol, x = e.xhr(), A = x.setRequestHeader, i && i.promise(x), e.crossDomain || v("X-Requested-With", "XMLHttpRequest"), v("Accept", t || "*/*"), (t = e.mimeType || t) && (t.indexOf(",") > -1 && (t = t.split(",", 2)[0]), x.overrideMimeType && x.overrideMimeType(t)), (e.contentType || e.contentType !== !1 && e.data && "GET" != e.type.toUpperCase()) && v("Content-Type", e.contentType || "application/x-www-form-urlencoded"), e.headers) for (q in e.headers) v(q, e.headers[q]);
                if (x.setRequestHeader = v, x.onreadystatechange = function() {
                        if (4 == x.readyState) {
                            x.onreadystatechange = j,
                                clearTimeout(r);
                            var b, c = !1;
                            if (x.status >= 200 && x.status < 300 || 304 == x.status || 0 == x.status && "file:" == w) {
                                n = n || k(e.mimeType || x.getResponseHeader("content-type")),
                                    b = x.responseText;
                                try {
                                    "script" == n ? (1, eval)(b) : "xml" == n ? b = x.responseXML: "json" == n && (b = y.test(b) ? null: a.parseJSON(b))
                                } catch(d) {
                                    c = d
                                }
                                c ? h(c, "parsererror", x, e, i) : g(b, x, e, i)
                            } else h(x.statusText || null, x.status ? "error": "abort", x, e, i)
                        }
                    },
                    f(x, e) === !1) return x.abort(),
                    h(null, "abort", x, e, i),
                    x;
                if (e.xhrFields) for (q in e.xhrFields) x[q] = e.xhrFields[q];
                B = "async" in e ? e.async: !0,
                    x.open(e.type, e.url, B, e.username, e.password);
                for (q in u) A.apply(x, u[q]);
                return e.timeout > 0 && (r = setTimeout(function() {
                        x.onreadystatechange = j,
                            x.abort(),
                            h(null, "timeout", x, e, i)
                    },
                    e.timeout)),
                    x.send(e.data ? e.data: null),
                    x
            },
            a.get = function() {
                return a.ajax(n.apply(null, arguments))
            },
            a.post = function() {
                var b = n.apply(null, arguments);
                return b.type = "POST",
                    a.ajax(b)
            },
            a.getJSON = function() {
                var b = n.apply(null, arguments);
                return b.dataType = "json",
                    a.ajax(b)
            },
            a.fn.load = function(b, c, d) {
                if (!this.length) return this;
                var e, f = this,
                    g = b.split(/\s/),
                    h = n(b, c, d),
                    i = h.success;
                return g.length > 1 && (h.url = g[0], e = g[1]),
                    h.success = function(b) {
                        f.html(e ? a("<div>").html(b.replace(t, "")).find(e) : b),
                        i && i.apply(f, arguments)
                    },
                    a.ajax(h),
                    this
            },
            A = encodeURIComponent,
            a.param = function(b, c) {
                var d = [];
                return d.add = function(b, c) {
                    a.isFunction(c) && (c = c()),
                    null == c && (c = ""),
                        this.push(A(b) + "=" + A(c))
                },
                    o(d, b, c),
                    d.join("&").replace(/%20/g, "+")
            }
    } (Zepto),
    function(a) {
        a.fn.serializeArray = function() {
            var b, c, d = [],
                e = function(a) {
                    return a.forEach ? a.forEach(e) : void d.push({
                        name: b,
                        value: a
                    })
                };
            return this[0] && a.each(this[0].elements,
                function(d, f) {
                    c = f.type,
                        b = f.name,
                    b && "fieldset" != f.nodeName.toLowerCase() && !f.disabled && "submit" != c && "reset" != c && "button" != c && "file" != c && ("radio" != c && "checkbox" != c || f.checked) && e(a(f).val())
                }),
                d
        },
            a.fn.serialize = function() {
                var a = [];
                return this.serializeArray().forEach(function(b) {
                    a.push(encodeURIComponent(b.name) + "=" + encodeURIComponent(b.value))
                }),
                    a.join("&")
            },
            a.fn.submit = function(b) {
                if (0 in arguments) this.bind("submit", b);
                else if (this.length) {
                    var c = a.Event("submit");
                    this.eq(0).trigger(c),
                    c.isDefaultPrevented() || this.get(0).submit()
                }
                return this
            }
    } (Zepto),
    function(a) {
        "__proto__" in {} || a.extend(a.zepto, {
            Z: function(b, c) {
                return b = b || [],
                    a.extend(b, a.fn),
                    b.selector = c || "",
                    b.__Z = !0,
                    b
            },
            isZ: function(b) {
                return "array" === a.type(b) && "__Z" in b
            }
        });
        try {
            getComputedStyle(void 0)
        } catch(b) {
            var c = getComputedStyle;
            window.getComputedStyle = function(a) {
                try {
                    return c(a)
                } catch(b) {
                    return null
                }
            }
        }
    } (Zepto),
    function(dd) {
        var didi = window.didi = window.dd = dd; !
            function(a) {
                "use strict";
                a._cfg = {},
                    a.init = function(b) {
                        b = b || {},
                        b.id && a.trace({
                            id: b.id,
                            data: b.data || {}
                        });
                        var c = a._cfg;
                        "auto" === b.debug && (b.debug = a.is("dev")),
                            c.debug = !!b.debug,
                        b.debug === !0,
                            b.wxSignFn ? c.wxSignFn = b.wxSignFn: b.wxSignApi && (c.wxSignApi = b.wxSignApi),
                            "undefined" != typeof b.share ? a.setShare(b.share) : a.app.setShare(!1)
                    },
                    a._ = {},
                    a._.require = function(a, b, c) {
                        if ($.isFunction(window.define) && (window.define.amd || window.define.cmd) && window.require) window.require.config({
                            paths: {
                                wx: a
                            }
                        }),
                            window.require([b],
                                function(a) {
                                    window[b] = a,
                                        c(a)
                                });
                        else {
                            var d = document.createElement("script");
                            d.type = "text/javascript",
                                d.src = a + ".js",
                                document.getElementsByTagName("head")[0].appendChild(d),
                                d.onload = function() {
                                    c(window[b])
                                }
                        }
                    },
                    $.getScript = function(a, b) {
                        var c = document.createElement("script");
                        c.type = "text/javascript",
                            c.src = a,
                            document.getElementsByTagName("head")[0].appendChild(c),
                            c.onload = c.onreadystatechange = function() {
                                this.readyState && "loaded" !== this.readyState && "complete" !== this.readyState || "function" == typeof b && b()
                            }
                    },
                    $.fn.touch = function(b, c, d) {
                        var g, h, i, j, k, l, e = $(this),
                            f = !1;
                        if (b === !1) return e.off(".touch"),
                            this;
                        if ("string" == typeof b && c === !1) return e.off(".touch", b),
                            this;
                        if ("string" == typeof b && $.isFunction(c)) f = !0;
                        else {
                            if (!$.isFunction(b)) return this;
                            f = !1,
                                c = b,
                                d = c
                        }
                        return g = 0,
                            h = 0,
                            i = a.is("mobile"),
                            j = function(a, b) {
                                d !== !1 && (b ? a.addClass("active") : a.removeClass("active"))
                            },
                            a._cfg.lastTouch = +new Date,
                            k = function(c, d) {
                                c = "touch" + c + ".touch";
                                var g = function() {
                                    d.apply(this, arguments),
                                        a._cfg.lastTouch = +new Date
                                };
                                f ? e.on(c, b, g) : e.on(c, g)
                            },
                            l = function(c, d) {
                                c = "mouse" + c + ".touch";
                                var g = function() {
                                    var b = +new Date; (!i || a.is("chrome") && b - a._cfg.lastTouch > 1e3) && d.apply(this, arguments)
                                };
                                f ? e.on(c, b, g) : e.on(c, g)
                            },
                            k("start",
                                function(a) {
                                    j($(this), !0);
                                    var b = a.touches || a.originalEvent && a.originalEvent.touches || [{}];
                                    b = b[0],
                                        g = b.pageX,
                                        h = b.pageY
                                }),
                            k("move",
                                function(a) {
                                    var b = a.touches || a.originalEvent && a.originalEvent.touches || [{}];
                                    b = b[0],
                                        Math.abs(b.pageX - g) + Math.abs(b.pageY - h) > 15 ? ($(this).attr("moved", "true"), j($(this), !1)) : ($(this).attr("moved", "false"), j($(this), !0))
                                }),
                            k("end",
                                function(a) {
                                    j($(this), !1),
                                        "true" !== $(this).attr("moved") ? c.call(this, a) : $(this).attr("moved", "false")
                                }),
                            k("cancel",
                                function() {
                                    j($(this), !1),
                                        $(this).attr("moved", "false")
                                }),
                            l("down",
                                function() {
                                    j($(this), !0)
                                }),
                            l("leave",
                                function() {
                                    j($(this), !1)
                                }),
                            l("up",
                                function(a) {
                                    j($(this), !1),
                                        c.call(this, a)
                                }),
                            this
                    },
                    $.fn.loading = function(a) {
                        "string" == typeof a && (a = {
                            text: a
                        })
                    }
            } (didi),
            function(a) {
                "use strict";
                var b = {
                    decode: function(a) {
                        a = a || window.location.href;
                        var b = document.createElement("a");
                        return b.href = a,
                        {
                            source: a,
                            protocol: b.protocol.replace(":", ""),
                            host: b.hostname,
                            port: b.port,
                            query: b.search,
                            params: function() {
                                var f, a = {},
                                    c = b.search.replace(/^\?/, "").split("&"),
                                    d = c.length,
                                    e = 0;
                                for (; d > e; e++) c[e] && (f = c[e].split("="), a[decodeURIComponent(f[0])] = decodeURIComponent(c[e].substr(f[0].length + 1)));
                                return a
                            } (),
                            file: (b.pathname.match(/\/([^\/?#]+)$/i) || [, ""])[1],
                            hash: b.hash.replace("#", ""),
                            path: b.pathname.replace(/^([^\/])/, "/$1"),
                            relative: (b.href.match(/tps?:\/\/[^\/]+(.+)/) || [, ""])[1],
                            segments: b.pathname.replace(/^\//, "").split("/")
                        }
                    },
                    encode: function(a) {
                        var b = [],
                            c = [];
                        return b.push(a.protocol, "://"),
                            b.push(a.host),
                        a.port && b.push(":", a.port),
                            b.push(a.path),
                            $.each(a.params,
                                function(a, b) {
                                    c.push(encodeURIComponent(a) + "=" + encodeURIComponent(b))
                                }),
                        c.length > 0 && b.push("?", c.join("&")),
                        a.hash && b.push("#", a.hash),
                            b.join("")
                    },
                    get: function(a, c) {
                        if (0 === arguments.length) return b.decode();
                        var d = "",
                            e = b.decode(c || window.location.href);
                        return $.each(a.split(","),
                            function(a, b) {
                                return e.params[b] ? (d = e.params[b], !1) : void 0
                            }),
                            d
                    },
                    build: function(a, c) {
                        var d = b.decode(a);
                        return $.each(c || {},
                            function(a, b) {
                                d.params[a] = b
                            }),
                            b.encode(d)
                    },
                    weixin: function(a, c) {
                        var a = b.build(a, c);
                        return a
                    },
                    https: function(a) {
                        return a.replace(/^http:/, "")
                    }
                };
                a.query = function(a) {
                    return a ? b.get(a) : b.decode().params
                },
                    $.each("build,encode,decode,https".split(","),
                        function(c, d) {
                            a.query[d] = b[d]
                        }),
                    window.getQueryData = a.query,
                    window.getQueryString = a.query,
                    window.getQuerySting = a.query
            } (didi),
            function(a) {
                "use strict";
                var b = null,
                    c = null,
                    d = null,
                    e = null,
                    f = null,
                    g = null,
                    h = null,
                    i = function(a) {
                        return c = window.document,
                            d = c.documentElement,
                            e = c.body,
                            this instanceof i ? (new i.fn.init(a), void 0) : h = new i(a)
                    },
                    j = function(a) {
                        var b = !1;
                        return b = Array.isArray(a)
                    },
                    k = function(a) {
                        $("body").append(a)
                    };
                i.fn = i.prototype = {
                    constructor: i,
                    init: function(a) {
                        var b, d, h, i, l, m, n, o, p, q, r, s, t, u, v, w, x, y, z, A, B, C, D, E;
                        if (a) {
                            if (b = c.createElement("div"), b.id = "d_wall", b.style.backgroundColor = a.bgcolor || "black", b.style.opacity = a.opacity || "0.2", b.style.filter = a.opacity ? "alpha(opacity=" + 100 * a.opacity + ")": "alpha(opacity=20)", b.className = "didi-dialog-wall", d = c.createElement("div"), d.id = "d_wrap", d.style.width = a.width || "260px", d.style.height = a.height || "210px", d.style.backgroundColor = a.d_bgcolor || "#fff", d.style.opacity = a.d_opacity ? a.d_opacity: "", d.style.filter = a.d_opacity ? "alpha(opacity=" + 100 * a.d_opacity + ")": "alpha(opacity=20)", "loading" === a.type && (d.style.padding = "0px"), d.setAttribute("dialog-type", a.type), d.className = "didi-dialog-wrap", h = "", h += "<div style='" + ("loading" === a.type ? "padding:0px;": "padding: 0px 16px;") + "'>", a.dom && 1 === a.dom.nodeType ? h += a.dom.outerHTML: a.html && "string" == typeof a.html ? h += a.html: a.domId && a.domId.length && (i = c.getElementById(a.domId), i && (h += i.outerHTML)), a.icon ? (l = a.icon.type, m = a.icon.width || "61px", n = a.icon.height || "61px", l && (o = "", o = "loading" === a.type ? "margin:36px 0px 10px 0": "margin:20px 0px 12px 0", p = "dialog-icon icon-" + l, "loading" === l && (p = "icon-loading"), h += '<p class="didi_dialog_icon" style="' + o + '"><span class="' + p + '" style="display: inline-block; width:' + m + ";height:" + n + "; background-size:" + m + " " + n + ';"></span></p>')) : h += '<div class="no-icon" style="height:20px"></div>', a.title && j(a.title)) for (q = 0, r = a.title.length; r > q; q++) s = a.title[q],
                            s && (t = s.color || "#ff8a01", u = s.fontSize || "1.4em", h += '<p class="didi-dialog-title" style="color:' + t + ";font-size:" + u + ';">' + s.txt + "</p>");
                            if (a.tips && j(a.tips)) for (v = 0, w = a.tips.length; w > v; v++) x = a.tips[v],
                            x && (y = x.color || "#666666", z = x.fontSize || "1.0em", h += '<p class="didi-dialog-p" style="color:' + y + ";font-size:" + z + ';">' + x.txt + "</p>");
                            if (a.btns && j(a.btns)) {
                                for (h += '<div id="d_dialog_footer" class="didi-dialog-footer">', A = 0, B = a.btns.length; B > A; A++) C = a.btns[A],
                                C && (h += "confirm" === a.type ? '<a class="' + C.klsName + '" id="' + C.id + '" style="width: 118px; height: 37px; line-height: 35px; margin:0 3px;">' + C.txt + "</a>": '<a class="' + C.klsName + '" id="' + C.id + '" style="width: 100%;">' + C.txt + "</a>");
                                h += "</div>"
                            }
                            h += "</div>",
                                d.innerHTML = h,
                                D = c.getElementById("d_wall"),
                                E = c.getElementById("d_wrap"),
                                D ? (e.removeChild(D), k(b)) : k(b),
                                E ? (e.removeChild(E), k(d)) : k(d),
                                f = c.getElementById("d_wall"),
                                g = c.getElementById("d_wrap"),
                            a.btns && a.btns.length && j(a.btns) && $.each(a.btns,
                                function(a, b) {
                                    if (b) {
                                        var d = b.id,
                                            e = b.eventType || "click",
                                            f = b.callback,
                                            g = c.getElementById(d);
                                        g && !g["on" + e] && ("click" === e ? $(g).touch(function(a) {
                                                a.preventDefault(),
                                                    a.stopPropagation(),
                                                    setTimeout(function() {
                                                            f(a)
                                                        },
                                                        0)
                                            },
                                            !0) : g.addEventListener(e, f, !1))
                                    }
                                })
                        }
                    },
                    _dialogPosi: function() {
                        var j, k, a = d.scrollTop,
                            b = d.scrollLeft,
                            e = d.clientHeight,
                            f = c.getElementsByTagName("body")[0].offsetWidth,
                            h = g.style.height.replace("px", ""),
                            i = g.style.width.replace("px", "");
                        return "auto" === h && (h = 220),
                            j = a + (e - h - 20) / 2,
                            k = b + (f - i) / 2,
                        {
                            top: j,
                            left: k
                        }
                    }
                },
                    i.fn.show = function() {
                        var h, i, j, k, a = this;
                        f && g && (h = e.scrollHeight, i = d.clientWidth, j = c.documentElement.scrollTop || c.body.scrollTop, f.style.width = i + "px", f.style.height = h + "px", f.style.display = "block", k = this._dialogPosi(), g.style.top = k.top + j + "px", g.style.left = k.left + "px", g.style.display = "block", window.addEventListener("resize",
                            function() {
                                a.reset.call(a)
                            },
                            !1), window.addEventListener("scroll",
                            function() {
                                a.reset.call(a),
                                    j = c.documentElement.scrollTop || c.body.scrollTop,
                                    g.style.top = k.top + j + "px"
                            },
                            !1)),
                        b && clearTimeout(b)
                    },
                    i.fn.hide = function(a) {
                        if (a && "string" == typeof a && g) {
                            var c = g.getAttribute("dialog-type");
                            if (a !== c) return ! 1
                        }
                        f && g && (f.style.display = "none", g.style.display = "none"),
                        b && clearTimeout(b)
                    },
                    i.fn.reset = function() {
                        var b, d, e, a = f.style.display;
                        "block" === a && f && g && (b = c.documentElement.scrollHeight, d = c.documentElement.clientWidth, f.style.width = d + "px", f.style.height = b + "px", e = this._dialogPosi(), g.style.top = e.top + "px", g.style.left = e.left + "px")
                    },
                    a.alert = function(a, b, c) {
                        if (c = c || {},
                            a === !1) return i.fn.hide("alert");
                        var d = {
                            type: "alert",
                            bgcolor: "black",
                            height: "auto",
                            width: c.width || "280px",
                            tips: [{
                                txt: a,
                                fontSize: "1.1em"
                            }],
                            btns: [{
                                id: "btn_close",
                                txt: c.text || "æˆ‘çŸ¥é“äº†",
                                klsName: "btn-orange",
                                eventType: "click",
                                callback: function() {
                                    h.hide(),
                                    b && b()
                                }
                            }]
                        };
                        c.icon !== !1 && (d.icon = {
                            type: c.icon || "alert",
                            width: "61px",
                            height: "61px"
                        }),
                            h = new i(d),
                            h.show()
                    },
                    a.confirm = function(a, b, c, d) {
                        if (d = d || {},
                            a === !1) return i.fn.hide("confirm");
                        var e = {
                            type: "confirm",
                            height: "auto",
                            width: d.width || "280px",
                            tips: [{
                                txt: a || "ç¡®å®šæ‰§è¡Œæ­¤æ“ä½œå—ï¼Ÿ",
                                color: "#666666",
                                fontSize: "1.04em"
                            }],
                            btns: [{
                                id: "btn-cancel",
                                txt: d.cancelText || "å–æ¶ˆ",
                                klsName: "btn-white",
                                eventType: "click",
                                callback: function() {
                                    h.hide(),
                                    "function" == typeof c && c()
                                }
                            },
                                {
                                    id: "btn-ok",
                                    txt: d.confirmText || "ç¡®å®š",
                                    klsName: "btn-orange",
                                    eventType: "click",
                                    callback: function() {
                                        h.hide(),
                                        "function" == typeof b && b()
                                    }
                                }]
                        };
                        d.icon !== !1 && (e.icon = {
                            type: d.icon || "alert",
                            width: "60px",
                            height: "60px"
                        }),
                            h = new i(e),
                            h.show()
                    },
                    a.loading = function(a, c, d) {
                        if (a === !1) return i.fn.hide("loading");
                        var e = {
                            type: "loading",
                            bgcolor: "#ffffff",
                            d_bgcolor: "black",
                            d_opacity: "0.7",
                            width: "125px",
                            height: "125px",
                            icon: {
                                type: "loading",
                                width: "30px",
                                height: "30px"
                            },
                            tips: [{
                                txt: a || "æ­£åœ¨åŠ è½½...",
                                color: "#FFFFFF",
                                fontSize: "13px"
                            }]
                        };
                        h = new i(e),
                            h.show(),
                        (null === c || void 0 === c) && (c = 1e4),
                        c && (b && clearTimeout(b), b = window.setTimeout(function() {
                                h.hide()
                            },
                            c)),
                        d && d()
                    },
                    a.dialog = i
            } (didi),
            function(a) {
                "use strict";
                var b, c, d;
                a.getData = function(a) {
                    if (void 0 !== localStorage[a]) return localStorage[a];
                    var b, c = new RegExp("(^| )" + a + "=([^;]*)(;|$)");
                    return b = document.cookie.match(c),
                        b ? unescape(b[2]) : null
                },
                    a.setData = function(a, b) {
                        localStorage[a] = b;
                        var c = new Date;
                        c.setTime(c.getTime() + 2592e3),
                            document.cookie = a + "=" + escape(b) + ";expires=" + c.toGMTString()
                    },
                    a.delData = function(b) {
                        var c = a.getData(b);
                        void 0 !== c && (delete localStorage[b], a.getData(b) && (document.cookie = b + "=" + c + ";expires=" + new Date(0).toGMTString()))
                    },
                    a.err = function(a) {
                        console.log("ERR:" + a)
                    },
                    a.ua = function() {
                        var a = {
                                android: /android/i,
                                iphone: /iphone/i,
                                ipad: /ipad/i,
                                ipod: /ipod/i,
                                weixin: /micromessenger/i,
                                mqq: /QQ\//i,
                                app: /didi.passenger/i,
                                sdk: /didi.sdk/i,
                                alipay: /aliapp/i,
                                chrome: /chrome\//i
                            },
                            b = {},
                            c = window.navigator.userAgent;
                        return $.each(a,
                            function(a, d) {
                                b[a] = d.test(c)
                            }),
                            b.ios = b.iphone || b.ipad || b.iphone,
                            b.mobile = b.ios || b.android,
                            b.pc = !b.mobile,
                            b.prod = /(udache.com|diditaxi.com.cn|xiaojukeji.com)\//.test(window.location.href),
                            b.dev = !b.prod,
                        window.chrome && window.chrome && (b.chrome = !0),
                        b.app || (b.app = !!window.DidiJSBridge),
                            b.didi = b.app,
                            b
                    },
                    a.is = function(b) {
                        var c = b.split(","),
                            d = a.ua(),
                            e = function(a) {
                                var b, c;
                                return (a = $.trim(a) || "") ? (b = a.split("."), c = !0, $.each(b,
                                    function(a, b) {
                                        return d[b] ? void 0 : (c = !1, !1)
                                    }), c) : !1
                            },
                            f = !1;
                        return $.each(c,
                            function(a, b) {
                                return e(b) ? (f = !0, !1) : void 0
                            }),
                            f
                    },
                    b = function(a) {
                        a = a || "",
                            a = a.split("."),
                            a.length = 4;
                        var b = [];
                        return $.each(a,
                            function(a, c) {
                                c = 1 * c,
                                    c ? b.push(c > 10 ? c: "0" + c) : b.push("00")
                            }),
                            parseInt(b.join(""), 10)
                    },
                    a.version = function() {},
                    a.version.compare = function(a, c, d) {
                        return a = b(a),
                            d = b(d),
                            c = $.trim(c) || "",
                            -1 !== c.indexOf("=") && a === d ? !0 : -1 !== c.indexOf(">") && a > d ? !0 : -1 !== c.indexOf("<") && d > a ? !0 : !1
                    },
                    a.version.parse = b,
                    c = {
                        data: {},
                        sign: "",
                        pv: function(a) {
                            var b = c.data = {
                                id: a.id,
                                data: a.data || {}
                            };
                            $.ajax({
                                url: "//gsactivity.diditaxi.com.cn/gulfstream/recommend/v1/activity/pv",
                                data: {
                                    a_id: b.id,
                                    data: b.data
                                },
                                dataType: "jsonp",
                                success: function(a) {
                                    c.sign = a.sign
                                }
                            })
                        },
                        log: function(a, b, d) {
                            $.isFunction(b) && (d = b, b = {});
                            var e = c.data;
                            $.ajax({
                                url: "//gsactivity.diditaxi.com.cn/gulfstream/recommend/v1/activity/log",
                                data: {
                                    a_id: e.id,
                                    action: a,
                                    data: b || {},
                                    sign: c.sign
                                },
                                dataType: "jsonp",
                                success: function() {
                                    d && d()
                                }
                            })
                        }
                    },
                    d = function(a, b, d) {
                        $.isPlainObject(a) && !c.sign ? c.pv(a) : "string" == typeof a && c.log(a, b, d)
                    },
                    $.extend(d, {
                        pv: c.pv,
                        log: c.log
                    }),
                    a.trace = d
            } (didi),
            function(didi) {
                "use strict";
                var Share = {
                        cache: {},
                        set: function() {}
                    },
                    Env = {
                        cfgs: [],
                        reg: function(a, b) {
                            Env.cfgs[a] = b
                        },
                        get: function() {
                            return Env.cfgs[id] || {}
                        }
                    };
                didi.env = function(a, b) {
                    return b ? Env.reg(a, b) : Env.get(a)
                },
                    $.each("reg".split(","),
                        function(a, b) {
                            didi.env[b] = Env[b]
                        }),
                    didi.setShare = function(a) {
                        didi.weixin.setShare(a),
                            didi.app.setShare(a),
                            didi.qq.setShare(a),
                            $.each(Env.cfgs,
                                function(b, c) {
                                    c.is && c.is() && c.setShare && c.setShare(a)
                                })
                    },
                    function(a) {
                        var b = {
                            fns: [],
                            isReady: !1,
                            isInit: !1,
                            init: function() {
                                b.isInit || (b.isInit = !0, a._.require("//res.wx.qq.com/open/js/jweixin-1.0.0", "wx",
                                    function(a) {
                                        a && a.hideOptionMenu && a.hideOptionMenu(),
                                            b.getSign(function(c) {
                                                var d = "onMenuShareTimeline,onMenuShareAppMessage,onMenuShareWeibo,hideOptionMenu,showOptionMenu,hideMenuItems,showMenuItems,chooseWXPay,scanQRCode,checkJsApi,getLocation".split(","),
                                                    e = {
                                                        debug: !1,
                                                        appId: c.appid || c.appId,
                                                        timestamp: "" + (c.timestamp || c.timestamp),
                                                        nonceStr: c.noncestr || c.nonceStr,
                                                        signature: c.sign || c.signature
                                                    };
                                                e.jsApiList = d,
                                                    a.config(e),
                                                    a.ready(function() {
                                                        b.isReady = !0,
                                                            $.each(b.fns,
                                                                function(b, c) {
                                                                    c(a)
                                                                })
                                                    }),
                                                    a.error(function() {})
                                            })
                                    }))
                            },
                            is: function() {
                                return a.is("weixin")
                            },
                            getSign: function(b) {
                                var d, e, c = a._cfg || {};
                                $.isFunction(a._cfg.wxSignFn) ? c.wxSignFn(function(a) {
                                    b(a)
                                }) : (d = location.href.split("#")[0], c.wxSignApi ? e = c.wxSignApi: /walletranship.com\//.test(d) ? e = "//activity.walletranship.com/wx/getconfig": (e = "//api.udache.com/gulfstream/api/v1/webapp/pJsApiSign", d = encodeURIComponent(d)), $.ajax({
                                    dataType: "jsonp",
                                    url: e,
                                    data: {
                                        url: d
                                    },
                                    success: function(a) {
                                        0 === 1 * a.errno && b(a.info || a.data || a)
                                    }
                                }))
                            },
                            fn: function(a) {
                                return b.is() ? 0 === arguments.length ? !0 : (b.isReady ? a(window.wx, !0) : (b.fns.push(a), b.init()), void 0) : !1
                            },
                            setShare: function(a) {
                                b.fn(function(b) {
                                    if (a !== !1) {
                                        var c = function(b) {
                                            var c = {
                                                title: a.title || "",
                                                link: a.link || a.url || a.href || "",
                                                imgUrl: a.imgUrl || a.icon || "",
                                                desc: a.desc || a.text || a.content || "",
                                                cancel: a.cancel ||
                                                function() {}
                                            };
                                            return c.success = function(c) {
                                                a.success && a.success({
                                                    from: "weixin",
                                                    to: b,
                                                    status: "success",
                                                    ret: c
                                                })
                                            },
                                                c
                                        };
                                        b.showOptionMenu({}),
                                            b.onMenuShareTimeline(c("weixin_timeline")),
                                            b.onMenuShareAppMessage(c("weixin_appmsg")),
                                            b.onMenuShareQQ(c("qq_appmsg")),
                                            b.onMenuShareQZone(c("qzone")),
                                            b.onMenuShareWeibo(c("qq_weibo"))
                                    }
                                })
                            },
                            sign: function() {}
                        };
                        a.weixin = function(a) {
                            return b.fn(a)
                        },
                            $.each("getSign,setShare,is".split(","),
                                function(c, d) {
                                    a.weixin[d] = b[d]
                                }),
                            a.env("weixin", b)
                    } (didi),
                    function(a) {
                        var b = {
                            fns: [],
                            isReady: !1,
                            isInit: !1,
                            init: function() {
                                if (!b.isInit) {
                                    b.isInit = !0;
                                    var a = document.createElement("script");
                                    a.type = "text/javascript",
                                        a.src = "//pub.idqqimg.com/qqmobile/qqapi.js?_bid=152",
                                        document.getElementsByTagName("head")[0].appendChild(a),
                                        a.onload = function() {
                                            b.isReady = !0,
                                                $.each(b.fns,
                                                    function(a, b) {
                                                        b(window.mqq)
                                                    })
                                        }
                                }
                            },
                            is: function() {
                                return a.is("mqq")
                            },
                            fn: function(c) {
                                return a.is("mqq") ? 0 === arguments.length ? !0 : (b.isReady ? c(window.mqq, !0) : (b.fns.push(c), b.init()), void 0) : !1
                            },
                            setShare: function(a) {
                                b.fn(function(b) {
                                    if (a === !1) return b.ui.setTitleButtons({
                                        right: {
                                            hidden: !0
                                        }
                                    }),
                                        void 0;
                                    var c = {
                                        title: a.title || "",
                                        share_url: a.link || a.url || a.href || "",
                                        image_url: a.imgUrl || a.icon || "",
                                        desc: a.desc || a.text || a.content || ""
                                    };
                                    b.data.setShareInfo(c,
                                        function() {})
                                })
                            }
                        };
                        a.qq = function(a) {
                            return b.fn(a)
                        },
                            $.each("setShare,is".split(","),
                                function(c, d) {
                                    a.qq[d] = b[d]
                                })
                    } (didi),
                    function() {
                        var ua, isAndroid, GOON, Hack, Bridge, Titan, Apollo, Share, Version = {
                                cfg: {
                                    pageClose: {
                                        fn: "page_close",
                                        callback: !1
                                    },
                                    pageRefresh: {
                                        fn: "page_refresh",
                                        callback: !1
                                    },
                                    pageOpen: {
                                        fn: "open_url",
                                        sarg: !0,
                                        alias: "openUrl",
                                        callback: !1,
                                        call: function(a) {
                                            return a.newWindow ? GOON: (didi.bridge.openNativeWebPage(a), void 0)
                                        }
                                    },
                                    openNativeWebPage: {
                                        call: function(a) {
                                            return didi.is("ios") && a.url && (window.location.href = a.url),
                                                GOON
                                        }
                                    },
                                    callNativeLogin: {
                                        callback: !1,
                                        sarg: !0,
                                        call: function(a, b) {
                                            return b ? Bridge.call("callNativeLoginWithCallback", {},
                                                b) : GOON
                                        }
                                    },
                                    callNativeLoginWithCallback: {
                                        support: "4.1"
                                    },
                                    initEntrance: {
                                        fn: "init_entrance",
                                        sarg: !0,
                                        callback: !1
                                    },
                                    invokeEntrance: {
                                        fn: "invoke_entrance",
                                        sarg: !0,
                                        callback: !1
                                    },
                                    showEntrance: {
                                        fn: "show_entrance",
                                        sarg: !0,
                                        callback: !1
                                    },
                                    hideEntrance: {
                                        fn: "hide_entrance",
                                        sarg: !0,
                                        callback: !1
                                    },
                                    shareWeixinTimeline: {
                                        fn: "share_weixin_timeline",
                                        sarg: !0,
                                        callback: !1
                                    },
                                    shareWeixinAppmsg: {
                                        fn: "share_weixin_appmsg",
                                        sarg: !0,
                                        callback: !1
                                    },
                                    shareQzone: {
                                        fn: "share_qzone",
                                        sarg: !0,
                                        callback: !1
                                    },
                                    shareQQAppmsg: {
                                        fn: "share_qq_appmsg",
                                        sarg: !0,
                                        callback: !1
                                    },
                                    shareSinaWeibo: {
                                        fn: "share_sina_weibo",
                                        sarg: !0,
                                        callback: !1
                                    },
                                    getUserInfo: {
                                        support: "3.9.5"
                                    },
                                    getSystemInfo: {
                                        support: "3.9.5"
                                    },
                                    getLocationInfo: {
                                        support: "3.9.5"
                                    },
                                    imageCutReview: {
                                        alias: "callback_image_literature_review",
                                        support: "3.9.8"
                                    },
                                    resizeImage: {
                                        support: "3.9.10"
                                    },
                                    markupPageClose: {
                                        fn: "markup_page_close",
                                        callback: !1,
                                        support: "4.1.3"
                                    },
                                    agreeMarkup: {
                                        fn: "agree_markup",
                                        callback: !1,
                                        support: "4.1.3"
                                    },
                                    disagreeMarkup: {
                                        fn: "disagree_markup",
                                        callback: !1,
                                        support: "4.1.3"
                                    },
                                    markupGuide: {
                                        fn: "markup_guide",
                                        callback: !1,
                                        support: "4.1.3"
                                    },
                                    pay: {
                                        fn: "pay_setup",
                                        support: "4.1.5"
                                    },
                                    bindCard: {
                                        fn: "bind_card",
                                        support: "4.1.5"
                                    },
                                    traceLog: {},
                                    apolloGetToggle: {
                                        support: "4.2",
                                        paramKey: "name",
                                        callback: function(a, b) {
                                            Apollo.data = a,
                                                b(a)
                                        }
                                    },
                                    apolloTraceLog: {
                                        support: "4.2",
                                        call: function(a, b) {
                                            return Apollo.traceLog(a, b)
                                        }
                                    }
                                },
                                downloadUrl: {
                                    ios: "https://itunes.apple.com/cn/app/di-di-da-che-zhi-jian-shang/id554499054?ls=1&mt=8",
                                    android: "http://dldir1.qq.com/diditaxi/apk/didi_psngr.apk"
                                },
                                map: !1,
                                init: function() {
                                    Version.map || (Version.map = {},
                                        $.each(Version.cfg,
                                            function(a, b) {
                                                b.fn || (b.fn = a);
                                                var c = a.replace(/([A-Z])/g, "_$1").toLowerCase();
                                                Version.map[a] = b,
                                                    Version.map[b.fn] = b,
                                                    Version.map[c] = b,
                                                b.alias && $.each(b.alias.split(","),
                                                    function(a, c) {
                                                        Version.map[c] = b
                                                    })
                                            }))
                                },
                                formatFn: function(a) {
                                    return Version.getFnCfg(a).fn || a
                                },
                                formatParam: function(a, b, c) {
                                    var e, d = Version.getFnCfg(a);
                                    return console.log(b, d),
                                        d.paramKey ? "string" == typeof b && d.paramKey && (e = {},
                                            e[d.paramKey] = b, b = e) : "object" == typeof b && d.sarg && c !== !0 && (b = JSON.stringify(b)),
                                        b
                                },
                                hasCallback: function(a) {
                                    return !! Version.getFnCfg(a).callback != !1
                                },
                                getFnCfg: function(a) {
                                    var b = a.replace(/\-/g, "_"),
                                        c = b.replace(/([A-Z])/g, "_$1").toLowerCase();
                                    return Version.init(),
                                    Version.map[b] || Version.map[c] || Version.map[a] || {}
                                },
                                parse: function(a) {
                                    a = a || "",
                                        a = a.split("."),
                                        a.length = 4;
                                    var b = [];
                                    return $.each(a,
                                        function(a, c) {
                                            c = 1 * c,
                                                c ? b.push(c >= 10 ? c: "0" + c) : b.push("00")
                                        }),
                                        parseInt(b.join(""), 10)
                                },
                                compare: function(a, b, c) {
                                    return a = Version.parse(a),
                                        c = Version.parse(c),
                                        b = $.trim(b) || "",
                                        -1 !== b.indexOf("=") && a === c ? !0 : -1 !== b.indexOf(">") && a > c ? !0 : -1 !== b.indexOf("<") && c > a ? !0 : !1
                                },
                                version: function(a) {
                                    var b, c;
                                    if (!a) return b = /didi.passenger\/([\d\.]+)/.exec(ua),
                                        b && b[1] ? b[1] : "3.9.4";
                                    switch (c = Version.version(), arguments.length) {
                                        case 1:
                                            if (b = /^\s*([>=<]*)\s*([\d\.]+)\s*$/.exec(a), b && 3 === b.length) return Version.compare(c, b[1] || "=", b[2]);
                                            break;
                                        case 2:
                                            return Version.version(a + arguments[1]);
                                        case 3:
                                            return Version.compare.apply(null, arguments)
                                    }
                                    return ! 1
                                },
                                support: function(a, b) {
                                    var c = Version.getFnCfg(a);
                                    return c.support ? Version.compare(b || Version.version(), ">=", c.support) : !1
                                }
                            },
                            didi = window.didi || {},
                            $ = window.$ || {};
                        $.each = $.each ||
                            function(a, b) {
                                var c, d, e;
                                if ("[object Array]" === Object.prototype.toString.call(a)) for (c = 0, d = a.length; d > c; c++) b(c, a[c]);
                                else for (e in a) b(e, a[e])
                            },
                            $.isFunction = $.isFunction ||
                                function(a) {
                                    return "function" == typeof a
                                },
                            ua = window.navigator.userAgent,
                            isAndroid = /android/i.test(ua),
                            GOON = "GOON_BRIDGE_CALL",
                            Hack = {
                                reInjection: function() {
                                    var a, b;
                                    console.log("DidiJSBridge initialization begin"),
                                        a = {
                                            queue: [],
                                            callback: function() {
                                                var a = Array.prototype.slice.call(arguments, 0),
                                                    b = a.shift(),
                                                    c = a.shift();
                                                this.queue[b].apply(this, a),
                                                c || delete this.queue[b]
                                            }
                                        },
                                        a.callHandler = function() {
                                            var c, d, e, f, g, h, i, b = Array.prototype.slice.call(arguments, 0);
                                            if (b.length < 1) throw "DidiJSBridge call error, message:miss method name";
                                            for (c = [], d = b.shift(), e = 0; e < b.length; e++) f = b[e],
                                                g = typeof f,
                                                c.push(g),
                                            "function" === g && (h = a.queue.length, a.queue[h] = f, b[e] = h);
                                            if (i = JSON.parse(prompt(JSON.stringify({
                                                    method: d,
                                                    types: c,
                                                    args: b
                                                }))), !i || 200 !== i.code) throw "DidiJSBridge call error, code:" + i.code + ", message:" + i.result;
                                            return i.result
                                        },
                                        Object.getOwnPropertyNames(a).forEach(function(b) {
                                            var c = a[b];
                                            "function" == typeof c && "callback" !== b && (a[b] = function() {
                                                return c.apply(a, [b].concat(Array.prototype.slice.call(arguments, 0)))
                                            })
                                        }),
                                        window.DidiJSBridge = a,
                                        b = document.createEvent("HTMLEvents"),
                                        b.initEvent("DidiJSBridgeReady", !1, !1),
                                        document.dispatchEvent(b),
                                        console.log("DidiJSBridge initialization end")
                                }
                            },
                            Bridge = {
                                isPre: !1,
                                isReady: !1,
                                fns: [],
                                prepare: function() {
                                    var a, b, c;
                                    Bridge.isPre || (a = null, b = 0, c = function() {
                                        "undefined" != typeof DidiJSBridge && (Bridge.getBridge(function(a) {
                                            $.each(Bridge.fns,
                                                function(b, c) {
                                                    c(a)
                                                }),
                                                Bridge.fns = []
                                        }), a && clearInterval(a), document.removeEventListener("DidiJSBridgeReady", c, !1))
                                    },
                                        document.addEventListener("DidiJSBridgeReady", c, !1), a = setInterval(function() {
                                            c(),
                                                b++,
                                            b > 100 && a && clearInterval(a)
                                        },
                                        100), Bridge.isPre = !0)
                                },
                                waitBridgeFn: null,
                                waitBridgeCount: 0,
                                getBridge: function(a) {
                                    var b = window.DidiJSBridge;
                                    if (Bridge.isReady) return a(b);
                                    if (isAndroid) {
                                        if (b.queue) return Bridge.isReady = !0,
                                            a(b),
                                            void 0;
                                        Bridge.waitBridgeCount < 4 ? (Bridge.waitBridgeFn && clearTimeout(Bridge.waitBridgeFn), Bridge.waitBridgeFn = setTimeout(function() {
                                                Bridge.waitBridgeCount++,
                                                    Bridge.getBridge(a)
                                            },
                                            500)) : 4 === Bridge.waitBridgeCount ? (Hack.reInjection(), Bridge.waitBridgeCount++, setTimeout(function() {
                                                Bridge.getBridge(a)
                                            },
                                            50)) : (Bridge.isReady = !0, a(b))
                                    } else setTimeout(function() {
                                            try {
                                                b.init && b.init({})
                                            } catch(c) {}
                                            Bridge.isReady = !0,
                                                a(b)
                                        },
                                        500)
                                },
                                fn: function(a) {
                                    return 0 === arguments.length ? !0 : "string" == typeof a ? Bridge.call.apply(null, arguments) : (Bridge.isReady ? Bridge.getBridge(a) : (Bridge.fns.push(a), Bridge.prepare()), void 0)
                                },
                                call: function(fnName, params, callback) {
                                    var cfg, ret;
                                    return "function" == typeof params && (callback = params, params = {}),
                                        cfg = Version.getFnCfg(fnName),
                                        cfg.call && (ret = cfg.call(params, callback), ret !== GOON) ? ret: (Titan.supply() ? Titan.call(fnName, params, callback) : Bridge.fn(function(bridge) {
                                            fnName = Version.formatFn(fnName),
                                                params = Version.formatParam(fnName, params);
                                            var hasCallback = cfg.callback !== !1;
                                            console.log(cfg.callback, hasCallback),
                                                hasCallback ? bridge.callHandler(fnName, params,
                                                    function(data) {
                                                        "string" == typeof data && (data = eval("(" + data + ")")),
                                                            callback = callback ||
                                                                function() {};
                                                        var callbackFn = $.isFunction(cfg.callback) ? cfg.callback: function(a, b) {
                                                            "function" == typeof b && b(a)
                                                        };
                                                        callbackFn(data, callback)
                                                    }) : params ? bridge.callHandler(fnName, params) : bridge.callHandler(fnName)
                                        }), void 0)
                                },
                                open: function(a) {
                                    var b, c, d, e, f;
                                    a = a || {},
                                    "string" == typeof a && (a = {
                                        biz: a
                                    }),
                                        b = Date.now(),
                                        c = "didipasnger://common_marketing_host",
                                        d = {
                                            0 : "å‡ºç§Ÿè½¦|taxi|æ‰“è½¦|0",
                                            1 : "ä¸“è½¦|udache|1",
                                            2 : "å¿«è½¦|flier|fastcar|2",
                                            3 : "é¡ºé£Žè½¦|shunfengche|3",
                                            4 : "ä»£é©¾|daijia|4"
                                        },
                                        e = 0,
                                        f = a.business || a.biz,
                                    f && ($.each(d,
                                        function(a, b) {
                                            b.indexOf(f) > -1 && (e = a)
                                        }), c = c + "?business=" + e),
                                    a.loading !== !1 && didi.loading && didi.loading("è¯·ç¨å€™..."),
                                        $("<iframe>").attr({
                                            src: a.packageUrl || c
                                        }).hide().appendTo("body"),
                                        setTimeout(function() {
                                                var d, e, c = Date.now(); (!b || 2500 > c - b) && (d = a.system ? "ios" === a.system ? "ios": "android": isAndroid ? "android": "ios", e = a[d] || Version.downloadUrl[d], location.href = e, a.loading !== !1 && didi.loading(!1))
                                            },
                                            2e3)
                                }
                            },
                            Titan = {
                                id: 0,
                                callbacks: [],
                                supply: function() {
                                    return "undefined" != typeof didi.bridge._forceTitan ? didi.bridge._forceTitan: isAndroid && Version.version(">= 4.4")
                                },
                                getId: function() {
                                    return Titan.id++,
                                    "titan_" + Titan.id
                                },
                                call: function(a, b, c) {
                                    var f, d = Titan.getId(),
                                        e = Version.getFnCfg(a);
                                    Titan.callbacks[d] = function(a) {
                                        var b = $.isFunction(e.callback) ? e.callback: function(a, b) {
                                            "function" == typeof b && b(a)
                                        };
                                        b(a, c)
                                    },
                                        b = Version.formatParam(a, b, !0),
                                        f = {
                                            id: d,
                                            cmd: Version.formatFn(a),
                                            params: b || {}
                                        },
                                        prompt(JSON.stringify(f))
                                },
                                callback: function(obj) {
                                    if (console.log("callback-fn:" + JSON.stringify(obj)), "string" == typeof obj && (obj = eval("(" + obj + ")")), obj.id && obj.result) {
                                        var fn = Titan.callbacks[obj.id] || !1;
                                        "function" == typeof fn && fn(obj.result),
                                            delete Titan.callbacks[obj.id]
                                    }
                                }
                            },
                            Apollo = {
                                data: !1,
                                traceLog: function(a) {
                                    var c = Apollo.data || !1;
                                    c && didi.bridge.traceLog({
                                        eventId: "ApolloTraceLog",
                                        extraInfo: {
                                            name: c.name,
                                            allow: c.allow,
                                            testKey: a.testKey,
                                            event: a.event
                                        }
                                    })
                                },
                                init: function(a, b) {
                                    var c = {
                                        isReady: !1,
                                        allow: Apollo.allow
                                    };
                                    didi.bridge.apolloGetToggle(a,
                                        function(a) {
                                            Apollo.data = a,
                                            b && b(c)
                                        })
                                },
                                allow: function() {
                                    return Apollo.data.allow
                                },
                                test: function() {}
                            },
                            Share = {
                                shareMap: {
                                    weixin_timeline: "å¾®ä¿¡æœ‹å‹åœˆ",
                                    weixin_appmsg: "å¾®ä¿¡å¥½å‹",
                                    sina_weibo: "æ–°æµªå¾®åš",
                                    qq_appmsg: "QQ",
                                    qzone: "QQç©ºé—´"
                                },
                                data: {},
                                formatShareData: function(a, b) {
                                    var c = {
                                            url: "link,url",
                                            icon_url: "icon",
                                            img_url: "imgUrl,img_url",
                                            title: "title",
                                            content: "desc,content",
                                            from: "from"
                                        },
                                        d = {
                                            title: "æ»´æ»´ä¸€ä¸‹ï¼Œç¾Žå¥½å‡ºè¡Œ",
                                            from: "native"
                                        },
                                        e = {};
                                    return b = b ? b + "_": "",
                                        $.each(c,
                                            function(c, d) {
                                                c = "share_" + c,
                                                    $.each(d.split(","),
                                                        function(d, f) {
                                                            return e[c] = e[c] || a[b + f] || "",
                                                                e[c] ? !1 : void 0
                                                        }),
                                                e[c] || $.each(d.split(","),
                                                    function(b, d) {
                                                        return e[c] = e[c] || a[d] || "",
                                                            e[c] ? !1 : void 0
                                                    })
                                            }),
                                        $.each(d,
                                            function(a, b) {
                                                var c = "share_" + a;
                                                e[c] = e[c] || b
                                            }),
                                        e
                                },
                                setShare: function(a, b) {
                                    var c = {
                                        entrance: {
                                            icon: "http://static.xiaojukeji.com/api/img/i-webview-entrance.png"
                                        },
                                        buttons: []
                                    };
                                    a !== !1 && (Share.data = a, $.each(Share.shareMap,
                                        function(b, d) {
                                            a[b] !== !1 && c.buttons.push({
                                                type: "share_" + b,
                                                name: d,
                                                data: Share.formatShareData(a, b),
                                                callback: function(c) {
                                                    a.success && a.success({
                                                        from: "didi_passenger",
                                                        to: b,
                                                        status: "success",
                                                        ret: c
                                                    })
                                                }
                                            })
                                        })),
                                    a.refresh !== !1 && c.buttons.push({
                                        type: "page_refresh",
                                        name: "åˆ·æ–°"
                                    }),
                                        Bridge.fn("init_entrance", c),
                                        Bridge.fn("show_entrance"),
                                    b && "function" == typeof b && b()
                                },
                                share: function(a, b) {
                                    if (a = a.replace(/\-/g, "_"), Share.shareMap[a]) {
                                        var c = Share.formatShareData(b || Share.data, a);
                                        Bridge.call("share_" + a, c)
                                    }
                                }
                            },
                            didi.bridge = Bridge.fn,
                            Version.version.parse = Version.parse,
                            $.each({
                                    setShare: Share.setShare,
                                    share: Share.share,
                                    version: Version.version,
                                    support: Version.support,
                                    is: Version.is,
                                    open: Bridge.open,
                                    _callback: Titan.callback
                                },
                                function(a, b) {
                                    didi.bridge[a] = b
                                }),
                            $.each(Version.cfg,
                                function(a, b) {
                                    b.public !== !1 && (didi.bridge[a] = function(b, c) {
                                        didi.bridge(a, b, c)
                                    })
                                }),
                            window.didi = didi,
                        window.dd && (window.dd.bridge = didi.bridge)
                    } (),
                    didi.app = didi.bridge,
                    function(a) {
                        var b = {
                            isPre: !1,
                            isReady: !1,
                            fns: [],
                            init: function() {},
                            prepare: function() {
                                if (!b.isPre) {
                                    var a = function() {
                                        b.isReady = !0,
                                            $.each(b.fns,
                                                function(a, b) {
                                                    b(window.AlipayJSBridge)
                                                })
                                    };
                                    "undefined" != typeof window.AlipayJSBridge ? a() : document.addEventListener("AlipayJSBridgeReady",
                                        function() {
                                            a()
                                        },
                                        !1),
                                        b.isPre = !0
                                }
                            },
                            fn: function(c) {
                                return a.is("alipay") ? 0 === arguments.length ? !0 : (b.isReady ? c(window.AlipayJSBridge) : (b.fns.push(c), b.prepare()), void 0) : !1
                            }
                        };
                        a.alipay = function(a) {
                            return b.fn(a)
                        },
                            $(b.init),
                            a.env("alipay", b)
                    } (didi)
            } (didi),
            function() {
                "use strict";
                var a = {
                    _tipsCont: null,
                    _tips: null,
                    _isTipShow: !1,
                    _tipsNs: "default",
                    _tipsFn: {},
                    tips: function(b, c) {
                        var e, f, d = 1e4;
                        "number" == typeof c ? (d = 1e3 * c, c = "default") : c = c || "default",
                            a._tipsCont ? (e = a._tipsCont, f = a._tips) : (e = $("<div>").addClass("slide-tips-cont"), f = $("<div>").addClass("slide-tips"), e.append(f).appendTo("body"), e.touch(function() {
                                $(e).removeClass("open")
                            }), a._tipsCont = e, a._tips = f),
                        a._tipsFn[c] && clearTimeout(a._tipsFn[c]),
                            a._tipsFn[c] = setTimeout(function() {
                                    a.tips(!1, c)
                                },
                                d),
                            b === !1 ? (void 0 === c || c === a._tipsNs) && e.removeClass("open") : setTimeout(function() {
                                f.html(b),
                                    e.addClass("open"),
                                    a._tipsNs = c
                            })
                    }
                };
                didi.tips = a.tips
            } ()
    } (window.dd || {});