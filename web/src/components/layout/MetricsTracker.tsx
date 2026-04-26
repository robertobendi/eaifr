'use client';

/**
 * Fires slatestack's privacy-friendly pageview endpoint on route changes.
 * The backend hashes visitor data with a salted one-way function — we do
 * not ship any cookies or client-side identifiers.
 */
import { useEffect } from 'react';
import { usePathname } from 'next/navigation';

export function MetricsTracker() {
  const pathname = usePathname();

  useEffect(() => {
    if (!pathname) return;
    const cms = process.env.NEXT_PUBLIC_CMS_URL || 'http://localhost:3000';
    const payload = {
      path: pathname,
      referrer: typeof document !== 'undefined' ? document.referrer : '',
    };
    try {
      // Fire and forget. Don't block navigation on analytics failures.
      fetch(`${cms}/api/metrics/pageview`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
        keepalive: true,
      }).catch(() => {});
    } catch {
      // ignore
    }
  }, [pathname]);

  return null;
}
