'use client';

import Image from 'next/image';
import Link from 'next/link';
import { usePathname } from 'next/navigation';
import { useState } from 'react';
import { cn } from '@/lib/cn';

const NAV = [
  { href: '/about', label: 'About' },
  { href: '/research', label: 'Research' },
  { href: '/academics', label: 'Academics' },
  { href: '/events', label: 'Events' },
  { href: '/news', label: 'News' },
  { href: '/people', label: 'People' },
  { href: '/publications', label: 'Publications' },
  { href: '/visit', label: 'Visit' },
];

export function Header() {
  const pathname = usePathname() || '/';
  const [open, setOpen] = useState(false);

  const isActive = (href: string) =>
    href === '/' ? pathname === '/' : pathname === href || pathname.startsWith(href + '/');

  return (
    <header className="sticky top-0 z-40 border-b border-ink/5 bg-warm/85 backdrop-blur supports-[backdrop-filter]:bg-warm/70">
      <div className="mx-auto flex max-w-7xl items-center gap-6 px-6 py-4 md:px-10">
        <Link href="/" className="group flex items-center gap-3" aria-label="EAIFR home">
          <Image
            src="/logos/eaifr-logo.png"
            alt="EAIFR"
            width={140}
            height={62}
            priority
            className="h-9 w-auto transition-opacity group-hover:opacity-90"
          />
          <span className="hidden font-sans text-[9.5px] font-bold uppercase tracking-[0.22em] text-mid sm:flex sm:flex-col sm:leading-relaxed sm:border-l sm:border-ink/10 sm:pl-3">
            <span>East African Institute</span>
            <span>for Fundamental Research</span>
          </span>
        </Link>

        <nav className="ml-auto hidden items-center gap-1 lg:flex" aria-label="Primary">
          {NAV.map((item) => (
            <Link
              key={item.href}
              href={item.href}
              className={cn(
                'rounded-full px-3 py-1.5 font-sans text-sm font-bold tracking-wide transition-colors',
                isActive(item.href)
                  ? 'bg-teal/10 text-teal'
                  : 'text-ink/70 hover:bg-ink/5 hover:text-ink',
              )}
            >
              {item.label}
            </Link>
          ))}
          <Link
            href="/apply"
            className="ml-3 inline-flex items-center gap-1.5 rounded-full bg-gold px-4 py-2 font-sans text-sm font-bold text-deep-navy hover:bg-[#c5992f]"
          >
            Apply
          </Link>
        </nav>

        <button
          type="button"
          aria-label="Open menu"
          aria-expanded={open}
          onClick={() => setOpen((s) => !s)}
          className="ml-auto flex h-10 w-10 items-center justify-center rounded-md text-ink lg:hidden"
        >
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
            {open ? (
              <>
                <line x1="18" y1="6" x2="6" y2="18" />
                <line x1="6" y1="6" x2="18" y2="18" />
              </>
            ) : (
              <>
                <line x1="3" y1="12" x2="21" y2="12" />
                <line x1="3" y1="6" x2="21" y2="6" />
                <line x1="3" y1="18" x2="21" y2="18" />
              </>
            )}
          </svg>
        </button>
      </div>

      {open && (
        <div className="border-t border-ink/5 bg-warm lg:hidden">
          <nav className="mx-auto flex max-w-7xl flex-col gap-1 px-6 py-4 md:px-10" aria-label="Mobile primary">
            {NAV.map((item) => (
              <Link
                key={item.href}
                href={item.href}
                onClick={() => setOpen(false)}
                className={cn(
                  'rounded-md px-3 py-2 font-sans text-sm font-bold tracking-wide',
                  isActive(item.href) ? 'bg-teal/10 text-teal' : 'text-ink/80',
                )}
              >
                {item.label}
              </Link>
            ))}
            <Link
              href="/apply"
              onClick={() => setOpen(false)}
              className="mt-2 inline-flex items-center justify-center rounded-full bg-gold px-4 py-2 font-sans text-sm font-bold text-deep-navy"
            >
              Apply
            </Link>
          </nav>
        </div>
      )}
    </header>
  );
}
