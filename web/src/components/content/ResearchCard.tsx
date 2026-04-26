import Image from 'next/image';
import Link from 'next/link';
import { cn } from '@/lib/cn';
import type { ResearchArea } from '@/lib/content';

type Accent = ResearchArea['accent'];

const accentStyles: Record<
  Accent,
  { subtitle: string; cta: string; glow: string; tint: string; ring: string; iconBg: string }
> = {
  teal: {
    subtitle: 'text-teal',
    cta: 'text-teal',
    glow: 'hover:shadow-[0_30px_60px_-25px_rgba(13,128,128,0.55)]',
    tint: 'from-teal/70 via-teal/20 to-transparent',
    ring: 'hover:ring-teal/40',
    iconBg: 'bg-teal',
  },
  navy: {
    subtitle: 'text-navy',
    cta: 'text-navy',
    glow: 'hover:shadow-[0_30px_60px_-25px_rgba(11,61,91,0.55)]',
    tint: 'from-navy/80 via-navy/30 to-transparent',
    ring: 'hover:ring-navy/40',
    iconBg: 'bg-navy',
  },
  forest: {
    subtitle: 'text-forest',
    cta: 'text-forest',
    glow: 'hover:shadow-[0_30px_60px_-25px_rgba(32,96,61,0.55)]',
    tint: 'from-forest/75 via-forest/25 to-transparent',
    ring: 'hover:ring-forest/40',
    iconBg: 'bg-forest',
  },
  rust: {
    subtitle: 'text-rust',
    cta: 'text-rust',
    glow: 'hover:shadow-[0_30px_60px_-25px_rgba(199,92,46,0.55)]',
    tint: 'from-rust/70 via-rust/20 to-transparent',
    ring: 'hover:ring-rust/40',
    iconBg: 'bg-rust',
  },
};

// Cover image + icon per area slug. Matches the brochure pairings.
const coverBySlug: Record<string, string> = {
  'geophysics': '/img/research-solid-earth.jpg',
  'condensed-matter-physics': '/img/research-condensed-matter.jpg',
  // Legacy slug still supported
  'physics-of-the-solid-earth': '/img/research-solid-earth.jpg',
};

function AreaIcon({ slug }: { slug: string }) {
  if (slug === 'geophysics' || slug === 'physics-of-the-solid-earth') {
    // Globe — matches the brochure Geophysics icon
    return (
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
        <circle cx="12" cy="12" r="9" />
        <path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18" />
      </svg>
    );
  }
  // Lattice — matches the brochure Condensed Matter icon
  return (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
      <rect x="3" y="3" width="7" height="7" rx="1.2" />
      <rect x="14" y="3" width="7" height="7" rx="1.2" />
      <rect x="3" y="14" width="7" height="7" rx="1.2" />
      <rect x="14" y="14" width="7" height="7" rx="1.2" />
    </svg>
  );
}

export function ResearchCard({ area }: { area: ResearchArea }) {
  const s = accentStyles[area.accent];
  const cover = coverBySlug[area.slug];

  // The `short` field holds the brochure-style subtitle (e.g.
  // "Volcanology & Seismology"). Overview is the long paragraph — we
  // show a truncated preview here so the card isn't dominated by copy.
  const preview =
    area.overview.length > 180
      ? area.overview.slice(0, area.overview.indexOf(' ', 160) || 180) + '…'
      : area.overview;

  return (
    <Link
      href={`/research/${area.slug}`}
      className={cn(
        'group relative flex h-full flex-col overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-ink/10 transition-all duration-500',
        'hover:-translate-y-1 hover:ring-2',
        s.glow,
        s.ring,
      )}
    >
      <div className="relative aspect-[16/10] w-full overflow-hidden bg-warm">
        {cover ? (
          <Image
            src={cover}
            alt=""
            fill
            sizes="(max-width: 768px) 90vw, 480px"
            className="object-cover transition-transform duration-[900ms] ease-out group-hover:scale-[1.04]"
          />
        ) : (
          <div className="absolute inset-0 bg-gradient-to-br from-cream to-warm" />
        )}
        <div
          className={cn(
            'pointer-events-none absolute inset-0 bg-gradient-to-t mix-blend-multiply',
            s.tint,
          )}
        />
      </div>

      <div className="flex flex-1 flex-col p-8">
        <div className="flex items-start gap-4">
          <div className={cn('flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-white', s.iconBg)}>
            <span className="h-5 w-5"><AreaIcon slug={area.slug} /></span>
          </div>
          <div>
            <h3 className="display text-[1.6rem] leading-tight text-ink">
              {area.name}
            </h3>
            {area.short && (
              <p className={cn('mt-1 font-sans text-[13px] font-bold tracking-wide', s.subtitle)}>
                {area.short}
              </p>
            )}
          </div>
        </div>

        {preview && (
          <p className="mt-6 font-serif text-[15.5px] leading-[1.6] text-mid">{preview}</p>
        )}

        <span
          className={cn(
            'mt-auto flex items-center gap-2 pt-8 font-sans text-xs font-bold uppercase tracking-[0.2em] transition-transform group-hover:translate-x-0.5',
            s.cta,
          )}
        >
          Explore
          <span aria-hidden>→</span>
        </span>
      </div>
    </Link>
  );
}
