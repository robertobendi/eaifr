import { Eyebrow } from '@/components/brand/Eyebrow';
import { GoldBar } from '@/components/brand/GoldBar';
import { DotGrid } from '@/components/brand/DotGrid';
import { cn } from '@/lib/cn';

type Tone = 'cream' | 'warm' | 'navy' | 'deep-navy' | 'forest';

const toneClass: Record<Tone, { bg: string; title: string; body: string; eyebrowTone: 'teal' | 'gold' | 'light' }> = {
  cream:    { bg: 'bg-cream text-mid',                         title: 'text-ink',     body: 'text-mid',       eyebrowTone: 'teal' },
  warm:     { bg: 'bg-warm text-mid',                          title: 'text-ink',     body: 'text-mid',       eyebrowTone: 'teal' },
  navy:     { bg: 'bg-navy text-white/90',                     title: 'text-white',   body: 'text-white/80',  eyebrowTone: 'gold' },
  'deep-navy': { bg: 'bg-deep-navy text-white/90',             title: 'text-white',   body: 'text-white/80',  eyebrowTone: 'gold' },
  forest:   { bg: 'bg-[linear-gradient(180deg,#143D28_0%,#20603D_100%)] text-white/90', title: 'text-white', body: 'text-white/80', eyebrowTone: 'gold' },
};

/**
 * A reusable page hero — the brochure "panel with title + greeting + bar".
 * Each top-level page (About, Research, Events, …) opens with one of
 * these. The tone controls the background + type color.
 */
export function PageHero({
  eyebrow,
  title,
  accent,
  summary,
  tone = 'cream',
}: {
  eyebrow: string;
  title: React.ReactNode;
  accent?: string;
  summary?: string;
  tone?: Tone;
}) {
  const t = toneClass[tone];
  const onDark = tone !== 'cream' && tone !== 'warm';
  return (
    <section className={cn('panel relative overflow-hidden', t.bg)}>
      {onDark && (
        <DotGrid cols={4} rows={4} tone="gold" className="absolute right-8 top-8 hidden sm:grid" />
      )}
      <div className="mx-auto max-w-6xl px-6 pb-16 pt-20 md:px-10 md:pb-24 md:pt-28">
        <Eyebrow tone={t.eyebrowTone}>{eyebrow}</Eyebrow>
        <h1 className={cn('mt-5 max-w-4xl font-serif text-4xl font-bold leading-[1.1] tracking-tight md:text-5xl', t.title)}>
          {title}
          {accent && <span className={cn('block font-normal italic', tone === 'cream' || tone === 'warm' ? 'text-gold' : 'text-gold')}>{accent}</span>}
        </h1>
        <GoldBar className="mt-6" />
        {summary && (
          <p className={cn('mt-7 max-w-2xl font-serif text-lg leading-relaxed', t.body)}>
            {summary}
          </p>
        )}
      </div>
    </section>
  );
}
