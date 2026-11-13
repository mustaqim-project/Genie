
        (function() {
            const _origWarn = console.warn;
            console.warn = function(...args) {
                if (typeof args[0] === 'string' && args[0].indexOf('cdn.tailwindcss.com') !== -1) return;
                _origWarn.apply(console, args);
            };
        })();
    