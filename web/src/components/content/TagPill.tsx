import { cn } from '@/lib/cn';

type Tone = 'teal' | 'navy' | 'rust' | 'forest' | 'purple' | 'gold';

const toneClass: Record<Tone, string> = {
  teal: 'text-teal border-teal',
  navy: 'text-navy border-navy',
  rust: 'text-rust border-rust',
  forest: 'text-forest border-forest',
  purple: 'text-[#5B4FA0] border-[#5B4FA0]',
  gold: 'text-gold border-gold',
};

export function TagPill({
  children,
  tone = 'teal',
  icon,
  className,
}: {
  children: React.ReactNode;
  tone?: Tone;
  icon?: React.ReactNode;
  className?: string;
}) {
  return (
    <span
      className={cn(
        'inline-flex items-center gap-1.5 rounded-full border-[1.5px] px-3.5 py-1.5 font-sans text-xs font-bold tracking-wide',
        toneClass[tone],
        className,
      )}
    >
      {icon && <span className="inline-flex shrink-0 [&>svg]:h-3.5 [&>svg]:w-3.5 [&>svg]:stroke-current [&>svg]:fill-none [&>svg]:stroke-[1.8]">{icon}</span>}
      {children}
    </span>
  );
}
