import Link from 'next/link';
import { cn } from '@/lib/cn';
import type { EventItem } from '@/lib/content';
import { TagPill } from './TagPill';

const kindTone: Record<EventItem['kind'], 'teal' | 'navy' | 'rust' | 'forest' | 'purple'> = {
  school: 'forest',
  conference: 'rust',
  workshop: 'purple',
  colloquium: 'navy',
  seminar: 'teal',
};

function fmtRange(start: string | null, end: string | null): string {
  if (!start) return '';
  const s = new Date(start);
  const e = end ? new Date(end) : null;
  const opts: Intl.DateTimeFormatOptions = { day: 'numeric', month: 'short', year: 'numeric' };
  if (!e || start === end) return s.toLocaleDateString('en-GB', opts);
  return `${s.toLocaleDateString('en-GB', { day: 'numeric', month: 'short' })} — ${e.toLocaleDateString('en-GB', opts)}`;
}

export function EventCard({ event, compact = false }: { event: EventItem; compact?: boolean }) {
  return (
    <Link
      href={`/events/${event.slug}`}
      className={cn(
        'group flex flex-col rounded-2xl border border-ink/10 bg-white p-6 shadow-sm transition-all hover:-translate-y-0.5 hover:border-teal/30 hover:shadow-md',
        compact ? 'gap-3' : 'gap-4',
      )}
    >
      <div className="flex items-center gap-3">
        <TagPill tone={kindTone[event.kind]}>{event.kind}</TagPill>
        {event.startDate && (
          <span className="font-sans text-xs font-bold uppercase tracking-[0.15em] text-mid">
            {fmtRange(event.startDate, event.endDate)}
          </span>
        )}
      </div>
      <h3 className="font-serif text-lg font-bold leading-snug text-ink group-hover:text-teal">
        {event.title}
      </h3>
      {!compact && event.summary && (
        <p className="font-serif text-[15px] leading-relaxed text-mid">{event.summary}</p>
      )}
      {event.location && (
        <p className="font-sans text-xs font-bold uppercase tracking-[0.15em] text-light-ink">
          {event.location}
        </p>
      )}
    </Link>
  );
}
