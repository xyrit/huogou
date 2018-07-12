$(function() {
    var mobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.userAgent);
    var touchstart = mobile ? "touchstart": "mousedown";
    var touchend = mobile ? "touchend": "mouseup";
    var touchmove = mobile ? "touchmove": "mousemove";

    var stageH = $(window).height();
    var playAG = false;
    var msgID = 1;
    var mesArr = [1.5, 2, 1, 2, 2.5, .8, 2, 2, 2.5, 1, 1.5, 1.5, 2.5];

    setPosition();
    $('#yaoqing').show();
    initButtons();

    var motion = new TimelineLite();

    motion.add(TweenLite.to('.non2', mesArr[0], {
        onComplete: function() {
            setMS()
        }
    }));
    motion.add(TweenLite.to('.non2', mesArr[1], {
        onComplete: function() {
            setMS()
        }
    }));
    motion.add(TweenLite.to('.non2', mesArr[2], {
        onComplete: function() {
            setMS()
        }
    }));
    motion.add(TweenLite.to('.non2', mesArr[3], {
        onComplete: function() {
            setMS()
        }
    }));
    motion.add(TweenLite.to('.non2', mesArr[4], {
        onComplete: function() {
            setMS();
            motion.pause();
        }
    }));

    motion.add(TweenLite.to('.non2', mesArr[5], {
        onComplete: function() {
            setMS()
        }
    }));
    motion.add(TweenLite.to('.non2', mesArr[6], {
        onComplete: function() {
            setMS()
        }
    }));
    motion.add(TweenLite.to('.non2', mesArr[7], {
        onComplete: function() {
            setMS()
        }
    }));
    motion.add(TweenLite.to('.non2', mesArr[8], {
        onComplete: function() {
            setMS()
        }
    }));
    motion.add(TweenLite.to('.non2', mesArr[9], {
        onComplete: function() {
            setMS()
        }
    }));
    motion.add(TweenLite.to('.non2', mesArr[10], {
        onComplete: function() {
            setMS()
        }
    }));
    motion.add(TweenLite.to('.non2', mesArr[11], {
        onComplete: function() {
            setMS()
        }
    }));
    motion.add(TweenLite.to('.non2', mesArr[12], {
        onComplete: function() {
            setMS()
        }
    }));
    motion.pause();

    motion.play();

    function setPosition() {
        console.log(stageH);
        $('.nonPic').css('height', stageH + 'px');
        $('.nonPic').css('margin', -stageH / 2 + 'px' + ' 0 0 -320px');
        $('.falsePic').css('height', stageH + 'px');
        $('.falsePic').css('margin', -stageH / 2 + 'px' + ' 0 0 -320px');
        $('.falsePic2').css('height', stageH + 'px');
        $('.falsePic2').css('margin', -stageH / 2 + 'px' + ' 0 0 -320px');
    }

    function setMS() {
        console.log(msgID);
        $('#msg' + msgID).show();
        $('body').scrollTop($(document).height() - stageH);
        msgID++;
    }

    function playAgain() {
        if (!playAG) {
            playAG = true;
            motion.play();
        }
    }

    function initButtons() {
        $('.non2').on(touchstart,
            function() {
                $('.nonPic').hide();
                $('.falsePic').hide();
                playAgain();

            });

        $('.btn1').on(touchstart,
            function() {
                $('.falsePic').hide();
                playAgain();
            });

        $('.btn2').on(touchstart,
            function() {
                $('.nonPic').show();
            });

        $('#falsePic').on(touchstart,
            function() {
                $('.falsePic').show();
                $('.shandong').hide();
                _czc.push(["_trackEvent", "按钮", "QQ红包", "QQ红包", 0, "urlPic"]);
            });

        $('#urlPic').on(touchstart,
            function() {

                $('.falsePic2').show();
                TweenLite.to('#falsePic', 1.5, {
                    onComplete: function() {
                        location.href = 'giftpackage.html';
                    }
                });

                _czc.push(["_trackEvent", "按钮", "渠道红包", "渠道红包", 0, "urlPic"]);
                $(this).off(touchstart);
            })
    }
});