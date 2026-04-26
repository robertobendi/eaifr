import { cn } from '@/lib/cn';

type Tone = 'teal' | 'gold' | 'navy' | 'forest' | 'rust' | 'light';

const toneClass: Record<Tone, string> = {
  teal: 'text-teal',
  gold: 'text-gold',
  navy: 'text-navy',
  forest: 'text-forest',
  rust: 'text-rust',
  light: 'text-light-ink',
};

export function Eyebrow({
  children,
  tone = 'teal',
  className,
}: {
  children: React.ReactNode;
  tone?: Tone;
  className?: string;
}) {
  return <p className={cn('eyebrow', toneClass[tone], className)}>{children}</p>;
}
