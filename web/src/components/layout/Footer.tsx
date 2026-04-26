import Link from 'next/link';
import { Eyebrow } from '@/components/brand/Eyebrow';
import { GoldBar } from '@/components/brand/GoldBar';

const LINKS = [
  { heading: 'Institute', items: [
    { href: '/about', label: 'About' },
    { href: '/people', label: 'People' },
    { href: '/visit', label: 'Visit' },
  ]},
  { heading: 'Research', items: [
    { href: '/research', label: 'Areas' },
    { href: '/publications', label: 'Publications' },
    { href: '/events', label: 'Events' },
  ]},
  { heading: 'Academics', items: [
    { href: '/academics', label: 'Programmes' },
    { href: '/apply', label: 'Apply' },
    { href: '/news', label: 'News' },
  ]},
];

export function Footer() {
  return (
    <footer className="panel bg-deep-navy text-light-ink">
      <div className="mx-auto grid max-w-7xl gap-12 px-6 py-16 md:grid-cols-[1.4fr_1fr_1fr_1fr] md:px-10 md:py-20">
        <div>
          <Eyebrow tone="gold">Contact · Murakaza neza</Eyebrow>
          <h3 className="mt-3 font-serif text-2xl font-bold text-white">
            East African Institute for <span className="text-gold">Fundamental Research</span>
          </h3>
          <GoldBar className="mt-5" />
          <div className="mt-6 space-y-2 font-serif text-[15px] leading-relaxed text-light-ink">
            <p>University of Rwanda · College of Science and Technology</p>
            <p>Avenue de l&rsquo;Armée, Kigali, Rwanda</p>
            <p>
              <a href="mailto:info@eaifr.org" className="text-bright-teal hover:underline">
                info@eaifr.org
              </a>
            </p>
          </div>
        </div>

        {LINKS.map((group) => (
          <div key={group.heading}>
            <Eyebrow tone="light">{group.heading}</Eyebrow>
            <ul className="mt-4 space-y-2 font-serif text-[15px]">
              {group.items.map((item) => (
                <li key={item.href}>
                  <Link href={item.href} className="text-white/80 transition-colors hover:text-white">
                    {item.label}
                  </Link>
                </li>
              ))}
            </ul>
          </div>
        ))}
      </div>

      <div className="border-t border-white/10">
        <div className="mx-auto flex max-w-7xl flex-col gap-3 px-6 py-6 text-xs text-light-ink md:flex-row md:items-center md:justify-between md:px-10">
          <p>© {new Date().getFullYear()} EAIFR · A UNESCO Category II institute.</p>
          <p className="font-sans tracking-wide">
            In partnership with{' '}
            <a href="https://www.ictp.it" className="text-bright-teal hover:underline" target="_blank" rel="noopener noreferrer">
              ICTP
            </a>{' '}
            ·{' '}
            <a href="https://ur.ac.rw" className="text-bright-teal hover:underline" target="_blank" rel="noopener noreferrer">
              University of Rwanda
            </a>{' '}
            ·{' '}
            <a href="https://www.unesco.org" className="text-bright-teal hover:underline" target="_blank" rel="noopener noreferrer">
              UNESCO
            </a>
          </p>
        </div>
      </div>
    </footer>
  );
}
