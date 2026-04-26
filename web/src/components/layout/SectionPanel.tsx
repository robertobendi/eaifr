import { cn } from '@/lib/cn';

type Bg = 'cream' | 'warm' | 'navy' | 'deep-navy' | 'forest' | 'white';

const bgClass: Record<Bg, string> = {
  cream: 'bg-cream text-mid',
  warm: 'bg-warm text-mid',
  navy: 'bg-navy text-white/90',
  'deep-navy': 'bg-deep-navy text-white/90',
  forest:
    'text-white/90 bg-[linear-gradient(180deg,#143D28_0%,#20603D_100%)]',
  white: 'bg-white text-mid',
};

/**
 * A full-width section with the signature 4px teal top border of the
 * brochure. Use `panel={false}` for sections that shouldn't carry the
 * stripe (e.g. the one just below the hero, to avoid a double stripe).
 */
export function SectionPanel({
  bg = 'white',
  panel = true,
  className,
  children,
  id,
}: {
  bg?: Bg;
  panel?: boolean;
  className?: string;
  children: React.ReactNode;
  id?: string;
}) {
  return (
    <section
      id={id}
      className={cn(
        panel && 'panel',
        bgClass[bg],
        'relative overflow-hidden',
        className,
      )}
    >
      {children}
    </section>
  );
}

export function SectionInner({
  children,
  className,
  size = 'md',
}: {
  children: React.ReactNode;
  className?: string;
  size?: 'sm' | 'md' | 'lg';
}) {
  const max = size === 'sm' ? 'max-w-3xl' : size === 'lg' ? 'max-w-7xl' : 'max-w-6xl';
  return (
    <div className={cn('mx-auto px-6 py-16 md:px-10 md:py-24', max, className)}>{children}</div>
  );
}
