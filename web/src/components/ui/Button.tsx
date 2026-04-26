import Link from 'next/link';
import { cn } from '@/lib/cn';

type Variant = 'primary' | 'ghost' | 'gold' | 'outline-light';

const variantClass: Record<Variant, string> = {
  primary:
    'bg-teal text-white hover:bg-[#0b6e6e] focus-visible:outline-teal',
  ghost:
    'text-teal hover:bg-teal/10 focus-visible:outline-teal',
  gold:
    'bg-gold text-deep-navy hover:bg-[#c5992f] focus-visible:outline-gold',
  'outline-light':
    'border border-white/30 text-white hover:bg-white/10 focus-visible:outline-white',
};

type ButtonProps = {
  href?: string;
  children: React.ReactNode;
  variant?: Variant;
  className?: string;
  external?: boolean;
} & Omit<React.ButtonHTMLAttributes<HTMLButtonElement>, 'children' | 'className'>;

export function Button({
  href,
  children,
  variant = 'primary',
  className,
  external,
  ...rest
}: ButtonProps) {
  const shared = cn(
    'inline-flex items-center justify-center gap-2 rounded-full px-5 py-2.5 font-sans text-sm font-bold tracking-wide transition-colors focus-visible:outline-2 focus-visible:outline-offset-2',
    variantClass[variant],
    className,
  );

  if (href) {
    const extraProps = external ? { target: '_blank', rel: 'noopener noreferrer' } : {};
    return (
      <Link href={href} className={shared} {...extraProps}>
        {children}
      </Link>
    );
  }
  return (
    <button className={shared} {...rest}>
      {children}
    </button>
  );
}
