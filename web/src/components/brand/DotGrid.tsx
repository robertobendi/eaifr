import { cn } from '@/lib/cn';

type Tone = 'gold' | 'teal' | 'cream';

const toneStyle: Record<Tone, string> = {
  gold: 'bg-gold',
  teal: 'bg-teal',
  cream: 'bg-cream',
};

export function DotGrid({
  cols = 3,
  rows = 3,
  tone = 'gold',
  className,
}: {
  cols?: 3 | 4 | 5 | 6;
  rows?: number;
  tone?: Tone;
  className?: string;
}) {
  const n = cols * rows;
  return (
    <div
      className={cn('dot-grid', className)}
      style={{ gridTemplateColumns: `repeat(${cols}, 9px)` }}
      aria-hidden="true"
    >
      {Array.from({ length: n }).map((_, i) => (
        <span key={i} className={toneStyle[tone]} />
      ))}
    </div>
  );
}
