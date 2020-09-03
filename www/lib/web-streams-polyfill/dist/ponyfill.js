/**
 * web-streams-polyfill v2.1.1
 */
(function (global, factory) {
    typeof exports === 'object' && typeof module !== 'undefined' ? factory(exports) :
    typeof define === 'function' && define.amd ? define(['exports'], factory) :
    (global = global || self, factory(global.WebStreamsPolyfill = {}));
}(this, (function (exports) { 'use strict';

    /// <reference lib="es2015.symbol" />
    var SymbolPolyfill = typeof Symbol === 'function' && typeof Symbol.iterator === 'symbol' ?
        Symbol :
        function (description) { return "Symbol(" + description + ")"; };

    /// <reference lib="dom" />
    function noop() {
        // do nothing
    }

    /// <reference lib="es2015.core" />
    // https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Number/isNaN#Polyfill
    var NumberIsNaN = Number.isNaN || function (x) {
        // eslint-disable-next-line no-self-compare
        return x !== x;
    };

    var rethrowAssertionErrorRejection =  noop;

    function typeIsObject(x) {
        return (typeof x === 'object' && x !== null) || typeof x === 'function';
    }
    function createArrayFromList(elements) {
        // We use arrays to represent lists, so this is basically a no-op.
        // Do a slice though just in case we happen to depend on the unique-ness.
        return elements.slice();
    }
    function ArrayBufferCopy(dest, destOffset, src, srcOffset, n) {
        new Uint8Array(dest).set(new Uint8Array(src, srcOffset, n), destOffset);
    }
    function IsFiniteNonNegativeNumber(v) {
        if (IsNonNegativeNumber(v) === false) {
            return false;
        }
        if (v === Infinity) {
            return false;
        }
        return true;
    }
    function IsNonNegativeNumber(v) {
        if (typeof v !== 'number') {
            return false;
        }
        if (NumberIsNaN(v)) {
            return false;
        }
        if (v < 0) {
            return false;
        }
        return true;
    }
    function Call(F, V, args) {
        if (typeof F !== 'function') {
            throw new TypeError('Argument is not a function');
        }
        return Function.prototype.apply.call(F, V, args);
    }
    function CreateAlgorithmFromUnderlyingMethod(underlyingObject, methodName, algoArgCount, extraArgs) {
        var method = underlyingObject[methodName];
        if (method !== undefined) {
            if (typeof method !== 'function') {
                throw new TypeError(method + " is not a method");
            }
            switch (algoArgCount) {
                case 0: {
                    return function () {
                        return PromiseCall(method, underlyingObject, extraArgs);
                    };
                }
                case 1: {
                    return function (arg) {
                        var fullArgs = [arg].concat(extraArgs);
                        return PromiseCall(method, underlyingObject, fullArgs);
                    };
                }
            }
        }
        return function () { return promiseResolvedWith(undefined); };
    }
    function InvokeOrNoop(O, P, args) {
        var method = O[P];
        if (method === undefined) {
            return undefined;
        }
        return Call(method, O, args);
    }
    function PromiseCall(F, V, args) {
        try {
            return promiseResolvedWith(Call(F, V, args));
        }
        catch (value) {
            return promiseRejectedWith(value);
        }
    }
    // Not implemented correctly
    function TransferArrayBuffer(O) {
        return O;
    }
    // Not implemented correctly
    function IsDetachedBuffer(O) {
        return false;
    }
    function ValidateAndNormalizeHighWaterMark(highWaterMark) {
        highWaterMark = Number(highWaterMark);
        if (NumberIsNaN(highWaterMark) || highWaterMark < 0) {
            throw new RangeError('highWaterMark property of a queuing strategy must be non-negative and non-NaN');
        }
        return highWaterMark;
    }
    function MakeSizeAlgorithmFromSizeFunction(size) {
        if (size === undefined) {
            return function () { return 1; };
        }
        if (typeof size !== 'function') {
            throw new TypeError('size property of a queuing strategy must be a function');
        }
        return function (chunk) { return size(chunk); };
    }
    var originalPromise = Promise;
    var originalPromiseThen = Promise.prototype.then;
    var originalPromiseResolve = Promise.resolve.bind(originalPromise);
    var originalPromiseReject = Promise.reject.bind(originalPromise);
    function newPromise(executor) {
        return new originalPromise(executor);
    }
    function promiseResolvedWith(value) {
        return originalPromiseResolve(value);
    }
    function promiseRejectedWith(reason) {
        return originalPromiseReject(reason);
    }
    function PerformPromiseThen(promise, onFulfilled, onRejected) {
        // There doesn't appear to be any way to correctly emulate the behaviour from JavaScript, so this is just an
        // approximation.
        return originalPromiseThen.call(promise, onFulfilled, onRejected);
    }
    function uponPromise(promise, onFulfilled, onRejected) {
        PerformPromiseThen(PerformPromiseThen(promise, onFulfilled, onRejected), undefined, rethrowAssertionErrorRejection);
    }
    function uponFulfillment(promise, onFulfilled) {
        uponPromise(promise, onFulfilled);
    }
    function uponRejection(promise, onRejected) {
        uponPromise(promise, undefined, onRejected);
    }
    function transformPromiseWith(promise, fulfillmentHandler, rejectionHandler) {
        return PerformPromiseThen(promise, fulfillmentHandler, rejectionHandler);
    }
    function setPromiseIsHandledToTrue(promise) {
        PerformPromiseThen(promise, undefined, rethrowAssertionErrorRejection);
    }

    // Original from Chromium
    // https://chromium.googlesource.com/chromium/src/+/0aee4434a4dba42a42abaea9bfbc0cd196a63bc1/third_party/blink/renderer/core/streams/SimpleQueue.js
    var QUEUE_MAX_ARRAY_SIZE = 16384;
    /**
     * Simple queue structure.
     *
     * Avoids scalability issues with using a packed array directly by using
     * multiple arrays in a linked list and keeping the array size bounded.
     */
    var SimpleQueue = /** @class */ (function () {
        function SimpleQueue() {
            this._cursor = 0;
            this._size = 0;
            // _front and _back are always defined.
            this._front = {
                _elements: [],
                _next: undefined
            };
            this._back = this._front;
            // The cursor is used to avoid calling Array.shift().
            // It contains the index of the front element of the array inside the
            // front-most node. It is always in the range [0, QUEUE_MAX_ARRAY_SIZE).
            this._cursor = 0;
            // When there is only one node, size === elements.length - cursor.
            this._size = 0;
        }
        Object.defineProperty(SimpleQueue.prototype, "length", {
            get: function () {
                return this._size;
            },
            enumerable: true,
            configurable: true
        });
        // For exception safety, this method is structured in order:
        // 1. Read state
        // 2. Calculate required state mutations
        // 3. Perform state mutations
        SimpleQueue.prototype.push = function (element) {
            var oldBack = this._back;
            var newBack = oldBack;
            if (oldBack._elements.length === QUEUE_MAX_ARRAY_SIZE - 1) {
                newBack = {
                    _elements: [],
                    _next: undefined
                };
            }
            // push() is the mutation most likely to throw an exception, so it
            // goes first.
            oldBack._elements.push(element);
            if (newBack !== oldBack) {
                this._back = newBack;
                oldBack._next = newBack;
            }
            ++this._size;
        };
        // Like push(), shift() follows the read -> calculate -> mutate pattern for
        // exception safety.
        SimpleQueue.prototype.shift = function () { // must not be called on an empty queue
            var oldFront = this._front;
            var newFront = oldFront;
            var oldCursor = this._cursor;
            var newCursor = oldCursor + 1;
            var elements = oldFront._elements;
            var element = elements[oldCursor];
            if (newCursor === QUEUE_MAX_ARRAY_SIZE) {
                newFront = oldFront._next;
                newCursor = 0;
            }
            // No mutations before this point.
            --this._size;
            this._cursor = newCursor;
            if (oldFront !== newFront) {
                this._front = newFront;
            }
            // Permit shifted element to be garbage collected.
            elements[oldCursor] = undefined;
            return element;
        };
        // The tricky thing about forEach() is that it can be called
        // re-entrantly. The queue may be mutated inside the callback. It is easy to
        // see that push() within the callback has no negative effects since the end
        // of the queue is checked for on every iteration. If shift() is called
        // repeatedly within the callback then the next iteration may return an
        // element that has been removed. In this case the callback will be called
        // with undefined values until we either "catch up" with elements that still
        // exist or reach the back of the queue.
        SimpleQueue.prototype.forEach = function (callback) {
            var i = this._cursor;
            var node = this._front;
            var elements = node._elements;
            while (i !== elements.length || node._next !== undefined) {
                if (i === elements.length) {
                    node = node._next;
                    elements = node._elements;
                    i = 0;
                    if (elements.length === 0) {
                        break;
                    }
                }
                callback(elements[i]);
                ++i;
            }
        };
        // Return the element that would be returned if shift() was called now,
        // without modifying the queue.
        SimpleQueue.prototype.peek = function () { // must not be called on an empty queue
            var front = this._front;
            var cursor = this._cursor;
            return front._elements[cursor];
        };
        return SimpleQueue;
    }());

    function ReadableStreamCreateReadResult(value, done, forAuthorCode) {
        var prototype = null;
        if (forAuthorCode === true) {
            prototype = Object.prototype;
        }
        var obj = Object.create(prototype);
        obj.value = value;
        obj.done = done;
        return obj;
    }
    function ReadableStreamReaderGenericInitialize(reader, stream) {
        reader._forAuthorCode = true;
        reader._ownerReadableStream = stream;
        stream._reader = reader;
        if (stream._state === 'readable') {
            defaultReaderClosedPromiseInitialize(reader);
        }
        else if (stream._state === 'closed') {
            defaultReaderClosedPromiseInitializeAsResolved(reader);
        }
        else {
            defaultReaderClosedPromiseInitializeAsRejected(reader, stream._storedError);
        }
    }
    // A client of ReadableStreamDefaultReader and ReadableStreamBYOBReader may use these functions directly to bypass state
    // check.
    function ReadableStreamReaderGenericCancel(reader, reason) {
        var stream = reader._ownerReadableStream;
        return ReadableStreamCancel(stream, reason);
    }
    function ReadableStreamReaderGenericRelease(reader) {
        if (reader._ownerReadableStream._state === 'readable') {
            defaultReaderClosedPromiseReject(reader, new TypeError('Reader was released and can no longer be used to monitor the stream\'s closedness'));
        }
        else {
            defaultReaderClosedPromiseResetToRejected(reader, new TypeError('Reader was released and can no longer be used to monitor the stream\'s closedness'));
        }
        reader._ownerReadableStream._reader = undefined;
        reader._ownerReadableStream = undefined;
    }
    // Helper functions for the readers.
    function readerLockException(name) {
        return new TypeError('Cannot ' + name + ' a stream using a released reader');
    }
    // Helper functions for the ReadableStreamDefaultReader.
    function defaultReaderClosedPromiseInitialize(reader) {
        reader._closedPromise = newPromise(function (resolve, reject) {
            reader._closedPromise_resolve = resolve;
            reader._closedPromise_reject = reject;
        });
    }
    function defaultReaderClosedPromiseInitializeAsRejected(reader, reason) {
        defaultReaderClosedPromiseInitialize(reader);
        defaultReaderClosedPromiseReject(reader, reason);
    }
    function defaultReaderClosedPromiseInitializeAsResolved(reader) {
        defaultReaderClosedPromiseInitialize(reader);
        defaultReaderClosedPromiseResolve(reader);
    }
    function defaultReaderClosedPromiseReject(reader, reason) {
        setPromiseIsHandledToTrue(reader._closedPromise);
        reader._closedPromise_reject(reason);
        reader._closedPromise_resolve = undefined;
        reader._closedPromise_reject = undefined;
    }
    function defaultReaderClosedPromiseResetToRejected(reader, reason) {
        defaultReaderClosedPromiseInitializeAsRejected(reader, reason);
    }
    function defaultReaderClosedPromiseResolve(reader) {
        reader._closedPromise_resolve(undefined);
        reader._closedPromise_resolve = undefined;
        reader._closedPromise_reject = undefined;
    }

    var CancelSteps = SymbolPolyfill('[[CancelSteps]]');
    var PullSteps = SymbolPolyfill('[[PullSteps]]');

    // Abstract operations for the ReadableStream.
    function AcquireReadableStreamDefaultReader(stream, forAuthorCode) {
        if (forAuthorCode === void 0) { forAuthorCode = false; }
        var reader = new ReadableStreamDefaultReader(stream);
        reader._forAuthorCode = forAuthorCode;
        return reader;
    }
    // ReadableStream API exposed for controllers.
    function ReadableStreamAddReadRequest(stream) {
        var promise = newPromise(function (resolve, reject) {
            var readRequest = {
                _resolve: resolve,
                _reject: reject
            };
            stream._reader._readRequests.push(readRequest);
        });
        return promise;
    }
    function ReadableStreamFulfillReadRequest(stream, chunk, done) {
        var reader = stream._reader;
        var readRequest = reader._readRequests.shift();
        readRequest._resolve(ReadableStreamCreateReadResult(chunk, done, reader._forAuthorCode));
    }
    function ReadableStreamGetNumReadRequests(stream) {
        return stream._reader._readRequests.length;
    }
    function ReadableStreamHasDefaultReader(stream) {
        var reader = stream._reader;
        if (reader === undefined) {
            return false;
        }
        if (!IsReadableStreamDefaultReader(reader)) {
            return false;
        }
        return true;
    }
    var ReadableStreamDefaultReader = /** @class */ (function () {
        function ReadableStreamDefaultReader(stream) {
            if (IsReadableStream(stream) === false) {
                throw new TypeError('ReadableStreamDefaultReader can only be constructed with a ReadableStream instance');
            }
            if (IsReadableStreamLocked(stream) === true) {
                throw new TypeError('This stream has already been locked for exclusive reading by another reader');
            }
            ReadableStreamReaderGenericInitialize(this, stream);
            this._readRequests = new SimpleQueue();
        }
        Object.defineProperty(ReadableStreamDefaultReader.prototype, "closed", {
            get: function () {
                if (!IsReadableStreamDefaultReader(this)) {
                    return promiseRejectedWith(defaultReaderBrandCheckException('closed'));
                }
                return this._closedPromise;
            },
            enumerable: true,
            configurable: true
        });
        ReadableStreamDefaultReader.prototype.cancel = function (reason) {
            if (!IsReadableStreamDefaultReader(this)) {
                return promiseRejectedWith(defaultReaderBrandCheckException('cancel'));
            }
            if (this._ownerReadableStream === undefined) {
                return promiseRejectedWith(readerLockException('cancel'));
            }
            return ReadableStreamReaderGenericCancel(this, reason);
        };
        ReadableStreamDefaultReader.prototype.read = function () {
            if (!IsReadableStreamDefaultReader(this)) {
                return promiseRejectedWith(defaultReaderBrandCheckException('read'));
            }
            if (this._ownerReadableStream === undefined) {
                return promiseRejectedWith(readerLockException('read from'));
            }
            return ReadableStreamDefaultReaderRead(this);
        };
        ReadableStreamDefaultReader.prototype.releaseLock = function () {
            if (!IsReadableStreamDefaultReader(this)) {
                throw defaultReaderBrandCheckException('releaseLock');
            }
            if (this._ownerReadableStream === undefined) {
                return;
            }
            if (this._readRequests.length > 0) {
                throw new TypeError('Tried to release a reader lock when that reader has pending read() calls un-settled');
            }
            ReadableStreamReaderGenericRelease(this);
        };
        return ReadableStreamDefaultReader;
    }());
    // Abstract operations for the readers.
    function IsReadableStreamDefaultReader(x) {
        if (!typeIsObject(x)) {
            return false;
        }
        if (!Object.prototype.hasOwnProperty.call(x, '_readRequests')) {
            return false;
        }
        return true;
    }
    function ReadableStreamDefaultReaderRead(reader) {
        var stream = reader._ownerReadableStream;
        stream._disturbed = true;
        if (stream._state === 'closed') {
            return promiseResolvedWith(ReadableStreamCreateReadResult(undefined, true, reader._forAuthorCode));
        }
        if (stream._state === 'errored') {
            return promiseRejectedWith(stream._storedError);
        }
        return stream._readableStreamController[PullSteps]();
    }
    // Helper functions for the ReadableStreamDefaultReader.
    function defaultReaderBrandCheckException(name) {
        return new TypeError("ReadableStreamDefaultReader.prototype." + name + " can only be used on a ReadableStreamDefaultReader");
    }

    /// <reference lib="es2018.asynciterable" />
    var _a;
    var AsyncIteratorPrototype;
    if (typeof SymbolPolyfill.asyncIterator === 'symbol') {
        // We're running inside a ES2018+ environment, but we're compiling to an older syntax.
        // We cannot access %AsyncIteratorPrototype% without non-ES2018 syntax, but we can re-create it.
        AsyncIteratorPrototype = (_a = {},
            // 25.1.3.1 %AsyncIteratorPrototype% [ @@asyncIterator ] ( )
            // https://tc39.github.io/ecma262/#sec-asynciteratorprototype-asynciterator
            _a[SymbolPolyfill.asyncIterator] = function () {
                return this;
            },
            _a);
        Object.defineProperty(AsyncIteratorPrototype, SymbolPolyfill.asyncIterator, { enumerable: false });
    }

    /// <reference lib="es2018.asynciterable" />
    var ReadableStreamAsyncIteratorPrototype = {
        next: function () {
            if (IsReadableStreamAsyncIterator(this) === false) {
                return promiseRejectedWith(streamAsyncIteratorBrandCheckException('next'));
            }
            var reader = this._asyncIteratorReader;
            if (reader._ownerReadableStream === undefined) {
                return promiseRejectedWith(readerLockException('iterate'));
            }
            return transformPromiseWith(ReadableStreamDefaultReaderRead(reader), function (result) {
                var done = result.done;
                if (done) {
                    ReadableStreamReaderGenericRelease(reader);
                }
                var value = result.value;
                return ReadableStreamCreateReadResult(value, done, true);
            });
        },
        return: function (value) {
            if (IsReadableStreamAsyncIterator(this) === false) {
                return promiseRejectedWith(streamAsyncIteratorBrandCheckException('next'));
            }
            var reader = this._asyncIteratorReader;
            if (reader._ownerReadableStream === undefined) {
                return promiseRejectedWith(readerLockException('finish iterating'));
            }
            if (reader._readRequests.length > 0) {
                return promiseRejectedWith(new TypeError('Tried to release a reader lock when that reader has pending read() calls un-settled'));
            }
            if (this._preventCancel === false) {
                var result = ReadableStreamReaderGenericCancel(reader, value);
                ReadableStreamReaderGenericRelease(reader);
                return transformPromiseWith(result, function () { return ReadableStreamCreateReadResult(value, true, true); });
            }
            ReadableStreamReaderGenericRelease(reader);
            return promiseResolvedWith(ReadableStreamCreateReadResult(value, true, true));
        }
    };
    if (AsyncIteratorPrototype !== undefined) {
        Object.setPrototypeOf(ReadableStreamAsyncIteratorPrototype, AsyncIteratorPrototype);
    }
    Object.defineProperty(ReadableStreamAsyncIteratorPrototype, 'next', { enumerable: false });
    Object.defineProperty(ReadableStreamAsyncIteratorPrototype, 'return', { enumerable: false });
    // Abstract operations for the ReadableStream.
    function AcquireReadableStreamAsyncIterator(stream, preventCancel) {
        if (preventCancel === void 0) { preventCancel = false; }
        var reader = AcquireReadableStreamDefaultReader(stream);
        var iterator = Object.create(ReadableStreamAsyncIteratorPrototype);
        iterator._asyncIteratorReader = reader;
        iterator._preventCancel = Boolean(preventCancel);
        return iterator;
    }
    function IsReadableStreamAsyncIterator(x) {
        if (!typeIsObject(x)) {
            return false;
        }
        if (!Object.prototype.hasOwnProperty.call(x, '_asyncIteratorReader')) {
            return false;
        }
        return true;
    }
    // Helper functions for the ReadableStream.
    function streamAsyncIteratorBrandCheckException(name) {
        return new TypeError("ReadableStreamAsyncIterator." + name + " can only be used on a ReadableSteamAsyncIterator");
    }

    function DequeueValue(container) {
        var pair = container._queue.shift();
        container._queueTotalSize -= pair.size;
        if (container._queueTotalSize < 0) {
            container._queueTotalSize = 0;
        }
        return pair.value;
    }
    function EnqueueValueWithSize(container, value, size) {
        size = Number(size);
        if (!IsFiniteNonNegativeNumber(size)) {
            throw new RangeError('Size must be a finite, non-NaN, non-negative number.');
        }
        container._queue.push({ value: value, size: size });
        container._queueTotalSize += size;
    }
    function PeekQueueValue(container) {
        var pair = container._queue.peek();
        return pair.value;
    }
    function ResetQueue(container) {
        container._queue = new SimpleQueue();
        container._queueTotalSize = 0;
    }

    var AbortSteps = SymbolPolyfill('[[AbortSteps]]');
    var ErrorSteps = SymbolPolyfill('[[ErrorSteps]]');
    var WritableStream = /** @class */ (function () {
        function WritableStream(underlyingSink, strategy) {
            if (underlyingSink === void 0) { underlyingSink = {}; }
            if (strategy === void 0) { strategy = {}; }
            InitializeWritableStream(this);
            var size = strategy.size;
            var highWaterMark = strategy.highWaterMark;
            var type = underlyingSink.type;
            if (type !== undefined) {
                throw new RangeError('Invalid type is specified');
            }
            var sizeAlgorithm = MakeSizeAlgorithmFromSizeFunction(size);
            if (highWaterMark === undefined) {
                highWaterMark = 1;
            }
            highWaterMark = ValidateAndNormalizeHighWaterMark(highWaterMark);
            SetUpWritableStreamDefaultControllerFromUnderlyingSink(this, underlyingSink, highWaterMark, sizeAlgorithm);
        }
        Object.defineProperty(WritableStream.prototype, "locked", {
            get: function () {
                if (IsWritableStream(this) === false) {
                    throw streamBrandCheckException('locked');
                }
                return IsWritableStreamLocked(this);
            },
            enumerable: true,
            configurable: true
        });
        WritableStream.prototype.abort = function (reason) {
            if (IsWritableStream(this) === false) {
                return promiseRejectedWith(streamBrandCheckException('abort'));
            }
            if (IsWritableStreamLocked(this) === true) {
                return promiseRejectedWith(new TypeError('Cannot abort a stream that already has a writer'));
            }
            return WritableStreamAbort(this, reason);
        };
        WritableStream.prototype.close = function () {
            if (IsWritableStream(this) === false) {
                return promiseRejectedWith(streamBrandCheckException('close'));
            }
            if (IsWritableStreamLocked(this) === true) {
                return promiseRejectedWith(new TypeError('Cannot close a stream that already has a writer'));
            }
            if (WritableStreamCloseQueuedOrInFlight(this) === true) {
                return promiseRejectedWith(new TypeError('Cannot close an already-closing stream'));
            }
            return WritableStreamClose(this);
        };
        WritableStream.prototype.getWriter = function () {
            if (IsWritableStream(this) === false) {
                throw streamBrandCheckException('getWriter');
            }
            return AcquireWritableStreamDefaultWriter(this);
        };
        return WritableStream;
    }());
    // Abstract operations for the WritableStream.
    function AcquireWritableStreamDefaultWriter(stream) {
        return new WritableStreamDefaultWriter(stream);
    }
    // Throws if and only if startAlgorithm throws.
    function CreateWritableStream(startAlgorithm, writeAlgorithm, closeAlgorithm, abortAlgorithm, highWaterMark, sizeAlgorithm) {
        if (highWaterMark === void 0) { highWaterMark = 1; }
        if (sizeAlgorithm === void 0) { sizeAlgorithm = function () { return 1; }; }
        var stream = Object.create(WritableStream.prototype);
        InitializeWritableStream(stream);
        var controller = Object.create(WritableStreamDefaultController.prototype);
        SetUpWritableStreamDefaultController(stream, controller, startAlgorithm, writeAlgorithm, closeAlgorithm, abortAlgorithm, highWaterMark, sizeAlgorithm);
        return stream;
    }
    function InitializeWritableStream(stream) {
        stream._state = 'writable';
        // The error that will be reported by new method calls once the state becomes errored. Only set when [[state]] is
        // 'erroring' or 'errored'. May be set to an undefined value.
        stream._storedError = undefined;
        stream._writer = undefined;
        // Initialize to undefined first because the constructor of the controller checks this
        // variable to validate the caller.
        stream._writableStreamController = undefined;
        // This queue is placed here instead of the writer class in order to allow for passing a writer to the next data
        // producer without waiting for the queued writes to finish.
        stream._writeRequests = new SimpleQueue();
        // Write requests are removed from _writeRequests when write() is called on the underlying sink. This prevents
        // them from being erroneously rejected on error. If a write() call is in-flight, the request is stored here.
        stream._inFlightWriteRequest = undefined;
        // The promise that was returned from writer.close(). Stored here because it may be fulfilled after the writer
        // has been detached.
        stream._closeRequest = undefined;
        // Close request is removed from _closeRequest when close() is called on the underlying sink. This prevents it
        // from being erroneously rejected on error. If a close() call is in-flight, the request is stored here.
        stream._inFlightCloseRequest = undefined;
        // The promise that was returned from writer.abort(). This may also be fulfilled after the writer has detached.
        stream._pendingAbortRequest = undefined;
        // The backpressure signal set by the controller.
        stream._backpressure = false;
    }
    function IsWritableStream(x) {
        if (!typeIsObject(x)) {
            return false;
        }
        if (!Object.prototype.hasOwnProperty.call(x, '_writableStreamController')) {
            return false;
        }
        return true;
    }
    function IsWritableStreamLocked(stream) {
        if (stream._writer === undefined) {
            return false;
        }
        return true;
    }
    function WritableStreamAbort(stream, reason) {
        var state = stream._state;
        if (state === 'closed' || state === 'errored') {
            return promiseResolvedWith(undefined);
        }
        if (stream._pendingAbortRequest !== undefined) {
            return stream._pendingAbortRequest._promise;
        }
        var wasAlreadyErroring = false;
        if (state === 'erroring') {
            wasAlreadyErroring = true;
            // reason will not be used, so don't keep a reference to it.
            reason = undefined;
        }
        var promise = newPromise(function (resolve, reject) {
            stream._pendingAbortRequest = {
                _promise: undefined,
                _resolve: resolve,
                _reject: reject,
                _reason: reason,
                _wasAlreadyErroring: wasAlreadyErroring
            };
        });
        stream._pendingAbortRequest._promise = promise;
        if (wasAlreadyErroring === false) {
            WritableStreamStartErroring(stream, reason);
        }
        return promise;
    }
    function WritableStreamClose(stream) {
        var state = stream._state;
        if (state === 'closed' || state === 'errored') {
            return promiseRejectedWith(new TypeError("The stream (in " + state + " state) is not in the writable state and cannot be closed"));
        }
        var promise = newPromise(function (resolve, reject) {
            var closeRequest = {
                _resolve: resolve,
                _reject: reject
            };
            stream._closeRequest = closeRequest;
        });
        var writer = stream._writer;
        if (writer !== undefined && stream._backpressure === true && state === 'writable') {
            defaultWriterReadyPromiseResolve(writer);
        }
        WritableStreamDefaultControllerClose(stream._writableStreamController);
        return promise;
    }
    // WritableStream API exposed for controllers.
    function WritableStreamAddWriteRequest(stream) {
        var promise = newPromise(function (resolve, reject) {
            var writeRequest = {
                _resolve: resolve,
                _reject: reject
            };
            stream._writeRequests.push(writeRequest);
        });
        return promise;
    }
    function WritableStreamDealWithRejection(stream, error) {
        var state = stream._state;
        if (state === 'writable') {
            WritableStreamStartErroring(stream, error);
            return;
        }
        WritableStreamFinishErroring(stream);
    }
    function WritableStreamStartErroring(stream, reason) {
        var controller = stream._writableStreamController;
        stream._state = 'erroring';
        stream._storedError = reason;
        var writer = stream._writer;
        if (writer !== undefined) {
            WritableStreamDefaultWriterEnsureReadyPromiseRejected(writer, reason);
        }
        if (WritableStreamHasOperationMarkedInFlight(stream) === false && controller._started === true) {
            WritableStreamFinishErroring(stream);
        }
    }
    function WritableStreamFinishErroring(stream) {
        stream._state = 'errored';
        stream._writableStreamController[ErrorSteps]();
        var storedError = stream._storedError;
        stream._writeRequests.forEach(function (writeRequest) {
            writeRequest._reject(storedError);
        });
        stream._writeRequests = new SimpleQueue();
        if (stream._pendingAbortRequest === undefined) {
            WritableStreamRejectCloseAndClosedPromiseIfNeeded(stream);
            return;
        }
        var abortRequest = stream._pendingAbortRequest;
        stream._pendingAbortRequest = undefined;
        if (abortRequest._wasAlreadyErroring === true) {
            abortRequest._reject(storedError);
            WritableStreamRejectCloseAndClosedPromiseIfNeeded(stream);
            return;
        }
        var promise = stream._writableStreamController[AbortSteps](abortRequest._reason);
        uponPromise(promise, function () {
            abortRequest._resolve();
            WritableStreamRejectCloseAndClosedPromiseIfNeeded(stream);
        }, function (reason) {
            abortRequest._reject(reason);
            WritableStreamRejectCloseAndClosedPromiseIfNeeded(stream);
        });
    }
    function WritableStreamFinishInFlightWrite(stream) {
        stream._inFlightWriteRequest._resolve(undefined);
        stream._inFlightWriteRequest = undefined;
    }
    function WritableStreamFinishInFlightWriteWithError(stream, error) {
        stream._inFlightWriteRequest._reject(error);
        stream._inFlightWriteRequest = undefined;
        WritableStreamDealWithRejection(stream, error);
    }
    function WritableStreamFinishInFlightClose(stream) {
        stream._inFlightCloseRequest._resolve(undefined);
        stream._inFlightCloseRequest = undefined;
        var state = stream._state;
        if (state === 'erroring') {
            // The error was too late to do anything, so it is ignored.
            stream._storedError = undefined;
            if (stream._pendingAbortRequest !== undefined) {
                stream._pendingAbortRequest._resolve();
                stream._pendingAbortRequest = undefined;
            }
        }
        stream._state = 'closed';
        var writer = stream._writer;
        if (writer !== undefined) {
            defaultWriterClosedPromiseResolve(writer);
        }
    }
    function WritableStreamFinishInFlightCloseWithError(stream, error) {
        stream._inFlightCloseRequest._reject(error);
        stream._inFlightCloseRequest = undefined;
        // Never execute sink abort() after sink close().
        if (stream._pendingAbortRequest !== undefined) {
            stream._pendingAbortRequest._reject(error);
            stream._pendingAbortRequest = undefined;
        }
        WritableStreamDealWithRejection(stream, error);
    }
    // TODO(ricea): Fix alphabetical order.
    function WritableStreamCloseQueuedOrInFlight(stream) {
        if (stream._closeRequest === undefined && stream._inFlightCloseRequest === undefined) {
            return false;
        }
        return true;
    }
    function WritableStreamHasOperationMarkedInFlight(stream) {
        if (stream._inFlightWriteRequest === undefined && stream._inFlightCloseRequest === undefined) {
            return false;
        }
        return true;
    }
    function WritableStreamMarkCloseRequestInFlight(stream) {
        stream._inFlightCloseRequest = stream._closeRequest;
        stream._closeRequest = undefined;
    }
    function WritableStreamMarkFirstWriteRequestInFlight(stream) {
        stream._inFlightWriteRequest = stream._writeRequests.shift();
    }
    function WritableStreamRejectCloseAndClosedPromiseIfNeeded(stream) {
        if (stream._closeRequest !== undefined) {
            stream._closeRequest._reject(stream._storedError);
            stream._closeRequest = undefined;
        }
        var writer = stream._writer;
        if (writer !== undefined) {
            defaultWriterClosedPromiseReject(writer, stream._storedError);
        }
    }
    function WritableStreamUpdateBackpressure(stream, backpressure) {
        var writer = stream._writer;
        if (writer !== undefined && backpressure !== stream._backpressure) {
            if (backpressure === true) {
                defaultWriterReadyPromiseReset(writer);
            }
            else {
                defaultWriterReadyPromiseResolve(writer);
            }
        }
        stream._backpressure = backpressure;
    }
    var WritableStreamDefaultWriter = /** @class */ (function () {
        function WritableStreamDefaultWriter(stream) {
            if (IsWritableStream(stream) === false) {
                throw new TypeError('WritableStreamDefaultWriter can only be constructed with a WritableStream instance');
            }
            if (IsWritableStreamLocked(stream) === true) {
                throw new TypeError('This stream has already been locked for exclusive writing by another writer');
            }
            this._ownerWritableStream = stream;
            stream._writer = this;
            var state = stream._state;
            if (state === 'writable') {
                if (WritableStreamCloseQueuedOrInFlight(stream) === false && stream._backpressure === true) {
                    defaultWriterReadyPromiseInitialize(this);
                }
                else {
                    defaultWriterReadyPromiseInitializeAsResolved(this);
                }
                defaultWriterClosedPromiseInitialize(this);
            }
            else if (state === 'erroring') {
                defaultWriterReadyPromiseInitializeAsRejected(this, stream._storedError);
                defaultWriterClosedPromiseInitialize(this);
            }
            else if (state === 'closed') {
                defaultWriterReadyPromiseInitializeAsResolved(this);
                defaultWriterClosedPromiseInitializeAsResolved(this);
            }
            else {
                var storedError = stream._storedError;
                defaultWriterReadyPromiseInitializeAsRejected(this, storedError);
                defaultWriterClosedPromiseInitializeAsRejected(this, storedError);
            }
        }
        Object.defineProperty(WritableStreamDefaultWriter.prototype, "closed", {
            get: function () {
                if (IsWritableStreamDefaultWriter(this) === false) {
                    return promiseRejectedWith(defaultWriterBrandCheckException('closed'));
                }
                return this._closedPromise;
            },
            enumerable: true,
            configurable: true
        });
        Object.defineProperty(WritableStreamDefaultWriter.prototype, "desiredSize", {
            get: function () {
                if (IsWritableStreamDefaultWriter(this) === false) {
                    throw defaultWriterBrandCheckException('desiredSize');
                }
                if (this._ownerWritableStream === undefined) {
                    throw defaultWriterLockException('desiredSize');
                }
                return WritableStreamDefaultWriterGetDesiredSize(this);
            },
            enumerable: true,
            configurable: true
        });
        Object.defineProperty(WritableStreamDefaultWriter.prototype, "ready", {
            get: function () {
                if (IsWritableStreamDefaultWriter(this) === false) {
                    return promiseRejectedWith(defaultWriterBrandCheckException('ready'));
                }
                return this._readyPromise;
            },
            enumerable: true,
            configurable: true
        });
        WritableStreamDefaultWriter.prototype.abort = function (reason) {
            if (IsWritableStreamDefaultWriter(this) === false) {
                return promiseRejectedWith(defaultWriterBrandCheckException('abort'));
            }
            if (this._ownerWritableStream === undefined) {
                return promiseRejectedWith(defaultWriterLockException('abort'));
            }
            return WritableStreamDefaultWriterAbort(this, reason);
        };
        WritableStreamDefaultWriter.prototype.close = function () {
            if (IsWritableStreamDefaultWriter(this) === false) {
                return promiseRejectedWith(defaultWriterBrandCheckException('close'));
            }
            var stream = this._ownerWritableStream;
            if (stream === undefined) {
                return promiseRejectedWith(defaultWriterLockException('close'));
            }
            if (WritableStreamCloseQueuedOrInFlight(stream) === true) {
                return promiseRejectedWith(new TypeError('Cannot close an already-closing stream'));
            }
            return WritableStreamDefaultWriterClose(this);
        };
        WritableStreamDefaultWriter.prototype.releaseLock = function () {
            if (IsWritableStreamDefaultWriter(this) === false) {
                throw defaultWriterBrandCheckException('releaseLock');
            }
            var stream = this._ownerWritableStream;
            if (stream === undefined) {
                return;
            }
            WritableStreamDefaultWriterRelease(this);
        };
        WritableStreamDefaultWriter.prototype.write = function (chunk) {
            if (IsWritableStreamDefaultWriter(this) === false) {
                return promiseRejectedWith(defaultWriterBrandCheckException('write'));
            }
            if (this._ownerWritableStream === undefined) {
                return promiseRejectedWith(defaultWriterLockException('write to'));
            }
            return WritableStreamDefaultWriterWrite(this, chunk);
        };
        return WritableStreamDefaultWriter;
    }());
    // Abstract operations for the WritableStreamDefaultWriter.
    function IsWritableStreamDefaultWriter(x) {
        if (!typeIsObject(x)) {
            return false;
        }
        if (!Object.prototype.hasOwnProperty.call(x, '_ownerWritableStream')) {
            return false;
        }
        return true;
    }
    // A client of WritableStreamDefaultWriter may use these functions directly to bypass state check.
    function WritableStreamDefaultWriterAbort(writer, reason) {
        var stream = writer._ownerWritableStream;
        return WritableStreamAbort(stream, reason);
    }
    function WritableStreamDefaultWriterClose(writer) {
        var stream = writer._ownerWritableStream;
        return WritableStreamClose(stream);
    }
    function WritableStreamDefaultWriterCloseWithErrorPropagation(writer) {
        var stream = writer._ownerWritableStream;
        var state = stream._state;
        if (WritableStreamCloseQueuedOrInFlight(stream) === true || state === 'closed') {
            return promiseResolvedWith(undefined);
        }
        if (state === 'errored') {
            return promiseRejectedWith(stream._storedError);
        }
        return WritableStreamDefaultWriterClose(writer);
    }
    function WritableStreamDefaultWriterEnsureClosedPromiseRejected(writer, error) {
        if (writer._closedPromiseState === 'pending') {
            defaultWriterClosedPromiseReject(writer, error);
        }
        else {
            defaultWriterClosedPromiseResetToRejected(writer, error);
        }
    }
    function WritableStreamDefaultWriterEnsureReadyPromiseRejected(writer, error) {
        if (writer._readyPromiseState === 'pending') {
            defaultWriterReadyPromiseReject(writer, error);
        }
        else {
            defaultWriterReadyPromiseResetToRejected(writer, error);
        }
    }
    function WritableStreamDefaultWriterGetDesiredSize(writer) {
        var stream = writer._ownerWritableStream;
        var state = stream._state;
        if (state === 'errored' || state === 'erroring') {
            return null;
        }
        if (state === 'closed') {
            return 0;
        }
        return WritableStreamDefaultControllerGetDesiredSize(stream._writableStreamController);
    }
    function WritableStreamDefaultWriterRelease(writer) {
        var stream = writer._ownerWritableStream;
        var releasedError = new TypeError('Writer was released and can no longer be used to monitor the stream\'s closedness');
        WritableStreamDefaultWriterEnsureReadyPromiseRejected(writer, releasedError);
        // The state transitions to "errored" before the sink abort() method runs, but the writer.closed promise is not
        // rejected until afterwards. This means that simply testing state will not work.
        WritableStreamDefaultWriterEnsureClosedPromiseRejected(writer, releasedError);
        stream._writer = undefined;
        writer._ownerWritableStream = undefined;
    }
    function WritableStreamDefaultWriterWrite(writer, chunk) {
        var stream = writer._ownerWritableStream;
        var controller = stream._writableStreamController;
        var chunkSize = WritableStreamDefaultControllerGetChunkSize(controller, chunk);
        if (stream !== writer._ownerWritableStream) {
            return promiseRejectedWith(defaultWriterLockException('write to'));
        }
        var state = stream._state;
        if (state === 'errored') {
            return promiseRejectedWith(stream._storedError);
        }
        if (WritableStreamCloseQueuedOrInFlight(stream) === true || state === 'closed') {
            return promiseRejectedWith(new TypeError('The stream is closing or closed and cannot be written to'));
        }
        if (state === 'erroring') {
            return promiseRejectedWith(stream._storedError);
        }
        var promise = WritableStreamAddWriteRequest(stream);
        WritableStreamDefaultControllerWrite(controller, chunk, chunkSize);
        return promise;
    }
    var WritableStreamDefaultController = /** @class */ (function () {
        /** @internal */
        function WritableStreamDefaultController() {
            throw new TypeError('WritableStreamDefaultController cannot be constructed explicitly');
        }
        WritableStreamDefaultController.prototype.error = function (e) {
            if (IsWritableStreamDefaultController(this) === false) {
                throw new TypeError('WritableStreamDefaultController.prototype.error can only be used on a WritableStreamDefaultController');
            }
            var state = this._controlledWritableStream._state;
            if (state !== 'writable') {
                // The stream is closed, errored or will be soon. The sink can't do anything useful if it gets an error here, so
                // just treat it as a no-op.
                return;
            }
            WritableStreamDefaultControllerError(this, e);
        };
        /** @internal */
        WritableStreamDefaultController.prototype[AbortSteps] = function (reason) {
            var result = this._abortAlgorithm(reason);
            WritableStreamDefaultControllerClearAlgorithms(this);
            return result;
        };
        /** @internal */
        WritableStreamDefaultController.prototype[ErrorSteps] = function () {
            ResetQueue(this);
        };
        return WritableStreamDefaultController;
    }());
    // Abstract operations implementing interface required by the WritableStream.
    function IsWritableStreamDefaultController(x) {
        if (!typeIsObject(x)) {
            return false;
        }
        if (!Object.prototype.hasOwnProperty.call(x, '_controlledWritableStream')) {
            return false;
        }
        return true;
    }
    function SetUpWritableStreamDefaultController(stream, controller, startAlgorithm, writeAlgorithm, closeAlgorithm, abortAlgorithm, highWaterMark, sizeAlgorithm) {
        controller._controlledWritableStream = stream;
        stream._writableStreamController = controller;
        // Need to set the slots so that the assert doesn't fire. In the spec the slots already exist implicitly.
        controller._queue = undefined;
        controller._queueTotalSize = undefined;
        ResetQueue(controller);
        controller._started = false;
        controller._strategySizeAlgorithm = sizeAlgorithm;
        controller._strategyHWM = highWaterMark;
        controller._writeAlgorithm = writeAlgorithm;
        controller._closeAlgorithm = closeAlgorithm;
        controller._abortAlgorithm = abortAlgorithm;
        var backpressure = WritableStreamDefaultControllerGetBackpressure(controller);
        WritableStreamUpdateBackpressure(stream, backpressure);
        var startResult = startAlgorithm();
        var startPromise = promiseResolvedWith(startResult);
        uponPromise(startPromise, function () {
            controller._started = true;
            WritableStreamDefaultControllerAdvanceQueueIfNeeded(controller);
        }, function (r) {
            controller._started = true;
            WritableStreamDealWithRejection(stream, r);
        });
    }
    function SetUpWritableStreamDefaultControllerFromUnderlyingSink(stream, underlyingSink, highWaterMark, sizeAlgorithm) {
        var controller = Object.create(WritableStreamDefaultController.prototype);
        function startAlgorithm() {
            return InvokeOrNoop(underlyingSink, 'start', [controller]);
        }
        var writeAlgorithm = CreateAlgorithmFromUnderlyingMethod(underlyingSink, 'write', 1, [controller]);
        var closeAlgorithm = CreateAlgorithmFromUnderlyingMethod(underlyingSink, 'close', 0, []);
        var abortAlgorithm = CreateAlgorithmFromUnderlyingMethod(underlyingSink, 'abort', 1, []);
        SetUpWritableStreamDefaultController(stream, controller, startAlgorithm, writeAlgorithm, closeAlgorithm, abortAlgorithm, highWaterMark, sizeAlgorithm);
    }
    // ClearAlgorithms may be called twice. Erroring the same stream in multiple ways will often result in redundant calls.
    function WritableStreamDefaultControllerClearAlgorithms(controller) {
        controller._writeAlgorithm = undefined;
        controller._closeAlgorithm = undefined;
        controller._abortAlgorithm = undefined;
        controller._strategySizeAlgorithm = undefined;
    }
    function WritableStreamDefaultControllerClose(controller) {
        EnqueueValueWithSize(controller, 'close', 0);
        WritableStreamDefaultControllerAdvanceQueueIfNeeded(controller);
    }
    function WritableStreamDefaultControllerGetChunkSize(controller, chunk) {
        try {
            return controller._strategySizeAlgorithm(chunk);
        }
        catch (chunkSizeE) {
            WritableStreamDefaultControllerErrorIfNeeded(controller, chunkSizeE);
            return 1;
        }
    }
    function WritableStreamDefaultControllerGetDesiredSize(controller) {
        return controller._strategyHWM - controller._queueTotalSize;
    }
    function WritableStreamDefaultControllerWrite(controller, chunk, chunkSize) {
        var writeRecord = { chunk: chunk };
        try {
            EnqueueValueWithSize(controller, writeRecord, chunkSize);
        }
        catch (enqueueE) {
            WritableStreamDefaultControllerErrorIfNeeded(controller, enqueueE);
            return;
        }
        var stream = controller._controlledWritableStream;
        if (WritableStreamCloseQueuedOrInFlight(stream) === false && stream._state === 'writable') {
            var backpressure = WritableStreamDefaultControllerGetBackpressure(controller);
            WritableStreamUpdateBackpressure(stream, backpressure);
        }
        WritableStreamDefaultControllerAdvanceQueueIfNeeded(controller);
    }
    // Abstract operations for the WritableStreamDefaultController.
    function WritableStreamDefaultControllerAdvanceQueueIfNeeded(controller) {
        var stream = controller._controlledWritableStream;
        if (controller._started === false) {
            return;
        }
        if (stream._inFlightWriteRequest !== undefined) {
            return;
        }
        var state = stream._state;
        if (state === 'erroring') {
            WritableStreamFinishErroring(stream);
            return;
        }
        if (controller._queue.length === 0) {
            return;
        }
        var writeRecord = PeekQueueValue(controller);
        if (writeRecord === 'close') {
            WritableStreamDefaultControllerProcessClose(controller);
        }
        else {
            WritableStreamDefaultControllerProcessWrite(controller, writeRecord.chunk);
        }
    }
    function WritableStreamDefaultControllerErrorIfNeeded(controller, error) {
        if (controller._controlledWritableStream._state === 'writable') {
            WritableStreamDefaultControllerError(controller, error);
        }
    }
    function WritableStreamDefaultControllerProcessClose(controller) {
        var stream = controller._controlledWritableStream;
        WritableStreamMarkCloseRequestInFlight(stream);
        DequeueValue(controller);
        var sinkClosePromise = controller._closeAlgorithm();
        WritableStreamDefaultControllerClearAlgorithms(controller);
        uponPromise(sinkClosePromise, function () {
            WritableStreamFinishInFlightClose(stream);
        }, function (reason) {
            WritableStreamFinishInFlightCloseWithError(stream, reason);
        });
    }
    function WritableStreamDefaultControllerProcessWrite(controller, chunk) {
        var stream = controller._controlledWritableStream;
        WritableStreamMarkFirstWriteRequestInFlight(stream);
        var sinkWritePromise = controller._writeAlgorithm(chunk);
        uponPromise(sinkWritePromise, function () {
            WritableStreamFinishInFlightWrite(stream);
            var state = stream._state;
            DequeueValue(controller);
            if (WritableStreamCloseQueuedOrInFlight(stream) === false && state === 'writable') {
                var backpressure = WritableStreamDefaultControllerGetBackpressure(controller);
                WritableStreamUpdateBackpressure(stream, backpressure);
            }
            WritableStreamDefaultControllerAdvanceQueueIfNeeded(controller);
        }, function (reason) {
            if (stream._state === 'writable') {
                WritableStreamDefaultControllerClearAlgorithms(controller);
            }
            WritableStreamFinishInFlightWriteWithError(stream, reason);
        });
    }
    function WritableStreamDefaultControllerGetBackpressure(controller) {
        var desiredSize = WritableStreamDefaultControllerGetDesiredSize(controller);
        return desiredSize <= 0;
    }
    // A client of WritableStreamDefaultController may use these functions directly to bypass state check.
    function WritableStreamDefaultControllerError(controller, error) {
        var stream = controller._controlledWritableStream;
        WritableStreamDefaultControllerClearAlgorithms(controller);
        WritableStreamStartErroring(stream, error);
    }
    // Helper functions for the WritableStream.
    function streamBrandCheckException(name) {
        return new TypeError("WritableStream.prototype." + name + " can only be used on a WritableStream");
    }
    // Helper functions for the WritableStreamDefaultWriter.
    function defaultWriterBrandCheckException(name) {
        return new TypeError("WritableStreamDefaultWriter.prototype." + name + " can only be used on a WritableStreamDefaultWriter");
    }
    function defaultWriterLockException(name) {
        return new TypeError('Cannot ' + name + ' a stream using a released writer');
    }
    function defaultWriterClosedPromiseInitialize(writer) {
        writer._closedPromise = newPromise(function (resolve, reject) {
            writer._closedPromise_resolve = resolve;
            writer._closedPromise_reject = reject;
            writer._closedPromiseState = 'pending';
        });
    }
    function defaultWriterClosedPromiseInitializeAsRejected(writer, reason) {
        defaultWriterClosedPromiseInitialize(writer);
        defaultWriterClosedPromiseReject(writer, reason);
    }
    function defaultWriterClosedPromiseInitializeAsResolved(writer) {
        defaultWriterClosedPromiseInitialize(writer);
        defaultWriterClosedPromiseResolve(writer);
    }
    function defaultWriterClosedPromiseReject(writer, reason) {
        setPromiseIsHandledToTrue(writer._closedPromise);
        writer._closedPromise_reject(reason);
        writer._closedPromise_resolve = undefined;
        writer._closedPromise_reject = undefined;
        writer._closedPromiseState = 'rejected';
    }
    function defaultWriterClosedPromiseResetToRejected(writer, reason) {
        defaultWriterClosedPromiseInitializeAsRejected(writer, reason);
    }
    function defaultWriterClosedPromiseResolve(writer) {
        writer._closedPromise_resolve(undefined);
        writer._closedPromise_resolve = undefined;
        writer._closedPromise_reject = undefined;
        writer._closedPromiseState = 'resolved';
    }
    function defaultWriterReadyPromiseInitialize(writer) {
        writer._readyPromise = newPromise(function (resolve, reject) {
            writer._readyPromise_resolve = resolve;
            writer._readyPromise_reject = reject;
        });
        writer._readyPromiseState = 'pending';
    }
    function defaultWriterReadyPromiseInitializeAsRejected(writer, reason) {
        defaultWriterReadyPromiseInitialize(writer);
        defaultWriterReadyPromiseReject(writer, reason);
    }
    function defaultWriterReadyPromiseInitializeAsResolved(writer) {
        defaultWriterReadyPromiseInitialize(writer);
        defaultWriterReadyPromiseResolve(writer);
    }
    function defaultWriterReadyPromiseReject(writer, reason) {
        setPromiseIsHandledToTrue(writer._readyPromise);
        writer._readyPromise_reject(reason);
        writer._readyPromise_resolve = undefined;
        writer._readyPromise_reject = undefined;
        writer._readyPromiseState = 'rejected';
    }
    function defaultWriterReadyPromiseReset(writer) {
        defaultWriterReadyPromiseInitialize(writer);
    }
    function defaultWriterReadyPromiseResetToRejected(writer, reason) {
        defaultWriterReadyPromiseInitializeAsRejected(writer, reason);
    }
    function defaultWriterReadyPromiseResolve(writer) {
        writer._readyPromise_resolve(undefined);
        writer._readyPromise_resolve = undefined;
        writer._readyPromise_reject = undefined;
        writer._readyPromiseState = 'fulfilled';
    }

    function isAbortSignal(value) {
        if (typeof value !== 'object' || value === null) {
            return false;
        }
        try {
            return typeof value.aborted === 'boolean';
        }
        catch (_a) {
            // AbortSignal.prototype.aborted throws if its brand check fails
            return false;
        }
    }

    /// <reference lib="dom" />
    var NativeDOMException = typeof DOMException !== 'undefined' ? DOMException : undefined;

    /// <reference types="node" />
    function isDOMExceptionConstructor(ctor) {
        if (!(typeof ctor === 'function' || typeof ctor === 'object')) {
            return false;
        }
        try {
            new ctor();
            return true;
        }
        catch (_a) {
            return false;
        }
    }
    function createDOMExceptionPolyfill() {
        var ctor = function DOMException(message, name) {
            this.message = message || '';
            this.name = name || 'Error';
            if (Error.captureStackTrace) {
                Error.captureStackTrace(this, this.constructor);
            }
        };
        ctor.prototype = Object.create(Error.prototype);
        Object.defineProperty(ctor.prototype, 'constructor', { value: ctor, writable: true, configurable: true });
        return ctor;
    }
    var DOMException$1 = isDOMExceptionConstructor(NativeDOMException) ? NativeDOMException : createDOMExceptionPolyfill();

    function ReadableStreamPipeTo(source, dest, preventClose, preventAbort, preventCancel, signal) {
        var reader = AcquireReadableStreamDefaultReader(source);
        var writer = AcquireWritableStreamDefaultWriter(dest);
        source._disturbed = true;
        var shuttingDown = false;
        // This is used to keep track of the spec's requirement that we wait for ongoing writes during shutdown.
        var currentWrite = promiseResolvedWith(undefined);
        return newPromise(function (resolve, reject) {
            var abortAlgorithm;
            if (signal !== undefined) {
                abortAlgorithm = function () {
                    var error = new DOMException$1('Aborted', 'AbortError');
                    var actions = [];
                    if (preventAbort === false) {
                        actions.push(function () {
                            if (dest._state === 'writable') {
                                return WritableStreamAbort(dest, error);
                            }
                            return promiseResolvedWith(undefined);
                        });
                    }
                    if (preventCancel === false) {
                        actions.push(function () {
                            if (source._state === 'readable') {
                                return ReadableStreamCancel(source, error);
                            }
                            return promiseResolvedWith(undefined);
                        });
                    }
                    shutdownWithAction(function () { return Promise.all(actions.map(function (action) { return action(); })); }, true, error);
                };
                if (signal.aborted === true) {
                    abortAlgorithm();
                    return;
                }
                signal.addEventListener('abort', abortAlgorithm);
            }
            // Using reader and writer, read all chunks from this and write them to dest
            // - Backpressure must be enforced
            // - Shutdown must stop all activity
            function pipeLoop() {
                return newPromise(function (resolveLoop, rejectLoop) {
                    function next(done) {
                        if (done) {
                            resolveLoop();
                        }
                        else {
                            // Use `PerformPromiseThen` instead of `uponPromise` to avoid
                            // adding unnecessary `.catch(rethrowAssertionErrorRejection)` handlers
                            PerformPromiseThen(pipeStep(), next, rejectLoop);
                        }
                    }
                    next(false);
                });
            }
            function pipeStep() {
                if (shuttingDown === true) {
                    return promiseResolvedWith(true);
                }
                return PerformPromiseThen(writer._readyPromise, function () {
                    return PerformPromiseThen(ReadableStreamDefaultReaderRead(reader), function (result) {
                        if (result.done === true) {
                            return true;
                        }
                        currentWrite = PerformPromiseThen(WritableStreamDefaultWriterWrite(writer, result.value), undefined, noop);
                        return false;
                    });
                });
            }
            // Errors must be propagated forward
            isOrBecomesErrored(source, reader._closedPromise, function (storedError) {
                if (preventAbort === false) {
                    shutdownWithAction(function () { return WritableStreamAbort(dest, storedError); }, true, storedError);
                }
                else {
                    shutdown(true, storedError);
                }
            });
            // Errors must be propagated backward
            isOrBecomesErrored(dest, writer._closedPromise, function (storedError) {
                if (preventCancel === false) {
                    shutdownWithAction(function () { return ReadableStreamCancel(source, storedError); }, true, storedError);
                }
                else {
                    shutdown(true, storedError);
                }
            });
            // Closing must be propagated forward
            isOrBecomesClosed(source, reader._closedPromise, function () {
                if (preventClose === false) {
                    shutdownWithAction(function () { return WritableStreamDefaultWriterCloseWithErrorPropagation(writer); });
                }
                else {
                    shutdown();
                }
            });
            // Closing must be propagated backward
            if (WritableStreamCloseQueuedOrInFlight(dest) === true || dest._state === 'closed') {
                var destClosed_1 = new TypeError('the destination writable stream closed before all data could be piped to it');
                if (preventCancel === false) {
                    shutdownWithAction(function () { return ReadableStreamCancel(source, destClosed_1); }, true, destClosed_1);
                }
                else {
                    shutdown(true, destClosed_1);
                }
            }
            setPromiseIsHandledToTrue(pipeLoop());
            function waitForWritesToFinish() {
                // Another write may have started while we were waiting on this currentWrite, so we have to be sure to wait
                // for that too.
                var oldCurrentWrite = currentWrite;
                return PerformPromiseThen(currentWrite, function () { return oldCurrentWrite !== currentWrite ? waitForWritesToFinish() : undefined; });
            }
            function isOrBecomesErrored(stream, promise, action) {
                if (stream._state === 'errored') {
                    action(stream._storedError);
                }
                else {
                    uponRejection(promise, action);
                }
            }
            function isOrBecomesClosed(stream, promise, action) {
                if (stream._state === 'closed') {
                    action();
                }
                else {
                    uponFulfillment(promise, action);
                }
            }
            function shutdownWithAction(action, originalIsError, originalError) {
                if (shuttingDown === true) {
                    return;
                }
                shuttingDown = true;
                if (dest._state === 'writable' && WritableStreamCloseQueuedOrInFlight(dest) === false) {
                    uponFulfillment(waitForWritesToFinish(), doTheRest);
                }
                else {
                    doTheRest();
                }
                function doTheRest() {
                    uponPromise(action(), function () { return finalize(originalIsError, originalError); }, function (newError) { return finalize(true, newError); });
                }
            }
            function shutdown(isError, error) {
                if (shuttingDown === true) {
                    return;
                }
                shuttingDown = true;
                if (dest._state === 'writable' && WritableStreamCloseQueuedOrInFlight(dest) === false) {
                    uponFulfillment(waitForWritesToFinish(), function () { return finalize(isError, error); });
                }
                else {
                    finalize(isError, error);
                }
            }
            function finalize(isError, error) {
                WritableStreamDefaultWriterRelease(writer);
                ReadableStreamReaderGenericRelease(reader);
                if (signal !== undefined) {
                    signal.removeEventListener('abort', abortAlgorithm);
                }
                if (isError) {
                    reject(error);
                }
                else {
                    resolve(undefined);
                }
            }
        });
    }

    var ReadableStreamDefaultController = /** @class */ (function () {
        /** @internal */
        function ReadableStreamDefaultController() {
            throw new TypeError();
        }
        Object.defineProperty(ReadableStreamDefaultController.prototype, "desiredSize", {
            get: function () {
                if (IsReadableStreamDefaultController(this) === false) {
                    throw defaultControllerBrandCheckException('desiredSize');
                }
                return ReadableStreamDefaultControllerGetDesiredSize(this);
            },
            enumerable: true,
            configurable: true
        });
        ReadableStreamDefaultController.prototype.close = function () {
            if (IsReadableStreamDefaultController(this) === false) {
                throw defaultControllerBrandCheckException('close');
            }
            if (ReadableStreamDefaultControllerCanCloseOrEnqueue(this) === false) {
                throw new TypeError('The stream is not in a state that permits close');
            }
            ReadableStreamDefaultControllerClose(this);
        };
        ReadableStreamDefaultController.prototype.enqueue = function (chunk) {
            if (IsReadableStreamDefaultController(this) === false) {
                throw defaultControllerBrandCheckException('enqueue');
            }
            if (ReadableStreamDefaultControllerCanCloseOrEnqueue(this) === false) {
                throw new TypeError('The stream is not in a state that permits enqueue');
            }
            return ReadableStreamDefaultControllerEnqueue(this, chunk);
        };
        ReadableStreamDefaultController.prototype.error = function (e) {
            if (IsReadableStreamDefaultController(this) === false) {
                throw defaultControllerBrandCheckException('error');
            }
            ReadableStreamDefaultControllerError(this, e);
        };
        /** @internal */
        ReadableStreamDefaultController.prototype[CancelSteps] = function (reason) {
            ResetQueue(this);
            var result = this._cancelAlgorithm(reason);
            ReadableStreamDefaultControllerClearAlgorithms(this);
            return result;
        };
        /** @internal */
        ReadableStreamDefaultController.prototype[PullSteps] = function () {
            var stream = this._controlledReadableStream;
            if (this._queue.length > 0) {
                var chunk = DequeueValue(this);
                if (this._closeRequested === true && this._queue.length === 0) {
                    ReadableStreamDefaultControllerClearAlgorithms(this);
                    ReadableStreamClose(stream);
                }
                else {
                    ReadableStreamDefaultControllerCallPullIfNeeded(this);
                }
                return promiseResolvedWith(ReadableStreamCreateReadResult(chunk, false, stream._reader._forAuthorCode));
            }
            var pendingPromise = ReadableStreamAddReadRequest(stream);
            ReadableStreamDefaultControllerCallPullIfNeeded(this);
            return pendingPromise;
        };
        return ReadableStreamDefaultController;
    }());
    // Abstract operations for the ReadableStreamDefaultController.
    function IsReadableStreamDefaultController(x) {
        if (!typeIsObject(x)) {
            return false;
        }
        if (!Object.prototype.hasOwnProperty.call(x, '_controlledReadableStream')) {
            return false;
        }
        return true;
    }
    function ReadableStreamDefaultControllerCallPullIfNeeded(controller) {
        var shouldPull = ReadableStreamDefaultControllerShouldCallPull(controller);
        if (shouldPull === false) {
            return;
        }
        if (controller._pulling === true) {
            controller._pullAgain = true;
            return;
        }
        controller._pulling = true;
        var pullPromise = controller._pullAlgorithm();
        uponPromise(pullPromise, function () {
            controller._pulling = false;
            if (controller._pullAgain === true) {
                controller._pullAgain = false;
                ReadableStreamDefaultControllerCallPullIfNeeded(controller);
            }
        }, function (e) {
            ReadableStreamDefaultControllerError(controller, e);
        });
    }
    function ReadableStreamDefaultControllerShouldCallPull(controller) {
        var stream = controller._controlledReadableStream;
        if (ReadableStreamDefaultControllerCanCloseOrEnqueue(controller) === false) {
            return false;
        }
        if (controller._started === false) {
            return false;
        }
        if (IsReadableStreamLocked(stream) === true && ReadableStreamGetNumReadRequests(stream) > 0) {
            return true;
        }
        var desiredSize = ReadableStreamDefaultControllerGetDesiredSize(controller);
        if (desiredSize > 0) {
            return true;
        }
        return false;
    }
    function ReadableStreamDefaultControllerClearAlgorithms(controller) {
        controller._pullAlgorithm = undefined;
        controller._cancelAlgorithm = undefined;
        controller._strategySizeAlgorithm = undefined;
    }
    // A client of ReadableStreamDefaultController may use these functions directly to bypass state check.
    function ReadableStreamDefaultControllerClose(controller) {
        var stream = controller._controlledReadableStream;
        controller._closeRequested = true;
        if (controller._queue.length === 0) {
            ReadableStreamDefaultControllerClearAlgorithms(controller);
            ReadableStreamClose(stream);
        }
    }
    function ReadableStreamDefaultControllerEnqueue(controller, chunk) {
        var stream = controller._controlledReadableStream;
        if (IsReadableStreamLocked(stream) === true && ReadableStreamGetNumReadRequests(stream) > 0) {
            ReadableStreamFulfillReadRequest(stream, chunk, false);
        }
        else {
            var chunkSize = void 0;
            try {
                chunkSize = controller._strategySizeAlgorithm(chunk);
            }
            catch (chunkSizeE) {
                ReadableStreamDefaultControllerError(controller, chunkSizeE);
                throw chunkSizeE;
            }
            try {
                EnqueueValueWithSize(controller, chunk, chunkSize);
            }
            catch (enqueueE) {
                ReadableStreamDefaultControllerError(controller, enqueueE);
                throw enqueueE;
            }
        }
        ReadableStreamDefaultControllerCallPullIfNeeded(controller);
    }
    function ReadableStreamDefaultControllerError(controller, e) {
        var stream = controller._controlledReadableStream;
        if (stream._state !== 'readable') {
            return;
        }
        ResetQueue(controller);
        ReadableStreamDefaultControllerClearAlgorithms(controller);
        ReadableStreamError(stream, e);
    }
    function ReadableStreamDefaultControllerGetDesiredSize(controller) {
        var stream = controller._controlledReadableStream;
        var state = stream._state;
        if (state === 'errored') {
            return null;
        }
        if (state === 'closed') {
            return 0;
        }
        return controller._strategyHWM - controller._queueTotalSize;
    }
    // This is used in the implementation of TransformStream.
    function ReadableStreamDefaultControllerHasBackpressure(controller) {
        if (ReadableStreamDefaultControllerShouldCallPull(controller) === true) {
            return false;
        }
        return true;
    }
    function ReadableStreamDefaultControllerCanCloseOrEnqueue(controller) {
        var state = controller._controlledReadableStream._state;
        if (controller._closeRequested === false && state === 'readable') {
            return true;
        }
        return false;
    }
    function SetUpReadableStreamDefaultController(stream, controller, startAlgorithm, pullAlgorithm, cancelAlgorithm, highWaterMark, sizeAlgorithm) {
        controller._controlledReadableStream = stream;
        controller._queue = undefined;
        controller._queueTotalSize = undefined;
        ResetQueue(controller);
        controller._started = false;
        controller._closeRequested = false;
        controller._pullAgain = false;
        controller._pulling = false;
        controller._strategySizeAlgorithm = sizeAlgorithm;
        controller._strategyHWM = highWaterMark;
        controller._pullAlgorithm = pullAlgorithm;
        controller._cancelAlgorithm = cancelAlgorithm;
        stream._readableStreamController = controller;
        var startResult = startAlgorithm();
        uponPromise(promiseResolvedWith(startResult), function () {
            controller._started = true;
            ReadableStreamDefaultControllerCallPullIfNeeded(controller);
        }, function (r) {
            ReadableStreamDefaultControllerError(controller, r);
        });
    }
    function SetUpReadableStreamDefaultControllerFromUnderlyingSource(stream, underlyingSource, highWaterMark, sizeAlgorithm) {
        var controller = Object.create(ReadableStreamDefaultController.prototype);
        function startAlgorithm() {
            return InvokeOrNoop(underlyingSource, 'start', [controller]);
        }
        var pullAlgorithm = CreateAlgorithmFromUnderlyingMethod(underlyingSource, 'pull', 0, [controller]);
        var cancelAlgorithm = CreateAlgorithmFromUnderlyingMethod(underlyingSource, 'cancel', 1, []);
        SetUpReadableStreamDefaultController(stream, controller, startAlgorithm, pullAlgorithm, cancelAlgorithm, highWaterMark, sizeAlgorithm);
    }
    // Helper functions for the ReadableStreamDefaultController.
    function defaultControllerBrandCheckException(name) {
        return new TypeError("ReadableStreamDefaultController.prototype." + name + " can only be used on a ReadableStreamDefaultController");
    }

    function ReadableStreamTee(stream, cloneForBranch2) {
        var reader = AcquireReadableStreamDefaultReader(stream);
        var reading = false;
        var canceled1 = false;
        var canceled2 = false;
        var reason1;
        var reason2;
        var branch1;
        var branch2;
        var resolveCancelPromise;
        var cancelPromise = newPromise(function (resolve) {
            resolveCancelPromise = resolve;
        });
        function pullAlgorithm() {
            if (reading === true) {
                return promiseResolvedWith(undefined);
            }
            reading = true;
            var readPromise = transformPromiseWith(ReadableStreamDefaultReaderRead(reader), function (result) {
                reading = false;
                var done = result.done;
                if (done === true) {
                    if (canceled1 === false) {
                        ReadableStreamDefaultControllerClose(branch1._readableStreamController);
                    }
                    if (canceled2 === false) {
                        ReadableStreamDefaultControllerClose(branch2._readableStreamController);
                    }
                    return;
                }
                var value = result.value;
                var value1 = value;
                var value2 = value;
                // There is no way to access the cloning code right now in the reference implementation.
                // If we add one then we'll need an implementation for serializable objects.
                // if (canceled2 === false && cloneForBranch2 === true) {
                //   value2 = StructuredDeserialize(StructuredSerialize(value2));
                // }
                if (canceled1 === false) {
                    ReadableStreamDefaultControllerEnqueue(branch1._readableStreamController, value1);
                }
                if (canceled2 === false) {
                    ReadableStreamDefaultControllerEnqueue(branch2._readableStreamController, value2);
                }
            });
            setPromiseIsHandledToTrue(readPromise);
            return promiseResolvedWith(undefined);
        }
        function cancel1Algorithm(reason) {
            canceled1 = true;
            reason1 = reason;
            if (canceled2 === true) {
                var compositeReason = createArrayFromList([reason1, reason2]);
                var cancelResult = ReadableStreamCancel(stream, compositeReason);
                resolveCancelPromise(cancelResult);
            }
            return cancelPromise;
        }
        function cancel2Algorithm(reason) {
            canceled2 = true;
            reason2 = reason;
            if (canceled1 === true) {
                var compositeReason = createArrayFromList([reason1, reason2]);
                var cancelResult = ReadableStreamCancel(stream, compositeReason);
                resolveCancelPromise(cancelResult);
            }
            return cancelPromise;
        }
        function startAlgorithm() { }
        branch1 = CreateReadableStream(startAlgorithm, pullAlgorithm, cancel1Algorithm);
        branch2 = CreateReadableStream(startAlgorithm, pullAlgorithm, cancel2Algorithm);
        uponRejection(reader._closedPromise, function (r) {
            ReadableStreamDefaultControllerError(branch1._readableStreamController, r);
            ReadableStreamDefaultControllerError(branch2._readableStreamController, r);
        });
        return [branch1, branch2];
    }

    /// <reference lib="es2015.core" />
    // https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Number/isInteger#Polyfill
    var NumberIsInteger = Number.isInteger || function (value) {
        return typeof value === 'number' &&
            isFinite(value) &&
            Math.floor(value) === value;
    };

    var ReadableStreamBYOBRequest = /** @class */ (function () {
        /** @internal */
        function ReadableStreamBYOBRequest() {
            throw new TypeError('ReadableStreamBYOBRequest cannot be used directly');
        }
        Object.defineProperty(ReadableStreamBYOBRequest.prototype, "view", {
            get: function () {
                if (IsReadableStreamBYOBRequest(this) === false) {
                    throw byobRequestBrandCheckException('view');
                }
                return this._view;
            },
            enumerable: true,
            configurable: true
        });
        ReadableStreamBYOBRequest.prototype.respond = function (bytesWritten) {
            if (IsReadableStreamBYOBRequest(this) === false) {
                throw byobRequestBrandCheckException('respond');
            }
            if (this._associatedReadableByteStreamController === undefined) {
                throw new TypeError('This BYOB request has been invalidated');
            }
            if (IsDetachedBuffer(this._view.buffer) === true) ;
            ReadableByteStreamControllerRespond(this._associatedReadableByteStreamController, bytesWritten);
        };
        ReadableStreamBYOBRequest.prototype.respondWithNewView = function (view) {
            if (IsReadableStreamBYOBRequest(this) === false) {
                throw byobRequestBrandCheckException('respond');
            }
            if (this._associatedReadableByteStreamController === undefined) {
                throw new TypeError('This BYOB request has been invalidated');
            }
            if (!ArrayBuffer.isView(view)) {
                throw new TypeError('You can only respond with array buffer views');
            }
            if (IsDetachedBuffer(view.buffer) === true) ;
            ReadableByteStreamControllerRespondWithNewView(this._associatedReadableByteStreamController, view);
        };
        return ReadableStreamBYOBRequest;
    }());
    var ReadableByteStreamController = /** @class */ (function () {
        /** @internal */
        function ReadableByteStreamController() {
            throw new TypeError('ReadableByteStreamController constructor cannot be used directly');
        }
        Object.defineProperty(ReadableByteStreamController.prototype, "byobRequest", {
            get: function () {
                if (IsReadableByteStreamController(this) === false) {
                    throw byteStreamControllerBrandCheckException('byobRequest');
                }
                if (this._byobRequest === undefined && this._pendingPullIntos.length > 0) {
                    var firstDescriptor = this._pendingPullIntos.peek();
                    var view = new Uint8Array(firstDescriptor.buffer, firstDescriptor.byteOffset + firstDescriptor.bytesFilled, firstDescriptor.byteLength - firstDescriptor.bytesFilled);
                    var byobRequest = Object.create(ReadableStreamBYOBRequest.prototype);
                    SetUpReadableStreamBYOBRequest(byobRequest, this, view);
                    this._byobRequest = byobRequest;
                }
                return this._byobRequest;
            },
            enumerable: true,
            configurable: true
        });
        Object.defineProperty(ReadableByteStreamController.prototype, "desiredSize", {
            get: function () {
                if (IsReadableByteStreamController(this) === false) {
                    throw byteStreamControllerBrandCheckException('desiredSize');
                }
                return ReadableByteStreamControllerGetDesiredSize(this);
            },
            enumerable: true,
            configurable: true
        });
        ReadableByteStreamController.prototype.close = function () {
            if (IsReadableByteStreamController(this) === false) {
                throw byteStreamControllerBrandCheckException('close');
            }
            if (this._closeRequested === true) {
                throw new TypeError('The stream has already been closed; do not close it again!');
            }
            var state = this._controlledReadableByteStream._state;
            if (state !== 'readable') {
                throw new TypeError("The stream (in " + state + " state) is not in the readable state and cannot be closed");
            }
            ReadableByteStreamControllerClose(this);
        };
        ReadableByteStreamController.prototype.enqueue = function (chunk) {
            if (IsReadableByteStreamController(this) === false) {
                throw byteStreamControllerBrandCheckException('enqueue');
            }
            if (this._closeRequested === true) {
                throw new TypeError('stream is closed or draining');
            }
            var state = this._controlledReadableByteStream._state;
            if (state !== 'readable') {
                throw new TypeError("The stream (in " + state + " state) is not in the readable state and cannot be enqueued to");
            }
            if (!ArrayBuffer.isView(chunk)) {
                throw new TypeError('You can only enqueue array buffer views when using a ReadableByteStreamController');
            }
            if (IsDetachedBuffer(chunk.buffer) === true) ;
            ReadableByteStreamControllerEnqueue(this, chunk);
        };
        ReadableByteStreamController.prototype.error = function (e) {
            if (IsReadableByteStreamController(this) === false) {
                throw byteStreamControllerBrandCheckException('error');
            }
            ReadableByteStreamControllerError(this, e);
        };
        /** @internal */
        ReadableByteStreamController.prototype[CancelSteps] = function (reason) {
            if (this._pendingPullIntos.length > 0) {
                var firstDescriptor = this._pendingPullIntos.peek();
                firstDescriptor.bytesFilled = 0;
            }
            ResetQueue(this);
            var result = this._cancelAlgorithm(reason);
            ReadableByteStreamControllerClearAlgorithms(this);
            return result;
        };
        /** @internal */
        ReadableByteStreamController.prototype[PullSteps] = function () {
            var stream = this._controlledReadableByteStream;
            if (this._queueTotalSize > 0) {
                var entry = this._queue.shift();
                this._queueTotalSize -= entry.byteLength;
                ReadableByteStreamControllerHandleQueueDrain(this);
                var view = void 0;
                try {
                    view = new Uint8Array(entry.buffer, entry.byteOffset, entry.byteLength);
                }
                catch (viewE) {
                    return promiseRejectedWith(viewE);
                }
                return promiseResolvedWith(ReadableStreamCreateReadResult(view, false, stream._reader._forAuthorCode));
            }
            var autoAllocateChunkSize = this._autoAllocateChunkSize;
            if (autoAllocateChunkSize !== undefined) {
                var buffer = void 0;
                try {
                    buffer = new ArrayBuffer(autoAllocateChunkSize);
                }
                catch (bufferE) {
                    return promiseRejectedWith(bufferE);
                }
                var pullIntoDescriptor = {
                    buffer: buffer,
                    byteOffset: 0,
                    byteLength: autoAllocateChunkSize,
                    bytesFilled: 0,
                    elementSize: 1,
                    ctor: Uint8Array,
                    readerType: 'default'
                };
                this._pendingPullIntos.push(pullIntoDescriptor);
            }
            var promise = ReadableStreamAddReadRequest(stream);
            ReadableByteStreamControllerCallPullIfNeeded(this);
            return promise;
        };
        return ReadableByteStreamController;
    }());
    // Abstract operations for the ReadableByteStreamController.
    function IsReadableByteStreamController(x) {
        if (!typeIsObject(x)) {
            return false;
        }
        if (!Object.prototype.hasOwnProperty.call(x, '_controlledReadableByteStream')) {
            return false;
        }
        return true;
    }
    function IsReadableStreamBYOBRequest(x) {
        if (!typeIsObject(x)) {
            return false;
        }
        if (!Object.prototype.hasOwnProperty.call(x, '_associatedReadableByteStreamController')) {
            return false;
        }
        return true;
    }
    function ReadableByteStreamControllerCallPullIfNeeded(controller) {
        var shouldPull = ReadableByteStreamControllerShouldCallPull(controller);
        if (shouldPull === false) {
            return;
        }
        if (controller._pulling === true) {
            controller._pullAgain = true;
            return;
        }
        controller._pulling = true;
        // TODO: Test controller argument
        var pullPromise = controller._pullAlgorithm();
        uponPromise(pullPromise, function () {
            controller._pulling = false;
            if (controller._pullAgain === true) {
                controller._pullAgain = false;
                ReadableByteStreamControllerCallPullIfNeeded(controller);
            }
        }, function (e) {
            ReadableByteStreamControllerError(controller, e);
        });
    }
    function ReadableByteStreamControllerClearPendingPullIntos(controller) {
        ReadableByteStreamControllerInvalidateBYOBRequest(controller);
        controller._pendingPullIntos = new SimpleQueue();
    }
    function ReadableByteStreamControllerCommitPullIntoDescriptor(stream, pullIntoDescriptor) {
        var done = false;
        if (stream._state === 'closed') {
            done = true;
        }
        var filledView = ReadableByteStreamControllerConvertPullIntoDescriptor(pullIntoDescriptor);
        if (pullIntoDescriptor.readerType === 'default') {
            ReadableStreamFulfillReadRequest(stream, filledView, done);
        }
        else {
            ReadableStreamFulfillReadIntoRequest(stream, filledView, done);
        }
    }
    function ReadableByteStreamControllerConvertPullIntoDescriptor(pullIntoDescriptor) {
        var bytesFilled = pullIntoDescriptor.bytesFilled;
        var elementSize = pullIntoDescriptor.elementSize;
        return new pullIntoDescriptor.ctor(pullIntoDescriptor.buffer, pullIntoDescriptor.byteOffset, bytesFilled / elementSize);
    }
    function ReadableByteStreamControllerEnqueueChunkToQueue(controller, buffer, byteOffset, byteLength) {
        controller._queue.push({ buffer: buffer, byteOffset: byteOffset, byteLength: byteLength });
        controller._queueTotalSize += byteLength;
    }
    function ReadableByteStreamControllerFillPullIntoDescriptorFromQueue(controller, pullIntoDescriptor) {
        var elementSize = pullIntoDescriptor.elementSize;
        var currentAlignedBytes = pullIntoDescriptor.bytesFilled - pullIntoDescriptor.bytesFilled % elementSize;
        var maxBytesToCopy = Math.min(controller._queueTotalSize, pullIntoDescriptor.byteLength - pullIntoDescriptor.bytesFilled);
        var maxBytesFilled = pullIntoDescriptor.bytesFilled + maxBytesToCopy;
        var maxAlignedBytes = maxBytesFilled - maxBytesFilled % elementSize;
        var totalBytesToCopyRemaining = maxBytesToCopy;
        var ready = false;
        if (maxAlignedBytes > currentAlignedBytes) {
            totalBytesToCopyRemaining = maxAlignedBytes - pullIntoDescriptor.bytesFilled;
            ready = true;
        }
        var queue = controller._queue;
        while (totalBytesToCopyRemaining > 0) {
            var headOfQueue = queue.peek();
            var bytesToCopy = Math.min(totalBytesToCopyRemaining, headOfQueue.byteLength);
            var destStart = pullIntoDescriptor.byteOffset + pullIntoDescriptor.bytesFilled;
            ArrayBufferCopy(pullIntoDescriptor.buffer, destStart, headOfQueue.buffer, headOfQueue.byteOffset, bytesToCopy);
            if (headOfQueue.byteLength === bytesToCopy) {
                queue.shift();
            }
            else {
                headOfQueue.byteOffset += bytesToCopy;
                headOfQueue.byteLength -= bytesToCopy;
            }
            controller._queueTotalSize -= bytesToCopy;
            ReadableByteStreamControllerFillHeadPullIntoDescriptor(controller, bytesToCopy, pullIntoDescriptor);
            totalBytesToCopyRemaining -= bytesToCopy;
        }
        return ready;
    }
    function ReadableByteStreamControllerFillHeadPullIntoDescriptor(controller, size, pullIntoDescriptor) {
        ReadableByteStreamControllerInvalidateBYOBRequest(controller);
        pullIntoDescriptor.bytesFilled += size;
    }
    function ReadableByteStreamControllerHandleQueueDrain(controller) {
        if (controller._queueTotalSize === 0 && controller._closeRequested === true) {
            ReadableByteStreamControllerClearAlgorithms(controller);
            ReadableStreamClose(controller._controlledReadableByteStream);
        }
        else {
            ReadableByteStreamControllerCallPullIfNeeded(controller);
        }
    }
    function ReadableByteStreamControllerInvalidateBYOBRequest(controller) {
        if (controller._byobRequest === undefined) {
            return;
        }
        controller._byobRequest._associatedReadableByteStreamController = undefined;
        controller._byobRequest._view = undefined;
        controller._byobRequest = undefined;
    }
    function ReadableByteStreamControllerProcessPullIntoDescriptorsUsingQueue(controller) {
        while (controller._pendingPullIntos.length > 0) {
            if (controller._queueTotalSize === 0) {
                return;
            }
            var pullIntoDescriptor = controller._pendingPullIntos.peek();
            if (ReadableByteStreamControllerFillPullIntoDescriptorFromQueue(controller, pullIntoDescriptor) === true) {
                ReadableByteStreamControllerShiftPendingPullInto(controller);
                ReadableByteStreamControllerCommitPullIntoDescriptor(controller._controlledReadableByteStream, pullIntoDescriptor);
            }
        }
    }
    function ReadableByteStreamControllerPullInto(controller, view) {
        var stream = controller._controlledReadableByteStream;
        var elementSize = 1;
        if (view.constructor !== DataView) {
            elementSize = view.constructor.BYTES_PER_ELEMENT;
        }
        var ctor = view.constructor;
        var buffer = TransferArrayBuffer(view.buffer);
        var pullIntoDescriptor = {
            buffer: buffer,
            byteOffset: view.byteOffset,
            byteLength: view.byteLength,
            bytesFilled: 0,
            elementSize: elementSize,
            ctor: ctor,
            readerType: 'byob'
        };
        if (controller._pendingPullIntos.length > 0) {
            controller._pendingPullIntos.push(pullIntoDescriptor);
            // No ReadableByteStreamControllerCallPullIfNeeded() call since:
            // - No change happens on desiredSize
            // - The source has already been notified of that there's at least 1 pending read(view)
            return ReadableStreamAddReadIntoRequest(stream);
        }
        if (stream._state === 'closed') {
            var emptyView = new ctor(pullIntoDescriptor.buffer, pullIntoDescriptor.byteOffset, 0);
            return promiseResolvedWith(ReadableStreamCreateReadResult(emptyView, true, stream._reader._forAuthorCode));
        }
        if (controller._queueTotalSize > 0) {
            if (ReadableByteStreamControllerFillPullIntoDescriptorFromQueue(controller, pullIntoDescriptor) === true) {
                var filledView = ReadableByteStreamControllerConvertPullIntoDescriptor(pullIntoDescriptor);
                ReadableByteStreamControllerHandleQueueDrain(controller);
                return promiseResolvedWith(ReadableStreamCreateReadResult(filledView, false, stream._reader._forAuthorCode));
            }
            if (controller._closeRequested === true) {
                var e = new TypeError('Insufficient bytes to fill elements in the given buffer');
                ReadableByteStreamControllerError(controller, e);
                return promiseRejectedWith(e);
            }
        }
        controller._pendingPullIntos.push(pullIntoDescriptor);
        var promise = ReadableStreamAddReadIntoRequest(stream);
        ReadableByteStreamControllerCallPullIfNeeded(controller);
        return promise;
    }
    function ReadableByteStreamControllerRespondInClosedState(controller, firstDescriptor) {
        firstDescriptor.buffer = TransferArrayBuffer(firstDescriptor.buffer);
        var stream = controller._controlledReadableByteStream;
        if (ReadableStreamHasBYOBReader(stream) === true) {
            while (ReadableStreamGetNumReadIntoRequests(stream) > 0) {
                var pullIntoDescriptor = ReadableByteStreamControllerShiftPendingPullInto(controller);
                ReadableByteStreamControllerCommitPullIntoDescriptor(stream, pullIntoDescriptor);
            }
        }
    }
    function ReadableByteStreamControllerRespondInReadableState(controller, bytesWritten, pullIntoDescriptor) {
        if (pullIntoDescriptor.bytesFilled + bytesWritten > pullIntoDescriptor.byteLength) {
            throw new RangeError('bytesWritten out of range');
        }
        ReadableByteStreamControllerFillHeadPullIntoDescriptor(controller, bytesWritten, pullIntoDescriptor);
        if (pullIntoDescriptor.bytesFilled < pullIntoDescriptor.elementSize) {
            // TODO: Figure out whether we should detach the buffer or not here.
            return;
        }
        ReadableByteStreamControllerShiftPendingPullInto(controller);
        var remainderSize = pullIntoDescriptor.bytesFilled % pullIntoDescriptor.elementSize;
        if (remainderSize > 0) {
            var end = pullIntoDescriptor.byteOffset + pullIntoDescriptor.bytesFilled;
            var remainder = pullIntoDescriptor.buffer.slice(end - remainderSize, end);
            ReadableByteStreamControllerEnqueueChunkToQueue(controller, remainder, 0, remainder.byteLength);
        }
        pullIntoDescriptor.buffer = TransferArrayBuffer(pullIntoDescriptor.buffer);
        pullIntoDescriptor.bytesFilled -= remainderSize;
        ReadableByteStreamControllerCommitPullIntoDescriptor(controller._controlledReadableByteStream, pullIntoDescriptor);
        ReadableByteStreamControllerProcessPullIntoDescriptorsUsingQueue(controller);
    }
    function ReadableByteStreamControllerRespondInternal(controller, bytesWritten) {
        var firstDescriptor = controller._pendingPullIntos.peek();
        var stream = controller._controlledReadableByteStream;
        if (stream._state === 'closed') {
            if (bytesWritten !== 0) {
                throw new TypeError('bytesWritten must be 0 when calling respond() on a closed stream');
            }
            ReadableByteStreamControllerRespondInClosedState(controller, firstDescriptor);
        }
        else {
            ReadableByteStreamControllerRespondInReadableState(controller, bytesWritten, firstDescriptor);
        }
        ReadableByteStreamControllerCallPullIfNeeded(controller);
    }
    function ReadableByteStreamControllerShiftPendingPullInto(controller) {
        var descriptor = controller._pendingPullIntos.shift();
        ReadableByteStreamControllerInvalidateBYOBRequest(controller);
        return descriptor;
    }
    function ReadableByteStreamControllerShouldCallPull(controller) {
        var stream = controller._controlledReadableByteStream;
        if (stream._state !== 'readable') {
            return false;
        }
        if (controller._closeRequested === true) {
            return false;
        }
        if (controller._started === false) {
            return false;
        }
        if (ReadableStreamHasDefaultReader(stream) === true && ReadableStreamGetNumReadRequests(stream) > 0) {
            return true;
        }
        if (ReadableStreamHasBYOBReader(stream) === true && ReadableStreamGetNumReadIntoRequests(stream) > 0) {
            return true;
        }
        var desiredSize = ReadableByteStreamControllerGetDesiredSize(controller);
        if (desiredSize > 0) {
            return true;
        }
        return false;
    }
    function ReadableByteStreamControllerClearAlgorithms(controller) {
        controller._pullAlgorithm = undefined;
        controller._cancelAlgorithm = undefined;
    }
    // A client of ReadableByteStreamController may use these functions directly to bypass state check.
    function ReadableByteStreamControllerClose(controller) {
        var stream = controller._controlledReadableByteStream;
        if (controller._queueTotalSize > 0) {
            controller._closeRequested = true;
            return;
        }
        if (controller._pendingPullIntos.length > 0) {
            var firstPendingPullInto = controller._pendingPullIntos.peek();
            if (firstPendingPullInto.bytesFilled > 0) {
                var e = new TypeError('Insufficient bytes to fill elements in the given buffer');
                ReadableByteStreamControllerError(controller, e);
                throw e;
            }
        }
        ReadableByteStreamControllerClearAlgorithms(controller);
        ReadableStreamClose(stream);
    }
    function ReadableByteStreamControllerEnqueue(controller, chunk) {
        var stream = controller._controlledReadableByteStream;
        var buffer = chunk.buffer;
        var byteOffset = chunk.byteOffset;
        var byteLength = chunk.byteLength;
        var transferredBuffer = TransferArrayBuffer(buffer);
        if (ReadableStreamHasDefaultReader(stream) === true) {
            if (ReadableStreamGetNumReadRequests(stream) === 0) {
                ReadableByteStreamControllerEnqueueChunkToQueue(controller, transferredBuffer, byteOffset, byteLength);
            }
            else {
                var transferredView = new Uint8Array(transferredBuffer, byteOffset, byteLength);
                ReadableStreamFulfillReadRequest(stream, transferredView, false);
            }
        }
        else if (ReadableStreamHasBYOBReader(stream) === true) {
            // TODO: Ideally in this branch detaching should happen only if the buffer is not consumed fully.
            ReadableByteStreamControllerEnqueueChunkToQueue(controller, transferredBuffer, byteOffset, byteLength);
            ReadableByteStreamControllerProcessPullIntoDescriptorsUsingQueue(controller);
        }
        else {
            ReadableByteStreamControllerEnqueueChunkToQueue(controller, transferredBuffer, byteOffset, byteLength);
        }
        ReadableByteStreamControllerCallPullIfNeeded(controller);
    }
    function ReadableByteStreamControllerError(controller, e) {
        var stream = controller._controlledReadableByteStream;
        if (stream._state !== 'readable') {
            return;
        }
        ReadableByteStreamControllerClearPendingPullIntos(controller);
        ResetQueue(controller);
        ReadableByteStreamControllerClearAlgorithms(controller);
        ReadableStreamError(stream, e);
    }
    function ReadableByteStreamControllerGetDesiredSize(controller) {
        var stream = controller._controlledReadableByteStream;
        var state = stream._state;
        if (state === 'errored') {
            return null;
        }
        if (state === 'closed') {
            return 0;
        }
        return controller._strategyHWM - controller._queueTotalSize;
    }
    function ReadableByteStreamControllerRespond(controller, bytesWritten) {
        bytesWritten = Number(bytesWritten);
        if (IsFiniteNonNegativeNumber(bytesWritten) === false) {
            throw new RangeError('bytesWritten must be a finite');
        }
        ReadableByteStreamControllerRespondInternal(controller, bytesWritten);
    }
    function ReadableByteStreamControllerRespondWithNewView(controller, view) {
        var firstDescriptor = controller._pendingPullIntos.peek();
        if (firstDescriptor.byteOffset + firstDescriptor.bytesFilled !== view.byteOffset) {
            throw new RangeError('The region specified by view does not match byobRequest');
        }
        if (firstDescriptor.byteLength !== view.byteLength) {
            throw new RangeError('The buffer of view has different capacity than byobRequest');
        }
        firstDescriptor.buffer = view.buffer;
        ReadableByteStreamControllerRespondInternal(controller, view.byteLength);
    }
    function SetUpReadableByteStreamController(stream, controller, startAlgorithm, pullAlgorithm, cancelAlgorithm, highWaterMark, autoAllocateChunkSize) {
        controller._controlledReadableByteStream = stream;
        controller._pullAgain = false;
        controller._pulling = false;
        controller._byobRequest = undefined;
        // Need to set the slots so that the assert doesn't fire. In the spec the slots already exist implicitly.
        controller._queue = controller._queueTotalSize = undefined;
        ResetQueue(controller);
        controller._closeRequested = false;
        controller._started = false;
        controller._strategyHWM = ValidateAndNormalizeHighWaterMark(highWaterMark);
        controller._pullAlgorithm = pullAlgorithm;
        controller._cancelAlgorithm = cancelAlgorithm;
        controller._autoAllocateChunkSize = autoAllocateChunkSize;
        controller._pendingPullIntos = new SimpleQueue();
        stream._readableStreamController = controller;
        var startResult = startAlgorithm();
        uponPromise(promiseResolvedWith(startResult), function () {
            controller._started = true;
            ReadableByteStreamControllerCallPullIfNeeded(controller);
        }, function (r) {
            ReadableByteStreamControllerError(controller, r);
        });
    }
    function SetUpReadableByteStreamControllerFromUnderlyingSource(stream, underlyingByteSource, highWaterMark) {
        var controller = Object.create(ReadableByteStreamController.prototype);
        function startAlgorithm() {
            return InvokeOrNoop(underlyingByteSource, 'start', [controller]);
        }
        var pullAlgorithm = CreateAlgorithmFromUnderlyingMethod(underlyingByteSource, 'pull', 0, [controller]);
        var cancelAlgorithm = CreateAlgorithmFromUnderlyingMethod(underlyingByteSource, 'cancel', 1, []);
        var autoAllocateChunkSize = underlyingByteSource.autoAllocateChunkSize;
        if (autoAllocateChunkSize !== undefined) {
            autoAllocateChunkSize = Number(autoAllocateChunkSize);
            if (NumberIsInteger(autoAllocateChunkSize) === false || autoAllocateChunkSize <= 0) {
                throw new RangeError('autoAllocateChunkSize must be a positive integer');
            }
        }
        SetUpReadableByteStreamController(stream, controller, startAlgorithm, pullAlgorithm, cancelAlgorithm, highWaterMark, autoAllocateChunkSize);
    }
    function SetUpReadableStreamBYOBRequest(request, controller, view) {
        request._associatedReadableByteStreamController = controller;
        request._view = view;
    }
    // Helper functions for the ReadableStreamBYOBRequest.
    function byobRequestBrandCheckException(name) {
        return new TypeError("ReadableStreamBYOBRequest.prototype." + name + " can only be used on a ReadableStreamBYOBRequest");
    }
    // Helper functions for the ReadableByteStreamController.
    function byteStreamControllerBrandCheckException(name) {
        return new TypeError("ReadableByteStreamController.prototype." + name + " can only be used on a ReadableByteStreamController");
    }

    // Abstract operations for the ReadableStream.
    function AcquireReadableStreamBYOBReader(stream, forAuthorCode) {
        if (forAuthorCode === void 0) { forAuthorCode = false; }
        var reader = new ReadableStreamBYOBReader(stream);
        reader._forAuthorCode = forAuthorCode;
        return reader;
    }
    // ReadableStream API exposed for controllers.
    function ReadableStreamAddReadIntoRequest(stream) {
        var promise = newPromise(function (resolve, reject) {
            var readIntoRequest = {
                _resolve: resolve,
                _reject: reject
            };
            stream._reader._readIntoRequests.push(readIntoRequest);
        });
        return promise;
    }
    function ReadableStreamFulfillReadIntoRequest(stream, chunk, done) {
        var reader = stream._reader;
        var readIntoRequest = reader._readIntoRequests.shift();
        readIntoRequest._resolve(ReadableStreamCreateReadResult(chunk, done, reader._forAuthorCode));
    }
    function ReadableStreamGetNumReadIntoRequests(stream) {
        return stream._reader._readIntoRequests.length;
    }
    function ReadableStreamHasBYOBReader(stream) {
        var reader = stream._reader;
        if (reader === undefined) {
            return false;
        }
        if (!IsReadableStreamBYOBReader(reader)) {
            return false;
        }
        return true;
    }
    var ReadableStreamBYOBReader = /** @class */ (function () {
        function ReadableStreamBYOBReader(stream) {
            if (!IsReadableStream(stream)) {
                throw new TypeError('ReadableStreamBYOBReader can only be constructed with a ReadableStream instance given a ' +
                    'byte source');
            }
            if (IsReadableByteStreamController(stream._readableStreamController) === false) {
                throw new TypeError('Cannot construct a ReadableStreamBYOBReader for a stream not constructed with a byte ' +
                    'source');
            }
            if (IsReadableStreamLocked(stream)) {
                throw new TypeError('This stream has already been locked for exclusive reading by another reader');
            }
            ReadableStreamReaderGenericInitialize(this, stream);
            this._readIntoRequests = new SimpleQueue();
        }
        Object.defineProperty(ReadableStreamBYOBReader.prototype, "closed", {
            get: function () {
                if (!IsReadableStreamBYOBReader(this)) {
                    return promiseRejectedWith(byobReaderBrandCheckException('closed'));
                }
                return this._closedPromise;
            },
            enumerable: true,
            configurable: true
        });
        ReadableStreamBYOBReader.prototype.cancel = function (reason) {
            if (!IsReadableStreamBYOBReader(this)) {
                return promiseRejectedWith(byobReaderBrandCheckException('cancel'));
            }
            if (this._ownerReadableStream === undefined) {
                return promiseRejectedWith(readerLockException('cancel'));
            }
            return ReadableStreamReaderGenericCancel(this, reason);
        };
        ReadableStreamBYOBReader.prototype.read = function (view) {
            if (!IsReadableStreamBYOBReader(this)) {
                return promiseRejectedWith(byobReaderBrandCheckException('read'));
            }
            if (this._ownerReadableStream === undefined) {
                return promiseRejectedWith(readerLockException('read from'));
            }
            if (!ArrayBuffer.isView(view)) {
                return promiseRejectedWith(new TypeError('view must be an array buffer view'));
            }
            if (IsDetachedBuffer(view.buffer) === true) ;
            if (view.byteLength === 0) {
                return promiseRejectedWith(new TypeError('view must have non-zero byteLength'));
            }
            return ReadableStreamBYOBReaderRead(this, view);
        };
        ReadableStreamBYOBReader.prototype.releaseLock = function () {
            if (!IsReadableStreamBYOBReader(this)) {
                throw byobReaderBrandCheckException('releaseLock');
            }
            if (this._ownerReadableStream === undefined) {
                return;
            }
            if (this._readIntoRequests.length > 0) {
                throw new TypeError('Tried to release a reader lock when that reader has pending read() calls un-settled');
            }
            ReadableStreamReaderGenericRelease(this);
        };
        return ReadableStreamBYOBReader;
    }());
    // Abstract operations for the readers.
    function IsReadableStreamBYOBReader(x) {
        if (!typeIsObject(x)) {
            return false;
        }
        if (!Object.prototype.hasOwnProperty.call(x, '_readIntoRequests')) {
            return false;
        }
        return true;
    }
    function ReadableStreamBYOBReaderRead(reader, view) {
        var stream = reader._ownerReadableStream;
        stream._disturbed = true;
        if (stream._state === 'errored') {
            return promiseRejectedWith(stream._storedError);
        }
        // Controllers must implement this.
        return ReadableByteStreamControllerPullInto(stream._readableStreamController, view);
    }
    // Helper functions for the ReadableStreamBYOBReader.
    function byobReaderBrandCheckException(name) {
        return new TypeError("ReadableStreamBYOBReader.prototype." + name + " can only be used on a ReadableStreamBYOBReader");
    }

    var ReadableStream = /** @class */ (function () {
        function ReadableStream(underlyingSource, strategy) {
            if (underlyingSource === void 0) { underlyingSource = {}; }
            if (strategy === void 0) { strategy = {}; }
            InitializeReadableStream(this);
            var size = strategy.size;
            var highWaterMark = strategy.highWaterMark;
            var type = underlyingSource.type;
            var typeString = String(type);
            if (typeString === 'bytes') {
                if (size !== undefined) {
                    throw new RangeError('The strategy for a byte stream cannot have a size function');
                }
                if (highWaterMark === undefined) {
                    highWaterMark = 0;
                }
                highWaterMark = ValidateAndNormalizeHighWaterMark(highWaterMark);
                SetUpReadableByteStreamControllerFromUnderlyingSource(this, underlyingSource, highWaterMark);
            }
            else if (type === undefined) {
                var sizeAlgorithm = MakeSizeAlgorithmFromSizeFunction(size);
                if (highWaterMark === undefined) {
                    highWaterMark = 1;
                }
                highWaterMark = ValidateAndNormalizeHighWaterMark(highWaterMark);
                SetUpReadableStreamDefaultControllerFromUnderlyingSource(this, underlyingSource, highWaterMark, sizeAlgorithm);
            }
            else {
                throw new RangeError('Invalid type is specified');
            }
        }
        Object.defineProperty(ReadableStream.prototype, "locked", {
            get: function () {
                if (IsReadableStream(this) === false) {
                    throw streamBrandCheckException$1('locked');
                }
                return IsReadableStreamLocked(this);
            },
            enumerable: true,
            configurable: true
        });
        ReadableStream.prototype.cancel = function (reason) {
            if (IsReadableStream(this) === false) {
                return promiseRejectedWith(streamBrandCheckException$1('cancel'));
            }
            if (IsReadableStreamLocked(this) === true) {
                return promiseRejectedWith(new TypeError('Cannot cancel a stream that already has a reader'));
            }
            return ReadableStreamCancel(this, reason);
        };
        ReadableStream.prototype.getReader = function (_a) {
            var mode = (_a === void 0 ? {} : _a).mode;
            if (IsReadableStream(this) === false) {
                throw streamBrandCheckException$1('getReader');
            }
            if (mode === undefined) {
                return AcquireReadableStreamDefaultReader(this, true);
            }
            mode = String(mode);
            if (mode === 'byob') {
                return AcquireReadableStreamBYOBReader(this, true);
            }
            throw new RangeError('Invalid mode is specified');
        };
        ReadableStream.prototype.pipeThrough = function (_a, _b) {
            var writable = _a.writable, readable = _a.readable;
            var _c = _b === void 0 ? {} : _b, preventClose = _c.preventClose, preventAbort = _c.preventAbort, preventCancel = _c.preventCancel, signal = _c.signal;
            if (IsReadableStream(this) === false) {
                throw streamBrandCheckException$1('pipeThrough');
            }
            if (IsWritableStream(writable) === false) {
                throw new TypeError('writable argument to pipeThrough must be a WritableStream');
            }
            if (IsReadableStream(readable) === false) {
                throw new TypeError('readable argument to pipeThrough must be a ReadableStream');
            }
            preventClose = Boolean(preventClose);
            preventAbort = Boolean(preventAbort);
            preventCancel = Boolean(preventCancel);
            if (signal !== undefined && !isAbortSignal(signal)) {
                throw new TypeError('ReadableStream.prototype.pipeThrough\'s signal option must be an AbortSignal');
            }
            if (IsReadableStreamLocked(this) === true) {
                throw new TypeError('ReadableStream.prototype.pipeThrough cannot be used on a locked ReadableStream');
            }
            if (IsWritableStreamLocked(writable) === true) {
                throw new TypeError('ReadableStream.prototype.pipeThrough cannot be used on a locked WritableStream');
            }
            var promise = ReadableStreamPipeTo(this, writable, preventClose, preventAbort, preventCancel, signal);
            setPromiseIsHandledToTrue(promise);
            return readable;
        };
        ReadableStream.prototype.pipeTo = function (dest, _a) {
            var _b = _a === void 0 ? {} : _a, preventClose = _b.preventClose, preventAbort = _b.preventAbort, preventCancel = _b.preventCancel, signal = _b.signal;
            if (IsReadableStream(this) === false) {
                return promiseRejectedWith(streamBrandCheckException$1('pipeTo'));
            }
            if (IsWritableStream(dest) === false) {
                return promiseRejectedWith(new TypeError('ReadableStream.prototype.pipeTo\'s first argument must be a WritableStream'));
            }
            preventClose = Boolean(preventClose);
            preventAbort = Boolean(preventAbort);
            preventCancel = Boolean(preventCancel);
            if (signal !== undefined && !isAbortSignal(signal)) {
                return promiseRejectedWith(new TypeError('ReadableStream.prototype.pipeTo\'s signal option must be an AbortSignal'));
            }
            if (IsReadableStreamLocked(this) === true) {
                return promiseRejectedWith(new TypeError('ReadableStream.prototype.pipeTo cannot be used on a locked ReadableStream'));
            }
            if (IsWritableStreamLocked(dest) === true) {
                return promiseRejectedWith(new TypeError('ReadableStream.prototype.pipeTo cannot be used on a locked WritableStream'));
            }
            return ReadableStreamPipeTo(this, dest, preventClose, preventAbort, preventCancel, signal);
        };
        ReadableStream.prototype.tee = function () {
            if (IsReadableStream(this) === false) {
                throw streamBrandCheckException$1('tee');
            }
            var branches = ReadableStreamTee(this);
            return createArrayFromList(branches);
        };
        ReadableStream.prototype.getIterator = function (_a) {
            var _b = (_a === void 0 ? {} : _a).preventCancel, preventCancel = _b === void 0 ? false : _b;
            if (IsReadableStream(this) === false) {
                throw streamBrandCheckException$1('getIterator');
            }
            return AcquireReadableStreamAsyncIterator(this, preventCancel);
        };
        return ReadableStream;
    }());
    if (typeof SymbolPolyfill.asyncIterator === 'symbol') {
        Object.defineProperty(ReadableStream.prototype, SymbolPolyfill.asyncIterator, {
            value: ReadableStream.prototype.getIterator,
            enumerable: false,
            writable: true,
            configurable: true
        });
    }
    // Abstract operations for the ReadableStream.
    // Throws if and only if startAlgorithm throws.
    function CreateReadableStream(startAlgorithm, pullAlgorithm, cancelAlgorithm, highWaterMark, sizeAlgorithm) {
        if (highWaterMark === void 0) { highWaterMark = 1; }
        if (sizeAlgorithm === void 0) { sizeAlgorithm = function () { return 1; }; }
        var stream = Object.create(ReadableStream.prototype);
        InitializeReadableStream(stream);
        var controller = Object.create(ReadableStreamDefaultController.prototype);
        SetUpReadableStreamDefaultController(stream, controller, startAlgorithm, pullAlgorithm, cancelAlgorithm, highWaterMark, sizeAlgorithm);
        return stream;
    }
    function InitializeReadableStream(stream) {
        stream._state = 'readable';
        stream._reader = undefined;
        stream._storedError = undefined;
        stream._disturbed = false;
    }
    function IsReadableStream(x) {
        if (!typeIsObject(x)) {
            return false;
        }
        if (!Object.prototype.hasOwnProperty.call(x, '_readableStreamController')) {
            return false;
        }
        return true;
    }
    function IsReadableStreamLocked(stream) {
        if (stream._reader === undefined) {
            return false;
        }
        return true;
    }
    // ReadableStream API exposed for controllers.
    function ReadableStreamCancel(stream, reason) {
        stream._disturbed = true;
        if (stream._state === 'closed') {
            return promiseResolvedWith(undefined);
        }
        if (stream._state === 'errored') {
            return promiseRejectedWith(stream._storedError);
        }
        ReadableStreamClose(stream);
        var sourceCancelPromise = stream._readableStreamController[CancelSteps](reason);
        return transformPromiseWith(sourceCancelPromise, noop);
    }
    function ReadableStreamClose(stream) {
        stream._state = 'closed';
        var reader = stream._reader;
        if (reader === undefined) {
            return;
        }
        if (IsReadableStreamDefaultReader(reader)) {
            reader._readRequests.forEach(function (readRequest) {
                readRequest._resolve(ReadableStreamCreateReadResult(undefined, true, reader._forAuthorCode));
            });
            reader._readRequests = new SimpleQueue();
        }
        defaultReaderClosedPromiseResolve(reader);
    }
    function ReadableStreamError(stream, e) {
        stream._state = 'errored';
        stream._storedError = e;
        var reader = stream._reader;
        if (reader === undefined) {
            return;
        }
        if (IsReadableStreamDefaultReader(reader)) {
            reader._readRequests.forEach(function (readRequest) {
                readRequest._reject(e);
            });
            reader._readRequests = new SimpleQueue();
        }
        else {
            reader._readIntoRequests.forEach(function (readIntoRequest) {
                readIntoRequest._reject(e);
            });
            reader._readIntoRequests = new SimpleQueue();
        }
        defaultReaderClosedPromiseReject(reader, e);
    }
    // Helper functions for the ReadableStream.
    function streamBrandCheckException$1(name) {
        return new TypeError("ReadableStream.prototype." + name + " can only be used on a ReadableStream");
    }

    var ByteLengthQueuingStrategy = /** @class */ (function () {
        function ByteLengthQueuingStrategy(_a) {
            var highWaterMark = _a.highWaterMark;
            this.highWaterMark = highWaterMark;
        }
        ByteLengthQueuingStrategy.prototype.size = function (chunk) {
            return chunk.byteLength;
        };
        return ByteLengthQueuingStrategy;
    }());

    var CountQueuingStrategy = /** @class */ (function () {
        function CountQueuingStrategy(_a) {
            var highWaterMark = _a.highWaterMark;
            this.highWaterMark = highWaterMark;
        }
        CountQueuingStrategy.prototype.size = function () {
            return 1;
        };
        return CountQueuingStrategy;
    }());

    // Class TransformStream
    var TransformStream = /** @class */ (function () {
        function TransformStream(transformer, writableStrategy, readableStrategy) {
            if (transformer === void 0) { transformer = {}; }
            if (writableStrategy === void 0) { writableStrategy = {}; }
            if (readableStrategy === void 0) { readableStrategy = {}; }
            var writableSizeFunction = writableStrategy.size;
            var writableHighWaterMark = writableStrategy.highWaterMark;
            var readableSizeFunction = readableStrategy.size;
            var readableHighWaterMark = readableStrategy.highWaterMark;
            var writableType = transformer.writableType;
            if (writableType !== undefined) {
                throw new RangeError('Invalid writable type specified');
            }
            var writableSizeAlgorithm = MakeSizeAlgorithmFromSizeFunction(writableSizeFunction);
            if (writableHighWaterMark === undefined) {
                writableHighWaterMark = 1;
            }
            writableHighWaterMark = ValidateAndNormalizeHighWaterMark(writableHighWaterMark);
            var readableType = transformer.readableType;
            if (readableType !== undefined) {
                throw new RangeError('Invalid readable type specified');
            }
            var readableSizeAlgorithm = MakeSizeAlgorithmFromSizeFunction(readableSizeFunction);
            if (readableHighWaterMark === undefined) {
                readableHighWaterMark = 0;
            }
            readableHighWaterMark = ValidateAndNormalizeHighWaterMark(readableHighWaterMark);
            var startPromise_resolve;
            var startPromise = newPromise(function (resolve) {
                startPromise_resolve = resolve;
            });
            InitializeTransformStream(this, startPromise, writableHighWaterMark, writableSizeAlgorithm, readableHighWaterMark, readableSizeAlgorithm);
            SetUpTransformStreamDefaultControllerFromTransformer(this, transformer);
            var startResult = InvokeOrNoop(transformer, 'start', [this._transformStreamController]);
            startPromise_resolve(startResult);
        }
        Object.defineProperty(TransformStream.prototype, "readable", {
            get: function () {
                if (IsTransformStream(this) === false) {
                    throw streamBrandCheckException$2('readable');
                }
                return this._readable;
            },
            enumerable: true,
            configurable: true
        });
        Object.defineProperty(TransformStream.prototype, "writable", {
            get: function () {
                if (IsTransformStream(this) === false) {
                    throw streamBrandCheckException$2('writable');
                }
                return this._writable;
            },
            enumerable: true,
            configurable: true
        });
        return TransformStream;
    }());
    function InitializeTransformStream(stream, startPromise, writableHighWaterMark, writableSizeAlgorithm, readableHighWaterMark, readableSizeAlgorithm) {
        function startAlgorithm() {
            return startPromise;
        }
        function writeAlgorithm(chunk) {
            return TransformStreamDefaultSinkWriteAlgorithm(stream, chunk);
        }
        function abortAlgorithm(reason) {
            return TransformStreamDefaultSinkAbortAlgorithm(stream, reason);
        }
        function closeAlgorithm() {
            return TransformStreamDefaultSinkCloseAlgorithm(stream);
        }
        stream._writable = CreateWritableStream(startAlgorithm, writeAlgorithm, closeAlgorithm, abortAlgorithm, writableHighWaterMark, writableSizeAlgorithm);
        function pullAlgorithm() {
            return TransformStreamDefaultSourcePullAlgorithm(stream);
        }
        function cancelAlgorithm(reason) {
            TransformStreamErrorWritableAndUnblockWrite(stream, reason);
            return promiseResolvedWith(undefined);
        }
        stream._readable = CreateReadableStream(startAlgorithm, pullAlgorithm, cancelAlgorithm, readableHighWaterMark, readableSizeAlgorithm);
        // The [[backpressure]] slot is set to undefined so that it can be initialised by TransformStreamSetBackpressure.
        stream._backpressure = undefined;
        stream._backpressureChangePromise = undefined;
        stream._backpressureChangePromise_resolve = undefined;
        TransformStreamSetBackpressure(stream, true);
        // Used by IsWritableStream() which is called by SetUpTransformStreamDefaultController().
        stream._transformStreamController = undefined;
    }
    function IsTransformStream(x) {
        if (!typeIsObject(x)) {
            return false;
        }
        if (!Object.prototype.hasOwnProperty.call(x, '_transformStreamController')) {
            return false;
        }
        return true;
    }
    // This is a no-op if both sides are already errored.
    function TransformStreamError(stream, e) {
        ReadableStreamDefaultControllerError(stream._readable._readableStreamController, e);
        TransformStreamErrorWritableAndUnblockWrite(stream, e);
    }
    function TransformStreamErrorWritableAndUnblockWrite(stream, e) {
        TransformStreamDefaultControllerClearAlgorithms(stream._transformStreamController);
        WritableStreamDefaultControllerErrorIfNeeded(stream._writable._writableStreamController, e);
        if (stream._backpressure === true) {
            // Pretend that pull() was called to permit any pending write() calls to complete. TransformStreamSetBackpressure()
            // cannot be called from enqueue() or pull() once the ReadableStream is errored, so this will will be the final time
            // _backpressure is set.
            TransformStreamSetBackpressure(stream, false);
        }
    }
    function TransformStreamSetBackpressure(stream, backpressure) {
        // Passes also when called during construction.
        if (stream._backpressureChangePromise !== undefined) {
            stream._backpressureChangePromise_resolve();
        }
        stream._backpressureChangePromise = newPromise(function (resolve) {
            stream._backpressureChangePromise_resolve = resolve;
        });
        stream._backpressure = backpressure;
    }
    var TransformStreamDefaultController = /** @class */ (function () {
        /** @internal */
        function TransformStreamDefaultController() {
            throw new TypeError('TransformStreamDefaultController instances cannot be created directly');
        }
        Object.defineProperty(TransformStreamDefaultController.prototype, "desiredSize", {
            get: function () {
                if (IsTransformStreamDefaultController(this) === false) {
                    throw defaultControllerBrandCheckException$1('desiredSize');
                }
                var readableController = this._controlledTransformStream._readable._readableStreamController;
                return ReadableStreamDefaultControllerGetDesiredSize(readableController);
            },
            enumerable: true,
            configurable: true
        });
        TransformStreamDefaultController.prototype.enqueue = function (chunk) {
            if (IsTransformStreamDefaultController(this) === false) {
                throw defaultControllerBrandCheckException$1('enqueue');
            }
            TransformStreamDefaultControllerEnqueue(this, chunk);
        };
        TransformStreamDefaultController.prototype.error = function (reason) {
            if (IsTransformStreamDefaultController(this) === false) {
                throw defaultControllerBrandCheckException$1('error');
            }
            TransformStreamDefaultControllerError(this, reason);
        };
        TransformStreamDefaultController.prototype.terminate = function () {
            if (IsTransformStreamDefaultController(this) === false) {
                throw defaultControllerBrandCheckException$1('terminate');
            }
            TransformStreamDefaultControllerTerminate(this);
        };
        return TransformStreamDefaultController;
    }());
    // Transform Stream Default Controller Abstract Operations
    function IsTransformStreamDefaultController(x) {
        if (!typeIsObject(x)) {
            return false;
        }
        if (!Object.prototype.hasOwnProperty.call(x, '_controlledTransformStream')) {
            return false;
        }
        return true;
    }
    function SetUpTransformStreamDefaultController(stream, controller, transformAlgorithm, flushAlgorithm) {
        controller._controlledTransformStream = stream;
        stream._transformStreamController = controller;
        controller._transformAlgorithm = transformAlgorithm;
        controller._flushAlgorithm = flushAlgorithm;
    }
    function SetUpTransformStreamDefaultControllerFromTransformer(stream, transformer) {
        var controller = Object.create(TransformStreamDefaultController.prototype);
        var transformAlgorithm = function (chunk) {
            try {
                TransformStreamDefaultControllerEnqueue(controller, chunk);
                return promiseResolvedWith(undefined);
            }
            catch (transformResultE) {
                return promiseRejectedWith(transformResultE);
            }
        };
        var transformMethod = transformer.transform;
        if (transformMethod !== undefined) {
            if (typeof transformMethod !== 'function') {
                throw new TypeError('transform is not a method');
            }
            transformAlgorithm = function (chunk) { return PromiseCall(transformMethod, transformer, [chunk, controller]); };
        }
        var flushAlgorithm = CreateAlgorithmFromUnderlyingMethod(transformer, 'flush', 0, [controller]);
        SetUpTransformStreamDefaultController(stream, controller, transformAlgorithm, flushAlgorithm);
    }
    function TransformStreamDefaultControllerClearAlgorithms(controller) {
        controller._transformAlgorithm = undefined;
        controller._flushAlgorithm = undefined;
    }
    function TransformStreamDefaultControllerEnqueue(controller, chunk) {
        var stream = controller._controlledTransformStream;
        var readableController = stream._readable._readableStreamController;
        if (ReadableStreamDefaultControllerCanCloseOrEnqueue(readableController) === false) {
            throw new TypeError('Readable side is not in a state that permits enqueue');
        }
        // We throttle transform invocations based on the backpressure of the ReadableStream, but we still
        // accept TransformStreamDefaultControllerEnqueue() calls.
        try {
            ReadableStreamDefaultControllerEnqueue(readableController, chunk);
        }
        catch (e) {
            // This happens when readableStrategy.size() throws.
            TransformStreamErrorWritableAndUnblockWrite(stream, e);
            throw stream._readable._storedError;
        }
        var backpressure = ReadableStreamDefaultControllerHasBackpressure(readableController);
        if (backpressure !== stream._backpressure) {
            TransformStreamSetBackpressure(stream, true);
        }
    }
    function TransformStreamDefaultControllerError(controller, e) {
        TransformStreamError(controller._controlledTransformStream, e);
    }
    function TransformStreamDefaultControllerPerformTransform(controller, chunk) {
        var transformPromise = controller._transformAlgorithm(chunk);
        return transformPromiseWith(transformPromise, undefined, function (r) {
            TransformStreamError(controller._controlledTransformStream, r);
            throw r;
        });
    }
    function TransformStreamDefaultControllerTerminate(controller) {
        var stream = controller._controlledTransformStream;
        var readableController = stream._readable._readableStreamController;
        if (ReadableStreamDefaultControllerCanCloseOrEnqueue(readableController) === true) {
            ReadableStreamDefaultControllerClose(readableController);
        }
        var error = new TypeError('TransformStream terminated');
        TransformStreamErrorWritableAndUnblockWrite(stream, error);
    }
    // TransformStreamDefaultSink Algorithms
    function TransformStreamDefaultSinkWriteAlgorithm(stream, chunk) {
        var controller = stream._transformStreamController;
        if (stream._backpressure === true) {
            var backpressureChangePromise = stream._backpressureChangePromise;
            return transformPromiseWith(backpressureChangePromise, function () {
                var writable = stream._writable;
                var state = writable._state;
                if (state === 'erroring') {
                    throw writable._storedError;
                }
                return TransformStreamDefaultControllerPerformTransform(controller, chunk);
            });
        }
        return TransformStreamDefaultControllerPerformTransform(controller, chunk);
    }
    function TransformStreamDefaultSinkAbortAlgorithm(stream, reason) {
        // abort() is not called synchronously, so it is possible for abort() to be called when the stream is already
        // errored.
        TransformStreamError(stream, reason);
        return promiseResolvedWith(undefined);
    }
    function TransformStreamDefaultSinkCloseAlgorithm(stream) {
        // stream._readable cannot change after construction, so caching it across a call to user code is safe.
        var readable = stream._readable;
        var controller = stream._transformStreamController;
        var flushPromise = controller._flushAlgorithm();
        TransformStreamDefaultControllerClearAlgorithms(controller);
        // Return a promise that is fulfilled with undefined on success.
        return transformPromiseWith(flushPromise, function () {
            if (readable._state === 'errored') {
                throw readable._storedError;
            }
            var readableController = readable._readableStreamController;
            if (ReadableStreamDefaultControllerCanCloseOrEnqueue(readableController) === true) {
                ReadableStreamDefaultControllerClose(readableController);
            }
        }, function (r) {
            TransformStreamError(stream, r);
            throw readable._storedError;
        });
    }
    // TransformStreamDefaultSource Algorithms
    function TransformStreamDefaultSourcePullAlgorithm(stream) {
        // Invariant. Enforced by the promises returned by start() and pull().
        TransformStreamSetBackpressure(stream, false);
        // Prevent the next pull() call until there is backpressure.
        return stream._backpressureChangePromise;
    }
    // Helper functions for the TransformStreamDefaultController.
    function defaultControllerBrandCheckException$1(name) {
        return new TypeError("TransformStreamDefaultController.prototype." + name + " can only be used on a TransformStreamDefaultController");
    }
    // Helper functions for the TransformStream.
    function streamBrandCheckException$2(name) {
        return new TypeError("TransformStream.prototype." + name + " can only be used on a TransformStream");
    }

    exports.ByteLengthQueuingStrategy = ByteLengthQueuingStrategy;
    exports.CountQueuingStrategy = CountQueuingStrategy;
    exports.ReadableStream = ReadableStream;
    exports.TransformStream = TransformStream;
    exports.WritableStream = WritableStream;

    Object.defineProperty(exports, '__esModule', { value: true });

})));
//# sourceMappingURL=ponyfill.js.map
