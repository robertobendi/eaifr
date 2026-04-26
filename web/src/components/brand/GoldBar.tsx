import { cn } from '@/lib/cn';

export function GoldBar({ className }: { className?: string }) {
  return <div className={cn('gold-bar', className)} aria-hidden="true" />;
}
