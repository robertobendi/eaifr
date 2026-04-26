import { cn } from '@/lib/cn';

/**
 * The italic gold tagline that sits above every hero title in the
 * brochure — e.g. "Murakaza neza · Welcome".
 */
export function Greeting({
  children,
  className,
}: {
  children: React.ReactNode;
  className?: string;
}) {
  return <p className={cn('greeting text-lg md:text-xl', className)}>{children}</p>;
}
