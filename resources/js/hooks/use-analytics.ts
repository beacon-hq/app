import { useEffect } from 'react';

export default function useAnalytics(enabled: boolean, src: string) {
    useEffect(() => {
        if (enabled) {
            // @ts-ignore
            let _mtm = (globalThis._mtm = globalThis?._mtm ?? []);

            _mtm.push({ 'mtm.startTime': Date.now(), event: 'mtm.Start' });
            (function () {
                let d = document,
                    g = d.createElement('script'),
                    s = d.getElementsByTagName('script')[0];
                g.async = true;
                g.src = src;
                s.parentNode?.insertBefore(g, s);
            })();
        }
    });
}
