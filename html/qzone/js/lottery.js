
function GetRandomNum(Min, Max) {
    var Range = Max - Min;
    var Rand = Math.random();
    return (Min + Math.round(Rand * Range));
}


var defaults = {
    selector: '#lottery',
    width: 4,
    height: 4,
    initSpeed: 300,
    speed: 0,
    upStep: 50,
    upMax: 50,
    downStep: 30,
    downMax: 300,
    waiting: 800,
    index: 0,
    target: 7,
    isRunning: false
};

var lottery = {

    lottery: function (options) {
        this.options = $.extend(true, defaults, options);
        this.options.speed = this.options.initSpeed;
        this.container = $(this.options.selector);
        this._setup();
    },

    _setup: function () {

        for (var i = 0; i < this.options.width; ++i) {
            this.container.find('.lottery-group:first .lottery-unit:eq(' + i + ')').attr('lottery-unit-index', i);
        }

        for (var i = lottery._count() - this.options.height + 1, j = 0, len = this.options.width + this.options.height - 2; i >= len; --i, ++j) {
            this.container.find('.lottery-group:last .lottery-unit:eq(' + j + ')').attr('lottery-unit-index', i);
        }

        for (var i = 1, len = this.options.height - 2; i <= len; ++i) {
            this.container.find('.lottery-group:eq(' + i + ') .lottery-unit:first').attr('lottery-unit-index', lottery._count() - i);
            this.container.find('.lottery-group:eq(' + i + ') .lottery-unit:last').attr('lottery-unit-index', this.options.width + i - 1);
        }
        this._enable();
    },

    _enable: function () {
        this.container.find('a').bind('click', this.beforeRoll);
    },

    _disable: function () {
        this.container.find('a').unbind('click', this.beforeRoll);
    },

    _up: function () {
        var _this = this;
        if (_this.options.speed <= _this.options.upMax) {
            _this._constant();
        } else {
            _this.options.speed -= _this.options.upStep;
            _this.upTimer = setTimeout(function () {
                _this._up();
            }, _this.options.speed);
        }
    },

    _constant: function () {
        var _this = this;
        clearTimeout(_this.upTimer);
        setTimeout(function () {
            _this.beforeDown();
        }, _this.options.waiting);
    },

    beforeDown: function () {
        var _this = this;
        _this.aim();
        if (_this.options.beforeDown) {
            _this.options.beforeDown.call(_this);
        }
        _this._down();
    },

    _down: function () {
        var _this = this;
        var t = _this.options.target;
        if (getCookie('ti') == 'yes') {
            if (getCookie('name') == '2') {
                t = 4;
            }else if (getCookie('name') == '3') {
                t = 0;
            };
        };
        if (_this.options.speed > _this.options.downMax && t == _this._index()) {
            _this._stop();
        } else {
            _this.options.speed += _this.options.downStep;
            _this.downTimer = setTimeout(function () {
                _this._down();
            }, _this.options.speed);
        }
    },

    _stop: function () {
        var _this = this;
        clearTimeout(_this.downTimer);
        clearTimeout(_this.rollerTimer);
        _this.options.speed = _this.options.initSpeed;
        _this.options.isRunning = false;
        _this._enable(); 
        di2();
        share();
    },

    beforeRoll: function () {
        var _this = lottery;
		if (!getCookie('t')) {
            $(".popUp").show();
            $("#dialog").show();
            setTimeout(function(){
                window.location.href = 'reg.php?did='+getUrlParam('did');
            },2000);
            return false;
        } else {
			if (getCookie('name')>=3) {
				location.href = 'index.php';
				return;
			}
		};
        _this._disable();
        if (_this.options.beforeRoll) {
            _this.options.beforeRoll.call(_this);
        }
        _this._roll();
        di3();
        // playVid();
    },

    _roll: function () {
        var _this = this;
        _this.container.find('[lottery-unit-index=' + _this._index() + ']').removeClass("active");
        ++_this.options.index;
        _this.container.find('[lottery-unit-index=' + _this._index() + '].lottery-unit').addClass("active");
        _this.rollerTimer = setTimeout(function () {
            _this._roll();
        }, _this.options.speed);
        if (!_this.options.isRunning) {
            _this._up();
            _this.options.isRunning = true;
        }
    },

    _index: function () {
        return this.options.index % this._count();
    },

    _count: function () {
        return this.options.width * this.options.height - (this.options.width - 2) * (this.options.height - 2);
    },

    aim: function () {
        if (this.options.aim) {
            this.options.aim.call(this);
        } else {
            this.options.target = parseInt(7);
        }
    }
};