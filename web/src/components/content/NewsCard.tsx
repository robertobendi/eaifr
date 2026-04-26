import Link from 'next/link';
import type { NewsItem } from '@/lib/content';

export function NewsCard({ item }: { item: NewsItem }) {
  const date = item.publishedAt ? new Date(item.publishedAt) : null;
  return (
    <Link
      href={`/news/${item.slug}`}
      className="group flex flex-col gap-3 rounded-2xl border border-ink/10 bg-white p-6 shadow-sm transition-all hover:-translate-y-0.5 hover:border-teal/30 hover:shadow-md"
    >
      {date && (
        <time
          dateTime={date.toISOString()}
          className="font-sans text-xs font-bold uppercase tracking-[0.18em] text-teal"
        >
          {date.toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' })}
        </time>
      )}
      <h3 className="font-serif text-lg font-bold leading-snug text-ink group-hover:text-teal">
        {item.title}
      </h3>
      {item.excerpt && (
        <p className="line-clamp-3 font-serif text-[15px] leading-relaxed text-mid">{item.excerpt}</p>
      )}
    </Link>
  );
}
