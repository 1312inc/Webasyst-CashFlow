/**
 * var long = $.wa.kmLongAction();
 * long.start({
            process_url: 'url_to_walongaction_controller',
            start: {
                data: { id: some_id }
            }
        });
 */
(function ($) {
    "use strict";
    $.wa.kmLongAction = function () {

        var data = {},
            processId = 0,
            stopFlag = 0,
            pauseFlag = 0,
            timerQueue = [];

        var options = {
            process_url: null, // endpoint
            delay: 2000, // between requests
            parallel: 1, // how many steps to run
            debug: false,
            afterCleanUp: function () {}, // after all done and all timeres trashed
            onServerError: function (response) {},
            // start related options
            start: {
                data: {}, // data to be passed to url
                onStart: function () {}, // just after start

                // inside callback after first response come
                onBegin: function (response) {}, // first line
                onEnd: function (response) {}, // last line
                onSuccess: function (response) {}, // success response
                onError: function (response) {}, // error response
                onUnknown: function (response) {} // some unknown happened
            },
            // step related options
            step: {
                data: {}, // data to be passed to url
                responseParams: {
                    ready: 'ready',
                    error: 'error',
                    progress: 'progress',
                    pause: 'pause'
                },
                stopData: {'stop': 1}, // stop param
                pauseData: {'pause': 1}, // pause param
                immediately: false, // start steps immediately after 'start' succeeds
                repeatOnError: 3, // how many times to repeat step after error comes

                // inside step post callback
                onBegin: function (response) {},  // first line @ step
                onEnd: function (response) {}, // last line @ step
                onNo: function (response) { // no response come...
                    return true; // should return true to step after
                },
                onReady: function (response) {}, // on 'ready' param come in response
                onProgress: function (response) {}, // progressando
                onPause: function (response) {}, // on 'pause' param come in response
                onStop: function (response) {}, // on 'stop' param come in response
                onUnknown: function (response) {},  // some unknown params in response
                onError: function (response) {}, // on 'error' param come in response
                onCustom: function (response) { // custom callback
                    return false; // should return false to go to the next condition, or true to break it
                }
            }
        };

        function _cleanUp() {
            _debug('cleanUp');
            processId = null;
            stopFlag = 0;
            pauseFlag = 0;
            var timer_id = timerQueue.pop();
            while (timer_id) {
                clearTimeout(timer_id);
                timer_id = timerQueue.pop();
            }
            timerQueue = [];
            if ($.isFunction(data.afterCleanUp)) {
                data.afterCleanUp();
            }
        }

        function _step(delay) {
            delay = delay || data.delay;

            var repeatOnError = data.step.repeatOnError;

            if (!Date.now) {
                Date.now = function () {
                    return new Date().getTime();
                };
            }

            var timer_id = setTimeout(function () {
                var step_data = $.extend(
                    true,
                    {},
                    data.step.data,
                    pauseFlag ? data.step.pauseData : {},
                    stopFlag ? data.step.stopData : {},
                    {
                        processId: processId,
                        ts: Date.now()
                    });

                _debug({'step start': step_data});

                $.post(data.process_url, step_data, function (r) {
                    if ($.isFunction(data.step.onBegin)) {
                        data.step.onBegin(r);
                    }

                    if (!r) {
                        _debug('step: something wrong - no response');
                        if ($.isFunction(data.step.onNo) && data.step.onNo(r)) {
                            _step(data.delay * 2);
                        }
                    } else if (data.step.onCustom && $.isFunction(data.step.onCustom) && data.step.onCustom(r)) {
                        _debug({'step: custom callback': r});
                    } else if (r[data.step.responseParams.ready]) {
                        _debug({'step: all done': r});
                        if ($.isFunction(data.step.onReady)) {
                            data.step.onReady(r)
                        }
                        _cleanUp();
                    } else if (r[data.step.responseParams.error]) {
                        _debug({'step: error': r});
                        if ($.isFunction(data.step.onError)) {
                            data.step.onError(r);
                        }
                        if (repeatOnError--) {
                            _step(delay * 3);
                        } else {
                            _cleanUp();
                        }
                    } else if (r[data.step.responseParams.pause]) {
                        _debug({'step: in pause': r});
                        if ($.isFunction(data.step.onPause)) {
                            data.step.onPause(r);
                        }
                        _step(delay * 2); // next step
                    } else if (r[data.step.responseParams.progress]) {
                        _debug({'step: in progress': r});
                        if ($.isFunction(data.step.onProgress)) {
                            data.step.onProgress(r);
                        }
                        repeatOnError = data.step.repeatOnError;
                        _step(delay); // next step
                    } else {
                        _debug({'step: some unknown': r});
                        if ($.isFunction(data.step.onUnknown)) {
                            data.step.onUnknown(r);
                        }
                        if (repeatOnError--) {
                            _step(delay * 3);
                        } else {
                            _cleanUp();
                        }
                    }

                    if ($.isFunction(data.step.onEnd)) {
                        data.step.onEnd(r);
                    }
                }, 'json').fail(function () {
                    _debug('step: server error');
                    if ($.isFunction(data.onServerError)) {
                        data.onServerError();
                    }
                    if (repeatOnError--) {
                        _step(delay * 3);
                    } else {
                        _cleanUp();
                    }
                });
            }, delay);
            timerQueue.push(timer_id);
        }

        function _debug(msg) {
            if (console.log && data.debug) {
                console.log({'kmLongAction': msg});
            }
        }

        return {
            // start all process
            start: function (o) {
                data = $.extend(true, {}, options, o);

                if ($.isFunction(data.start.onStart)) {
                    data.start.onStart();
                }

                if (!data.process_url) {
                    return false;
                }

                _debug('post to long action');
                $.post(data.process_url, data.start.data, function (r) {
                    _debug({'start: post ok': r});
                    if ($.isFunction(data.start.onBegin)) {
                        data.start.onBegin(r);
                    }

                    _debug({'start: after init()': r});
                    if (r && r.processId) {
                        processId = r.processId;
                        if ($.isFunction(data.start.onSuccess)) {
                            data.start.onSuccess(r);
                        }
                        for (var i = 1; i <= data.parallel; i++) {
                            _step(data.step.immediately ? 0 : data.delay * i);
                        }
                    } else if (r && r.error) {
                        _debug({'start: error': r});
                        if ($.isFunction(data.start.onError)) {
                            data.start.onError(r);
                        }
                    } else {
                        _debug({'start: some error': r});
                        if ($.isFunction(data.start.onUnknown)) {
                            data.start.onUnknown(r);
                        }
                        _cleanUp();
                    }

                    if ($.isFunction(data.start.onEnd)) {
                        data.start.onEnd(r);
                    }
                }, 'json').fail(function () {
                    _debug('start: post fail');
                    if ($.isFunction(data.onServerError)) {
                        data.onServerError();
                    }
                    _cleanUp();
                });
            },
            step: _step,
            stop: function () { // will send stop data with next step
                if (processId) {
                    stopFlag = 1;
                }
            },
            halt: _cleanUp,
            pause: function () { // will send pause data with next step
                pauseFlag = 1;
            },
            continue: function () {
                pauseFlag = 0;
            }
        }
    };
})(jQuery);
